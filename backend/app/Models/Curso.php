<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    protected $table = 'cursos';

    protected $fillable = [
        'nome',
        'codigo',
        'tipo_periodo', // 'semestral' ou 'anual'
        'total_periodos', // 6 ou 2
        'turnos', // ['Manhã', 'Noite'] etc.
        'ativo'
    ];

    protected $casts = [
        'turnos' => 'array',
        'ativo' => 'boolean',
        'total_periodos' => 'integer',
    ];

    public function periodos()
    {
        return $this->hasMany(CursoPeriodo::class)->orderBy('turno')->orderBy('periodo_numero');
    }

    public function disciplinas()
    {
        return $this->hasMany(Disciplina::class);
    }
}
