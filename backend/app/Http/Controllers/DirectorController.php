<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use App\Models\Sala;
use App\Models\Alocacao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DirectorController extends Controller
{
    public function getProfessores()
    {
        $professores = Professor::where('ativo', true)
            ->with(['alocacoesAtivas'])
            ->get();
        
        return response()->json($professores);
    }

    public function getSalas()
    {
        $salas = Sala::where('ativa', true)
            ->with(['alocacoesAtivas.professor'])
            ->get();
        
        return response()->json($salas);
    }

    public function getAlocacoesAtuais()
    {
        $horarioAtual = now()->format('H:i:s');

        $alocacoes = Alocacao::with(['professor', 'sala'])
            ->where('data', now()->toDateString())
            ->where('horario_inicio', '<=', $horarioAtual)
            ->where(function($query) use ($horarioAtual) {
                $query->whereNull('horario_fim')
                      ->orWhere('horario_fim', '>=', $horarioAtual);
            })
            ->get();
        
        return response()->json($alocacoes);
    }

    public function getAlocacoesSemana(Request $request)
    {
        $inicioSemana = $request->filled('inicio')
            ? Carbon::parse($request->inicio)->startOfDay()
            : now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $fimSemana = (clone $inicioSemana)->addDays(4)->endOfDay();

        $alocacoes = Alocacao::with(['professor', 'sala'])
            ->whereBetween('data', [$inicioSemana->toDateString(), $fimSemana->toDateString()])
            ->get();

        return response()->json($alocacoes);
    }

    public function alocarProfessor(Request $request)
    {
        $request->validate([
            'professor_id' => 'required|exists:professores,id',
            'sala_id' => 'required|exists:salas,id',
            'predio' => 'nullable|string|max:50',
            'data' => 'required|date',
            'horario_atual' => 'nullable',
            'horario_inicio' => 'nullable',
            'horario_fim' => 'nullable'
        ]);

        try {
            DB::beginTransaction();

            $predio = $request->predio ?? 'Prédio 1';
            $horarioBase = $request->horario_inicio ?? $request->horario_atual;

            if (!$horarioBase) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Horário inicial é obrigatório.'
                ], 422);
            }

            $horarioInicio = date('H:i:s', strtotime($horarioBase));
            $horarioFim = $request->filled('horario_fim')
                ? date('H:i:s', strtotime($request->horario_fim))
                : null;

            // Verificar se o professor já está alocado neste intervalo
            $professorAlocado = Alocacao::where('professor_id', $request->professor_id)
                ->where('data', $request->data)
                ->where(function($query) use ($horarioInicio, $horarioFim) {
                    if ($horarioFim) {
                        $query->where('horario_inicio', '<', $horarioFim)
                              ->where(function($q) use ($horarioInicio) {
                                  $q->whereNull('horario_fim')
                                    ->orWhere('horario_fim', '>', $horarioInicio);
                              });
                    } else {
                        $query->where('horario_inicio', '<=', $horarioInicio)
                              ->where(function($q) use ($horarioInicio) {
                                  $q->whereNull('horario_fim')
                                    ->orWhere('horario_fim', '>=', $horarioInicio);
                              });
                    }
                })
                ->lockForUpdate()
                ->first();

            if ($professorAlocado) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Este professor já está lecionando em outra sala neste horário.',
                    'alocacao_atual' => $professorAlocado
                ], 409);
            }

            // Verificar se a sala já está ocupada neste prédio e intervalo
            $salaOcupada = Alocacao::where('sala_id', $request->sala_id)
                ->where('predio', $predio)
                ->where('data', $request->data)
                ->where(function($query) use ($horarioInicio, $horarioFim) {
                    if ($horarioFim) {
                        $query->where('horario_inicio', '<', $horarioFim)
                              ->where(function($q) use ($horarioInicio) {
                                  $q->whereNull('horario_fim')
                                    ->orWhere('horario_fim', '>', $horarioInicio);
                              });
                    } else {
                        $query->where('horario_inicio', '<=', $horarioInicio)
                              ->where(function($q) use ($horarioInicio) {
                                  $q->whereNull('horario_fim')
                                    ->orWhere('horario_fim', '>=', $horarioInicio);
                              });
                    }
                })
                ->lockForUpdate()
                ->first();

            if ($salaOcupada) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Esta sala já está ocupada neste horário.'
                ], 423);
            }

            // Criar a alocação
            $alocacao = Alocacao::create([
                'professor_id' => $request->professor_id,
                'sala_id' => $request->sala_id,
                'predio' => $predio,
                'data' => $request->data,
                'horario_inicio' => $horarioInicio,
                'horario_fim' => $horarioFim,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Professor alocado com sucesso!',
                'alocacao' => $alocacao->load(['professor', 'sala'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao processar alocação: ' . $e->getMessage()
            ], 500);
        }
    }

    public function desalocarProfessor($id)
    {
        try {
            $alocacao = Alocacao::findOrFail($id);
            $alocacao->delete();

            return response()->json([
                'message' => 'Professor removido da alocação com sucesso.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao desalocar professor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verificarProfessor($professorId)
    {
        try {
            $professor = Professor::findOrFail($professorId);
            $horarioAtual = now()->format('H:i:s');
            
            $alocacaoAtual = Alocacao::where('professor_id', $professorId)
                ->where('data', now()->toDateString())
                ->where('horario_inicio', '<=', $horarioAtual)
                ->where(function($query) use ($horarioAtual) {
                    $query->whereNull('horario_fim')
                          ->orWhere('horario_fim', '>=', $horarioAtual);
                })
                ->with('sala')
                ->first();

            return response()->json([
                'professor' => $professor,
                'esta_alocado' => !is_null($alocacaoAtual),
                'alocacao_atual' => $alocacaoAtual
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao verificar professor: ' . $e->getMessage()
            ], 500);
        }
    }
}
