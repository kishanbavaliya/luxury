<?php

namespace App\Http\Controllers\Web\Admin;

use App\Base\Filters\Admin\RequestFilter;
use App\Base\Filters\Admin\RequestCancellationFilter;
use App\Base\Filters\Master\CommonMasterFilter;
use App\Base\Libraries\QueryFilter\QueryFilterContract;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\Rides\FetchDriversFromFirebaseHelpers;
use App\Http\Requests\Request\AcceptRejectRequest;
use App\Http\Controllers\Api\V1\BaseController;

use App\Models\Request\Request as RequestRequest;
use App\Models\Request\RequestBill;
use App\Models\Request\RequestCancellationFee;
use App\Models\Admin\CancellationReason;
use Illuminate\Http\Request;
use App\Base\Constants\Setting\Settings;

class NoShowDriverController extends BaseController
{
    use FetchDriversFromFirebaseHelpers;

    public function index()
    {
        $page = trans('pages_names.no_show_driver_rides');
        $main_menu = 'trip-request';
        $sub_menu = 'no-show-driver-rides';

        return view('admin.no-show-driver-rides.index', compact('page', 'main_menu', 'sub_menu'));
    }


    public function getAllRides(QueryFilterContract $queryFilter)
    {
        $app_for = config('app.app_for');


        $query = RequestRequest::where('transport_type' , 'taxi')->where('driver_no_show',1)->where('is_cancelled',0)->where('is_completed',0);
      
        if($app_for=='taxi')
        {
        
        $query = RequestRequest::where('driver_no_show',1)->where('is_cancelled',0)->where('is_completed',0);

        }


        $results = $queryFilter->builder($query)->customFilter(new RequestFilter)->defaultSort('-created_at')->paginate();

        return view('admin.no-show-driver-rides._rides', compact('results'));
    }
public function getDocument($id)
    {
        $result=RequestRequest::find($id);
        $page = trans('pages_names.no_show_driver_rides');
        $main_menu = 'trip-request';
        $sub_menu = 'no-show-driver-rides';

        return view('admin.no-show-driver-rides.document', compact('page', 'main_menu', 'sub_menu','result'));
    }

    public function approveRide($id)
    {
        $result = RequestRequest::find($id);
    
        if (!$result) {
            return redirect()->back()->with('error', 'Ride not found.');
        }
        $request_detail = RequestRequest::where("id", $id)->first();

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
        }
       
    
        // Dynamic pricing values from database
        $base_price = $result->request_eta_amount??0.00;
        $base_distance = 1;
        $price_per_distance = 0.00;
        $price_per_time = 0.00;
        $waiting_charge_per_min = 0.00;
    
        // Calculate billing values
        $distance_price = $result->total_distance * $price_per_distance;
        $time_price = $result->total_time * $price_per_time;
        $waiting_charge = 0.00; // Adjust logic if needed
    
        // Dynamic service tax calculation
        $service_tax_percentage = 0.00;
        $service_tax = ($distance_price + $time_price) * ($service_tax_percentage / 100);
    
        // Dynamic commission calculations
        $admin_commission = $service_tax;
        $admin_commission_with_tax = $admin_commission * 3; // Example multiplier
        $driver_commission = $distance_price - $admin_commission;
        $total_amount = $distance_price + $time_price + $service_tax;
    
        // Mark ride as completed
        $result->is_completed = 1;
        $result->completed_at = now();
        $result->save();
    
        // Insert billing data into request_bills table
        RequestBill::create([
            'request_id' => $result->id,
            'base_price' => $base_price,
            'base_distance' => $base_distance,
            'total_distance' => $result->total_distance,
            'total_time' => $result->total_time,
            'price_per_distance' => $price_per_distance,
            'distance_price' => $distance_price,
            'price_per_time' => $price_per_time,
            'time_price' => $time_price,
            'waiting_charge_per_min' => $waiting_charge_per_min,
            'calculated_waiting_time' => 0,
            'after_trip_start_waiting_time' => 0,
            'before_trip_start_waiting_time' => 0,
            'airport_surge_fee' => 0.00,
            'waiting_charge' => $waiting_charge,
            'cancellation_fee' => 0.00,
            'service_tax' => $service_tax,
            'service_tax_percentage' => $service_tax_percentage,
            'promo_discount' => 0.00,
            'admin_commision' => $admin_commission,
            'admin_commision_from_driver' => $admin_commission,
            'admin_commision_with_tax' => $admin_commission_with_tax,
            'driver_commision' => $driver_commission,
            'total_amount' => $total_amount,
            'requested_currency_code' => $result->requested_currency_code,
            'requested_currency_symbol' => $result->requested_currency_symbol,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        return redirect()->to('/no-show-driver-rides')->with('success', 'Ride approved and billing completed successfully.');
    }
    
    
    
     
}
