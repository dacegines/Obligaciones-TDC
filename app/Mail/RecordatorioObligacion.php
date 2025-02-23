<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecordatorioObligacion extends Mailable
{
    use Queueable, SerializesModels;

    public $requisito;
    public $tipo_notificacion;
    public $dias_restantes;
    public $color; // <-- Agregar esta variable

    public function __construct($requisito, $tipo_notificacion, $dias_restantes, $color)
    {
        $this->requisito = $requisito;
        $this->tipo_notificacion = $tipo_notificacion;
        $this->dias_restantes = $dias_restantes;
        $this->color = $color; // <-- Asignar el valor de color
    }

    public function build()
    {
        return $this->subject('Recordatorio de fecha límite para cumplimiento de obligación')
            ->markdown('emails.recordatorio')
            ->with([
                'requisito' => $this->requisito,
                'tipo_notificacion' => $this->tipo_notificacion,
                'dias_restantes' => $this->dias_restantes,
                'color' => $this->color, // <-- Pasar la variable a la vista
            ]);
    }
}
