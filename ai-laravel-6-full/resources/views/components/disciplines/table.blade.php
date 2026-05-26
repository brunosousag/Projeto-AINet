<div {{ $attributes }}>
    <table class="table-auto border-collapse">
        <thead>
        <tr class="border-b-2 border-b-gray-400 dark:border-b-gray-500 bg-gray-100 dark:bg-gray-800">
            <th class="px-2 py-2 text-left">Abbreviation</th>
            <th class="px-2 py-2 text-left">Name</th>
            @if($showCourse)
                <th class="px-2 py-2 text-left  hidden md:table-cell">Course</th>
            @endif
            <th class="px-2 py-2 text-left hidden sm:table-cell">Year</th>
            <th class="px-2 py-2 text-left hidden sm:table-cell">Semester</th>
            <th class="px-2 py-2 text-left hidden sm:table-cell">ECTS</th>
            <th class="px-2 py-2 text-left hidden sm:table-cell">Hours</th>
            <th class="px-2 py-2 text-left hidden md:table-cell">Optional</th>
            @if($showView)
                <th></th>
            @endif
            @if($showEdit)
                <th></th>
            @endif
            @if($showDelete)
                <th></th>
            @endif
            @can('use-cart')
                @if($showAddToCart)
                    <th></th>
                @endif
                @if($showRemoveFromCart)
                    <th></th>
                @endif
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach ($disciplines as $discipline)
            <tr class="border-b border-b-gray-400 dark:border-b-gray-500">
                <td class="px-2 py-2 text-left">{{ $discipline->abbreviation }}</td>
                <td class="px-2 py-2 text-left">{{ $discipline->name }}</td>
                @if($showCourse)
                    <td class="px-2 py-2 text-left hidden md:table-cell">{{ $discipline->courseRef->name }}</td>
                @endif
                <td class="px-2 py-2 text-left hidden sm:table-cell">{{ $discipline->year }}</td>
                <td class="px-2 py-2 text-left hidden sm:table-cell">{{ $discipline->semesterDescription }}</td>
                <td class="px-2 py-2 text-left hidden sm:table-cell">{{ $discipline->ECTS }}</td>
                <td class="px-2 py-2 text-left hidden sm:table-cell">{{ $discipline->hours }}</td>
                <td class="px-2 py-2 text-left hidden md:table-cell">{{ $discipline->optional ? 'optional' : '' }}</td>
                @if($showView)
                    @can('view', $discipline)
                        <td class="ps-2 px-0.5">
                            <a href="{{ route('disciplines.show', ['discipline' => $discipline]) }}">
                                <flux:icon.eye class="size-5 hover:text-green-600" />
                            </a>
                        </td>
                    @else
                        <td></td>
                    @endcan
                @endif
                @if($showEdit)
                    @can('update', $discipline)
                        <td class="px-0.5">
                            <a href="{{ route('disciplines.edit', ['discipline' => $discipline]) }}">
                                <flux:icon.pencil-square class="size-5 hover:text-blue-600" />
                            </a>
                        </td>
                    @else
                        <td></td>
                    @endcan
                @endif
                @if($showDelete)
                    @can('delete', $discipline)
                        <td class="px-0.5">
                            <form method="POST" action="{{ route('disciplines.destroy', ['discipline' => $discipline]) }}" class="flex items-center">
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
                @can('use-cart')
                    @if($showAddToCart)
                        <td class="pl-4">
                            <form method="POST" action="{{ route('cart.add', ['discipline' => $discipline]) }}" class="flex items-center">
                                @csrf
                                <button type="submit">
                                    <flux:icon.shopping-cart class="size-5 hover:text-green-600" />
                                </button>
                            </form>
                        </td>
                    @endif
                    @if($showRemoveFromCart)
                        <td class="pl-4">
                            <form method="POST" action="{{ route('cart.remove', ['discipline' => $discipline]) }}" class="flex items-center">
                                @csrf
                                @method('DELETE')
                                <button type="submit">
                                    <flux:icon.minus-circle class="size-5 hover:text-red-600" />
                                </button>
                            </form>
                        </td>
                    @endif
                @endcan
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
