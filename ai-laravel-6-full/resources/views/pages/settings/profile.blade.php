<?php

use App\Concerns\ProfileValidationRules;
use App\Models\Customer;
use App\Traits\UserPhotoFileStorage;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules, UserPhotoFileStorage, WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $gender = 'M';
    public ?string $nif = null;
    public ?string $address = null;
    public ?string $default_payment_type = null;
    public ?string $default_payment_ref = null;
    public $photo_file = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user()->load('customer');

        $this->name = $user->name;
        $this->email = $user->email;
        $this->gender = $user->gender ?? 'M';
        $this->nif = $user->customer?->nif;
        $this->address = $user->customer?->address;
        $this->default_payment_type = $user->customer?->default_payment_type;
        $this->default_payment_ref = $user->customer?->default_payment_ref;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            ...$this->profileRules($user->id),
            'gender' => ['required', Rule::in(['M', 'F'])],
            'photo_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:4096'],
            'nif' => ['nullable', 'digits:9'],
            'address' => ['nullable', 'string', 'max:2000'],
            'default_payment_type' => ['nullable', Rule::in(['Visa', 'PayPal', 'MB WAY'])],
            'default_payment_ref' => ['nullable', 'string', 'max:255', 'required_with:default_payment_type'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'gender' => $validated['gender'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        $this->storeUserPhoto($this->photo_file, $user);

        if ($user->isCustomer()) {
            $customer = Customer::withTrashed()->firstOrNew(['id' => $user->id]);
            $customer->fill([
                'nif' => ($validated['nif'] ?? null) ?: null,
                'address' => ($validated['address'] ?? null) ?: null,
                'default_payment_type' => ($validated['default_payment_type'] ?? null) ?: null,
                'default_payment_ref' => ($validated['default_payment_ref'] ?? null) ?: null,
            ]);
            $customer->save();

            if ($customer->trashed()) {
                $customer->restore();
            }
        }

        $this->reset('photo_file');

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    public function deletePhoto(): void
    {
        $this->deleteUserPhoto(Auth::user());

        Flux::toast(variant: 'success', text: __('Photo deleted.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Flux::toast(text: __('A new verification link has been sent to your email address.'));
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your profile and customer defaults')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6" enctype="multipart/form-data">
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

                <flux:select wire:model="gender" :label="__('Gender')" required>
                    <option value="M">{{ __('Male') }}</option>
                    <option value="F">{{ __('Female') }}</option>
                </flux:select>
            </div>

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                    </div>
                @endif
            </div>

            <div class="grid gap-4 md:grid-cols-[6rem_1fr]">
                <img src="{{ $photo_file ? $photo_file->temporaryUrl() : Auth::user()->photo_full_url }}"
                     alt="{{ Auth::user()->name }}"
                     class="h-24 w-24 rounded-lg border border-zinc-200 bg-zinc-100 object-cover dark:border-zinc-700 dark:bg-zinc-800">

                <div>
                    <label for="photo_file" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('Photo') }}
                    </label>
                    <input id="photo_file"
                           wire:model="photo_file"
                           type="file"
                           accept="image/png,image/jpeg"
                           class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 file:mr-4 file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-200">
                    @error('photo_file')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    @if (Auth::user()->photo_url)
                        <flux:button class="mt-3" type="button" wire:click="deletePhoto" icon="trash" variant="danger">
                            {{ __('Delete photo') }}
                        </flux:button>
                    @endif
                </div>
            </div>

            @if (Auth::user()->isCustomer())
                <section class="space-y-4">
                    <flux:heading size="sm">{{ __('Customer defaults') }}</flux:heading>

                    <div class="grid gap-4 md:grid-cols-2">
                        <flux:input wire:model="nif" :label="__('NIF')" type="text" maxlength="9" />

                        <flux:select wire:model="default_payment_type" :label="__('Default payment type')">
                            <option value="">{{ __('No default') }}</option>
                            <option value="Visa">Visa</option>
                            <option value="PayPal">PayPal</option>
                            <option value="MB WAY">MB WAY</option>
                        </flux:select>

                        <flux:input wire:model="default_payment_ref" :label="__('Default payment reference')" type="text" />
                    </div>

                    <flux:textarea wire:model="address" :label="__('Address')" rows="4" />
                </section>
            @endif

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" data-test="update-profile-button">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
