<?php

namespace App\Http\Controllers\Api\V1\Request;

use App\Models\User;
use App\Jobs\NotifyViaMqtt;
use App\Models\Admin\Driver;
use Illuminate\Http\Request;
use App\Jobs\NotifyViaSocket;
use App\Models\Request\RequestMeta;
use App\Base\Constants\Masters\PushEnums;
use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Request\Request as RequestModel;
use App\Http\Requests\Request\AcceptRejectRequest;
use App\Jobs\Notifications\AndroidPushNotification;
use App\Transformers\Requests\TripRequestTransformer;
use App\Models\Request\DriverRejectedRequest;
use Kreait\Firebase\Contract\Database;
use App\Jobs\Notifications\SendPushNotification;
use Sk\Geohash\Geohash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Helpers\Rides\FetchDriversFromFirebaseHelpers;
use App\Models\Request\RequestCycles;
use App\Models\Admin\ZoneType;
use Carbon\Carbon;

/**
 * @group Driver-trips-apis
 *
 * APIs for Driver-trips apis
 */
class RequestAcceptRejectController extends BaseController
{
    use FetchDriversFromFirebaseHelpers;

    protected $request;

    public function __construct(RequestModel $request,Database $database)
    {
        $this->request = $request;
        $this->database = $database;
    }

