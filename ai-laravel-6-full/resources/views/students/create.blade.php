<x-layouts::main-content title="New Student"
                        heading="Create a student"
                        subheading='Click on "Save" button to store the information.'>
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <form method="POST" action="{{ route('students.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mt-6 space-y-4">
                        @include('students.partials.fields', ['mode' => 'create'])
                    </div>
                    @include('partials.form-buttons', ['entity' => 'student', 'save' => true, 'cancel' => true])
                </form>
            </section>
        </div>
    </div>
</x-layouts::main-content>
