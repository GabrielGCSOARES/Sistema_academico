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
        Schema::table('docentes', function (Blueprint $table) {
            if (!Schema::hasColumn('docentes', 'disciplina_id')) {
                $table->foreignId('disciplina_id')
                    ->nullable()
                    ->constrained('disciplinas')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            if (Schema::hasColumn('docentes', 'disciplina_id')) {
                $table->dropForeign(['disciplina_id']);
                $table->dropColumn('disciplina_id');
            }
        });
    }
};

