<?php

namespace App\Http\Response;

use Illuminate\Http\JsonResponse;

class ClientResponse extends JsonResponse
{
    /*系统统一的错误返回*/
    const SYS_BASE_ERROR    = 10000;        /*系统基础的信息*/

    const SYS_CLIENT_INFO   = 10001;        /*解析clientInfo失败*/

    const SYS_CLIENT_AUTH   = 10002;        /*解析clientAuth失败或验证无效*/

    const SYS_NEED_LOGIN    = 10003;        /*需要登录*/

    const SYS_ALREADY_LOGIN = 10004;        /*已经登录*/

    const SYS_INVALID_GPS   = 10005;        /*没有当前GPS信息*/

    const SYS_BANNED        = 10006;        /*请求被屏蔽*/

    const SYS_CLIENT_STOKEN = 10008;        /*解析Server-SToken失败或验证无效*/

    const SYS_CLIENT_STOKEN_EMPTY   = 10009;  /*Server-SToken不存在*/

    /**
     * Client Response.
     *
     * @param null $data
     * @param int $status
     * @param array $headers
     * @param int $options
     */
    public function __construct($data = null, $status = 200, $headers = [], $options = 0)
    {
        if (config('app.debug') == true) {
            $options |= JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        }

        $headers['Api'] = app('request')->path();

        parent::__construct($data, $status, $headers, $options);
    }

    /**
     * success response.
     *
     * @param array $data
     * @param string $msg
     * @param array $header
     * @return ClientResponse
     */
    public static function success($data = [], $msg = 'success', $header = [])
    {
        $data['code'] = 0;
        $data['msg']  = $msg;

        return new self($data, 200, $header);
    }

    /**
     * error response.
     *
     * @param $code
     * @param $msg
     * @return ClientResponse
     */
    public static function fail($code, $msg)
    {
        $data['code'] = $code;
        $data['msg']  = $msg;

        return new self($data, 200);
    }
}
