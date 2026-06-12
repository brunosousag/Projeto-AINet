<x-layouts::main-content title="New User"
                         heading="New user"
                         subheading="Create a store account">
    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        @include('users.partials.fields', ['mode' => 'create'])
        @include('partials.form-buttons', ['entity' => 'user', 'save' => true, 'cancel' => true])
    </form>
</x-layouts::main-content>
