<x-layouts::main-content title="{{ $tshirtImage->name }}"
                         heading="{{ $tshirtImage->name }}"
                         subheading="{{ $tshirtImage->category?->name ?? 'Catalog image' }}">
    <div class="grid gap-6 lg:grid-cols-[minmax(280px,460px)_1fr]">
        <div class="rounded-lg border border-zinc-200 bg-zinc-100 p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <img src="{{ $tshirtImage->image_full_url }}"
                 alt="{{ $tshirtImage->name }}"
                 class="aspect-square w-full object-contain">
        </div>

        <div class="space-y-6">
            @if ($tshirtImage->description)
                <p class="text-zinc-700 dark:text-zinc-300">{{ $tshirtImage->description }}</p>
            @endif

            <form method="POST" action="{{ route('cart.add') }}"
                  class="space-y-4 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                @csrf
                <input type="hidden" name="tshirt_image_id" value="{{ $tshirtImage->id }}">

                <div class="grid gap-3 sm:grid-cols-3">
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

                <div class="flex gap-2">
                    <flux:button type="submit" icon="shopping-cart" variant="primary">Add to cart</flux:button>
                    <flux:button :href="route('tshirt-images.index')" icon="arrow-left" variant="ghost">Back</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::main-content>
