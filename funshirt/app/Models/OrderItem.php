<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'tshirt_image_id', 'color_code', 'size', 'qty', 'unit_price', 'sub_total', 'custom'];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'unit_price' => 'decimal:2',
            'sub_total' => 'decimal:2',
            'custom' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function tshirtImage(): BelongsTo
    {
        return $this->belongsTo(TshirtImage::class)->withTrashed();
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color_code', 'code')->withTrashed();
    }

    public function getDesignNameAttribute(): string
    {
        return data_get($this->custom, 'design.name', $this->tshirtImage?->name ?? 'Deleted image');
    }

    /**
     * @return array<string, mixed>
     */
    public function getDesignSettingsAttribute(): array
    {
        return data_get($this->custom, 'design.settings', $this->tshirtImage?->custom ?? []);
    }

    public function getDesignImageUrlAttribute(): string
    {
        $filename = data_get($this->custom, 'design.image_url');
        $isPersonal = data_get($this->custom, 'design.is_personal');

        if ($isPersonal === true || (! $filename && $this->tshirtImage?->customer_id !== null)) {
            return route('orders.items.image', ['order' => $this->order_id, 'item' => $this]);
        }

        if ($filename && $isPersonal === false) {
            return asset('storage/tshirt_images/'.basename($filename));
        }

        return $this->tshirtImage?->image_full_url ?? asset('storage/tshirt_images/placeholder.png');
    }
}
