<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Authorization extends Model
{
    
    protected $table = 'authorizations';

    // Permitir asignación en estos campos
    protected $fillable = [
        'name',
        'guard_name'
    ];

    // Relación polimórfica con los modelos (User)
    public function models(): MorphToMany
    {
        return $this->morphToMany(User::class, 'model', 'model_has_authorizations', 'authorization_id', 'model_id');
    }
}
