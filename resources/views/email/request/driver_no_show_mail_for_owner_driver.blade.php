@extends('email.layout')

@section('content')
<body>
    <section class="contact-main-div">
        <div class="contact-us-content">
            <h2 style="text-align: center;font-weight: 400;">No-Show Confirmation and Next Steps</h2>
            
            <p>Dear <strong>{{ $data['partner_name'] }}</strong>,</p>            
            <p>Thank you for your collaboration and continued support in completing our rides.</p>
            
            <h2>Ride Details:</h2>
            <ul>
                <li><strong>Passenger:</strong> {{ $data['user_name'] }}</li>
                <li><strong>Passenger’s phone number:</strong> {{ $data['user_mobile'] }}</li>
                <li><strong>Pickup address:</strong> {{ $data['pickup_location'] }}</li>
                <li><strong>Destination address:</strong> {{ $data['destination'] }}</li>
                <li><strong>Flight number (if applicable):</strong> {{ $data['flight_number'] ?? 'N/A' }}</li>
                <li><strong>Pickup time:</strong> {{ $data['pickup_time'] }}</li>
                <li><strong>Special instructions:</strong> {{ $data['pickup_poc_instruction'] }}</li>
            </ul>
            
            <p>We have received your no-show proof for this ride and are currently reviewing it carefully. Once all required documents have been uploaded and verified, the payment for this ride will be processed in full.</p>
            
            <p>Please continue to check the app for further ride opportunities.</p>
            
            <p>Should you have any questions, feel free to contact us at any time.</p>
            
            <p>Best regards,</p>
            <p><strong>Luxury Limoexpress</strong></p>
        </div>
    </section>
</body>
@endsection
