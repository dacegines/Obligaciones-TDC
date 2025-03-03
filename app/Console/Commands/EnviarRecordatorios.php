<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Requisito;
use App\Models\Notificacion;
use App\Mail\RecordatorioObligacion;
use Illuminate\Support\Facades\Mail;

class EnviarRecordatorios extends Command
{
    protected $signature = 'correo:diario';
    protected $description = 'Envía recordatorios de obligaciones por correo según la fecha límite';

    public function handle()
    {
        // Definir los tipos de notificación y días restantes
        $notificaciones = [
            'primera_notificacion' => 30,
            'segunda_notificacion' => 58,
            'tercera_notificacion' => 5,
        ];

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
                'requisitos.evidencia',
                'requisitos.periodicidad',
                'requisitos.responsable',
                'requisitos.fecha_limite_cumplimiento',
                'requisitos.origen_obligacion',
                'requisitos.clausula_condicionante_articulo',
                'notificaciones2.email'
            )
            ->get();

        foreach ($requisitos as $requisito) {
            Mail::to($requisito->email)->send(new RecordatorioObligacion($requisito, $tipo_notificacion, $dias_restantes, $this->obtenerColorNotificacion($tipo_notificacion, $dias_restantes)));
            $this->info("Correo enviado a {$requisito->email}");
        }
    }

    /**
     * Función para determinar el color según el tipo de notificación y los días restantes
     */
    private function obtenerColorNotificacion($tipo_notificacion, $dias_restantes)
    {
        if ($tipo_notificacion == 'primera_notificacion' && $dias_restantes == 30) {
            return '#90ee90'; 
        } elseif ($tipo_notificacion == 'segunda_notificacion' && $dias_restantes == 58) {
            return '#ffff99'; 
        } elseif ($tipo_notificacion == 'tercera_notificacion' && $dias_restantes == 5) {
            return '#ffcc99'; 
        } else {
            return '#ced4da';
        }
    }
}
