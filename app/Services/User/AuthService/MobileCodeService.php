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

    private function send($mobile, $code)
    {
        $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
        $smsConf = array(
            'key'   => 'e7b94e0f6810c701556c09abc59540fc', //您申请的APPKEY
            'mobile'    => $mobile, //接受短信的用户手机号码
            'tpl_id'    => '54005', //您申请的短信模板ID，根据实际情况修改
            'tpl_value' =>'#code#='.$code.'&#m#=10' //您设置的模板变量，根据实际情况修改
        );

        $content = $this->juhecurl($sendUrl, $smsConf, 1); //请求发送短信

        if($content){
            $result = json_decode($content,true);
            $error_code = $result['error_code'];
            if($error_code == 0){
                //状态为0，说明短信发送成功
                echo "短信发送成功,短信ID：".$result['result']['sid'];
            }else{
                //状态非0，说明失败
                $msg = $result['reason'];
                echo "短信发送失败(".$error_code.")：".$msg;
            }
        }else{
            //返回内容异常，以下可根据业务逻辑自行修改
            echo "请求发送短信失败";
        }
    }

    /**
     * 请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
     */
    private function juhecurl($url, $params=false, $ispost=0){
        $httpInfo = array();
        $ch = curl_init();
        $header = array('content-type:text/html;charset=utf-8');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt($ch, CURLOPT_USERAGENT , 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22' );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 30 );
        curl_setopt($ch, CURLOPT_TIMEOUT , 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER , true );

        if($ispost) {
            curl_setopt($ch , CURLOPT_POST , true );
            curl_setopt($ch , CURLOPT_POSTFIELDS , $params );
            curl_setopt($ch , CURLOPT_URL , $url );
        } else {
            if($params) {
                curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
            } else {
                curl_setopt( $ch , CURLOPT_URL , $url);
            }
        }

        $response = curl_exec( $ch );
        if ($response === FALSE) {
            return false;
        }

        $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
        $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
        curl_close( $ch );

        return $response;
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

            $res = $this->send($mobile, $code);

            Log::info(__FILE__ . '(' . __LINE__ . '), send sms successful', [
                'mobile' => $mobile,
                'code'   => $code,
                'res' => $res,
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
