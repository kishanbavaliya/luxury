<?php

namespace App\Http\Controllers\Web\Admin;

use App\Models\User;
use App\Exports\UsersExport;
use App\Models\Admin\Driver;
use Illuminate\Http\Request;
use App\Exports\DriverExport;
use App\Exports\TravelExport;
use App\Base\Constants\Auth\Role;
use App\Models\Admin\VehicleType;
use Illuminate\Support\Facades\DB;
use App\Exports\DriverDutiesExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Base\Filters\Admin\UserFilter;
use App\Base\Filters\Admin\RequestFilterChecking;
use App\Base\Filters\Admin\DriverFilter;
use App\Base\Filters\Admin\RequestFilter;
use App\Base\Constants\Masters\DateOptions;
use App\Base\Filters\Master\CommonMasterFilter;
use App\Models\Request\Request as RequestRequest;
use App\Base\Libraries\QueryFilter\QueryFilterContract;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Admin\Owner;
use App\Exports\OwnerExport;
use App\Base\Filters\Admin\OwnerFilter;
use Config;
use PDF;

class ReportController extends Controller
{
    protected $format = ['xlsx','xls','csv'];

    public function userReport()
    {
        $page = trans('pages_names.user_report');

        $main_menu = 'reports';
        $sub_menu = 'user_report';
        $formats = $this->format;

        return view('admin.reports.user_report', compact('page', 'main_menu', 'sub_menu', 'formats'));
    }

    public function driverReport()
    {
        $page = trans('pages_names.driver_report');

        $main_menu = 'reports';
        $sub_menu = 'driver_report';
        $formats = $this->format;
        $vehicletype = VehicleType::active()->get();
        $app_for = config('app.app_for');

        return view('admin.reports.driver_report', compact('page', 'main_menu', 'sub_menu', 'app_for', 'formats', 'vehicletype'));
    }
    public function ownerReport()
    {
        $page = trans('pages_names.owner_report');

        $main_menu = 'reports';
        $sub_menu = 'owner_report';
        $formats = $this->format;
        $vehicletype = VehicleType::active()->get();

        return view('admin.reports.owner_report', compact('page', 'main_menu', 'sub_menu', 'formats', 'vehicletype'));
    }

    public function driverDutiesReport()
    {
        $page = trans('pages_names.driver_duties_report');
        $main_menu = 'reports';
        $sub_menu = 'driver_duties_report';
        $formats = $this->format;
        $drivers = Driver::get();

        return view('admin.reports.driver_duties', compact('page', 'main_menu', 'sub_menu', 'formats', 'drivers'));
    }

    public function travelReport()
    {
        $page = trans('pages_names.finance_report');

        $main_menu = 'reports';
        $sub_menu = 'travel_report';
        $formats = $this->format;
        $vehicletype = VehicleType::active()->get();

        return view('admin.reports.travel_report', compact('page', 'main_menu', 'sub_menu', 'formats', 'vehicletype'));
    }

    public function revenueReport(Request $request)
    {
        $page = trans('pages_names.revenue_report');
        $main_menu = 'reports';
        $sub_menu = 'revenue_report';

        $dateOption = $request->input('date_option', 'month');
        $from = $request->input('from');
        $to = $request->input('to');

        switch ($dateOption) {
            case 'today':
                $from = Carbon::today()->toDateString();
                $to = Carbon::today()->toDateString();
                break;
            case 'week':
                $from = Carbon::now()->startOfWeek()->toDateString();
                $to = Carbon::now()->endOfWeek()->toDateString();
                break;
            case 'month':
                $from = Carbon::now()->startOfMonth()->toDateString();
                $to = Carbon::now()->endOfMonth()->toDateString();
                break;
            case 'year':
                $from = Carbon::now()->startOfYear()->toDateString();
                $to = Carbon::now()->endOfYear()->toDateString();
                break;
            case 'date':
            default:
                $from = $from ?? Carbon::now()->startOfMonth()->toDateString();
                $to = $to ?? Carbon::now()->toDateString();
                break;
        }

        $trips = DB::table('requests')
            ->where('is_completed', 1)
            ->whereBetween('created_at', [$from, $to])
            ->whereNull('deleted_at')
            ->get();

        $totalRevenue = $trips->sum('discounted_total');
        $ownRevenue = $trips->where('company_key', 70)->sum('discounted_total');

        // Map revenue by partner
        $partnerData = $trips->groupBy('company_key')->map(function ($group, $companyKey) {
            $company = DB::table('companies')->where('id', $companyKey)->first();
            return [
                'name' => $company->name ?? 'Unknown',
                'revenue' => $group->sum('discounted_total'),
                'trip_count' => $group->count(),
            ];
        })->values(); // Ensure numeric keys for pagination

        // Manual Pagination
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = $partnerData->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $partnerRevenues = new LengthAwarePaginator($currentItems, $partnerData->count(), $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query()
        ]);

