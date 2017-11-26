<?php

namespace App\Services\User\Contracts;

interface UserAuthInterface
{
    /**
     * 用户使用验证码的方式进行登录.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string       $mobile        [description]
     * @param string       $code          [description]
     *
     * @return array [description]
     */
    public function loginByMobileCode($mobile, $code, $clientInfo, $userType = 1);

    /**
     * 用户名密码登录
     *
     * @author liutianping@ttyongche.com
     *
     * @param string       $mobile        [description]
     * @param string       $code          [description]
     *
     * @return array [description]
     */
    public function loginByUserName($userName, $password, $userType = 1);

    /**
     * 发送验证码.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string       $mobile        [description]
     *
     * @return array [description]
     */
    public function sendCode($mobile);

    /**
     * 获取用户信息.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string       $userId        [description]
     *
     * @return array [description]
     */
    public function getUserInfo($userId);

    /**
     * 更新用户信息.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string      $userId          [description]
     * @param array       $userInfo        [description]
     *
     * @return array [description]
     */
    public function updateUserInfo($userId, array $userInfo);
}
