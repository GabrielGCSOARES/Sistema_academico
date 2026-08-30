<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Disciplina;
use App\Models\Professor;
use App\Models\Curso;
use App\Models\CursoPeriodo;
use Illuminate\Http\Request;

class DocenteDisciplinaController extends Controller
{
    /**
     * Lista disciplinas cadastradas com suporte a busca e dados enriquecidos (sigla 1, sigla 2, curso, turno).
     */
    public function listarDisciplinas(Request $request)
    {
        $query = Disciplina::with(['curso', 'cursoPeriodo']);

        if ($request->filled('busca')) {
            $busca = trim($request->string('busca')->toString());
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('sigla', 'like', "%{$busca}%")
                  ->orWhere('sigla_secundaria', 'like', "%{$busca}%");
            });
        }

        if ($request->filled('status_sigla')) {
            $status = $request->string('status_sigla');
            if ($status === 'com_sigla') {
                $query->where(function ($q) {
                    $q->whereNotNull('sigla')->where('sigla', '!=', '')
                      ->orWhere(function ($q2) {
                          $q2->whereNotNull('sigla_secundaria')->where('sigla_secundaria', '!=', '');
                      });
                });
            } elseif ($status === 'sem_sigla') {
                $query->where(function ($q) {
                    $q->where(function ($q1) {
                        $q1->whereNull('sigla')->orWhere('sigla', '');
                    })->where(function ($q2) {
                        $q2->whereNull('sigla_secundaria')->orWhere('sigla_secundaria', '');
                    });
                });
            }
        }

        $disciplinas = $query->orderBy('nome')->get()->unique('nome')->values();
        return response()->json($disciplinas);
    }

    /**
     * Atualiza as siglas (2 ou mais siglas) de uma disciplina.
     * Importante: esta ação APENAS insere/modifica a sigla e NÃO bloqueia a disciplina.
     */
    public function atualizarSigla(Request $request, $id)
    {
        $request->validate([
            'sigla' => 'nullable|string|max:255',
            'sigla_secundaria' => 'nullable|string|max:255',
        ]);

        $disciplina = Disciplina::findOrFail($id);

        $sigla = $request->has('sigla')
            ? ($request->filled('sigla') ? strtoupper(trim($request->string('sigla')->toString())) : null)
            : $disciplina->sigla;

        $siglaSec = $request->has('sigla_secundaria')
            ? ($request->filled('sigla_secundaria') ? strtoupper(trim($request->string('sigla_secundaria')->toString())) : null)
            : $disciplina->sigla_secundaria;

        Disciplina::where('nome', $disciplina->nome)->update([
            'sigla' => $sigla,
            'sigla_secundaria' => $siglaSec,
        ]);

        $disciplina->refresh();

        return response()->json([
            'message' => "Siglas da disciplina \"{$disciplina->nome}\" atualizadas com sucesso!",
            'disciplina' => $disciplina->load(['curso', 'cursoPeriodo']),
        ]);
    }

    /**
     * Redefine (limpa) as siglas de uma disciplina caso tenham sido inseridas incorretamente.
     */
    public function redefinirSiglas($id)
    {
        $disciplina = Disciplina::findOrFail($id);
        Disciplina::where('nome', $disciplina->nome)->update([
            'sigla' => null,
            'sigla_secundaria' => null,
        ]);

        $disciplina->refresh();

        return response()->json([
            'message' => "Siglas da disciplina \"{$disciplina->nome}\" redefinidas com sucesso!",
            'disciplina' => $disciplina->load(['curso', 'cursoPeriodo']),
        ]);
    }

    /**
     * Atualiza os dados de uma disciplina.
     */
    public function atualizarDisciplina(Request $request, $id)
    {
        $validated = $request->validate([
            'nome' => 'sometimes|required|string|max:255',
            'sigla' => 'nullable|string|max:50',
            'sigla_secundaria' => 'nullable|string|max:50',
            'curso_id' => 'nullable|exists:cursos,id',
            'curso_periodo_id' => 'nullable|exists:curso_periodos,id',
            'turno' => 'nullable|string|in:Manhã,Tarde,Noite',
            'periodo_numero' => 'nullable|integer|min:1|max:12',
        ]);

        $disciplina = Disciplina::findOrFail($id);

        if (isset($validated['nome'])) {
            $disciplina->nome = preg_replace('/\s+/', ' ', trim($validated['nome']));
        }
        if (array_key_exists('sigla', $validated)) {
            $disciplina->sigla = !empty($validated['sigla']) ? strtoupper(trim($validated['sigla'])) : null;
        }
        if (array_key_exists('sigla_secundaria', $validated)) {
            $disciplina->sigla_secundaria = !empty($validated['sigla_secundaria']) ? strtoupper(trim($validated['sigla_secundaria'])) : null;
        }
        if (array_key_exists('curso_id', $validated)) {
            $disciplina->curso_id = $validated['curso_id'];
        }
        if (array_key_exists('curso_periodo_id', $validated)) {
            $disciplina->curso_periodo_id = $validated['curso_periodo_id'];
        }
        if (array_key_exists('turno', $validated)) {
            $disciplina->turno = $validated['turno'];
        }
        if (array_key_exists('periodo_numero', $validated)) {
            $disciplina->periodo_numero = $validated['periodo_numero'];
        }

        $disciplina->save();

        return response()->json([
            'message' => 'Disciplina atualizada com sucesso!',
            'disciplina' => $disciplina->load(['curso', 'cursoPeriodo']),
        ]);
    }

    public function excluirDisciplina($id)
    {
        $disciplina = Disciplina::findOrFail($id);

        Docente::where('disciplina_id', $id)->update(['disciplina_id' => null]);
        Professor::where('disciplina', $disciplina->nome)->update(['disciplina' => 'Sem disciplina']);

        $nome = $disciplina->nome;
        $disciplina->delete();

        return response()->json([
            'message' => "Disciplina \"{$nome}\" excluída com sucesso!"
        ]);
    }

    public function listarDocentes()
    {
        $docentes = Docente::with('disciplina')->orderBy('nome')->get(['id', 'nome', 'disciplina_id']);
        return response()->json($docentes);
    }

    public function vincular(Request $request)
    {
        $request->validate([
            'docente_id' => 'required|exists:docentes,id',
            'disciplina_id' => 'nullable|exists:disciplinas,id',
            'disciplina_nome' => 'nullable|string|max:255',
            'disciplina_sigla' => 'nullable|string|max:50',
            'disciplina_sigla_secundaria' => 'nullable|string|max:50',
            'curso_id' => 'nullable|exists:cursos,id',
            'turno' => 'nullable|string|in:Manhã,Tarde,Noite',
        ]);

        if ($request->filled('disciplina_nome')) {
            $nomeDisciplina = preg_replace('/\s+/', ' ', trim($request->string('disciplina_nome')->toString()));

            if ($nomeDisciplina === '') {
                return response()->json([
                    'message' => 'Informe o nome da nova disciplina.'
                ], 422);
            }

            $sigla = $request->filled('disciplina_sigla') ? strtoupper(trim($request->string('disciplina_sigla')->toString())) : null;
            $siglaSec = $request->filled('disciplina_sigla_secundaria') ? strtoupper(trim($request->string('disciplina_sigla_secundaria')->toString())) : null;

            $disciplina = Disciplina::firstOrCreate(
                ['nome' => $nomeDisciplina],
                [
                    'sigla' => $sigla,
                    'sigla_secundaria' => $siglaSec,
                    'curso_id' => $request->curso_id,
                    'turno' => $request->turno,
                ]
            );
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
