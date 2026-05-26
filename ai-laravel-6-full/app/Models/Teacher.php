<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['user_id', 'department', 'office', 'extension', 'locker'])]
#[Table(timestamps: false)]
class Teacher extends Model
{
    public function departmentRef(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department', 'abbreviation');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(
            Discipline::class,
            'teachers_disciplines'
        );
    }
}
