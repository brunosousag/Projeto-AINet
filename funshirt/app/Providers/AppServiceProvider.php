<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::define('admin', fn (User $user): bool => $user->isAdmin());
        Gate::define('employee', fn (User $user): bool => $user->isEmployee());
        Gate::define('customer', fn (User $user): bool => $user->isCustomer());
        Gate::define('edit-profile', fn (User $user): bool => $user->isAdmin() || $user->isCustomer());
        Gate::define('use-cart', fn (?User $user): bool => $user === null || $user->isCustomer());
        Gate::define('checkout', fn (User $user): bool => $user->isCustomer());
        Gate::define('manage-catalog', fn (User $user): bool => $user->isAdmin());
        Gate::define('manage-orders', fn (User $user): bool => $user->isAdmin() || $user->isEmployee());
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
