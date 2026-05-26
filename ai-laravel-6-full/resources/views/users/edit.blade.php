<x-layouts::main-content title="Edit User"
                         heading="Edit user"
                         subheading="{{ $user->name }}">
    <form method="POST" action="{{ route('users.update', ['user' => $user]) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        @include('users.partials.fields', ['mode' => 'edit'])
        @include('partials.form-buttons', ['entity' => 'user', 'value' => $user, 'show' => true, 'save' => true, 'cancel' => true])
    </form>
</x-layouts::main-content>
