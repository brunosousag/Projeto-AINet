<div {{ $attributes }}>
    <table class="table-auto border-collapse">
        <thead>
        <tr class="border-b-2 border-b-gray-400 dark:border-b-gray-500 bg-gray-100 dark:bg-gray-800">
            <th class="px-2 py-2 text-left">Abbreviation</th>
            <th class="px-2 py-2 text-left">Name</th>
            <th class="px-2 py-2 text-left hidden sm:table-cell">Type</th>
            <th class="px-2 py-2 text-right hidden sm:table-cell">Nº Semesters</th>
            <th class="px-2 py-2 text-right hidden sm:table-cell">Nº Places</th>
            @if($showView)
                <th></th>
            @endif
            @if($showEdit)
                <th></th>
            @endif
            @if($showDelete)
                <th></th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach ($courses as $course)
            <tr class="border-b border-b-gray-400 dark:border-b-gray-500">
                <td class="px-2 py-2 text-left">{{ $course->abbreviation }}</td>
                <td class="px-2 py-2 text-left">{{ $course->name }}</td>
                <td class="px-2 py-2 text-left hidden sm:table-cell">{{ $course->type }}</td>
                <td class="px-2 py-2 text-right hidden sm:table-cell">{{ $course->semesters }}</td>
                <td class="px-2 py-2 text-right hidden sm:table-cell">{{ $course->places }}</td>
                @if($showView)
                    @can('view', $course)
                        <td class="ps-2 px-0.5">
                            <a href="{{ route('courses.show', ['course' => $course]) }}">
                                <flux:icon.eye class="size-5 hover:text-green-600" />
                            </a>
                        </td>
                    @else
                        <td></td>
                    @endcan
                @endif
                @if($showEdit)
                    @can('update', $course)
                        <td class="px-0.5">
                            <a href="{{ route('courses.edit', ['course' => $course]) }}">
                                <flux:icon.pencil-square class="size-5 hover:text-blue-600" />
                            </a>
                        </td>
                    @else
                        <td></td>
                    @endcan
                @endif
                @if($showDelete)
                    @can('delete', $course)
                        <td class="px-0.5">
                            <form method="POST" action="{{ route('courses.destroy', ['course' => $course]) }}" class="flex items-center">
                                @csrf
                                @method('DELETE')
                                <button type="submit">
                                    <flux:icon.trash class="size-5 hover:text-red-600" />
                                </button>
                            </form>
                        </td>
                    @else
                        <td></td>
                    @endcan
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
