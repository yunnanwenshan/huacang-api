<?php

namespace App\Components;

use App\Components\Config\EnvConfig;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Log;
use Psr\Http\Message\ResponseInterface;
use Qconf;

/**
 * 请求其他服务的通用Request.
 *
 * Class ServiceRequest
 */
class ServiceRequest
{
    //$default_code + 5
    private $default_code = 11000;

    private $service;

    private $uri;

    /**
     * 初始化.
     *
     * @param $service
     */
    public function __construct($service)
    {
        $this->service = $service;
        $this->uri     = '';
    }

    /**
     * 触发请求
     *
     * @param $uri
     * @param $data
     * @throws Exception
     * @return mixed
     */
    public function request($uri, $data, $timeout = 5)
    {
        $profiles = [];
        $profiles['time']['start'] = microtime(true);
        $profiles['memory']['start'] = memory_get_usage();

        $client = new Client();
        $body   = [
          'params'  => $data,
        ];
        $requestBodyJson = json_encode($body);
        $requestHeader   = [
            'Content-Type'  => 'application/json; charset=UTF-8',
            'Service-Token' => $this->generateToken(),
        ];
        $options = [
            'headers'           => $requestHeader,
            'allow_redirects'   => false,
            'body'              => $requestBodyJson,
            'timeout'           => $timeout,
        ];

        $this->uri = $uri;

        Log::info('[ServiceRequest] before Request options', [
            'uri'       => $this->uri,
            'service'   => $this->service,
            'options'   => $options,
            'params'    => $body,
        ]);

        $retryTimes = 3;
        $retry = 0;
        //增加重试机制
        RETRY:
        try {
            $retry ++;
            $response = $client->request('POST', $uri, $options);
            Log::info('[ServiceRequest] after Request options', [
                'uri' => $this->uri,
                'service' => $this->service,
                'options' => $options,
                'params' => $body,
            ]);
        } catch (Exception $e) {
            Log::error(__FILE__ . '(' . __LINE__ . '), java api error', [
                'uri' => $uri,
                'data' => $data,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            if ($retry < $retryTimes) {
                usleep(300);
                goto RETRY;
            }
            throw new \RuntimeException('网络超时', $this->default_code + 5);
        }

        try {
            $array = $this->checkResponse($response);
        } catch (Exception $e) {
            $code = $e->getCode();
            $message = $e->getMessage();
            Log::warning(__FILE__ . '(' . __LINE__ . '), request interface error,', [
                    'uri' => $uri,
                    'data' => $data,
                    'code' => $code,
                    'message' => $message,
                ]);
//            throw new Exception($e->getMessage(), $e->getCode());
            throw new Exception(EnvConfig::env("middleware_SYS_MSG_SYSTEM_ERROR"), $code);
        }
        Log::info('[ServiceRequest] Response Result', [
            'uri'        => $this->uri,
            'service'    => $this->service,
            'response'   => $array,
            'params'    => $body,
        ]);

        $profiles['time']['end'] = microtime(true);
        $profiles['time']['cost'] = $profiles['time']['end'] - $profiles['time']['start'];
        $profiles['memory']['end'] = memory_get_usage();
        $profiles['memory']['max'] = DebugUtil::convert(memory_get_peak_usage());
        if (intval($profiles['time']['cost']) >= 1) {
            \Log::warning(
                \Carbon\Carbon::now()->format('Y-m-d H:i:s') . ' ' . $uri . ", time cost: {$profiles['time']['cost']} s" . PHP_EOL
            );
        } else {
            \Log::info(
                __FILE__ . '(' . __LINE__ . ') ' . PHP_EOL
                . ",ServiceRequest time cost: {$profiles['time']['cost']} s" . PHP_EOL
                . ", ServiceRequest memory max: {$profiles['memory']['max']}" . PHP_EOL
                . ", uri:" . $uri
                . ", params:" . json_encode($data) . PHP_EOL
            );
        }

        return $array['result'];
    }

    /**
     * 检查请求返回信息.
     *
     * @param $array
     * @throws Exception
     */
    private function checkResponse(ResponseInterface $response)
    {
        if ($response->getStatusCode() != 200) {
            $this->triggerError([
                'code'          => $response->getStatusCode(),
                'message'       => 'Http Request Error',
                'headers'       => $response->getHeaders(),
            ]);
            throw new Exception('Request error', $this->default_code + 5);
        }
        $content = $response->getBody()->getContents();
        Log::info('[ServiceRequest] Response contents', [
            'uri'       => $this->uri,
            'service'   => $this->service,
            'contents'  => $content,
        ]);
        $array = json_decode($content, true);
        if (json_last_error()) {
            $this->triggerError([
                'code'      => json_last_error(),
                'message'   => 'Json Decode Error',
            ]);
            throw new Exception('Json Decode error', $this->default_code + 1);
        }
        if (isset($array['error'])) {
            $this->triggerError($array['error']);
            if (isset($array['error']['message'])) {
                if (isset($array['error']['code'])) {
                    throw new Exception($array['error']['message'], $array['error']['code']);
                } else {
                    throw new Exception($array['error']['message'], $this->default_code + 4);
                }
            } else {
                throw new Exception('Service Result error', $this->default_code + 2);
            }
        }

        if (!isset($array['result'])) {
            $this->triggerError([
                'code'      => 2001,
                'message'   => 'Success Response Not Contain "result"',
            ]);
            throw new Exception('Service Success Parse Error', $this->default_code + 3);
        }

        return $array;
    }

    /**
     * 生成Token信息.
     *
     * @return string
     */
    public function generateToken()
    {
        $key     = 'keys.signature_secret_for_service.'.$this->service;
        $secret  = config($key);
        $nonce   = rand();
        $payload = $this->service.'.'.$nonce;
        $sign    = sha1($payload.$secret);
        $token   = $payload.'.'.$sign;

        return $token;
    }

    /**
     * 触发错误的信息.
     *
     * @param $error
     */
    private function triggerError($error)
    {
        Log::info('[ServiceRequest] Request error', [
            'uri'       => $this->uri,
            'service'   => $this->service,
            'error'     => $error,
        ]);
    }

    /***
     * @param $host 主机配置
     * @param $path uri
     * @return string
     * 获取接口完整的url地址
     */
    public function getUri($host, $path)
    {
        if (in_array(env('APP_ENV'), ['testing'])) {
            $uri = Qconf::getHost($host).$path;
        } else if (in_array(env('APP_ENV'), ['development'])) {
            $uri = $host.$path;
        } else {
            $uri = Qconf::getHost($host).$path;
        }

        return $uri;
    }
}
