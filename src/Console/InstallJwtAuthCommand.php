<?php

namespace Ijp\Auth\Console;

use Illuminate\Console\Command;

class InstallJwtAuthCommand extends Command
{
    protected $signature = 'ijp-auth:install';
    protected $description = 'Install the JWT Auth bu publishing controller and middleware';

    public function handle()
    {
        $this->info('Publishing JWT Auth Controller and Middleware...');
        $this->info('Publishing JWT Auth stubs...');
        $this->call('vendor:publish', [
            '--tag' => 'jwt-auth-stubs',
            '--tag' => 'jwt-auth-migrations',
        ]);
        $this->info('JWT Auth installed successfully!');
    }
}
