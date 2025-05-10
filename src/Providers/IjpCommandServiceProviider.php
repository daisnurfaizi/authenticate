<?php

namespace Ijp\Auth;

use Illuminate\Support\ServiceProvider;

class IjpCommandServiceProviider extends ServiceProvider
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
}
