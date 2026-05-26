<x-layouts::main-content title="Cart"
                         heading="Shopping Cart"
                         subheading="T-shirts selected during this session">
    <div class="space-y-6">
        @if ($cart['lines']->isEmpty())
            <div class="rounded-lg border border-zinc-200 bg-white p-6 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                Your cart is empty.
            </div>
        @else
            <div class="space-y-3">
                @foreach ($cart['lines'] as $item)
                    <div class="grid gap-4 rounded-lg border border-zinc-200 bg-white p-4 md:grid-cols-[96px_1fr_auto] dark:border-zinc-700 dark:bg-zinc-900">
                        <img src="{{ $item['tshirt_image']->image_full_url }}"
                             alt="{{ $item['tshirt_image']->name }}"
                             class="aspect-square w-24 rounded bg-zinc-100 object-contain p-2 dark:bg-zinc-800">

                        <div class="space-y-3">
                            <div>
                                <h2 class="font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $item['tshirt_image']->name }}
                                </h2>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $item['color']->name }} - {{ $item['size'] }}
                                    @if ($item['has_discount'])
                                        - discount
                                    @endif
                                </p>
                            </div>

                            <form method="POST" action="{{ route('cart.update', ['line' => $item['line']]) }}"
                                  class="grid gap-2 sm:grid-cols-[180px_90px_100px_auto]">
                                @csrf
                                @method('PUT')

                                <flux:select name="color_code" label="Color">
                                    @foreach ($colors as $color)
                                        <option value="{{ $color->code }}" @selected($color->code === $item['color']->code)>
                                            {{ $color->name }}
                                        </option>
                                    @endforeach
                                </flux:select>

                                <flux:select name="size" label="Size">
                                    @foreach ($sizes as $size)
                                        <option value="{{ $size }}" @selected($size === $item['size'])>{{ $size }}</option>
                                    @endforeach
                                </flux:select>

                                <flux:input type="number" name="qty" label="Qty" min="0" max="999" value="{{ $item['qty'] }}" />

                                <div class="flex items-end">
                                    <flux:button type="submit" icon="check" variant="primary">Update</flux:button>
                                </div>
                            </form>
                        </div>

                        <div class="flex flex-col items-start gap-3 md:items-end">
                            <div class="text-right">
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ number_format($item['unit_price'], 2) }} EUR each
                                </p>
                                <p class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ number_format($item['sub_total'], 2) }} EUR
                                </p>
                            </div>

                            <form method="POST" action="{{ route('cart.remove', ['line' => $item['line']]) }}">
                                @csrf
                                @method('DELETE')
                                <flux:button type="submit" icon="trash" variant="danger">Remove</flux:button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-col gap-3 rounded-lg border border-zinc-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-700 dark:bg-zinc-900">
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $cart['count'] }} item(s)</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ number_format($cart['total'], 2) }} EUR
                    </p>
                </div>

                <div class="flex gap-2">
                    <form method="POST" action="{{ route('cart.destroy') }}">
                        @csrf
                        @method('DELETE')
                        <flux:button type="submit" icon="x-mark" variant="ghost">Clear</flux:button>
                    </form>

                    @guest
                        <flux:button icon="credit-card" variant="primary" :href="route('login')">Login to checkout</flux:button>
                    @else
                        @can('checkout')
                            <flux:button icon="credit-card" variant="primary" :href="route('checkout.show')">Checkout</flux:button>
                        @else
                            <flux:button icon="credit-card" variant="primary" disabled>Checkout</flux:button>
                        @endcan
                    @endguest
                </div>
            </div>
        @endif
    </div>
</x-layouts::main-content>
