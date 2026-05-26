<x-layouts::main-content :title="$teacher->name"
                        heading="View Teacher"
                        :subheading="$teacher->user->name">
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <div class="mt-6 space-y-4">
                    @include('teachers.partials.fields', ['mode' => 'show'])
                </div>
                @include('partials.form-buttons',[
                        'entity' => 'teacher',
                        'value' => $teacher,
                        'new' => Gate::check('create', \App\Models\Teacher::class),
                        'edit' => Gate::check('update', $teacher),
                        'delete' => Gate::check('delete', $teacher)
                        ])

                @if($teacher->disciplines->isEmpty())
                    <p class="mt-12 text-xl text-gray-700 dark:text-gray-300">
                        This teacher is not assigned to any disciplines.
                    </p>
                @else
                    @can('viewAny', \App\Models\Discipline::class)
                        <h3 class="pt-16 pb-4 text-xl font-medium text-gray-700 dark:text-gray-300">
                            Disciplines
                        </h3>
                        <x-disciplines.table :disciplines="$teacher->disciplines"
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
    <form id="delete-form" method="POST" action="{{ route('teachers.destroy', ['teacher' => $teacher]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts.main-content>
