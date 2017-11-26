<?php

namespace App\Http\Middleware;

use Closure;

class Develop
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
            $develop = new \App\Http\Auth\Develop();
            $develop->setRequest($request);
            $develop->validate();
        } catch (\Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return $next($request);
    }
}
