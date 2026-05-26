<x-layouts::main-content title="Students"
                        heading="List of students"
                        subheading="Manage the students of the institution">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl ">
        <div class="flex justify-start ">
            <div class="my-4 p-6 ">
                <x-students.filter-card
                    :filterAction="route('students.index')"
                    :resetUrl="route('students.index')"
                    :courses="$courseOptions"
                    :course="old('course', $filterByCourse)"
                    :name="old('name', $filterByName)"
                    class="mb-6"
                />
                @can('create', \App\Models\Student::class)
                    <div class="flex items-center gap-4 mb-4">
                        <flux:button variant="primary" href="{{ route('students.create') }}">Create a new student</flux:button>
                    </div>
                @endcan
                <div class="my-4 font-base text-sm text-gray-700 dark:text-gray-300">
                    <x-students.table :students="$students"
                                      :showCourse="true"
                                      :showView="true"
                                      :showEdit="true"
                                      :showDelete="true"
                    />
                </div>
                <div class="mt-4">
                    {{ $students->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layouts::main-content>
