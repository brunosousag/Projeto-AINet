@php
    $mode = $mode ?? 'edit';
    $readonly = $mode === 'show';
    $code = old('code', $color->code);
    $pickerCode = preg_match('/^[0-9a-fA-F]{6}$/', $code ?? '') ? strtolower($code) : 'ffffff';
@endphp

<div x-data="{ code: @js($code ?: 'ffffff'), pickerCode: @js($pickerCode) }"
     class="flex flex-col gap-4 sm:max-w-2xl">
    <div class="flex flex-wrap items-end gap-4">
        <flux:input name="code"
                    label="Hex code"
                    value="{{ $code }}"
                    x-model="code"
                    x-on:input="code = $event.target.value.replace('#', ''); if (/^[0-9a-fA-F]{6}$/.test(code)) pickerCode = code.toLowerCase()"
                    :disabled="$readonly"
                    :readonly="$mode === 'edit'" />

        @if ($mode === 'create')
            <label class="cursor-pointer text-sm font-medium text-zinc-700 dark:text-zinc-300">
                Color picker
                <input type="color"
                       x-model="pickerCode"
                       x-on:input="code = pickerCode.slice(1)"
                       class="mt-2 block h-12 w-24 cursor-pointer rounded border border-zinc-300 bg-transparent p-1 dark:border-zinc-600">
            </label>
        @else
            <span class="mb-1 block h-10 w-16 rounded border border-zinc-300 dark:border-zinc-600"
                  x-bind:style="'background-color: #' + code"></span>
        @endif
    </div>

    <flux:input name="name"
                label="Name"
                value="{{ old('name', $color->name) }}"
                :disabled="$readonly" />

    @if ($readonly)
        <img src="{{ asset("storage/tshirt_base/{$color->code}.jpg") }}"
             alt="{{ $color->name }}"
             class="w-48 rounded border border-zinc-200 bg-white dark:border-zinc-700">
    @else
        <x-field.image name="base_image_file"
                       label="{{ $mode === 'create' ? 'T-shirt base image (JPEG)' : 'Replace t-shirt base image (JPEG)' }}"
                       width="md"
                       accept="image/jpeg"
                       :deleteAllow="false"
                       :imageUrl="$color->exists ? asset('storage/tshirt_base/'.$color->code.'.jpg') : asset('storage/tshirt_images/placeholder.png')" />
    @endif
</div>
