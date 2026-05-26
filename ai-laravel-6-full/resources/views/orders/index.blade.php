<x-layouts::main-content title="Orders"
                         heading="{{ $canManageOrders ? 'Orders' : 'My orders' }}"
                         subheading="Track t-shirt orders">
    <div class="space-y-6">
        <form method="GET" action="{{ route('orders.index') }}" class="flex flex-wrap items-end gap-3">
            <flux:input name="search" label="Search" value="{{ $filters['search'] }}" />

            <flux:select name="status" label="Status">
                <option value="">All</option>
                @foreach (['pending' => 'Pending', 'closed' => 'Closed', 'canceled' => 'Canceled'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </flux:select>

            <flux:button type="submit" icon="magnifying-glass" variant="primary">Filter</flux:button>
            <flux:button :href="route('orders.index')" icon="x-mark" variant="ghost">Reset</flux:button>
        </form>

        @if ($orders->isEmpty())
            <div class="rounded-lg border border-zinc-200 bg-white p-6 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                No orders found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] table-auto border-collapse">
                    <thead>
                        <tr class="border-b-2 border-b-gray-400 bg-gray-100 dark:border-b-gray-500 dark:bg-gray-800">
                            <th class="px-2 py-2 text-left">Order</th>
                            @if ($canManageOrders)
                                <th class="px-2 py-2 text-left">Customer</th>
                            @endif
                            <th class="px-2 py-2 text-left">Date</th>
                            <th class="px-2 py-2 text-left">Status</th>
                            <th class="px-2 py-2 text-right">Items</th>
                            <th class="px-2 py-2 text-right">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr class="border-b border-b-gray-400 dark:border-b-gray-500">
                                <td class="px-2 py-2 text-left">#{{ $order->id }}</td>
                                @if ($canManageOrders)
                                    <td class="px-2 py-2 text-left">
                                        <div>{{ $order->customer?->user?->name ?? 'Unknown customer' }}</div>
                                        <div class="text-xs text-zinc-500">{{ $order->customer?->user?->email }}</div>
                                    </td>
                                @endif
                                <td class="px-2 py-2 text-left">{{ $order->date?->format('Y-m-d') }}</td>
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

            <div>
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-layouts::main-content>
