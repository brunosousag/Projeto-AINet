<div {{ $attributes }}>
    <form method="GET" action="{{ $filterAction }}">
        <div class="flex justify-between space-x-3">
            <div class="grow flex flex-col space-y-2">
                <div>
                    <flux:select name="course" label="Course">
                        @foreach ($listCourses as $value => $description)
                            <flux:select.option value="{{ $value }}" :selected="$course === $value">{{ $description }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="flex space-x-3">
                    <div class="flex-1/2">
                        <flux:select class="grow" name="year" label="Year">
                            @foreach ($listYears as $value => $description)
                                <flux:select.option value="{{ $value }}"  :selected="$year === $value">{{ $description }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div class="flex-1/2">
                        <flux:select  class="grow" name="semester" label="Semester">
                            @foreach ($listSemesters as $value => $description)
                                <flux:select.option value="{{ $value }}" :selected="$semester === $value">{{ $description }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
                <div>
                    <flux:input name="teacher" label="Teacher" class="grow" value="{{ $teacher }}"/>
                </div>
            </div>
            <div class="grow-0 flex flex-col space-y-3 justify-start">
                <div class="pt-6">
                    <flux:button variant="filled" type="submit" class="w-full">Filter</flux:button>
                </div>
                <div>
                    <flux:button variant="subtle" :href="$resetUrl"  class="w-full">Cancel</flux:button>
                </div>
            </div>
        </div>
    </form>
</div>
