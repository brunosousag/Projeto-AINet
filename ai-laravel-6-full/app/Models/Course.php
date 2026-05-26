<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['abbreviation', 'name', 'name_pt', 'type', 'semesters', 'ECTS',
            'places', 'contact', 'objectives', 'objectives_pt'])]
#[Table(key: 'abbreviation', keyType: 'string', incrementing: false, timestamps: false)]
class Course extends Model
{
    public function getFullNameAttribute()
    {
        return match ($this->type) {
            'Master'    => "Master's in ",
            'TESP'      => 'TeSP - ',
            default     => ''
        } . $this->name;
    }

    public function getImageUrlAttribute()
    {
        $abrUpper = strtoupper(trim($this->abbreviation));
        if (Storage::disk('public')->exists("courses/$abrUpper.png")) {
            return asset("storage/courses/$abrUpper.png");
        } else {
            return asset("storage/courses/no_course.png");
        }
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'course', 'abbreviation');
    }

    public function disciplines(): HasMany
    {
        return $this->hasMany(Discipline::class, 'course', 'abbreviation');
    }
}
