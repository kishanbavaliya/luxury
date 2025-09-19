@extends('email.layout')

@section('content')
<body>
    <section class="contact-main-div">
        <div class="contact-us-content">
            <h2 style="text-align: center; font-weight: 400;">Successful Completion of the Ride – Thank You for Your Cooperation!</h2>
            <p>Dear <strong>{{ $data['partner_name'] }}</strong>,</p>

            <p>We would like to thank you for the successful completion of the recent ride.</p>

            <h3>Ride Details:</h3>
            <ul>
                <li><strong>Passenger: </strong> {{ $data['customer_name'] }}</li>
                <li><strong>Passenger’s phone number: </strong> {{ $data['customer_phone_number'] }}</li>
                <li><strong>Pickup Time:</strong> {{ $data['pickup_time'] }}</li>
                <li><strong>Pickup Location:</strong> {{ $data['pickup_location'] }}</li>
                <li><strong>Destination:</strong> {{ $data['destination'] }}</li>
                <li><strong>Special instructions:</strong> {{ $data['pickup_poc_instruction'] }}</li>
            </ul>


            <p>The ride has been successfully completed. Thank you for your efforts!</p>

            <p>Please continue to check the app regularly to be available for upcoming rides. We look forward to continued collaboration with you.</p>

            <p>If you have any questions, feel free to reach out to us.</p>

            <p><span>Best regards,</span></p>
            <p><strong>Luxury Limoexpress</strong></p>
        </div>
    </section>
</body>
@endsection