    /**
    * Driver Response for Trip Request
    * @bodyParam request_id uuid required id request
    * @bodyParam is_accept boolean required response of request i.e accept or reject. input should be 0 or 1.
    * @response {
    "success": true,
    "message": "success"}
    */
    public function respondRequest(AcceptRejectRequest $request)
    {

        /**
        * Get Request Detail
        * Validate the request i,e the request is already accepted by some one and it is a valid request for accept or reject state.
        * If is_accept is true then update the driver's id to the request detail.
        * And Update the driver's available state as false. And delete all meta driver records from request_meta table
        * Send the notification to the user with request detail.
        * If is_accept is false, then delete the driver record from request_meta table
        * And Send the request to next driver who is available in the request_meta table
        * If there is no driver available in request_meta table, then send notification with no driver available state to the user.
        */
        // Get Request Detail
        $request_detail = $this->request->where('id', $request->input('request_id'))->first();
      
        // Validate the request i,e the request is already accepted by some one and it is a valid request for accept or reject state.
        $this->validateRequestDetail($request_detail);
        $driver = auth()->user()->driver;
        // Delete Meta Driver From Firebase
        // $this->database->getReference('request-meta/'.$request_detail->id)->set(['driver_id'=>'','request_id'=>$request_detail->id,'user_id'=>$request_detail->user_id,'active'=>1,'updated_at'=> Database::SERVER_TIMESTAMP]);

        // $this->database->getReference('request-meta/'.$request_detail->id.'/'.$driver->id)->remove();




        $get_request_datas = RequestCycles::where('request_id', $request->input('request_id'))->first();
        if($get_request_datas)
        { 
            // Log::info('-------------accept_reject----------------');
            // Log::info(auth()->user());
            $user_data = User::find(auth()->user()->driver->user_id);
            $request_data = json_decode(base64_decode($get_request_datas->request_data), true);
            $request_datas['request_id'] = $request_detail->id;
            $request_datas['user_id'] = $request_detail->user_id; 
            $request_datas['driver_id'] = auth()->user()->driver->id; 
            $driver_details['name'] = auth()->user()->driver->name;
            $driver_details['mobile'] = auth()->user()->driver->mobile;
            $driver_details['image'] = $user_data->profile_picture;
            if ($request->input('is_accept')) {
                $is_accept = 2;
                $status = 1;
                $process_type = "accept";
            }
            else{
                $is_accept = 3;
                $status = 2;
                $process_type = "decline";
            }
            $rating = number_format(auth()->user()->rating, 2);
            $data[0]['rating'] = $rating; 
            $data[0]['status'] = $status; 
            $data[0]['is_accept'] = $is_accept; 
           
            $data[0]['dricver_details'] = $driver_details;
            $data[0]['created_at'] = date("Y-m-d H:i:s", time());  
            $data[0]['orderby_status'] = intval($get_request_datas->orderby_status) + 1;
            $data[0]['process_type'] =  $process_type; 
            $request_datas['orderby_status'] = intval($get_request_datas->orderby_status) + 1;
           
            if ($request_data === null) {
                // If $request_data is null, initialize it as an empty array
                $request_data = [];
            }
            $request_data1 = array_merge($request_data, $data);
            $request_datas['request_data'] = base64_encode(json_encode($request_data1));  
            Log::info($request_datas);
            $insert_request_cycles = RequestCycles::where('id',$get_request_datas->id)->update($request_datas);

        }

        if ($request->input('is_accept')) {

            $this->database->getReference('request-meta/'.$request_detail->id)->set(['driver_id'=>$driver->id,'request_id'=>$request_detail->id,'user_id'=>$request_detail->user_id,'active'=>1,'is_accepted'=>1,'profile_picture'=>auth()->user()->profile_picture,'name'=>auth()->user()->name,'rating'=>auth()->user()->rating,'updated_at'=> Database::SERVER_TIMESTAMP]); 
            $this->database->getReference('requests/'.$request_detail->id)->update(['is_accept' => 1]);

            $this->database->getReference('request-meta/'.$request_detail->id)->remove();

            // Update Driver to the trip request detail
            $updated_params = ['driver_id'=>auth()->user()->driver->id,
            'accepted_at'=>date('Y-m-d H:i:s'),
            'is_driver_started'=>true];

            if(auth()->user()->driver->owner_id){

                $updated_params['owner_id'] = auth()->user()->driver->owner_id;

                $updated_params['fleet_id'] = auth()->user()->driver->fleet_id;
            }

            $request_detail->update($updated_params);
            $request_detail->fresh();
            // Delete all Meta records of the request
            $this->deleteMetaRecords($request);
            // Update the driver's available state as false
            // $driver->available = false;
            $driver->save();
            $request_result =  fractal($request_detail, new TripRequestTransformer);
            $push_request_detail = $request_result->toJson();
            if ($request_detail->if_dispatch) {
                goto accet_dispatch_notify;
            }
            $user = User::find($request_detail->user_id);
            // $title = trans('push_notifications.trip_accepted_title');
            // $body = trans('push_notifications.trip_accepted_body');

            $details = [
                "user_name" => $user->name,
                "user_mobile" => $user->mobile,
                "partner_name" => $driver->name,
                "pickup_time" => $request_detail->trip_start_time != "" ? date("d-m-Y H:i:s", strtotime($request_detail->trip_start_time)) : "",
                "pickup_location" => $request_detail->pick_address,
                "destination" => $request_detail->drop_address,
                "pickup_poc_instruction" => $request_detail->pickup_poc_instruction,
            ];
            \Mail::to($driver->email)->send(new \App\Mail\DriverTakesRideFromMarketMail($details));

            $details = [
                "user_name" => $user->name,
                "partner_name" => $driver->name,
                "pickup_time" => $request_detail->trip_start_time != "" ? date("d-m-Y H:i:s", strtotime($request_detail->trip_start_time)) : "",
                "pickup_location" => $request_detail->pick_address,
                "destination" => $request_detail->drop_address,
                "request_eta_amount" => $request_detail->request_eta_amount,
                "requested_currency_symbol" => $request_detail->requested_currency_symbol,
                "vehicle_type" => $driver->vehicle_type,
            ];
            \Mail::to($user->email)->send(new \App\Mail\BookingConfirmationCustomerMail($details));

            $title = trans('push_notifications.trip_accepted_title',[],$user->lang);
            $body = trans('push_notifications.trip_accepted_body',[],$user->lang);  
                  
            $push_data = ['notification_enum'=>PushEnums::TRIP_ACCEPTED_BY_DRIVER,'result'=>(string)$push_request_detail];
            dispatch(new SendPushNotification($user,$title,$body));

             accet_dispatch_notify:
        // @TODO send sms,email & push notification with request detail
        } else {

            $this->database->getReference('request-meta/'.$request_detail->id)->set(['driver_id'=>$driver->id,'request_id'=>$request_detail->id,'user_id'=>$request_detail->user_id,'active'=>1,'is_accepted'=>0,'profile_picture'=>auth()->user()->profile_picture,'name'=>auth()->user()->name,'rating'=>auth()->user()->rating,'updated_at'=> Database::SERVER_TIMESTAMP]); 

            // Log::info('request-number');
            // Log::info($request_detail->request_number);
            // Log::info('----------');

            if($request_detail->old_driver_id != ""){
                // get type id
                $zone_type_detail = ZoneType::where('id', $request->vehicle_type)->first();
                $type_id = $zone_type_detail->type_id;

                $request_data = [
                    "pick_lat" => $request_detail->pick_lat,
                    "pick_lng" => $request_detail->pick_lng,
                    "drop_lat" => $request_detail->drop_lat,
                    "drop_lng" => $request_detail->drop_lng,
                ];
                $request_data = (object) $request_data;
                $nearest_drivers =  $this->getFirebaseDrivers($request_data, $type_id);

                $selected_drivers = [];
                $i = 0;
                foreach ($nearest_drivers[0] as $driver) {
                    // $selected_drivers[$i]["request_id"] = $request_detail->id;
                    foreach ($nearest_drivers[1] as $key => $firebase_driver) {

                            if($driver->id==$key){
                                $selected_drivers[$i]["distance_to_pickup"] = $firebase_driver['distance'];
                            }
                    }
                    
                    $selected_drivers[$i]["user_id"] = $user_detail->id;
                    $selected_drivers[$i]["driver_id"] = $driver->id;
                    $selected_drivers[$i]["active"] = 0;
                    $selected_drivers[$i]["assign_method"] = 1;
                    $selected_drivers[$i]["created_at"] = date('Y-m-d H:i:s');
                    $selected_drivers[$i]["updated_at"] = date('Y-m-d H:i:s');

                    if(get_settings('trip_dispatch_type')==0){
                        $selected_drivers[$i]["active"] = 1;
                    
                        // Add Driver into Firebase Request Meta
                        $this->database->getReference('request-meta/'.$request_detail->id)->set(['driver_id'=>$driver->id,'request_id'=>$request_detail->id,'user_id'=>$request_detail->user_id,'active'=>1,'updated_at'=> Database::SERVER_TIMESTAMP]);

                
                        $driver = Driver::find($driver->id);

                        $notifable_driver = $driver->user;
                        $push_data = ['title' => $title,'message' => $body,'push_type'=>'meta-request'];

                        $title = trans('push_notifications.new_request_title',[],$notifable_driver->lang);
                        $body = trans('push_notifications.new_request_body',[],$notifable_driver->lang);

                        dispatch(new SendPushNotification($notifable_driver,$title,$body,$push_data));


                    }

                    $i++;
                }
            }
            $request_result =  fractal($request_detail, new TripRequestTransformer)->parseIncludes('userDetail');
            // Save Driver Reject Requests
            DriverRejectedRequest::create(['request_id'=>$request_detail->id,
                'driver_id'=>$driver->id]);

            $push_request_detail = $request_result->toJson();
            // Delete Driver record from meta table
            RequestMeta::where('request_id', $request->input('request_id'))->where('driver_id', $driver->id)->delete();

                 // Send Ride to the Nearest Next Driver
                $this->fetchDriversFromFirebase($request_detail);

                goto end;

                // Cancell the request as automatic cancell state
                $request_detail->update(['is_cancelled'=>true,'cancel_method'=>0,'cancelled_at'=>date('Y-m-d H:i:s')]);
                $this->database->getReference('bid-meta/'.$request_detail->id)->remove();
                $request_result =  fractal($request_detail, new TripRequestTransformer);
                $push_request_detail = $request_result->toJson();
                // Send push notification as no-driver-found to the user
                if ($request_detail->if_dispatch) {
                    goto dispatch_notify;
                }
                $user = User::find($request_detail->user_id);
                // $title = trans('push_notifications.no_driver_found_title');
                // $body = trans('push_notifications.no_driver_found_body');

                $title = trans('push_notifications.no_driver_found_title',[],$user->lang);
                $body = trans('push_notifications.no_driver_found_body',[],$user->lang);                
                dispatch(new SendPushNotification($user,$title,$body));
                $push_data = ['notification_enum'=>PushEnums::NO_DRIVER_FOUND,'result'=>(string)$push_request_detail];
                dispatch_notify:
                no_drivers_available:

        }
        end:

         Artisan::call('assign_drivers:for_regular_rides');

        return $this->respondSuccess();
    }
    
