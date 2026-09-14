<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoPeriodo extends Model
{
    use HasFactory;

    protected $table = 'curso_periodos';

    protected $fillable = [
        'curso_id',
        'turno', // 'Manhã', 'Tarde', 'Noite'
        'periodo_numero', // 1..6 ou 1..2
        'periodo_tipo', // 'Semestre', 'Ano'
        'nome_completo',
        'sigla_curso_turno',
    ];

    protected $casts = [
        'periodo_numero' => 'integer',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function disciplinas()
    {
        return $this->belongsToMany(Disciplina::class, 'curso_periodo_disciplina')
            ->withTimestamps()
            ->orderBy('sigla')
            ->orderBy('nome');
    }

    public function disciplinasDiretas()
    {
        return $this->hasMany(Disciplina::class, 'curso_periodo_id');
    }
}
