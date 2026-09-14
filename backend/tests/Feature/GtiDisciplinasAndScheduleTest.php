<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\CursoPeriodo;
use App\Models\Disciplina;
use App\Models\Docente;
use App\Models\Professor;
use App\Models\Sala;
use App\Models\Alocacao;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GtiDisciplinasAndScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_gti_disciplinas_seeder_registers_exact_49_disciplines(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        $cursoGti = Curso::where('nome', 'Gestão da Tecnologia da Informação')->firstOrFail();
        $this->assertEquals('GTI', $cursoGti->codigo);

        // Verifica o total exato de 49 disciplinas
        $disciplinas = Disciplina::where('curso_id', $cursoGti->id)
            ->where('turno', 'Manhã')
            ->get();

        $this->assertCount(49, $disciplinas);

        // Verifica a quantidade exata por semestre
        $this->assertEquals(7, $disciplinas->where('periodo_numero', 1)->count());
        $this->assertEquals(9, $disciplinas->where('periodo_numero', 2)->count());
        $this->assertEquals(9, $disciplinas->where('periodo_numero', 3)->count());
        $this->assertEquals(7, $disciplinas->where('periodo_numero', 4)->count());
        $this->assertEquals(7, $disciplinas->where('periodo_numero', 5)->count());
        $this->assertEquals(10, $disciplinas->where('periodo_numero', 6)->count());

        // Verifica que todas possuem sigla não nula e única no curso
        $siglas = $disciplinas->pluck('sigla')->filter();
        $this->assertCount(49, $siglas);
        $this->assertEquals(49, $siglas->unique()->count());

        // Verifica exemplos específicos solicitados
        $algoritmos = $disciplinas->where('sigla', 'IAL004')->first();
        $this->assertNotNull($algoritmos);
        $this->assertEquals('Algoritmos', $algoritmos->nome);
        $this->assertEquals(1, $algoritmos->periodo_numero);

        $ingles = $disciplinas->where('sigla', 'LIN100')->first();
        $this->assertNotNull($ingles);
        $this->assertEquals('Inglês I', $ingles->nome);
        $this->assertEquals(1, $ingles->periodo_numero);

        $matematicaDiscreta = $disciplinas->where('sigla', 'MAT006')->first();
        $this->assertNotNull($matematicaDiscreta);
        $this->assertEquals('Matemática Discreta', $matematicaDiscreta->nome);
        $this->assertEquals(1, $matematicaDiscreta->periodo_numero);
    }

    public function test_seeder_is_idempotent_and_does_not_duplicate_disciplines(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        $cursoGti = Curso::where('nome', 'Gestão da Tecnologia da Informação')->firstOrFail();
        $totalAntes = Disciplina::where('curso_id', $cursoGti->id)->count();

        // Roda o seeder uma segunda vez
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\GtiDisciplinasSeeder'])->assertSuccessful();

        $totalDepois = Disciplina::where('curso_id', $cursoGti->id)->count();
        $this->assertEquals($totalAntes, $totalDepois);
        $this->assertEquals(49, $totalDepois);
    }

    public function test_api_disciplinas_filters_by_course_id(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        $cursoGti = Curso::where('nome', 'Gestão da Tecnologia da Informação')->firstOrFail();

        $response = $this->getJson("/api/disciplinas?curso_id={$cursoGti->id}");

        $response->assertOk()
            ->assertJsonCount(49)
            ->assertJsonStructure([
                '*' => ['id', 'nome', 'sigla', 'curso_id', 'periodo_numero', 'turno']
            ]);
    }

    public function test_student_query_returns_weekly_schedule_with_saturday_and_filters(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        $cursoGti = Curso::where('nome', 'Gestão da Tecnologia da Informação')->firstOrFail();
        $inicioSemana = now()->startOfWeek(Carbon::MONDAY)->toDateString();

        // Consulta geral da semana para GTI Manhã
        $response = $this->getJson('/api/alocacoes/consulta-aluno?' . http_build_query([
            'curso_id' => $cursoGti->id,
            'turno' => 'Manhã',
            'inicio' => $inicioSemana,
        ]));

        $response->assertOk();
        $data = $response->json();
        $this->assertGreaterThanOrEqual(10, count($data));

        // Verifica que temos aula no sábado
        $sabadoData = now()->startOfWeek(Carbon::MONDAY)->addDays(5)->toDateString();
        $aulaSabado = collect($data)->first(fn ($item) => str_starts_with($item['data'] ?? '', $sabadoData));
        $this->assertNotNull($aulaSabado, 'Deveria existir aula alocada para o sábado');

        // Testar filtro por disciplina (ex: IAL004)
        $discAlgoritmos = Disciplina::where('sigla', 'IAL004')->firstOrFail();
        $responseDisc = $this->getJson('/api/alocacoes/consulta-aluno?' . http_build_query([
            'curso_id' => $cursoGti->id,
            'disciplina_id' => $discAlgoritmos->id,
            'inicio' => $inicioSemana,
        ]));

        $responseDisc->assertOk();
        $dataDisc = $responseDisc->json();
        $this->assertNotEmpty($dataDisc);
        foreach ($dataDisc as $item) {
            $this->assertEquals('Algoritmos', $item['professor']['disciplina']);
        }

        // Testar filtro por período (ex: 1º semestre)
        $responseSem1 = $this->getJson('/api/alocacoes/consulta-aluno?' . http_build_query([
            'curso_id' => $cursoGti->id,
            'periodo_numero' => 1,
            'inicio' => $inicioSemana,
        ]));

        $responseSem1->assertOk();
        $dataSem1 = $responseSem1->json();
        $this->assertNotEmpty($dataSem1);
        foreach ($dataSem1 as $item) {
            $this->assertEquals(1, $item['professor']['docente']['disciplina']['periodo_numero']);
        }
    }
}
