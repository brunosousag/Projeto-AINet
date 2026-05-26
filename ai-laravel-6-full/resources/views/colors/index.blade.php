<x-layouts::main-content title="Colors"
                         heading="Colors"
                         subheading="Manage available t-shirt colors">
    <div class="space-y-6">
        <div class="flex flex-wrap items-end gap-3">
            <form method="GET" action="{{ route('colors.index') }}" class="flex flex-wrap items-end gap-3">
                <flux:input name="search" label="Search" value="{{ $filters['search'] }}" />
                <flux:button type="submit" icon="magnifying-glass" variant="primary">Filter</flux:button>
                <flux:button :href="route('colors.index')" icon="x-mark" variant="ghost">Reset</flux:button>
            </form>

            <div class="grow"></div>
            <flux:button variant="primary" icon="plus" href="{{ route('colors.create') }}">New color</flux:button>
        </div>

        <x-colors.table :colors="$colors"
                        :showView="true"
                        :showEdit="true"
                        :showDelete="true" />

        <div>
            {{ $colors->links() }}
        </div>
    </div>
</x-layouts::main-content>
