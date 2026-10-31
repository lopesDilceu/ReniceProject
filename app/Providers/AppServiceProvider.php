<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
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
        DB::prohibitDestructiveCommands(app()->isProduction());

        // Any N+1 (lazy loading inside a loop) throws outside production, so
        // tests catch it instead of the database (MNT-01, #26).
        Model::preventLazyLoading(! app()->isProduction());

        // The admin area: every /admin route requires this ability.
        Gate::define('admin', fn (User $user): bool => $user->is_admin);

        // Strict in production (including a check against known breaches);
        // Laravel's minimum of 8 characters elsewhere, so demo data and tests
        // can use simple passwords.
        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)->mixedCase()->letters()->numbers()->symbols()->uncompromised()
            : null,
        );
    }
}
