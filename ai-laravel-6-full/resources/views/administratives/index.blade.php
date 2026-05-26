<x-layouts::main-content title="Administratives"
                        heading="List of administratives"
                        subheading="Manage the administratives of the institution">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl ">
        <div class="flex justify-start ">
            <div class="my-4 p-6 ">
                <x-administratives.filter-card
                    :filterAction="route('administratives.index')"
                    :resetUrl="route('administratives.index')"
                    :name="old('name', $filterByName)"
                    class="mb-6"
                />
                @can('create', \App\Models\User::class)
                    <div class="flex items-center gap-4 mb-4">
                        <flux:button variant="primary" href="{{ route('administratives.create') }}">Create a new administrative</flux:button>
                    </div>
                @endcan
                <div class="my-4 font-base text-sm text-gray-700 dark:text-gray-300">
                    <x-administratives.table :administratives="$administratives"
                                             :showView="true"
                                             :showEdit="true"
                                             :showDelete="true"
                    />
                </div>
                <div class="mt-4">
                    {{ $administratives->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layouts::main-content>
