<x-layouts::main-content :title="$discipline->name"
                        :heading="'Discipline '. $discipline->name">
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <div class="mt-6 space-y-4">
                    @include('disciplines.partials.fields', ['mode' => 'show'])
                </div>
                @include('partials.form-buttons',[
                        'entity' => 'discipline',
                        'value' => $discipline,
                        'new' => Gate::check('create', \App\Models\Discipline::class),
                        'edit' => Gate::check('update', $discipline),
                        'delete' => Gate::check('delete', $discipline)
                        ])

                @if($discipline->teachers->isEmpty())
                    <p class="mt-12 text-xl text-gray-700 dark:text-gray-300">
                        This discipline has no assigned teachers.
                    </p>
                @else
                    @can('viewAny',  \App\Models\Teacher::class)
                        <h3 class="pt-16 pb-4 text-xl font-medium text-gray-700 dark:text-gray-300">
                            Teachers
                        </h3>
                        <x-teachers.table :teachers="$discipline->teachers"
                                        :showView="true"
                                        :showEdit="false"
                                        :showDelete="false"
                                        class="pt-4"
                        />
                    @endcan
                @endif
            </section>
        </div>
    </div>
    <form id="delete-form" method="POST" action="{{ route('disciplines.destroy', ['discipline' => $discipline]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts.main-content>
