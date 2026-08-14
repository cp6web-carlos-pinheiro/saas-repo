<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\MicrosoftExtendSocialite;

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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Layout System (Fundação): toda paginação da aplicação passa a usar o componente
        // compartilhado em resources/views/vendor/pagination/ui.blade.php, sem exigir troca
        // de ->links() em cada view.
        Paginator::defaultView('pagination::ui');
        Paginator::defaultSimpleView('pagination::ui');

        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('microsoft', MicrosoftExtendSocialite::class);
        });
    }
}