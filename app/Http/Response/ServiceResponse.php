<?php

namespace App\Http\Response;

use Illuminate\Http\JsonResponse;

class ServiceResponse extends JsonResponse
{
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
    public static function success($data = [], $header = [])
    {
        $data['result'] = $data;
        return new self($data, 200, $header);
    }

    /**
     * error response.
     *
     * @param $code
     * @param $msg
     * @return ClientResponse
     */
    public static function fail($code, $msg, $errorTrace)
    {
        $data['error'] = [
            'code'      => $code,
            'message'   => $msg,
            'data'      => $errorTrace,
        ];

        return new self($data, 200);
    }
}
