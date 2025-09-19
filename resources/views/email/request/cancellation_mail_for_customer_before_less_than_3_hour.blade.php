@extends('email.layout')

@section('content')
<body>
    <section class="contact-main-div">
        <div class="contact-us-content">
            <h2 style="text-align: center; font-weight: 400;">Ride Cancellation Notification</h2>
            <p>Dear <strong>{{ $data['customer_name'] }}</strong>,</p>

            <p>We regret to inform you that your following ride has been cancelled as requested:</p>

            <h2>Ride Details:</h2>
            <ul>
                <li><strong>Pickup Time:</strong> {{ $data['pickup_time'] }}</li>
                <li><strong>Pickup Location:</strong> {{ $data['pickup_location'] }}</li>
                <li><strong>Destination:</strong> {{ $data['destination'] }}</li>
                <li><strong>Vehicle Class:</strong> {{ $data['vehicle_class'] }}</li>
                <li><strong>Booking Number:</strong> {{ $data['booking_number'] }}</li>
            </ul>

            <p>Since the cancellation was made less than 3 hours before the scheduled pickup time, the full amount of <strong>{{ $data['amount'] }}</strong> will still be charged.</p>

            <p>We hope to assist you with a future booking and appreciate your understanding.</p>

            <p><span>Best regards,</span></p>
            <p><strong>Luxury Limoexpress</strong></p>
        </div>
    </section>
</body>
@endsection
