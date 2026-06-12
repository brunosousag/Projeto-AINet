<x-layouts::main-content :title="$category->name"
                         :heading="'Edit category '.$category->name"
                         subheading="Update catalog category data">
    <form method="POST" action="{{ route('categories.update', ['category' => $category]) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mt-6 space-y-4">
            @include('categories.partials.fields', ['mode' => 'edit'])
        </div>
        @include('partials.form-buttons', [
            'entity' => 'category',
            'value' => $category,
            'new' => true,
            'show' => true,
            'delete' => true,
            'save' => true,
            'cancel' => true,
        ])
    </form>

    <form id="delete-form" method="POST" action="{{ route('categories.destroy', ['category' => $category]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    <form id="form_to_delete_category_image" method="POST" action="{{ route('categories.image.destroy', ['category' => $category]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts::main-content>
