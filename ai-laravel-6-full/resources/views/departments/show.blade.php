<x-layouts::main-content :title="$department->name"
                        :heading="'Department '. $department->name">
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <div class="mt-6 space-y-4">
                    @include('departments.partials.fields', ['mode' => 'show'])
                </div>
                @include('partials.form-buttons',[
                        'entity' => 'department',
                        'value' => $department,
                        'new' => Gate::check('create', \App\Models\Department::class),
                        'edit' => Gate::check('update', $department),
                        'delete' => Gate::check('delete', $department)
                        ])
                @if($department->teachers->isEmpty())
                    <p class="mt-12 text-xl text-gray-700 dark:text-gray-300">
                        This department has no assigned teachers.
                    </p>
                @else
                    @can('viewAny', \App\Models\Teacher::class)
                        <h3 class="pt-16 pb-4 text-xl font-medium text-gray-700 dark:text-gray-300">
                            Teachers
                        </h3>
                        <x-teachers.table :teachers="$department->teachers"
                                        :showDepartment="false"
                                        :showView="true"
                                        :showEdit="false"
                                        :showDelete="false"
                                        class="pt-4"
                        />

                    @endcan
                @endif
            </section>
        </div>
    </div>

    <form id="delete-form" method="POST" action="{{ route('departments.destroy', ['department' => $department]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts::main-content>