    public function respond_for_owner(Request $request)
    {

        /**
        * Get Request Detail
        * Validate the request i,e the request is already accepted by some one and it is a valid request for accept or reject state.
        * If is_accept is true then update the driver's id to the request detail.
        * And Update the driver's available state as false. And delete all meta driver records from request_meta table
        * Send the notification to the user with request detail.
        * If is_accept is false, then delete the driver record from request_meta table
        * And Send the request to next driver who is available in the request_meta table
        * If there is no driver available in request_meta table, then send notification with no driver available state to the user.
        */
        // Get Request Detail
        $request_detail = $this->request->where('id', $request->input('request_id'))->first();
        $driver_user = Driver::where("id",$request->driver_id)->first()->user;
        $driver = Driver::find($request->driver_id);
       
        // Delete Meta Driver From Firebase
        // $this->database->getReference('request-meta/'.$request_detail->id)->set(['driver_id'=>'','request_id'=>$request_detail->id,'user_id'=>$request_detail->user_id,'active'=>1,'updated_at'=> Database::SERVER_TIMESTAMP]);

        // $this->database->getReference('request-meta/'.$request_detail->id.'/'.$driver->id)->remove();




        $get_request_datas = RequestCycles::where('request_id', $request->input('request_id'))->first();
        if($get_request_datas)
        { 
            // Log::info('-------------accept_reject----------------');
            // Log::info(auth()->user());
            $user_data = User::find($request->driver_id);
            $request_data = json_decode(base64_decode($get_request_datas->request_data), true);
            $request_datas['request_id'] = $request_detail->id;
            $request_datas['user_id'] = $request_detail->user_id; 
            $request_datas['driver_id'] = $driver->id; 
            $driver_details['name'] = $driver->name;
            $driver_details['mobile'] = $driver->mobile;
            $driver_details['image'] = $user_data->profile_picture??null;
            if ($request->input('is_accept')) {
                $is_accept = 2;
                $status = 1;
                $process_type = "accept";
            }
            else{
                $is_accept = 3;
                $status = 2;
                $process_type = "decline";
            }
            $rating = number_format($driver->rating, 2);
            $data[0]['rating'] = $rating; 
            $data[0]['status'] = $status; 
            $data[0]['is_accept'] = $is_accept; 
           
            $data[0]['dricver_details'] = $driver_details;
            $data[0]['created_at'] = date("Y-m-d H:i:s", time());  
            $data[0]['orderby_status'] = intval($get_request_datas->orderby_status) + 1;
            $data[0]['process_type'] =  $process_type; 
            $request_datas['orderby_status'] = intval($get_request_datas->orderby_status) + 1;
           
            if ($request_data === null) {
                // If $request_data is null, initialize it as an empty array
                $request_data = [];
            }
            $request_data1 = array_merge($request_data, $data);
            $request_datas['request_data'] = base64_encode(json_encode($request_data1));  
            Log::info($request_datas);
            $insert_request_cycles = RequestCycles::where('id',$get_request_datas->id)->update($request_datas);

        }

        if ($request->input('is_accept')) {

            $this->database->getReference('request-meta/'.$request_detail->id)->set(['driver_id'=>$driver->id,'request_id'=>$request_detail->id,'user_id'=>$request_detail->user_id,'active'=>1,'is_accepted'=>1,'profile_picture'=>auth()->user()->profile_picture,'name'=>auth()->user()->name,'rating'=>auth()->user()->rating,'updated_at'=> Database::SERVER_TIMESTAMP]); 
            $this->database->getReference('requests/'.$request_detail->id)->update(['is_accept' => 1]);

            $this->database->getReference('request-meta/'.$request_detail->id)->remove();

            // Update Driver to the trip request detail
            $updated_params = ['driver_id'=>$driver->id,
            'accepted_at'=>date('Y-m-d H:i:s'),
            'is_driver_started'=>true];

            if(auth()->user()->owner->id){

                $updated_params['owner_id'] = auth()->user()->owner->id;

                $updated_params['fleet_id'] = $driver->fleet_id;
            }

            $request_detail->update($updated_params);
            $request_detail->fresh();
            // Delete all Meta records of the request
            $this->deleteMetaRecords($request);
            // Update the driver's available state as false
            // $driver->available = false;
            $driver->save();
            $request_result =  fractal($request_detail, new TripRequestTransformer);
            $push_request_detail = $request_result->toJson();
            if ($request_detail->if_dispatch) {
                goto accet_dispatch_notify;
            }
            $user = User::find($request_detail->user_id);
            // $title = trans('push_notifications.trip_accepted_title');
            // $body = trans('push_notifications.trip_accepted_body');

            $details = [
                "user_name" => $user->name,
                "user_mobile" => $user->mobile,
                "partner_name" => $driver->name,
                "pickup_time" => $request_detail->trip_start_time != "" ? date("d-m-Y H:i:s", strtotime($request_detail->trip_start_time)) : "",
                "pickup_location" => $request_detail->pick_address,
                "destination" => $request_detail->drop_address,
                "pickup_poc_instruction" => $request_detail->pickup_poc_instruction,
            ];
            \Mail::to($driver->email)->send(new \App\Mail\DriverTakesRideFromMarketMail($details));

            $details = [
                "user_name" => $user->name,
                "partner_name" => $driver->name,
                "pickup_time" => $request_detail->trip_start_time != "" ? date("d-m-Y H:i:s", strtotime($request_detail->trip_start_time)) : "",
                "pickup_location" => $request_detail->pick_address,
                "destination" => $request_detail->drop_address,
                "request_eta_amount" => $request_detail->request_eta_amount,
                "requested_currency_symbol" => $request_detail->requested_currency_symbol,
                "vehicle_type" => $driver->vehicle_type,
            ];
            \Mail::to($user->email)->send(new \App\Mail\BookingConfirmationCustomerMail($details));

            $title = trans('push_notifications.trip_accepted_title',[],$user->lang);
            $body = trans('push_notifications.trip_accepted_body',[],$user->lang);  
                  
            $push_data = ['notification_enum'=>PushEnums::TRIP_ACCEPTED_BY_DRIVER,'result'=>(string)$push_request_detail];
            dispatch(new SendPushNotification($user,$title,$body));

             accet_dispatch_notify:
        // @TODO send sms,email & push notification with request detail
        } else {

            $this->database->getReference('request-meta/'.$request_detail->id)->set(['driver_id'=>$driver->id,'request_id'=>$request_detail->id,'user_id'=>$request_detail->user_id,'active'=>1,'is_accepted'=>0,'profile_picture'=>$driver_user->profile_picture,'name'=>$driver_user->name,'rating'=>$driver_user->rating,'updated_at'=> Database::SERVER_TIMESTAMP]); 

            // Log::info('request-number');
            // Log::info($request_detail->request_number);
            // Log::info('----------');

            if($request_detail->old_driver_id != ""){
                // get type id
                $zone_type_detail = ZoneType::where('id', $request->vehicle_type)->first();
                $type_id = $zone_type_detail->type_id;

                $request_data = [
                    "pick_lat" => $request_detail->pick_lat,
                    "pick_lng" => $request_detail->pick_lng,
                    "drop_lat" => $request_detail->drop_lat,
                    "drop_lng" => $request_detail->drop_lng,
                ];
                $request_data = (object) $request_data;
                $nearest_drivers =  $this->getFirebaseDrivers($request_data, $type_id);

                $selected_drivers = [];
                $i = 0;
                foreach ($nearest_drivers[0] as $driver) {
                    // $selected_drivers[$i]["request_id"] = $request_detail->id;
                    foreach ($nearest_drivers[1] as $key => $firebase_driver) {

                            if($driver->id==$key){
                                $selected_drivers[$i]["distance_to_pickup"] = $firebase_driver['distance'];
                            }
                    }
                    
                    $selected_drivers[$i]["user_id"] = $user_detail->id;
                    $selected_drivers[$i]["driver_id"] = $driver->id;
                    $selected_drivers[$i]["active"] = 0;
                    $selected_drivers[$i]["assign_method"] = 1;
                    $selected_drivers[$i]["created_at"] = date('Y-m-d H:i:s');
                    $selected_drivers[$i]["updated_at"] = date('Y-m-d H:i:s');

                    if(get_settings('trip_dispatch_type')==0){
                        $selected_drivers[$i]["active"] = 1;
                    
                        // Add Driver into Firebase Request Meta
                        $this->database->getReference('request-meta/'.$request_detail->id)->set(['driver_id'=>$driver->id,'request_id'=>$request_detail->id,'user_id'=>$request_detail->user_id,'active'=>1,'updated_at'=> Database::SERVER_TIMESTAMP]);

                
                        $driver = Driver::find($driver->id);

                        $notifable_driver = $driver->user;
                        $push_data = ['title' => $title,'message' => $body,'push_type'=>'meta-request'];

                        $title = trans('push_notifications.new_request_title',[],$notifable_driver->lang);
                        $body = trans('push_notifications.new_request_body',[],$notifable_driver->lang);

                        dispatch(new SendPushNotification($notifable_driver,$title,$body,$push_data));


                    }

                    $i++;
                }
            }
            $request_result =  fractal($request_detail, new TripRequestTransformer)->parseIncludes('userDetail');
            // Save Driver Reject Requests
            DriverRejectedRequest::create(['request_id'=>$request_detail->id,
                'driver_id'=>$driver->id]);

            $push_request_detail = $request_result->toJson();
            // Delete Driver record from meta table
            RequestMeta::where('request_id', $request->input('request_id'))->where('driver_id', $driver->id)->delete();

                 // Send Ride to the Nearest Next Driver
                $this->fetchDriversFromFirebase($request_detail);

                goto end;

                // Cancell the request as automatic cancell state
                $request_detail->update(['is_cancelled'=>true,'cancel_method'=>0,'cancelled_at'=>date('Y-m-d H:i:s')]);
                $this->database->getReference('bid-meta/'.$request_detail->id)->remove();
                $request_result =  fractal($request_detail, new TripRequestTransformer);
                $push_request_detail = $request_result->toJson();
                // Send push notification as no-driver-found to the user
                if ($request_detail->if_dispatch) {
                    goto dispatch_notify;
                }
                $user = User::find($request_detail->user_id);
                // $title = trans('push_notifications.no_driver_found_title');
                // $body = trans('push_notifications.no_driver_found_body');

                $title = trans('push_notifications.no_driver_found_title',[],$user->lang);
                $body = trans('push_notifications.no_driver_found_body',[],$user->lang);                
                dispatch(new SendPushNotification($user,$title,$body));
                $push_data = ['notification_enum'=>PushEnums::NO_DRIVER_FOUND,'result'=>(string)$push_request_detail];
                dispatch_notify:
                no_drivers_available:

        }
        end:

         Artisan::call('assign_drivers:for_regular_rides');

        return $this->respondSuccess();
    }

