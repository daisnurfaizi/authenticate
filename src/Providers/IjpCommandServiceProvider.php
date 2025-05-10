<?php

namespace Ijp\Auth\Providers;

use Illuminate\Support\ServiceProvider;

class IjpCommandServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            \Ijp\Auth\Console\InstallJwtAuthCommand::class,
        ]);
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../stubs/AuthController.stub' => app_path('Http/Controllers/AuthController.php'),
            __DIR__ . '/../stubs/JwtMiddleware.stub' => app_path('Http/Middleware/JwtMiddleware.php'),
            __DIR__ . '/../Helper/ResponseJsonFormater.stub' => app_path('Helper/ResponseJsonFormater.php'),
        ], 'jwt-auth-stubs');
    }
}
