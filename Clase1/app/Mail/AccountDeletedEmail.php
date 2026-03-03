<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountDeletedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $userEmail;

    public function __construct($userName, $userEmail)
    {
        $this->userName = $userName;
        $this->userEmail = $userEmail;
    }

    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS', 'noreply@itfipmaps.local'),
                            env('MAIL_FROM_NAME', 'ITFIP Maps'))
                    ->subject('Tu cuenta ha sido eliminada - ITFIP Maps')
                    ->view('emails.account-deleted')
                    ->with([
                        'userName' => $this->userName,
                        'userEmail' => $this->userEmail,
                    ]);
    }
}
