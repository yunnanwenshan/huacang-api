<?php

namespace App\Http\Middleware\Admin;

use App\Http\Auth\Web;
use App\Http\Response\ClientResponse;
use ClientRequest;
use Closure;
use Exception;
use Log;
use WebAuth;

class AdminAccessLoginCheck
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
        $domain = config('session.domain');

        try {
            /*request 验证*/
            WebAuth::setHttpRequest($request);
            WebAuth::valServerToken();
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

            //互斥登陆
        } catch (Exception $e) {
            Log::warning(__FILE__.'('.__LINE__.'):'.__CLASS__.'->'.__FUNCTION__.'() '.'err: '.$e->getMessage());

            return response()->clientFail(
                ClientResponse::SYS_CLIENT_STOKEN,
                '您的账号信息已失效，请重新登录'
            )
                ->withCookie(cookie()->forget(Web::SERVER_TOKEN, '/', $domain))
                ->withCookie(cookie()->forget('uid', '/', $domain));
        }

        return $next($request);
    }
}