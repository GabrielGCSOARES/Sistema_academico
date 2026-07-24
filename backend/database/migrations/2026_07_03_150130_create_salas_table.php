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
        if (Schema::hasTable('salas')) {
            Schema::table('salas', function (Blueprint $table) {
                if (!Schema::hasColumn('salas', 'predio')) {
                    $table->string('predio')->default('Prédio 1')->after('nome');
                }

                if (!Schema::hasColumn('salas', 'tipo')) {
                    $table->string('tipo')->nullable()->after('capacidade');
                }
            });

            return;
        }

        Schema::create('salas', function (Blueprint $table) {
            $table->id();

            $table->string('nome');

            $table->string('predio');

            $table->integer('capacidade');

            $table->string('tipo')->nullable();

            $table->boolean('ativa')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('salas')) {
            return;
        }

        Schema::table('salas', function (Blueprint $table) {
            if (Schema::hasColumn('salas', 'tipo')) {
                $table->dropColumn('tipo');
            }

            if (Schema::hasColumn('salas', 'predio')) {
                $table->dropColumn('predio');
            }
        });
    }
};
