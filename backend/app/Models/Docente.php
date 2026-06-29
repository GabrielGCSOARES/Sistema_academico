<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    protected $fillable = [
        'nome',
        'disciplina_id'
    ];

    public function disciplina()
    {
        return $this->belongsTo(Disciplina::class);
    }
}

