@php
    $mode = $mode ?? 'edit';
    $readonly = $mode == 'show';
@endphp

<div class="w-full sm:w-96">
    <flux:input name="abbreviation" label="Abbreviation" value="{{ old('abbreviation', $discipline->abbreviation) }}"
        :disabled="$readonly" :readonly="$mode == 'edit'"/>
</div>

<flux:input name="name" label="Name" value="{{ old('name', $discipline->name) }}" :disabled="$readonly" />

<flux:input name="name_pt" label="Name (Portuguese)" value="{{ old('name_pt', $discipline->name_pt) }}" :disabled="$readonly" />

<flux:select name="course"  label="Course" :disabled="$readonly">
    @foreach($courses as $course)
        <flux:select.option value="{{ $course->abbreviation }}"
            :selected="old('course', $discipline->course) == $course->abbreviation">
            {{ $course->fullName}}</flux:select.option>
    @endforeach
</flux:select>

<div class="flex flex-row sm:flex-row sm space-x-4">
    <flux:input name="year" label="Year" value="{{ old('year', $discipline->year) }}" :disabled="$readonly" />
    <flux:input name="semester" label="Semester" value="{{ old('semester', $discipline->semester) }}" :disabled="$readonly" />
    <flux:input name="ECTS" label="ECTS" value="{{ old('ECTS', $discipline->ECTS) }}" :disabled="$readonly" />
    <flux:input name="hours" label="Hours" value="{{ old('hours', $discipline->hours) }}" :disabled="$readonly" />
</div>

<input type="hidden" name="optional" value="0"/>
<flux:checkbox name="optional" label="Optional" value="1" :checked="old('optional', $discipline->optional) == '1'" :disabled="$readonly" />
<flux:error name="optional" />
