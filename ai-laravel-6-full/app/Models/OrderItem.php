<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'tshirt_image_id', 'color_code', 'size', 'qty', 'unit_price', 'sub_total', 'custom'])]
class OrderItem extends Model
{
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
}
