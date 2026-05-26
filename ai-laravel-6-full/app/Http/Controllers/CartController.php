<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Models\TshirtImage;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function show(CartService $cartService): View
    {
        return view('cart.show', [
            'cart' => $cartService->summary(),
            'colors' => Color::orderBy('name')->get(),
            'sizes' => ['XS', 'S', 'M', 'L', 'XL'],
        ]);
    }

    public function add(Request $request, CartService $cartService): RedirectResponse
    {
        $validated = $request->validate([
            'tshirt_image_id' => ['required', 'integer', 'exists:tshirt_images,id'],
            'color_code' => ['required', 'string', 'exists:colors,code'],
            'size' => ['required', 'in:XS,S,M,L,XL'],
            'qty' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $tshirtImage = TshirtImage::findOrFail($validated['tshirt_image_id']);
        $color = Color::findOrFail($validated['color_code']);

        abort_unless(
            $tshirtImage->customer_id === null
                || ($request->user()?->isCustomer() && $request->user()->id === $tshirtImage->customer_id),
            403
        );

        $cartService->add($tshirtImage, $color, $validated['size'], (int) $validated['qty']);

        return back()
            ->with('alert-type', 'success')
            ->with('alert-msg', "T-shirt '{$tshirtImage->name}' adicionada ao carrinho.");
    }

    public function update(Request $request, CartService $cartService, string $line): RedirectResponse
    {
        $validated = $request->validate([
            'color_code' => ['required', 'string', 'exists:colors,code'],
            'size' => ['required', 'in:XS,S,M,L,XL'],
            'qty' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $color = Color::findOrFail($validated['color_code']);
        $cartService->update($line, $color, $validated['size'], (int) $validated['qty']);

        return back()
            ->with('alert-type', 'success')
            ->with('alert-msg', 'Carrinho atualizado.');
    }

    public function remove(CartService $cartService, string $line): RedirectResponse
    {
        $cartService->remove($line);

        return back()
            ->with('alert-type', 'success')
            ->with('alert-msg', 'Item removido do carrinho.');
    }

    public function destroy(CartService $cartService): RedirectResponse
    {
        $cartService->clear();

        return back()
            ->with('alert-type', 'success')
            ->with('alert-msg', 'Carrinho limpo.');
    }
}
