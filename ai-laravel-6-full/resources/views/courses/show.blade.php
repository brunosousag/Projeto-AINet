<x-layouts::main-content :title="$course->name"
                        :heading="'Course '. $course->name">
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <div class="mt-6 space-y-4">
                    @include('courses.partials.fields', ['mode' => 'show'])
                </div>
                @include('partials.form-buttons',[
                        'entity' => 'course',
                        'value' => $course,
                        'new' => Gate::check('create', \App\Models\Course::class),
                        'edit' => Gate::check('update', $course),
                        'delete' => Gate::check('delete', $course)
                        ])

                @if($course->disciplines->isEmpty())
                    <p class="mt-12 text-xl text-gray-700 dark:text-gray-300">
                        This course has no assigned disciplines.
                    </p>
                @else
                    @can('viewCurriculum', \App\Models\Course::class)
                        <h3 class="pt-16 pb-4 text-xl font-medium text-gray-700 dark:text-gray-300">
                            Curriculum
                        </h3>
                        <x-courses.curriculum :disciplines="$course->disciplines"
                                            :showView="true"
                                            class="pt-4"
                        />
                    @endcan
                @endif
            </section>
        </div>
    </div>
    <form id="delete-form" method="POST" action="{{ route('courses.destroy', ['course' => $course]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts::main-content>
