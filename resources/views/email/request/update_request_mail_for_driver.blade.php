@extends('email.layout')

@section('content')
<body>
    <section class="contact-main-div">
        <div class="contact-us-content">
            <h2 style="text-align: center;font-weight: 400;">Change of Ride Details - Confirmation Required</h2>
            <p>Dear <strong>{{ $data['partner_name'] }}</strong>,</p>

            <p>We would like to inform you that the passenger has made changes to the booked ride. Here are the details:</p>

            <h3>Original Ride Details:</h3>
            <ul>
                <li><strong>Pickup Time:</strong> {{ $data['original_pickup_time'] }}</li>
                <li><strong>Pickup Location:</strong> {{ $data['pickup_location'] }}</li>
                <li><strong>Destination:</strong> {{ $data['destination'] }}</li>
                <li><strong>Vehicle Class:</strong> {{ $data['vehicle_class'] }}</li>
                <li><strong>Booking Number:</strong> {{ $data['booking_number'] }}</li>
            </ul>

            <h3>Updated Ride Details:</h3>
            <ul>
                <li><strong>New Pickup Time:</strong> {{ $data['updated_pickup_time'] }}</li>
            </ul>

            <p>Would you still like to proceed with the ride?</p>
            <p>If yes, please confirm the ride in the app.
            If not, please return the ride in the app so we can assign it to another partner.</p>

            <p>Thank you for your cooperation. Please check your app regularly for more ride opportunities.</p>

            <p><span>Best regards,</span></p>
            <p><strong>Luxury Limoexpress</strong></p>
        </div>
    </section>
</body>
@endsection
