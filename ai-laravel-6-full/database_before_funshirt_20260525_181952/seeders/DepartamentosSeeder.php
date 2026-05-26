<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentosSeeder extends Seeder
{
    private static $departments = [
        ['abbreviation' => 'DEI', 'name' => 'Department of Computer Engineering', 'name_pt' => 'Departamento Engenharia Informática'],
        ['abbreviation' => 'DM', 'name' => 'Department of Mathematics', 'name_pt' => 'Departamento de Matemática'],
        ['abbreviation' => 'DGE', 'name' => 'Department of Management and Economics', 'name_pt' => 'Departamento de Gestão e Economia'],
        ['abbreviation' => 'DCJ', 'name' => 'Department of Legal Sciences', 'name_pt' => 'Departamento de Ciências Jurídicas'],
        ['abbreviation' => 'DCL', 'name' => 'Department of Language Sciences', 'name_pt' => 'Departamento de Ciências da Linguagem'],
        ['abbreviation' => 'DEA', 'name' => 'Department of Environmental Engineering', 'name_pt' => 'Departamento de Engenharia do Ambiente'],
        ['abbreviation' => 'DEC', 'name' => 'Civil Engineering Department', 'name_pt' => 'Departamento de Engenharia Civil'],
        ['abbreviation' => 'DEE', 'name' => 'Department of Electrical Engineering', 'name_pt' => 'Departamento de Engenharia Electrotecnica'],
        ['abbreviation' => 'DEM', 'name' => 'Department of Mechanical Engineering', 'name_pt' => 'Departamento de Engenharia Mecânica'],
    ];

    public function run()
    {
        $this->command->line('--- > Creating Departments');

        DB::table('departments')->truncate();
        DB::table('departments')->insert(DepartamentosSeeder::$departments);
    }
}
