<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Auth\Client as ClientAuth;
use App\Http\Auth\Web  as WebAuth;
use App\Http\Requests\ClientRequest;
use App\Http\Response\ClientResponse;
use DB;
use Illuminate\Contracts\Routing\ResponseFactory;
use Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(ResponseFactory $factory)
    {
        if (config('app.debug')) {
            DB::listen(function ($sql, $bindings, $time) {
                Log::info('DB query', [
                    'sql' => $sql,
                    'bindings' => $bindings,
                    'time' => $time,
                ]);
            });
        }

        $this->extendResponse($factory);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Contracts\ClientRequestAuth',     'App\Http\Auth\Client');
        $this->app->bind('App\Contracts\ServiceRequestAuth',    'App\Http\Auth\Service');
        $this->app->bind('App\Contracts\ClientRequest',         'App\Http\Requests\ClientRequest');

        /*Client Auth Singleton*/
        $this->app->singleton('client.auth', function ($app) {
            return new ClientAuth();
        });

        /*Web Auth Singleton*/
        $this->app->singleton('web.auth', function ($app) {
            return new WebAuth();
        });

        $this->app->singleton('client.request', function ($app) {
            return new ClientRequest();
        });
    }

    /**
     * 扩展Response.
     *
     * @param ResponseFactory $factory
     */
    private function extendResponse(ResponseFactory $factory)
    {
        $factory->macro('clientSuccess', function ($data = [], $msg = 'success', $header = []) {
            return ClientResponse::success($data, $msg, $header);
        });
        $factory->macro('clientFail', function ($code, $msg) {
            return ClientResponse::fail($code, $msg);
        });
    }
}
