<div>
    <table class="table-auto border-collapse">
        <thead>
        <tr class="border-b-2 border-b-gray-400 dark:border-b-gray-500 bg-gray-100 dark:bg-gray-800">
            <th class="px-2 py-2 text-left">Name</th>
            @if($showDepartment)
                <th class="px-2 py-2 text-left hidden md:table-cell">Department</th>
            @endif
            <th class="px-2 py-2 text-left hidden sm:table-cell">Email</th>
            <th class="px-2 py-2 text-left hidden md:table-cell">Office</th>
            <th class="px-2 py-2 text-right hidden md:table-cell">Extension</th>
            <th class="px-2 py-2 text-left hidden md:table-cell">Locker</th>
            <th class="px-2 py-2 text-center">Adm.</th>
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
        @foreach ($teachers as $teacher)
            <tr class="border-b border-b-gray-400 dark:border-b-gray-500">
                <td class="px-2 py-2 text-left">{{ $teacher->user->name }}</td>
                @if($showDepartment)
                    <td class="px-2 py-2 text-left hidden md:table-cell">{{ $teacher?->departmentRef?->name }}</td>
                @endif
                <td class="px-2 py-2 text-left hidden sm:table-cell">{{ $teacher->user->email }}</td>
                <td class="px-2 py-2 text-left hidden md:table-cell">{{$teacher->office }}</td>
                <td class="px-2 py-2 text-right hidden md:table-cell">{{ $teacher->extension }}</td>
                <td class="px-2 py-2 text-left hidden md:table-cell">{{ $teacher->locker }}</td>
                <td class="px-2 py-2 text-center">{{ $teacher->user->admin ? 'Yes' : '-'}}</td>
                @if($showView)
                    @can('view', $teacher)
                        <td class="ps-2 px-0.5">
                            <a href="{{ route('teachers.show', ['teacher' => $teacher]) }}">
                                <flux:icon.eye class="size-5 hover:text-green-600" />
                            </a>
                        </td>
                    @else
                        <td></td>
                    @endcan
                @endif
                @if($showEdit)
                    @can('update', $teacher)
                        <td class="px-0.5">
                            <a href="{{ route('teachers.edit', ['teacher' => $teacher]) }}">
                                <flux:icon.pencil-square class="size-5 hover:text-blue-600" />
                            </a>
                        </td>
                    @else
                        <td></td>
                    @endcan
                @endif
                @if($showDelete)
                    @can('delete', $teacher)
                        <td class="px-0.5">
                            <form method="POST" action="{{ route('teachers.destroy', ['teacher' => $teacher]) }}" class="flex items-center">
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
