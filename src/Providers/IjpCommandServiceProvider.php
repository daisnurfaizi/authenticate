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
            __DIR__ . '/../stubs/AuthController.php' => app_path('Http/Controllers/AuthController.php'),
            __DIR__ . '/../stubs/JwtMiddleware.php' => app_path('Http/Middleware/JwtMiddleware.php'),
            __DIR__ . '/../Helper/ResponseJsonFormater.php' => app_path('Helper/ResponseJsonFormater.php'),
        ], 'jwt-auth-stubs');
    }
}
