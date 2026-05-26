<x-layouts::main-content title="Upload Image"
                         heading="Upload image"
                         subheading="Create a private image for your own t-shirts">
    <form method="POST" action="{{ route('personal-tshirt-images.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-4">
        @csrf

        <flux:input name="name" label="Name" value="{{ old('name') }}" />

        <flux:textarea name="description" label="Description" rows="4">{{ old('description') }}</flux:textarea>

        <x-field.image name="image_file"
                       label="Image"
                       width="md"
                       :imageUrl="asset('storage/tshirt_images/placeholder.png')" />

        <div class="flex flex-wrap items-center gap-3">
            <flux:button type="submit" icon="cloud-arrow-up" variant="primary">Upload</flux:button>
            <flux:button :href="route('personal-tshirt-images.index')" icon="arrow-left" variant="ghost">Back</flux:button>
        </div>
    </form>
</x-layouts::main-content>
