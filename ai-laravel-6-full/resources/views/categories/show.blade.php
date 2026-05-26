<x-layouts::main-content :title="$category->name"
                         :heading="'Category '.$category->name"
                         subheading="Catalog category details">
    <div class="mt-6 space-y-4">
        @include('categories.partials.fields', ['mode' => 'show'])

        <p class="text-sm text-zinc-600 dark:text-zinc-300">
            {{ $category->tshirt_images_count }} catalog image(s)
        </p>
    </div>

    @include('partials.form-buttons', [
        'entity' => 'category',
        'value' => $category,
        'new' => true,
        'edit' => true,
        'delete' => true,
    ])

    <form id="delete-form" method="POST" action="{{ route('categories.destroy', ['category' => $category]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts::main-content>
