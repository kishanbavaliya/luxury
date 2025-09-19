<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Master\MailTemplate;
use App\Models\User;

class CancellationMailForDriverBeforeLessThan3Hour extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {

            $this->data = $data;

     }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = $this->data;

        return $this->subject('Cancellation of Ride Booking')->view('email.request.cancellation_mail_for_driver_before_less_than_3_hour', ['data' => $data]);
    }
}
