<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\CursoPeriodo;
use App\Models\Disciplina;
use App\Models\Docente;
use App\Models\Professor;
use App\Models\Sala;
use App\Models\Alocacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoAndSiglaTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_all_7_courses_with_exact_periods_and_turnos(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        $this->assertEquals(7, Curso::count());

        // 1. Automação Industrial (6 semestres: Manhã e Noite) -> 12 períodos
        $automacao = Curso::where('nome', 'Automação Industrial')->firstOrFail();
        $this->assertEquals(6, $automacao->total_periodos);
        $this->assertEquals(['Manhã', 'Noite'], $automacao->turnos);
        $this->assertEquals(12, $automacao->periodos()->count());

        // 2. Manutenção Industrial (6 semestres: Manhã e Noite) -> 12 períodos
        $manutencao = Curso::where('nome', 'Manutenção Industrial')->firstOrFail();
        $this->assertEquals(6, $manutencao->total_periodos);
        $this->assertEquals(['Manhã', 'Noite'], $manutencao->turnos);
        $this->assertEquals(12, $manutencao->periodos()->count());

        // 3. Gestão da Tecnologia da Informação (6 semestres: Manhã e Noite) -> 12 períodos
        $gti = Curso::where('nome', 'Gestão da Tecnologia da Informação')->firstOrFail();
        $this->assertEquals(6, $gti->total_periodos);
        $this->assertEquals(['Manhã', 'Noite'], $gti->turnos);
        $this->assertEquals(12, $gti->periodos()->count());

        // 4. Gestão Empresarial (6 semestres: Tarde e Noite) -> 12 períodos
        $ge = Curso::where('nome', 'Gestão Empresarial')->firstOrFail();
        $this->assertEquals(6, $ge->total_periodos);
        $this->assertEquals(['Tarde', 'Noite'], $ge->turnos);
        $this->assertEquals(12, $ge->periodos()->count());

        // 5. Produção Fonográfica (6 semestres: Tarde) -> 6 períodos
        $pf = Curso::where('nome', 'Produção Fonográfica')->firstOrFail();
        $this->assertEquals(6, $pf->total_periodos);
        $this->assertEquals(['Tarde'], $pf->turnos);
        $this->assertEquals(6, $pf->periodos()->count());

        // 6. Processos Gerenciais (2 anos: Tarde) -> 2 períodos
        $pg = Curso::where('nome', 'Processos Gerenciais')->firstOrFail();
        $this->assertEquals(2, $pg->total_periodos);
        $this->assertEquals('anual', $pg->tipo_periodo);
        $this->assertEquals(['Tarde'], $pg->turnos);
        $this->assertEquals(2, $pg->periodos()->count());

        // 7. Análise e Desenvolvimento de Sistemas (2 anos: Tarde) -> 2 períodos
        $ads = Curso::where('nome', 'Análise e Desenvolvimento de Sistemas')->firstOrFail();
        $this->assertEquals(2, $ads->total_periodos);
        $this->assertEquals('anual', $ads->tipo_periodo);
        $this->assertEquals(['Tarde'], $ads->turnos);
        $this->assertEquals(2, $ads->periodos()->count());

        // Total de estruturas de períodos/turnos = 12 + 12 + 12 + 12 + 6 + 2 + 2 = 58
        $this->assertEquals(58, CursoPeriodo::count());
    }

    public function test_can_update_discipline_sigla_without_blocking_allocation(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        $disciplina = Disciplina::create([
            'nome' => 'Algoritmos e Lógica',
            'sigla' => 'ALG-N',
            'turno' => 'Noite',
        ]);

        $response = $this->putJson("/api/disciplinas/{$disciplina->id}/sigla", [
            'sigla' => 'ALG-NOITE-01',
        ]);

        $response->assertOk()
            ->assertJsonPath('disciplina.sigla', 'ALG-NOITE-01');

        $this->assertDatabaseHas('disciplinas', [
            'id' => $disciplina->id,
            'sigla' => 'ALG-NOITE-01',
        ]);

        // Verificar que o docente pode vincular essa disciplina normalmente
        $docente = Docente::firstOrFail();
        $vincularRes = $this->postJson('/api/docentes/vincular-disciplina', [
            'docente_id' => $docente->id,
            'disciplina_id' => $disciplina->id,
        ]);

        $vincularRes->assertOk();
        $this->assertEquals($disciplina->id, $docente->fresh()->disciplina_id);
    }

    public function test_api_cursos_returns_courses_with_turnos_and_periods(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        $response = $this->getJson('/api/cursos');

        $response->assertOk()
            ->assertJsonCount(7);
    }

    public function test_api_curso_periodos_filter_by_turno(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        $responseManha = $this->getJson('/api/curso-periodos?turno=Manhã');
        $responseManha->assertOk();
        // 6 semestres * 3 cursos com Manhã (AUT, MAN, GTI) = 18
        $responseManha->assertJsonCount(18);

        $responseNoite = $this->getJson('/api/curso-periodos?turno=Noite');
        $responseNoite->assertOk();
        // 6 semestres * 4 cursos com Noite (AUT, MAN, GTI, GE) = 24
        $responseNoite->assertJsonCount(24);

        $responseTarde = $this->getJson('/api/curso-periodos?turno=Tarde');
        $responseTarde->assertOk();
        // GE (6) + PF (6) + PG (2) + ADS (2) = 16
        $responseTarde->assertJsonCount(16);
    }

    public function test_can_insert_two_siglas_and_reset_them(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        $disciplina = Disciplina::create([
            'nome' => 'Banco de Dados',
            'sigla' => 'BD-M',
            'sigla_secundaria' => 'BD-N',
        ]);

        $this->assertDatabaseHas('disciplinas', [
            'id' => $disciplina->id,
            'sigla' => 'BD-M',
            'sigla_secundaria' => 'BD-N',
        ]);

        // Redefinir siglas
        $response = $this->putJson("/api/disciplinas/{$disciplina->id}/redefinir-siglas");
        $response->assertOk()
            ->assertJsonPath('disciplina.sigla', null)
            ->assertJsonPath('disciplina.sigla_secundaria', null);

        $this->assertDatabaseHas('disciplinas', [
            'id' => $disciplina->id,
            'sigla' => null,
            'sigla_secundaria' => null,
        ]);
    }

    public function test_student_query_by_turno_returns_weekly_schedule(): void
    {
        $disciplina = Disciplina::create(['nome' => 'Sistemas Embarcados']);
        $docente = Docente::create([
            'nome' => 'Professor Noite',
            'disciplina_id' => $disciplina->id,
        ]);
        Professor::create([
            'id' => $docente->id,
            'nome' => $docente->nome,
            'email' => 'prof-noite@fatec.edu',
            'disciplina' => $disciplina->nome,
            'curso' => 'Automação Industrial',
            'ativo' => true,
        ]);
        $sala = Sala::create([
            'nome' => 'Sala 05',
            'predio' => 'Prédio 1',
            'capacidade' => 40,
            'tipo' => 'Sala',
            'ativa' => true,
        ]);

        $inicioSemana = now()->startOfWeek();

        Alocacao::create([
            'professor_id' => $docente->id,
            'sala_id' => $sala->id,
            'predio' => 'Prédio 1',
            'data' => $inicioSemana->copy()->addDay()->toDateString(),
            'horario_inicio' => '19:00:00',
            'horario_fim' => '19:50:00',
        ]);

        $response = $this->getJson('/api/alocacoes/consulta-aluno?' . http_build_query([
            'turno' => 'Noite',
            'inicio' => $inicioSemana->toDateString(),
        ]));

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.horario_inicio', '19:00:00');
    }
}
