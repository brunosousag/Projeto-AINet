<x-layouts::main-content title="Upload Image"
                         heading="Upload image"
                         subheading="Create a private image for your own t-shirts">
    <form method="POST" action="{{ route('personal-tshirt-images.store') }}" enctype="multipart/form-data" class="max-w-5xl space-y-4">
        @csrf

        @include('personal-tshirt-images.partials.fields', ['tshirtImage' => null])

        <div class="flex flex-wrap items-center gap-3">
            <flux:button type="submit" icon="cloud-arrow-up" variant="primary">Upload</flux:button>
            <flux:button :href="route('personal-tshirt-images.index')" icon="arrow-left" variant="ghost">Back</flux:button>
        </div>
    </form>
</x-layouts::main-content>
