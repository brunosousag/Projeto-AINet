<x-layouts::main-content title="New Administrative"
                        heading="Create a administrative"
                        subheading='Click on "Save" button to store the information.'>
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <form method="POST" action="{{ route('administratives.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mt-6 space-y-4">
                        @include('administratives.partials.fields', ['mode' => 'create'])
                    </div>
                    @include('partials.form-buttons', ['entity' => 'administrative', 'save' => true, 'cancel' => true])
                </form>
            </section>
        </div>
    </div>
</x-layouts::main-content>
