<x-layouts::main-content title="New Discipline"
                        heading="Create a discipline"
                        subheading='Click on "Save" button to store the information.'>
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <form method="POST" action="{{ route('disciplines.store') }}">
                    @csrf
                    <div class="mt-6 space-y-4">
                        @include('disciplines.partials.fields', ['mode' => 'create'])
                    </div>
                    @include('partials.form-buttons', ['entity' => 'discipline', 'save' => true, 'cancel' => true])
                </form>
            </section>
        </div>
    </div>
</x-layouts::main-content>
