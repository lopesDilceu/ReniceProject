<?php

namespace App\Providers;

use App\Models\User;
use App\Payments\FakeGateway;
use App\Payments\MercadoPago\MercadoPagoGateway;
use App\Payments\MercadoPago\WebhookSignature;
use App\Payments\PaymentGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use InvalidArgumentException;
use Laravel\Sanctum\PersonalAccessToken;

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

        // API: 60 requests per minute per token, so one integration cannot
        // starve the others; the public catalog is counted per IP.
        RateLimiter::for('api', function (Request $request): Limit {
            $token = $request->user()?->currentAccessToken();

            return Limit::perMinute(60)->by($token instanceof PersonalAccessToken ? 'token:'.$token->getKey() : 'ip:'.$request->ip());
        });

        // Issuing tokens checks a password: 5 attempts per minute per e-mail
        // and IP, like the login form.
        RateLimiter::for('api-tokens', fn (Request $request): Limit => Limit::perMinute(5)
            ->by(Str::lower((string) $request->input('email')).'|'.$request->ip()));

        // The admin area: every /admin route requires this ability.
        Gate::define('admin', fn (User $user): bool => $user->is_admin);

        // The Pulse dashboard (/pulse): metrics, slow queries and exceptions.
        Gate::define('viewPulse', fn (User $user): bool => $user->is_admin);

        // Laravel's /up answers 200 as long as PHP runs; make it fail when
        // the database is down too. /health has the detailed report.
        Event::listen(DiagnosingHealth::class, fn () => DB::select('select 1'));

        // Strict in production (including a check against known breaches);
        // Laravel's minimum of 8 characters elsewhere, so demo data and tests
        // can use simple passwords.
        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)->mixedCase()->letters()->numbers()->symbols()->uncompromised()
            : null,
        );
    }
}
