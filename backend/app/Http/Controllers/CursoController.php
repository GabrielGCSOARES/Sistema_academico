<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\CursoPeriodo;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    /**
     * Lista todos os cursos com seus períodos e turnos estruturados.
     */
    public function listarCursos(Request $request)
    {
        $relations = ['periodos'];
        if ($request->boolean('com_disciplinas')) {
            $relations[] = 'periodos.disciplinas';
        }

        $cursos = Curso::with($relations)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return response()->json($cursos);
    }

    /**
     * Retorna os detalhes de um curso específico com períodos e disciplinas estruturadas.
     */
    public function detalharCurso($id)
    {
        $curso = Curso::with(['periodos.disciplinas', 'disciplinas'])->findOrFail($id);
        return response()->json($curso);
    }

    /**
     * Lista todos os períodos/semestres/anos dos cursos, com suporte a filtros.
     */
    public function listarPeriodos(Request $request)
    {
        $relations = ['curso'];
        if ($request->boolean('com_disciplinas') || $request->has('com_disciplinas')) {
            $relations[] = 'disciplinas';
        }

        $query = CursoPeriodo::with($relations);

        if ($request->filled('curso_id')) {
            $query->where('curso_id', $request->integer('curso_id'));
        }

        if ($request->filled('turno')) {
            $query->where('turno', $request->string('turno'));
        }

        if ($request->filled('sigla_curso_turno')) {
            $query->where('sigla_curso_turno', $request->string('sigla_curso_turno'));
        }

        if ($request->filled('periodo_numero')) {
            $query->where('periodo_numero', $request->integer('periodo_numero'));
        }

        if ($request->filled('periodo_tipo')) {
            $query->where('periodo_tipo', $request->string('periodo_tipo'));
        }

        $periodos = $query->orderBy('curso_id')->orderBy('turno')->orderBy('periodo_numero')->get();

        return response()->json($periodos);
    }

    /**
     * Cria um novo curso e gera automaticamente suas estruturas de períodos e turnos.
     */
    public function criarCurso(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255|unique:cursos,nome',
            'codigo' => 'nullable|string|max:50',
            'tipo_periodo' => 'required|in:semestral,anual',
            'total_periodos' => 'required|integer|min:1|max:12',
            'turnos' => 'required|array|min:1',
            'turnos.*' => 'string|in:Manhã,Tarde,Noite',
        ]);

        $curso = Curso::create([
            'nome' => trim($validated['nome']),
            'codigo' => !empty($validated['codigo']) ? strtoupper(trim($validated['codigo'])) : null,
            'tipo_periodo' => $validated['tipo_periodo'],
            'total_periodos' => $validated['total_periodos'],
            'turnos' => $validated['turnos'],
            'ativo' => true,
        ]);

        $isAnual = $curso->tipo_periodo === 'anual';
        $periodoTipo = $isAnual ? 'Ano' : 'Semestre';

        foreach ($curso->turnos as $turno) {
            $siglaTurno = match ($turno) {
                'Manhã' => 'M',
                'Tarde' => 'T',
                'Noite' => 'N',
                default => substr($turno, 0, 1),
            };
            $siglaCursoTurno = $curso->codigo ? "{$curso->codigo} - {$siglaTurno}" : null;

            for ($num = 1; $num <= $curso->total_periodos; $num++) {
                CursoPeriodo::create([
                    'curso_id' => $curso->id,
                    'turno' => $turno,
                    'periodo_numero' => $num,
                    'periodo_tipo' => $periodoTipo,
                    'nome_completo' => "{$curso->nome} - {$num}º {$periodoTipo} ({$turno})",
                    'sigla_curso_turno' => $siglaCursoTurno,
                ]);
            }
        }

        return response()->json([
            'message' => 'Curso e períodos criados com sucesso!',
            'curso' => $curso->load('periodos'),
        ], 201);
    }
}
