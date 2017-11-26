<?php

namespace App\Http\Middleware\Client;

use Closure;
use ClientAuth;
use Exception;

class Authenticate
{
    /**
     * Create a new middleware instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            ClientAuth::setRequest($request);
            ClientAuth::valClientSig();
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return $next($request);
    }
}
