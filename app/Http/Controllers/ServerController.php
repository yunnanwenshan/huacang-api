<?php

namespace App\Http\Controllers;

use App;
use Cache;
use ClientRequest;
use Illuminate\Http\Request;
use Log;

class ServerController extends Controller
{
    const ERROR_CODE = 12000;

    /**
     * 清掉cookie.
     *
     * @return Illuminate\Http\Response;
     */
    public function clearcookie(Request $request)
    {
        $url = $request->url();
        $url_components = parse_url($url);

        $domain = '.number-7.cn';
        if (ends_with($url_components['host'], '.number-7.cn')) {
            $domain = '.number-7.cn';
        }

        $response = response('Clear ('.$domain.') Cookie Success:<br><pre>'.implode("\n", array_keys($_COOKIE)).'</pre>', 200);

        foreach ($_COOKIE as $key => $value) {
            $response->withCookie(cookie()->forget($key, '/', $domain));
        }

        return $response;
    }

    /**
     * 反向代理服务器的心跳检测.
     *
     * @return Illuminate\Http\Response;
     */
    public function heartbeat()
    {
        return response('', 204);
    }

    /**
     * 欢迎界面、首页.
     *
     * @return Illuminate\Http\Response;
     */
    public function welcome(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(['code' => 0, 'msg' => 'Welcome']);
        }

        return view('welcome', [
            'url' => action('ServerController@welcome'),
            'ip' => $request->getClientIp(),
            'ips' => json_encode($request->getClientIps()),
            'port' => $request->getPort(),
            'host' => $request->getHttpHost(),
            'header' => json_encode($request->header()),
            'client_proto' => json_encode(Request::getTrustedHeaderName('client_proto')),
            'client_port' => json_encode(Request::getTrustedHeaderName('client_port')),
            'client_host' => json_encode(Request::getTrustedHeaderName('client_host')),
            'client_ip' => json_encode(Request::getTrustedHeaderName('client_ip')),
            'forwarded' => json_encode(Request::getTrustedHeaderName('forwarded')),
            'proxies' => json_encode(Request::getTrustedProxies('')),
        ]);
    }
}
