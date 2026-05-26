<x-layouts::main-content title="Students"
                        heading="My Students"
                        subheading="List of students I am teaching.">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl ">
        <div class="flex justify-start ">
            <div class="my-4 p-6 ">
                @if($students->isEmpty())
                    <div class="font-base text-lg text-gray-700 dark:text-gray-300">
                        You have no students.
                    </div>
                @else
                    <div class="my-4 font-base text-sm text-gray-700 dark:text-gray-300">
                        <x-students.table :students="$students"
                                        :showCourse="true"
                                        :showView="true"
                                        :showEdit="false"
                                        :showDelete="false"
                        />
                    </div>
                    <div class="mt-4">
                        {{ $students->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::main-content>
