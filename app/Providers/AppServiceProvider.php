<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        // 1. إعداد رابط إعادة تعيين كلمة السر (الذي كان موجوداً عندك)
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // 2. إعداد قواعد التحقق من قوة كلمة السر (التي اتفقنا عليها)
        Password::defaults(function () {
            return Password::min(8)
                          ->letters()
                          ->numbers();
        });
    }
}
