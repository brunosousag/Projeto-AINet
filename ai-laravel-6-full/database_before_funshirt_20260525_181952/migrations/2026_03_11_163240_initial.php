<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->string('abbreviation', 20);
            $table->string('name');
            $table->string('name_pt');
            $table->enum('type', ['Degree', 'Master', 'TESP']);
            $table->integer('semesters');
            $table->integer('ECTS');
            $table->integer('places');
            $table->string('contact');
            $table->text('objectives');
            $table->text('objectives_pt');

            $table->primary('abbreviation');
        });

        Schema::create('disciplines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('course', 20);
            $table->integer('year');
            $table->integer('semester');
            $table->string('abbreviation', 20);
            $table->string('name');
            $table->string('name_pt');
            $table->integer('ECTS');
            $table->integer('hours');
            $table->boolean('optional')->default(false);

            $table->foreign('course')->references('abbreviation')->on('courses');
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->string('abbreviation', 20);
            $table->string('name');
            $table->string('name_pt');

            $table->primary('abbreviation');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('admin')->default(false);
            // Types = T- Teacher; S- Student; A= Academic Official
            $table->enum('type', ['T', 'S', 'A']);
            $table->enum('gender', ['M', 'F']);
            $table->string('photo_url')->nullable();
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('department', 20);
            $table->string('office', 50)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('locker', 20)->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('department')->references('abbreviation')->on('departments');
        });


        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('number', 20);
            $table->string('course', 20);

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('course')->references('abbreviation')->on('courses');
        });

        Schema::create('teachers_disciplines', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('discipline_id');
            $table->boolean('responsible')->default(false);

            $table->primary(['teacher_id', 'discipline_id']);
            $table->foreign('teacher_id')->references('id')->on('teachers');
            $table->foreign('discipline_id')->references('id')->on('disciplines');
        });

        Schema::create('students_disciplines', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('discipline_id');
            $table->boolean('repeating')->default(false);
            $table->integer('grade')->nullable();

            $table->primary(['student_id', 'discipline_id']);
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('discipline_id')->references('id')->on('disciplines');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students_disciplines');
        Schema::dropIfExists('teachers_disciplines');
        Schema::dropIfExists('students');
        Schema::dropIfExists('teachers');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['admin', 'type', 'gender', 'photo_url']);
        });
        Schema::dropIfExists('departments');
        Schema::dropIfExists('disciplines');
        Schema::dropIfExists('courses');
    }
};
