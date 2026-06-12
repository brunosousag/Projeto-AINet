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
                    @php
                        $settings = $tshirtImage->custom ?? [];
                        $previewTop = $settings['preview_top'] ?? 25;
                        $previewWidth = $settings['preview_width'] ?? 48;
                        $previewHeight = $settings['preview_height'] ?? 50;
                        $previewOpacity = $settings['preview_opacity'] ?? 100;
                    @endphp
                    <article x-data="{
                                selectedColor: '{{ $colors->first()?->code ?? 'fafafa' }}',
                                previewTop: {{ $previewTop }},
                                previewWidth: {{ $previewWidth }},
                                previewHeight: {{ $previewHeight }},
                                previewOpacity: {{ $previewOpacity }}
                             }"
                             class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="bg-zinc-100 p-5 dark:bg-zinc-800">
                            <x-tshirt-preview :image-url="$tshirtImage->image_full_url"
                                              :alt="$tshirtImage->name"
                                              :color-code="$colors->first()?->code ?? 'fafafa'"
                                              :settings="$tshirtImage->custom"
                                              dynamic
                                              dynamic-placement
                                              class="mx-auto w-full max-w-72" />
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

                                <x-color-swatches :colors="$colors" />

                                <div class="grid grid-cols-2 gap-2">
                                    <flux:select name="size" label="Size">
                                        @foreach ($sizes as $size)
                                            <option value="{{ $size }}">{{ $size }}</option>
                                        @endforeach
                                    </flux:select>

                                    <flux:input type="number" name="qty" label="Quantity" min="1" max="999" value="1" />
                                </div>

                                <div class="grid gap-3 border-t border-zinc-200 pt-3 dark:border-zinc-700">
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

                                <flux:button type="submit" icon="shopping-cart" variant="primary" class="w-full">Add</flux:button>
                            </form>

                            <div class="grid grid-cols-2 gap-2">
                                <flux:button :href="route('personal-tshirt-images.edit', ['personal_tshirt_image' => $tshirtImage])" icon="pencil-square" variant="filled">Edit</flux:button>
                                <form method="POST" action="{{ route('personal-tshirt-images.destroy', ['personal_tshirt_image' => $tshirtImage]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button type="submit" icon="trash" variant="danger" class="w-full">Delete</flux:button>
                                </form>
                            </div>
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
