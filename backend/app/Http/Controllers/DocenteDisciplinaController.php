<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Disciplina;
use App\Models\Professor;
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
            'disciplina_id' => 'nullable|exists:disciplinas,id',
            'disciplina_nome' => 'nullable|string|max:255',
        ]);

        if ($request->filled('disciplina_nome')) {
            $nomeDisciplina = preg_replace('/\s+/', ' ', trim($request->string('disciplina_nome')->toString()));

            if ($nomeDisciplina === '') {
                return response()->json([
                    'message' => 'Informe o nome da nova disciplina.'
                ], 422);
            }

            $disciplina = Disciplina::firstOrCreate([
                'nome' => $nomeDisciplina
            ]);
            $disciplinaId = $disciplina->id;
        } else if ($request->filled('disciplina_id')) {
            $disciplinaId = $request->disciplina_id;
        } else {
            return response()->json([
                'message' => 'Informe uma disciplina existente ou o nome de uma nova disciplina.'
            ], 422);
        }

        // Verificar se a disciplina já está atribuída a outro docente
        $outroDocente = Docente::where('disciplina_id', $disciplinaId)
            ->where('id', '!=', $request->docente_id)
            ->first();

        if ($outroDocente) {
            $nomeDisciplina = Disciplina::find($disciplinaId)?->nome ?? 'Disciplina';
            return response()->json([
                'message' => "A disciplina \"{$nomeDisciplina}\" já está atribuída ao professor {$outroDocente->nome}. Desvincule primeiro antes de atribuir a outro professor."
            ], 409);
        }

        $docente = Docente::query()->findOrFail($request->docente_id);
        $docente->disciplina_id = $disciplinaId;
        $docente->save();

        $professor = Professor::find($docente->id);
        if ($professor) {
            $professor->disciplina = $docente->disciplina?->nome ?? 'Sem disciplina';
            $professor->save();
        }

        return response()->json([
            'message' => 'Vínculo docente→disciplina salvo com sucesso!',
            'docente' => $docente->load('disciplina'),
            'disciplina' => $docente->disciplina,
        ]);
    }

    public function remover($id)
    {
        $docente = Docente::findOrFail($id);
        $disciplinaAnterior = $docente->disciplina?->nome ?? 'Sem disciplina';

        $docente->disciplina_id = null;
        $docente->save();

        $professor = Professor::find($docente->id);
        if ($professor) {
            $professor->disciplina = 'Sem disciplina';
            $professor->save();
        }

        return response()->json([
            'message' => "Disciplina \"{$disciplinaAnterior}\" removida do professor {$docente->nome} com sucesso!",
            'docente' => $docente,
        ]);
    }
}

