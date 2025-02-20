<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requisito;
use App\Models\ObligacionUsuario;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Archivo;
use App\Mail\EstadoEvidenciaCambiado;
use App\Models\EvidenceNotification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\DatosEvidenciaMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\AlertaCorreo;


class ObligacionesController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('superUsuario') && !Auth::user()->can('obligaciones de concesión')  && !Auth::user()->can('obligaciones de concesión limitado')) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
    
        try {
            $user = Auth::user();
            if (!$user || !$user->puesto) {
                $this->logWarning('Usuario autenticado sin puesto definido', ['user_id' => $user->id ?? null]);
                return back()->withErrors(['error' => 'No se encontró el puesto del usuario autenticado']);
            }
    
            $currentYear = Carbon::now()->year;
    
            // Obtener los puestos de usuarios asociados con authorization_id = 7
            $puestosExcluidos = DB::table('users')
                ->join('model_has_authorizations', 'users.id', '=', 'model_has_authorizations.model_id')
                ->where('model_has_authorizations.authorization_id', 7)
                ->distinct()
                ->pluck('users.puesto')
                ->toArray();
    
            // Obtener los requisitos con avance y filtro de la tabla pivote
            $requisitos = $this->obtenerRequisitosConAvance($currentYear, $user, $puestosExcluidos);
    
            $this->logInfo('Requisitos cargados correctamente', ['user_id' => $user->id, 'total_requisitos' => $requisitos->count()]);
    
            return view('gestion_cumplimiento.obligaciones.index', compact('requisitos', 'user', 'currentYear', 'puestosExcluidos'));
        } catch (\Exception $e) {
            $this->logError('Error al cargar las obligaciones', ['error' => $e->getMessage(), 'user_id' => $user->id ?? null]);
            return back()->withErrors(['error' => 'Ocurrió un error al cargar las obligaciones.']);
        }
    }
    



    private function obtenerRequisitosConAvance($year, $user, $puestosExcluidos)
    {
        $query = Requisito::with('archivos')->porAno($year);
    
        // Obtener los requisitos que el usuario puede ver según la tabla pivote
        $requisitosIds = ObligacionUsuario::where('user_id', $user->id)
        ->where('view', 1)
        ->pluck('numero_evidencia') 
        ->toArray();
    
        if (!empty($requisitosIds)) {
            $query->whereIn('numero_evidencia', $requisitosIds);
        }
    
        if (!in_array($user->puesto, $puestosExcluidos)) {
            $query->permitirVisualizacion($user);
        }
    
        return $query->get()
            ->filter(fn($requisito) => !empty($requisito->responsable))
            ->each(
                fn($requisito) =>
                $requisito->total_avance = $this->getTotalAvance($requisito->numero_requisito, $user->puesto, $year)
            );
    }
    
    public function getTotalAvance($numero_requisito, $puesto, $year)
    {
        try {
            if (empty($numero_requisito)) {
                $this->logWarning('Número de requisito vacío al intentar calcular el total de avance.');
                return 0;
            }

            // Obtener los puestos de usuarios asociados con authorization_id = 7
            $puestosExcluidos = DB::table('users')
                ->join('model_has_authorizations', 'users.id', '=', 'model_has_authorizations.model_id')
                ->where('model_has_authorizations.authorization_id', 7)
                ->distinct()
                ->pluck('users.puesto')
                ->toArray();

            // Crear una consulta base para filtrar por numero_requisito y año
            $query = Requisito::where('numero_requisito', $numero_requisito)
                ->whereYear('fecha_limite_cumplimiento', $year);

            // Aplicar el filtro de puesto si no está en los puestos excluidos
            if (!in_array($puesto, $puestosExcluidos)) {
                $query->where('responsable', $puesto);
            }

            // Obtener el total de registros aplicando los filtros
            $totalRegistros = $query->count();

            if ($totalRegistros === 0) {
                $this->logWarning('No se encontraron registros para el número de requisito, año y puesto especificado.', [
                    'numero_requisito' => $numero_requisito,
                    'puesto' => $puesto,
                    'year' => $year,
                ]);
                return 0;
            }

            // Calcular el número de registros completados (donde porcentaje es 100)
            $completados = $query->where('porcentaje', 100)->count();

            // Calcular el porcentaje completado
            $total_avance = ($completados * 100.0) / $totalRegistros;

            
            $total_avance = round($total_avance, 2);
            if ($total_avance > 99.95 && $total_avance < 100.05) {
                $total_avance = 100.00;
            }

            return $total_avance;
        } catch (\Exception $e) {
            $this->logError('Error al calcular el total de avance', [
                'numero_requisito' => $numero_requisito,
                'puesto' => $puesto,
                'year' => $year,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }




    public function getDetallesEvidencia(Request $request)
    {
       
        $request->validate([
            'evidencia_id' => 'required|numeric|exists:requisitos,numero_evidencia',
            'year' => 'nullable|numeric|min:2024|max:2040' 
        ]);

        try {
            $evidenciaId = $request->evidencia_id;
            $year = $request->year; 

            
            $detalle = Requisito::where('numero_evidencia', $evidenciaId)->first();

            if ($detalle) {
                // Obtener todas las fechas límite filtradas por año si existe
                $fechasLimite = Requisito::where('numero_evidencia', $evidenciaId)
                    ->when($year, function ($query) use ($year) {
                        return $query->whereYear('fecha_limite_cumplimiento', $year);
                    })
                    ->pluck('fecha_limite_cumplimiento')
                    ->map(function ($fecha) {
                        return Carbon::parse($fecha)->format('d/m/Y');
                    });

                return response()->json([
                    'evidencia' => $detalle->evidencia,
                    'periodicidad' => $detalle->periodicidad,
                    'responsable' => $detalle->responsable,
                    'fechas_limite_cumplimiento' => $fechasLimite,
                    'origen_obligacion' => $detalle->origen_obligacion,
                    'clausula_condicionante_articulo' => $detalle->clausula_condicionante_articulo,
                    'id_notificacion' => $detalle->id_notificacion,
                    'condicion' => $detalle->condicion,
                ]);
            } else {
                return response()->json(['error' => 'No se encontró la evidencia'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ocurrió un error al obtener los detalles de la evidencia'], 500);
        }
    }



    public function obtenerNotificaciones(Request $request)
    {
        
        $request->validate([
            'id_notificaciones' => ['required', 'regex:/^[a-zA-Z]+\d+(\.\d+)?$/'],
        ]);

        try {
            $idNotificacion = $request->id_notificaciones;

            $notificaciones = DB::table('notificaciones')
                ->where('id_notificacion', $idNotificacion)
                ->distinct()
                ->pluck('nombre')
                ->toArray();

            if (empty($notificaciones)) {
                $this->logInfo('No se encontraron notificaciones', ['id_notificacion' => $idNotificacion]);
                return response()->json([], 200);
            }

            $this->logInfo('Notificaciones obtenidas correctamente', ['id_notificacion' => $idNotificacion]);
            return response()->json($notificaciones);
        } catch (\Exception $e) {
            $this->logError('Error al obtener notificaciones', ['id_notificacion' => $idNotificacion, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Ocurrió un error al obtener las notificaciones'], 500);
        }
    }

    public function obtenerTablaNotificaciones(Request $request)
    {
        $idNotificaciones = $request->input('id_notificaciones');

        // Consulta con ordenamiento por tipo_notificacion de manera personalizada
        $notificaciones = DB::table('notificaciones')
            ->where('id_notificacion', $idNotificaciones)
            ->orderByRaw("FIELD(tipo_notificacion, 'primera_notificacion', 'segunda_notificacion', 'tercera_notificacion', 'notificacion_carga_vobo')")
            ->get(['id', 'nombre', 'tipo_notificacion', 'requisito_id'])
            ->toArray();

        $resultado = array_map(function ($notificacion) {
            $dias = '';
            $tipoNotificacion = '';
            $estilo = '';

            switch ($notificacion->tipo_notificacion) {
                case 'primera_notificacion':
                    $dias = '30 días antes de la fecha de vencimiento';
                    $tipoNotificacion = '1era Notificación';
                    $estilo = 'style="background-color: #90ee90; color: black;"';
                    break;
                case 'segunda_notificacion':
                    $dias = '15 días antes de la fecha de vencimiento';
                    $tipoNotificacion = '2da Notificación';
                    $estilo = 'style="background-color: #ffff99; color: black;"';
                    break;
                case 'tercera_notificacion':
                    $dias = '5 días antes de la fecha de vencimiento';
                    $tipoNotificacion = '3era Notificación';
                    $estilo = 'style="background-color: #ffcc99; color: black;"';
                    break;
                case 'notificacion_carga_vobo':
                    $dias = 'Inmediato antes de la fecha de vencimiento';
                    $tipoNotificacion = '4ta Notificación';
                    $estilo = 'style="background-color: #ff9999; color: black;"';
                    break;
            }

            return [
                'id' => $notificacion->id,
                'nombre' => $notificacion->nombre,
                'tipo' => $tipoNotificacion,
                'dias' => $dias,
                'estilo' => $estilo,
                'requisito_id' => $notificacion->requisito_id
            ];
        }, $notificaciones);

        return response()->json($resultado);
    }




    public function cambiarEstado(Request $request)
    {
        
        $request->validate([
            'id' => 'required|integer|exists:requisitos,id',
        ]);

        try {
            $requisitoId = $request->id;

            // Buscar el requisito 
            $requisito = Requisito::find($requisitoId);
            if (!$requisito) {
                $this->logInfo('Requisito no encontrado', ['requisito_id' => $requisitoId]);
                return response()->json(['error' => 'Requisito no encontrado'], 404);
            }

            // Cambiar el estado del requisito 
            $requisito->approved = !$requisito->approved;
            $requisito->save();

            $this->logInfo('Estado del requisito cambiado', [
                'requisito_id' => $requisitoId,
                'nuevo_estado' => $requisito->approved
            ]);

            //Obtener correos de evidence_notifications con type = 1 ***
            $emailNotifications = EvidenceNotification::where('type', 1)->pluck('email')->toArray();

            // Combinar los correos del requisito actual con los de la tabla evidence_notifications
            $emailResponsables = !empty($requisito->email) ? [$requisito->email] : [];
            $destinatarios = array_merge($emailResponsables, $emailNotifications);


            // Enviar correo a los responsables si hay destinatarios 
            if (count($destinatarios) > 0) {
                Mail::to($destinatarios)->send(new EstadoEvidenciaCambiado(
                    $requisito->nombre,
                    $requisito->evidencia,
                    $requisito->periodicidad,
                    $requisito->responsable,
                    Carbon::parse($requisito->fecha_limite_cumplimiento)->format('d/m/Y'),
                    $requisito->origen_obligacion,
                    $requisito->clausula_condicionante_articulo,
                    $requisito->approved
                ));

                $this->logInfo('Correo enviado correctamente', ['destinatarios' => $destinatarios]);
            } else {
                $this->logWarning('No se encontraron destinatarios para el envío de correo', ['requisito_id' => $requisitoId]);
            }

            return response()->json(['success' => true, 'approved' => $requisito->approved]);
        } catch (\Exception $e) {
            $this->logError('Error al cambiar el estado del requisito', [
                'requisito_id' => $request->id ?? 'N/A',
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Ocurrió un error al cambiar el estado del requisito'], 500);
        }
    }


    public function obtenerEstadoApproved(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|integer|exists:requisitos,id'
            ]);

            $requisito = Requisito::find($validatedData['id']);
            $this->logInfo('Estado "approved" obtenido correctamente', ['requisito_id' => $requisito->id, 'approved' => $requisito->approved]);

            return response()->json(['approved' => $requisito->approved]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logWarning('Error de validación al obtener el estado "approved"', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Datos no válidos', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            $this->logError('Error inesperado al obtener el estado "approved"', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Ocurrió un error al obtener el estado aprobado'], 500);
        }
    }

    public function enviarCorreoDatosEvidencia(Request $request)
    {
        try {
            
            $request->validate([
                'evidencia' => 'required|string'
            ]);

            
            $datos = $request->all();

            
            $requisito = Requisito::where('evidencia', $datos['evidencia'])->first();

            
            if (!$requisito) {
                $this->logWarning('No se encontró el requisito asociado a la evidencia', ['evidencia' => $datos['evidencia']]);
                return response()->json(['error' => 'No se encontró el requisito asociado a la evidencia'], 404);
            }

            // Agregar la lógica que necesites, pero sin enviar correos

            return response()->json(['success' => true, 'message' => 'Procesamiento completado sin envío de correo.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logWarning('Error de validación al procesar los datos de evidencia', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Datos no válidos', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            $this->logError('Error inesperado al procesar los datos de evidencia', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Ocurrió un error durante el procesamiento'], 500);
        }
    }

    public function actualizarPorcentaje(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|integer|exists:requisitos,id',
            ]);

            $requisito = Requisito::find($validatedData['id']);
            if (!$requisito) {
                $this->logWarning('Requisito no encontrado', ['requisito_id' => $validatedData['id']]);
                return response()->json(['error' => 'Requisito no encontrado'], 404);
            }

            $requisito->porcentaje = $requisito->porcentaje == 100 ? 0 : 100;
            $requisito->save();

            $this->logInfo('Porcentaje actualizado correctamente', [
                'requisito_id' => $requisito->id,
                'nuevo_porcentaje' => $requisito->porcentaje,
            ]);

            return response()->json(['success' => true, 'nuevo_porcentaje' => $requisito->porcentaje]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logWarning('Error de validación al actualizar el porcentaje', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Datos no válidos', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            $this->logError('Error inesperado al actualizar el porcentaje', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Ocurrió un error al actualizar el porcentaje'], 500);
        }
    }

    public function actualizarPorcentajeSuma(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'numero_requisito' => 'required|integer',
                'requisito_id' => 'required|integer|exists:requisitos,id',
            ]);

            $numeroRequisito = $validatedData['numero_requisito'];
            $requisitoId = $validatedData['requisito_id'];
            $avanceActual = DB::table('requisitos')
                ->where('id', $requisitoId)
                ->value('avance');

            $nuevoAvance = ($avanceActual > 0) ? 0 : round(100 / DB::table('requisitos')
                ->where('numero_requisito', $numeroRequisito)
                ->count(), 2);

            DB::table('requisitos')
                ->where('id', $requisitoId)
                ->update(['avance' => $nuevoAvance]);

            $this->logInfo('Avance actualizado', [
                'requisito_id' => $requisitoId,
                'numero_requisito' => $numeroRequisito,
                'nuevo_avance' => $nuevoAvance,
            ]);

            return response()->json([
                'conteo' => DB::table('requisitos')->where('numero_requisito', $numeroRequisito)->count(),
                'nuevo_avance' => $nuevoAvance,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logWarning('Error de validación al actualizar el avance', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Datos no válidos', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            $this->logError('Error inesperado al actualizar el avance', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Ocurrió un error al actualizar el avance'], 500);
        }
    }

    public function validarArchivos(Request $request)
    {
        $validatedData = $request->validate([
            'requisito_id' => 'required|integer|exists:requisitos,id',
            'numero_evidencia' => 'required|string',
            'fecha_limite_cumplimiento' => 'required|date',
        ]);

        $requisitoId = $validatedData['requisito_id'];
        $numeroEvidencia = $validatedData['numero_evidencia'];
        $fechaLimiteCumplimiento = $validatedData['fecha_limite_cumplimiento'];

        try {
            $archivo = DB::table('archivos')
                ->where('requisito_id', $requisitoId)
                ->where('evidencia', $numeroEvidencia)
                ->whereDate('fecha_limite_cumplimiento', $fechaLimiteCumplimiento)
                ->first();

            if ($archivo) {
                $this->logInfo('Archivo validado exitosamente', ['archivo_id' => $archivo->id, 'requisito_id' => $requisitoId]);
                return response()->json(['exists' => true, 'message' => 'Archivo encontrado.'], 200);
            } else {
                $this->logInfo('No se encontraron archivos con los datos proporcionados', ['requisito_id' => $requisitoId, 'numero_evidencia' => $numeroEvidencia]);
                return response()->json(['exists' => false, 'message' => 'No se encontraron archivos con los datos proporcionados.'], 404);
            }
        } catch (\Exception $e) {
            $this->logError('Error al validar archivos', ['requisito_id' => $requisitoId, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al validar archivos.'], 500);
        }
    }

    public function verificarArchivos(Request $request)
    {
        $validatedData = $request->validate([
            'requisito_id' => 'required|integer|exists:requisitos,id',
            'fecha_limite_cumplimiento' => 'required|date_format:d/m/Y',
        ]);

        try {
            $requisitoId = $validatedData['requisito_id'];
            $fechaLimite = Carbon::createFromFormat('d/m/Y', $validatedData['fecha_limite_cumplimiento'])->format('Y-m-d');
            $conteo = Archivo::where('requisito_id', $requisitoId)
                ->whereDate('fecha_limite_cumplimiento', $fechaLimite)
                ->count();

            $this->logInfo('Verificación de archivos completada', [
                'requisito_id' => $requisitoId,
                'fecha_limite_cumplimiento' => $fechaLimite,
                'conteo' => $conteo,
            ]);

            return response()->json(['conteo' => $conteo]);
        } catch (\Exception $e) {
            $this->logError('Error al verificar archivos', [
                'requisito_id' => $request->input('requisito_id'),
                'fecha_limite_cumplimiento' => $request->input('fecha_limite_cumplimiento'),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Error al verificar archivos.'], 500);
        }
    }

    private function logInfo($message, $context = [])
    {
        Log::info($message, $context);
    }

    private function logWarning($message, $context = [])
    {
        Log::warning($message, $context);
    }

    private function logError($message, $context = [])
    {
        Log::error($message, $context);
    }

    public function filtrarObligaciones(Request $request)
    {
        $year = $request->input('year');
        $user = Auth::user();
    
        if (!$user || !$user->puesto) {
            return back()->withErrors(['error' => 'No se encontró el puesto del usuario autenticado']);
        }
    
        // Obtener los puestos de usuarios asociados con authorization_id = 7
        $puestosExcluidos = DB::table('users')
            ->join('model_has_authorizations', 'users.id', '=', 'model_has_authorizations.model_id')
            ->where('model_has_authorizations.authorization_id', 7)
            ->distinct()
            ->pluck('users.puesto')
            ->toArray();
    
        // Obtener los requisitos que el usuario puede ver según la tabla pivote
        $requisitosIds = ObligacionUsuario::where('user_id', $user->id)
            ->where('view', 1)
            ->pluck('numero_evidencia')
            ->toArray();
    
        $query = Requisito::porAno($year)
            ->with('archivos');
    
        if (!empty($requisitosIds)) {
            $query->whereIn('numero_evidencia', $requisitosIds);
        }
    
        if (!in_array($user->puesto, $puestosExcluidos)) {
            $query->permitirVisualizacion($user);
        }
    
        $requisitos = $query->get()
            ->filter(fn($requisito) => !empty($requisito->responsable))
            ->each(
                fn($requisito) =>
                $requisito->total_avance = $this->getTotalAvance($requisito->numero_requisito, $user->puesto, $year)
            );
    
        return view('gestion_cumplimiento.obligaciones.index', compact('requisitos', 'user', 'year'));
    }
    
    

    public function obtenerDetalleEvidencia(Request $request)
    {
        try {
            $evidenciaId = $request->input('evidencia_id');
            $detalleId = $request->input('detalle_id');
            $requisitoId = $request->input('requisito_id');

            if (empty($evidenciaId) || empty($detalleId) || empty($requisitoId)) {
                Log::warning('Datos de entrada faltantes o inválidos', [
                    'evidencia_id' => $evidenciaId,
                    'detalle_id' => $detalleId,
                    'requisito_id' => $requisitoId
                ]);
                return response()->json(['error' => 'Datos de entrada faltantes o inválidos'], 400);
            }

            $detalle = Requisito::where('id', $detalleId)
                ->where('numero_evidencia', $evidenciaId)
                ->first();

            if (!$detalle) {
                Log::info('Detalle no encontrado', ['detalle_id' => $detalleId, 'evidencia_id' => $evidenciaId]);
                return response()->json(['error' => 'Detalle no encontrado'], 404);
            }

            $archivo = Archivo::where('requisito_id', $requisitoId)->first();

            return response()->json([
                'id' => $detalle->id,
                'numero_requisito' => $evidenciaId,
                'nombre' => $detalle->nombre,
                'evidencia' => $detalle->evidencia,
                'periodicidad' => $detalle->periodicidad,
                'responsable' => $detalle->responsable,
                'fecha_limite_cumplimiento' => $detalle->fecha_limite_cumplimiento
                    ? \Carbon\Carbon::parse($detalle->fecha_limite_cumplimiento)->format('d/m/Y')
                    : null,
                'origen_obligacion' => $detalle->origen_obligacion,
                'clausula_condicionante_articulo' => $detalle->clausula_condicionante_articulo,
                'nombre_archivo' => $archivo ? $archivo->nombre_archivo : null,
                'condicion' => $detalle->condicion,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener el detalle de evidencia', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Ocurrió un error al obtener el detalle de la evidencia'], 500);
        }
    }

    public function enviarCorreoAlerta(Request $request)
    {
        $diasRestantes = $request->input('dias_restantes');

        // Definir el color de fondo según los días restantes
        switch ($diasRestantes) {
            case 30:
                $colorFondo = '#90ee90'; // Verde claro
                break;
            case 15:
                $colorFondo = '#ffff99'; // Amarillo claro
                break;
            case 5:
                $colorFondo = '#ffcc99'; // Naranja claro
                break;
            case 2:
            case 1:
                $colorFondo = '#ff9999'; // Rojo claro
                break;
            default:
                $colorFondo = '#ffffff'; // Color por defecto (blanco)
                break;
        }

        // Enviar el correo
        Mail::to('daniel.cervantes@supervia.mx')->send(new AlertaCorreo($diasRestantes, $colorFondo));

        return response()->json(['success' => true, 'message' => "Correo de alerta enviado correctamente."]);
    }

    public function obtenerUsuarios()
    {
        try {
            // Obtener todos los usuarios con su nombre y puesto 
            $usuarios = DB::table('users')->select('id', 'name', 'puesto', 'email')->get();

            if ($usuarios->isEmpty()) {
                return response()->json(['message' => 'No se encontraron usuarios.'], 404);
            }

            return response()->json($usuarios, 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener usuarios: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al obtener los usuarios.'], 500);
        }
    }

    public function UsuarioNuevoTablaNotificaciones(Request $request)
    {
        
        $validatedData = $request->validate([
            'requisitoId' => 'required|integer|exists:requisitos,id',
            'numeroRequisito' => 'required|string|max:50',
            'evidenciaId' => 'required|string|max:50',
            'idNotificaciones' => 'required|string|max:50|exists:notificaciones,id_notificacion',
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'tipoNotificacion' => 'required|string|in:primera_notificacion,segunda_notificacion,tercera_notificacion,notificacion_carga_vobo|max:50',
        ]);
    
        try {
            
            $existeRegistro = DB::table('notificaciones')->where([
                ['requisito_id', $validatedData['numeroRequisito']],
                ['numero_evidencia', $validatedData['evidenciaId']],
                ['id_notificacion', $validatedData['idNotificaciones']],
                ['nombre', $validatedData['nombre']],
                ['email', $validatedData['email']],
                ['tipo_notificacion', $validatedData['tipoNotificacion']],
            ])->exists();
    
            if ($existeRegistro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe esta notificación para este puesto.',
                ], 200); 
            }
    
            
            DB::table('notificaciones')->insert([
                'requisito_id' => $validatedData['numeroRequisito'],
                'numero_evidencia' => $validatedData['evidenciaId'],
                'id_notificacion' => $validatedData['idNotificaciones'],
                'nombre' => $validatedData['nombre'],
                'email' => $validatedData['email'],
                'tipo_notificacion' => $validatedData['tipoNotificacion'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Usuario agregado a la tabla de notificaciones correctamente.',
            ], 200); 
        } catch (\Exception $e) {
            
            Log::error('Error al guardar notificación: ', [
                'error' => $e->getMessage(),
                'datos' => $validatedData,
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al guardar el usuario en la tabla de notificaciones.',
            ], 500);
        }
    }
    
    
    

    public function eliminarNotificacion(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|integer|exists:notificaciones,id'
        ]);
    
        try {
            
            $notificacion = DB::table('notificaciones')->where('id', $validatedData['id'])->first();
    
            if (is_null($notificacion)) {
                return response()->json(['error' => 'La notificación no existe o ya fue eliminada.'], 404);
            }
    
            
            DB::table('notificaciones')->where('id', $validatedData['id'])->delete();
    
            return response()->json(['message' => 'La notificación se eliminó correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor. Intente más tarde.'], 500);
        }
    }

    
    
    
    
}
