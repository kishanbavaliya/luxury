@extends('email.layout')

@section('content')
 <body>
    <section class="contact-main-div">
        <div class="contact-us-content">
            <h2 style="text-align: center;font-weight: 400;">Ride Accepted – Important Instructions and Details</h2>
            <p>Dear <strong>{{ $data['partner_name'] }}</strong>,</p>

            <p>Congratulations on accepting the ride! You are now responsible for transporting the passenger as per the following details:</p>

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

            <h3>Important Instructions:</h3>
            <ul>
                <li>60-90 minutes reminder: Please remember to press the reminder button in the app 60-90 minutes before the scheduled pickup time to confirm your arrival.</li>
                <li>Arrived button: Ensure you press the “Arrived” button in the app as soon as you reach the pickup location.</li>
                <li>Flight status: Please check the flight status independently in addition to the information provided by the app.</li>
            </ul>

            <h3>Dress Code:</h3>
            <ul>
                <li>Standard-Klasse: Wear appropriate, neat clothing (no tracksuits).</li>
                <li>Business-Klasse: Black suit, white shirt, black or gold tie, and black patent leather shoes.</li>
                <li>First-Class-Klasse: Same as Business-Klasse – black suit, white shirt, black or gold tie, and black patent leather shoes.</li>
            </ul>

            <h3>Pickup Procedure:</h3>
            <ul>
                <li>Name sign: Greet the passenger with a tablet or printed name sign. The name sign PDF is attached for your convenience.</li>
                <li>Luggage assistance: Be prepared to assist the passenger with loading their luggage.</li>
                <li>Opening the door: Hold the door open for the passenger before they enter the vehicle, and load the luggage afterward.</li>
                <li>Arrival procedure: Upon arrival at the destination, open the door for the passenger and assist them with their luggage, if necessary, up to their doorstep.</li>
            </ul>

            <p>Please follow these instructions carefully to ensure a smooth and professional experience. If you have any questions, feel free to reach out.</p>

            <p><span>Best regards,</span></p>
            <p><strong>Luxury Limoexpress</strong></p>
        </div>
    </section>
</body>
@endsection