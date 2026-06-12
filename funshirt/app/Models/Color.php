<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Color extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'custom'];

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

    public function getBaseImageUrlAttribute(): string
    {
        $path = "tshirt_base/{$this->code}.jpg";

        if (Storage::disk('public')->exists($path)) {
            return asset("storage/{$path}");
        }

        return asset('storage/tshirt_base/fafafa.jpg');
    }
}
