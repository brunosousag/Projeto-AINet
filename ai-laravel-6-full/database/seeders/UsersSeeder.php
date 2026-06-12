<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    use SeederUtils;

    private $typesOfUsers =  ['A', 'F', 'C'];
    private $numberOfUsers = [6, 15, 500];
    private $numberOfSoftDeletedUsers = [1, 3, 35];
    private $numberOfBlocked = [1, 3, 30];
    private $files_M = [];
    private $files_F = [];
    private $hashedPassword = null;
    private $faker = null;

    public static $allUsers = [];

    public function run()
    {
        $this->command->table(['Users table seeder notice'], [
            ["As photos serão armazenadas na pasta storage/app/public/photos"]
        ]);

        $this->hashedPassword = bcrypt('123');

        $this->cleanStorageFolder('photos');
        $this->preencherNamesFicheirosPhotos();

        $this->faker = DatabaseSeeder::$seedLanguage == 'pt_PT' ? \Faker\Factory::create('pt_PT') : \Faker\Factory::create();

        $variosUsers = [];
        $totalGuardados = 0;
        $totalParaGuardar = 0;
        foreach ($this->typesOfUsers as $idxUserType => $user_typeUser) {
            $totalParaGuardar += $this->numberOfUsers[$idxUserType];
        }
        foreach ($this->typesOfUsers as $idxUserType => $user_typeUser) {
            $totalUsers = $this->numberOfUsers[$idxUserType];
            for ($i = 0; $i < $totalUsers; $i++) {
                $variosUsers[] = $this->newFakerUser($this->faker, $user_typeUser);
                if (count($variosUsers) >= 50) {
                    $totalGuardados += count($variosUsers);
                    DB::table('users')->insert($variosUsers);
                    $this->command->info("Guardados $totalGuardados/$totalParaGuardar users na base de dados");
                    $variosUsers = [];
                }
            }
        }
        if (count($variosUsers) > 0) {
            $totalGuardados += count($variosUsers);
            DB::table('users')->insert($variosUsers);
            $this->command->info("Guardados $totalGuardados/$totalParaGuardar users na base de dados");
        }

        UsersSeeder::$allUsers['A'] = DB::table('users')->where('user_type', 'A')->get();
        UsersSeeder::$allUsers['F'] = DB::table('users')->where('user_type', 'F')->get();
        UsersSeeder::$allUsers['C'] = DB::table('users')->where('user_type', 'C')->get();

        shuffle($this->files_M);
        shuffle($this->files_F);

        UsersSeeder::$allUsers['A'] = UsersSeeder::$allUsers['A']->shuffle();
        UsersSeeder::$allUsers['F'] = UsersSeeder::$allUsers['F']->shuffle();

        $this->copiarPhotos(UsersSeeder::$allUsers['A']);
        $this->copiarPhotos(UsersSeeder::$allUsers['F']);
        $totalCustomers = UsersSeeder::$allUsers['C']->count();
        $first10Customers = UsersSeeder::$allUsers['C']->take(10);
        $LastCustomers = UsersSeeder::$allUsers['C']->take(-1* ($totalCustomers - 10)); // todos menos os primeiros 10
        $this->copiarPhotos($first10Customers->shuffle());
        $this->copiarPhotos($LastCustomers->shuffle());

        $this->copyFileToStorage('photos', 'anonymous.png', 'photos', 'anonymous.png');

        UsersSeeder::$allUsers['C'] = UsersSeeder::$allUsers['C']->shuffle();
        $idsToBlock = [];
        $idsToDelete = [];
        foreach ($this->typesOfUsers as $idxUserType => $user_typeUser) {
            $usersToBlock = $this->numberOfBlocked[$idxUserType];
            $usersToDelete = $this->numberOfSoftDeletedUsers[$idxUserType];
            foreach (UsersSeeder::$allUsers[$user_typeUser] as $user) {
                if ($usersToBlock > 0) {
                    $idsToBlock[] = $user->id;
                    $usersToBlock--;
                } elseif (($usersToBlock == 0) && ($usersToDelete > 0)) {
                    $idsToDelete[] = $user->id;
                    $usersToDelete--;
                }
                if (($usersToBlock == 0) && ($usersToDelete == 0)) {
                    continue;
                }
            }
        }

        if (count($idsToBlock) > 0) {
            $this->command->info("Bloquear " . count($idsToBlock) . " users na base de dados");
            DB::table('users')->whereIn('id', $idsToBlock)->update(['blocked' => 1]);
        }
        if (count($idsToDelete) > 0) {
            $this->command->info("Soft Delete " . count($idsToDelete) . " users na base de dados");
            DB::table('users')->whereNotIn('id', $idsToDelete)->update(['deleted_at' => null]);
        }

        $totalGuardados = 0;
        $totalParaGuardar = UsersSeeder::$allUsers['C']->count();
        $array_customers = [];
        foreach (UsersSeeder::$allUsers['C'] as $customerUser) {
            $array_customers[] = $this->newFakerCustomer($this->faker, $customerUser);
            if (count($array_customers) >= 50) {
                $totalGuardados += count($array_customers);
                DB::table('customers')->insert($array_customers);
                $this->command->info("Guardados $totalGuardados/$totalParaGuardar customers na base de dados");
                $array_customers = [];
            }
        }
        if (count($array_customers) > 0) {
            $totalGuardados += count($array_customers);
            DB::table('users')->insert($array_customers);
            $this->command->info("Guardados $totalGuardados/$totalParaGuardar customers na base de dados");
        }

        // Atualizar o softdelete dos customers para refletir o softdelete dos users
        $this->command->info("Atualizar os softdeletes dos customers para ifcarem iguais aos clientes");
        DB::update("
            UPDATE customers
            SET deleted_at = (
                SELECT users.deleted_at
                FROM users
                WHERE users.id = customers.id
            )
            ");

        // Mudar alguns email para simplificar testes
        $this->command->info("Mudar alguns email para simplificar testes");
        $ids = DB::table('users')->where('user_type', 'A')->take(3)->pluck('id')->toArray();
        $i = 1;
        foreach ($ids as $id) {
            DB::table('users')->where('id', $id)->update(['email' => "a$i@mail.pt"]);
            $i++;
        }
        $ids = DB::table('users')->where('user_type', 'F')->take(3)->pluck('id')->toArray();
        $i = 1;
        foreach ($ids as $id) {
            DB::table('users')->where('id', $id)->update(['email' => "f$i@mail.pt"]);
            $i++;
        }
        $ids = DB::table('users')->where('user_type', 'C')->take(10)->pluck('id')->toArray();
        $i = 1;
        foreach ($ids as $id) {
            DB::table('users')->where('id', $id)->update(['email' => "c$i@mail.pt"]);
            $i++;
        }
    }

    private function preencherNamesFicheirosPhotos()
    {
        $allFiles = collect(File::files(database_path('seeders/photos')));
        foreach ($allFiles as $f) {
            if (strpos($f->getFileName(), 'm_') !== false) {
                $this->files_M[] = $f->getFileName();
            } elseif (strpos($f->getFileName(), 'w_') !== false) {
                $this->files_F[] = $f->getFileName();
            }
        }
    }

    private function copiarPhotos($userCollection)
    {
        foreach ($userCollection as $user) {
            if ((count($this->files_M) == 0) && (count($this->files_F) == 0)) {
                break;
            }
            $file = $user->gender == 'M' ? array_shift($this->files_M) : array_shift($this->files_F);
            if ($file) {
                $newFileName = $this->copyFileToStorage('photos', $file, 'photos', null, $user->id);
                DB::table('users')->where('id', $user->id)->update(['photo_url' => $newFileName]);
            }
        }
    }

    private function newFakerUser($faker, $user_type)
    {
        $fullname = null;
        $email = null;
        $gender = null;
        $this->randomName($faker, $gender, $fullname, $email);
        $createdAt = $faker->dateTimeBetween(DatabaseSeeder::startDateMonthsAgoAsString(), '-3 months');
        $email_verified_at = $faker->dateTimeBetween($createdAt, '-2 months');
        $updatedAt = $faker->dateTimeBetween($email_verified_at, '-1 months');
        $deletedAt = $faker->dateTimeBetween($updatedAt);
        return [
            'name' => $fullname,
            'email' => $email,
            'email_verified_at' => $email_verified_at,
            'password' => $this->hashedPassword,
            'remember_token' => Str::random(10),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'user_type' => $user_type,
            'blocked' => 0,
            'gender' => $gender,
            'deleted_at' => $deletedAt,
            'custom' => null
        ];
    }


    private function newFakerCustomer($faker, $customerUser)
    {
        $paymentType = '';
        $paymentReference = '';
        $this->ramdomPaymentMethod($customerUser->email, $paymentType, $paymentReference);
        return [
            'id' => $customerUser->id,
            'nif' => $faker->randomNumber($nbDigits = 9, $strict = true),
            'address' => $faker->address,
            'default_payment_type' => $paymentType,
            'default_payment_ref' => $paymentReference,
            'custom' => null
        ];
    }
}
