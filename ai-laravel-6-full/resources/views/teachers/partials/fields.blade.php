@php
    $mode = $mode ?? 'edit';
    $readonly = $mode == 'show';
    $adminReadonly = $readonly;
    if (!$adminReadonly) {
        if ($mode == 'create') {
            $adminReadonly = !Gate::check('createAdmin', App\Models\Teacher::class);
        } elseif ($mode == 'edit') {
            $adminReadonly = !Gate::check('updateAdmin', $teacher);
        } else {
            $adminReadonly = true;
        }
    }
@endphp

<div class="flex flex-wrap space-x-8">
    <div class="grow mt-6 space-y-4">
        <flux:input name="name" label="Name" :value="old('name', $teacher->user->name)" :disabled="$readonly" />
        <flux:input name="email" type="email" label="Email" :value="old('email', $teacher->user->email)" :disabled="$readonly" />
        <flux:radio.group name="gender" label="Gender" :disabled="$readonly" class="flex space-x-8">
            <flux:radio value="M" label="Masculine" :checked="$teacher->user->gender == 'M'" />
            <flux:radio value="F" label="Feminine" :checked="$teacher->user->gender == 'F'" />
        </flux:radio.group>
        <flux:error name="gender" />
        <flux:select name="department"  label="Department" :disabled="$readonly">
            @foreach($departments as $abbreviation => $name)
                <flux:select.option value="{{ $abbreviation }}"
                                    :selected="old('department', $teacher->department) == $abbreviation">
                    {{ $name }}</flux:select.option>
            @endforeach
        </flux:select>
        <div class="flex space-x-4">
            <flux:input name="office" label="Office" :value="old('office', $teacher->office)" :disabled="$readonly" />
            <flux:input name="extension" label="Extension" :value="old('extension', $teacher->extension)" :disabled="$readonly" />
            <flux:input name="locker" label="Locker" :value="old('locker', $teacher->locker)" :disabled="$readonly" />
        </div>
        <flux:field variant="inline">
            <input type="hidden" name="admin" value="0">
            <flux:checkbox name="admin" :disabled="$adminReadonly"  :checked="old('admin', $teacher->user->admin) == '1'" value="1"/>
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
            :deleteAllow="($mode == 'edit') && ($teacher->user->photo_url)"
            deleteForm="form_to_delete_photo"
            :imageUrl="$teacher->user->photoFullUrl"/>
    </div>
</div>
