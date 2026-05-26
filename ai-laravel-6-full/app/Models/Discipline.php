<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['course', 'year', 'semester', 'abbreviation', 'name', 'name_pt',
        'ECTS', 'hours', 'optional'])]
#[Table(timestamps: false)]
class Discipline extends Model
{
    public function getSemesterDescriptionAttribute()
    {
        return match ($this->semester) {
            0       => "Anual",
            1       => "1st",
            2       => "2nd",
            default => '?'
        };
    }

    public function courseRef(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course', 'abbreviation');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'teachers_disciplines');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            Student::class,
            'students_disciplines'
        );
    }
}
