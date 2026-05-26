<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable(['customer_id', 'category_id', 'name', 'description', 'image_url', 'custom'])]
class TshirtImage extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'custom' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getImageFullUrlAttribute(): string
    {
        $folder = $this->customer_id ? 'tshirt_images_private' : 'tshirt_images';
        $disk = $this->customer_id ? 'local' : 'public';
        $path = "$folder/{$this->image_url}";

        if ($this->image_url && Storage::disk($disk)->exists($path)) {
            return $this->customer_id
                ? route('tshirt-images.private-image', ['tshirtImage' => $this])
                : asset("storage/$folder/{$this->image_url}");
        }

        return asset('storage/tshirt_images/placeholder.png');
    }
}
