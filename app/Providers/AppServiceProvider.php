<?php

namespace App\Providers;

use App\Services\AuditLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            AuditLogger::log(
                action: 'AUTH_LOGIN',
                auditable: $event->user,
                newValues: ['email' => $event->user->email, 'role' => $event->user->role],
                userId: $event->user->getAuthIdentifier()
            );
        });

        if (str_starts_with((string) config('app.url'), 'https://')) {
        URL::forceScheme('https');
    }
    }
}