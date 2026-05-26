<x-layouts::main-content :title="$tshirtImage->name"
                         :heading="'Edit catalog image '.$tshirtImage->name"
                         subheading="Update public t-shirt image data">
    <form method="POST" action="{{ route('catalog-images.update', ['catalogImage' => $tshirtImage]) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')
        @include('catalog-tshirt-images.partials.fields', ['mode' => 'edit'])
        @include('partials.form-buttons', [
            'entity' => 'catalog-image',
            'value' => $tshirtImage,
            'new' => true,
            'show' => true,
            'delete' => true,
            'save' => true,
            'cancel' => true,
        ])
    </form>

    <form id="delete-form" method="POST" action="{{ route('catalog-images.destroy', ['catalogImage' => $tshirtImage]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts::main-content>
