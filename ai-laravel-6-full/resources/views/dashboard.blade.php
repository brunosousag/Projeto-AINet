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
                @if ($management && $canSeeStatistics)
                    <p class="mt-1 text-xs text-zinc-500">{{ number_format($stats['cancellation_rate'], 1) }}% cancellation rate</p>
                @endif
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
                        <flux:button icon="photo" variant="filled" :href="route('shop.index')">Catalog</flux:button>
                        <flux:button icon="clipboard-document-list" variant="filled" :href="route('orders.index')">My orders</flux:button>
                        <flux:button icon="cloud-arrow-up" variant="filled" :href="route('personal-tshirt-images.index')">My images</flux:button>
                    </div>
                </section>
            @endif
        </div>

        @if ($management && $canSeeStatistics)
            @php
                $monthlyMax = max(1, (float) $monthlySales->max('revenue'));
                $colorMax = max(1, (int) $colorSales->max('sold_qty'));
                $sizeMax = max(1, (int) $sizeSales->max('sold_qty'));
                $categoryMax = max(1, (int) $categorySales->max('sold_qty'));
                $designTypeMax = max(1, (int) $designTypeSales->max('sold_qty'));
                $reasonMax = max(1, (int) $cancellationReasons->max('total'));
            @endphp

            <section class="space-y-3">
                <div>
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Monthly closed sales</h2>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Revenue and completed orders during the last 12 months</p>
                </div>

                <div class="grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    @foreach ($monthlySales as $month)
                        <div class="grid grid-cols-[68px_1fr_96px] items-center gap-3 text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ $month['label'] }}</span>
                            <div class="h-3 overflow-hidden rounded bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full rounded bg-green-600"
                                     style="width: {{ $month['revenue'] > 0 ? max(1, $month['revenue'] * 100 / $monthlyMax) : 0 }}%"></div>
                            </div>
                            <span class="text-right text-zinc-700 dark:text-zinc-300">
                                {{ number_format($month['revenue'], 2) }} EUR
                                <span class="block text-xs text-zinc-500">{{ $month['orders'] }} order(s)</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-3">
                <section class="space-y-3">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Best-selling colors</h2>
                    <div class="space-y-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        @foreach ($colorSales as $item)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="flex items-center gap-2">
                                        <span class="size-4 rounded-full border border-zinc-300" style="background-color: #{{ $item->color_code }}"></span>
                                        {{ $item->color?->name ?? $item->color_code }}
                                    </span>
                                    <span>{{ (int) $item->sold_qty }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-full rounded bg-blue-600" style="width: {{ $item->sold_qty * 100 / $colorMax }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="space-y-3">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Sizes sold</h2>
                    <div class="space-y-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        @foreach ($sizeSales as $item)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span>{{ $item->size }}</span>
                                    <span>{{ (int) $item->sold_qty }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-full rounded bg-amber-500" style="width: {{ $item->sold_qty * 100 / $sizeMax }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="space-y-3">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Top customers</h2>
                    <div class="space-y-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        @foreach ($topCustomers as $item)
                            <div class="flex items-start justify-between gap-3 border-b border-zinc-100 pb-2 last:border-0 last:pb-0 dark:border-zinc-800">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item->customer?->user?->name ?? 'Unknown customer' }}</p>
                                    <p class="text-xs text-zinc-500">{{ $item->orders_count }} order(s)</p>
                                </div>
                                <p class="shrink-0 text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ number_format((float) $item->revenue, 2) }} EUR</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <div class="grid gap-6 xl:grid-cols-3">
                <section class="space-y-3">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Best-selling categories</h2>
                    <div class="space-y-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        @foreach ($categorySales as $item)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="truncate">{{ $item->label }}</span>
                                    <span>{{ (int) $item->sold_qty }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-full rounded bg-emerald-600" style="width: {{ $item->sold_qty * 100 / $categoryMax }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="space-y-3">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Catalog vs personal designs</h2>
                    <div class="space-y-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        @foreach ($designTypeSales as $item)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span>{{ $item->label }}</span>
                                    <span>{{ (int) $item->sold_qty }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-full rounded bg-violet-600" style="width: {{ $item->sold_qty * 100 / $designTypeMax }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="space-y-3">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Cancellation reasons</h2>
                    <div class="space-y-3 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        @foreach ($cancellationReasons as $item)
                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="truncate" title="{{ $item['label'] }}">{{ $item['label'] }}</span>
                                    <span>{{ $item['total'] }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-full rounded bg-rose-500" style="width: {{ $item['total'] * 100 / $reasonMax }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        @endif
    </div>
</x-layouts::main-content>
