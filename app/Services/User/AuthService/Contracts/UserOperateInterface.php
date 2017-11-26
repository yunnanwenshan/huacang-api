<?php

namespace App\Services\User\AuthService\Contracts;

interface UserOperateInterface
{
    /**
     * 验证用户信息.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string       $mobile        [description]
     * @param string       $code          [description]
     * @param array        $clientInfo    [description]
     *
     * @return array [description]
     */
    public function login($mobile, $code, array $clientInfo, $userType = 1);

    /**
     * 用户名与密码
     *
     * @author liutianping@ttyongche.com
     *
     * @param string       $userName        [description]
     * @param string       $password          [description]
     *
     * @return array [description]
     */
    public function loginByUserName($userName, $password, $userType = 2);

    /**
     * 完成用户登录.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string $mobile [description]
     * @param $clientInfo  $clientInfo    [description]
     *
     * @return array [description]
     */
    public function finishLogin($mobile, array $clientInfo);

    /**
     * 注册新用户.
     *
     * @param string       $mobile        [description]
     * @param string       $clientType    [description]
     *
     * @return array [description]
     */
    public function registerNewUser($mobile, $userType = 1);

    /**
     * 更新用户信息.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string       $mobile        [description]
     *
     * @return bool [description]
     */
    public function updateUser($userId, array $userInfo);

    /**
     * 获取用户信息.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string       $mobile        [description]
     *
     * @return bool [description]
     */
    public function getUser($userId);
}
