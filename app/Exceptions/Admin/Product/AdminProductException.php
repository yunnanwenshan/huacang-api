<?php

namespace App\Exceptions\Admin\Product;


class AdminProductException extends \Exception
{
    /**
     * 购物车异常信息
     */
    const DEFAULT_CODE = 970000;

    /**
     * 异常文案信息.
     */
    const PRODUCT_ADD_FAIL = '创建产品失败'; // DEFAULT_CODE + 1
    const PRODUCT_PARAM_ERROR = '参数错误';  // DEFAULT_CODE + 2, 3, 4
    const PRODUCT_NOT_EXIST = '产品不存在';  // DEFAULT_CODE + 5, 7, 8, 9, 10
    const PRODUCT_UPDATE_FAIL = '产品更新失败';  // DEFAULT_CODE + 6
    const PRODUCT_PRODUCT_COUNT_ZERO = '产品为0';  // DEFAULT_CODE + 11,12

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