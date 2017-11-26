<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class WebAuth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'web.auth';
    }
}
