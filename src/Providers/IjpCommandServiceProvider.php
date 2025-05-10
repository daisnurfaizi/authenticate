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

        $this->publishes([
            __DIR__ . '/../migrations/app_role_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_app_role_table.php'),
            // beri jeda 1 detik agar tidak bentrok dengan migration yang lain
            __DIR__ . '/../migrations/add_role_id_to_user.php' => database_path('migrations/' . date('Y_m_d_His', time() + 1) . '_add_role_id_to_user.php'),
            __DIR__ . '/../migrations/app_permissions_table.php' => database_path('migrations/' . date('Y_m_d_His', time() + 2) . '_create_app_permissions_table.php'),
            __DIR__ . '/../migrations/app_role_has_permissions_table.php' => database_path('migrations/' . date('Y_m_d_His', time() + 3) . '_create_app_role_has_permissions_table.php'),

        ], 'jwt-auth-migrations');
    }
}
