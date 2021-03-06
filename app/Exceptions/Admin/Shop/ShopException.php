<?php
namespace App\Exceptions\Admin\Shop;

class ShopException extends \Exception
{
    /**
     * 商城异常信息
     */
    const DEFAULT_CODE = 910000;

    /**
     * 异常文案信息.
     */
    const SHOP_EXIST        = '商城名称已存在'; // DEFAULT_CODE + 1
    const SHOP_NOT_EXIST    = '商城不存在'; // DEFAULT_CODE + 2, 3, 4
    const SHOP_PRODUCT_EXIST = '商品已存在商店中'; // DEFAULT_CODE + 5
    const SHOP_PRODUCT_NO_EXIST = '商品不存在'; // DEFAULT_CODE + 6

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