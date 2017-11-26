<?php

namespace App\Services\User\AuthService;

use App\Exceptions\User\UserException;
use App\Models\Code;
use App\Services\User\AuthService\Contracts\MobileCodeInterface;
use Carbon\Carbon;
use Log;
use Exception;

class MobileCodeService implements  MobileCodeInterface
{

    /**
     * 发送验证码.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string       $mobile        [description]
     * @param int          $type          [description]
     *
     * @return array [description]
     */
    public function sendCode($mobile)
    {

        $result = null;
        $msg = '验证码发送失败，请稍后重试';

        try {
            //针对当前mobile，先删除之前旧的数据
            $codeRecord = Code::where('mobile', $mobile)->first();

            if ($codeRecord) {
                $codeRecord->delete();
            }

            //保持验证码到库中
            $code = rand(1000, 9999);
            $codeRecord = new Code();
            $codeRecord->mobile = $mobile;

            $codeRecord->verify_times = 0;
            $codeRecord->code = $code;
            //过期时间一分钟
            $codeRecord->code_expired = with(Carbon::now())->addMinute(10);
            $codeRecord->save();

            //将验证码发送到用户手机上
            if ($result) {
                $msg = $result['errmsg'];
            }

            Log::info(__FILE__ . '(' . __LINE__ . '), send sms successful', [
                'mobile' => $mobile,
                'code'   => $code,
                'result' => $result,
            ]);
        } catch (Exception $e) {
            Log::info(__FILE__ . '(' . __LINE__ . '), send sns fail' , [
                'mobile' => $mobile,
                'msg' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw new UserException(UserException::AUTH_NET_FAIL, UserException::DEFAULT_CODE + 4);
        }

        return ['msg' => $msg, 'sms_code' => $code];
    }

    /**
     * 验证登录验证码是否正确.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string       $mobile        [description]
     * @param string       $code          [description]
     *
     * @return bool [description]
     */
    public function verifyCode($mobile, $code)
    {
        //为苹果测试账号
        if (!strncmp($mobile, '10000000000', 11) && !strncmp($code, '1234', 4)) {
            return true;
        }

        //市场测试账号
        if (!strncmp($mobile, '00000000000', 11) && !strncmp($code, '1234', 4)) {
            return true;
        }

        //自己测试使用
        if (!strncmp($mobile, '17100000080', 11) && !strncmp($code, '1234', 4)) {
            return true;
        }

        $type = Code::CODE_LOGIN;
        return $this->verifyCodeBase($mobile, $code, $type);
    }

    /**
     * 验证验证码.
     *
     * @param string       $mobile        [description]
     * @param string       $code          [description]
     *
     * @return bool [description]
     */
    public function verifyCodeBase($mobile, $code, $type)
    {
        $codeRecord = Code::where('mobile', $mobile)
            ->orderby('update_time', 'desc')
            ->first();

        Log::info(__FILE__ . '(' . __LINE__ . '), verify code', [
            'mobile' => $mobile,
            'code' => $code,
            'type'   => $type,
            'code_recode' => $codeRecord,
        ]);

        if (empty($codeRecord) || ($codeRecord->code === 0)) {
            Log::info(__FILE__ . '(' . __LINE__ . '), verify code fail, ', [
                'mobile' => $mobile,
                'code' => $code,
                'type' => $type,
                'recode_code' => empty($codeRecord) ? null : $codeRecord->code,
            ]);
            throw new UserException(UserException::AUTH_CODE_FAIL, UserException::DEFAULT_CODE + 1);
        }

        $currentTime = with(Carbon::now())->timestamp;
        $expire      = with(new Carbon($codeRecord->code_expired))->timestamp;
        if ($expire < $currentTime) {
            Log::info(__FILE__ . '(' . __LINE__ . '), verify code expired, ', [
                'mobile' => $mobile,
                'code' => $code,
                'type' => $type,
                'expire' => $expire,
                'current_time' => $currentTime,
                'recode_code' => empty($codeRecord) ? null : $codeRecord->code,
            ]);
            throw new UserException(UserException::AUTH_CODE_EXPIRE, UserException::DEFAULT_CODE + 5);
        }

        if ($code == $codeRecord->code) {
            $codeRecord->code         = 0;
            $codeRecord->verify_times = 0;
            $codeRecord->code_expired = 0;
            $codeRecord->save();

            return true;
        } else {
            if ($codeRecord->verify_times < 3) {
                $codeRecord->verify_times += 1;
                $codeRecord->save();
            } else {
                $codeRecord->code         = 0;
                $codeRecord->verify_times = 0;
                $codeRecord->code_expired = 0;
                $codeRecord->save();
            }
        }
        Log::info(__FILE__ . '(' . __LINE__ . '), verify code err, ', [
            'mobile' => $mobile,
            'code' => $code,
            'type' => $type,
            'recode_code' => empty($codeRecord) ? null : $codeRecord->code,
        ]);
        throw new UserException(UserException::AUTH_CODE_FAIL, UserException::DEFAULT_CODE + 1);
    }
}
