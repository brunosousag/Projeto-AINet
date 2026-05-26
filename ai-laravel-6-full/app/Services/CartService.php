<?php

namespace App\Services;

use App\Models\Color;
use App\Models\Price;
use App\Models\TshirtImage;
use Illuminate\Support\Collection;

class CartService
{
    private const SessionKey = 'cart';

    /**
     * @return array<string, array{tshirt_image_id:int,color_code:string,size:string,qty:int}>
     */
    public function all(): array
    {
        return session(self::SessionKey, []);
    }

    public function add(TshirtImage $tshirtImage, Color $color, string $size, int $qty): void
    {
        $cart = $this->all();
        $key = $this->lineKey($tshirtImage->id, $color->code, $size);
        $currentQty = $cart[$key]['qty'] ?? 0;

        $cart[$key] = [
            'tshirt_image_id' => $tshirtImage->id,
            'color_code' => $color->code,
            'size' => $size,
            'qty' => $currentQty + $qty,
        ];

        $this->put($cart);
    }

    public function update(string $line, Color $color, string $size, int $qty): void
    {
        $cart = $this->all();

        if (! isset($cart[$line])) {
            return;
        }

        $item = $cart[$line];
        unset($cart[$line]);

        if ($qty > 0) {
            $newKey = $this->lineKey($item['tshirt_image_id'], $color->code, $size);
            $cart[$newKey] = [
                'tshirt_image_id' => $item['tshirt_image_id'],
                'color_code' => $color->code,
                'size' => $size,
                'qty' => ($cart[$newKey]['qty'] ?? 0) + $qty,
            ];
        }

        $this->put($cart);
    }

    public function remove(string $line): void
    {
        $cart = $this->all();
        unset($cart[$line]);
        $this->put($cart);
    }

    public function clear(): void
    {
        session()->forget(self::SessionKey);
    }

    /**
     * @return array{lines:Collection<int, array<string, mixed>>, total:float, count:int}
     */
    public function summary(): array
    {
        $cart = $this->all();
        $prices = Price::query()->first();

        if ($cart === [] || ! $prices) {
            return [
                'lines' => collect(),
                'total' => 0.0,
                'count' => 0,
            ];
        }

        $images = TshirtImage::with(['category', 'customer.user'])
            ->whereIn('id', collect($cart)->pluck('tshirt_image_id')->unique())
            ->get()
            ->keyBy('id');
        $colors = Color::whereIn('code', collect($cart)->pluck('color_code')->unique())
            ->get()
            ->keyBy('code');

        $lines = collect($cart)
            ->map(function (array $item, string $line) use ($images, $colors, $prices): ?array {
                $tshirtImage = $images->get($item['tshirt_image_id']);
                $color = $colors->get($item['color_code']);

                if (! $tshirtImage || ! $color) {
                    return null;
                }

                $unitPrice = $this->unitPrice($tshirtImage, (int) $item['qty'], $prices);
                $subTotal = $unitPrice * (int) $item['qty'];

                return [
                    'line' => $line,
                    'tshirt_image' => $tshirtImage,
                    'color' => $color,
                    'size' => $item['size'],
                    'qty' => (int) $item['qty'],
                    'unit_price' => $unitPrice,
                    'sub_total' => $subTotal,
                    'has_discount' => (int) $item['qty'] >= $prices->qty_discount,
                ];
            })
            ->filter()
            ->values();

        return [
            'lines' => $lines,
            'total' => (float) $lines->sum('sub_total'),
            'count' => (int) $lines->sum('qty'),
        ];
    }

    public function lineKey(int $tshirtImageId, string $colorCode, string $size): string
    {
        return rtrim(strtr(base64_encode("$tshirtImageId|$colorCode|$size"), '+/', '-_'), '=');
    }

    private function put(array $cart): void
    {
        if ($cart === []) {
            $this->clear();

            return;
        }

        session()->put(self::SessionKey, $cart);
    }

    private function unitPrice(TshirtImage $tshirtImage, int $qty, Price $prices): float
    {
        $isOwnImage = $tshirtImage->customer_id !== null;
        $hasDiscount = $qty >= $prices->qty_discount;

        return (float) match (true) {
            $isOwnImage && $hasDiscount => $prices->unit_price_own_discount,
            $isOwnImage => $prices->unit_price_own,
            $hasDiscount => $prices->unit_price_catalog_discount,
            default => $prices->unit_price_catalog,
        };
    }
}
