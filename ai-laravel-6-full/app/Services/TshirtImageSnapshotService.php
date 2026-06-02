<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\TshirtImage;

class TshirtImageSnapshotService
{
    /**
     * @return array<string, mixed>
     */
    public function for(TshirtImage $tshirtImage, ?array $settings = null): array
    {
        return [
            'name' => $tshirtImage->name,
            'description' => $tshirtImage->description,
            'image_url' => $tshirtImage->image_url,
            'is_personal' => $tshirtImage->customer_id !== null,
            'settings' => $settings ?? $tshirtImage->custom ?? [],
        ];
    }

    public function preserveExistingOrders(TshirtImage $tshirtImage): void
    {
        $snapshot = $this->for($tshirtImage);

        $tshirtImage->orderItems()
            ->get()
            ->each(function (OrderItem $item) use ($snapshot): void {
                if (data_get($item->custom, 'design')) {
                    return;
                }

                $item->update([
                    'custom' => array_replace($item->custom ?? [], [
                        'design' => $snapshot,
                    ]),
                ]);
            });
    }
}
