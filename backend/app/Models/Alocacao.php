<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alocacao extends Model
{
    use HasFactory;

    protected $table = 'alocacoes';

    protected $fillable = [
        'professor_id',
        'sala_id',
        'predio',
        'data',
        'horario_inicio',
        'horario_fim'
    ];

    protected $casts = [
        'data' => 'date',
        'horario_inicio' => 'datetime:H:i:s',
        'horario_fim' => 'datetime:H:i:s',
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
