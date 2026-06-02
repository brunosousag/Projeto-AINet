<x-layouts::main-content title="{{ $tshirtImage->name }}"
                         heading="{{ $tshirtImage->name }}"
                         subheading="{{ $tshirtImage->category?->name ?? 'Catalog image' }}">
    @php
        $settings = $tshirtImage->custom ?? [];
        $previewTop = $settings['preview_top'] ?? 25;
        $previewWidth = $settings['preview_width'] ?? 48;
        $previewHeight = $settings['preview_height'] ?? 50;
        $previewOpacity = $settings['preview_opacity'] ?? 100;
    @endphp

    <div x-data="{
            selectedColor: '{{ $colors->first()?->code ?? 'fafafa' }}',
            previewTop: {{ $previewTop }},
            previewWidth: {{ $previewWidth }},
            previewHeight: {{ $previewHeight }},
            previewOpacity: {{ $previewOpacity }}
         }"
         class="grid gap-6 lg:grid-cols-[minmax(280px,460px)_1fr]">
        <div class="rounded-lg border border-zinc-200 bg-zinc-100 p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <x-tshirt-preview :image-url="$tshirtImage->image_full_url"
                              :alt="$tshirtImage->name"
                              :color-code="$colors->first()?->code ?? 'fafafa'"
                              :settings="$tshirtImage->custom"
                              dynamic
                              dynamic-placement
                              class="mx-auto w-full max-w-md" />
        </div>

        <div class="space-y-6">
            @if ($tshirtImage->description)
                <p class="text-zinc-700 dark:text-zinc-300">{{ $tshirtImage->description }}</p>
            @endif

            <form method="POST" action="{{ route('cart.add') }}"
                  class="space-y-4 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                @csrf
                <input type="hidden" name="tshirt_image_id" value="{{ $tshirtImage->id }}">

                <x-color-swatches :colors="$colors" />

                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:select name="size" label="Size">
                        @foreach ($sizes as $size)
                            <option value="{{ $size }}">{{ $size }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input type="number" name="qty" label="Quantity" min="1" max="999" value="1" />
                </div>

                <div class="grid gap-4 border-t border-zinc-200 pt-4 sm:grid-cols-2 dark:border-zinc-700">
                    <label class="text-sm text-zinc-700 dark:text-zinc-300">
                        Top position: <span x-text="previewTop"></span>%
                        <input type="range" name="preview_top" min="0" max="70" x-model="previewTop" class="mt-2 block w-full">
                    </label>

                    <label class="text-sm text-zinc-700 dark:text-zinc-300">
                        Width: <span x-text="previewWidth"></span>%
                        <input type="range" name="preview_width" min="10" max="90" x-model="previewWidth" class="mt-2 block w-full">
                    </label>

                    <label class="text-sm text-zinc-700 dark:text-zinc-300">
                        Height: <span x-text="previewHeight"></span>%
                        <input type="range" name="preview_height" min="10" max="90" x-model="previewHeight" class="mt-2 block w-full">
                    </label>

                    <label class="text-sm text-zinc-700 dark:text-zinc-300">
                        Opacity: <span x-text="previewOpacity"></span>%
                        <input type="range" name="preview_opacity" min="10" max="100" x-model="previewOpacity" class="mt-2 block w-full">
                    </label>
                </div>

                <div class="flex gap-2">
                    <flux:button type="submit" icon="shopping-cart" variant="primary">Add to cart</flux:button>
                    <flux:button :href="route('shop.index')" icon="arrow-left" variant="ghost">Back</flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::main-content>
