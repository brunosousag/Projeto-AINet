<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::middleware(['auth'])->group(function () {
    Route::get('settings', fn () => redirect()->route(
        auth()->user()->can('edit-profile') ? 'profile.edit' : 'security.edit'
    ));

    Route::livewire('settings/profile', 'pages::settings.profile')
        ->middleware('can:edit-profile')
        ->name('profile.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');

    Route::livewire('settings/security', 'pages::settings.security')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('security.edit');
});
