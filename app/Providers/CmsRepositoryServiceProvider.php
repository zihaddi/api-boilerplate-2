<?php

namespace App\Providers;

use App\Interfaces\Cms\ContactRepositoryInterface;
use App\Repositories\Cms\ContactRepository;
use Illuminate\Support\ServiceProvider;

use App\Repositories\Cms\AuthClientRepository;
use App\Interfaces\Cms\AuthClientRepositoryInterface;


class CmsRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AuthClientRepositoryInterface::class, AuthClientRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
