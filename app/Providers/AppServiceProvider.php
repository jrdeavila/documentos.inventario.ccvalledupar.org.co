<?php

namespace App\Providers;

use App\Models\MregEstInscrito;
use App\Services\MregEstInscritosQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

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
        Auth::provider('plaintext', function ($app, array $config) {
            return new PlainTextUserProvider();
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
