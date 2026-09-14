<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('curso_periodos', function (Blueprint $table) {
            if (!Schema::hasColumn('curso_periodos', 'sigla_curso_turno')) {
                $table->string('sigla_curso_turno', 20)->nullable()->after('turno')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curso_periodos', function (Blueprint $table) {
            if (Schema::hasColumn('curso_periodos', 'sigla_curso_turno')) {
                $table->dropColumn('sigla_curso_turno');
            }
        });
    }
};
