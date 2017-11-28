<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\User\UserException;
use App\Models\User;
use App\Services\User\Contracts\UserAuthInterface;
use Illuminate\Http\Request;
use Exception;
use Log;

class AdminController extends Controller
{
    /**
     * 构造函数，
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
            'user_name' => 'required|string|min:6',
            'password' => 'required|alpha_num|min:6',
        ]);

        // 使用验证码进行登录，目前只支持验证码登录一种方式
        $userName = $request->input('user_name');
        $password = $request->input('password');
        $userType = 2;

        try {
            $result = $this->userAuth->loginByUserName($userName, $password, $userType);
            $serverToken = $ticket = $result['ticket'];
//            $cookieToken = cookie()->make('Server-Token', $serverToken, time() + 86400 * 30, '/', '.'.config('config.reliable_root_domain'), false, true);
//            $cookieUid = cookie()->make('uid', $result['user_id'], time() + 86400 * 30, '/', '.'.config('config.reliable_root_domain'), false, false);
            $cookieToken = cookie()->make('Server-Token', $serverToken, time() + 86400 * 30, '/', '.', false, true);
            $cookieUid = cookie()->make('uid', $result['user_id'], time() + 86400 * 30, '/', '.', false, false);
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
}