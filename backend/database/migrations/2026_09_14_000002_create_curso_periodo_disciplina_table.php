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
        Schema::create('curso_periodo_disciplina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_periodo_id')->constrained('curso_periodos')->cascadeOnDelete();
            $table->foreignId('disciplina_id')->constrained('disciplinas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['curso_periodo_id', 'disciplina_id'], 'unique_curso_periodo_disciplina');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_periodo_disciplina');
    }
};
