<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\CursoPeriodo;
use App\Models\Disciplina;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstruturaCurricularTest extends TestCase
{
    use RefreshDatabase;

    public function test_estrutura_curricular_completa_dos_seis_cursos(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        $esperadoPorCurso = [
            'GTI - N' => [
                'curso_codigo' => 'GTI',
                'curso_nome' => 'Gestão da Tecnologia da Informação',
                'turno' => 'Noite',
                'semestres' => [
                    1 => 7,
                    2 => 9,
                    3 => 9,
                    4 => 7,
                    5 => 7,
                    6 => 10,
                ],
                'total' => 49,
            ],
            'AI - M' => [
                'curso_codigo' => 'AI',
                'curso_nome' => 'Automação Industrial',
                'turno' => 'Manhã',
                'semestres' => [
                    1 => 7,
                    2 => 7,
                    3 => 7,
                    4 => 6,
                    5 => 8,
                    6 => 9,
                ],
                'total' => 44,
            ],
            'AI - N' => [
                'curso_codigo' => 'AI',
                'curso_nome' => 'Automação Industrial',
                'turno' => 'Noite',
                'semestres' => [
                    1 => 7,
                    2 => 7,
                    3 => 7,
                    4 => 6,
                    5 => 8,
                    6 => 9,
                ],
                'total' => 44,
            ],
            'MI - M' => [
                'curso_codigo' => 'MI',
                'curso_nome' => 'Manutenção Industrial',
                'turno' => 'Manhã',
                'semestres' => [
                    1 => 8,
                    2 => 7,
                    3 => 8,
                    4 => 8,
                    5 => 9,
                    6 => 10,
                ],
                'total' => 50,
            ],
            'MI - N' => [
                'curso_codigo' => 'MI',
                'curso_nome' => 'Manutenção Industrial',
                'turno' => 'Noite',
                'semestres' => [
                    1 => 8,
                    2 => 7,
                    3 => 8,
                    4 => 8,
                    5 => 9,
                    6 => 10,
                ],
                'total' => 50,
            ],
            'PFO - T' => [
                'curso_codigo' => 'PFO',
                'curso_nome' => 'Produção Fonográfica',
                'turno' => 'Tarde',
                'semestres' => [
                    1 => 10,
                    2 => 11,
                    3 => 8,
                    4 => 9,
                    5 => 11,
                    6 => 12,
                ],
                'total' => 61,
            ],
        ];

        foreach ($esperadoPorCurso as $siglaCursoTurno => $info) {
            $periodos = CursoPeriodo::where('sigla_curso_turno', $siglaCursoTurno)
                ->orderBy('periodo_numero')
                ->get();

            // 1. Cada curso possui exatamente 6 semestres
            $this->assertCount(6, $periodos, "O curso/turno {$siglaCursoTurno} deve possuir 6 semestres.");

            $totalDisciplinasCurso = 0;

            foreach ($info['semestres'] as $semestreNum => $qtdEsperada) {
                $periodo = $periodos->firstWhere('periodo_numero', $semestreNum);
                $this->assertNotNull($periodo, "Semestre {$semestreNum} do curso {$siglaCursoTurno} não encontrado.");

                $disciplinasSemestre = $periodo->disciplinas;
                $this->assertCount(
                    $qtdEsperada,
                    $disciplinasSemestre,
                    "Semestre {$semestreNum} de {$siglaCursoTurno} deve possuir exatamente {$qtdEsperada} disciplinas."
                );

                // Cada disciplina deve possuir código/sigla e nome preenchidos
                foreach ($disciplinasSemestre as $disc) {
                    $this->assertNotEmpty($disc->sigla, "Disciplina {$disc->nome} deve ter sigla.");
                    $this->assertNotEmpty($disc->nome, "Disciplina com sigla {$disc->sigla} deve ter nome.");
                }

                $totalDisciplinasCurso += $disciplinasSemestre->count();
            }

            $this->assertEquals($info['total'], $totalDisciplinasCurso, "Total de disciplinas do curso {$siglaCursoTurno} diverge.");
        }
    }

    public function test_disciplinas_compartilhadas_nao_sao_duplicadas(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        // MET001 - Estatística aparece em GTI - N e AI - M
        $met001 = Disciplina::where('sigla', 'MET001')->get();
        $this->assertCount(1, $met001, 'MET001 deve existir apenas uma única vez na tabela de disciplinas.');

        $periodosMet001 = $met001->first()->cursoPeriodos->pluck('sigla_curso_turno')->all();
        $this->assertContains('GTI - N', $periodosMet001);
        $this->assertContains('AI - M', $periodosMet001);

        // MI - M e MI - N compartilham todas as 50 disciplinas sem duplicação
        $periodoMiM1 = CursoPeriodo::where('sigla_curso_turno', 'MI - M')->where('periodo_numero', 1)->firstOrFail();
        $periodoMiN1 = CursoPeriodo::where('sigla_curso_turno', 'MI - N')->where('periodo_numero', 1)->firstOrFail();

        $discsM1 = $periodoMiM1->disciplinas->pluck('id')->sort()->values()->all();
        $discsN1 = $periodoMiN1->disciplinas->pluck('id')->sort()->values()->all();
        $this->assertEquals($discsM1, $discsN1, 'MI - M e MI - N devem compartilhar os mesmos IDs de disciplinas no semestre 1.');
    }

    public function test_disciplinas_com_siglas_distintas_sao_registros_distintos(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        // CEE001 (AI - M) vs CEE017 (AI - N)
        $cee001 = Disciplina::where('sigla', 'CEE001')->first();
        $cee017 = Disciplina::where('sigla', 'CEE017')->first();

        $this->assertNotNull($cee001);
        $this->assertNotNull($cee017);
        $this->assertNotEquals($cee001->id, $cee017->id, 'CEE001 e CEE017 devem ser registros distintos.');
        $this->assertEquals('Inovação e Empreendedorismo', $cee001->nome);
        $this->assertEquals('Inovação e Empreendedorismo', $cee017->nome);

        // MET001 vs MET016
        $met001 = Disciplina::where('sigla', 'MET001')->first();
        $met016 = Disciplina::where('sigla', 'MET016')->first();

        $this->assertNotNull($met001);
        $this->assertNotNull($met016);
        $this->assertNotEquals($met001->id, $met016->id);
        $this->assertEquals('Estatística', $met001->nome);
        $this->assertEquals('Estatística Básica', $met016->nome);
    }

    public function test_seeder_executado_duas_vezes_e_totalmente_idempotente(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        $totalCursosAntes = Curso::count();
        $totalPeriodosAntes = CursoPeriodo::count();
        $totalDisciplinasAntes = Disciplina::count();
        $totalPivotsAntes = \DB::table('curso_periodo_disciplina')->count();

        // Roda uma segunda vez
        $this->artisan('db:seed')->assertSuccessful();

        $this->assertEquals($totalCursosAntes, Curso::count());
        $this->assertEquals($totalPeriodosAntes, CursoPeriodo::count());
        $this->assertEquals($totalDisciplinasAntes, Disciplina::count());
        $this->assertEquals($totalPivotsAntes, \DB::table('curso_periodo_disciplina')->count());
    }

    public function test_consulta_hierarquica_curso_turno_semestre_disciplinas_via_api(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        // 1. Consulta períodos por sigla_curso_turno
        $response = $this->getJson('/api/curso-periodos?sigla_curso_turno=GTI - N&com_disciplinas=1');
        $response->assertOk()
            ->assertJsonCount(6);

        $semestre1 = collect($response->json())->firstWhere('periodo_numero', 1);
        $this->assertNotNull($semestre1);
        $this->assertCount(7, $semestre1['disciplinas']);
        $this->assertEquals('AGO006', $semestre1['disciplinas'][0]['sigla']);

        // 2. Consulta disciplinas filtrando por sigla_curso_turno
        $responseDisc = $this->getJson('/api/disciplinas?sigla_curso_turno=PFO - T');
        $responseDisc->assertOk()
            ->assertJsonCount(61);

        // 3. Consulta detalhe do curso com períodos e disciplinas
        $cursoGti = Curso::where('codigo', 'GTI')->firstOrFail();
        $responseCurso = $this->getJson("/api/cursos/{$cursoGti->id}");
        $responseCurso->assertOk()
            ->assertJsonStructure([
                'id',
                'nome',
                'periodos' => [
                    '*' => [
                        'id',
                        'turno',
                        'periodo_numero',
                        'sigla_curso_turno',
                        'disciplinas' => [
                            '*' => ['id', 'sigla', 'nome']
                        ]
                    ]
                ]
            ]);
    }
}
