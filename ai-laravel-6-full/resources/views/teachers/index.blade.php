<x-layouts::main-content title="Teachers"
                        heading="List of teachers"
                        subheading="Manage the teachers of the institution">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl ">
        <div class="flex justify-start ">
            <div class="my-4 p-6 ">
                <x-teachers.filter-card
                    :filterAction="route('teachers.index')"
                    :resetUrl="route('teachers.index')"
                    :departments="$departments"
                    :department="old('department', $filterByDepartment)"
                    :name="old('name', $filterByName)"
                    class="mb-6"
                />
                @can('create', \App\Models\Teacher::class)
                    <div class="flex items-center gap-4 mb-4">
                        <flux:button variant="primary" href="{{ route('teachers.create') }}">Create a new teacher</flux:button>
                    </div>
                @endcan
                <div class="my-4 font-base text-sm text-gray-700 dark:text-gray-300">
                    <x-teachers.table :teachers="$teachers"
                                      :showDepartment="true"
                                      :showView="true"
                                      :showEdit="true"
                                      :showDelete="true"
                    />
                </div>
                <div class="mt-4">
                    {{ $teachers->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layouts::main-content>
