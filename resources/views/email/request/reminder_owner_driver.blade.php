@extends('email.layout')

@section('content')
 <body>
    <section class="contact-main-div">
        <div class="contact-us-content">
            <h2 style="text-align: center;font-weight: 400;">60-90 Min Reminder – Please Press the Button for Upcoming Ride</h2>
            <p>Dear <strong>{{ $data['patner_name'] }}</strong>,</p>

            <p>This is a reminder that the following ride will begin shortly. Please make sure to press the 60-90 Min Reminder button to prepare and confirm the ride in a timely manner.</p>

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

            <p>Please ensure that you are ready to complete the ride on time. Press the 60-90 Min Reminder button to confirm everything is set.</p>

            <p>Feel free to reach out to us if you have any questions.</p>
            
            <p>Thank you for your cooperation, and safe travels!</p>

            <p><span>Best regards,</span></p>
            <p><strong>Luxury Limoexpress</strong></p>
        </div>
    </section>
</body>
@endsection