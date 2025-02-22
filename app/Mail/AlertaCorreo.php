<?php

// AlertaCorreo.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AlertaCorreo extends Mailable
{
    use Queueable, SerializesModels;

    public $diasRestantes;
    public $colorFondo;

    /**
     * Crear una nueva instancia del mensaje.
     *
     * @param int $diasRestantes
     * @param string $colorFondo
     */
    public function __construct($diasRestantes, $colorFondo)
    {
        $this->diasRestantes = $diasRestantes;
        $this->colorFondo = $colorFondo;
    }

    /**
     * Construir el mensaje.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Alerta de Cumplimiento")
                    ->from('alertas.aws.supervia@supervia.mx')
                    ->priority(1)
                    ->view('emails.alerta')
                    ->with([
                        'diasRestantes' => $this->diasRestantes,
                        'colorFondo' => $this->colorFondo,
                    ]);
    }
}
