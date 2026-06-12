@php
    $mode = $mode ?? 'edit';
    $readonly = $mode === 'show';
@endphp

<div class="flex flex-col gap-8 sm:flex-row sm:items-start">
    <div class="grow space-y-4">
        <flux:input name="name"
                    label="Name"
                    value="{{ old('name', $category->name) }}"
                    :disabled="$readonly" />
    </div>

    <x-field.image name="image_file"
                   label="Image"
                   width="md"
                   :readonly="$readonly"
                   deleteTitle="Delete image"
                   :deleteAllow="($mode === 'edit') && (bool) $category->image_url"
                   deleteForm="form_to_delete_category_image"
                   :imageUrl="$category->image_full_url"
                   class="w-full sm:w-64" />
</div>
