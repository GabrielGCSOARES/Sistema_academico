<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alocacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'professor_id',
        'sala_id',
        'data',
        'horario_inicio',
        'horario_fim'
    ];

    protected $casts = [
        'data' => 'date',
        'horario_inicio' => 'datetime',
        'horario_fim' => 'datetime',
    ];

    public function professor()
    {
        return $this->belongsTo(Professor::class);
    }

    public function sala()
    {
        return $this->belongsTo(Sala::class);
    }
}