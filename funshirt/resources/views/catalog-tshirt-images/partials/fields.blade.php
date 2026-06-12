@php
    $mode = $mode ?? 'edit';
    $readonly = $mode === 'show';
@endphp

<div class="flex flex-col gap-8 lg:flex-row lg:items-start">
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
    </div>

    <x-field.image name="image_file"
                   label="Image"
                   width="md"
                   :readonly="$readonly"
                   :deleteAllow="false"
                   :imageUrl="$tshirtImage->image_full_url"
                   class="w-full lg:w-64" />
</div>
