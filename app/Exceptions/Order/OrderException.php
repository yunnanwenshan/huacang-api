<?php

namespace App\Exceptions\Order;


class OrderException extends \Exception
{
    /**
     * 用户无法正常登录时，返回的异常状态开始值
     */
    const DEFAULT_CODE = 30000;

    /**
     * 异常文案信息.
     */
    const ORDER_PARAM_FAIL = '非法参数';     // DEFAULT_CODE + 1
    const ORDER_CREATE_FAIL = '创建订单失败'; // DEFAULT_CODE + 2, 5
    const ORDER_NOT_EXIST = '订单不存在';     // DEFAULT_CODE + 3, 4
    const ORDER_NULL = '请选择商品';          // DEFAULT_CODE + 6
    const ORDER_PRODUCT_OFFLINE = '有部分商品已经下线'; // DEFAULT_CODE + 7
    const ORDER_PRODUCT_STOCK_INSUFFICIENT = '商品库存不足'; // DEFAULT_CODE + 8
    const ORDER_PRODUCT_STOCK_FAIL = '库存不足'; // DEFAULT_CODE + 9
    const ORDER_CANCEL = '订单已被取消'; // DEFAULT_CODE + 10
    const ORDER_FINISHED = '订单已完成'; // DEFAULT_CODE + 11
    const ORDER_NOT_ALLOWED_CANCEL = '订单状态不允许操作， 订单已完成'; // DEFAULT_CODE + 12
    const ORDER_MARKET_NOT_EXIST = '商店不存在'; // DEFAULT_CODE + 13, 16
    const ORDER_MARKET_NOT_GOOD = '有部分商品不在当前商店里'; // DEFAULT_CODE + 14
    const ORDER_TOTAL_FEE_NO_EQUAL = '总费用有误'; // DEFAULT_CODE + 15

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