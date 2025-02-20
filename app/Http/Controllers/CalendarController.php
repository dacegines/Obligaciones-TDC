<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Requisito;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class CalendarController extends Controller
{
    public function index()
    {
        
        return view('gestion_cumplimiento.calendario.index');
    }

    public function fetchRequisitos(Request $request)
    {
        try {
            
            if (!Auth::user()->can('superUsuario') && !Auth::user()->can('obligaciones de concesión')  && !Auth::user()->can('obligaciones de concesión limitado')) {
                abort(403, 'No tienes permiso para acceder a esta página.');
            }
    
            
            $user = Auth::user();
    
            if (!$user || !$user->puesto) {
                Log::warning('Usuario autenticado sin puesto definido', ['user_id' => $user->id ?? null]);
                return response()->json(['error' => 'No se encontró el puesto del usuario autenticado'], 403);
            }
    
           
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
            ])->get();
            
            return response()->json($requisitos, 200, [], JSON_UNESCAPED_UNICODE);
            
    
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
