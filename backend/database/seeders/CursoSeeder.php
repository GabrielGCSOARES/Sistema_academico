<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curso;
use App\Models\CursoPeriodo;

class CursoSeeder extends Seeder
{
    public function run(): void
    {
        $cursosConfig = [
            [
                'nome' => 'Automação Industrial',
                'codigo' => 'AI',
                'tipo_periodo' => 'semestral',
                'total_periodos' => 6,
                'turnos' => ['Manhã', 'Noite'],
            ],
            [
                'nome' => 'Manutenção Industrial',
                'codigo' => 'MI',
                'tipo_periodo' => 'semestral',
                'total_periodos' => 6,
                'turnos' => ['Manhã', 'Noite'],
            ],
            [
                'nome' => 'Gestão da Tecnologia da Informação',
                'codigo' => 'GTI',
                'tipo_periodo' => 'semestral',
                'total_periodos' => 6,
                'turnos' => ['Manhã', 'Noite'],
            ],
            [
                'nome' => 'Gestão Empresarial',
                'codigo' => 'GE',
                'tipo_periodo' => 'semestral',
                'total_periodos' => 6,
                'turnos' => ['Tarde', 'Noite'],
            ],
            [
                'nome' => 'Produção Fonográfica',
                'codigo' => 'PFO',
                'tipo_periodo' => 'semestral',
                'total_periodos' => 6,
                'turnos' => ['Tarde'],
            ],
            [
                'nome' => 'Processos Gerenciais',
                'codigo' => 'PG',
                'tipo_periodo' => 'anual',
                'total_periodos' => 2,
                'turnos' => ['Tarde'],
            ],
            [
                'nome' => 'Análise e Desenvolvimento de Sistemas',
                'codigo' => 'ADS',
                'tipo_periodo' => 'anual',
                'total_periodos' => 2,
                'turnos' => ['Tarde'],
            ],
        ];

        $totalCursos = 0;
        $totalPeriodos = 0;

        foreach ($cursosConfig as $dadosCurso) {
            $curso = Curso::updateOrCreate(
                ['nome' => $dadosCurso['nome']],
                [
                    'codigo' => $dadosCurso['codigo'],
                    'tipo_periodo' => $dadosCurso['tipo_periodo'],
                    'total_periodos' => $dadosCurso['total_periodos'],
                    'turnos' => $dadosCurso['turnos'],
                    'ativo' => true,
                ]
            );
            $totalCursos++;

            $isAnual = $dadosCurso['tipo_periodo'] === 'anual';
            $periodoTipo = $isAnual ? 'Ano' : 'Semestre';

            foreach ($dadosCurso['turnos'] as $turno) {
                for ($numero = 1; $numero <= $dadosCurso['total_periodos']; $numero++) {
                    $nomeCompleto = "{$curso->nome} - {$numero}º {$periodoTipo} ({$turno})";

                    CursoPeriodo::updateOrCreate(
                        [
                            'curso_id' => $curso->id,
                            'turno' => $turno,
                            'periodo_numero' => $numero,
                        ],
                        [
                            'periodo_tipo' => $periodoTipo,
                            'nome_completo' => $nomeCompleto,
                        ]
                    );
                    $totalPeriodos++;
                }
            }
        }

        $this->command->info("{$totalCursos} cursos e {$totalPeriodos} estruturas de períodos/turnos sincronizados com sucesso!");
    }
}
