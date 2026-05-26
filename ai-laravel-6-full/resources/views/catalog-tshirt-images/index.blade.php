<x-layouts::main-content title="Catalog Images"
                         heading="Catalog images"
                         subheading="Manage public t-shirt catalog images">
    <div class="space-y-6">
        <div class="flex flex-wrap items-end gap-3">
            <form method="GET" action="{{ route('catalog-images.index') }}" class="flex flex-wrap items-end gap-3">
                <flux:input name="search" label="Search" value="{{ $filters['search'] }}" />

                <flux:select name="category_id" label="Category">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </flux:select>

                <flux:button type="submit" icon="magnifying-glass" variant="primary">Filter</flux:button>
                <flux:button :href="route('catalog-images.index')" icon="x-mark" variant="ghost">Reset</flux:button>
            </form>

            <div class="grow"></div>
            <flux:button variant="primary" icon="plus" href="{{ route('catalog-images.create') }}">New image</flux:button>
        </div>

        @if ($tshirtImages->isEmpty())
            <div class="rounded-lg border border-zinc-200 bg-white p-6 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                No catalog images found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] table-auto border-collapse">
                    <thead>
                        <tr class="border-b-2 border-b-gray-400 bg-gray-100 dark:border-b-gray-500 dark:bg-gray-800">
                            <th class="px-2 py-2 text-left">Image</th>
                            <th class="px-2 py-2 text-left">Name</th>
                            <th class="px-2 py-2 text-left">Category</th>
                            <th class="px-2 py-2 text-right">Order items</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tshirtImages as $tshirtImage)
                            <tr class="border-b border-b-gray-400 dark:border-b-gray-500">
                                <td class="px-2 py-2">
                                    <img src="{{ $tshirtImage->image_full_url }}"
                                         alt="{{ $tshirtImage->name }}"
                                         class="h-14 w-14 rounded bg-zinc-100 object-contain p-1 dark:bg-zinc-800">
                                </td>
                                <td class="px-2 py-2 text-left">{{ $tshirtImage->name }}</td>
                                <td class="px-2 py-2 text-left">{{ $tshirtImage->category?->name ?? '-' }}</td>
                                <td class="px-2 py-2 text-right">{{ $tshirtImage->order_items_count }}</td>
                                <td class="ps-2 px-0.5">
                                    <a href="{{ route('catalog-images.show', ['catalogImage' => $tshirtImage]) }}">
                                        <flux:icon.eye class="size-5 hover:text-green-600" />
                                    </a>
                                </td>
                                <td class="px-0.5">
                                    <a href="{{ route('catalog-images.edit', ['catalogImage' => $tshirtImage]) }}">
                                        <flux:icon.pencil-square class="size-5 hover:text-blue-600" />
                                    </a>
                                </td>
                                <td class="px-0.5">
                                    <form method="POST" action="{{ route('catalog-images.destroy', ['catalogImage' => $tshirtImage]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">
                                            <flux:icon.trash class="size-5 hover:text-red-600" />
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                {{ $tshirtImages->links() }}
            </div>
        @endif
    </div>
</x-layouts::main-content>
