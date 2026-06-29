<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Disciplina;
use Illuminate\Http\Request;

class DocenteDisciplinaController extends Controller
{
    public function listarDisciplinas()
    {
        $disciplinas = Disciplina::orderBy('nome')->get(['id', 'nome']);
        return response()->json($disciplinas);
    }

    public function listarDocentes()
    {
        $docentes = Docente::orderBy('nome')->get(['id', 'nome', 'disciplina_id']);
        return response()->json($docentes);
    }

    public function vincular(Request $request)
    {
        $request->validate([
            'docente_id' => 'required|exists:docentes,id',
            'disciplina_id' => 'required|exists:disciplinas,id',
        ]);

        $docente = Docente::query()->findOrFail($request->docente_id);
        $docente->disciplina_id = $request->disciplina_id;
        $docente->save();

        return response()->json([
            'message' => 'Vínculo docente→disciplina salvo com sucesso!',
            'docente' => $docente->load('disciplina'),
        ]);
    }
}

