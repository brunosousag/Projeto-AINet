<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GradesSeeder extends Seeder
{
    public function run()
    {
        $this->command->line('--- > Creating Random Grades');

        $disciplines = DB::table('disciplines')->pluck('id')->toArray();
        $disciplines = Arr::shuffle($disciplines);
        $disciplines = Arr::take($disciplines, 120);

        // SQLite does not support TRUNCATE or RAND functions, so update command has to be changed
        $databaseType = DB::getConfig("driver");
        if ($databaseType == 'sqlite') {
            DB::table('students_disciplines')->whereIn('discipline_id', $disciplines)
                ->update([
                    'grade' => DB::raw('abs(random() % 23)')
                ]);
        } else {
            DB::table('students_disciplines')->whereIn('discipline_id', $disciplines)
                ->update([
                    'grade' => DB::raw('TRUNCATE(RAND()*(23), 0)')
                ]);
        }
        DB::table('students_disciplines')->where('grade', '>', 20)
            ->update([
                'grade' => null
            ]);

    }
}
