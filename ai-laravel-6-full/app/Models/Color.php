<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'name', 'custom'])]
class Color extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'custom' => 'array',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'color_code', 'code');
    }
}
