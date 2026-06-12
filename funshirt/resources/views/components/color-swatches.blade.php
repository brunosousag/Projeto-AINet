@props([
    'colors',
    'selected' => null,
])

@php
    $selected ??= $colors->first()?->code;
@endphp

<fieldset class="space-y-2">
    <legend class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Color</legend>

    <div class="flex flex-wrap gap-2">
        @foreach ($colors as $color)
            <label class="relative cursor-pointer" title="{{ $color->name }}">
                <input type="radio"
                       name="color_code"
                       value="{{ $color->code }}"
                       class="peer sr-only"
                       x-model="selectedColor"
                       @checked($selected === $color->code)>

                <span class="flex size-8 items-center justify-center rounded-full border border-zinc-300 shadow-sm transition peer-focus-visible:ring-2 peer-focus-visible:ring-accent peer-focus-visible:ring-offset-2 peer-checked:ring-2 peer-checked:ring-zinc-900 peer-checked:ring-offset-2 dark:border-zinc-600 dark:peer-checked:ring-white"
                      style="background-color: #{{ $color->code }}">
                    <span x-cloak
                          x-show="selectedColor === '{{ $color->code }}'"
                          class="flex size-4 items-center justify-center rounded-full bg-white/90 text-zinc-900 shadow">
                        <flux:icon.check class="size-3" />
                    </span>
                </span>

                <span class="sr-only">{{ $color->name }}</span>
            </label>
        @endforeach
    </div>
</fieldset>
