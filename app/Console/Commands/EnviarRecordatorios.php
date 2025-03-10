<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Requisito;
use App\Models\Notificacion;
use App\Models\Reminder; // Importamos el modelo de la tabla reminders
use App\Mail\RecordatorioObligacion;
use Illuminate\Support\Facades\Mail;

class EnviarRecordatorios extends Command
{
    protected $signature = 'correo:diario';
    protected $description = 'Envía recordatorios de obligaciones por correo según la fecha límite';

    public function handle()
    {
        // Obtener los recordatorios desde la base de datos
        $notificaciones = Reminder::pluck('reminder_days', 'reminder_type')->toArray();

        foreach ($notificaciones as $tipo => $dias) {
            $this->enviarRecordatorios($tipo, $dias);
        }

        $this->info('Correos de recordatorio enviados correctamente.');
    }

    private function enviarRecordatorios($tipo_notificacion, $dias_restantes)
    {
        $requisitos = Requisito::join('notificaciones2', 'requisitos.id_notificaciones', '=', 'notificaciones2.id_notificacion')
            ->where('notificaciones2.tipo_notificacion', $tipo_notificacion)
            ->whereRaw('DATEDIFF(requisitos.fecha_limite_cumplimiento, CURDATE()) = ?', [$dias_restantes])
            ->where('requisitos.approved', 0)
            ->select(
                'requisitos.nombre',
                'requisitos.numero_evidencia',
                'requisitos.evidencia',
                'requisitos.periodicidad',
                'requisitos.responsable',
                'requisitos.fecha_limite_cumplimiento',
                'requisitos.origen_obligacion',
                'requisitos.clausula_condicionante_articulo',
                'notificaciones2.email'
            )
            ->get();
    
        if ($requisitos->isEmpty()) {
            $this->info("No hay registros para la notificación {$tipo_notificacion} con {$dias_restantes} días restantes.");
            return;
        }
    
        foreach ($requisitos as $requisito) {
            $color = $this->obtenerColorNotificacion($tipo_notificacion);
            
            // Mensaje de depuración antes de enviar el correo
            $this->info("Enviando correo a: {$requisito->email} con tipo: {$tipo_notificacion} y días restantes: {$dias_restantes}");
    
            Mail::to($requisito->email)->send(new RecordatorioObligacion($requisito, $tipo_notificacion, $dias_restantes, $color));
            
            $this->info("Correo enviado a {$requisito->email}");
        }
    }
    
    

    /**
     * Función para determinar el color según el tipo de notificación
     */
    private function obtenerColorNotificacion($tipo_notificacion)
    {

        $colores = [
            'primera notificación' => '#90ee90', 
            'segunda notificación' => '#ffff99', 
            'tercera notificación' => '#ffcc99', 
        ];


        return $colores[$tipo_notificacion] ?? '#ced4da';
    }
}
