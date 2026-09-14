<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curso;
use App\Models\CursoPeriodo;
use App\Models\Disciplina;

class GtiDisciplinasSeeder extends Seeder
{
    /**
     * Mapeamento completo das 49 disciplinas do curso Gestão da Tecnologia da Informação (Manhã) - GTI - Matutino.
     */
    private const DISCIPLINAS_GTI = [
        // SEMESTRE 1 (7 disciplinas)
        ['semestre' => 1, 'sigla' => 'AGO006', 'nome' => 'Processos Gerenciais', 'aliases' => ['PROCESSOS GERENCIAS', 'PROCESSOS GERENCIAIS']],
        ['semestre' => 1, 'sigla' => 'IAL004', 'nome' => 'Algoritmos', 'aliases' => ['ALGORITMOS']],
        ['semestre' => 1, 'sigla' => 'ITI001', 'nome' => 'Fundamentos de Tecnologia da Informação', 'aliases' => ['FUNDAMENTOS DE TECNOLOGIA DA INFORMAÇÃO']],
        ['semestre' => 1, 'sigla' => 'LIN100', 'nome' => 'Inglês I', 'aliases' => ['INGLÊS I', 'LÍNGUA INGLESA I']],
        ['semestre' => 1, 'sigla' => 'LPO001', 'nome' => 'Comunicação e Expressão', 'aliases' => ['COMUNICAÇÃO E EXPRESSÃO']],
        ['semestre' => 1, 'sigla' => 'MAT006', 'nome' => 'Matemática Discreta', 'aliases' => ['MATEMÁTICA DISCRETA']],
        ['semestre' => 1, 'sigla' => 'TAA001', 'nome' => 'Atividades Acadêmico-científico-culturais I', 'aliases' => ['ATIVIDADES ACADÊMICO-CIENTÍFICO-CULTURAIS I', 'ATIVIDADES ACADEMICO CIENTIFICO CULTURAIS I']],

        // SEMESTRE 2 (9 disciplinas)
        ['semestre' => 2, 'sigla' => 'IGS001', 'nome' => 'Gestão de Sistemas Operacionais', 'aliases' => ['GESTÃO DE SISTEMAS OPERACIONAIS']],
        ['semestre' => 2, 'sigla' => 'IHW001', 'nome' => 'Laboratório de Hardware', 'aliases' => ['LABORATÓRIO DE HARDWARE']],
        ['semestre' => 2, 'sigla' => 'ILP100', 'nome' => 'Linguagens de Programação', 'aliases' => ['LÍNGUAGENS DE PROGRAMAÇÃO', 'LINGUAGENS DE PROGRAMAÇÃO']],
        ['semestre' => 2, 'sigla' => 'IMP951', 'nome' => 'Modelagem de Processos', 'aliases' => ['MODELAGEM DE PROCESSOS']],
        ['semestre' => 2, 'sigla' => 'LIN200', 'nome' => 'Inglês II', 'aliases' => ['INGLÊS II', 'LÍNGUA INGLESA II']],
        ['semestre' => 2, 'sigla' => 'MMF001', 'nome' => 'Matemática Financeira', 'aliases' => ['MATEMÁTICA FINANCEIRA']],
        ['semestre' => 2, 'sigla' => 'MPT004', 'nome' => 'Metodologia da Pesquisa Científico-tecnológica', 'aliases' => ['METODOLOGIA DA PESQUISA CIENTÍFICO – TECNOLÓGICA', 'METODOLOGIA DA PESQUISA CIENTÍFICO-TECNOLÓGICA', 'METODOLOGIA DA PESQUISA CIENTIFICO TECNOLOGICA']],
        ['semestre' => 2, 'sigla' => 'TAA002', 'nome' => 'Atividades Acadêmico-científico-culturais II', 'aliases' => ['ATIVIDADES ACADÊMICO-CIENTÍFICO-CULTURAIS II', 'ATIVIDADES ACADEMICO CIENTIFICO CULTURAIS II']],
        ['semestre' => 2, 'sigla' => 'TGI103', 'nome' => 'Atividades de Projetos I (Modelagem de Processos)', 'aliases' => ['ATIVIDADES DE PROJETOS I (MODELAGEM DE PROCESSOS)']],

        // SEMESTRE 3 (9 disciplinas)
        ['semestre' => 3, 'sigla' => 'AGA001', 'nome' => 'Gestão Ambiental', 'aliases' => ['GESTÃO AMBIENTAL']],
        ['semestre' => 3, 'sigla' => 'AGR002', 'nome' => 'Gestão de Pessoas', 'aliases' => ['GESTÃO DE PESSOAS']],
        ['semestre' => 3, 'sigla' => 'CON102', 'nome' => 'Contabilidade', 'aliases' => ['CONTABILIDADE']],
        ['semestre' => 3, 'sigla' => 'IBD951', 'nome' => 'Banco de Dados e Aplicações', 'aliases' => ['BANCO DE DADOS E APLICAÇÕES']],
        ['semestre' => 3, 'sigla' => 'IES951', 'nome' => 'Engenharia de Software e Aplicações', 'aliases' => ['ENGENHARIA DE SOFTWARE E APLICAÇÕES']],
        ['semestre' => 3, 'sigla' => 'LIN300', 'nome' => 'Inglês III', 'aliases' => ['INGLÊS III']],
        ['semestre' => 3, 'sigla' => 'MET001', 'nome' => 'Estatística', 'aliases' => ['ESTATÍSTICA']],
        ['semestre' => 3, 'sigla' => 'TGI203', 'nome' => 'Atividades de Projetos II (Engenharia de Software)', 'aliases' => ['ATIVIDADES DE PROJETOS II (ENGENHARIA DE SOFTWARE)']],
        ['semestre' => 3, 'sigla' => 'TGI303', 'nome' => 'Atividades de Projetos III (Banco de Dados)', 'aliases' => ['ATIVIDADES DE PROJETOS III (BANCO DE DADOS)', 'ATIVIDADES DE PROJETOS III']],

        // SEMESTRE 4 (7 disciplinas)
        ['semestre' => 4, 'sigla' => 'AGF001', 'nome' => 'Gestão Financeira', 'aliases' => ['GESTÃO FINANCEIRA']],
        ['semestre' => 4, 'sigla' => 'AGP001', 'nome' => 'Gestão da Produção', 'aliases' => ['GESTÃO DA PRODUÇÃO']],
        ['semestre' => 4, 'sigla' => 'ILP951', 'nome' => 'Programação para Internet', 'aliases' => ['PROGRAMAÇÃO PARA INTERNET']],
        ['semestre' => 4, 'sigla' => 'IRC108', 'nome' => 'Redes de Computadores', 'aliases' => ['REDES DE COMPUTADORES']],
        ['semestre' => 4, 'sigla' => 'LIN400', 'nome' => 'Inglês IV', 'aliases' => ['INGLÊS IV']],
        ['semestre' => 4, 'sigla' => 'PMG007', 'nome' => 'Fundamentos de Marketing', 'aliases' => ['FUNDAMENTOS DE MARKETING']],
        ['semestre' => 4, 'sigla' => 'TGI403', 'nome' => 'Atividades de Projetos IV (Programação para Internet)', 'aliases' => ['ATIVIDADES DE PROJETOS IV (PROGRAMAÇÃO PARA INTERNET)']],

        // SEMESTRE 5 (7 disciplinas)
        ['semestre' => 5, 'sigla' => 'AGE001', 'nome' => 'Planejamento e Gestão Estratégica', 'aliases' => ['PLANEJAMENTO E GESTÃO ESTRATÉGICA']],
        ['semestre' => 5, 'sigla' => 'GPJ002', 'nome' => 'Gestão de Projetos', 'aliases' => ['GESTÃO DE PROJETOS']],
        ['semestre' => 5, 'sigla' => 'IGT002', 'nome' => 'Gestão de Tecnologia da Informação', 'aliases' => ['GESTÃO DE TECNOLOGIA DA INFORMAÇÃO']],
        ['semestre' => 5, 'sigla' => 'IPT001', 'nome' => 'Projetos de Tecnologia da Informação I', 'aliases' => ['PROJETOS DE TECNOLOGIA DA INFORMAÇÃO I']],
        ['semestre' => 5, 'sigla' => 'ISI951', 'nome' => 'Sistemas Integrados de Gestão e Aplicações', 'aliases' => ['SISTEMAS INTEGRADOS DE GESTÃO E APLICAÇÕES', 'SISTEMAS INTEGRADOS DE GESTÃO']],
        ['semestre' => 5, 'sigla' => 'LIN500', 'nome' => 'Inglês V', 'aliases' => ['INGLÊS V']],
        ['semestre' => 5, 'sigla' => 'TGI503', 'nome' => 'Atividades de Projetos V (Sistemas Integrados de Gestão)', 'aliases' => ['ATIVIDADES DE PROJETOS V (SISTEMAS INTEGRADOS DE GESTÃO)']],

        // SEMESTRE 6 (10 disciplinas)
        ['semestre' => 6, 'sigla' => 'AGE003', 'nome' => 'Gestão Econômica', 'aliases' => ['GESTÃO ECONÔMICA']],
        ['semestre' => 6, 'sigla' => 'DDI001', 'nome' => 'Legislação Aplicada à Tecnologia da Informação', 'aliases' => ['LEGISLAÇÃO APLICADA À TECNOLOGIA DA INFORMAÇÃO']],
        ['semestre' => 6, 'sigla' => 'EGI004', 'nome' => 'Estágio Supervisionado em Gestão da Tecnologia da Informação', 'aliases' => ['ESTÁGIO SUPERVISIONADO EM GESTÃO DA TECNOLOGIA DA INFORMAÇÃO', 'ESTÁGIO SUPERVISIONADO']],
        ['semestre' => 6, 'sigla' => 'IIN001', 'nome' => 'Inteligência de Negócios', 'aliases' => ['INTELIGÊNCIA DE NEGÓCIOS']],
        ['semestre' => 6, 'sigla' => 'INE001', 'nome' => 'Negócios Eletrônicos', 'aliases' => ['NEGÓCIOS ELETRÔNICOS']],
        ['semestre' => 6, 'sigla' => 'IPT951', 'nome' => 'Projetos de Tecnologia da Informação II', 'aliases' => ['PROJETOS DE TECNOLOGIA DA INFORMAÇÃO II']],
        ['semestre' => 6, 'sigla' => 'LIN600', 'nome' => 'Inglês VI', 'aliases' => ['INGLÊS VI']],
        ['semestre' => 6, 'sigla' => 'TAI001', 'nome' => 'Tópicos Avançados em Tecnologia da Informação', 'aliases' => ['TÓPICOS AVANÇADOS EM TECNOLOGIA DA INFORMAÇÃO']],
        ['semestre' => 6, 'sigla' => 'TGI012', 'nome' => 'Trabalho de Graduação em Gestão da Tecnologia da Informação', 'aliases' => ['TRABALHO DE GRADUAÇÃO EM GESTÃO DA TECNOLOGIA DA INFORMAÇÃO', 'GESTÃO DO TRABALHO DE GRADUAÇÃO']],
        ['semestre' => 6, 'sigla' => 'TGI603', 'nome' => 'Atividades de Projetos VI (Projetos de Tecnologia da Informação II)', 'aliases' => ['ATIVIDADES DE PROJETOS VI (PROJETOS DE TECNOLOGIA DA INFORMAÇÃO II)', 'ATIVIDADES DE PROJETOS VI']],
    ];

