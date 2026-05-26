<x-layouts::main-content :title="$student->name"
                        heading="Edit Student"
                        :subheading="$student->user->name">
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <form method="POST" action="{{ route('students.update', ['student' => $student]) }}"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mt-6 space-y-4">
                        @include('students.partials.fields', ['mode' => 'edit'])
                    </div>
                    @include('partials.form-buttons',[
                            'entity' => 'student',
                            'value' => $student,
                            'new' => Gate::check('create', \App\Models\Student::class),
                            'show' => Gate::check('view', $student),
                            'delete' => Gate::check('delete', $student),
                            'save' => true,
                            'cancel' => true
                            ])
                </form>
            </section>
        </div>
    </div>
    <form id="delete-form" method="POST" action="{{ route('students.destroy', ['student' => $student]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    <form class="hidden" id="form_to_delete_photo"
        method="POST"
        action="{{ route('students.photo.destroy', ['student' => $student]) }}">
        @csrf
        @method('DELETE')
    </form>
</x-layouts::main-content>
