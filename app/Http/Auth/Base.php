<?php

namespace App\Http\Auth;

use App\Components\Secure;
use App\Http\Response\ClientResponse;
use ClientRequest;
use Exception;
use Illuminate\Http\Request as HttpRequest;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256 as HS256;
use Lcobucci\JWT\Token;
use Log;

class Base
{
    /**
     * validate error code.
     */
    const VALIDATE_FAIL = 1000;

    /**
     * httpHeader info.
     *
     * @var null
     */
    protected $httpHeader = null;

    /**
     * http request.
     *
     * @var HttpRequest
     */
    protected $request = null;

    /**
     * header client info.
     *
     * @var null
     */
    protected $clientInfo = null;

    /**
     * @param $key
     * @throws \Exception
     * @return mixed
     */
    protected function getHeaderKey($key, $origin = 1)
    {
        $raw = $this->request->header($key);
        if (is_null($raw)) {
            $this->validateFail($key.' the key is null');
        }
        if ($origin == 1) {
            return $raw;
        }
        $array = json_decode($raw, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            $this->validateFail("$key json decode error");
        }

        return $array;
    }

    /**
     * 设置client info.
     *
     * @param array $clientInfo
     */
    protected function setClientInfo(array $clientInfo)
    {
        $this->clientInfo   = $clientInfo;
    }

    /**
     * set http request.
     *
     * @param HttpRequest $request
     */
    public function setHttpRequest(HttpRequest $request)
    {
        $this->request    = $request;
        $this->httpHeader = $request->header();
    }

    /**
     * @param $msg
     * @throws \Exception
     */
    protected function validateFail($msg, $code = ClientResponse::SYS_CLIENT_STOKEN)
    {
        Log::warning('Client Validate Fail', [
            'code'      => $code,
            'msg'       => $msg,
            'file'      => __FILE__,
            'class'     => __CLASS__,
            'request'   => $this->request->all(),
            'header'    => $this->httpHeader,
            'path'      => $this->request->getBasePath(),
        ]);
        throw new \Exception($msg, $code);
    }

    /**
     * 验证server token.
     *
     * @param $serverToken
     * @throws Exception
     */
    protected function validateServerToken($serverToken)
    {
        if (empty($serverToken)) {
            $this->validateFail('Validate server token empty', ClientResponse::SYS_CLIENT_STOKEN);
        }

        try {
            $token = (new Parser())->parse((string) $serverToken);

            /*验证alg字段，防止有人alg=none*/
            if ('HS256' != $token->getHeader('alg')) {
                $this->validateFail('Token alg is empty');
            }

            $signer = new HS256();

            if (!$token->verify($signer, config('keys.keys_for_token_signature'))) {
                $this->validateFail('Sign verify failed');
            }

            if ($token->getClaim('env') != hash('crc32b', env('APP_ENV'))) {
                $this->validateFail('Env verify failed');
            }

            $this->setUser($token);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), ClientResponse::SYS_CLIENT_STOKEN);
        }
    }

    /**
     * 谁知用户.
     *
     * @param  Token     $token
     * @throws Exception
     */
    protected function setUser(Token $token)
    {
        /*服务器端颁发的token*/
        $ticket = $token->getClaim('ttu');
//        $userType = $token->getClaim('utype');

        if (isset($ticket) && !empty($ticket)) {
            $this->user     = new \stdClass();
            $this->user->id = $token->getClaim('uid');
            ClientRequest::setUser($this->user->id, $ticket);
        } else {
            $this->validateFail('ticket is empty');
        }
    }
}