    /**
    * Delete All Meta driver's records
    */
    public function deleteMetaRecords(Request $request)
    {
        RequestMeta::where('request_id', $request->input('request_id'))->delete();
    }

    /**
    * Validate the request detail
    */
    public function validateRequestDetail($request_detail)
    {
        if ($request_detail->is_driver_started && $request_detail->driver_id!=auth()->user()->driver->id) {
            $this->throwCustomException('request accepted by another driver');
        }

        if ($request_detail->is_completed) {
            $this->throwCustomException('request completed already');
        }
        if ($request_detail->is_cancelled) {
            $this->throwCustomException('request already cancelled');
        }
    }

     /**
    * Get Drivers from firebase
    */
    public function getFirebaseDrivers($request, $type_id)
    {
        $pick_lat = $request->pick_lat;
        $pick_lng = $request->pick_lng;

        // NEW flow        
        $driver_search_radius = get_settings('driver_search_radius')?:30;

        $radius = kilometer_to_miles($driver_search_radius);

        $calculatable_radius = ($radius/2);

        $calulatable_lat = 0.0144927536231884 * $calculatable_radius;
        $calulatable_long = 0.0181818181818182 * $calculatable_radius;

        $lower_lat = ($pick_lat - $calulatable_lat);
        $lower_long = ($pick_lng - $calulatable_long);

        $higher_lat = ($pick_lat + $calulatable_lat);
        $higher_long = ($pick_lng + $calulatable_long);

        $g = new Geohash();

        $lower_hash = $g->encode($lower_lat,$lower_long, 12);
        $higher_hash = $g->encode($higher_lat,$higher_long, 12);

        $conditional_timestamp = Carbon::now()->subMinutes(7)->timestamp;

        $vehicle_type = $type_id;

        $fire_drivers = $this->database->getReference('drivers')->orderByChild('g')->startAt($lower_hash)->endAt($higher_hash)->getValue();
        
        $firebase_drivers = [];

        $i=-1;
    

        foreach ($fire_drivers as $key => $fire_driver) {
            $i +=1; 
            $driver_updated_at = Carbon::createFromTimestamp($fire_driver['updated_at'] / 1000)->timestamp;


            if(array_key_exists('vehicle_type',$fire_driver) && $fire_driver['vehicle_type']==$vehicle_type && $fire_driver['is_active']==1 && $fire_driver['is_available']==1 && $conditional_timestamp < $driver_updated_at){


                $distance = distance_between_two_coordinates($pick_lat,$pick_lng,$fire_driver['l'][0],$fire_driver['l'][1],'K');

                if($distance <= $driver_search_radius){

                    $firebase_drivers[$fire_driver['id']]['distance']= $distance;

                }

            }elseif(array_key_exists('vehicle_types',$fire_driver)  && in_array($vehicle_type, $fire_driver['vehicle_types']) && $fire_driver['is_active']==1 && $fire_driver['is_available']==1 && $conditional_timestamp < $driver_updated_at)
                {


                $distance = distance_between_two_coordinates($pick_lat,$pick_lng,$fire_driver['l'][0],$fire_driver['l'][1],'K');

                if($distance <= $driver_search_radius){

                    $firebase_drivers[$fire_driver['id']]['distance']= $distance;

                }

            }      

        }
        $current_date = Carbon::now();

        asort($firebase_drivers);


        if (!empty($firebase_drivers)) {

            $nearest_driver_ids = [];

            $removable_driver_ids=[];

                foreach ($firebase_drivers as $key => $firebase_driver) {
                    
                    $nearest_driver_ids[]=$key;


                $has_enabled_my_route_drivers=Driver::where('id',$key)->where('active', 1)->where('approve', 1)->where('available', 1)->where(function($query)use($request){
                    $query->where('transport_type','taxi')->orWhere('transport_type','both');
                })->where('enable_my_route_booking',1)->first();


                $route_coordinates=null;

                if($has_enabled_my_route_drivers){

                    //get line string from helper
                    $route_coordinates = get_line_string($request->pick_lat, $request->pick_lng, $request->drop_lat, $request->drop_lng);

                }       
                        if($has_enabled_my_route_drivers!=null &$route_coordinates!=null){

                            $enabled_route_matched = $nearest_driver->intersects('route_coordinates',$route_coordinates)->first();
                            
                            if(!$enabled_route_matched){

                                $removable_driver_ids[]=$key;
                            }

                            $current_location_of_driver = $nearest_driver->enabledRoutes()->whereDate('created_at',$current_date)->orderBy('created_at','desc')->first();

                            if($current_location_of_driver){

                            $distance_between_current_location_to_drop = distance_between_two_coordinates($current_location_of_driver->current_lat, $current_location_of_driver->current_lng, $request->drop_lat, $request->drop_lng,'K');

                            $distance_between_current_location_to_my_route = distance_between_two_coordinates($current_location_of_driver->current_lat, $current_location_of_driver->current_lng, $nearest_driver->my_route_lat, $nearest_driver->my_route_lng,'K');

                            // Difference between both of above values

                            $difference = $distance_between_current_location_to_drop - $distance_between_current_location_to_my_route;

                            $difference=$difference < 0 ? (-1) * $difference : $difference;

                            if($difference>5){

                                $removable_driver_ids[]=$key;

                            }
    
                            }
                            
                        }


                }

            $nearest_driver_ids = array_diff($nearest_driver_ids,$removable_driver_ids);

                if(count($nearest_driver_ids)>0){
                    $nearest_driver_ids[0]=$nearest_driver_ids[0];

                }else{

                   $nearest_driver_ids=[];

                }

                $driver_search_radius = get_settings('driver_search_radius')?:30;

                $haversine = "(6371 * acos(cos(radians($pick_lat)) * cos(radians(pick_lat)) * cos(radians(pick_lng) - radians($pick_lng)) + sin(radians($pick_lat)) * sin(radians(pick_lat))))";
                // Get Drivers who are all going to accept or reject the some request that nears the user's current location.
                $meta_drivers = RequestMeta::whereHas('request.requestPlace', function ($query) use ($haversine,$driver_search_radius) {
                    $query->select('request_places.*')->selectRaw("{$haversine} AS distance")
                ->whereRaw("{$haversine} < ?", [$driver_search_radius]);
                })->pluck('driver_id')->toArray();

                $nearest_drivers = Driver::where('active', 1)->where('approve', 1)->where('available', 1)->where(function($query)use($request){
                    $query->where('transport_type','taxi')->orWhere('transport_type','both');
                })->whereIn('id', $nearest_driver_ids)->whereNotIn('id', $meta_drivers)->orderByRaw(DB::raw("FIELD(id, " . implode(',', $nearest_driver_ids) . ")"))->limit(10)->get();


                if ($nearest_drivers->isEmpty()) {
                    // $this->throwCustomException('all drivers are busy');

                    // return null;
                    return ['no-drivers-found','no-firebase-drivers'];

                }
                $returned_drivers = [$nearest_drivers,$firebase_drivers];
                
                return $returned_drivers;
            
        } else {

            return ['no-drivers-found','no-firebase-drivers'];

            // return null;
        }
    }