    public function run(): void
    {
        // 1. Localizar ou assegurar o curso Gestão da Tecnologia da Informação
        $curso = Curso::firstOrCreate(
            ['nome' => 'Gestão da Tecnologia da Informação'],
            [
                'codigo' => 'GTI',
                'tipo_periodo' => 'semestral',
                'total_periodos' => 6,
                'turnos' => ['Manhã', 'Noite'],
                'ativo' => true,
            ]
        );

        // 2. Assegurar as estruturas de CursoPeriodo para os 6 semestres do turno Manhã
        $periodosPorNumero = [];
        for ($num = 1; $num <= 6; $num++) {
            $periodo = CursoPeriodo::firstOrCreate(
                [
                    'curso_id' => $curso->id,
                    'turno' => 'Manhã',
                    'periodo_numero' => $num,
                ],
                [
                    'periodo_tipo' => 'Semestre',
                    'nome_completo' => "{$curso->nome} - {$num}º Semestre (Manhã)",
                ]
            );
            $periodosPorNumero[$num] = $periodo;
        }

        $atualizadas = 0;
        $criadas = 0;

        foreach (self::DISCIPLINAS_GTI as $item) {
            $sigla = strtoupper(trim($item['sigla']));
            $nomeCanonical = trim($item['nome']);
            $semestre = (int) $item['semestre'];
            $periodo = $periodosPorNumero[$semestre] ?? null;

            // Busca idempotente:
            // 1) Por sigla exata
            $disciplina = Disciplina::where('sigla', $sigla)->first();

            // 2) Se não encontrar por sigla, busca por nome canônico ou aliases conhecidos
            if (!$disciplina) {
                $disciplina = Disciplina::where('nome', $nomeCanonical)
                    ->orWhere('nome', mb_strtoupper($nomeCanonical))
                    ->first();
            }

            if (!$disciplina && !empty($item['aliases'])) {
                foreach ($item['aliases'] as $alias) {
                    $disciplina = Disciplina::where('nome', $alias)
                        ->orWhere('nome', mb_strtoupper($alias))
                        ->first();
                    if ($disciplina) {
                        break;
                    }
                }
            }

            $dadosAtualizacao = [
                'nome' => $nomeCanonical,
                'sigla' => $sigla,
                'curso_id' => $curso->id,
                'curso_periodo_id' => $periodo?->id,
                'turno' => 'Manhã',
                'periodo_numero' => $semestre,
            ];

            if ($disciplina) {
                // Atualiza o registro existente preservando o ID e relacionamentos com docentes
                $disciplina->update($dadosAtualizacao);
                $atualizadas++;
            } else {
                // Cria registro novo
                Disciplina::create($dadosAtualizacao);
                $criadas++;
            }
        }

        $total = count(self::DISCIPLINAS_GTI);
        $this->command?->info("GtiDisciplinasSeeder: {$total} disciplinas processadas com sucesso ({$atualizadas} atualizadas, {$criadas} criadas) associadas ao GTI - Matutino!");
    }
}
