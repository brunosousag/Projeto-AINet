<x-layouts::main-content title="Disciplines"
                        heading="List of disciplines"
                        subheading="Manage the disciplines offered by the institution">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl ">
        <div class="flex justify-start ">
            <div class="my-4 p-6 ">
                <x-disciplines.filter-card
                    :filterAction="route('disciplines.index')"
                    :resetUrl="route('disciplines.index')"
                    :courses="$sharedCourses->pluck('fullName', 'abbreviation')->toArray()"
                    :course="old('course', $filterByCourse)"
                    :year="old('year', $filterByYear)"
                    :semester="old('semester', $filterBySemester)"
                    :teacher="old('teacher', $filterByTeacher)"
                    class="mb-6"
                    />
                @can('create', \App\Models\Discipline::class)
                    <div class="flex items-center gap-4 mb-4">
                        <flux:button variant="primary" href="{{ route('disciplines.create') }}">Create a new discipline</flux:button>
                    </div>
                @endcan
                <div class="my-4 font-base text-sm text-gray-700 dark:text-gray-300">
                    <x-disciplines.table
                        :disciplines="$disciplines"
                        :showCourse="true"
                        :showView="true"
                        :showEdit="true"
                        :showDelete="true"
                        :showAddToCart="true"
                        :showRemoveFromCart="false"
                    />
                </div>
                <div class="mt-4">
                    {{ $disciplines->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layouts::main-content>
