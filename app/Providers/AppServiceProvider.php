<?php

namespace App\Providers;

//use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

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
      Response::macro('unicodeJson', function ($data, $status = 200) {
        return response()->json(
            $data,
            $status,
            [],
            JSON_UNESCAPED_UNICODE
        );
    });
}

}
