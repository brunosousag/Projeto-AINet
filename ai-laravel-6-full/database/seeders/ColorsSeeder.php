<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ColorsSeeder extends Seeder
{
    use SeederUtils;

    public static $colors = [
        "00a2f2" => ["Azul marinho", "Navy blue"],
        "1e1e21" => ["Preto", "Black"],
        "201f30" => ["Azul escuro", "Dark blue"],
        "284d9d" => ["Azul", "Blue"],
        "4bd7ef" => ["Azul cyan", "Cyan"],
        "73336a" => ["Roxo", "Purple"],
        "ac283b" => ["Vermelho", "Red"],
        "bceb97" => ["Verde claro", "Light Green"],
        "cfdcd8" => ["", ""],
        "e7e0ee" => ["Branco sujo", "Off white"],
        "f0eff3" => ["Cinza clara", "Light gray"],
        "f9b014" => ["Amarelo torrado", "Roasted yellow"],
        "fcabd2" => ["Rosa clara", "Light pink"],
        "fd4083" => ["Rosa", "Pink"],
        "fef7db" => ["Amarelo esbatido", "Faded yellow"],
        "10534e" => ["Verde escuro", "Dark green"],
        "1fba8f" => ["Verde", "Green"],
        "282c48" => ["Azul escuro", "Dark blue"],
        "49302c" => ["Castanho", "Brown"],
        "684f2e" => ["", ""],
        "7f7277" => ["", ""],
        "b5c8eb" => ["Azul bebé", "Baby blue"],
        "c7c6cf" => ["Cinza", "Gray"],
        "dc192d" => ["Vermelho vivo", "Bright red"],
        "ecdb2e" => ["Amarelo", "Yellow"],
        "f3f46b" => ["Amarelo claro", "Light yellow"],
        "fafafa" => ["Branco", "White"],
        "fcfbff" => ["", ""],
        "fd890f" => ["Laranja", "Orange"],
        "ffd2c3" => ["Salmão", "Salmon"],
    ];

    private $tshirt_basePath = 'public/tshirt_base';

    public function run()
    {
        $this->command->info("Colors e TShirts de base");
        $faker = DatabaseSeeder::$seedLanguage == 'pt_PT' ? \Faker\Factory::create('pt_PT') : \Faker\Factory::create();
        $this->cleanStorageFolder('tshirt_base');
        foreach (ColorsSeeder::$colors as $code => $names) {
            $this->copyFileToStorage('tshirt_base', $code . '.jpg', 'tshirt_base', $code . '.jpg');
            if (trim($names[0]) != "") {
                DB::table('colors')->insert([
                    'code' => $code,
                    'name' =>  DatabaseSeeder::$seedLanguage == 'pt_PT' ? $names[0] : $names[1]
                ]);
            }
        }
        $soft_deleted = ["4bd7ef", "f0eff3", "f9b014"];
        foreach ($soft_deleted as $code) {
            $deletedAt = $faker->dateTimeBetween(DatabaseSeeder::startDateMonthsAgoAsString(), '-1 months');
            DB::table('colors')
                ->where('code', $code)
                ->update(['deleted_at' => $deletedAt]);
        }
        $this->copia_tshirt_base_plain();
    }

    private function copia_tshirt_base_plain()
    {
        $this->copyFileToStorage('tshirt_base', 'plain_white.png', 'tshirt_base', 'plain_white.png');
    }
}
