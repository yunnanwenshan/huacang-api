<?php

namespace App\Services\User\AuthService\Contracts;

interface MobileCodeInterface
{
    /**
     * 发送验证码.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string       $mobile        [description]
     * @param int          $type          [description]
     *
     * @return bool [description]
     */
    public function sendCode($mobile);

    /**
     * 验证登录验证码.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string       $mobile        [description]
     * @param string       $code          [description]
     *
     * @return bool [description]
     */
    public function verifyCode($mobile, $code);
}
