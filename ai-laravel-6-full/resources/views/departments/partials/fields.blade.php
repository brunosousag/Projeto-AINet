@php
    $mode = $mode ?? 'edit';
    $readonly = $mode == 'show';
@endphp

<div class="w-full sm:w-96">
    <flux:input name="abbreviation" label="Abbreviation" value="{{ old('abbreviation', $department->abbreviation) }}"
                :disabled="$readonly" :readonly="$mode == 'edit'"/>
</div>

<flux:input name="name" label="Name" value="{{ old('name', $department->name) }}" :disabled="$readonly" />

<flux:input name="name_pt" label="Name (Portuguese)" value="{{ old('name_pt', $department->name_pt) }}" :disabled="$readonly" />
