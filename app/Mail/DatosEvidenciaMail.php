<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DatosEvidenciaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre;
    public $evidencia;
    public $periodicidad;
    public $responsable;
    public $fecha_limite_cumplimiento;
    public $origen_obligacion;
    public $clausula_condicionante_articulo;
    public $rutaArchivo; 
    public $usuario; 
    public $puesto; 

    public function __construct(
        $nombre,
        $evidencia,
        $periodicidad,
        $responsable,
        $fecha_limite_cumplimiento,
        $origen_obligacion,
        $clausula_condicionante_articulo,
        $rutaArchivo = null,
        $usuario = null,
        $puesto = null 
    ) {
        $this->nombre = $nombre;
        $this->evidencia = $evidencia;
        $this->periodicidad = $periodicidad;
        $this->responsable = $responsable;
        $this->fecha_limite_cumplimiento = $fecha_limite_cumplimiento;
        $this->origen_obligacion = $origen_obligacion;
        $this->clausula_condicionante_articulo = $clausula_condicionante_articulo;
        $this->rutaArchivo = $rutaArchivo;
        $this->usuario = $usuario; 
        $this->puesto = $puesto; 
    }

    public function build()
    {
        $correo = $this->view('emails.datos_evidencia')
            ->subject('Nueva evidencia agregada.')
            ->from('alertas.aws.supervia@supervia.mx')
            ->priority(1)
            ->with([
                'nombre' => $this->nombre,
                'evidencia' => $this->evidencia,
                'periodicidad' => $this->periodicidad,
                'responsable' => $this->responsable,
                'fecha_limite_cumplimiento' => $this->fecha_limite_cumplimiento,
                'origen_obligacion' => $this->origen_obligacion,
                'clausula_condicionante_articulo' => $this->clausula_condicionante_articulo,
                'usuario' => $this->usuario, 
                'puesto' => $this->puesto, 
            ]);

        // Adjuntar el archivo si existe
        if ($this->rutaArchivo && file_exists($this->rutaArchivo)) {
            $fileName = basename($this->rutaArchivo);

            // Extraer todo después del primer "_"
            $fileNameTrimmed = substr($fileName, strpos($fileName, '_') + 1);

            $correo->attach($this->rutaArchivo, [
                'as' => $fileNameTrimmed, // Nombre ajustado del archivo
                'mime' => mime_content_type($this->rutaArchivo),
            ]);
        }

        return $correo;
    }
}