@php
    $mode = $mode ?? 'edit';
    $readonly = $mode === 'show';
@endphp

<div class="grid gap-8 lg:grid-cols-[1fr_18rem]">
    <div class="space-y-6">
        <section class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input name="name"
                            label="Name"
                            value="{{ old('name', $user->name) }}"
                            :disabled="$readonly" />

                <flux:input type="email"
                            name="email"
                            label="Email"
                            value="{{ old('email', $user->email) }}"
                            :disabled="$readonly" />

                <flux:select name="user_type" label="Type" :disabled="$readonly">
                    @foreach ($typeLabels as $value => $label)
                        <option value="{{ $value }}" @selected(old('user_type', $user->user_type) === $value)>{{ $label }}</option>
                    @endforeach
                </flux:select>

                <flux:select name="gender" label="Gender" :disabled="$readonly">
                    <option value="M" @selected(old('gender', $user->gender) === 'M')>Masculine</option>
                    <option value="F" @selected(old('gender', $user->gender) === 'F')>Feminine</option>
                </flux:select>
            </div>

            @if (! $readonly)
                <div class="grid gap-4 md:grid-cols-2">
                    <flux:input type="password"
                                name="password"
                                label="{{ $mode === 'create' ? 'Password (default: 123)' : 'New password' }}" />
                    <flux:input type="password"
                                name="password_confirmation"
                                label="Confirm password" />
                </div>

                <flux:checkbox name="blocked"
                               value="1"
                               label="Blocked"
                               :checked="old('blocked', $user->blocked)" />
            @endif
        </section>

    </div>

    <div>
        <x-field.image name="photo_file"
                       label="Photo"
                       width="md"
                       :readonly="$readonly"
                       :deleteAllow="$mode === 'edit' && (bool) $user->photo_url"
                       deleteForm="delete-photo-form"
                       :imageUrl="$user->photo_full_url" />

        @if ($readonly)
            <div class="mt-6 space-y-2 text-sm text-zinc-600 dark:text-zinc-300">
                <p><span class="font-medium">Status:</span> {{ $user->blocked ? 'Blocked' : 'Active' }}</p>
                @if ($user->isCustomer())
                    <p><span class="font-medium">Orders:</span> {{ $user->customer?->orders_count ?? 0 }}</p>
                    <p><span class="font-medium">Own images:</span> {{ $user->customer?->tshirt_images_count ?? 0 }}</p>
                @endif
            </div>
        @endif
    </div>
</div>
