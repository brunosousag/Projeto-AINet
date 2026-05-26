@php
    $mode = $mode ?? 'edit';
    $readonly = $mode == 'show';
@endphp
<div class="flex flex-wrap space-x-8">
    <div class="grow mt-6 space-y-4">
        <flux:input name="number" label="Number" :value="old('number', $student->number)" :disabled="$readonly" />
        <flux:input name="name" label="Name" :value="old('name', $student->user->name)" :disabled="$readonly" />
        <flux:input name="email" type="email" label="Email" :value="old('email', $student->user->email)" :disabled="$readonly" />
        <flux:radio.group name="gender" label="Gender" :disabled="$readonly" class="flex space-x-8">
            <flux:radio value="M" label="Masculine" :checked="$student->user->gender == 'M'" />
            <flux:radio value="F" label="Feminine" :checked="$student->user->gender == 'F'" />
        </flux:radio.group>
        <flux:error name="gender" />
        <flux:select name="course"  label="Course" :disabled="$readonly">
            @foreach($sharedCourses->pluck('fullName', 'abbreviation')->toArray() as $abbreviation => $name)
                <flux:select.option value="{{ $abbreviation }}"
                                    :selected="old('course', $student->course) == $abbreviation">
                    {{ $name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>
    <div class="pb-6 pe-12">
        <x-field.image
            name="photo_file"
            label="Photo"
            width="md"
            :readonly="$readonly"
            deleteTitle="Delete Photo"
            :deleteAllow="($mode == 'edit') && ($student->user->photo_url)"
            deleteForm="form_to_delete_photo"
            :imageUrl="$student->user->photoFullUrl"/>
    </div>
</div>