        return view('admin.reports.revenue_report', compact(
            'page', 'main_menu', 'sub_menu',
            'from', 'to', 'totalRevenue', 'ownRevenue',
            'partnerRevenues', 'dateOption'
        ));
    }

    public function commissionReport(Request $request)
    {
        $page = "Commission Overview";
        $main_menu = 'reports';
        $sub_menu = 'commission_overview';

        $filter = $request->filter ?? 'daily';

        switch ($filter) {
            case 'weekly':
                $from = Carbon::now()->startOfWeek();
                break;
            case 'monthly':
                $from = Carbon::now()->startOfMonth();
                break;
            case 'yearly':
                $from = Carbon::now()->startOfYear();
                break;
            default:
                $from = Carbon::today();
        }

        $to = Carbon::now()->endOfDay();

        // Fetch commission entries
        $commissionQuery = DB::table('owner_wallet_histories')
        ->where('remarks', 'Admin Commission For Trip')
        ->whereBetween('created_at', [$from, $to]);

        $totalCommission = $commissionQuery->sum('amount'); 

        $commissions = $commissionQuery
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        $partnerCommissions = DB::table('owner_wallet_histories')
            ->select('user_id', DB::raw('SUM(amount) as commission'))
            ->where('remarks', 'Admin Commission For Trip')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('user_id')
            ->get()
            ->map(function ($item) {
                $owner = DB::table('owners')->where('id', $item->user_id)->first();
                return [
                    'partner_name' => $owner->name ?? 'Unknown',
                    'commission' => $item->commission,
                ];
            });

        return view('admin.reports.commission_report', compact(
            'page', 'main_menu', 'sub_menu', 'commissions', 'totalCommission', 'partnerCommissions', 'filter'
        ));
    }

    public function starRatingReport(Request $request)
    {
        $page = "Star Ratings Overview";
        $main_menu = 'reports';
        $sub_menu = 'star_ratings_report';
        $filter = $request->input('filter', 'all'); // 'all', '7days', '30days'

        // Base query for filters (without joins)
        $baseQuery = DB::table('request_ratings');

        if ($filter === '7days') {
            $baseQuery->where('created_at', '>=', Carbon::now()->subDays(7));
        } elseif ($filter === '30days') {
            $baseQuery->where('created_at', '>=', Carbon::now()->subDays(30));
        }

        // Clone for average
        $averageRating = clone $baseQuery;
        $averageRating = $averageRating->avg('rating');

        // Ratings per driver (paginate)
        $driverRatings = DB::table('request_ratings')
            ->select(
                'request_ratings.driver_id',
                DB::raw('AVG(request_ratings.rating) as avg_rating'),
                DB::raw('COUNT(*) as total_reviews'),
                'drivers.name as driver_name'
            )
            ->leftJoin('drivers', 'request_ratings.driver_id', '=', 'drivers.id')
            ->when($filter === '7days', function ($q) {
                $q->where('request_ratings.created_at', '>=', Carbon::now()->subDays(7));
            })
            ->when($filter === '30days', function ($q) {
                $q->where('request_ratings.created_at', '>=', Carbon::now()->subDays(30));
            })
            ->groupBy('request_ratings.driver_id', 'drivers.name')
            ->paginate(10);

        // Latest 5 reviews (optional)
        $latestReviews = DB::table('request_ratings')
            ->select('request_ratings.*', 'drivers.name as driver_name')
            ->leftJoin('drivers', 'request_ratings.driver_id', '=', 'drivers.id')
            ->orderByDesc('request_ratings.created_at')
            ->limit(5)
            ->get();

        return view('admin.reports.star_ratings_report', compact(
            'page', 'main_menu', 'sub_menu',
            'averageRating',
            'driverRatings',
            'latestReviews',
            'filter'
        ));
    }

    public function completeRidesReport(Request $request)
    {
        $page = "Completed Rides Report";
        $main_menu = 'reports';
        $sub_menu = 'completed_rides';

        $filter = $request->input('filter', 'all');
        $from = null;
        $to = null;

        // Apply date range filter based on selected option
        if ($filter === '7days') {
            $from = Carbon::now()->subDays(7);
        } elseif ($filter === '30days') {
            $from = Carbon::now()->subDays(30);
        } elseif ($filter === 'today') {
            $from = Carbon::now()->startOfDay();
            $to = Carbon::now()->endOfDay();
        }

        // Build base query
        $query = DB::table('requests')
            ->select('company_key', DB::raw('COUNT(*) as total_rides'))
            ->where('is_completed', 1)
            ->whereNull('deleted_at');

        if ($from) {
            $query->whereBetween('created_at', [$from, $to ?? Carbon::now()]);
        }

        // Group by company and paginate
        $completedRides = $query
            ->groupBy('company_key')
            ->orderByDesc('total_rides')
            ->paginate(10);

        // Get the company IDs from paginated items (fix for pluck issue)
        $companyKeys = collect($completedRides->items())->pluck('company_key')->unique()->filter();

        // Fetch company names
        $companies = DB::table('companies')
            ->whereIn('id', $companyKeys)
            ->pluck('name', 'id');

        return view('admin.reports.completed_rides_report', compact(
            'page',
            'main_menu',
            'sub_menu',
            'completedRides',
            'companies',
            'filter'
        ));
    }

    public function companyTagReport(Request $request)
    {
        $page = 'Company Tag Business Report';
        $main_menu = 'reports';
        $sub_menu = 'company_tag_report';

        $filter = $request->input('filter', 'all');
        $query = DB::table('requests')
            ->select('refrence_name', DB::raw('COUNT(*) as total_rides'))
            ->where('is_completed', 1)
            ->whereNull('deleted_at')
            ->groupBy('refrence_name');

        if ($filter == '7days') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7));
        } elseif ($filter == '30days') {
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        } elseif ($filter == 'month') {
            $query->whereMonth('created_at', Carbon::now()->month);
        } elseif ($filter == 'year') {
            $query->whereYear('created_at', Carbon::now()->year);
        }

        $rides = $query->paginate(10);

        return view('admin.reports.company_tag_report', compact('page', 'main_menu', 'sub_menu', 'rides', 'filter'));
    }

    public function pdfReport(Request $request)
    {
        $page = 'Automatic PDF Report';
        $main_menu = 'reports';
        $sub_menu = 'auto_pdf_report';

        $from = $request->from ?? Carbon::now()->startOfMonth()->toDateString();
        $to = $request->to ?? Carbon::now()->toDateString();

        $reportData = DB::table('requests')
            ->select('driver_id',
                DB::raw('COUNT(*) as completed_rides'),
                DB::raw('SUM(discounted_total) as revenue'),
                DB::raw('SUM(discounted_total * 0.2) as admin_commission'),
                DB::raw('SUM(discounted_total * 0.8) as driver_earning')
            )
            ->where('is_completed', 1)
            ->whereNotNull('driver_id')
            ->whereBetween('completed_at', [$from, $to])
            ->groupBy('driver_id')
            ->paginate(10);

        $driverNames = DB::table('drivers')->pluck('name', 'id');

        return view('admin.reports.pdf_report', compact('page', 'main_menu', 'sub_menu','reportData', 'driverNames', 'from', 'to'));
    }

    public function downloadPDF(Request $request)
{
    $from = $request->from ?? Carbon::now()->startOfMonth()->toDateString();
    $to = $request->to ?? Carbon::now()->toDateString();

    $reportData = DB::table('requests')
        ->select('driver_id',
            DB::raw('COUNT(*) as completed_rides'),
            DB::raw('SUM(discounted_total) as revenue'),
            DB::raw('SUM(discounted_total * 0.2) as admin_commission'),
            DB::raw('SUM(discounted_total * 0.8) as driver_earning')
        )
        ->where('is_completed', 1)
        ->whereNotNull('driver_id')
        ->whereBetween('completed_at', [$from, $to])
        ->groupBy('driver_id')
        ->get();

    $driverNames = DB::table('drivers')->pluck('name', 'id');

    // ✅ Suppress PHP 8.2+ deprecation warning temporarily
    set_error_handler(function ($errno, $errstr) {
        if (
            str_contains($errstr, 'file_get_contents()') &&
            str_contains($errstr, 'null')
        ) {
            return true; // ignore this specific warning
        }
        return false; // allow other warnings
    }, E_WARNING);

    $pdf = PDF::loadView('admin.reports.pdf_template', compact('reportData', 'driverNames', 'from', 'to'));

    restore_error_handler(); // ✅ restore normal error handling

    return $pdf->download('business-report.pdf');
}


    public function downloadReport(Request $request, QueryFilterContract $queryFilter)
    {

        $method = "download".$request->model."Report";

        $filename = $this->$method($request, $queryFilter);

        $file = url('storage/'.$filename);

        return $file;
    }

    public function downloadUserReport(Request $request, QueryFilterContract $queryFilter)
    {
        $format = $request->format;

        $query = User::companyKey()->belongsToRole(Role::USER);

        $data = $queryFilter->builder($query)->customFilter(new UserFilter)->defaultSort('-date')->get();

        $filename = "$request->model Report-".date('ymdis').'.'.$format;

        Excel::store(new UsersExport($data), $filename, 'local');

        return $filename;
    }

    public function downloadDriverReport(Request $request, QueryFilterContract $queryFilter)
    {
        $format = $request->format;

        $query = Driver::query();

        $data = $queryFilter->builder($query)->customFilter(new DriverFilter)->defaultSort('-date')->get();

        $filename = "$request->model Report-".date('ymdis').'.'.$format;

        Excel::store(new DriverExport($data), $filename, 'local');

        return $filename;
    }

    /**
    * Download Driver Duties Report
    *
    */
    public function downloadDriverDutiesReport(Request $request)
    {
        $format = $request->format;
        $date_option = $request->date_option;
        $current_date = Carbon::now();
        $driver = $request->driver;

        if ($date_option == DateOptions::TODAY) {
            $date_array = [$current_date->format("Y-m-d"),$current_date->format("Y-m-d"),$driver];
        } elseif ($date_option == DateOptions::YESTERDAY) {
            $yesterday_date = Carbon::yesterday()->format('Y-m-d');
            $date_array = [$yesterday_date,$yesterday_date,$driver];
        } elseif ($date_option == DateOptions::CURRENT_WEEK) {
            $date_array = [$current_date->startOfWeek()->toDateString(),$current_date->endOfWeek()->toDateString(),$driver];
        } elseif ($date_option == DateOptions::LAST_WEEK) {
            $date_array = [$current_date->subWeek()->toDateString(), $current_date->startOfWeek()->toDateString(),$driver];
        } elseif ($date_option == DateOptions::CURRENT_MONTH) {
            $date_array = [$current_date->startOfMonth()->toDateString(), $current_date->endOfMonth()->toDateString(),$driver];
        } elseif ($date_option == DateOptions::PREVIOUS_MONTH) {
            $date_array = [$current_date->startOfMonth()->toDateString(), $current_date->endOfMonth()->toDateString(),$driver];
        } elseif ($date_option == DateOptions::CURRENT_YEAR) {
            $date_array = [$current_date->startOfYear()->toDateString(), $current_date->endOfYear()->toDateString(),$driver];
        } else {
            $date_array = [];
        }

        // $date_array =['2020-11-11','2020-11-20',6];

        $data = DB::select('CALL get_driver_duration_report(?,?,?)', $date_array);
        if (count($data)==1) {
            $data = (object) array();
        }
        $filename = "$request->model Report-".date('ymdis').'.'.$format;

        Excel::store(new DriverDutiesExport($data), $filename, 'local');

        return $filename;

        // dd($record);
    }

    public function downloadTravelReport(Request $request, QueryFilterContract $queryFilter)
    {
        // dd($request);
        $format = $request->format;

        $query = RequestRequest::query();

        $data = $queryFilter->builder($query)->customFilter(new RequestFilter)->defaultSort('created_at')->get();

        $filename = "$request->model Report-".date('ymdis').'.'.$format;

        Excel::store(new TravelExport($data), $filename, 'local');

        return $filename;
    }
    public function downloadOwnerReport(Request $request, QueryFilterContract $queryFilter)
{
    $format = $request->format;

    $query = Owner::query();
    if (env('APP_FOR') == 'demo') {
        $query->whereHas('user', function ($query) use ($request) {
            $query->where('active', $request->status);
        });
    }

    $data = $queryFilter->builder($query)->customFilter(new OwnerFilter)->defaultSort('-date')->get();

    $filename = "$request->model Report-" . date('ymdis') . '.' . $format;

    Excel::store(new OwnerExport($data), $filename, 'local');

    return $filename;
}

}
