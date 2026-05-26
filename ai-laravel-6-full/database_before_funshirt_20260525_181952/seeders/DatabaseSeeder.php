<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{

    public function run()
    {
        $databaseType = DB::getConfig("driver");
        $this->command->line('Running seeders for ' . $databaseType . ' database');
        if ($databaseType == 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement("SET foreign_key_checks=0");
        }

        $this->call(CursosSeeder::class);
        $this->call(DisciplinasSeeder::class);
        $this->call(DepartamentosSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(GradesSeeder::class);

        if ($databaseType == 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement("SET foreign_key_checks=1");
        }

    }
}
