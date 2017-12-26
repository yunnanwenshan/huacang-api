<?php

use GuzzleHttp\Client;

class AuthTest extends TestCase
{
    private $client;
    private $jar;

    const LOCAL_SERVER = 'http://localhost:8888';
    const TEST_SERVER = 'http://test-api.huacang.com:8889';
    const SERVER = 'http://api.huacang.com:8888';
    static $current_server = self::LOCAL_SERVER;

    public function __construct()
    {
        parent::__construct();
        $this->jar = new \GuzzleHttp\Cookie\CookieJar();
        $this->client =new Client(['cookies' => true]);
    }


    public function testUserLogin()
    {
        $request_body = [
            'mobile' => '10000000000',
            'code' => 1234,
        ];
        $request_header = [
            'Content-Type' => 'application/json; charset=UTF-8',
        ];

        $response = $this->client->request('POST', static::$current_server.'/web/v1/user/login', [
            'headers'           => $request_header,
            'allow_redirects'   => false,
            'json'              => $request_body,
            'cookies'           => $this->jar,
        ]);

        if ($response->getStatusCode() != 200) {
            throw new Exception('request error');
        }

        print_r($response->getBody()->getContents());
        print_r($response->getHeaders());

        $request_body = '';
        $result = json_decode((string)$response->getBody(), true);
        print_r($result);
        if (json_last_error()) {
            //print_r($result, $response);
        }

        $request_header = [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Server-Token' => $result['ticket'],
        ];

        $response = $this->client->request('POST', self::$current_server . '/web/v1/user/detail', [
            'headers'           => $request_header,
            'allow_redirects'   => false,
            'json'              => $request_body,
            'cookies'           => $this->jar,
        ]);

        $request_body = [
            'avatar' => 'https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1514310797059&di=8683cf2326854378f79fc0188032cfd6&imgtype=0&src=http%3A%2F%2Fimgsrc.baidu.com%2Fimgad%2Fpic%2Fitem%2F728da9773912b31bc2fe74138d18367adab4e17e.jpg',
            'sex' => 1,
        ];

        $response = $this->client->request('POST', self::$current_server . '/web/v1/user/updateinfo', [
            'headers'           => $request_header,
            'allow_redirects'   => false,
            'json'              => $request_body,
            'cookies'           => $this->jar,
        ]);

        if ($response->getStatusCode() != 200) {
            throw new Exception('request error');
        }

//        print_r($response->getHeaders());
        print_r($response->getBody()->getContents());
    }
}
