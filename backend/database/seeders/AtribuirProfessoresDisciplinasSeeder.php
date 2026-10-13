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
        // 1. Estruturar disciplinas por curso e períodos (manhã/noite/tarde) para todos os cursos cadastrados
        $this->estruturarCursosRestantes();
        $this->sincronizarGtiManha();

        // 2. Obter lista de nomes de docentes reais cadastrados (excluindo qualquer um com 'teste')
        $nomesDocentes = Docente::where('nome', 'not like', '%teste%')
            ->pluck('nome')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($nomesDocentes)) {
            $this->command?->warn('Nenhum docente cadastrado.');
            return;
        }

        // 3. Atribuir professor para cada disciplina cadastrada
        $todasDisciplinas = Disciplina::all();
        $this->command?->info("Atribuindo professores para {$todasDisciplinas->count()} disciplinas cadastradas...");

        // Obter docentes livres (sem disciplina atribuída)
        $docentesLivres = Docente::whereNull('disciplina_id')
            ->where('nome', 'not like', '%teste%')
            ->get()
            ->shuffle();

        foreach ($todasDisciplinas as $disciplina) {
            // Verifica se a disciplina já tem um docente
            $docenteExistente = Docente::where('disciplina_id', $disciplina->id)
                ->where('nome', 'not like', '%teste%')
                ->first();

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
                // Sorteia um nome da lista de docentes existentes
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

        // 4. Gerar Grade Horária da Semana (Alocações) para todos os cursos
        $this->gerarAlocacoesSemanais();
    }

    /**
     * Estrutura as matrizes curriculares dos cursos restantes (GE, PG, ADS)
     * utilizando as disciplinas reais já existentes cadastradas no sistema.
     */
    private function estruturarCursosRestantes(): void
    {
        $estruturas = [
            'GE' => [
                'nome' => 'Gestão Empresarial',
                'turnos' => ['Tarde', 'Noite'],
                'semestres' => [
                    1 => [
                        ['sigla' => 'ADG001', 'nome' => 'ADMINISTRAÇÃO GERAL'],
                        ['sigla' => 'LPO001', 'nome' => 'Comunicação e Expressão'],
                        ['sigla' => 'CON102', 'nome' => 'Contabilidade'],
                        ['sigla' => 'MAT001', 'nome' => 'MATEMÁTICA'],
                        ['sigla' => 'SOC001', 'nome' => 'SOCIOLOGIA DAS ORGANIZAÇÕES'],
                        ['sigla' => 'INF001', 'nome' => 'INFORMÁTICA APLICADA À GESTÃO'],
                    ],
                    2 => [
                        ['sigla' => 'CPO001', 'nome' => 'COMPORTAMENTO ORGANIZACIONAL'],
                        ['sigla' => 'ECO001', 'nome' => 'ECONOMIA'],
                        ['sigla' => 'EST001', 'nome' => 'ESTATÍSTICA APLICADA À GESTÃO'],
                        ['sigla' => 'PMG001', 'nome' => 'Fundamentos de Marketing'],
                        ['sigla' => 'GAM001', 'nome' => 'GESTÃO AMBIENTAL EMPRESARIAL'],
                        ['sigla' => 'MMF001', 'nome' => 'Matemática Financeira'],
                    ],
                    3 => [
                        ['sigla' => 'CCU001', 'nome' => 'CONTABILIDADE E CUSTOS'],
                        ['sigla' => 'DIR001', 'nome' => 'DIREITO EMPRESARIAL'],
                        ['sigla' => 'AGR002', 'nome' => 'Gestão de Pessoas'],
                        ['sigla' => 'LOG001', 'nome' => 'LOGÍSTICA'],
                        ['sigla' => 'OSM001', 'nome' => 'ORGANIZAÇÃO, SISTEMAS E MÉTODOS'],
                        ['sigla' => 'PIG001', 'nome' => 'PROJETO INTEGRADOR EM GESTÃO EMPRESARIAL I'],
                    ],
                    4 => [
                        ['sigla' => 'GPO001', 'nome' => 'GESTÃO DA PRODUÇÃO E OPERAÇÕES'],
                        ['sigla' => 'GMK001', 'nome' => 'GESTÃO DE MARKETING'],
                        ['sigla' => 'AGF001', 'nome' => 'Gestão Financeira'],
                        ['sigla' => 'NEL001', 'nome' => 'NEGÓCIOS ELETRÔNICOS'],
                        ['sigla' => 'RSA001', 'nome' => 'RESPONSABILIDADE SOCIAL E AMBIENTAL'],
                        ['sigla' => 'PIG002', 'nome' => 'PROJETO INTEGRADOR EM GESTÃO EMPRESARIAL II'],
                    ],
                    5 => [
                        ['sigla' => 'DNG001', 'nome' => 'DESENVOLVIMENTO DE NEGÓCIOS'],
                        ['sigla' => 'AGQ012', 'nome' => 'Gestão da Qualidade'],
                        ['sigla' => 'GOL001', 'nome' => 'GESTÃO DE OPERAÇÕES LOGÍSTICAS'],
                        ['sigla' => 'GPE001', 'nome' => 'GESTÃO DE PROJETOS EMPRESARIAS'],
                        ['sigla' => 'GFO001', 'nome' => 'GESTÃO FINANCEIRA E ORÇAMENTÁRIA'],
                        ['sigla' => 'PIG003', 'nome' => 'PROJETO INTEGRADOR EM GESTÃO EMPRESARIAL III'],
                    ],
                    6 => [
                        ['sigla' => 'GIN001', 'nome' => 'GESTÃO DA INOVAÇÃO'],
                        ['sigla' => 'GCO001', 'nome' => 'GESTÃO DO CONHECIMENTO'],
                        ['sigla' => 'GES001', 'nome' => 'GESTÃO ESTRATÉGICA'],
                        ['sigla' => 'GVC001', 'nome' => 'GOVERNANÇA CORPORATIVA'],
                        ['sigla' => 'NIN001', 'nome' => 'NEGÓCIOS INTERNACIONAIS'],
                        ['sigla' => 'PPG001', 'nome' => 'PROJETO DE PESQUISA NA GESTÃO EMPRESARIAL'],
                    ],
                ],
            ],
            'PG' => [
                'nome' => 'Processos Gerenciais',
                'turnos' => ['Tarde'],
                'semestres' => [
                    1 => [
                        ['sigla' => 'PRG001', 'nome' => 'PROCESSOS GERENCIAS'],
                        ['sigla' => 'ADG001', 'nome' => 'ADMINISTRAÇÃO GERAL'],
                        ['sigla' => 'LPO001', 'nome' => 'Comunicação e Expressão'],
                        ['sigla' => 'CON102', 'nome' => 'Contabilidade'],
                        ['sigla' => 'DIR001', 'nome' => 'DIREITO EMPRESARIAL'],
                        ['sigla' => 'PMG001', 'nome' => 'Fundamentos de Marketing'],
                        ['sigla' => 'MMF001', 'nome' => 'Matemática Financeira'],
                    ],
                    2 => [
                        ['sigla' => 'OSM001', 'nome' => 'ORGANIZAÇÃO, SISTEMAS E MÉTODOS'],
                        ['sigla' => 'AGQ012', 'nome' => 'Gestão da Qualidade'],
                        ['sigla' => 'AGR002', 'nome' => 'Gestão de Pessoas'],
                        ['sigla' => 'AGF001', 'nome' => 'Gestão Financeira'],
                        ['sigla' => 'LOG001', 'nome' => 'LOGÍSTICA'],
                        ['sigla' => 'PGE001', 'nome' => 'PLANEJAMENTO E GESTÃO ESTRATÉGICA'],
                    ],
                ],
            ],
            'ADS' => [
                'nome' => 'Análise e Desenvolvimento de Sistemas',
                'turnos' => ['Tarde'],
                'semestres' => [
                    1 => [
                        ['sigla' => 'IAL004', 'nome' => 'Algoritmos'],
                        ['sigla' => 'IAL005', 'nome' => 'Lógica de Programação'],
                        ['sigla' => 'IBD951', 'nome' => 'Banco de Dados e Aplicações'],
                        ['sigla' => 'ESW001', 'nome' => 'ENGENHARIA DE SOFTWARE'],
                        ['sigla' => 'EDA001', 'nome' => 'ESTRUTURA DE DADOS'],
                        ['sigla' => 'IRC108', 'nome' => 'Redes de Computadores'],
                    ],
                    2 => [
                        ['sigla' => 'ILP951', 'nome' => 'Programação para Internet'],
                        ['sigla' => 'PMP001', 'nome' => 'PROGRAMAÇÃO MULTIPLATAFORMA'],
                        ['sigla' => 'BIA001', 'nome' => 'BUSINESS INTELLIGENCE E BIG DATA'],
                        ['sigla' => 'CNV001', 'nome' => 'COMPUTAÇÃO EM NUVEM'],
                        ['sigla' => 'GPS001', 'nome' => 'GESTÃO ÁGIL DE PROJETOS DE SOFTWARE'],
                        ['sigla' => 'SDC001', 'nome' => 'SEGURANÇA E DEFESA CIBERNÉTICA'],
                    ],
                ],
            ],
        ];

        foreach ($estruturas as $codigo => $conf) {
            $curso = Curso::where('codigo', $codigo)
                ->orWhere('nome', $conf['nome'])
                ->first();

            if (!$curso) {
                continue;
            }

            foreach ($conf['turnos'] as $turno) {
                foreach ($conf['semestres'] as $numero => $disciplinasLista) {
                    $periodo = CursoPeriodo::where('curso_id', $curso->id)
                        ->where('turno', $turno)
                        ->where('periodo_numero', $numero)
                        ->first();

                    if (!$periodo) {
                        continue;
                    }

                    $disciplinasIds = [];

                    foreach ($disciplinasLista as $item) {
                        $nome = trim($item['nome']);
                        $sigla = strtoupper(trim($item['sigla']));

                        // Busca disciplina existente por sigla ou por nome
                        $disciplina = Disciplina::where('sigla', $sigla)->first();

                        if (!$disciplina) {
                            $disciplina = Disciplina::where('nome', $nome)
                                ->orWhere('nome', mb_strtoupper($nome))
                                ->first();
                        }

                        if ($disciplina) {
                            if (!$disciplina->sigla) {
                                $disciplina->sigla = $sigla;
                            }
                            if (!$disciplina->curso_id) {
                                $disciplina->curso_id = $curso->id;
                            }
                            if (!$disciplina->periodo_numero) {
                                $disciplina->periodo_numero = $numero;
                            }
                            if (!$disciplina->turno) {
                                $disciplina->turno = $turno;
                            }
                            $disciplina->save();
                        } else {
                            $disciplina = Disciplina::create([
                                'nome' => $nome,
                                'sigla' => $sigla,
                                'curso_id' => $curso->id,
                                'curso_periodo_id' => $periodo->id,
                                'turno' => $turno,
                                'periodo_numero' => $numero,
                            ]);
                        }

                        $disciplinasIds[] = $disciplina->id;
                    }

                    if (!empty($disciplinasIds)) {
                        $periodo->disciplinas()->syncWithoutDetaching($disciplinasIds);
                    }
                }
            }
        }
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
     * Gera alocações horárias semanais.
     * Garante que cada curso cadastrado no sistema possua 1 professor atribuído lecionando
     * durante toda a semana (Segunda a Sexta-feira) para exibição imediata na grade do aluno.
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

        // Semana corrente (Segunda a Sexta)
        $inicioSemana = now()->startOfWeek(Carbon::MONDAY);
        $diasSemana = [0, 1, 2, 3, 4]; // 0=Segunda ... 4=Sexta

        $cursos = Curso::where('ativo', true)->with('periodos.disciplinas')->get();
        $alocacoesCriadas = 0;

        // ETAPA A: Garantir 1 professor com disciplina para cada curso lecionando A SEMANA TODA (Segunda a Sexta)
        foreach ($cursos as $curso) {
            $primeiroPeriodo = $curso->periodos->first(fn($p) => $p->disciplinas->isNotEmpty());
            if (!$primeiroPeriodo) {
                continue;
            }

            $disciplinaPrincipal = $primeiroPeriodo->disciplinas->first();
            if (!$disciplinaPrincipal) {
                continue;
            }

            $docente = Docente::where('disciplina_id', $disciplinaPrincipal->id)
                ->where('nome', 'not like', '%teste%')
                ->first();

            if (!$docente) {
                $docente = Docente::where('nome', 'not like', '%teste%')->first();
                if ($docente) {
                    $docente->disciplina_id = $disciplinaPrincipal->id;
                    $docente->save();
                    $this->sincronizarProfessorModel($docente, $disciplinaPrincipal);
                }
            }

            if (!$docente) {
                continue;
            }

            $turno = $primeiroPeriodo->turno ?? ($curso->turnos[0] ?? 'Manhã');
            $blocos = $horariosTurno[$turno] ?? $horariosTurno['Manhã'];
            $horarioBloco = $blocos[0]; // Primeiro bloco de aula do turno

            // Alocar Segunda a Sexta (5 dias)
            foreach ($diasSemana as $diaOffset) {
                $dataAula = $inicioSemana->copy()->addDays($diaOffset)->toDateString();

                // Verifica se este professor já tem alocação neste dia e horário
                $alocacaoExistente = Alocacao::where('professor_id', $docente->id)
                    ->where('data', $dataAula)
                    ->where('horario_inicio', $horarioBloco['inicio'])
                    ->first();

                if ($alocacaoExistente) {
                    $alocacoesCriadas++;
                    continue;
                }

                // Busca sala disponível sem colisão (sala não ocupada no horário e prédio)
                $salaEscolhida = $salas->first(function ($sala) use ($dataAula, $horarioBloco) {
                    return !Alocacao::where('data', $dataAula)
                        ->where('horario_inicio', $horarioBloco['inicio'])
                        ->where('predio', $sala->predio)
                        ->where('sala_id', $sala->id)
                        ->exists();
                });

                if (!$salaEscolhida) {
                    continue;
                }

                Alocacao::create([
                    'professor_id' => $docente->id,
                    'sala_id' => $salaEscolhida->id,
                    'predio' => $salaEscolhida->predio ?? 'Prédio 1',
                    'data' => $dataAula,
                    'horario_inicio' => $horarioBloco['inicio'],
                    'horario_fim' => $horarioBloco['fim'],
                ]);
                $alocacoesCriadas++;
            }

            $this->command?->info("✓ Curso {$curso->nome} ({$turno}): Professor '{$docente->nome}' alocado a '{$disciplinaPrincipal->nome}' durante toda a semana (Segunda a Sexta).");
        }

        // ETAPA B: Distribuir as demais disciplinas nos períodos
        $periodos = CursoPeriodo::with(['disciplinas', 'curso'])->get();

        foreach ($periodos as $periodo) {
            $disciplinas = $periodo->disciplinas;
            if ($disciplinas->isEmpty()) {
                continue;
            }

            $turno = $periodo->turno;
            $blocos = $horariosTurno[$turno] ?? $horariosTurno['Manhã'];
            $maxSlots = count($diasSemana) * count($blocos);
            $slotIndex = 0;

            foreach ($disciplinas as $disciplina) {
                $docente = Docente::where('disciplina_id', $disciplina->id)
                    ->where('nome', 'not like', '%teste%')
                    ->first();

                if (!$docente) {
                    continue;
                }

                // Aloca 1 aula adicional na semana para as demais disciplinas
                $slot = ($slotIndex * 2) % $maxSlots;
                $diaOffset = intdiv($slot, count($blocos));
                $blocoIdx = $slot % count($blocos);
                $horario = $blocos[$blocoIdx];
                $dataAula = $inicioSemana->copy()->addDays($diaOffset)->toDateString();

                // Verifica se o docente já leciona neste horário
                $profOcupado = Alocacao::where('professor_id', $docente->id)
                    ->where('data', $dataAula)
                    ->where('horario_inicio', $horario['inicio'])
                    ->exists();

                if ($profOcupado) {
                    $slotIndex++;
                    continue;
                }

                // Busca sala disponível
                $salaEscolhida = $salas->first(function ($sala) use ($dataAula, $horario) {
                    return !Alocacao::where('data', $dataAula)
                        ->where('horario_inicio', $horario['inicio'])
                        ->where('predio', $sala->predio)
                        ->where('sala_id', $sala->id)
                        ->exists();
                });

                if ($salaEscolhida) {
                    Alocacao::create([
                        'professor_id' => $docente->id,
                        'sala_id' => $salaEscolhida->id,
                        'predio' => $salaEscolhida->predio ?? 'Prédio 1',
                        'data' => $dataAula,
                        'horario_inicio' => $horario['inicio'],
                        'horario_fim' => $horario['fim'],
                    ]);
                    $alocacoesCriadas++;
                }

                $slotIndex++;
            }
        }

        $this->command?->info("{$alocacoesCriadas} alocações semanais registradas/atualizadas com sucesso!");
    }
}
