<?php

namespace App\Services\User;

use Illuminate\Support\ServiceProvider;

class UserAuthServiceProviders extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('App\Services\User\Contracts\UserAuthInterface', 'App\Services\User\UserAuthService');
        $this->app->bind('App\Services\User\AuthService\Contracts\MobileCodeInterface', 'App\Services\User\AuthService\MobileCodeService');
        $this->app->bind('App\Services\User\AuthService\Contracts\UserOperateInterface', 'App\Services\User\AuthService\UserOperateService');
    }
}
