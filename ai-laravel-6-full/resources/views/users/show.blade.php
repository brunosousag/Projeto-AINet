<x-layouts::main-content title="User"
                         heading="{{ $user->name }}"
                         subheading="{{ $typeLabels[$user->user_type] ?? $user->user_type }}">
    <div class="space-y-6">
        @include('users.partials.fields', ['mode' => 'show'])

        <form id="delete-form" method="POST" action="{{ route('users.destroy', ['user' => $user]) }}">
            @csrf
            @method('DELETE')
        </form>

        @include('partials.form-buttons', [
            'entity' => 'user',
            'value' => $user,
            'edit' => true,
            'delete' => auth()->id() !== $user->id,
            'deleteForm' => 'delete-form',
        ])
    </div>
</x-layouts::main-content>
