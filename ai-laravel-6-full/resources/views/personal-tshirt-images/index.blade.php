<x-layouts::main-content title="My Images"
                         heading="My images"
                         subheading="Private images available only to you">
    <div class="space-y-6">
        <div class="flex flex-wrap items-end gap-3">
            <form method="GET" action="{{ route('personal-tshirt-images.index') }}" class="flex flex-wrap items-end gap-3">
                <flux:input name="search" label="Search" value="{{ $filters['search'] }}" />
                <flux:button type="submit" icon="magnifying-glass" variant="primary">Filter</flux:button>
                <flux:button :href="route('personal-tshirt-images.index')" icon="x-mark" variant="ghost">Reset</flux:button>
            </form>

            <div class="grow"></div>
            <flux:button variant="primary" icon="cloud-arrow-up" href="{{ route('personal-tshirt-images.create') }}">Upload image</flux:button>
        </div>

        @if ($tshirtImages->isEmpty())
            <div class="rounded-lg border border-zinc-200 bg-white p-6 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                No personal images found.
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($tshirtImages as $tshirtImage)
                    <article class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="bg-zinc-100 dark:bg-zinc-800">
                            <img src="{{ $tshirtImage->image_full_url }}"
                                 alt="{{ $tshirtImage->name }}"
                                 class="aspect-square w-full object-contain p-6">
                        </div>

                        <div class="space-y-4 p-4">
                            <div>
                                <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $tshirtImage->name }}</h2>
                                @if ($tshirtImage->description)
                                    <p class="mt-2 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $tshirtImage->description }}</p>
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

                                <flux:button type="submit" icon="shopping-cart" variant="primary" class="w-full">Add</flux:button>
                            </form>

                            <form method="POST" action="{{ route('personal-tshirt-images.destroy', ['personal_tshirt_image' => $tshirtImage]) }}">
                                @csrf
                                @method('DELETE')
                                <flux:button type="submit" icon="trash" variant="danger" class="w-full">Delete</flux:button>
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
