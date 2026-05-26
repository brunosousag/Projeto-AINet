<x-layouts::main-content :title="$department->name"
                        :heading="'Edit department '. $department->name"
                        subheading='Click on "Save" button to store the information.'>
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <form method="POST" action="{{ route('departments.update', ['department' => $department]) }}">
                    @csrf
                    @method('PUT')
                    <div class="mt-6 space-y-4">
                        @include('departments.partials.fields', ['mode' => 'edit'])
                    </div>
                    @include('partials.form-buttons',[
                            'entity' => 'department',
                            'value' => $department,
                            'new' => Gate::check('create', \App\Models\Department::class),
                            'show' => Gate::check('view', $department),
                            'delete' => Gate::check('delete', $department),
                            'save' => true,
                            'cancel' => true
                            ])

                </form>
            </section>
        </div>
    </div>
    <form id="delete-form" method="POST" action="{{ route('departments.destroy', ['department' => $department]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts::main-content>
