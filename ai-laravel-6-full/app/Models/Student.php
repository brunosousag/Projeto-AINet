<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['user_id', 'number', 'course'])]
#[Table(timestamps: false)]
class Student extends Model
{
    public function courseRef(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course', 'abbreviation');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(
            Discipline::class,
            'students_disciplines',
            'student_id',
            'discipline_id'
        );
    }
}
