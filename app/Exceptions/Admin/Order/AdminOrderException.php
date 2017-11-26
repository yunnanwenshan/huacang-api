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
    const ORDER_NO_CANCEL = '订单已发货，订单不能直接取消，请联系发货方'; // DEFAULT_CODE + 2

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