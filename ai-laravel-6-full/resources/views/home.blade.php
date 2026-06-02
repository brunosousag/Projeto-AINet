<x-layouts::main-content title="FunShirt" heading="FunShirt">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                Create your custom T-shirt
            </h2>

            <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                Browse the catalog, add designs to the cart and complete your order through checkout.
            </p>

            <div class="mt-6">
                <flux:button variant="primary" icon="shopping-bag" :href="route('shop.index')">
                    Open catalog
                </flux:button>
            </div>
        </div>
    </div>
</x-layouts::main-content>
