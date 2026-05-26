<x-layouts::main-content title="New Color"
                         heading="Create color"
                         subheading="Store a new t-shirt color">
    <form method="POST" action="{{ route('colors.store') }}">
        @csrf
        <div class="mt-6 space-y-4">
            @include('colors.partials.fields', ['mode' => 'create'])
        </div>
        @include('partials.form-buttons', ['entity' => 'color', 'save' => true, 'cancel' => true])
    </form>
</x-layouts::main-content>
