<?php

namespace App\Providers;

use App\Models\MregEstInscrito;
use App\Services\MregEstInscritosQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Passport\Bridge\UserRepository as PassportUserRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend(PassportUserRepository::class, function ($service, $app) {
            // OJO al orden de argumentos: primero hasher, luego user provider
            return new \App\Passport\PlainUserRepository(
                $app['hash'],
                $app['auth']->createUserProvider(config('auth.guards.api.provider'))
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::enablePasswordGrant();

        Auth::provider('plaintext', function ($app, array $config) {
            return new PlainTextUserProvider();
        });

        Auth::provider('plain_api', function ($app, array $config) {
            return new PlainApiUserProvider();
        });

        Collection::macro('withEstablishments', function () {
            /** @var \Illuminate\Support\Collection $this */
            return MregEstInscritosQuery::attachEstablishments($this);
        });

        MregEstInscrito::macro('withEstablishments', function () {
            /** @var \Illuminate\Support\Collection $this */
            return MregEstInscritosQuery::attachEstablishments($this);
        });
    }
}
