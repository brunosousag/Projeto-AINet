<x-layouts::main-content :title="__('Courses')"
                        heading="List of courses"
                        subheading="Manage the courses offered by the institution">
  <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl ">
    <div class="flex justify-start ">
      <div class="my-4 p-6 ">
        @can('create', \App\Models\Course::class)
            <div class="flex items-center gap-4 mb-4">
            <flux:button variant="primary" href="{{ route('courses.create') }}">Create a new course</flux:button>
            </div>
        @endcan
        <div class="my-4 font-base text-sm text-gray-700 dark:text-gray-300">
            <x-courses.table :courses="$courses"
                                    :showView="true"
                                    :showEdit="true"
                                    :showDelete="true"
            />
        </div>
        <div class="mt-4">
          {{ $courses->links() }}
        </div>
      </div>
    </div>
  </div>
</x-layouts::main-content>
