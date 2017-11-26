<?php

namespace App\Http\Auth;

use ClientRequest;
use Cookie;
use Exception;
use Illuminate\Http\Request as HttpRequest;
use Log;

class Web extends Base
{
    /**
     * the key of server token in header.
     */
    const SERVER_TOKEN = 'Server-Token';

    const CLIENT_GUID = 'guid'; //客户端唯一key，单点登录判断

    /**
     * 设置回调参数.
     *
     * @param  HttpRequest $request
     * @param              $response
     * @return mixed
     */
    public function setCallBack($response)
    {
        $jsonpCallback = $this->request->input('callback');
        if ($response instanceof Illuminate\Http\JsonResponse && !empty($jsonpCallback)) {
            $response->setCallback($jsonpCallback);
        }

        return $response;
    }

    /**
     * 验证server token.
     *
     * @throws Exception
     */
    public function valServerToken()
    {
        /*设置server token*/
        $serverToken = Cookie::get(self::SERVER_TOKEN);
        if (empty($serverToken)) {
            $serverToken = $this->request->header(self::SERVER_TOKEN);
        }

        Log::info(__FILE__ . '(' . __LINE__ . '), val server token, ', [
            'server_token' => $serverToken,
        ]);

        /*设置client info*/
        $appName    = config('app.application_name');
        $clientInfo = ['clientType' => 'Web', 'appnm' => 'web-'.$appName];

        /*验证server token*/
        if (stripos($this->request->header('User-Agent'), 'MicroMessenger') !== false) {
            $this->setClientInfo(array_merge($clientInfo, ['model' => 'weixin']));
            ClientRequest::setClientInfo(json_encode(array_merge($clientInfo, ['model' => 'weixin'])));
        } else {
            $this->setClientInfo($clientInfo);
            ClientRequest::setClientInfo(json_encode(array_merge($clientInfo, ['model' => 'weixin'])));
        }

        $this->validateServerToken($serverToken);
    }

    public function setHttpRequest(HttpRequest $request)
    {
        $this->request    = $request;
        $this->httpHeader = $request->header();
        ClientRequest::setRequest($request);
    }
}
