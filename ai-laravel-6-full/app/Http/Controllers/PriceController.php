<?php

namespace App\Http\Controllers;

use App\Http\Requests\PriceFormRequest;
use App\Models\Price;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PriceController extends Controller
{
    public function edit(): View
    {
        Gate::authorize('manage-catalog');

        return view('prices.edit', [
            'price' => $this->currentPrice(),
        ]);
    }

    public function update(PriceFormRequest $request): RedirectResponse
    {
        $price = $this->currentPrice();
        $price->update($request->validated());

        return redirect()
            ->route('prices.edit')
            ->with('alert-type', 'success')
            ->with('alert-msg', 'Prices updated successfully.');
    }

    private function currentPrice(): Price
    {
        return Price::query()->first()
            ?? Price::create([
                'unit_price_catalog' => 10,
                'unit_price_own' => 15,
                'unit_price_catalog_discount' => 8.5,
                'unit_price_own_discount' => 12,
                'qty_discount' => 5,
            ]);
    }
}
