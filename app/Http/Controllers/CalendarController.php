<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requisito;
use App\Models\ObligacionUsuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class CalendarController extends Controller
{
    public function index()
    {
        try {
            // Verificar permisos del usuario
            if (!Auth::user()->can('superUsuario') && !Auth::user()->can('obligaciones de concesión') && !Auth::user()->can('obligaciones de concesión limitado')) {
                abort(403, 'No tienes permiso para acceder a esta página.');
            }
    
            // Obtener el usuario autenticado
            $user = Auth::user();
    
            if (!$user || !$user->puesto) {
                Log::warning('Usuario autenticado sin puesto definido', ['user_id' => $user->id ?? null]);
                return view('gestion_cumplimiento.calendario.index')->with('error', 'No se encontró el puesto del usuario autenticado');
            }
    
            // Verificar si el usuario tiene autorización (authorization_id = 7)
            $tieneAutorizacion = DB::table('model_has_authorizations')
                ->where('authorization_id', 7)
                ->where('model_id', $user->id)
                ->exists();
    
            if (!$tieneAutorizacion) {
                Log::info('El usuario no tiene autorización con authorization_id = 7', ['user_id' => $user->id]);
                return view('gestion_cumplimiento.calendario.index')->with('error', 'No tienes autorización para ver estas obligaciones');
            }
    
            // Verificar si el usuario tiene obligaciones con view = 1
            $tieneObligaciones = ObligacionUsuario::where('user_id', $user->id)
                ->where('view', 1)
                ->exists();
    
            if (!$tieneObligaciones) {
                Log::info('El usuario no tiene obligaciones con view = 1', ['user_id' => $user->id]);
                return view('gestion_cumplimiento.calendario.index')->with('error', 'Este usuario no tiene obligaciones registradas, permisos para ver las obligaciones o el año no contiene obligaciones.');
            }
    
            // Si todo está bien, cargar la vista del calendario
            return view('gestion_cumplimiento.calendario.index');
    
        } catch (\Exception $e) {
            Log::error('Error al cargar la vista del calendario', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return view('gestion_cumplimiento.calendario.index')->with('error', 'Ocurrió un error al cargar la vista del calendario.');
        }
    }
    public function fetchRequisitos(Request $request)
    {
        try {
            // Verificar permisos del usuario
            if (!Auth::user()->can('superUsuario') && !Auth::user()->can('obligaciones de concesión')  && !Auth::user()->can('obligaciones de concesión limitado')) {
                abort(403, 'No tienes permiso para acceder a esta página.');
            }
    
            // Obtener el usuario autenticado
            $user = Auth::user();
    
            if (!$user || !$user->puesto) {
                Log::warning('Usuario autenticado sin puesto definido', ['user_id' => $user->id ?? null]);
                return response()->json(['error' => 'No se encontró el puesto del usuario autenticado'], 403);
            }
    
            // Verificar si el usuario tiene autorización (authorization_id = 7)
            $tieneAutorizacion = DB::table('model_has_authorizations')
                ->where('authorization_id', 7)
                ->where('model_id', $user->id)
                ->exists();
    
            if (!$tieneAutorizacion) {
                // Si no tiene autorización, no mostrar datos
                Log::info('El usuario no tiene autorización con authorization_id = 7', ['user_id' => $user->id]);
                return response()->json([], 200);
            }
    
            // Verificar si el usuario tiene obligaciones con view = 1
            $tieneObligaciones = ObligacionUsuario::where('user_id', $user->id)
                ->where('view', 1)
                ->exists();
    
            if (!$tieneObligaciones) {
                // Si no tiene obligaciones con view = 1, no mostrar datos
                Log::info('El usuario no tiene obligaciones con view = 1', ['user_id' => $user->id]);
                return response()->json([], 200);
            }
    
            // Obtener los IDs de requisitos que el usuario puede ver
            $requisitosIds = ObligacionUsuario::where('user_id', $user->id)
                ->where('view', 1)
                ->pluck('numero_evidencia')
                ->toArray();
    
            // Obtener el año seleccionado (por defecto, el año actual)
            $ano = $request->get('year', now()->year);
    
            // Filtrar los requisitos usando los scopes definidos en el modelo
            $requisitos = Requisito::select([
                    'id', 
                    'nombre as title', 
                    'numero_evidencia as obligacion',
                    'fecha_limite_cumplimiento as start', 
                    'clausula_condicionante_articulo as description', 
                    'responsable',
                    'approved' 
                ])
                ->whereYear('fecha_limite_cumplimiento', $ano) // Filtrar por año
                ->whereIn('numero_evidencia', $requisitosIds) // Filtrar por obligaciones visibles
                ->get();
    
            Log::info('Requisitos cargados correctamente', [
                'user_id' => $user->id,
                'total_requisitos' => $requisitos->count()
            ]);
    
            // Devuelve los datos en formato JSON con caracteres no escapados
            return response()->json($requisitos, 200, [], JSON_UNESCAPED_UNICODE);
    
        } catch (\Exception $e) {
            Log::error('Error al cargar los requisitos', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return response()->json(['error' => 'Ocurrió un error al cargar los requisitos.'], 500);
        }
    }
    
}
