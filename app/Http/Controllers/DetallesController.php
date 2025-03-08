<?php

namespace App\Http\Controllers;

use App\Models\Requisito;
use App\Models\ObligacionUsuario;
use Illuminate\Http\Request;
use App\Exports\RequisitosExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DetallesController extends Controller
{ 
    public function index(Request $request)
    {
        if (!Auth::user()->can('superUsuario') && !Auth::user()->can('obligaciones de concesión') && !Auth::user()->can('obligaciones de concesión limitado')) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
    
        if (!Auth::check()) {
            return redirect()->route('login');
        }
    
        $user = Auth::user();
        $year = \Carbon\Carbon::now()->year;
    
        // Verificar si el usuario tiene autorización (authorization_id = 7)
        $tieneAutorizacion = DB::table('model_has_authorizations')
            ->where('authorization_id', 7)
            ->where('model_id', $user->id)
            ->exists();
    
        if (!$tieneAutorizacion) {
            // Si no tiene autorización, no mostrar nada
            return view('gestion_cumplimiento.detalles.index', [
                'requisitos' => collect(), // Colección vacía
                'year' => $year
            ])->with('info', 'No tienes autorización para ver detalles.');
        }
    
        // Verificar si el usuario tiene obligaciones con view = 1
        $tieneObligaciones = ObligacionUsuario::where('user_id', $user->id)
            ->where('view', 1)
            ->exists();
    
        if (!$tieneObligaciones) {
            // Si no tiene obligaciones con view = 1, no mostrar nada
            return view('gestion_cumplimiento.detalles.index', [
                'requisitos' => collect(), // Colección vacía
                'year' => $year
            ])->with('info', 'No tienes obligaciones para mostrar.');
        }
    
        // Construir consulta base
        $requisitos = $this->getRequisitos($year, $user);
    
        return view('gestion_cumplimiento.detalles.index', compact('requisitos', 'year'));
    }

    // Método para filtrar los requisitos por año
    public function filtrarDetalles(Request $request)
    {
        $validatedData = $request->validate([
            'year' => 'required|integer|min:2024|max:2040',
        ]);
    
        $year = $validatedData['year'];
        $user = Auth::user();
    
        // Verificar si el usuario tiene autorización (authorization_id = 7)
        $tieneAutorizacion = DB::table('model_has_authorizations')
            ->where('authorization_id', 7)
            ->where('model_id', $user->id)
            ->exists();
    
        if (!$tieneAutorizacion) {
            // Si no tiene autorización, no mostrar nada
            return view('gestion_cumplimiento.detalles.index', [
                'requisitos' => collect(), // Colección vacía
                'year' => $year
            ])->with('info', 'No tienes autorización para ver detalles.');
        }
    
        // Verificar si el usuario tiene obligaciones con view = 1
        $tieneObligaciones = ObligacionUsuario::where('user_id', $user->id)
            ->where('view', 1)
            ->exists();
    
        if (!$tieneObligaciones) {
            // Si no tiene obligaciones con view = 1, no mostrar nada
            return view('gestion_cumplimiento.detalles.index', [
                'requisitos' => collect(), // Colección vacía
                'year' => $year
            ])->with('info', 'No tienes obligaciones para mostrar.');
        }
    
        // Construir consulta base
        $requisitos = $this->getRequisitos($year, $user);
    
        return view('gestion_cumplimiento.detalles.index', compact('requisitos', 'year'));
    }

    // Método para exportar requisitos a Excel
    public function export(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $validatedData = $request->validate([
            'year' => 'required|integer|min:2024|max:2040',
        ]);

        $year = $validatedData['year'];

        try {
            $requisitos = Requisito::whereYear('fecha_limite_cumplimiento', $year)
                ->orderBy('fecha_limite_cumplimiento', 'asc')
                ->get();

            return Excel::download(new RequisitosExport($requisitos), 'requisitos.xlsx');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error al exportar los datos.');
        }
    }

    // Método para obtener archivos relacionados con una fecha específica
    public function obtenerArchivosPorFecha($fecha_limite_cumplimiento)
    {
        $archivos = DB::table('archivos')
            ->where('fecha_limite_cumplimiento', $fecha_limite_cumplimiento)
            ->select('nombre_archivo', 'ruta_archivo')
            ->get();

        return response()->json($archivos);
    }

    // Método para generar un PDF con los requisitos
    public function descargarPDF(Request $request)
    {
        $year = $request->input('year', \Carbon\Carbon::now()->year);
        $search = $request->input('search');
        $user = Auth::user();
    
        if (!$user) {
            return redirect()->route('login');
        }
    
        // Verificar si el usuario tiene autorización (authorization_id = 7)
        $tieneAutorizacion = DB::table('model_has_authorizations')
            ->where('authorization_id', 7)
            ->where('model_id', $user->id)
            ->exists();
    
        if (!$tieneAutorizacion) {
            // Si no tiene autorización, no generar el PDF
            return redirect()->back()->with('info', 'No tienes autorización para descargar el PDF.');
        }
    
        // Verificar si el usuario tiene obligaciones con view = 1
        $tieneObligaciones = ObligacionUsuario::where('user_id', $user->id)
            ->where('view', 1)
            ->exists();
    
        if (!$tieneObligaciones) {
            // Si no tiene obligaciones con view = 1, no generar el PDF
            return redirect()->back()->with('info', 'No tienes obligaciones para descargar el PDF.');
        }
    
        // Obtener los IDs de requisitos que el usuario puede ver
        $requisitosIds = ObligacionUsuario::where('user_id', $user->id)
            ->where('view', 1)
            ->pluck('numero_evidencia')
            ->toArray();
    
        // Construir la consulta principal con filtro de ObligacionUsuario
        $requisitosQuery = DB::table('requisitos as r')
            ->select(
                'r.numero_evidencia',
                'r.clausula_condicionante_articulo as clausula',
                'r.evidencia as requisito_evidencia',
                'r.periodicidad',
                'r.fecha_limite_cumplimiento',
                'r.responsable',
                'r.porcentaje',
                DB::raw("CASE 
                    WHEN r.porcentaje = 100 THEN 'Cumplido'
                    WHEN r.fecha_limite_cumplimiento < NOW() THEN 'Vencido'
                    WHEN DATEDIFF(r.fecha_limite_cumplimiento, NOW()) <= 30 THEN 'Próximo'
                    ELSE 'Activo'
                END AS estatus"),
                DB::raw("(SELECT COUNT(*) FROM archivos a WHERE a.fecha_limite_cumplimiento = r.fecha_limite_cumplimiento) as cantidad_archivos")
            )
            ->whereYear('r.fecha_limite_cumplimiento', $year);
    
        // Aplicar el filtro de ObligacionUsuario
        if (!empty($requisitosIds)) {
            $requisitosQuery->whereIn('r.numero_evidencia', $requisitosIds);
        }
    
        // Aplicar el filtro de búsqueda si se proporciona
        if (!empty($search)) {
            $requisitosQuery->where(function ($query) use ($search) {
                $query->where('r.numero_evidencia', 'like', "%$search%")
                    ->orWhere('r.clausula_condicionante_articulo', 'like', "%$search%")
                    ->orWhere('r.evidencia', 'like', "%$search%")
                    ->orWhere('r.periodicidad', 'like', "%$search%")
                    ->orWhere('r.responsable', 'like', "%$search%")
                    ->orWhere(DB::raw("CASE 
                            WHEN r.porcentaje = 100 THEN 'Cumplido'
                            WHEN r.fecha_limite_cumplimiento < NOW() THEN 'Vencido'
                            WHEN DATEDIFF(r.fecha_limite_cumplimiento, NOW()) <= 30 THEN 'Próximo'
                            ELSE 'Activo'
                        END"), 'like', "%$search%");
            });
        }
    
        $requisitos = $requisitosQuery->get();
    
        // Verificar si hay resultados antes de generar el PDF
        if ($requisitos->isEmpty()) {
            return redirect()->back()->with('info', 'No hay registros para descargar.');
        }
    
        // Generar el PDF
        $pdf = Pdf::loadView('pdf.reporte', compact('requisitos', 'year'))
            ->setPaper('A4', 'landscape');
    
        return $pdf->download('reporte_detalles.pdf');
    }
    

    // Método privado para construir la consulta de requisitos
    private function getRequisitos($year, $user, $search = null)
    {
        // Verificar si el usuario tiene autorización (authorization_id = 7)
        $tieneAutorizacion = DB::table('model_has_authorizations')
            ->where('authorization_id', 7)
            ->where('model_id', $user->id)
            ->exists();
    
        if (!$tieneAutorizacion) {
            // Si no tiene autorización, retornar una colección vacía
            return collect();
        }
    
        // Obtener los requisitos que el usuario puede ver según la tabla pivote
        $requisitosIds = ObligacionUsuario::where('user_id', $user->id)
            ->where('view', 1)
            ->pluck('numero_evidencia')
            ->toArray();
    
        // Si no hay obligaciones con view = 1, retornar una colección vacía
        if (empty($requisitosIds)) {
            return collect();
        }
    
        // Construir la consulta base
        $query = DB::table('requisitos as r')
            ->leftJoin('archivos as a', 'r.fecha_limite_cumplimiento', '=', 'a.fecha_limite_cumplimiento')
            ->select(
                'r.id',
                DB::raw('MAX(r.numero_evidencia) as numero_evidencia'),
                DB::raw('MAX(r.clausula_condicionante_articulo) as clausula'),
                DB::raw('MAX(r.evidencia) as requisito_evidencia'),
                DB::raw('MAX(r.periodicidad) as periodicidad'),
                DB::raw('MAX(r.fecha_limite_cumplimiento) as fecha_limite_cumplimiento'),
                DB::raw('MAX(r.responsable) as responsable'),
                DB::raw('MAX(r.porcentaje) as porcentaje'),
                DB::raw('COUNT(a.id) as cantidad_archivos'),
                DB::raw("CASE 
                    WHEN MAX(r.porcentaje) = 100 THEN 'Cumplido'
                    WHEN MAX(r.fecha_limite_cumplimiento) < NOW() THEN 'Vencido'
                    WHEN DATEDIFF(MAX(r.fecha_limite_cumplimiento), NOW()) <= 30 THEN 'Próximo'
                    ELSE 'Activo'
                END AS estatus")
            )
            ->whereYear('r.fecha_limite_cumplimiento', $year)
            ->groupBy('r.id')
            ->orderBy('r.fecha_limite_cumplimiento', 'asc');
    
        // Aplicar filtro de `ObligacionUsuario`
        if (!empty($requisitosIds)) {
            $query->whereIn('r.numero_evidencia', $requisitosIds);
        }
    
        // Aplicar lógica según authorization_id
        $authorizationId = DB::table('model_has_authorizations')
            ->where('model_id', $user->id)
            ->value('authorization_id');
    
        if ($authorizationId == 8) {
            $query->where('r.responsable', $user->puesto);
        }
    
        // Aplicar filtro de búsqueda si se proporciona
        if (!empty($search)) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('r.numero_evidencia', 'like', "%$search%")
                    ->orWhere('r.clausula_condicionante_articulo', 'like', "%$search%")
                    ->orWhere('r.evidencia', 'like', "%$search%")
                    ->orWhere('r.periodicidad', 'like', "%$search%")
                    ->orWhere('r.responsable', 'like', "%$search%");
            });
        }
    
        return $query->get();
    }
    
}
