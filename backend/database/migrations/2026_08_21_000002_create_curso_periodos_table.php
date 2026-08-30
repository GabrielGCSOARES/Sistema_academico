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
        Schema::create('curso_periodos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->string('turno', 20); // 'Manhã', 'Tarde', 'Noite'
            $table->unsignedTinyInteger('periodo_numero'); // 1..6 ou 1..2
            $table->string('periodo_tipo', 20)->default('Semestre'); // 'Semestre', 'Ano'
            $table->string('nome_completo')->nullable(); // Ex: "1º Semestre - Manhã", "1º Ano - Tarde"
            $table->timestamps();

            $table->unique(['curso_id', 'turno', 'periodo_numero'], 'unique_curso_turno_periodo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_periodos');
    }
};
