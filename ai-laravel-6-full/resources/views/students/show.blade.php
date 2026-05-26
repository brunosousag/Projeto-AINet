<x-layouts::main-content :title="$student->name"
                        heading="View Student"
                        :subheading="$student->user->name">
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <div class="mt-6 space-y-4">
                    @include('students.partials.fields', ['mode' => 'show'])
                </div>
                @include('partials.form-buttons',[
                        'entity' => 'student',
                        'value' => $student,
                        'new' => Gate::check('create', \App\Models\Student::class),
                        'edit' => Gate::check('update', $student),
                        'delete' => Gate::check('delete', $student)
                        ])
                @if($student->disciplines->isEmpty())
                    <p class="mt-12 text-xl text-gray-700 dark:text-gray-300">
                        This student is not enrolled in any disciplines.
                    </p>
                @else
                    @can('viewAny', \App\Models\Discipline::class)
                        <h3 class="pt-16 pb-4 text-xl font-medium text-gray-700 dark:text-gray-300">
                            Disciplines
                        </h3>
                        <x-disciplines.table :disciplines="$student->disciplines"
                                            :showView="true"
                                            :showEdit="false"
                                            :showDelete="false"
                                            :showAddToCart="true"
                                            :showRemoveFromCart="false"
                                            class="pt-4"
                        />
                    @endcan
                @endif
            </section>
        </div>
    </div>
    <form id="delete-form" method="POST" action="{{ route('students.destroy', ['student' => $student]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts.main-content>
