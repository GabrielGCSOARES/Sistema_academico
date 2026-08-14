<?php

namespace Tests\Feature;

use App\Models\Docente;
use App\Models\Disciplina;
use App\Models\Professor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocenteSeedAndExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_docentes_and_professores_from_seed(): void
    {
        $this->artisan('db:seed')
            ->assertSuccessful();

        $this->assertGreaterThan(110, Docente::count());
        $this->assertGreaterThan(110, Professor::count());
    }

    public function test_xlsx_export_returns_spreadsheet_for_allocations(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        $response = $this->get('/api/alocacoes/exportar-xlsx');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertNotEmpty($response->getContent());
    }

    public function test_new_subject_is_persisted_when_linked_to_a_teacher(): void
    {
        $this->artisan('db:seed')->assertSuccessful();
        $docente = Docente::query()->firstOrFail();

        $response = $this->postJson('/api/docentes/vincular-disciplina', [
            'docente_id' => $docente->id,
            'disciplina_nome' => '  Arquitetura   de Software  ',
        ]);

        $response->assertOk()
            ->assertJsonPath('disciplina.nome', 'Arquitetura de Software');

        $this->assertDatabaseHas('disciplinas', ['nome' => 'Arquitetura de Software']);
        $this->assertSame(
            Disciplina::where('nome', 'Arquitetura de Software')->value('id'),
            $docente->fresh()->disciplina_id
        );
    }

    public function test_turn_export_endpoint_returns_the_entire_week(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        $response = $this->get('/api/alocacoes/grade/exportar-xlsx?turno=noturno&dia_semana=3');

        $response->assertOk();
        $this->assertStringContainsString('Segunda', $response->getContent());
        $this->assertStringContainsString('Sexta', $response->getContent());
        $this->assertStringContainsString('07:40 - 08:30', $response->getContent());
        $this->assertStringContainsString('21:40 - 22:30', $response->getContent());
    }
}
