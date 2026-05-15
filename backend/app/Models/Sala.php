<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'capacidade',
        'localizacao',
        'ativa'
    ];

    public function alocacoes()
    {
        return $this->hasMany(Alocacao::class);
    }

    public function alocacoesAtivas()
    {
        return $this->hasMany(Alocacao::class)->where('data', now()->toDateString());
    }
}