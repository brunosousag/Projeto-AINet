<x-layouts::main-content title="New Category"
                         heading="Create category"
                         subheading="Store a new catalog category">
    <form method="POST" action="{{ route('categories.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mt-6 space-y-4">
            @include('categories.partials.fields', ['mode' => 'create'])
        </div>
        @include('partials.form-buttons', ['entity' => 'category', 'save' => true, 'cancel' => true])
    </form>
</x-layouts::main-content>
