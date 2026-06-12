<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    use SeederUtils;


    // Categories - usar esta tabela para associar aos seeds dos tshirtImages
    public static $categories = [
        "fun" => ["Engraçado", "Funny"],
        "geek" => ["Geeks", "Geeks"],
        "memes" => ["Memes", "Memes"],
        "insp" => ["Inspiração", "Inspiration"],
        "plain" => ["Simples", "Plain"],
        "filme" => ["Filmes", "Movies"],
        "music" => ["Musica", "Music"],
        "places" => ["Locais", "Places"],
        "logo" => ["Logotipos", "Logos"],
        "pub" => ["Publicidade e marcas", "Advertising and brands"],
        "abst" => ["Desenhos Abstratos", "Abstract Drawings"],
        "drinks" => ["Bebidas", "Drinks"],
        "nosense" => ["Sem Sentido", "Meaningless"],
        "infantil" => ["Infantil", "Childish"],
        "sports" => ["Desporto", "Sports"],
        "summer" => ["Verão", "Summer"],
        "surf" => ["Surf", "Surf"],
        "tattoo" => ["Tattoo", "Tattoo"],
        "vintage" => ["Vintage", "Vintage"],
        "cool" => ["Cool", "Cool"],
        "words" => ["Frases", "Phrases"]
        //"null" => "Sem category definida"
    ];

    public function run()
    {
        $this->command->info("Categorias de tshirtImages");
        $this->cleanStorageFolder('categories');

        foreach (CategoriesSeeder::$categories as $key => $value) {
            $id = DB::table('categories')->insertGetId(['name' => DatabaseSeeder::$seedLanguage == 'pt_PT' ? $value[0] : $value[1]]);
            if ($key != "nosense") { /// a categoria "Sem sentido" não tem imagem associada
                $newName = $this->copyFileToStorage('categories', "$key.png", 'categories', null, $id);
                DB::table('categories')->where('id', $id)->update(['image_url' => $newName]);
            }
            CategoriesSeeder::$categories[$key] = $id;
        }
        $this->copyFileToStorage('categories', 'default_category.png', 'categories','default_category.png');
        $this->copyFileToStorage('categories', 'no_category.png', 'categories', 'no_category.png');
        $this->command->info("Categorias de tshirtImages - seed completado");
    }
}
