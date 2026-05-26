@php
    $mode = $mode ?? 'edit';
    $readonly = $mode == 'show';
    $adminReadonly = $readonly;
    if (!$adminReadonly) {
        if ($mode == 'create') {
            $adminReadonly = !Gate::check('createAdmin', App\Models\User::class);
        } elseif ($mode == 'edit') {
            $adminReadonly = !Gate::check('updateAdmin', $administrative);
        } else {
            $adminReadonly = true;
        }
    }
@endphp
<div class="flex flex-wrap space-x-8">
    <div class="grow mt-6 space-y-4">
        <flux:input name="name" label="Name" :value="old('name', $administrative->name)" :disabled="$readonly" />
        <flux:input name="email" type="email" label="Email" :value="old('email', $administrative->email)" :disabled="$readonly" />
        <flux:radio.group name="gender" label="Gender" :disabled="$readonly" class="flex space-x-8">
            <flux:radio value="M" label="Masculine" :checked="$administrative->gender == 'M'" />
            <flux:radio value="F" label="Feminine" :checked="$administrative->gender == 'F'" />
        </flux:radio.group>
        <flux:error name="gender" />
        <flux:field variant="inline">
            <input type="hidden" name="admin" value="0">
            <flux:checkbox name="admin" :disabled="$adminReadonly" :checked="old('admin', $administrative->admin) == '1'" value="1"/>
            <flux:label>Administrador</flux:label>
            <flux:error name="admin" />
        </flux:field>
    </div>
    <div class="pb-6 pe-12">
        <x-field.image
            name="photo_file"
            label="Photo"
            width="md"
            :readonly="$readonly"
            deleteTitle="Delete Photo"
            :deleteAllow="($mode == 'edit') && ($administrative->photo_url)"
            deleteForm="form_to_delete_photo"
            :imageUrl="$administrative->photoFullUrl"/>
    </div>
</div>
