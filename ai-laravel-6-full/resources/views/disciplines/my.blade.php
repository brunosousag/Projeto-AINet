<x-layouts::main-content title="Disciplines"
                        heading="My Disciplines"
                        :subheading="match(auth()?->user()?->type) {
                                'T' => 'List of disciplines I am teaching.',
                                'S' => 'List of disciplines I am enrolled in.',
                                default => 'List of disciplines I am associated to.',
                            }">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl ">
        <div class="flex justify-start ">
            <div class="my-4 p-6 ">
                <div class="my-4 font-base text-sm text-gray-700 dark:text-gray-300">
                    <x-disciplines.table :disciplines="$disciplines"
                        :showCourse="true"
                        :showView="true"
                        :showEdit="false"
                        :showDelete="false"
                        :showAddToCart="false"
                        :showRemoveFromCart="false"
                        />
                </div>
            </div>
        </div>
    </div>
</x-layouts::main-content>
