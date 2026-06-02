<x-layouts::main-content title="Edit Image"
                         heading="Edit image"
                         subheading="Update your private image and its t-shirt preview">
    <form method="POST"
          action="{{ route('personal-tshirt-images.update', ['personal_tshirt_image' => $tshirtImage]) }}"
          enctype="multipart/form-data"
          class="max-w-5xl space-y-4">
        @csrf
        @method('PUT')

        @include('personal-tshirt-images.partials.fields', ['tshirtImage' => $tshirtImage])

        <div class="flex flex-wrap items-center gap-3">
            <flux:button type="submit" icon="check" variant="primary">Save</flux:button>
            <flux:button :href="route('personal-tshirt-images.index')" icon="arrow-left" variant="ghost">Back</flux:button>
        </div>
    </form>
</x-layouts::main-content>
