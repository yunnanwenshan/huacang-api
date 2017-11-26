<?php

namespace App\Exceptions\User;

use Exception;

class UserException extends Exception
{
    /**
     * 用户无法正常登录时，返回的异常状态开始值
     */
    const DEFAULT_CODE = 90000;

    /**
     * 异常文案信息.
     */
    const AUTH_CODE_FAIL          = '验证码验证失败';     // DEFAULT_CODE + 1
    const AUTH_CLIENT_INFO        = '缺少请求参数';         // DEFAULT_CODE + 2
    const AUTH_INVALID_MOBILE     = '请输入正确的手机号码';    // DEFAULT_CODE + 3
    const AUTH_NET_FAIL           = '验证码发送失败，稍后重试';        // DEFAULT_CODE + 4
    const AUTH_CODE_EXPIRE        = '验证码已过期';     // DEFAULT_CODE + 5
    const AUTH_USER_NOT_EXIST     = '用户不存在';    // DEFAULT_CODE + 6, 42, 43
    const AUTH_USER_CERTIFIED     = '已实名认证，用户名不允许修改';    // DEFAULT_CODE + 7
    const AUTH_CERTIFICATE_PARAM  = '实名认证信息有误';     // DEFAULT_CODE + 8
    const AUTH_CERTIFICATE_FAIL   = '实名认证失败';     // DEFAULT_CODE + 9
    const AUTH_BIND_CARD_PARAM    = self::AUTH_CLIENT_INFO;     // DEFAULT_CODE + 10
    const AUTH_BIND_CARD_FAIL     = '请输入正确的卡号';     // DEFAULT_CODE + 11
    const AUTH_BINDED_CARD        = '卡已绑定';     // DEFAULT_CODE + 12
    const AUTH_BIND_CARD_FAIL2    = '暂不支持';     // DEFAULT_CODE + 13
    const AUTH_CERTIFICATED       = '已认证';      //DEFAULT_CODE + 14
    const AUTH_BIND_INVALID_CARD  = '请填写个人储蓄卡号，不支持信用卡提现';      //DEFAULT_CODE + 15
    const AUTH_USER_NOT_CERTIFIED = '未实名认证';      //DEFAULT_CODE + 16
    const AUTH_SERVERTOKEN_EMPTY  = 'Server-Token is null';      //DEFAULT_CODE + 17
    const AUTH_BANK_CERT_FAIL     = '非本人名下的银行卡，请输入正确卡'; //DEFAULT_CODE + 18, 19
    const AUTH_DEPOSIT_NOT_PAY    = '押金未缴纳'; //DEFAULT_CODE + 20, 25
    const AUTH_CERTIFIED_NOT      = '未实名认证'; //DEFAULT_CODE + 21
    const AUTH_WALLET_NEG         = '车费余额不足，充值后可用车'; //DEFAULT_CODE + 42
    const AUTH_IDCARD_AGE_LESS    = '未满16周岁暂时不可以驾驶电动自行车，请到"钱包"退押金'; //DEFAULT_CODE + 23
    const AUTH_IDCARD_AGE_GREATER = '65周岁及以上暂时不可以驾驶电动车，请到"钱包"退押金'; //DEFAULT_CODE + 24
    const AUTH_IDCARD_MUST_UNIQUE = '此身份证已经注册过了，一个身份证只允许注册一个账号。如果你有问题请联系客服处理。'; //DEFAULT_CODE + 26
    const AUTH_NEW_MOBILE_EXIST   = '新号码已被注册';    // DEFAULT_CODE + 27
    const AUTH_CERT_USER_NOT_EXT  = '你的信息暂时无法认证，需要提交资料进行人工认证'; //DEFAULT_CODE + 28
    const AUTH_SMS_MAX_VALUE      = '验证码获取频繁，稍后重试'; //DEFAULT_CODE + 29
    const AUTH_MANUL_CERTED       = '该用户已提交的资料已经审核通过'; //DEFAULT_CODE + 30
    const AUTH_ALIPAY_CERT_IS_NULL= '请使用身份证号实名认证'; //DEFAULT_CODE + 32
    const AUTH_MOBILE_IDCARD_USER_NO_MATCH = '支付宝手机号用户与身份证号用户不匹配'; //DEFAULT_CODE + 34
    const AUTH_ALIPAY_INFO_IS_NULL = '支付宝用户信息获取失败'; //DEFAULT_CODE + 35
    const AUTH_CANCEL_DEPOSIT_NOT_REFUND = '押金未退回'; //DEFAULT_CODE + 36
    const AUTH_CANCEL_INRIDING     = '有进行中订单'; //DEFAULT_CODE + 37
    const AUTH_CANCEL_HAS_DEBT     = '用户充值车费有欠款'; //DEFAULT_CODE + 38
    const AUTH_CANCEL_DONE         = '用户已注销'; //DEFAULT_CODE + 39
    const AUTH_CANCEL_ORDER_END_TIME = '最后一个订单结束时间未超过10分钟'; //DEFAULT_CODE + 40
    const AUTH_USER_CANCEL = '当前账户已被注销'; //DEFAULT_CODE + 41, 42
    const USER_ONE_MINUTE_NOT_REPEAT_SEND = '验证码1分钟内不能重复发送'; //DEFAULT_CODE + 43
    const USER_SEND_SMS_LIMITED = "验证码获取次数已达今日上限，如有疑问，请联系客服"; //DEFAULT_CODE + 44
    const ADMIN_USER_LOGIN_NOT_EXIST = '用户名不存在'; //DEFAULT_CODE + 45
    const ADMIN_USER_PASSWORD_ERROR = '用户名不存在'; //DEFAULT_CODE + 46

    public function __construct($message = '', $code = 0, $previous = null)
    {
        /*
         * 该code会在返回的json中直接使用，所以请勿使用0
         */
        if ($code == 0) {
            $code = self::DEFAULT_CODE;
        }
        parent::__construct($message, $code, $previous);
    }
}
