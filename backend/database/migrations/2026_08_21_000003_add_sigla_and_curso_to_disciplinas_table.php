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
        Schema::table('disciplinas', function (Blueprint $table) {
            $table->string('sigla', 50)->nullable()->after('nome')->index();
            $table->foreignId('curso_id')->nullable()->after('sigla')->constrained('cursos')->nullOnDelete();
            $table->foreignId('curso_periodo_id')->nullable()->after('curso_id')->constrained('curso_periodos')->nullOnDelete();
            $table->string('turno', 20)->nullable()->after('curso_periodo_id'); // 'Manhã', 'Tarde', 'Noite'
            $table->unsignedTinyInteger('periodo_numero')->nullable()->after('turno');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disciplinas', function (Blueprint $table) {
            $table->dropForeign(['curso_periodo_id']);
            $table->dropForeign(['curso_id']);
            $table->dropColumn(['sigla', 'curso_id', 'curso_periodo_id', 'turno', 'periodo_numero']);
        });
    }
};
