<?php

namespace App\Http\Middleware\Client;

use App\Http\Response\ClientResponse;
use ClientAuth;
use ClientRequest;
use Closure;
use Exception;
use Log;

class AccessLoginCheck
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
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            ClientAuth::setRequest($request);
            ClientAuth::validate();

            $user = ClientRequest::getUser();
            if (empty($user) || is_null($user)) {
                Log::warning('Client Validate Fail', [
                    'code' => ClientResponse::SYS_NEED_LOGIN,
                    'msg' => 'need login',
                    'file' => __FILE__,
                    'class' => __CLASS__,
                    'user' => $user,
                ]);
                throw new \Exception('the verify result failed', ClientResponse::SYS_CLIENT_STOKEN);
            }

        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return $next($request);
    }
}
