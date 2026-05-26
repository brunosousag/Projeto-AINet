<x-layouts::main-content title="Dashboard"
                         heading="{{ ($mode ?? 'management') === 'management' ? 'Dashboard' : 'My dashboard' }}"
                         subheading="{{ ($mode ?? 'management') === 'management' ? 'Store activity overview' : 'Your orders and uploaded images' }}">
    @php
        $management = ($mode ?? 'management') === 'management';
    @endphp

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <section class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $management ? 'Closed revenue' : 'Closed order value' }}</p>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($stats['closed_revenue'], 2) }} EUR</p>
                <p class="mt-1 text-xs text-zinc-500">Pending: {{ number_format($stats['pending_revenue'], 2) }} EUR</p>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Orders</p>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $stats['orders'] }}</p>
                <p class="mt-1 text-xs text-zinc-500">{{ $stats['pending_orders'] }} pending, {{ $stats['closed_orders'] }} closed, {{ $stats['canceled_orders'] }} canceled</p>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $management ? 'Customers' : 'My images' }}</p>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $management ? $stats['customers'] : $stats['personal_images'] }}</p>
                <p class="mt-1 text-xs text-zinc-500">{{ $management ? 'Active customer records' : 'Uploaded private images' }}</p>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $management ? 'Images' : 'Catalog images' }}</p>
                <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $management ? $stats['catalog_images'] + $stats['personal_images'] : $stats['catalog_images'] }}</p>
                <p class="mt-1 text-xs text-zinc-500">{{ $management ? "{$stats['catalog_images']} catalog, {$stats['personal_images']} customer-owned" : 'Available public designs' }}</p>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Latest orders</h2>
                    <flux:button icon="clipboard-document-list" variant="ghost" :href="route('orders.index')">Orders</flux:button>
                </div>

                @if ($latestOrders->isEmpty())
                    <div class="rounded-lg border border-zinc-200 bg-white p-6 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                        No orders yet.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[720px] table-auto border-collapse">
                            <thead>
                                <tr class="border-b-2 border-b-gray-400 bg-gray-100 dark:border-b-gray-500 dark:bg-gray-800">
                                    <th class="px-2 py-2 text-left">Order</th>
                                    @if ($management)
                                        <th class="px-2 py-2 text-left">Customer</th>
                                    @endif
                                    <th class="px-2 py-2 text-left">Status</th>
                                    <th class="px-2 py-2 text-right">Items</th>
                                    <th class="px-2 py-2 text-right">Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($latestOrders as $order)
                                    <tr class="border-b border-b-gray-400 dark:border-b-gray-500">
                                        <td class="px-2 py-2 text-left">#{{ $order->id }}</td>
                                        @if ($management)
                                            <td class="px-2 py-2 text-left">
                                                <div>{{ $order->customer?->user?->name ?? 'Unknown customer' }}</div>
                                                <div class="text-xs text-zinc-500">{{ $order->date?->format('Y-m-d') }}</div>
                                            </td>
                                        @endif
                                        <td class="px-2 py-2 text-left">
                                            <span class="rounded bg-zinc-100 px-2 py-1 text-xs uppercase text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td class="px-2 py-2 text-right">{{ $order->items_count }}</td>
                                        <td class="px-2 py-2 text-right">{{ number_format((float) $order->total_price, 2) }} EUR</td>
                                        <td class="ps-2 px-0.5">
                                            <a href="{{ route('orders.show', ['order' => $order]) }}">
                                                <flux:icon.eye class="size-5 hover:text-green-600" />
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            @if ($management)
                <section class="space-y-3">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Top images</h2>

                    @if ($topImages->isEmpty())
                        <div class="rounded-lg border border-zinc-200 bg-white p-6 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                            No sold images yet.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($topImages as $item)
                                <div class="grid grid-cols-[4rem_1fr_auto] items-center gap-3 rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                                    <img src="{{ $item->tshirtImage?->image_full_url ?? asset('storage/tshirt_images/placeholder.png') }}"
                                         alt="{{ $item->tshirtImage?->name ?? 'Deleted image' }}"
                                         class="h-16 w-16 rounded bg-zinc-100 object-contain p-2 dark:bg-zinc-800">
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item->tshirtImage?->name ?? 'Deleted image' }}</p>
                                        <p class="text-xs text-zinc-500">{{ (int) $item->sold_qty }} units sold</p>
                                    </div>
                                    <p class="text-right text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format((float) $item->revenue, 2) }} EUR</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @else
                <section class="space-y-3">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Shortcuts</h2>
                    <div class="grid gap-3">
                        <flux:button icon="photo" variant="filled" :href="route('tshirt-images.index')">Catalog</flux:button>
                        <flux:button icon="clipboard-document-list" variant="filled" :href="route('orders.index')">My orders</flux:button>
                        <flux:button icon="cloud-arrow-up" variant="filled" :href="route('personal-tshirt-images.index')">My images</flux:button>
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-layouts::main-content>
