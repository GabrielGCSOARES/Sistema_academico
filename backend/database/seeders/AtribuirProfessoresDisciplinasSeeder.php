<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curso;
use App\Models\CursoPeriodo;
use App\Models\Disciplina;
use App\Models\Docente;
use App\Models\Professor;
use App\Models\Sala;
use App\Models\Alocacao;
use Carbon\Carbon;

class AtribuirProfessoresDisciplinasSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Sincronizar períodos de GTI (Manhã e Noite)
        $this->sincronizarGtiManha();

        // 2. Obter lista de nomes de docentes reais cadastrados
        $nomesDocentes = Docente::pluck('nome')->filter()->unique()->values()->all();
        if (empty($nomesDocentes)) {
            $this->command?->warn('Nenhum docente cadastrado.');
            return;
        }

        // 3. Atribuir professor aleatório para cada disciplina cadastrada
        $todasDisciplinas = Disciplina::all();
        $this->command?->info("Atribuindo professores para {$todasDisciplinas->count()} disciplinas cadastradas...");

        // Obter docentes livres (sem disciplina atribuída)
        $docentesLivres = Docente::whereNull('disciplina_id')->get()->shuffle();

        foreach ($todasDisciplinas as $disciplina) {
            // Verifica se a disciplina já tem um docente
            $docenteExistente = Docente::where('disciplina_id', $disciplina->id)->first();
            if ($docenteExistente) {
                // Já tem professor lecionando
                $this->sincronizarProfessorModel($docenteExistente, $disciplina);
                continue;
            }

            // Se há docentes livres sem disciplina, usa um deles
            if ($docentesLivres->isNotEmpty()) {
                $docente = $docentesLivres->pop();
                $docente->disciplina_id = $disciplina->id;
                $docente->save();
            } else {
                // Sorteia um nome aleatório da lista de docentes existentes e cria um registro
                $nomeSorteado = $nomesDocentes[array_rand($nomesDocentes)];
                $docente = Docente::create([
                    'nome' => $nomeSorteado,
                    'disciplina_id' => $disciplina->id,
                ]);
            }

            $this->sincronizarProfessorModel($docente, $disciplina);
        }

        $totalComDocente = Docente::whereNotNull('disciplina_id')->count();
        $this->command?->info("{$totalComDocente} disciplinas agora possuem professores lecionando!");

        // 4. Gerar Grade Horária da Semana (Alocações) para os cursos cadastrados
        $this->gerarAlocacoesSemanais();
    }

    /**
     * Garante que o período da Manhã de GTI tenha as mesmas disciplinas estruturadas do período da Noite.
     */
    private function sincronizarGtiManha(): void
    {
        $gti = Curso::where('codigo', 'GTI')
            ->orWhere('nome', 'Gestão da Tecnologia da Informação')
            ->first();

        if (!$gti) {
            return;
        }

        for ($semestre = 1; $semestre <= 6; $semestre++) {
            $periodoNoite = CursoPeriodo::where('curso_id', $gti->id)
                ->where('turno', 'Noite')
                ->where('periodo_numero', $semestre)
                ->first();

            $periodoManha = CursoPeriodo::where('curso_id', $gti->id)
                ->where('turno', 'Manhã')
                ->where('periodo_numero', $semestre)
                ->first();

            if ($periodoNoite && $periodoManha) {
                $disciplinaIds = $periodoNoite->disciplinas()->pluck('disciplinas.id')->all();
                if (!empty($disciplinaIds)) {
                    $periodoManha->disciplinas()->syncWithoutDetaching($disciplinaIds);
                }
            }
        }
    }

    /**
     * Sincroniza o registro na tabela professores para manter paridade com docentes.
     */
    private function sincronizarProfessorModel(Docente $docente, Disciplina $disciplina): void
    {
        $cursoNome = $disciplina->curso?->nome ?: 'Não informado';

        Professor::updateOrCreate(
            ['id' => $docente->id],
            [
                'nome' => $docente->nome,
                'email' => sprintf('professor%d@fatec.edu', $docente->id),
                'disciplina' => $disciplina->nome,
                'curso' => $cursoNome,
                'ativo' => true,
            ]
        );
    }

    /**
     * Gera alocações horárias semanais para as disciplinas dos cursos, permitindo que a Grade da Semana funcione.
     */
    private function gerarAlocacoesSemanais(): void
    {
        $salas = Sala::where('ativa', true)->get();
        if ($salas->isEmpty()) {
            return;
        }

        // Horários por turno (2 blocos por dia)
        $horariosTurno = [
            'Manhã' => [
                ['inicio' => '07:40:00', 'fim' => '09:20:00'],
                ['inicio' => '09:30:00', 'fim' => '11:10:00'],
            ],
            'Tarde' => [
                ['inicio' => '14:00:00', 'fim' => '15:40:00'],
                ['inicio' => '15:50:00', 'fim' => '17:30:00'],
            ],
            'Noite' => [
                ['inicio' => '19:00:00', 'fim' => '20:40:00'],
                ['inicio' => '20:50:00', 'fim' => '22:30:00'],
            ],
        ];

        // Definir a semana corrente (Segunda a Sábado)
        $inicioSemana = now()->startOfWeek(Carbon::MONDAY);
        $diasSemana = [0, 1, 2, 3, 4, 5]; // 0=Segunda, ..., 5=Sábado

        $periodos = CursoPeriodo::with(['disciplinas', 'curso'])->get();

        $alocacoesCriadas = 0;

        foreach ($periodos as $periodo) {
            $disciplinas = $periodo->disciplinas;
            if ($disciplinas->isEmpty()) {
                continue;
            }

            $turno = $periodo->turno;
            $blocos = $horariosTurno[$turno] ?? $horariosTurno['Manhã'];

            // Distribui as disciplinas nos dias da semana e blocos
            $slotIndex = 0;
            $maxSlots = count($diasSemana) * count($blocos);

            foreach ($disciplinas as $disciplina) {
                $docente = Docente::where('disciplina_id', $disciplina->id)->first();
                if (!$docente) {
                    continue;
                }

                // Alocar cada disciplina em 1 ou 2 slots na semana
                $qtdAulas = 2;
                for ($k = 0; $k < $qtdAulas; $k++) {
                    $slot = ($slotIndex + ($k * 3)) % $maxSlots;
                    $diaOffset = intdiv($slot, count($blocos));
                    $blocoIdx = $slot % count($blocos);
                    $horario = $blocos[$blocoIdx];
                    $dataAula = $inicioSemana->copy()->addDays($diaOffset)->toDateString();

                    // Escolhe uma sala disponível sem colisão
                    $salaEscolhida = null;
                    foreach ($salas->shuffle() as $sala) {
                        $colisao = Alocacao::where('data', $dataAula)
                            ->where('horario_inicio', $horario['inicio'])
                            ->where(function ($q) use ($sala, $docente) {
                                $q->where(function ($qs) use ($sala) {
                                    $qs->where('sala_id', $sala->id)
                                       ->where('predio', $sala->predio);
                                })->orWhere('professor_id', $docente->id);
                            })
                            ->exists();

                        if (!$colisao) {
                            $salaEscolhida = $sala;
                            break;
                        }
                    }

                    if ($salaEscolhida) {
                        Alocacao::updateOrCreate(
                            [
                                'professor_id' => $docente->id,
                                'data' => $dataAula,
                                'horario_inicio' => $horario['inicio'],
                            ],
                            [
                                'sala_id' => $salaEscolhida->id,
                                'predio' => $salaEscolhida->predio,
                                'horario_fim' => $horario['fim'],
                            ]
                        );
                        $alocacoesCriadas++;
                    }
                }

                $slotIndex++;
            }
        }

        $this->command?->info("{$alocacoesCriadas} alocações semanais registradas/atualizadas com sucesso!");
    }
}
