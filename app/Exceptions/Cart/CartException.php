<?php
namespace App\Exceptions\Cart;


class CartException extends \Exception
{
    /**
     * 购物车异常信息
     */
    const DEFAULT_CODE = 10000;

    /**
     * 异常文案信息.
     */
    const CART_ADD_PRODUCT_FAIL = '商品加入失败'; // DEFAULT_CODE + 1
    const CART_REMOVE_PARAMS_INVALID = '参数非法'; // DEFAULT_CODE + 2, 3, 5
    const CART_REMOVE_FAIL = '删除商品失败'; // DEFAULT_CODE + 3

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