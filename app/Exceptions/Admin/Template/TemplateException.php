<?php

namespace App\Exceptions\Admin\Template;


class TemplateException extends \Exception
{
    /**
     * 购物车异常信息
     */
    const DEFAULT_CODE = 990000;

    /**
     * 异常文案信息.
     */
    const TEMPLATE_ADD_FAIL = '创建模版失败'; // DEFAULT_CODE + 1

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