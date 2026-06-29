<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DirectorController;
use App\Http\Controllers\DocenteDisciplinaController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('api')->group(function () {
    // Rotas para o diretor acadêmico
    Route::get('/professores', [DirectorController::class, 'getProfessores']);

    // Vínculo docente → disciplina
    Route::get('/disciplinas', [DocenteDisciplinaController::class, 'listarDisciplinas']);
    Route::get('/docentes', [DocenteDisciplinaController::class, 'listarDocentes']);
    Route::post('/docentes/vincular-disciplina', [DocenteDisciplinaController::class, 'vincular']);

    Route::get('/salas', [DirectorController::class, 'getSalas']);
    Route::get('/alocacoes/atuais', [DirectorController::class, 'getAlocacoesAtuais']);
    Route::get('/alocacoes/semana', [DirectorController::class, 'getAlocacoesSemana']);
    Route::post('/alocacoes', [DirectorController::class, 'alocarProfessor']);
    Route::delete('/alocacoes/{id}', [DirectorController::class, 'desalocarProfessor']);
    Route::get('/professores/{id}/verificar-disponibilidade', [DirectorController::class, 'verificarProfessor']);
});
