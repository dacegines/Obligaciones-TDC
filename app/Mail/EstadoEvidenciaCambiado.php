<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EstadoEvidenciaCambiado extends Mailable
{
    use Queueable, SerializesModels;
    public $nombre;
    public $titulo;
    public $periodicidad;
    public $responsable;
    public $fecha_limite;
    public $origen_obligacion;
    public $clausula;
    public $approved;

    public function __construct($nombre, $titulo, $periodicidad, $responsable, $fecha_limite, $origen_obligacion, $clausula, $approved)
    {
        $this->nombre = $nombre;
        $this->titulo = $titulo;
        $this->periodicidad = $periodicidad;
        $this->responsable = $responsable;
        $this->fecha_limite = $fecha_limite;
        $this->origen_obligacion = $origen_obligacion;
        $this->clausula = $clausula;
        $this->approved = $approved;
    }

    public function build()
    {
        return $this->view('emails.estado_evidencia')
        ->subject('Estado de la obligación actualizado')
        ->from('alertas.aws.supervia@supervia.mx')
        //->replyTo('soporte@tu-dominio.com', 'Soporte Técnico')
        //->cc('manager@tu-dominio.com', 'Manager')
        //->bcc('auditor@tu-dominio.com', 'Auditor')
        ->priority(1)
        /*->attach(storage_path('app/public/reporte.pdf'), [
            'as' => 'ReporteAnual.pdf',
            'mime' => 'application/pdf',
        ])*/
                    ->with([
                        'nombre' => $this->nombre,
                        'titulo' => $this->titulo,
                        'periodicidad' => $this->periodicidad,
                        'responsable' => $this->responsable,
                        'fecha_limite' => $this->fecha_limite,
                        'origen_obligacion' => $this->origen_obligacion,
                        'clausula' => $this->clausula,
                        'approved' => $this->approved,
                    ]);
    }
}

