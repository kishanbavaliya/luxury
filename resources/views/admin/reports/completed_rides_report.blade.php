@extends('admin.layouts.app')

@section('title', 'Completed Rides Report')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border d-flex justify-content-between">
                    <h3 class="box-title">Completed Rides Overview</h3>
                    <form method="get" action="{{ route('completeRidesReport') }}">
                        <select name="filter" class="form-control" onchange="this.form.submit()">
                            <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>All Time</option>
                            <option value="7days" {{ $filter == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="30days" {{ $filter == '30days' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="today" {{ $filter == 'today' ? 'selected' : '' }}>Today</option>
                        </select>
                    </form>
                </div>

                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Completed Rides</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($completedRides as $row)
                                <tr>
                                    <td>{{ $companies[$row->company_key] ?? 'Luxury Limoexpress' }}</td>
                                    <td>{{ $row->total_rides }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2">No data available for selected filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end">
                        {{ $completedRides->appends(['filter' => $filter])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
