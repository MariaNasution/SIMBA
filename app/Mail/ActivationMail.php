<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ActivationMail extends Mailable
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function build()
    {
        return $this->subject('Aktivasi Akun SIS')
            ->view('emails.activation')
            ->with('token', $this->token);
    }
}
