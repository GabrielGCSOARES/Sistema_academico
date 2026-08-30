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
        'nome_completo'
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
        return $this->hasMany(Disciplina::class, 'curso_periodo_id');
    }
}
