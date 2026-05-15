<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Professor;
use App\Models\Sala;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Criar professores de exemplo
        Professor::updateOrCreate(['email' => 'silva@universidade.edu'], [
            'nome' => 'Dr. Silva',
            'disciplina' => 'Cálculo I',
            'curso' => 'Engenharia',
            'ativo' => true
        ]);

        Professor::updateOrCreate(['email' => 'santos@universidade.edu'], [
            'nome' => 'Dra. Santos',
            'disciplina' => 'Física',
            'curso' => 'Física',
            'ativo' => true
        ]);

        Professor::updateOrCreate(['email' => 'oliveira@universidade.edu'], [
            'nome' => 'Prof. Oliveira',
            'disciplina' => 'Programação',
            'curso' => 'Ciência da Computação',
            'ativo' => true
        ]);

        // Criar salas de exemplo
        Sala::updateOrCreate(['nome' => 'Sala 101'], [
            'capacidade' => 40,
            'localizacao' => 'Bloco A - 1º Andar',
            'ativa' => true
        ]);

        Sala::updateOrCreate(['nome' => 'Sala 102'], [
            'capacidade' => 35,
            'localizacao' => 'Bloco A - 1º Andar',
            'ativa' => true
        ]);

        Sala::updateOrCreate(['nome' => 'Laboratório 201'], [
            'capacidade' => 25,
            'localizacao' => 'Bloco B - 2º Andar',
            'ativa' => true
        ]);

        Sala::updateOrCreate(['nome' => 'Auditório'], [
            'capacidade' => 100,
            'localizacao' => 'Bloco C - Térreo',
            'ativa' => true
        ]);
    }
}
