@extends('admin.layouts.app')

@section('title', 'Company Tag Business Report')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border d-flex justify-content-between">
                    <h3 class="box-title">Company Tag Report</h3>
                    <form method="get" action="{{ route('companyTagReport') }}">
                        <select name="filter" class="form-control" onchange="this.form.submit()">
                            <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>All Time</option>
                            <option value="7days" {{ $filter == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="30days" {{ $filter == '30days' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="month" {{ $filter == 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="year" {{ $filter == 'year' ? 'selected' : '' }}>This Year</option>
                        </select>
                    </form>
                </div>

                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Company Tag</th>
                                <th>Total Rides</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rides as $ride)
                                <tr>
                                    <td>{{ $ride->refrence_name ?? 'N/A' }}</td>
                                    <td>{{ $ride->total_rides }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2">No data found for the selected filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end">
                        {{ $rides->appends(['filter' => $filter])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
