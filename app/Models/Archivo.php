<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\FileComment;
use Illuminate\Support\Facades\Auth;


class Archivo extends Model {
    use HasFactory;

    protected $table = 'archivos';

    protected $fillable = [
        'requisito_id',
        'evidencia',
        'fecha_limite_cumplimiento',
        'nombre_archivo',
        'ruta_archivo',
        'fecha_subida',
        'usuario',
        'puesto',
    ];

    public function requisito() {
        return $this->belongsTo(Requisito::class);
    }

    // Relación con comentarios
    public function comments() {
        return $this->hasMany(FileComment::class, 'archivo_id');
    }


public function storeComment(Request $request)
{
    // Validar la solicitud
    $request->validate([
        'archivo_id' => 'required|exists:archivos,id',
        'comment' => 'required|string|max:500'
    ]);

    // Guardar el comentario en la base de datos
    $comment = FileComment::create([
        'archivo_id' => $request->archivo_id,
        'user_id' => Auth::id(), // ID del usuario autenticado
        'comment' => $request->comment
    ]);

    // Retornar una respuesta JSON con el comentario creado
    return response()->json([
        'message' => 'Comentario agregado correctamente',
        'comment' => [
            'id' => $comment->id,
            'archivo_id' => $comment->archivo_id,
            'user' => Auth::user()->name,
            'text' => $comment->comment,
            'fecha' => now()->format('Y-m-d H:i:s')
        ]
    ], 201);
}

}


