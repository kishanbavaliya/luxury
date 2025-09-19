<!DOCTYPE html>
<html>
<head>
    <title>Business PDF Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h2 {
            margin: 0;
            padding: 0;
        }

        .report-header {
            margin-bottom: 20px;
        }

        .date-range {
            margin-top: 10px;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="report-header">
        <h2>Business Report</h2>
        <p class="date-range"><strong>Date Range:</strong> {{ $from }} to {{ $to }}</p>
    </div>

    <table>
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
</body>
</html>
