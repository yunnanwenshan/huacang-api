<?php

namespace App\Exceptions\Admin\AdminOrderException;


class AdminOrderException extends \Exception
{
    /**
     * 购物车异常信息
     */
    const DEFAULT_CODE = 980000;

    /**
     * 异常文案信息.
     */
    const ORDER_NOT_EXIST = '订单不存在'; // DEFAULT_CODE + 1
    const ORDER_NO_CANCEL = '订单已发货，订单不能直接取消'; // DEFAULT_CODE + 2
    const ORDER_CANCELED = '订单已被取消'; // DEFAULT_CODE + 3
    const ORDER_FINISHED = '订单已完成'; // DEFAULT_CODE + 4
    const ORDER_NO_FINISHED = '订单当前状态不允许操作，订单需要支付完成才可以结束。订单当前订单状态: '; // DEFAULT_CODE + 5
    const ORDER_ORTHER_OP = '订单其它操作暂不支持'; // DEFAULT_CODE + 6
    const ORDER_USER_REQUEST = '用户已申请取消订单，只能取消订单'; // DEFAULT_CODE + 7

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