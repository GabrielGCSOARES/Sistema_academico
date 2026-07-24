<?php

namespace Tests\Feature;

use App\Models\Docente;
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
}
