<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Archivo; // Importamos el modelo correcto
use App\Models\User;

class FileComment extends Model {
    use HasFactory;

    protected $fillable = ['archivo_id', 'user_id', 'comment'];

    // Relación con la tabla archivos
    public function archivo() {
        return $this->belongsTo(Archivo::class, 'archivo_id');
    }

    // Relación con la tabla users
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

}

