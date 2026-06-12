<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'image_url', 'custom'];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'custom' => 'array',
        ];
    }

    public function tshirtImages(): HasMany
    {
        return $this->hasMany(TshirtImage::class);
    }

    public function getImageFullUrlAttribute(): string
    {
        $filename = $this->image_url ?: 'no_category.png';

        if (Storage::disk('public')->exists("categories/$filename")) {
            return asset("storage/categories/$filename");
        }

        return asset('storage/categories/default_category.png');
    }
}
