<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;

class ResetPasswordorMail extends Mailable
{
    use Queueable, SerializesModels;

    protected    $token;

    /**
     * Create a new message instance.
     *
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info('Token yang dikirim ke view email:', ['token' => $this->token]);

        return $this->subject('Reset Password Mail')
            ->view('emails.password-reset', [
                'token' => $this->token,
            ]);
    }
}
