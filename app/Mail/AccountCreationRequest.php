<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountCreationRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $position;
    public $email;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $position, $email)
    {
        $this->name = $name;
        $this->position = $position;
        $this->email = $email;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Solicitud de Creación de Cuenta')
                    ->from('alertas.aws.supervia@supervia.mx')
                    ->markdown('emails.account_creation_request')
                    ->priority(1)
                    ->with([
                        'name' => $this->name,
                        'puesto' => $this->position,
                        'email' => $this->email,
                    ]);
    }
}
