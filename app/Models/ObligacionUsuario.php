<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObligacionUsuario extends Model
{
    use HasFactory;

    protected $table = 'obligacion_usuario'; 

    protected $fillable = ['user_id', 'numero_evidencia', 'view']; // Cambiamos numero_requisito a numero_evidencia

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
