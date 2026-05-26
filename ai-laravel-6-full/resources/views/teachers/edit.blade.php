<x-layouts::main-content :title="$teacher->name"
                        heading="Edit Teacher"
                        :subheading="$teacher->user->name">
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <form method="POST" action="{{ route('teachers.update', ['teacher' => $teacher]) }}"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mt-6 space-y-4">
                        @include('teachers.partials.fields', ['mode' => 'edit'])
                    </div>
                    @include('partials.form-buttons',[
                            'entity' => 'teacher',
                            'value' => $teacher,
                            'new' => Gate::check('create', \App\Models\Teacher::class),
                            'show' => Gate::check('view', $teacher),
                            'delete' => Gate::check('delete', $teacher),
                            'save' => true,
                            'cancel' => true
                            ])
                </form>
            </section>
        </div>
    </div>
    <form id="delete-form" method="POST" action="{{ route('teachers.destroy', ['teacher' => $teacher]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    <form class="hidden" id="form_to_delete_photo"
        method="POST"
        action="{{ route('teachers.photo.destroy', ['teacher' => $teacher]) }}">
        @csrf
        @method('DELETE')
    </form>
</x-layouts::main-content>
