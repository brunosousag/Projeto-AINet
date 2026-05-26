@php
    $mode = $mode ?? 'edit';
    $readonly = $mode == 'show';
@endphp


<div class="flex flex-col sm:flex-row sm:justify-between space-x-8">
    <div class="grow mt-6 space-y-4">
        <div class="w-full flex flex-col space-y-4 sm:flex-row sm:space-x-4 sm:space-y-0">
            <div class="w-full sm:w-64">
                <flux:input name="abbreviation" label="Abbreviation" value="{{ old('abbreviation', $course->abbreviation) }}"
                    :disabled="$readonly" :readonly="$mode == 'edit'"/>
            </div>

            <flux:radio.group name="type" label="Type of course" :disabled="$readonly" variant="pills"
                class="ps-8 py-2">
                <flux:radio value="Degree" label=" Degree" :checked="old('type', $course->type) == 'Degree'" />
                <flux:radio value="Master" label="Master " :checked="old('type', $course->type) == 'Master'" />
                <flux:radio value="TESP" label="TESP" :checked="old('type', $course->type) == 'TESP'" />
                <flux:error name="type" />
            </flux:radio.group>
        </div>

        <flux:input name="name" label="Name" value="{{ old('name', $course->name) }}" :disabled="$readonly" />

        <flux:input name="name_pt" label="Name (Portuguese)" value="{{ old('name_pt', $course->name_pt) }}" :disabled="$readonly" />

        <div class="flex flex-row sm:flex-row sm:space-x-4">
            <div class="w-full">
                <flux:input name="semesters" label="Nº Semesters" value="{{ old('semesters', $course->semesters) }}" :disabled="$readonly" />
            </div>
            <div class="w-full">
                <flux:input name="ECTS" label="Nº ECTS" value="{{ old('ECTS', $course->ECTS) }}" :disabled="$readonly" />
            </div>
            <div class="w-full">
                <flux:input name="places" label="Nº Places" value="{{ old('places', $course->places) }}" :disabled="$readonly" />
            </div>
        </div>

        <flux:input name="contact" label="Contact" value="{{ old('contact', $course->contact) }}" :disabled="$readonly" />

        <flux:textarea name="objectives" label="Objective" :disabled="$readonly" :resize="$readonly ? 'none' : 'vertical'" rows="5" >
            {{ old('objectives', $course->objectives) }}
        </flux:textarea>
        <flux:error name="objectives" />

        <flux:textarea name="objectives_pt" label="Objective (Portuguese)" :disabled="$readonly" :resize="$readonly ? 'none' : 'vertical'" rows="5" >
            {{ old('objectives_pt', $course->objectives_pt) }}
        </flux:textarea>
        <flux:error name="objectives_pt" />
    </div>
    <div>
        <x-field.image
            name="image_file"
            label="Image"
            width="md"
            :readonly="$readonly"
            deleteTitle="Delete Image"
            :deleteAllow="($mode == 'edit') && ($course->imageUrl)"
            deleteForm="form_to_delete_course_image"
            :imageUrl="$course->imageUrl"
            class="sm:-mt-[1.5rem] w-full sm:w-64"/>
    </div>
</div>
