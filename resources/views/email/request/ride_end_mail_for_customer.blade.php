@extends('email.layout')

@section('content')
<body>
    <section class="contact-main-div">
        <div class="contact-us-content">
            <h2 style="text-align: center; font-weight: 400;">Thank You for Choosing Our Service – Ride Completed!</h2>
            <p>Dear <strong>{{ $data['customer_name'] }}</strong>,</p>

            <p>Thank you for choosing our service! We are happy to inform you that your recent ride has been successfully completed.</p>

            <h3>Ride Details:</h3>
            <ul>
                <li><strong>Pickup Time:</strong> {{ $data['pickup_time'] }}</li>
                <li><strong>Pickup Location:</strong> {{ $data['pickup_location'] }}</li>
                <li><strong>Destination:</strong> {{ $data['destination'] }}</li>
                <li><strong>Special instructions:</strong> {{ $data['pickup_poc_instruction'] }}</li>
            </ul>


            <p>We would greatly appreciate it if you could take a moment to leave your feedback on the app. Your feedback helps us improve our services and ensure a great experience for all our customers.</p>

            <p>We look forward to serving you on your future rides!</p>

            <p>If you have any questions or need assistance, please don’t hesitate to contact us.</p>

            <p><span>Best regards,</span></p>
            <p><strong>Luxury Limoexpress</strong></p>
        </div>
    </section>
</body>
@endsection
