<x-layouts::main-content title="Users"
                         heading="Users"
                         subheading="Manage customers, employees and administrators">
    <div class="space-y-6">
        <div class="flex flex-wrap items-end gap-3">
            <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-end gap-3">
                <flux:input name="search" label="Search" value="{{ $filters['search'] }}" />

                <flux:select name="user_type" label="Type">
                    <option value="">All types</option>
                    @foreach ($typeLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['user_type'] === $value)>{{ $label }}</option>
                    @endforeach
                </flux:select>

                <flux:select name="blocked" label="Status">
                    <option value="">All statuses</option>
                    <option value="0" @selected($filters['blocked'] === '0')>Active</option>
                    <option value="1" @selected($filters['blocked'] === '1')>Blocked</option>
                </flux:select>

                <flux:button type="submit" icon="magnifying-glass" variant="primary">Filter</flux:button>
                <flux:button :href="route('users.index')" icon="x-mark" variant="ghost">Reset</flux:button>
            </form>

            <div class="grow"></div>
            <flux:button variant="primary" icon="plus" href="{{ route('users.create') }}">New user</flux:button>
        </div>

        @if ($users->isEmpty())
            <div class="rounded-lg border border-zinc-200 bg-white p-6 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                No users found.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] table-auto border-collapse">
                    <thead>
                        <tr class="border-b-2 border-b-gray-400 bg-gray-100 dark:border-b-gray-500 dark:bg-gray-800">
                            <th class="px-2 py-2 text-left">User</th>
                            <th class="px-2 py-2 text-left">Type</th>
                            <th class="px-2 py-2 text-left">Status</th>
                            <th class="px-2 py-2 text-right">Orders</th>
                            <th class="px-2 py-2 text-right">Own images</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="border-b border-b-gray-400 dark:border-b-gray-500">
                                <td class="px-2 py-2 text-left">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $user->photo_full_url }}"
                                             alt="{{ $user->name }}"
                                             class="h-10 w-10 rounded-full bg-zinc-100 object-cover dark:bg-zinc-800">
                                        <div>
                                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</div>
                                            <div class="text-xs text-zinc-500">#{{ $user->id }} - {{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 py-2 text-left">
                                    @if (auth()->id() === $user->id)
                                        {{ $typeLabels[$user->user_type] ?? $user->user_type }}
                                    @else
                                        <form method="POST" action="{{ route('users.change-type', ['user' => $user]) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="user_type"
                                                    class="rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-900">
                                                @foreach ($typeLabels as $value => $label)
                                                    <option value="{{ $value }}" @selected($user->user_type === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" title="Change type">
                                                <flux:icon.check class="size-5 hover:text-blue-600" />
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-left">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded px-2 py-1 text-xs uppercase {{ $user->blocked ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-200' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200' }}">
                                            {{ $user->blocked ? 'Blocked' : 'Active' }}
                                        </span>
                                        @unless (auth()->id() === $user->id)
                                            <form method="POST" action="{{ route('users.block-unblock', ['user' => $user]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" title="{{ $user->blocked ? 'Unblock' : 'Block' }}">
                                                    @if ($user->blocked)
                                                        <flux:icon.lock-open class="size-5 hover:text-emerald-600" />
                                                    @else
                                                        <flux:icon.lock-closed class="size-5 hover:text-red-600" />
                                                    @endif
                                                </button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                                <td class="px-2 py-2 text-right">{{ $user->customer?->orders_count ?? '-' }}</td>
                                <td class="px-2 py-2 text-right">{{ $user->customer?->tshirt_images_count ?? '-' }}</td>
                                <td class="ps-2 px-0.5">
                                    <a href="{{ route('users.show', ['user' => $user]) }}">
                                        <flux:icon.eye class="size-5 hover:text-green-600" />
                                    </a>
                                </td>
                                <td class="px-0.5">
                                    <a href="{{ route('users.edit', ['user' => $user]) }}">
                                        <flux:icon.pencil-square class="size-5 hover:text-blue-600" />
                                    </a>
                                </td>
                                <td class="px-0.5">
                                    @unless (auth()->id() === $user->id)
                                        <form method="POST" action="{{ route('users.destroy', ['user' => $user]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">
                                                <flux:icon.trash class="size-5 hover:text-red-600" />
                                            </button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-layouts::main-content>
