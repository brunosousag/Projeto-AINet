<div>
    <table class="table-auto border-collapse">
        <thead>
        <tr class="border-b-2 border-b-gray-400 dark:border-b-gray-500 bg-gray-100 dark:bg-gray-800">
            <th class="px-2 py-2 text-left">Name</th>
            <th class="px-2 py-2 text-left hidden md:table-cell">Email</th>
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
        @foreach ($administratives as $administrative)
            <tr class="border-b border-b-gray-400 dark:border-b-gray-500">
                <td class="px-2 py-2 text-left">{{ $administrative->name }}</td>
                <td class="px-2 py-2 text-left hidden md:table-cell">{{ $administrative->email }}</td>
                <td class="px-2 py-2 text-center">{{ $administrative->admin ? 'Yes' : '-'}}</td>
                @if($showView)
                    @can('view', $administrative)
                        <td class="ps-2 px-0.5">
                            <a href="{{ route('administratives.show', ['administrative' => $administrative]) }}">
                                <flux:icon.eye class="size-5 hover:text-green-600" />
                            </a>
                        </td>
                    @else
                        <td></td>
                    @endcan
                @endif
                @if($showEdit)
                    @can('update', $administrative)
                        <td class="px-0.5">
                            <a href="{{ route('administratives.edit', ['administrative' => $administrative]) }}">
                                <flux:icon.pencil-square class="size-5 hover:text-blue-600" />
                            </a>
                        </td>
                    @else
                        <td></td>
                    @endcan
                @endif
                @if($showDelete)
                    @can('delete', $administrative)
                    <td class="px-0.5">
                        <form method="POST" action="{{ route('administratives.destroy', ['administrative' => $administrative]) }}" class="flex items-center">
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
