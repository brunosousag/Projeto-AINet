<x-layouts::main-content :title="$administrative->name"
                        heading="View Administrative"
                        :subheading="$administrative->name">
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <div class="mt-6 space-y-4">
                    @include('administratives.partials.fields', ['mode' => 'show'])
                </div>
                @include('partials.form-buttons',[
                        'entity' => 'administrative',
                        'value' => $administrative,
                        'new' => Gate::check('create', \App\Models\User::class),
                        'edit' => Gate::check('update', $administrative),
                        'delete' => Gate::check('delete', $administrative)
                        ])

            </section>
        </div>
    </div>
    <form id="delete-form" method="POST" action="{{ route('administratives.destroy', ['administrative' => $administrative]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts.main-content>
