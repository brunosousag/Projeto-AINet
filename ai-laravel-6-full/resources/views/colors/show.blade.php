<x-layouts::main-content :title="$color->name"
                         :heading="'Color '.$color->name"
                         subheading="T-shirt color details">
    <div class="mt-6 space-y-4">
        @include('colors.partials.fields', ['mode' => 'show'])

        <p class="text-sm text-zinc-600 dark:text-zinc-300">
            {{ $color->order_items_count }} order item(s)
        </p>
    </div>

    @include('partials.form-buttons', [
        'entity' => 'color',
        'value' => $color,
        'new' => true,
        'edit' => true,
        'delete' => true,
    ])

    <form id="delete-form" method="POST" action="{{ route('colors.destroy', ['color' => $color]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts::main-content>
