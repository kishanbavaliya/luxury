@extends('email.layout')

@section('content')
<body>
    <section class="contact-main-div">
        <div class="contact-us-content">
            <h2 style="text-align: center; font-weight: 400;">Confirmation of Your Ride Booking</h2>
            <p>Dear <strong>{{ $data['customer_name'] }}</strong>,</p>

            <p>We are pleased to confirm your booking with us. Below are the details of your reservation:</p>

            <h3>Original Ride Details:</h3>
            <ul>
                <li><strong>Pickup Time:</strong> {{ $data['original_pickup_time'] }}</li>
                <li><strong>Pickup Location:</strong> {{ $data['pickup_location'] }}</li>
                <li><strong>Destination:</strong> {{ $data['destination'] }}</li>
                <li><strong>Vehicle Class:</strong> {{ $data['vehicle_class'] }}</li>
                <li><strong>Booking Number:</strong> {{ $data['booking_number'] }}</li>
                <li><strong>Driver:</strong> {{ $data['partner_name'] }}</li>
                <li><strong>Price:</strong> {{ $data['price'] }}</li>
            </ul>

            <h3>Updated Ride Details:</h3>
            <ul>
                <li><strong>New Pickup Time:</strong> {{ $data['updated_pickup_time'] }}</li>
            </ul>

            <p>If there are any changes or if you have any further questions, please don’t hesitate to contact us. We are happy to assist you at any time.</p>

            <p>Thank you for choosing us, and we look forward to welcoming you on board.</p>

            <p><span>Best regards,</span></p>
            <p><strong>Luxury Limoexpress</strong></p>
        </div>
    </section>
</body>
@endsection