    public function reminder_90_60($id){
        $request_detail = RequestModel::where("id", $id)->first();

        $user = User::find($request_detail->user_id);

        if (!empty($user)) {
            $details = [
                "user_name" => $user->name,
                "user_mobile" => $user->mobile,
                "pickup_time" => $request_detail->trip_start_time ? date("d-m-Y H:i:s", strtotime($request_detail->trip_start_time)) : "",
                "pickup_location" => $request_detail->pick_address,
                "destination" => $request_detail->drop_address,
                "pickup_poc_instruction" => $request_detail->pickup_poc_instruction,
            ];

            if($user->email != ""){
                \Mail::to($user->email)->send(new \App\Mail\ReminderCustomerMail($details));
            }

            if (!empty($request_detail->driverDetail) && !empty($request_detail->driverDetail->email)) {
                $details["patner_name"] = $request_detail->driverDetail->name;
                \Mail::to($request_detail->driverDetail->email)->send(new \App\Mail\ReminderOwnerDriverMail($details));
            }

            if (!empty($request_detail->ownerDetail) && !empty($request_detail->ownerDetail->email)) {
                $details["patner_name"] = $request_detail->ownerDetail->owner_name;
                \Mail::to($request_detail->ownerDetail->email)->send(new \App\Mail\ReminderOwnerDriverMail($details));
            }
        }
        $request_detail->update(["is_driver_confirm" => 1]);
        
        return $this->respondSuccess();
    }

