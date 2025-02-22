<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $puesto;
    public $email;

    public function __construct($name, $puesto, $email)
    {
        $this->name = $name;
        $this->puesto = $puesto;
        $this->email = $email;
    }

    public function build()
    {
        return $this->subject('Solicitud de Recuperación de Contraseña')
                    ->from('alertas.aws.supervia@supervia.mx')
                    ->view('emails.password-reset-request')
                    ->priority(1)
                    ->with([
                        'name' => $this->name,
                        'puesto' => $this->puesto,
                        'email' => $this->email,
                    ]);
    }
}
