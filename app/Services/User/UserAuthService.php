<?php

namespace App\Services\User;

use App\Services\User\AuthService\Contracts\MobileCodeInterface;
use App\Services\User\AuthService\Contracts\UserOperateInterface;
use Log;

class UserAuthService extends UserAuthAbstract
{
    /**
     * 用户使用验证码的方式进行登录.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string $mobile [description]
     * @param string $code [description]
     *
     * @return array [description]
     */
    public function __construct(
        MobileCodeInterface $mobileCode,
        UserOperateInterface $userOperate
    )
    {
        $this->mobileCode = $mobileCode;
        $this->userOperate = $userOperate;
    }

    /**
     * 用户使用验证码的方式进行登录.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string $mobile [description]
     * @param string $code [description]
     *
     * @return array [description]
     */
    public function loginByMobileCode($mobile, $code, $clientInfo, $userType = 1)
    {
        return $this->userOperate->login($mobile, $code, $clientInfo, $userType);
    }

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
    public function loginByUserName($userName, $password, $userType = 1)
    {
        return $this->userOperate->loginByUserName($userName, $password, $userType);
    }

    /**
     * 发送验证码.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string $mobile [description]
     * @param int $type [description]
     *
     * @return mixed [description]
     */
    public function sendCode($mobile)
    {
        return $this->mobileCode->sendCode($mobile);
    }

    /**
     * 获取用户信息.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string $userId [description]
     *
     * @return mixed [description]
     */
    public function getUserInfo($userId)
    {
        return $this->userOperate->getUser($userId);
    }

    /**
     * 更新用户信息.
     *
     * @author liutianping@ttyongche.com
     *
     * @param array $userInfo [description]
     *
     * @return mixed [description]
     */
    public function updateUserInfo($userId, array $userInfo)
    {
        if (!is_array($userInfo)) {
            return;
        }

        return $this->userOperate->updateUser($userId, $userInfo);
    }
}
