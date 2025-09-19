@extends('email.layout')

@section('content')
 <body>
 <section class="contact-main-div">
    <div class="contact-us-content">
        <h2 style="text-align: center;font-weight: 400;">Confirmation of Your Ride Booking</h2>
        <p>Dear <strong>{{ $data['user_name'] }}</strong>,</p>

        <p>We are pleased to confirm your booking with us. Below are the details of your reservation:</p>

        <h2>Reservation Details:</h2>
        <ul>
            <li><strong>Departure Date & Time:</strong> {{ $data['pickup_time'] }}</li>
            <li><strong>Departure Location:</strong> {{ $data['pickup_location'] }}</li>
            <li><strong>Destination:</strong> {{ $data['destination'] }}</li>
            @if($data['vehicle_type'] != "")
                <li><strong>Vehicle Type:</strong> {{ $data['vehicle_type'] }}</li>
            @endif
            <li><strong>Driver:</strong> {{ $data['partner_name'] }}</li>
            <li><strong>Price:</strong> {{ $data['requested_currency_symbol'] }} {{ $data['request_eta_amount'] }}</li>
        </ul>

        <p>If there are any changes or if you have any further questions, please don’t hesitate to contact us. We are happy to assist you at any time.</p>

        <p>Thank you for choosing us, and we look forward to welcoming you on board.</p>

        <p><span>Best regards,</span></p>
        <p><strong>Luxury Limoexpress</strong></p>
    </div>
</section>
</body>
@endsection