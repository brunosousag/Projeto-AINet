<x-layouts::main-content :title="$color->name"
                         :heading="'Edit color '.$color->name"
                         subheading="Update t-shirt color data">
    <form method="POST" action="{{ route('colors.update', ['color' => $color]) }}">
        @csrf
        @method('PUT')
        <div class="mt-6 space-y-4">
            @include('colors.partials.fields', ['mode' => 'edit'])
        </div>
        @include('partials.form-buttons', [
            'entity' => 'color',
            'value' => $color,
            'new' => true,
            'show' => true,
            'delete' => true,
            'save' => true,
            'cancel' => true,
        ])
    </form>

    <form id="delete-form" method="POST" action="{{ route('colors.destroy', ['color' => $color]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts::main-content>
