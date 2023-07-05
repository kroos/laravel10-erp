<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // using this to override Illuminate\Auth\EloquentUserProvider
        \Illuminate\Support\Facades\Auth::provider('loginuserprovider', function($app, array $config) {
            return new Auth\EloquentUserProvider($app['hash'], $config['model']);
        });
    }
}
