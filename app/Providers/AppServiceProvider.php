<?php

namespace App\Providers;

use App\Models\User;
use App\Payments\FakeGateway;
use App\Payments\MercadoPago\MercadoPagoGateway;
use App\Payments\MercadoPago\WebhookSignature;
use App\Payments\PaymentGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Resolved per use, so tests can switch gateways through config.
        $this->app->bind(PaymentGateway::class, fn (): PaymentGateway => match (Config::string('payments.gateway')) {
            'mercadopago' => new MercadoPagoGateway(
                Config::string('services.mercadopago.access_token'),
                Config::string('services.mercadopago.base_url'),
            ),
            'fake' => new FakeGateway,
            default => throw new InvalidArgumentException('Unknown PAYMENT_GATEWAY "'.Config::string('payments.gateway').'".'),
        });

        $this->app->bind(WebhookSignature::class, fn () => new WebhookSignature((string) config('services.mercadopago.webhook_secret')));
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
