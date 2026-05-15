<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DirectorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('api')->group(function () {
    // Rotas para o diretor acadêmico
    Route::get('/professores', [DirectorController::class, 'getProfessores']);
    Route::get('/salas', [DirectorController::class, 'getSalas']);
    Route::get('/alocacoes/atuais', [DirectorController::class, 'getAlocacoesAtuais']);
    Route::post('/alocacoes', [DirectorController::class, 'alocarProfessor']);
    Route::delete('/alocacoes/{id}', [DirectorController::class, 'desalocarProfessor']);
    Route::get('/professores/{id}/verificar-disponibilidade', [DirectorController::class, 'verificarProfessor']);
});