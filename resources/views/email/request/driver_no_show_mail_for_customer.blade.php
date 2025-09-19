@extends('email.layout')

@section('content')
<body>
    <section class="contact-main-div">
        <div class="contact-us-content">
            <h2 style="text-align: center;font-weight: 400;">No-Show Confirmation and Ride Cancellation</h2>
            
            <p>Dear <strong>{{ $data['user_name'] }}</strong>,</p>
            
            <p>Thank you for choosing our service. We regret to inform you that your scheduled ride could not be completed as the driver had to leave the pickup location due to the waiting time exceeding our free wait policy.</p>
            
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
            
            <p>Please note that our free waiting time policy allows for:</p>
            <ul>
                <li>60 minutes of free waiting time at airports and train stations</li>
                <li>15 minutes at hotels or other pickup addresses</li>
            </ul>
            
            <p>Unfortunately, after this period, the driver was unable to wait any longer and had to depart. We understand this may be disappointing, and we sincerely apologize for any inconvenience this may cause. However, these are the terms of our service.</p>
            
            <p>We hope to welcome you again soon and would be happy to assist with any future bookings.</p>
            
            <p>Thank you for your understanding.</p>
            
            <p>Best regards,</p>
            <p><strong>Luxury Limoexpress</strong></p>
        </div>
    </section>
</body>
@endsection
