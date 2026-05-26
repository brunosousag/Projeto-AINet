<x-layouts::main-content title="New Course"
                        heading="Create a course"
                        subheading='Click on "Save" button to store the information.'>
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <form method="POST" action="{{ route('courses.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mt-6 space-y-4">
                        @include('courses.partials.fields', ['mode' => 'create'])
                    </div>
                    @include('partials.form-buttons', ['entity' => 'course', 'save' => true, 'cancel' => true])
                </form>
            </section>
        </div>
    </div>
</x-layouts::main-content>
