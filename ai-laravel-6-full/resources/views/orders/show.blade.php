<x-layouts::main-content title="Order #{{ $order->id }}"
                         heading="Order #{{ $order->id }}"
                         subheading="Status: {{ $order->status }}">
    <div class="space-y-6">
        <div class="grid gap-4 lg:grid-cols-3">
            <section class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-3 text-base font-semibold text-zinc-900 dark:text-zinc-100">Customer</h2>
                <div class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <p>{{ $order->customer?->user?->name }}</p>
                    <p>{{ $order->customer?->user?->email }}</p>
                    <p>NIF: {{ $order->nif }}</p>
                </div>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-3 text-base font-semibold text-zinc-900 dark:text-zinc-100">Payment</h2>
                <div class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <p>{{ $order->payment_type }}</p>
                    <p>{{ $order->payment_ref }}</p>
                    <p>{{ $order->date?->format('Y-m-d') }}</p>
                </div>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-3 text-base font-semibold text-zinc-900 dark:text-zinc-100">Delivery</h2>
                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $order->address }}</p>
            </section>
        </div>

        @if ($order->notes || $order->reason_for_cancellation)
            <section class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                @if ($order->notes)
                    <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">Notes</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $order->notes }}</p>
                @endif

                @if ($order->reason_for_cancellation)
                    <h2 class="mb-2 mt-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">Cancellation reason</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $order->reason_for_cancellation }}</p>
                @endif
            </section>
        @endif

        <section class="space-y-3">
            @foreach ($order->items as $item)
                <div class="grid gap-4 rounded-lg border border-zinc-200 bg-white p-4 md:grid-cols-[80px_1fr_auto] dark:border-zinc-700 dark:bg-zinc-900">
                    <img src="{{ $item->tshirtImage->image_full_url }}"
                         alt="{{ $item->tshirtImage->name }}"
                         class="h-20 w-20 rounded bg-zinc-100 object-contain p-2 dark:bg-zinc-800">

                    <div>
                        <h2 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $item->tshirtImage->name }}</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $item->color?->name }} - {{ $item->size }} - {{ $item->qty }} unit(s)
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ number_format((float) $item->unit_price, 2) }} EUR each</p>
                        <p class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format((float) $item->sub_total, 2) }} EUR</p>
                    </div>
                </div>
            @endforeach
        </section>

        <div class="flex flex-wrap items-start gap-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Total</p>
                <p class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format((float) $order->total_price, 2) }} EUR</p>
            </div>

            <div class="grow"></div>

            @if ($order->receipt_url)
                <flux:button icon="document-arrow-down" variant="filled" :href="route('orders.receipt', ['order' => $order])">Receipt</flux:button>
            @endif

            @if ($canManageOrders && $order->status === 'pending')
                <form method="POST" action="{{ route('orders.close', ['order' => $order]) }}">
                    @csrf
                    @method('PATCH')
                    <flux:button type="submit" icon="check" variant="primary">Close</flux:button>
                </form>
            @endif

            @if ($order->status === 'pending')
                <form method="POST" action="{{ route('orders.cancel', ['order' => $order]) }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    @method('PATCH')
                    <flux:input name="reason_for_cancellation" label="Cancellation reason" />
                    <flux:button type="submit" icon="x-mark" variant="danger">Cancel</flux:button>
                </form>
            @endif

            <flux:button icon="arrow-left" variant="ghost" :href="route('orders.index')">Back</flux:button>
        </div>
    </div>
</x-layouts::main-content>
