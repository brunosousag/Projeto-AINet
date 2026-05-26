<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['id', 'nif', 'address', 'default_payment_type', 'default_payment_ref', 'custom'])]
class Customer extends Model
{
    use SoftDeletes;

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'custom' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id')->withTrashed();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function tshirtImages(): HasMany
    {
        return $this->hasMany(TshirtImage::class);
    }
}
