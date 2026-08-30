<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DirectorController;
use App\Http\Controllers\DocenteDisciplinaController;
use App\Http\Controllers\CursoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('api')->group(function () {
    // Rotas para o diretor acadêmico - Professores
    Route::get('/professores', [DirectorController::class, 'getProfessores']);
    Route::post('/professores', [DirectorController::class, 'criarProfessor']);
    Route::put('/professores/{id}', [DirectorController::class, 'editarProfessor']);
    Route::delete('/professores/{id}', [DirectorController::class, 'excluirProfessor']);

    // Cursos e Períodos / Turnos
    Route::get('/cursos', [CursoController::class, 'listarCursos']);
    Route::get('/cursos/{id}', [CursoController::class, 'detalharCurso']);
    Route::get('/curso-periodos', [CursoController::class, 'listarPeriodos']);
    Route::post('/cursos', [CursoController::class, 'criarCurso']);

    // Vínculo docente → disciplina e Gestão de Disciplinas & Siglas
    Route::get('/disciplinas', [DocenteDisciplinaController::class, 'listarDisciplinas']);
    Route::post('/disciplinas', [DocenteDisciplinaController::class, 'criarDisciplina']);
    Route::put('/disciplinas/{id}/sigla', [DocenteDisciplinaController::class, 'atualizarSigla']);
    Route::put('/disciplinas/{id}/redefinir-siglas', [DocenteDisciplinaController::class, 'redefinirSiglas']);
    Route::put('/disciplinas/{id}', [DocenteDisciplinaController::class, 'atualizarDisciplina']);
    Route::delete('/disciplinas/{id}', [DocenteDisciplinaController::class, 'excluirDisciplina']);
    Route::get('/docentes', [DocenteDisciplinaController::class, 'listarDocentes']);
    Route::post('/docentes/vincular-disciplina', [DocenteDisciplinaController::class, 'vincular']);
    Route::delete('/docentes/{id}/disciplina', [DocenteDisciplinaController::class, 'remover']);

    // Salas e Alocações
    Route::get('/salas', [DirectorController::class, 'getSalas']);
    Route::get('/alocacoes/atuais', [DirectorController::class, 'getAlocacoesAtuais']);
    Route::get('/alocacoes/semana', [DirectorController::class, 'getAlocacoesSemana']);
    Route::get('/alocacoes/consulta-aluno', [DirectorController::class, 'consultarAlocacoesAluno']);
    Route::get('/alocacoes/exportar-xlsx', [DirectorController::class, 'exportarXlsx']);
    Route::get('/alocacoes/professores-lecionando/exportar-xlsx', [DirectorController::class, 'exportarProfessoresLecionandoXlsx']);
    Route::get('/alocacoes/grade/exportar-xlsx', [DirectorController::class, 'exportarGradeDiaTurnoXlsx']);
    Route::post('/alocacoes', [DirectorController::class, 'alocarProfessor']);
    Route::delete('/alocacoes/{id}', [DirectorController::class, 'desalocarProfessor']);
    Route::get('/professores/{id}/verificar-disponibilidade', [DirectorController::class, 'verificarProfessor']);
});
