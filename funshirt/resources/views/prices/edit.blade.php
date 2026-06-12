<x-layouts::main-content title="Prices"
                         heading="Prices"
                         subheading="Configure current t-shirt prices and discount threshold">
    <form method="POST" action="{{ route('prices.update') }}" class="max-w-4xl space-y-4">
        @csrf
        @method('PUT')

        <div class="grid gap-4 md:grid-cols-2">
            <flux:input type="number" step="0.01" min="0" name="unit_price_catalog" label="Catalog image unit price" value="{{ old('unit_price_catalog', $price->unit_price_catalog) }}" />
            <flux:input type="number" step="0.01" min="0" name="unit_price_own" label="Own image unit price" value="{{ old('unit_price_own', $price->unit_price_own) }}" />
            <flux:input type="number" step="0.01" min="0" name="unit_price_catalog_discount" label="Catalog discount unit price" value="{{ old('unit_price_catalog_discount', $price->unit_price_catalog_discount) }}" />
            <flux:input type="number" step="0.01" min="0" name="unit_price_own_discount" label="Own discount unit price" value="{{ old('unit_price_own_discount', $price->unit_price_own_discount) }}" />
            <flux:input type="number" min="1" name="qty_discount" label="Discount quantity" value="{{ old('qty_discount', $price->qty_discount) }}" />
        </div>

        <div class="flex justify-start">
            <flux:button type="submit" icon="check" variant="primary">Save prices</flux:button>
        </div>
    </form>
</x-layouts::main-content>
