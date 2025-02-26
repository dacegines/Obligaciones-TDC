<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Archivo;

class DeletedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'requirement_id',
        'evidence',
        'compliance_deadline',
        'user_name',
        'user_position',
        'user_id',
        'deleted_by',
    ];

    public function eliminar(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|integer|exists:archivos,id',
            'ruta_archivo' => 'required|string',
        ]);

        $archivo = Archivo::find($validatedData['id']);

        if ($archivo) {
            try {
                // 1. Recuperar los datos del archivo
                $datosArchivo = [
                    'file_name' => $archivo->nombre_archivo,
                    'file_path' => $archivo->ruta_archivo,
                    'requirement_id' => $archivo->requisito_id,
                    'evidence' => $archivo->evidencia,
                    'compliance_deadline' => $archivo->fecha_limite_cumplimiento,
                    'user_name' => $archivo->usuario,
                    'user_position' => $archivo->puesto,
                    'user_id' => $archivo->user_id,
                    'deleted_by' => Auth::id(), // ID del usuario que eliminó el archivo
                ];

                // 2. Insertar los datos en la tabla "deleted_files"
                DeletedFile::create($datosArchivo);

                // 3. Eliminar el archivo del almacenamiento
                $rutaArchivo = 'public/' . $archivo->ruta_archivo;
                if (Storage::exists($rutaArchivo)) {
                    Storage::delete($rutaArchivo);
                }

                // 4. Eliminar el registro de la tabla "archivos"
                $archivo->delete();

                return response()->json(['success' => true, 'message' => 'Archivo eliminado correctamente']);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Error al eliminar el archivo: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Archivo no encontrado'], 404);
    }
}
