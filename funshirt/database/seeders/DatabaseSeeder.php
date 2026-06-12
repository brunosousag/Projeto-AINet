<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public static $startDate;
    public static $startDateMonthsAgo = 24;
    public static $dbInsertBlockSize = 500;

    // public static $seedType = "small";
    // public static $seedType = "full";
    // public static $seedLanguage = "pt_PT";
    // public static $seedLanguage = "en_US";

    public static $seedLanguage = "pt_PT";

    public function run(): void
    {
        $this->command->info("-----------------------------------------------");
        $this->command->info("START of database seeder");
        $this->command->info("-----------------------------------------------");

        self::$startDate = Carbon::now()->subMonths(self::$startDateMonthsAgo);
        self::$seedLanguage = $this->command->choice('What is the main language of the data?', ['pt_PT', 'en_US'], 0);

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            DB::statement('SET foreign_key_checks=0');
            // No permissions to change global setting. Change the session setting only
            // DB::statement("SET @@global.time_zone = '+00:00'");
            DB::statement("SET time_zone = '+00:00'");
        }

        DB::table('users')->delete();
        DB::table('customers')->delete();
        DB::table('tshirt_images')->delete();
        DB::table('colors')->delete();
        DB::table('categories')->delete();
        DB::table('prices')->delete();
        DB::table('orders')->delete();
        DB::table('order_items')->delete();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'users'");
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'customers'");
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'tshirt_images'");
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'colors'");
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'categories'");
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'prices'");
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'orders'");
            DB::statement("DELETE FROM sqlite_sequence WHERE name = 'order_items'");
        } else {
            DB::statement('ALTER TABLE users AUTO_INCREMENT = 0');
            DB::statement('ALTER TABLE customers AUTO_INCREMENT = 0');
            DB::statement('ALTER TABLE tshirt_images AUTO_INCREMENT = 0');
            DB::statement('ALTER TABLE colors AUTO_INCREMENT = 0');
            DB::statement('ALTER TABLE categories AUTO_INCREMENT = 0');
            DB::statement('ALTER TABLE prices AUTO_INCREMENT = 0');
            DB::statement('ALTER TABLE orders AUTO_INCREMENT = 0');
            DB::statement('ALTER TABLE order_items AUTO_INCREMENT = 0');
        }

        $this->command->info("-----------------------------------------------");

        $this->call(PricesSeeder::class);
        $this->call(ColorsSeeder::class);
        $this->call(CategoriesSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(TshirtImagesSeeder::class);
        $this->call(OrdersSeeder::class);

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement('SET foreign_key_checks=1');
        }

        $this->command->info("-----------------------------------------------");
        $this->command->info("END of database seeder");
        $this->command->info("-----------------------------------------------");
    }

    public static function startDateMonthsAgoAsString()
    {
        return '-' . self::$startDateMonthsAgo . ' months';
    }
}
