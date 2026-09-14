<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disciplina extends Model
{
    protected $fillable = [
        'nome',
        'sigla',
        'sigla_secundaria',
        'curso_id',
        'curso_periodo_id',
        'turno',
        'periodo_numero',
    ];

    protected $casts = [
        'periodo_numero' => 'integer',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function cursoPeriodo()
    {
        return $this->belongsTo(CursoPeriodo::class, 'curso_periodo_id');
    }

    public function cursoPeriodos()
    {
        return $this->belongsToMany(CursoPeriodo::class, 'curso_periodo_disciplina', 'disciplina_id', 'curso_periodo_id')
            ->withTimestamps();
    }

    public function docentes()
    {
        return $this->hasMany(Docente::class);
    }
}

