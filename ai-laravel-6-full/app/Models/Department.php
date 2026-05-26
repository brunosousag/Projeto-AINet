<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['abbreviation', 'name', 'name_pt'])]
#[Table(key: 'abbreviation', keyType: 'string', incrementing: false, timestamps: false)]
class Department extends Model
{
    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class, 'department', 'abbreviation');
    }
}
