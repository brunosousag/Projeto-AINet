<x-layouts::main-content title="Teachers"
                        heading="My Teachers"
                        subheading="List of teachers that teach disciplines I am enrolled in.">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl ">
        <div class="flex justify-start ">
            <div class="my-4 p-6 ">
                @if($teachers->isEmpty())
                    <div class="font-base text-lg text-gray-700 dark:text-gray-300">
                        You have no teachers.
                    </div>
                @else
                    <div class="my-4 font-base text-sm text-gray-700 dark:text-gray-300">
                        <x-teachers.table :teachers="$teachers"
                                        :showDepartment="true"
                                        :showView="true"
                                        :showEdit="false"
                                        :showDelete="false"
                        />
                    </div>
                    <div class="mt-4">
                        {{ $teachers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::main-content>
