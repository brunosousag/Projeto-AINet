<x-layouts::main-content title="Categories"
                         heading="Categories"
                         subheading="Manage catalog categories">
    <div class="space-y-6">
        <div class="flex flex-wrap items-end gap-3">
            <x-categories.filter-card
                :filterAction="route('categories.index')"
                :resetUrl="route('categories.index')"
                :search="$filters['search']" />

            <div class="grow"></div>
            <flux:button variant="primary" icon="plus" href="{{ route('categories.create') }}">New category</flux:button>
        </div>

        <x-categories.table :categories="$categories"
                            :showView="true"
                            :showEdit="true"
                            :showDelete="true" />

        <div>
            {{ $categories->links() }}
        </div>
    </div>
</x-layouts::main-content>
