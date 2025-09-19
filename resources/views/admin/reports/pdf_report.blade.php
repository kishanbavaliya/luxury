@extends('admin.layouts.app')

@section('title', 'Automatic PDF Report')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border d-flex justify-content-between">
                    <h3 class="box-title">Business Report</h3>
                    <form method="get" action="{{ route('pdfReport') }}" class="form-inline">
                        <input type="date" name="from" value="{{ $from }}" class="form-control mx-2" required>
                        <input type="date" name="to" value="{{ $to }}" class="form-control mx-2" required>
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        <a href="{{ route('downloadPDF', ['from' => $from, 'to' => $to]) }}" class="btn btn-sm btn-success ml-2">Download PDF</a>
                    </form>
                </div>

                <div class="box-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Driver</th>
                                <th>Completed Rides</th>
                                <th>Total Revenue</th>
                                <th>Admin Commission</th>
                                <th>Driver Earning</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData as $row)
                                <tr>
                                    <td>{{ $driverNames[$row->driver_id] ?? 'N/A' }}</td>
                                    <td>{{ $row->completed_rides }}</td>
                                    <td>${{ number_format($row->revenue, 2) }}</td>
                                    <td>${{ number_format($row->admin_commission, 2) }}</td>
                                    <td>${{ number_format($row->driver_earning, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $reportData->appends(['from' => $from, 'to' => $to])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
