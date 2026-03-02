<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $entity;
    public $token;
    public $verificationUrl;

    /**
     * Create a new message instance.
     *
     * @param  mixed  $entity  User or PendingUser
     * @param  string $token
     * @return void
     */
    public function __construct($entity, string $token)
    {
        $this->entity = $entity;
        $this->token = $token;
        $this->verificationUrl = rtrim(env('APP_FRONTEND_URL', 'http://localhost:5173'), '/') .
                                 '/verify-email?token=' . $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $name = null;
        if (isset($this->entity->name)) {
            $name = $this->entity->name;
        } elseif (isset($this->entity->nombres) && isset($this->entity->apellidos)) {
            $name = trim($this->entity->nombres . ' ' . $this->entity->apellidos);
        } else {
            $name = $this->entity->email;
        }

        return $this->from(env('MAIL_FROM_ADDRESS', 'noreply@itfipmaps.local'),
                            env('MAIL_FROM_NAME', 'ITFIP Maps'))
                    ->subject('Verificar tu correo - ITFIP Maps')
                    ->view('emails.verification')
                    ->with([
                        'name' => $name,
                        'verificationUrl' => $this->verificationUrl,
                    ]);
    }
}
