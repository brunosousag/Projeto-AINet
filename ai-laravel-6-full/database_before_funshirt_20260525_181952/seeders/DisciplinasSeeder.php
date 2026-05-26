<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DisciplinasSeeder extends Seeder
{
    public function run()
    {
        DB::table('disciplines')->truncate();
        $this->loadFromJson();
    }

    private function loadFromJson()
    {
        $this->command->line('--- > Creating Disciplines');
        DB::table('disciplines')->truncate();

        $disciplinesFromJSONFile = json_decode(file_get_contents(database_path('seeders/data') . "/disciplines.json"), true);
        DB::table('disciplines')->insert($disciplinesFromJSONFile);
    }
}
