<?php

namespace App\Providers;

use App\Listeners\ActivatePsychologistAfterEmailVerification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(Verified::class, ActivatePsychologistAfterEmailVerification::class);

        Event::listen(SocialiteWasCalled::class, \SocialiteProviders\VKontakte\VKontakteExtendSocialite::class);
        Event::listen(SocialiteWasCalled::class, \SocialiteProviders\Yandex\YandexExtendSocialite::class);
    }
}
