<x-layouts::main-content title="New Catalog Image"
                         heading="Create catalog image"
                         subheading="Store a new public t-shirt image">
    <form method="POST" action="{{ route('catalog-images.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @include('catalog-tshirt-images.partials.fields', ['mode' => 'create'])
        @include('partials.form-buttons', ['entity' => 'catalog-image', 'save' => true, 'cancel' => true])
    </form>
</x-layouts::main-content>
