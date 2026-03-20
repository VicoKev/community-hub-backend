<?php

namespace App\Providers;

use App\Listeners\EnvoyerEmailConfirmationInscription;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        $this->configureCommands();
        $this->configureModels();
        $this->configureDates();
        $this->configureUrls();
        $this->configurePassport();
        $this->configureEvents();
    }

    /**
     * Configure the application's commands.
     */
    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands($this->app->environment('production'));
    }

    /**
     * Configure the models.
     */
    private function configureModels(): void
    {
        Model::shouldBeStrict(! $this->app->environment('production'));
    }

    /**
     * Configure the dates.
     */
    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    /**
     * Configure the urls.
     */
    private function configureUrls(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Configure event listeners.
     */
    private function configureEvents(): void
    {
        Event::listen(Registered::class, EnvoyerEmailConfirmationInscription::class);
    }

    /**
     * Configure Passport token lifetimes.
     */
    private function configurePassport(): void
    {
        Passport::tokensExpireIn(now()->addHours(1));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}
