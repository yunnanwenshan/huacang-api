<?php

namespace App\Services\User\AuthService;

use App\Exceptions\User\UserException;
use App\Models\User;
use App\Models\UserInfo;
use App\Services\User\AuthService\Contracts\MobileCodeInterface;
use App\Services\User\AuthService\Contracts\UserOperateInterface;
use App\Services\User\CommonTrait\GenerateJwt;
use DB;
use Log;
use Cookie;

class UserOperateService implements UserOperateInterface
{
    /**
     * 引入生成jwt字符串的方法
     */
    use GenerateJwt;

    /**
     * 构造函数.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string $mobile [description]
     * @param string $code   [description]
     *
     * @return null [description]
     */
    public function __construct(MobileCodeInterface $mobileCode)
    {
        $this->mobileCode = $mobileCode;
    }

    /**
     * 验证用户信息.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string $mobile [description]
     * @param string $code   [description]
     * @param $clientInfo  $clientInfo    [description]
     *
     * @return array [description]
     */
    public function login($mobile, $code, array $clientInfo, $userType = 1)
    {
        //校验验证码
        $this->mobileCode->verifyCode($mobile, $code);
        //用户登录
        return $this->finishLogin($mobile, $clientInfo, $userType);
    }


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
    public function loginByUserName($userName, $password, $userType = 2)
    {
        //新用户标记
        $isNewUser = 0;
        $user = User::where('user.user_name', $userName)
            ->join('user_info', 'user.id' , '=', 'user_info.user_id')
            ->select('user.id', 'user_info.user_id', 'user.mobile', 'user_info.client_type', 'user_info.has_order')
            ->first();

        if (empty($user)) {
            Log::error(__FILE__ . '(' . __LINE__ . '), user login fail, ', [
                'user_name' => $userName,
                'password' => $password,
                'user_type' => $userType,
            ]);
            throw new UserException(UserException::ADMIN_USER_LOGIN_NOT_EXIST, UserException::DEFAULT_CODE + 45);
        }

        $passwordSha1 = sha1($password);
        if (strncmp($passwordSha1, $user->password, strlen($user->password)) != 0) {
            Log::info(__FILE__ . '(' . __LINE__ . '), user login fail, ', [
                'user_name' => $userName,
                'password' => $password,
                'user_type' => $userType,
            ]);
            throw new UserException(UserException::ADMIN_USER_PASSWORD_ERROR, UserException::DEFAULT_CODE + 46);
        }

        $userId = $user->user_id;
        $clientInfo = app('Illuminate\Http\Request')->header('clientInfo');
        $jwt = $this->generateJwt($clientInfo, $userName, $userId, $userType);

        $user->token = $jwt['access_token'];
        $user->save();

        $result = array_merge(['ticket' => $jwt['jwt'], 'is_new_user' => $isNewUser, 'user_id' => $userId]);

        Log::info(__FILE__.'('.__LINE__.'), admin user login successful, ', [
            'user_name' => $userName,
            'password' => $password,
            'clientInfo' => $clientInfo,
        ]);

        return $result;
    }

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
    public function finishLogin($mobile, array $clientInfo, $userType = 1)
    {
        //新用户标记
        $isNewUser = 0;
        $user = User::where('user.mobile', $mobile)
            ->join('user_info', 'user.id' , '=', 'user_info.user_id')
            ->select('user.id', 'user_info.user_id', 'user.mobile', 'user_info.client_type', 'user_info.has_order')
            ->first();

        if (empty($user)) {
            try {
                $isNewUser = 1;

                DB::beginTransaction();
                //注册新用户
                $user = $this->registerNewUser($mobile, $userType);
                $userId = $user->id;
                DB::commit();

                Log::info(__FILE__.'('.__LINE__.'), line create new user successful', [
                    'mobile' => $mobile,
                    'clientInfo' => $clientInfo,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::info('login fail', [
                    'errMSG' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                throw new UserException($e->getMessage(), $e->getCode());
            }
        } else {
            $userId = $user->user_id;
        }

        $jwt = $this->generateJwt($clientInfo, $mobile, $userId, $userType);

        $user->token = $jwt['access_token'];
        $user->save();

        $result = array_merge(['ticket' => $jwt['jwt'], 'is_new_user' => $isNewUser, 'user_id' => $userId]);

        Log::info(__FILE__.'('.__LINE__.'), user login successful, ', [
            'mobile' => $mobile,
            'clientInfo' => $clientInfo,
        ]);

        return $result;
    }

    /**
     * 注册新用户
     */
    public function registerNewUser($mobile, $userType = 1)
    {
        //用户基本信息
        $user = new User();
        $user->mobile = $mobile;
        $user->save();

        //用户扩展信息
        $userInfo = new UserInfo();
        $userInfo->user_id = $user->id;
        $userInfo->mobile = $mobile;
        $userInfo->client_type = $userType;
        $userInfo->save();

        return $user;
    }

    /**
     * 更新用户信息.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string $mobile [description]
     *
     * @return bool [description]
     */
    public function updateUser($userId, array $userInfo)
    {
        try {
            DB::begintransaction();
            if (isset($userInfo['avatar']) && !empty($userInfo['avatar'])) {
                $this->updateUserAvatar($userId, $userInfo['avatar']);
            }

            if (isset($userInfo['sex']) && ($userInfo['sex'] != 0)) {
                $this->updateUserSex($userId, $userInfo['sex']);
            }

            if (isset($userInfo['real_name']) && (!empty($userInfo['real_name']))) {
                $this->updateUserRealName($userId, $userInfo['real_name']);
            }

            DB::commit();

            Log::info(__FILE__ . '(' . __LINE__ . '), update user info successful, ', [
                'user_id' => $userId,
                'userInfo' => $userInfo,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error(__FILE__ . '(' . __LINE__ . '), update user info fail, ', [
                'user_id' => $userId,
                'user_info' => $userInfo,
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ]);
        }
    }

    private function updateUserAvatar($userId, $avatar)
    {
        UserInfo::where('user_id', $userId)
            ->limit(1)
            ->update(['avatar' => $avatar]);
    }

    private function updateUserSex($userId, $sex)
    {
        UserInfo::where('user_id', $userId)
            ->limit(1)
            ->update(['gender' => $sex]);
    }

    private function updateUserRealName($userId, $realName)
    {
        UserInfo::where('user_id', $userId)
            ->limit(1)
            ->update(['real_name' => $realName]);
    }

    /**
     * 获取用户信息.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string $mobile [description]
     *
     * @return bool [description]
     */
    public function getUser($userId)
    {
        $user = User::where('id', $userId)->first();
        if (empty($user)) {
            Log::info(__CLASS__.':'.__FUNCTION__.'=>'.'userid = '.$userId.' not exist');
            throw new UserException(UserException::AUTH_USER_NOT_EXIST, UserException::DEFAULT_CODE + 6);
        }
        $userInfo = $user->export();

        return $userInfo;
    }
}
