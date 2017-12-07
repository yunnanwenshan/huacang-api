<?php

namespace App\Exceptions\Product;


class ProductException extends \Exception
{
    /**
     * 购物车异常信息
     */
    const DEFAULT_CODE = 10000;

    /**
     * 异常文案信息.
     */
    const PRODUCT_NOT_EXIST = '商品不存在'; // DEFAULT_CODE + 1, 4, 9
    const PRODUCT_IDS_IS_NULL = '提交参数非法'; // DEFAULT_CODE + 2, 3
    const PRODUCT_MARKET_NAME_IS_NULL = '商城名称不能为空'; // DEFAULT_CODE + 5
    const PRODUCT_MARKET_CREATE_FAIL = '商城失败'; // DEFAULT_CODE + 6
    const PRODUCT_MARKET_NAME_EXISTED = '商城已存在'; // DEFAULT_CODE + 7, 8
    const PRODUCT_NO_ONLINE = '商品已下线'; // DEFAULT_CODE + 10

    public function __construct($message = '', $code = 0, $previous = null)
    {
        /*
         * 该code会在返回的json中直接使用，所以请勿使用0
         */
        if ($code == 0) {
            $code = self::DEFAULT_CODE;
        }
        parent::__construct($message, $code, $previous);
    }
}