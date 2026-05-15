<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use App\Models\Sala;
use App\Models\Alocacao;
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
        $alocacoes = Alocacao::with(['professor', 'sala'])
            ->where('data', now()->toDateString())
            ->where(function($query) {
                $query->whereNull('horario_fim')
                      ->orWhere('horario_fim', '>=', now());
            })
            ->get();
        
        return response()->json($alocacoes);
    }

    public function alocarProfessor(Request $request)
    {
        $request->validate([
            'professor_id' => 'required|exists:professores,id',
            'sala_id' => 'required|exists:salas,id',
            'data' => 'required|date',
            'horario_atual' => 'required'
        ]);

        try {
            DB::beginTransaction();

            // Verificar se o professor já está alocado neste horário
            $professorAlocado = Alocacao::where('professor_id', $request->professor_id)
                ->where('data', $request->data)
                ->where(function($query) use ($request) {
                    $horario = date('H:i:s', strtotime($request->horario_atual));
                    $query->where('horario_inicio', '<=', $horario)
                          ->where(function($q) use ($horario) {
                              $q->whereNull('horario_fim')
                                ->orWhere('horario_fim', '>=', $horario);
                          });
                })
                ->lockForUpdate()
                ->first();

            if ($professorAlocado) {
                DB::rollBack();
                return response()->json([
                    'message' => 'ATENÇÃO: Este professor já está lecionando em outra sala neste horário!',
                    'alocacao_atual' => $professorAlocado
                ], 409);
            }

            // Verificar se a sala já está ocupada
            $salaOcupada = Alocacao::where('sala_id', $request->sala_id)
                ->where('data', $request->data)
                ->where(function($query) use ($request) {
                    $horario = date('H:i:s', strtotime($request->horario_atual));
                    $query->where('horario_inicio', '<=', $horario)
                          ->where(function($q) use ($horario) {
                              $q->whereNull('horario_fim')
                                ->orWhere('horario_fim', '>=', $horario);
                          });
                })
                ->lockForUpdate()
                ->first();

            if ($salaOcupada) {
                DB::rollBack();
                return response()->json([
                    'message' => 'ATENÇÃO: Esta sala já está ocupada neste horário!'
                ], 423);
            }

            // Criar a alocação
            $alocacao = Alocacao::create([
                'professor_id' => $request->professor_id,
                'sala_id' => $request->sala_id,
                'data' => $request->data,
                'horario_inicio' => $request->horario_atual,
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
            
            // Registrar horário de fim
            $alocacao->update([
                'horario_fim' => now()
            ]);

            return response()->json([
                'message' => 'Professor desalocado com sucesso!',
                'alocacao' => $alocacao
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
            
            $alocacaoAtual = Alocacao::where('professor_id', $professorId)
                ->where('data', now()->toDateString())
                ->where(function($query) {
                    $query->whereNull('horario_fim')
                          ->orWhere('horario_fim', '>=', now());
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