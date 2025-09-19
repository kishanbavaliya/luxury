@extends('admin.layouts.app')

@section('title', 'Revenue Report')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/vendor_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">

<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">

                <div class="box-header with-border">
                    <h3 class="box-title">Revenue Overview</h3>
                </div>

                {{-- Filter Form --}}
                <form method="get" class="form-horizontal" action="{{ route('revenueReport') }}">
                    <div class="row px-4 pt-4">

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date Filter</label>
                                <select name="date_option" id="date_option" class="form-control">
                                    <option value="date" {{ request('date_option', $dateOption) == 'date' ? 'selected' : '' }}>Custom Date</option>
                                    <option value="today" {{ request('date_option') == 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="week" {{ request('date_option') == 'week' ? 'selected' : '' }}>This Week</option>
                                    <option value="month" {{ request('date_option') == 'month' ? 'selected' : '' }}>This Month</option>
                                    <option value="year" {{ request('date_option') == 'year' ? 'selected' : '' }}>This Year</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 dateDiv">
                            <div class="form-group">
                                <label>From Date</label>
                                <input type="text" class="form-control datepicker" name="from" value="{{ $from }}">
                            </div>
                        </div>

                        <div class="col-md-3 dateDiv">
                            <div class="form-group">
                                <label>To Date</label>
                                <input type="text" class="form-control datepicker" name="to" value="{{ $to }}">
                            </div>
                        </div>

                        <div class="col-md-3 mt-4">
                            <button type="submit" class="btn btn-primary mt-2">Filter</button>
                        </div>

                    </div>
                </form>

                {{-- Revenue Summary --}}
                <div class="box-body">
                    <h5 class="mt-4"><strong>Summary:</strong></h5>
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Total Revenue (All Partners)</th>
                                <td>₹{{ number_format($totalRevenue, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Your Revenue (Luxury Limoexpress)</th>
                                <td>₹{{ number_format($ownRevenue, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Revenue Per Partner --}}
                    <h5 class="mt-4"><strong>Revenue by Partner:</strong></h5>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Partner Name</th>
                                <th>Number of Trips</th>
                                <th>Revenue (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($partnerRevenues as $partner)
                                <tr>
                                    <td>{{ $partner['name'] }}</td>
                                    <td>{{ $partner['trip_count'] }}</td>
                                    <td>₹{{ number_format($partner['revenue'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">No data available for the selected date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-end">
                        {{ $partnerRevenues->links('pagination::bootstrap-4') }}
                    </div>

                </div>

            </div>
        </div>
    </div>
</section>

{{-- JS Dependencies --}}
<script src="{{ asset('assets/vendor_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>

<script>
    $('.datepicker').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        endDate: 'today'
    });

    function toggleDateFields() {
        if ($('#date_option').val() === 'date') {
            $('.dateDiv').show();
        } else {
            $('.dateDiv').hide();
        }
    }

    $(document).on('change', '#date_option', toggleDateFields);
    $(document).ready(function () {
        toggleDateFields();
    });
</script>
@endsection
