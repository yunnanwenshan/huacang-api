<?php

use GuzzleHttp\Client;

class AuthTest extends TestCase
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
            "product_id" => 4,
            "name" => "华为手机",
            "class_id" => 15, //产品分类（用户没找到自己的分类，就可自己填写一个）
            "type" => 1, //产品类形， 1 实物，2 虚拟
            "code" => "1231",
            "recommend" => "好", //推荐理由
            "brands" => "华为", //产品品牌
            "valid_time" => "2017-12-05", //产品生效日期,如果状态是已上架，好这个就是上架上效时间
            "cost_price" => 43, //
            "supply_price" => 56,
            "selling_price" => 60,
            "stock_num" => 100,
            "min_sell_num" => 10,
            "detail" => "2342", //产品详情
            "sale_type" => 2, //1 立即上架 2 暂不上架
            "template_id" => 32, //模板编号（没有模板，用户要新建一个，界面要有引导功能）
            "user_id" => 32,
            "main_img" => "http://www.baidu.com", //七牛的链接
            "sub_img" =>  ["http://www.baidu.com", "http://www.baidu.com"], //七牛的链接
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

        $response = $this->client->request('POST', self::$current_server . '/admin/v1/product/update', [
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
