<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\User\UserException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\User\Contracts\UserAuthInterface;
use ClientRequest;
use Exception;
use Illuminate\Http\Request;
use Validator;
use Log;
use Cache;
use Redis;

class UserController extends Controller
{
    /**
     * 构造函数，注入UserAuthInterface的实现UserAuthService::class.
     *
     * @param UserAuthInterface $userAuth [description]
     * @param Request           $request  [description]
     *
     * @return not [description]
     */
    public function __construct(UserAuthInterface $userAuth, Request $request)
    {
        parent::__construct($request);
        $this->userAuth = $userAuth;
    }

    /**
     * 登录.
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function login(Request $request)
    {
        // 验证参数
        $this->validate($request, [
            'mobile' => 'required',
            'code' => 'required|alpha_num',
        ]);

        // 使用验证码进行登录，目前只支持验证码登录一种方式
        $input = $request->input();
        $mobile = strval($input['mobile']);
        $code = $input['code'];
        $userType = 1;

        try {
            if (!is_array($this->info)) {
                $this->info = ClientRequest::getInfo();
                if (!is_array($this->info)) {
                    throw new UserException(UserException::AUTH_CLIENT_INFO, UserException::DEFAULT_CODE + 2);
                }
            }

            $result = $this->userAuth->loginByMobileCode($mobile, $code, $this->info, $userType);
            $serverToken = $ticket = $result['ticket'];
            $cookieToken = cookie()->make('Server-Token', $serverToken, time() + 86400 * 30, '/', '.'.config('config.reliable_root_domain'), false, false);
            $cookieUid = cookie()->make('uid', $result['user_id'], time() + 86400 * 30, '/', '.'.config('config.reliable_root_domain'), false, false);
            $rs['ticket'] = $ticket;
            $rs['is_new_user'] = $result['is_new_user'];
            $user = User::where('id', $result['user_id'])->first();
            $rs = array_merge($rs, $user->export());

            return response()->clientSuccess($rs)
                ->withCookie($cookieToken)
                ->withCookie($cookieUid);
        } catch (UserException $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 发送验证码.
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function sendSeccode(Request $request)
    {
        $this->validate($request, [
            'mobile' => 'required',
        ]);

        $input = $request->input();
        $mobile = strval($input['mobile']);
        try {
            $limitMobileKey = 'rate_limit_' . $mobile;
            $mobileNum = Redis::get($limitMobileKey);
            if (!empty($mobileNum)) {
                Log::info('===========================mobile', ['mobileNum' => $mobileNum]);
                throw new Exception('发送短信太频繁，1分钟之后再重试', 1);
            }
            Redis::transaction(function ($tx) use ($limitMobileKey) {
                $tx->set($limitMobileKey, 1);
                $tx->expire($limitMobileKey, 60); //过期时间一天
            });
            $result = $this->userAuth->sendCode($mobile);
        } catch (UserException $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess($result, $result['msg']);
    }

    /**
     * 获取用户信息.
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function getUserInfo(Request $request)
    {
        $userId = is_null($this->user) ? 0 : $this->user->id;
        try {
            $result = $this->userAuth->getUserInfo($userId);

            $userInfo = [
                'user_id' => $result['user_id'],
                'name' => $result['name'],
                'avatar' => $result['avatar'],
                'mobile' => $result['mobile'],
                'sex' => $result['sex'],
            ];

            Log::info(__FILE__ . '(' . __LINE__ . '), user detail, ', [
                'user_id' => $userId,
                'user_info' => $userInfo,
                'clientInfo' => $request->header('clientInfo'),
            ]);
        } catch (UserException $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess($userInfo);
    }

    /**
     * 更改用户信息.
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function updateUserInfo(Request $request)
    {
        $this->validate($request, [
            'avatar' => 'url',
        ]);

        try {
            $userId = is_null($this->user) ? 0 : $this->user->id;
            $userInfo['avatar'] = $request->input('avatar');
            $result = $this->userAuth->updateUserInfo($userId, $userInfo);
        } catch (UserException $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess($result);
    }
}
