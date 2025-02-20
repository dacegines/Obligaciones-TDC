<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Archivo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth; // Para obtener el usuario autenticado
use App\Mail\ArchivoSubidoMail;
use App\Models\Requisito;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\EvidenceNotification;
use App\Mail\DatosEvidenciaMail;

class ArchivoController extends Controller
{
    public function subirArchivo(Request $request)
    {
        $validatedData = $request->validate([
            'archivo' => 'required|file|max:20480',
            'requisito_id' => 'required|integer',
            'evidencia' => 'required|string',
            'fecha_limite_cumplimiento' => 'required|date',
            'usuario' => 'required|string',
            'puesto' => 'required|string',
        ]);

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $filePath = $file->storeAs('uploads', $fileName, 'public');

            // Guardar el archivo en la base de datos
            $archivo = new Archivo();
            $archivo->nombre_archivo = $fileName;
            $archivo->ruta_archivo = $filePath;
            $archivo->requisito_id = $validatedData['requisito_id'];
            $archivo->evidencia = $validatedData['evidencia'];
            $archivo->fecha_limite_cumplimiento = $validatedData['fecha_limite_cumplimiento'];
            $archivo->usuario = $validatedData['usuario'];
            $archivo->puesto = $validatedData['puesto'];
            $archivo->fecha_subida = now(); 
            $archivo->save();

            // Buscar el requisito relacionado
            $requisito = Requisito::find($validatedData['requisito_id']);

            if (!$requisito) {
                return response()->json(['error' => 'No se encontró el requisito asociado.'], 404);
            }

            // Obtener destinatarios
            $emailNotifications = EvidenceNotification::where('type', 1)->pluck('email')->toArray();
            $emailResponsables = !empty($requisito->email) ? [$requisito->email] : [];
            $destinatarios = array_merge($emailResponsables, $emailNotifications);

            if (empty($destinatarios)) {
                return response()->json(['error' => 'No se encontraron destinatarios para el correo.'], 400);
            }

            // Ruta completa del archivo
            $rutaArchivo = storage_path("app/public/{$filePath}");

            // Enviar correo con el archivo adjunto
            Mail::to($destinatarios)->send(new DatosEvidenciaMail(
                $requisito->nombre,
                $requisito->evidencia,
                $requisito->periodicidad,
                $requisito->responsable,
                $validatedData['fecha_limite_cumplimiento'],
                $requisito->origen_obligacion,
                $requisito->clausula_condicionante_articulo,
                $rutaArchivo // Adjuntar el archivo recién subido
            ));

            return response()->json(['success' => 'Archivo subido y correo enviado correctamente.']);
        }

        return response()->json(['error' => 'No se pudo subir el archivo.'], 422);
    }

    public function listarArchivos(Request $request)
    {
        $requisitoId = $request->input('requisito_id');
        $evidenciaId = $request->input('evidencia_id');
        $fechaLimite = $request->input('fecha_limite');

        // Obtén los archivos relacionados con el requisito, la evidencia y la fecha límite
        $archivos = Archivo::where('requisito_id', $requisitoId)
            ->where('evidencia', $evidenciaId)
            ->whereDate('fecha_limite_cumplimiento', $fechaLimite) // Filtrar por la fecha límite
            ->get();

        
        return response()->json(['archivos' => $archivos]);
    }



    public function eliminar(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|integer|exists:archivos,id',
            'ruta_archivo' => 'required|string',
        ]);

        $archivo = Archivo::find($validatedData['id']);

        if ($archivo) {
            try {
                $rutaArchivo = 'public/' . $archivo->ruta_archivo;

                if (Storage::exists($rutaArchivo)) {
                    Storage::delete($rutaArchivo);
                }

                $archivo->delete();

                return response()->json(['success' => true, 'message' => 'Archivo eliminado correctamente']);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Error al eliminar el archivo'], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Archivo no encontrado'], 404);
    }
}
