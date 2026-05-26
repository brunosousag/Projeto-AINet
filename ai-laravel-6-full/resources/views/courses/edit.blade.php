<x-layouts::main-content :title="$course->name"
                         :heading="'Edit course '. $course->name"
                         subheading='Click on "Save" button to store the information.'>
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <form method="POST" action="{{ route('courses.update', ['course' => $course]) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mt-6 space-y-4">
                        @include('courses.partials.fields', ['mode' => 'edit'])
                    </div>
                    @include('partials.form-buttons',[
                            'entity' => 'course',
                            'value' => $course,
                            'new' => Gate::check('create', \App\Models\Course::class),
                            'show' => Gate::check('view', $course),
                            'delete' => Gate::check('delete', $course),
                            'save' => true,
                            'cancel' => true
                            ])
                </form>
            </section>
        </div>
    </div>
    <form id="delete-form" method="POST" action="{{ route('courses.destroy', ['course' => $course]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    <form class="hidden" id="form_to_delete_course_image"
        method="POST"
        action="{{ route('courses.image.destroy', ['course' => $course]) }}">
        @csrf
        @method('DELETE')
    </form>
</x-layouts::main-content>

