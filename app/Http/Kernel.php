<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\LogDebugger::class,
        \Fideloper\Proxy\TrustProxies::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \App\Http\Middleware\Cors::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'cauth'          => \App\Http\Middleware\Client\Authenticate::class,      /* cauth   = "client auth" */
        'cauth.kol'      => \App\Http\Middleware\Client\AccessLoginCheck::class,  /* kol = "keep online" */
        'wauth'          => \App\Http\Middleware\Web\Authenticate::class,         /* wauth  = "web auth" */
        'wauth.kol'      => \App\Http\Middleware\Web\AccessLoginCheck::class,     /* kol = "keep online" */
        'cauth.develop'  => \App\Http\Middleware\Develop::class,
        'admin_wauth'    => \App\Http\Middleware\Admin\AdminAuthenticate::class,     /* wauth  = "admin web auth" */
        'admin_wauth.kol'=> \App\Http\Middleware\Admin\AdminAccessLoginCheck::class, /* kol = "keep online" */
    ];
}
