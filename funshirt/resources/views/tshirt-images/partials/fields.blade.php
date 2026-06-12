@php
    $mode = $mode ?? 'edit';
    $readonly = $mode === 'show';
    $settings = $tshirtImage->custom ?? [];
    $previewTop = old('preview_top', $settings['preview_top'] ?? 25);
    $previewWidth = old('preview_width', $settings['preview_width'] ?? 48);
    $previewHeight = old('preview_height', $settings['preview_height'] ?? 50);
    $previewOpacity = old('preview_opacity', $settings['preview_opacity'] ?? 100);
    $selectedColor = $colors->first()?->code ?? 'fcfbff';
    $previewImage = $tshirtImage->image_full_url;
@endphp

<div x-data="{
        selectedColor: @js($selectedColor),
        previewImage: @js($previewImage),
        previewTop: {{ $previewTop }},
        previewWidth: {{ $previewWidth }},
        previewHeight: {{ $previewHeight }},
        previewOpacity: {{ $previewOpacity }}
     }"
     x-on:image-preview-selected.window="previewImage = $event.detail"
     class="space-y-6">
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px]">
        <div class="grow space-y-4">
        <flux:input name="name"
                    label="Name"
                    value="{{ old('name', $tshirtImage->name) }}"
                    :disabled="$readonly" />

        <flux:select name="category_id" label="Category" :disabled="$readonly">
            <option value="">No category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $tshirtImage->category_id) === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </flux:select>

        <flux:textarea name="description"
                       label="Description"
                       :disabled="$readonly"
                       rows="5">{{ old('description', $tshirtImage->description) }}</flux:textarea>

        <x-field.image name="image_file"
                       label="{{ $mode === 'create' ? 'Design image' : 'Replace design image' }}"
                       width="sm"
                       :readonly="$readonly"
                       :deleteAllow="false"
                       :imageUrl="$tshirtImage->image_full_url" />
        </div>

        <section class="space-y-4">
            <div>
                <p class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">T-shirt preview</p>
                <x-tshirt-preview :image-url="$previewImage"
                                  :alt="$tshirtImage->name ?: 'Catalog design'"
                                  :color-code="$selectedColor"
                                  :settings="$settings"
                                  dynamic
                                  dynamic-placement
                                  dynamic-image
                                  class="w-full rounded border border-zinc-200 dark:border-zinc-700" />
            </div>

            <x-color-swatches :colors="$colors" :selected="$selectedColor" />
        </section>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
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
</div>
