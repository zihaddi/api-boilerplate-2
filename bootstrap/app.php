<?php

use App\Http\Middleware\HandleHttpRequest;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::prefix('api/admin')
                ->middleware('api')
                ->group(base_path('routes/api/admin.php'));
            Route::prefix('api/cms')
                ->middleware('api')
                ->group(base_path('routes/api/cms.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'check.permission' => App\Http\Middleware\CheckPermission::class,

        ]);
        $middleware->append(HandleHttpRequest::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
