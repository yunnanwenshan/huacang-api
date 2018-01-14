<?php

use GuzzleHttp\Client;

class AddTemplateTest extends TestCase
{
    private $client;
    private $jar;

    const LOCAL_SERVER = 'http://127.0.0.1:8888';
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
            'user_name' => 'ltptest123456',
            'password' => 123456,
        ];
        $request_header = [
            'Content-Type' => 'application/json; charset=UTF-8',
        ];

        $response = $this->client->request('POST', static::$current_server.'/admin/v1/user/login', [
            'headers'           => $request_header,
            'allow_redirects'   => false,
            'json'              => $request_body,
            'cookies'           => $this->jar,
        ]);

        if ($response->getStatusCode() != 200) {
            throw new Exception('request error');
        }

        print_r($response->getBody()->getContents());

        $request_body = [
            'share_id' => 12,
            'user_product_id' => 4,
            'cost_price' => 1000,
            'supply_price' => 5000,
            'selling_price' => 6000,
        ];

        $result = json_decode((string)$response->getBody(), true);
        print_r($result);
        if (json_last_error()) {
            //print_r($result, $response);
        }

        $request_header = [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Server-Token' => $result['ticket'],
        ];

        $response = $this->client->request('POST', self::$current_server . '/admin/v1/market/price/update', [
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
