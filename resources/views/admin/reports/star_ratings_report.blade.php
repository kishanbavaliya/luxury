@extends('admin.layouts.app')

@section('title', 'Star Ratings Report')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border d-flex justify-content-between">
                    <h3 class="box-title">Star Ratings Overview</h3>
                    <form method="get" action="{{ route('starRatingReport') }}">
                        <select name="filter" class="form-control" onchange="this.form.submit()">
                            <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>All Time</option>
                            <option value="7days" {{ $filter == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="30days" {{ $filter == '30days' ? 'selected' : '' }}>Last 30 Days</option>
                        </select>
                    </form>
                </div>

                <div class="box-body">

                    {{-- Average Rating --}}
                    <div class="alert alert-success">
                        <strong>Average Rating ({{ ucfirst($filter) }}):</strong> {{ number_format($averageRating, 2) }}
                    </div>

                    {{-- Ratings per Driver --}}
                    <h4>Ratings per Driver</h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Driver Name</th>
                                <th>Average Rating</th>
                                <th>Total Reviews</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($driverRatings as $driver)
                                <tr>
                                    <td>{{ $driver->driver_name ?? 'N/A' }}</td>
                                    <td>{{ number_format($driver->avg_rating, 2) }}</td>
                                    <td>{{ $driver->total_reviews }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">No rating data found for the selected period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end">
                        {{ $driverRatings->appends(['filter' => $filter])->links() }}
                    </div>

                    {{-- Latest 5 Reviews --}}
                    <h4 class="mt-5">Latest 5 Reviews</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Driver</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Reviewed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($latestReviews as $review)
                                <tr>
                                    <td>{{ $review->driver_name ?? 'N/A' }}</td>
                                    <td>{{ $review->rating }}</td>
                                    <td>{{ $review->comment ?? 'No comment' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($review->created_at)->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">No recent reviews available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
