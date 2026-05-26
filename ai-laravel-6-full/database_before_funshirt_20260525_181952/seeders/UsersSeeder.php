<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UsersSeeder extends Seeder
{
    private $total_users_teachers_dei = 100;
    private $total_users_teachers_outros = 20;
    private $total_outros_users = 20;
    private $total_users_students = 800;

    private $photoPath = 'public/photos';
    private $files_M = [];
    private $files_F = [];
    private $files_X = [];
    private $used_emails = [
        'marco.monteiro@ipleiria.pt',
        'eduardo.silva@ipleiria.pt',
        'eugenia.bernardino@ipleiria.pt',
    ];
    private $courses = [];
    private $outros_departments = [];

    private $passwordHash = '';

    public function run()
    {
        $this->command->line('--- > Creating Users');

        DB::table('teachers_disciplines')->truncate();
        DB::table('students_disciplines')->truncate();
        DB::table('teachers')->truncate();
        DB::table('students')->truncate();
        DB::table('users')->truncate();

        $this->passwordHash = bcrypt('123');

        $this->cleanPhotoFiles();

        // Preencher files_M com fotos de Homens e files_F com fotos de mulheres
        $allFiles = collect(File::files(database_path('seeders/photos')));
        foreach ($allFiles as $f) {
            if (strpos($f->getPathname(), 'm_')) {
                $this->files_M[] = $f->getPathname();
            } elseif (strpos($f->getPathname(), 'w_')) {
                $this->files_F[] = $f->getPathname();
            } elseif (strpos($f->getPathname(), 'X_')) {
                $this->files_X[] = $f->getPathname();
            } elseif (strpos($f->getPathname(), 'anonymous.png')) {
                $this->saveAnonymousPhoto($f->getPathname());
            }
        }

        // Podia ir à base de dados buscar os courses:
        //$this->courses = DB::table('courses')->pluck('abbreviation');
        // Mas vou definir à mão, para mudar probabilidades:
        $this->courses = [
            'EI', 'EI', 'EI', 'EI', 'EI', 'EI', 'EI', 'EI', 'EI', 'EI', 'EI', 'EI', 'EI', 'EI', 'EI',
            'JDM', 'JDM', 'JDM', 'JDM', 'JDM', 'JDM',
            'MEI-CM', 'MEI-CM',
            'MCIF', 'MCIF',
            'MCD', 'MCD',
            'TESP-DWM', 'TESP-DWM',
            'TESP-PSI', 'TESP-PSI', 'TESP-PSI',
            'TESP-CRI', 'TESP-CRI',
            'TESP-RSI',
            'TESP-TI'
        ];

        $this->outros_departments = DB::table('departments')->where('abbreviation', '<>', 'DEI')->pluck('abbreviation');

        $faker = \Faker\Factory::create('pt_PT');

        // Primeiro USER é sempre admin, com email sys@mail.pt
        $newUser = $this->newFakerUser($faker, 'A');
        $newUser['email'] = "sys@ipleiria.pt";
        $newUser['admin'] = true;
        $newId = DB::table('users')->insertGetId($newUser);

        $teacherAInet = -1;
        $userAInet = -1;
        $userAInetIDs = [];
        $teachersAInetIDs = [];

        for ($i = 0; $i < $this->total_users_teachers_dei; $i++) {
            $newIds = $this->newFakerDocente($faker, 'DEI');
            $changedUserData = [];
            $changedDocenteData = [];
            switch ($i) {
                case 0:
                    $teacherAInet = $newIds['teacher_id'];
                    $userAInet = $newIds['user_id'];
                    $changedUserData['name'] = 'Marco António de Oliveira Monteiro';
                    $changedUserData['email'] = 'marco.monteiro@ipleiria.pt';
                    $changedUserData['gender'] = 'M';
                    $changedUserData['admin'] = true;
                    $changedDocenteData['office'] = 'G.15-12';
                    $changedDocenteData['extension'] = '203166';
                    $changedDocenteData['locker'] = 'A069';
                    break;
                case 1:
                    $teacherAInet = $newIds['teacher_id'];
                    $userAInet = $newIds['user_id'];
                    $changedUserData['name'] = 'Eduardo Manuel Caetano da Silva';
                    $changedUserData['email'] = 'eduardo.silva@ipleiria.pt';
                    $changedUserData['gender'] = 'M';
                    $changedDocenteData['office'] = 'D.S.02.48';
                    break;
                case 2:
                    $teacherAInet = $newIds['teacher_id'];
                    $userAInet = $newIds['user_id'];
                    $changedUserData['name'] = 'Eugénia Moreira Bernardino';
                    $changedUserData['email'] = 'eugenia.bernardino@ipleiria.pt';
                    $changedUserData['gender'] = 'F';
                    $changedDocenteData['office'] = 'G.1.5.11';
                    $changedDocenteData['extension'] = '203167';
                    $changedDocenteData['locker'] = 'A064';
                    break;
            }
            if ($changedUserData) {
                $userAInetIDs[] = $userAInet;
                DB::table('users')->where('id', $userAInet)->update($changedUserData);
            }
            if ($changedDocenteData) {
                $teachersAInetIDs[] = $teacherAInet;
                DB::table('teachers')->where('id', $teacherAInet)->update($changedDocenteData);
            }
            if ($i % 10 === 0) {
                $this->command->line('Created teacher of DEI ' . ($i + 1) . '/' . $this->total_users_teachers_dei);
            }
        }

        for ($i = 0; $i < $this->total_users_teachers_outros; $i++) {
            $this->newFakerDocente($faker, $faker->randomElement($this->outros_departments));
            if ($i % 10 === 0) {
                $this->command->line('Created teacher of another department (<> DEI) ' . ($i + 1) . '/' . $this->total_users_teachers_outros);
            }
        }

        for ($i = 0; $i < $this->total_outros_users; $i++) {
            $newUser = $this->newFakerUser($faker, 'A');
            DB::table('users')->insert($newUser);

            if ($i % 10 === 0) {
                $this->command->line('Created Academic Official ' . ($i + 1) . '/' . $this->total_outros_users);
            }
        }

        for ($i = 0; $i < $this->total_users_students; $i++) {
            $this->newFakerAluno($faker);
            if ($i % 10 === 0) {
                $this->command->line('Created students ' . ($i + 1) . '/' . $this->total_users_students);
            }
        }

        // ---------------- ---------------- ---------------- ---------------- ----------------
        // FOTOS: BEGIN
        // ---------------- ---------------- ---------------- ---------------- ----------------

        // Photos of known teachers
        foreach ($this->files_X as $file) {
            $prefixoIdx = strpos($file, 'X_');
            if ($prefixoIdx) {
                $idx = substr($file, $prefixoIdx + 2, 1);
                $this->savePhotoOfUser($userAInetIDs[$idx], $file);
            }
        }

        shuffle($this->files_M);
        shuffle($this->files_F);

        // Primeiros 5 users Admin, 10 teachers, 20 students têm sempre foto.
        $users = DB::table('users')->where('type', 'A')->whereNull('photo_url')->select('id', 'gender')->get();
        $this->savePhotoOfUsers($users, 5);

        $users = DB::table('users')->where('type', 'T')->whereNull('photo_url')->whereNotIn('id', $userAInetIDs)->select('id', 'gender')->get();
        $this->savePhotoOfUsers($users, 10);

        $users = DB::table('users')->where('type', 'S')->whereNull('photo_url')->select('id', 'gender')->get();
        $this->savePhotoOfUsers($users, 20);

        // Rand SQL function does not work with SQLite - it was replaced by Collection Shuffle method
        //$todos_users = DB::table('users')->whereNull('photo_url')->whereNotIn('id', $userAInetIDs)->orderByRaw('RAND()')->pluck('gender', 'id');
        $todos_users = DB::table('users')
            ->select('id', 'gender')
            ->whereNull('photo_url')
            ->whereNotIn('id', $userAInetIDs)
            ->get()
            ->shuffle();
        foreach ($todos_users as $user) {
            // If there is no available photos, stop the cicle
            if (!($this->files_M || $this->files_F)) {
                break;
            }
            $file = null;
            if ($user->gender == 'M' && !empty($this->files_M)) {
                $file = array_shift($this->files_M);
            } elseif ($user->gender == 'F' && !empty($this->files_F)) {
                $file = array_shift($this->files_F);
            }
            if ($file) {
                $this->savePhotoOfUser($user->id, $file);
            }
        }

        // ---------------- ---------------- ---------------- ---------------- ----------------
        // FOTOS: END
        // ---------------- ---------------- ---------------- ---------------- ----------------

        $ainet = DB::table('disciplines')->where('course', 'EI')->where('abbreviation', 'AI')->pluck('id')[0];

        $i = 0;
        foreach ($teachersAInetIDs as $teacherID) {
            $teacherDisc = [
                'teacher_id' => $teacherID,
                'discipline_id' => $ainet,
                'responsible' => false
            ];
            if ($i == 0) {
                $teacherDisc['responsible'] = true;
            }
            $i++;
            DB::table('teachers_disciplines')->insert($teacherDisc);
        }

        $disciplines = DB::table('disciplines')->where('id', '<>', $ainet)->pluck('id');

        $todos_teachers = DB::table('teachers')->pluck('id');

        $todos_students = DB::table('students')->pluck('id');

        $contadorDisc = 1;
        $totalDisc = count($disciplines);
        foreach ($disciplines as $disc) {
            $teachersDisc = [];
            $numDocentes = $faker->randomElement([1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 3, 3, 3, 3, 4, 5]);
            $teachers = $faker->randomElements($todos_teachers, $numDocentes);
            $i = 0;
            foreach ($teachers as $teacher) {
                $teachersDisc[] = [
                    'teacher_id' => $teacher,
                    'discipline_id' => $disc,
                    'responsible' => $i === 0 ? true : false
                ];
                $i++;
            }
            DB::table('teachers_disciplines')->insert($teachersDisc);

            $studentsDisc = [];
            $students = $faker->randomElements($todos_students, random_int(4, 100));
            foreach ($students as $students) {
                $studentsDisc[] = [
                    'student_id' => $students,
                    'discipline_id' => $disc,
                    'repeating' => random_int(1, 4) === 2 ? true : false
                ];
            }

            DB::table('students_disciplines')->insert($studentsDisc);

            $this->command->line('Criadas associações com teachers e students para discipline ' . ($contadorDisc) . '/' . $totalDisc);
            $contadorDisc++;
        }

        // SQLLite does not support alias, so I have to change these 2 commands:
//        DB::update("update users as u set u.type = 'S' where u.type <> 'S' and u.id in (select user_id from students)");
//        DB::update("update users as u set u.type = 'T' where u.type <> 'T' and u.id in (select user_id from teachers)");
        DB::update("update users set type = 'S' where type <> 'S' and id in (select user_id from students)");
        DB::update("update users set type = 'T' where type <> 'T' and id in (select user_id from teachers)");
    }

    private function cleanPhotoFiles()
    {
        $storagePath = storage_path("app/$this->photoPath");
        if (File::exists($storagePath)) {
            File::deleteDirectory($storagePath);
        }
        if (!File::exists(storage_path("app"))) {
            File::makeDirectory(storage_path("app"));
        }
        if (!File::exists(storage_path("app/public"))) {
            File::makeDirectory(storage_path("app/public"));
        }
        File::makeDirectory($storagePath);
    }

    private function savePhotoOfUsers($users, $max)
    {
        $i = 0;
        foreach ($users as $user) {
            $file = $user->gender == 'M' ? array_shift($this->files_M) : array_shift($this->files_F);
            $this->savePhotoOfUser($user->id, $file);
            $i++;
            if ($i >= $max) {
                break;
            }
        }
    }

    private function savePhotoOfUser($user_id, $file)
    {
        $targetDir = storage_path('app/' . $this->photoPath);
        $newfilename = $user_id . "_" . uniqid() . '.jpg';
        File::copy($file, $targetDir . '/' . $newfilename);
        DB::table('users')->where('id', $user_id)->update(['photo_url' => $newfilename]);
        $this->command->info("Updated photo of User $user_id.");
    }

    private function saveAnonymousPhoto($file)
    {
        $targetDir = storage_path('app/' . $this->photoPath);
        File::copy($file, $targetDir . '/anonymous.png');
        $this->command->info("Copied Anonymous Photo");
    }

    private function newFakerDocente($faker, $department)
    {
        $newUser = $this->newFakerUser($faker, 'T');
        $newId = DB::table('users')->insertGetId($newUser);

        $teacher = [
            'user_id' => $newId,
            'department' => $department,
            'office' => 'G-' . rand(1, 3) . '.' . rand(1, 30),
            'extension' => '203' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'locker' => 'S' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)
        ];

        $newIdDocente = DB::table('teachers')->insertGetId($teacher);
        return ['user_id' => $newId, 'teacher_id' => $newIdDocente];
    }

    private function newFakerAluno($faker)
    {
        $newUser = $this->newFakerUser($faker, 'S');
        $newId = DB::table('users')->insertGetId($newUser);

        $students = [
            'user_id' => $newId,
            'number' => '21' . rand(5, 9) .  str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
            'course' => $faker->randomElement($this->courses),
        ];

        $newIdAluno = DB::table('students')->insertGetId($students);
        return ['user_id' => $newId, '' => $newIdAluno];
    }

    private function newFakerUser($faker, $type)
    {
        $email = "";
        $gender = "";
        $sufixoMail = $type == 'S' ? "@mail.pt" : "@ipleiria.pt";
        $name = $this->randomName($faker, $gender, $email, $sufixoMail);
        $createdAt = $faker->dateTimeBetween('-10 years', '-3 months');
        $email_verified_at = $faker->dateTimeBetween($createdAt, '-2 months');
        $updatedAt = $faker->dateTimeBetween($email_verified_at, '-1 months');
        return [
            'email' => $email,
            'name' => $name,
            'password' => $this->passwordHash, //bcrypt('123'),
            'remember_token' => Str::random(10),
// First 3 solutions may induce timezone issues, dues to daylight saving issues
// Check '2024-03-31 01:42:59' to verify that problem

//            'email_verified_at' => $email_verified_at,
//            'created_at' => $createdAt,
//            'updated_at' => $updatedAt,

//            'email_verified_at' => new Carbon($email_verified_at),
//            'created_at' => new Carbon($createdAt),
//            'updated_at' => new Carbon($updatedAt),

//            'email_verified_at' => $email_verified_at->format('Y-m-d H:i:s'),
//            'created_at' => $createdAt->format('Y-m-d H:i:s'),
//            'updated_at' =>  $updatedAt->format('Y-m-d H:i:s'),

            'email_verified_at' => new Carbon($email_verified_at, 'Europe/Lisbon'), //->setTimezone('Europe/Lisbon'),
            'created_at' => new Carbon($createdAt, 'Europe/Lisbon'),
            'updated_at' => new Carbon($updatedAt, 'Europe/Lisbon'),

            'type' => $type,
            'admin' => false,
            'gender' => $gender,    // This will not be saved on DB
        ];
    }

    private function stripAccents($stripAccents)
    {
        $from = 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ';
        $to =   'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY';
        $keys = array();
        $values = array();
        preg_match_all('/./u', $from, $keys);
        preg_match_all('/./u', $to, $values);
        $mapping = array_combine($keys[0], $values[0]);
        return strtr($stripAccents, $mapping);
    }

    private function strtr_utf8($str, $from, $to)
    {
        $keys = array();
        $values = array();
        preg_match_all('/./u', $from, $keys);
        preg_match_all('/./u', $to, $values);
        $mapping = array_combine($keys[0], $values[0]);
        return strtr($str, $mapping);
    }

    private function randomName($faker, &$gender, &$email, $sufixoMail)
    {
        $gender = $faker->randomElement(['male', 'female']);
        $firstname = $faker->firstName($gender);
        $lastname = $faker->lastName();
        $secondname = $faker->numberBetween(1, 3) == 2 ? "" : " " . $faker->firstName($gender);
        $number_middlenames = $faker->numberBetween(1, 6);
        $number_middlenames = $number_middlenames == 1 ? 0 : ($number_middlenames >= 5 ? $number_middlenames - 3 : 1);
        $middlenames = "";
        for ($i = 0; $i < $number_middlenames; $i++) {
            $middlenames .= " " . $faker->lastName();
        }
        $fullname = $firstname . $secondname . $middlenames . " " . $lastname;

        $email = strtolower($this->stripAccents($firstname) . "." . $this->stripAccents($lastname) . $sufixoMail);
        $i = 2;
        while (in_array($email, $this->used_emails)) {
            $email = strtolower($this->stripAccents($firstname) . "." . $this->stripAccents($lastname) . "." . $i . $sufixoMail);
            $i++;
        }
        $this->used_emails[] = $email;
        $gender = $gender == 'male' ? 'M' : 'F';
        return $fullname;
    }
}
