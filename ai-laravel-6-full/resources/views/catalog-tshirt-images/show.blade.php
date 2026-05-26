<x-layouts::main-content :title="$tshirtImage->name"
                         :heading="$tshirtImage->name"
                         subheading="{{ $tshirtImage->category?->name ?? 'No category' }}">
    <div class="grid gap-6 lg:grid-cols-[320px_1fr]">
        <div class="rounded-lg border border-zinc-200 bg-zinc-100 p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <img src="{{ $tshirtImage->image_full_url }}"
                 alt="{{ $tshirtImage->name }}"
                 class="aspect-square w-full object-contain">
        </div>

        <div class="space-y-4">
            @if ($tshirtImage->description)
                <p class="text-zinc-700 dark:text-zinc-300">{{ $tshirtImage->description }}</p>
            @endif

            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                {{ $tshirtImage->order_items_count }} order item(s)
            </p>

            @include('partials.form-buttons', [
                'entity' => 'catalog-image',
                'value' => $tshirtImage,
                'new' => true,
                'edit' => true,
                'delete' => true,
            ])
        </div>
    </div>

    <form id="delete-form" method="POST" action="{{ route('catalog-images.destroy', ['catalogImage' => $tshirtImage]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts::main-content>
