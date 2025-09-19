<?php

namespace App\Transformers\Requests;

use App\Transformers\Transformer;
use App\Models\Request\Request as RequestModel;
use Carbon\Carbon;
use Log;

class EarningTripRequestTransformer extends Transformer
{
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected array $availableIncludes = [
        
    ];

    /**
     * Resources that can be included in default.
     *
     * @var array
     */
    protected array $defaultIncludes = [
       
    ];

    /**
     * A Fractal transformer.
     *
     * @param RequestModel $request
     * @return array
     */
    public function transform(RequestModel $request)
    {
        
        $params =  [
            'id' => $request->id,
            'request_number' => $request->request_number,
            'ride_otp'=>$request->ride_otp,
            'is_later' => $request->is_later,
            'user_id' => $request->user_id,
            'service_location_id'=>$request->service_location_id,
            'total_distance'=>number_format($request->total_distance,2),
            'unit' => $request->unit==2?'MILES':'KM',
            'trip_commission'=>$request->requestBill->driver_commision,


        ];

        $timezone = auth()->user()->timezone?:env('SYSTEM_DEFAULT_TIMEZONE');

        $params['trip_start_time'] = Carbon::parse($request->trip_start_time,'UTC')->setTimezone($timezone)->format('h:i A');
       

        return $params;
    }

   
}
