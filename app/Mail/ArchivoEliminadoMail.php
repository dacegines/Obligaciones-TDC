<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArchivoEliminadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $requisitoNombre;
    public $evidencia;
    public $responsable;
    public $fechaLimite;
    public $usuario;
    public $puesto;
    public $rutaArchivo;
    public $periodicidad;
    public $origenObligacion;
    public $clausulaCondicionanteArticulo;
    public $numeroEvidencia; // Nueva propiedad

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        $requisitoNombre,
        $evidencia,
        $periodicidad,
        $responsable,
        $fechaLimite,
        $origenObligacion,
        $clausulaCondicionanteArticulo,
        $usuario,
        $puesto,
        $rutaArchivo,
        $numeroEvidencia = null // Nuevo parámetro
    ) {
        $this->requisitoNombre = $requisitoNombre;
        $this->evidencia = $evidencia;
        $this->periodicidad = $periodicidad;
        $this->responsable = $responsable;
        $this->fechaLimite = $fechaLimite;
        $this->origenObligacion = $origenObligacion;
        $this->clausulaCondicionanteArticulo = $clausulaCondicionanteArticulo;
        $this->usuario = $usuario;
        $this->puesto = $puesto;
        $this->rutaArchivo = $rutaArchivo;
        $this->numeroEvidencia = $numeroEvidencia; // Asignar el nuevo parámetro
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $correo = $this->subject('Eliminación de archivo adjunto')
            ->from('alertas.aws.supervia@supervia.mx')
            ->view('emails.archivo_eliminado')
            ->priority(1)
            ->with([
                'nombre' => $this->requisitoNombre,
                'evidencia' => $this->evidencia,
                'periodicidad' => $this->periodicidad,
                'responsable' => $this->responsable,
                'fecha_limite_cumplimiento' => $this->fechaLimite,
                'origen_obligacion' => $this->origenObligacion,
                'clausula_condicionante_articulo' => $this->clausulaCondicionanteArticulo,
                'usuario' => $this->usuario,
                'puesto' => $this->puesto,
                'numeroEvidencia' => $this->numeroEvidencia, // Pasar el número de evidencia a la vista
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