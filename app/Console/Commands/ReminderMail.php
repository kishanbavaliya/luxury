<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Request\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Request\RequestMeta;
use Illuminate\Support\Facades\Log;
use App\Jobs\NoDriverFoundNotifyJob;
use App\Jobs\SendRequestToNextDriversJob;
use Kreait\Firebase\Contract\Database;
use App\Models\User;

class ReminderMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:toownerdrivercustomer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reminder mail for customer, driver and owner.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Database $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $currentDateTime = Carbon::now();
        $ninetyMinutesLater = $currentDateTime->copy()->addMinutes(90);

        $request_details = Request::where("is_driver_arrived", "0")
            ->where("is_later", "1")
            ->where("is_reminder_customer", 0)
            ->whereBetween('trip_start_time', [$currentDateTime, $ninetyMinutesLater])
            ->get();

        if ($request_details->isNotEmpty()) {
            foreach ($request_details as $request_detail) {
                $user = User::find($request_detail->user_id);

                if ($user && !empty($user->email)) {
                    $details = [
                        "user_name" => $user->name,
                        "user_mobile" => $user->mobile,
                        "pickup_time" => $request_detail->trip_start_time ? date("d-m-Y H:i:s", strtotime($request_detail->trip_start_time)) : "",
                        "pickup_location" => $request_detail->pick_address,
                        "destination" => $request_detail->drop_address,
                        "pickup_poc_instruction" => $request_detail->pickup_poc_instruction,
                    ];

                    \Mail::to($user->email)->send(new \App\Mail\ReminderCustomerMail($details));

                    if (!empty($request_detail->driverDetail) && !empty($request_detail->driverDetail->email)) {
                        $details["patner_name"] = $request_detail->driverDetail->name;
                        \Mail::to($request_detail->driverDetail->email)->send(new \App\Mail\ReminderOwnerDriverMail($details));
                    }

                    if (!empty($request_detail->ownerDetail) && !empty($request_detail->ownerDetail->email)) {
                        $details["patner_name"] = $request_detail->ownerDetail->owner_name;
                        \Mail::to($request_detail->ownerDetail->email)->send(new \App\Mail\ReminderOwnerDriverMail($details));
                    }

                    $request_detail->update(["is_reminder_customer" => 1]);
                }
            }
        }

        $datatest = [
            'message' => 'Testing email functionality in ' . url("/")
        ];
    
        try {
            \Mail::send([], [], function ($message) use ($datatest) {
                $message->to('tmtestdemo@gmail.com')
                        ->subject('Your Email Subject')
                        ->setBody($datatest['message'], 'text/html');
            });
        } catch (\Exception $e) {
        }
        
        $this->info('Reminder emails sent successfully.');
        return 0; // Command exit code
    }
}
