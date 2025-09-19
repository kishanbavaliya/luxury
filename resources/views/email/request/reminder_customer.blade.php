@extends('email.layout')

@section('content')
 <body>
    <section class="contact-main-div">
        <div class="contact-us-content">
            <h2 style="text-align: center;font-weight: 400;">Your Ride is Starting Soon – We’re Looking Forward to It!</h2>
            <p>Dear <strong>{{ $data['user_name'] }}</strong>,</p>

            <p>We’re excited to let you know that your ride will begin shortly! Our driver is preparing for your pickup, and we look forward to providing you with a smooth and comfortable journey.</p>

            <h2>Ride Details:</h2>
            <ul>
                <li><strong>Passenger:</strong> {{ $data['user_name'] }}</li>
                <li><strong>Phone number:</strong> {{ $data['user_mobile'] }}</li>
                <li><strong>Pickup address:</strong> {{ $data['pickup_location'] }}</li>
                <li><strong>Destination address:</strong> {{ $data['destination'] }}</li>
                {{-- <li><strong>Flight number:</strong> </li> --}}
                <li><strong>Pickup time:</strong> {{ $data['pickup_time'] }}</li>
                <li><strong>Special instructions:</strong> {{ $data['pickup_poc_instruction'] }}</li>
            </ul>

            <p>If you have any last-minute updates or requests, feel free to contact us. Otherwise, sit back, relax, and we’ll be there soon!</p>

            <p>Thank you for choosing our service. We look forward to serving you!</p>

            <p><span>Best regards,</span></p>
            <p><strong>Luxury Limoexpress</strong></p>
        </div>
    </section>
</body>
@endsection