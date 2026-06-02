<form method="GET" action="{{ $filterAction }}" {{ $attributes->class(['flex flex-wrap items-end gap-3']) }}>
    <flux:input name="search" label="Search" value="{{ $search }}" />
    <flux:button type="submit" icon="magnifying-glass" variant="primary">Filter</flux:button>
    <flux:button :href="$resetUrl" icon="x-mark" variant="ghost">Reset</flux:button>
</form>
