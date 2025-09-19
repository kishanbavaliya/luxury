@extends('email.layout')

@section('content')
 <body>
    <section class="contact-main-div">
        <div class="contact-us-content">
            <h2 style="text-align: center;font-weight: 400;">Cancellation of Ride Booking</h2>
            <p>Dear <strong>{{ $data['partner_name'] }}</strong>,</p>

            <p>We regret to inform you that the customer has cancelled the following ride:</p>

            <h2>Ride Details:</h2>
            <ul>
                <li><strong>Pickup Time:</strong> {{ $data['pickup_time'] }}</li>
                <li><strong>Pickup Location:</strong> {{ $data['pickup_location'] }}</li>
                <li><strong>Destination:</strong> {{ $data['destination'] }}</li>
                <li><strong>Vehicle Class:</strong> {{ $data['vehicle_class'] }}</li>
                <li><strong>Booking Number:</strong> {{ $data['booking_number'] }}</li>
            </ul>

            <p>As the cancellation occurred less than 3 hours before the ride, no payment will be made for this booking.</p>

            <p>Please check the app for more available rides. We look forward to working with you on a future booking.</p>

            <p>Thank you for your understanding.</p>
            <p><span>Best regards,</span></p>
            <p><strong>Luxury Limoexpress</strong></p>
        </div>
    </section>
</body>
@endsection