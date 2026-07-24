<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Professor;
use App\Models\Sala;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            DisciplinaSeeder::class,
            DocenteSeeder::class,
            SalaSeeder::class,
        ]);
    }
}
