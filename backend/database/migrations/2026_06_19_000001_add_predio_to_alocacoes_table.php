<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('alocacoes', 'predio')) {
            Schema::table('alocacoes', function (Blueprint $table) {
                $table->string('predio')->default('Prédio 1')->after('sala_id');
            });
        }

        if (!$this->indexExists('alocacoes', 'alocacoes_sala_id_index')) {
            Schema::table('alocacoes', function (Blueprint $table) {
                $table->index('sala_id', 'alocacoes_sala_id_index');
            });
        }

        Schema::table('alocacoes', function (Blueprint $table) {
            if ($this->indexExists('alocacoes', 'unique_sala_horario')) {
                $table->dropUnique('unique_sala_horario');
            }

            if (!$this->indexExists('alocacoes', 'unique_predio_sala_horario')) {
                $table->unique(['predio', 'sala_id', 'data', 'horario_inicio'], 'unique_predio_sala_horario');
            }
        });
    }

    public function down()
    {
        Schema::table('alocacoes', function (Blueprint $table) {
            if ($this->indexExists('alocacoes', 'unique_predio_sala_horario')) {
                $table->dropUnique('unique_predio_sala_horario');
            }

            if (!$this->indexExists('alocacoes', 'unique_sala_horario')) {
                $table->unique(['sala_id', 'data', 'horario_inicio'], 'unique_sala_horario');
            }

            if ($this->indexExists('alocacoes', 'alocacoes_sala_id_index')) {
                $table->dropIndex('alocacoes_sala_id_index');
            }

            if (Schema::hasColumn('alocacoes', 'predio')) {
                $table->dropColumn('predio');
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return collect(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]))->isNotEmpty();
    }
};
