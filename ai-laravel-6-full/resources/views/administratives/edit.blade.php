<x-layouts::main-content :title="$administrative->name"
                        heading="Edit Administrative"
                        :subheading="$administrative->name">
    <div class="flex flex-col space-y-6">
        <div class="max-full">
            <section>
                <form method="POST" action="{{ route('administratives.update', ['administrative' => $administrative]) }}"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mt-6 space-y-4">
                        @include('administratives.partials.fields', ['mode' => 'edit'])
                    </div>
                    @include('partials.form-buttons',[
                            'entity' => 'administrative',
                            'value' => $administrative,
                            'new' => Gate::check('create', \App\Models\Administrative::class),
                            'show' => Gate::check('view', $administrative),
                            'delete' => Gate::check('delete', $administrative),
                            'save' => true,
                            'cancel' => true
                            ])
                </form>
            </section>
        </div>
    </div>
    <form id="delete-form" method="POST" action="{{ route('administratives.destroy', ['administrative' => $administrative]) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    <form class="hidden" id="form_to_delete_photo"
        method="POST"
        action="{{ route('administratives.photo.destroy', ['administrative' => $administrative]) }}">
        @csrf
        @method('DELETE')
    </form>
</x-layouts::main-content>
