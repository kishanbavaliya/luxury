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
use App\Helpers\Rides\FetchDriversFromFirebaseHelpers;

class SendRequestToAllOwnerDriver extends Command
{
    use FetchDriversFromFirebaseHelpers;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send_request:toallownerdriver';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change the request to all owner and driver respond';

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

        Log::info("succccccccessss");

        $request_lists = Request::where('is_driver_started', 0)
        ->where('is_driver_arrived', 0)
        ->where('is_trip_start', 0)
        ->where('is_completed', 0)
        ->where('is_cancelled', 0)
        ->where('is_paid', 0)
        ->where('user_rated', 0)
        ->where('driver_rated', 0)
        ->whereNull('driver_id')
        ->whereNull('send_to_owner_driver')
        ->where('created_at', '<', Carbon::now()->subMinutes(1))
        ->get();

        foreach($request_lists as $request_list){
            $this->fetchDriversFromFirebase($request_list, 2);
            $request_list->send_to_owner_driver = 1;
            $request_list->save();
        }
    }
}
