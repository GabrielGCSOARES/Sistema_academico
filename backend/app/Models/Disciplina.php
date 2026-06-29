<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disciplina extends Model
{
    protected $fillable = [
        'nome'
    ];

    public function docentes()
    {
        return $this->hasMany(Docente::class);
    }
}

