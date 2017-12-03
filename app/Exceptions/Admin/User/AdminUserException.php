<?php

namespace App\Exceptions\Admin\User;


class AdminUserException extends \Exception
{
    /**
     * 购物车异常信息
     */
    const DEFAULT_CODE = 980000;

    /**
     * 异常文案信息.
     */
    const USER_NO_MARKET = '暂无商家店铺信息'; // DEFAULT_CODE + 1
    const USER_MOBILE_USED = '手机号已被使用'; // DEFAULT_CODE + 2
    const USER_USERNAME_USED = '用户名已被使用'; // DEFAULT_CODE + 3
    const USER_MARKET_NAME_USED = '商家们已被使用'; // DEFAULT_CODE + 4

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