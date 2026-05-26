<x-layouts::main-content title="Catalog"
                         heading="FunShirt Catalog"
                         subheading="Catalog images available for printed t-shirts">
    <div class="space-y-6">
        <form method="GET" action="{{ route('tshirt-images.index') }}"
              class="grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 md:grid-cols-[1fr_220px_auto_auto] dark:border-zinc-700 dark:bg-zinc-900">
            <flux:input name="search" label="Search" value="{{ $filters['search'] }}" />

            <flux:select name="category_id" label="Category">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </flux:select>

            <div class="flex items-end">
                <flux:button type="submit" icon="magnifying-glass" variant="primary">Filter</flux:button>
            </div>
            <div class="flex items-end">
                <flux:button :href="route('tshirt-images.index')" icon="x-mark" variant="ghost">Reset</flux:button>
            </div>
        </form>

        @if ($tshirtImages->isEmpty())
            <div class="rounded-lg border border-zinc-200 bg-white p-6 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                No catalog images found.
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($tshirtImages as $tshirtImage)
                    <article class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                        <a href="{{ route('tshirt-images.show', ['tshirtImage' => $tshirtImage]) }}" class="block bg-zinc-100 dark:bg-zinc-800">
                            <img src="{{ $tshirtImage->image_full_url }}"
                                 alt="{{ $tshirtImage->name }}"
                                 class="aspect-square w-full object-contain p-6">
                        </a>

                        <div class="space-y-4 p-4">
                            <div>
                                <div class="flex items-start justify-between gap-3">
                                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                        <a href="{{ route('tshirt-images.show', ['tshirtImage' => $tshirtImage]) }}">
                                            {{ $tshirtImage->name }}
                                        </a>
                                    </h2>
                                    @if ($tshirtImage->category)
                                        <span class="rounded bg-zinc-100 px-2 py-1 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ $tshirtImage->category->name }}
                                        </span>
                                    @endif
                                </div>
                                @if ($tshirtImage->description)
                                    <p class="mt-2 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ $tshirtImage->description }}
                                    </p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('cart.add') }}" class="grid gap-3">
                                @csrf
                                <input type="hidden" name="tshirt_image_id" value="{{ $tshirtImage->id }}">

                                <div class="grid grid-cols-3 gap-2">
                                    <flux:select name="color_code" label="Color">
                                        @foreach ($colors as $color)
                                            <option value="{{ $color->code }}">{{ $color->name }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:select name="size" label="Size">
                                        @foreach ($sizes as $size)
                                            <option value="{{ $size }}">{{ $size }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:input type="number" name="qty" label="Qty" min="1" max="999" value="1" />
                                </div>

                                <flux:button type="submit" icon="shopping-cart" variant="primary" class="w-full">
                                    Add
                                </flux:button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div>
                {{ $tshirtImages->links() }}
            </div>
        @endif
    </div>
</x-layouts::main-content>
