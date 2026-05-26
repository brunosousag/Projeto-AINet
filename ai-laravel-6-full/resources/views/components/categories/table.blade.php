<div {{ $attributes }}>
    <table class="table-auto border-collapse">
        <thead>
            <tr class="border-b-2 border-b-gray-400 bg-gray-100 dark:border-b-gray-500 dark:bg-gray-800">
                <th class="px-2 py-2 text-left">Image</th>
                <th class="px-2 py-2 text-left">Name</th>
                <th class="hidden px-2 py-2 text-right sm:table-cell">Images</th>
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
            @foreach ($categories as $category)
                <tr class="border-b border-b-gray-400 dark:border-b-gray-500">
                    <td class="px-2 py-2">
                        <img src="{{ $category->image_full_url }}"
                             alt="{{ $category->name }}"
                             class="h-12 w-12 rounded bg-zinc-100 object-contain p-1 dark:bg-zinc-800">
                    </td>
                    <td class="px-2 py-2 text-left">{{ $category->name }}</td>
                    <td class="hidden px-2 py-2 text-right sm:table-cell">{{ $category->tshirt_images_count }}</td>
                    @if($showView)
                        <td class="ps-2 px-0.5">
                            <a href="{{ route('categories.show', ['category' => $category]) }}">
                                <flux:icon.eye class="size-5 hover:text-green-600" />
                            </a>
                        </td>
                    @endif
                    @if($showEdit)
                        <td class="px-0.5">
                            <a href="{{ route('categories.edit', ['category' => $category]) }}">
                                <flux:icon.pencil-square class="size-5 hover:text-blue-600" />
                            </a>
                        </td>
                    @endif
                    @if($showDelete)
                        <td class="px-0.5">
                            <form method="POST" action="{{ route('categories.destroy', ['category' => $category]) }}" class="flex items-center">
                                @csrf
                                @method('DELETE')
                                <button type="submit">
                                    <flux:icon.trash class="size-5 hover:text-red-600" />
                                </button>
                            </form>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
