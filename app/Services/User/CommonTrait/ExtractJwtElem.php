<?php

namespace App\Services\User\CommonTrait;

use Exception;
use Lcobucci\JWT\Parser;

trait ExtractJwtElem
{
    /**
     * 从jwt中获取微信openid.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string $jwt [description]
     *
     * @return string [description]
     */
    public function extractOpenId($jwt)
    {
        try {
            $token  = (new Parser())->parse((string) $jwt);
            $openId = $token->getClaim('openid');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $openId;
    }

    /**
     * 从jwt中获取用户id.
     *
     * @author liutianping@ttyongche.com
     *
     * @param string $jwt [description]
     *
     * @return string [description]
     */
    public function extractUserId($jwt)
    {
        try {
            $token = (new Parser())->parse((string) $jwt);
            $uid   = $token->getClaim('uid');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $uid;
    }

    /**
     * 从jwt中获取token
     *
     * @author liutianping@ttyongche.com
     *
     * @param string       $jwt        [description]
     *
     * @return string [description]
     */
    public function extractTtu($jwt)
    {
        try {
            $token = (new Parser())->parse((string) $jwt);
            $uid = $token->getClaim('ttu');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $uid;
    }
}
