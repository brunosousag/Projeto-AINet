@php
    $mode = $mode ?? 'edit';
    $readonly = $mode === 'show';
    $code = old('code', $color->code);
@endphp

<div class="flex flex-col gap-4 sm:max-w-2xl">
    <div class="flex items-end gap-4">
        <flux:input name="code"
                    label="Hex code"
                    value="{{ $code }}"
                    :disabled="$readonly"
                    :readonly="$mode === 'edit'" />

        <span class="mb-1 block h-10 w-16 rounded border border-zinc-300 dark:border-zinc-600"
              style="background-color: #{{ $code ?: 'ffffff' }}"></span>
    </div>

    <flux:input name="name"
                label="Name"
                value="{{ old('name', $color->name) }}"
                :disabled="$readonly" />
</div>
