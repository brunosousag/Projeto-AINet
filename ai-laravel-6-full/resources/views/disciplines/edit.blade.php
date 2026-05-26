<x-layouts::main-content :title="$discipline->name"
                        :heading="'Edit discipline '. $discipline->name"
                        subheading='Click on "Save" button to store the information.'>
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <form method="POST" action="{{ route('disciplines.update', ['discipline' => $discipline]) }}">
                    @csrf
                    @method('PUT')
                    <div class="mt-6 space-y-4">
                        @include('disciplines.partials.fields', ['mode' => 'edit'])
                    </div>
                    @include('partials.form-buttons',[
                            'entity' => 'discipline',
                            'value' => $discipline,
                            'new' => Gate::check('create', \App\Models\Discipline::class),
                            'show' => Gate::check('view', $discipline),
                            'delete' => Gate::check('delete', $discipline),
                            'save' => true,
                            'cancel' => true
                            ])
                </form>
            </section>
        </div>
    </div>
    <form id="delete-form" method="POST" action="{{ route('disciplines.destroy', ['discipline' => $discipline]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts::main-content>
