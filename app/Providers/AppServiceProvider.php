<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\AdminSettingsPolicy;
use App\Policies\AssetPolicy;
use App\Policies\SlaPolicyPolicy;
use App\Policies\TicketPolicy;
use App\Settings\GeneralSettings;
use App\Settings\PasswordSettings;
use App\Settings\StyleSettings;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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

        Gate::before(static fn (User $user): ?bool => $user->hasRole((string) config('superadmin.role')) ? true : null);

        Gate::policy(GeneralSettings::class, AdminSettingsPolicy::class);
        Gate::policy(StyleSettings::class, AdminSettingsPolicy::class);
        Gate::policy(PasswordSettings::class, AdminSettingsPolicy::class);
        Gate::policy(Asset::class, AssetPolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(SlaPolicy::class, SlaPolicyPolicy::class);

        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User) {
                $event->user
                    ->forceFill(['last_login_at' => Date::now()])
                    ->saveQuietly();
            }
        });

        RateLimiter::for('temporary-uploads', static function (Request $request): array {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return [
                Limit::perMinute(20)->by('temporary-uploads:minute:'.$key),
                Limit::perHour(100)->by('temporary-uploads:hour:'.$key),
            ];
        });
    }
}
