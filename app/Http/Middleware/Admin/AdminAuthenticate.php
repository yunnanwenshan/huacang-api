<?php
/**
 * Created by IntelliJ IDEA.
 * User: admin
 * Date: 17/11/21
 * Time: 下午3:29
 */

namespace App\Http\Middleware\Admin;

use ClientRequest;
use Closure;
use Illuminate\Http\JsonResponse;
use WebAuth;

class AdminAuthenticate
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
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /*设置client info*/
        $appName    = config('app.application_name');
        $clientInfo = ['clientType' => 'Web', 'appnm' => 'web-'.$appName];
        ClientRequest::setClientInfo(json_encode($clientInfo));
        WebAuth::setHttpRequest($request);

        $response = $next($request);

        $jsonp_callback = $request->input('callback');
        if (!empty($jsonp_callback) && $response instanceof JsonResponse) {
            $response->setCallback($jsonp_callback);
        }

        WebAuth::setCallBack($response);

        return WebAuth::setCallBack($response);
    }
}