<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Archivo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth; 
use App\Mail\ArchivoSubidoMail;
use App\Models\Requisito;
use App\Models\DeletedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\EvidenceNotification;
use App\Mail\DatosEvidenciaMail;
use App\Mail\ArchivoEliminadoMail;
use App\Models\FileComment;

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
            $archivo->user_id = Auth::id();
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
                $rutaArchivo, 
                $validatedData['usuario'], 
                $validatedData['puesto'] 
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
    
        $archivos = Archivo::where('requisito_id', $requisitoId)
            ->where('evidencia', $evidenciaId)
            ->whereDate('fecha_limite_cumplimiento', $fechaLimite)
            ->with(['comments.user:id,name,puesto']) 
            ->withCount('comments')
            ->get();
    
        return response()->json([
            'archivos' => $archivos,
            'currentUserId' => Auth::id(), 
        ]);
    }
    
    


    public function eliminar(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|integer|exists:archivos,id',
            'ruta_archivo' => 'required|string',
        ]);
    
        $archivo = Archivo::find($validatedData['id']);
    
        if (!$archivo) {
            return response()->json(['success' => false, 'message' => 'Archivo no encontrado.'], 404);
        }
    
        try {
            
            DeletedFile::create([
                'file_name' => $archivo->nombre_archivo,
                'file_path' => $archivo->ruta_archivo,
                'requirement_id' => $archivo->requisito_id,
                'evidence' => $archivo->evidencia,
                'compliance_deadline' => $archivo->fecha_limite_cumplimiento,
                'user_name' => $archivo->usuario,
                'user_position' => $archivo->puesto,
                'user_id' => $archivo->user_id,
                'deleted_by' => Auth::id(),
            ]);
    
            
            $requisito = Requisito::find($archivo->requisito_id);
            if ($requisito) {
                $emailNotifications = EvidenceNotification::where('type', 1)->pluck('email')->toArray();
                $emailResponsables = !empty($requisito->email) ? [$requisito->email] : [];
                $destinatarios = array_merge($emailResponsables, $emailNotifications);
    
                if (!empty($destinatarios)) {
                    $rutaArchivo = storage_path('app/public/' . $archivo->ruta_archivo);
                    if (file_exists($rutaArchivo)) {
                        Mail::to($destinatarios)->send(new ArchivoEliminadoMail(
                            $requisito->nombre,
                            $requisito->evidencia,
                            $requisito->periodicidad,
                            $requisito->responsable,
                            $archivo->fecha_limite_cumplimiento,
                            $requisito->origen_obligacion,
                            $requisito->clausula_condicionante_articulo,
                            $archivo->usuario,
                            $archivo->puesto,
                            $rutaArchivo
                        ));
                    } else {
                        Log::error('El archivo no existe en la ruta: ' . $rutaArchivo);
                    }
                }
            }
    
            
            $rutaArchivoStorage = 'public/' . $archivo->ruta_archivo;
            if (Storage::exists($rutaArchivoStorage)) {
                Storage::delete($rutaArchivoStorage);
            }
    
            
            $archivo->delete();
    
            return response()->json(['success' => true, 'message' => 'Archivo y comentarios eliminados correctamente.']);
    
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar el archivo'], 500);
        }
    }
    



public function storeComment(Request $request)
{
    
    $request->validate([
        'archivo_id' => 'required|exists:archivos,id',
        'comment' => 'required|string|max:500'
    ]);

    
    $comment = FileComment::create([
        'archivo_id' => $request->archivo_id,
        'user_id' => Auth::id(), 
        'comment' => $request->comment
    ]);

    
    return response()->json([
        'message' => 'Comentario agregado correctamente',
        'comment' => [
            'id' => $comment->id,
            'archivo_id' => $comment->archivo_id,
            'user' => Auth::user()->name, 
            'puesto' => Auth::user()->puesto,
            'text' => $comment->comment,
            'fecha' => now()->format('Y-m-d H:i:s')
        ]
    ], 201);
}

public function eliminarComentario($id)
{
    $comentario = FileComment::find($id);

    if (!$comentario) {
        return response()->json(['message' => 'Comentario no encontrado.'], 404);
    }

    
    if ($comentario->user_id !== Auth::id()) {
        return response()->json(['message' => 'No tienes permiso para eliminar este comentario.'], 403);
    }

    $comentario->delete();

    return response()->json(['message' => 'Comentario eliminado correctamente.']);
}


}