    public function customer_no_show(Request $request){
        $request_detail = RequestModel::where("id", $request->request_id)->first();

        $user = User::find($request_detail->user_id);

        if ($user && !empty($user->email)) {
            $details = [
                "user_name" => $user->name,
                "user_mobile" => $user->mobile,
                "pickup_time" => $request_detail->trip_start_time ? date("d-m-Y H:i:s", strtotime($request_detail->trip_start_time)) : "",
                "pickup_location" => $request_detail->pick_address,
                "destination" => $request_detail->drop_address,
                "flight_number" => $request_detail->flight_number,
                "pickup_poc_instruction" => $request_detail->pickup_poc_instruction,
            ];

            \Mail::to($user->email)->send(new \App\Mail\CustomerNoShowMailCustomer($details));

            if (!empty($request_detail->driverDetail) && !empty($request_detail->driverDetail->email)) {
                $details["partner_name"] = $request_detail->driverDetail->name;
                \Mail::to($request_detail->driverDetail->email)->send(new \App\Mail\CustomerNoShowMailOwnerDriver($details));
            }

            if (!empty($request_detail->ownerDetail) && !empty($request_detail->ownerDetail->email)) {
                $details["partner_name"] = $request_detail->ownerDetail->owner_name;
                \Mail::to($request_detail->ownerDetail->email)->send(new \App\Mail\CustomerNoShowMailOwnerDriver($details));
            }

            $request_detail_data = ["customer_no_show" => 1];
            if ($request->hasFile('customer_no_show_file')) {
                $imageName = rand(1111,9999). time() . '.' . $request->customer_no_show_file->extension();
                $request->customer_no_show_file->move(public_path('images'), $imageName);
                $request_detail_data['customer_no_show_file'] = $imageName;
            }

            $request_detail->update($request_detail_data);
        }
        
        return $this->respondSuccess();
    }

