<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SslCommerzService;
use App\Interfaces\Payment\SslCommerzInterface;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckPermission;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
