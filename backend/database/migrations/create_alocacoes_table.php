<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alocacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professor_id')->constrained()->onDelete('cascade');
            $table->foreignId('sala_id')->constrained()->onDelete('cascade');
            $table->date('data');
            $table->time('horario_inicio');
            $table->time('horario_fim')->nullable();
            $table->timestamps();
            
            // Índices para melhor performance e restrições
            $table->index(['data', 'horario_inicio']);
            $table->unique(['sala_id', 'data', 'horario_inicio'], 'unique_sala_horario');
        });
    }

    public function down()
    {
        Schema::dropIfExists('alocacoes');
    }
};