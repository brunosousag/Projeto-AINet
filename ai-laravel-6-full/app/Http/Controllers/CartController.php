<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Models\TshirtImage;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CartController extends Controller
{
    public function show(CartService $cartService): View
    {
        Gate::authorize('use-cart');

        return view('cart.show', [
            'cart' => $cartService->summary(),
            'colors' => Color::orderBy('name')->get(),
        ]);
    }

    public function add(Request $request, CartService $cartService): RedirectResponse
    {
        Gate::authorize('use-cart');

        $validated = $request->validate([
            'tshirt_image_id' => ['required', 'integer', 'exists:tshirt_images,id'],
            'color_code' => ['required', 'string', 'exists:colors,code'],
            'size' => ['required', 'in:XS,S,M,L,XL'],
            'qty' => ['required', 'integer', 'min:1', 'max:999'],
            ...$this->previewRules(),
        ]);

        $tshirtImage = TshirtImage::findOrFail($validated['tshirt_image_id']);
        $color = Color::findOrFail($validated['color_code']);

        abort_unless(
            $tshirtImage->customer_id === null
                || ($request->user()?->isCustomer() && $request->user()->id === $tshirtImage->customer_id),
            403
        );

        $cartService->add(
            $tshirtImage,
            $color,
            $validated['size'],
            (int) $validated['qty'],
            $this->previewSettings($validated, $tshirtImage->custom ?? []),
        );

        return back()
            ->with('alert-type', 'success')
            ->with('alert-msg', "T-shirt '{$tshirtImage->name}' adicionada ao carrinho.");
    }

    public function update(Request $request, CartService $cartService, string $line): RedirectResponse
    {
        Gate::authorize('use-cart');

        $validated = $request->validate([
            'color_code' => ['required', 'string', 'exists:colors,code'],
            'qty' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $color = Color::findOrFail($validated['color_code']);
        $cartService->update(
            $line,
            $color,
            (int) $validated['qty'],
        );

        return back()
            ->with('alert-type', 'success')
            ->with('alert-msg', 'Carrinho atualizado.');
    }

    public function remove(CartService $cartService, string $line): RedirectResponse
    {
        Gate::authorize('use-cart');

        $cartService->remove($line);

        return back()
            ->with('alert-type', 'success')
            ->with('alert-msg', 'Item removido do carrinho.');
    }

    public function destroy(CartService $cartService): RedirectResponse
    {
        Gate::authorize('use-cart');

        $cartService->clear();

        return back()
            ->with('alert-type', 'success')
            ->with('alert-msg', 'Carrinho limpo.');
    }

    /**
     * @return array<string, list<string>>
     */
    private function previewRules(): array
    {
        return [
            'preview_top' => ['sometimes', 'integer', 'between:0,70'],
            'preview_width' => ['sometimes', 'integer', 'between:10,90'],
            'preview_height' => ['sometimes', 'integer', 'between:10,90'],
            'preview_opacity' => ['sometimes', 'integer', 'between:10,100'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $defaults
     * @return array<string, int>
     */
    private function previewSettings(array $validated, array $defaults): array
    {
        return [
            'preview_top' => (int) ($validated['preview_top'] ?? $defaults['preview_top'] ?? 25),
            'preview_width' => (int) ($validated['preview_width'] ?? $defaults['preview_width'] ?? 48),
            'preview_height' => (int) ($validated['preview_height'] ?? $defaults['preview_height'] ?? 50),
            'preview_opacity' => (int) ($validated['preview_opacity'] ?? $defaults['preview_opacity'] ?? 100),
        ];
    }
}
