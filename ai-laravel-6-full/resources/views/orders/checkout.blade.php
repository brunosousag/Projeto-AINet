<x-layouts::main-content title="Checkout"
                         heading="Checkout"
                         subheading="Confirm delivery and payment data">
    <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <form method="POST" action="{{ route('checkout.store') }}" class="space-y-4">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input name="nif"
                            label="NIF"
                            value="{{ old('nif', $customer?->nif) }}"
                            maxlength="9" />

                <flux:select name="payment_type" label="Payment type">
                    @foreach ($paymentTypes as $paymentType)
                        <option value="{{ $paymentType }}" @selected(old('payment_type', $customer?->default_payment_type) === $paymentType)>
                            {{ $paymentType }}
                        </option>
                    @endforeach
                </flux:select>
            </div>

            <flux:input name="payment_ref"
                        label="Payment reference"
                        value="{{ old('payment_ref', $customer?->default_payment_ref) }}" />

            <flux:textarea name="address" label="Address" rows="4">{{ old('address', $customer?->address) }}</flux:textarea>

            <flux:textarea name="notes" label="Notes" rows="3">{{ old('notes') }}</flux:textarea>

            <flux:checkbox name="save_defaults" value="1" label="Save these data as my default checkout data" :checked="old('save_defaults', true)" />

            <div class="flex flex-wrap items-center gap-3">
                <flux:button type="submit" icon="credit-card" variant="primary">Place order</flux:button>
                <flux:button :href="route('cart.show')" icon="arrow-left" variant="ghost">Back to cart</flux:button>
            </div>
        </form>

        <aside class="space-y-4 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Order summary</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $cart['count'] }} item(s)</p>
            </div>

            <div class="space-y-3">
                @foreach ($cart['lines'] as $item)
                    <div class="flex gap-3">
                        <img src="{{ $item['tshirt_image']->image_full_url }}"
                             alt="{{ $item['tshirt_image']->name }}"
                             class="h-14 w-14 rounded bg-zinc-100 object-contain p-1 dark:bg-zinc-800">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item['tshirt_image']->name }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $item['qty'] }} x {{ $item['size'] }} - {{ $item['color']->name }}
                            </p>
                        </div>
                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ number_format($item['sub_total'], 2) }} EUR
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">Total</span>
                    <span class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($cart['total'], 2) }} EUR</span>
                </div>
            </div>
        </aside>
    </div>
</x-layouts::main-content>
