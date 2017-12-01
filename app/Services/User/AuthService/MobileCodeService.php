<?php

namespace App\Services\User\AuthService;

use App\Exceptions\User\UserException;
use App\Models\Code;
use App\Services\User\AuthService\Contracts\MobileCodeInterface;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Log;
use Exception;

class MobileCodeService implements  MobileCodeInterface
{
    /**
     * 聚合数据发送短信https://www.juhe.cn
     *
     * @author liutianping@ttyongche.com
     *
     * @param string       $mobile        [description]
     * @param int          $type          [description]
     *
     * @return bool
     */
    private function sendSms($mobile, $code)
    {
        $urlConf = 'http://v.juhe.cn/sms/send?key=e7b94e0f6810c701556c09abc59540fc&mobile=%s&tpl_id=54005&tpl_value=%s';
        $talValue = urlencode('#code#='.$code.'&#m#=10');
        $url = sprintf($urlConf, $mobile, $talValue);

        $client = new Client();
        $response = $client->get($url);
        if ($response->getStatusCode() != 200) {
            Log::info(__FILE__ . '(' . __LINE__  .'), request sms server fail, ', [
                'mobile' => $mobile,
                'code' => $code,
                'response' => $response,
            ]);
            return false;
        }

        $result = json_decode((string)$response->getBody(), true);
        if (json_last_error()) {
            Log::info(__FILE__ . '(' . __LINE__ . '), send sms fail, parse json fail, ', [
                'mobile' => $mobile,
                'code' => $code,
                'resposne' => $response,
            ]);
            return false;
        }

        if ($result['error_code'] != 0) {
            Log::info(__FILE__ . '(' . __LINE__ . '), send sms fail,', [
                'mobile' => $mobile,
                'code' => $code,
                'result' => $result,
            ]);
            return false;
        }

        Log::info(__FILE__ . '(' . __LINE__ . '), send sms successful, ', [
            'mobile' => $mobile,
            'code' => $code,
            'result' => $result,
        ]);

        return true;
    }

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

            $res = $this->sendSms($mobile, $code);

            Log::info(__FILE__ . '(' . __LINE__ . '), send sms successful', [
                'mobile' => $mobile,
                'code'   => $code,
                'res' => $res,
            ]);

            if ($res == false) {
                throw new Exception("send sms fail",1);
            }
        } catch (Exception $e) {
            Log::info(__FILE__ . '(' . __LINE__ . '), send sns fail' , [
                'mobile' => $mobile,
                'msg' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw new UserException(UserException::AUTH_NET_FAIL, UserException::DEFAULT_CODE + 4);
        }

        return [
            'msg' => 'success'
        ];
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
