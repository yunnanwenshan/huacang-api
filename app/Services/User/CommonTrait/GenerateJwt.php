<?php

namespace App\Services\User\CommonTrait;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256 as HS256;
use Log;

trait GenerateJwt
{
    /**
     * 产生jwt字符串.
     *
     * @author liutianping@ttyongche.com
     *
     * @param array  $clientInfo [description]
     * @param string $mobile     [description]
     * @param int    $userId     [description]
     * @param string $openId     [description]
     *
     * @return string [description]
     */
    public function generateJwt($clientInfo, $mobile = '', $userId = 0, $userType = 1)
    {
        Log::debug(__FILE__.'.'.__LINE__, [
            'clientInfo' => $clientInfo,
            'mobile' => $mobile,
            'userId' => $userId,
        ]);

        $result = [
            'jwt' => '',
            'access_token' => '',
        ];

        //生成ticket
        $accessToken = md5(rand(111111111, 999999999).time());
        $result['access_token'] = $accessToken;

        //生成jwt
        $signer = new HS256();
        if (!strncmp($clientInfo['clientType'], 'Web', strlen($clientInfo['clientType']))) {
            //web端登录
            $str = $clientInfo['appnm'].'-'.$clientInfo['clientType'];

            $jwt = (new Builder())->setAudience($str)
                ->setIssuedAt(time())
                ->set('env', hash('crc32b', env('APP_ENV')))
                ->set('ttu', $accessToken)
                ->set('uid', strval($userId))
                ->set('utype', strval($userType))
                ->sign($signer, config('keys.keys_for_token_signature'))
                ->getToken()
                ->__toString();
        } else {
            $str = $clientInfo['appnm'].'-'.$clientInfo['clientType'].'-'.$clientInfo['appVer'];
            $jwt = (new Builder())->setAudience($str)
                ->setIssuedAt(time())
                ->set('env', hash('crc32b', env('APP_ENV')))
                ->set('ttu', $accessToken)
                ->set('uid', strval($userId))
                ->set('utype', strval($userType))
                ->sign($signer, config('keys.keys_for_token_signature'))
                ->getToken()
                ->__toString();
        }

        $result['jwt'] = $jwt;

        return $result;
    }
}