    public function driver_no_show(Request $request){

        $request_detail = RequestModel::where("id", $request->request_id)->first();

        $user = User::find($request_detail->user_id);
        
        if (!empty($user)) {
            $details = [
                "user_name" => $user->name,
                "user_mobile" => $user->mobile,
                "pickup_time" => $request_detail->trip_start_time ? date("d-m-Y H:i:s", strtotime($request_detail->trip_start_time)) : "",
                "pickup_location" => $request_detail->pick_address,
                "destination" => $request_detail->drop_address,
                "flight_number" => $request_detail->flight_number,
                "pickup_poc_instruction" => $request_detail->pickup_poc_instruction,
            ];

            if($user->email != ""){
                \Mail::to($user->email)->send(new \App\Mail\DriverNoShowMailCustomer($details));
            }

            if (!empty($request_detail->driverDetail) && !empty($request_detail->driverDetail->email)) {
                $details["partner_name"] = $request_detail->driverDetail->name;
                \Mail::to($request_detail->driverDetail->email)->send(new \App\Mail\DriverNoShowMailOwnerDriver($details));
            }

            if (!empty($request_detail->ownerDetail) && !empty($request_detail->ownerDetail->email)) {
                $details["partner_name"] = $request_detail->ownerDetail->owner_name;
                \Mail::to($request_detail->ownerDetail->email)->send(new \App\Mail\DriverNoShowMailOwnerDriver($details));
            }

            
            $request_detail_data = ["driver_no_show" => 1];
          

            if ($request->hasFile('driver_no_show_file')) {
                $imageNames = [];

                foreach ($request->file('driver_no_show_file') as $file) {
                    $imageName = rand(1111, 9999) . time() . '.' . $file->extension();
                    $file->move(public_path('images'), $imageName);
                    $imageNames[] = $imageName;
                }

                $request_detail_data['driver_no_show_file'] = implode(',', $imageNames);
            }

            $request_detail->update($request_detail_data);
        }
        
        return $this->respondSuccess();
    }
}
