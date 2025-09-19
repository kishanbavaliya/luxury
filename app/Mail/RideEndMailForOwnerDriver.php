<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Master\MailTemplate;
use App\Models\User;

class RideEndMailForOwnerDriver extends Mailable
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

        return $this->subject('Successful Completion of the Ride – Thank You for Your Cooperation!')->view('email.request.ride_end_mail_for_owner_driver', ['data' => $data]);
    }
}
