<x-layouts::main-content title="Categories"
                         heading="Categories"
                         subheading="Manage catalog categories">
    <div class="space-y-6">
        <div class="flex flex-wrap items-end gap-3">
            <form method="GET" action="{{ route('categories.index') }}" class="flex flex-wrap items-end gap-3">
                <flux:input name="search" label="Search" value="{{ $filters['search'] }}" />
                <flux:button type="submit" icon="magnifying-glass" variant="primary">Filter</flux:button>
                <flux:button :href="route('categories.index')" icon="x-mark" variant="ghost">Reset</flux:button>
            </form>

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
