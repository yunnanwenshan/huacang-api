<?php

namespace App\Http\Auth;

use App\Contracts\ClientRequestAuth;
use App\Facades\ClientRequest;
use App\Http\Response\ClientResponse;
use Illuminate\Http\Request as HttpRequest;
use Log;
use Exception;

class Client extends Base implements ClientRequestAuth
{
    /**
     * the key of server token in header.
     */
    const SERVER_TOKEN = 'Server-Token';

    /**
     * the key of client signature in header.
     */
    const CLIENT_SIGNATURE = 'App-Signature';

    /**
     * User Request Validate.
     *
     * @return mixed
     */
    public function validate()
    {
        try {
            $this->valClientSig();
            $this->valServerToken();
        } catch (Exception $e) {
            Log::info(__FILE__ . '(' . __LINE__ . '), client validate fail, ', [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'dsc' => EnvConfig::env('middleware_SYS_MSG_SYSTEM_ERROR'),
            ]);
            throw new Exception('系统错误', $e->getCode());
        }
    }

    /**
     * Set Request Header Info.
     *
     * @param HttpRequest $request
     * @return mixed
     */
    public function setRequest(HttpRequest $request)
    {
        $this->setHttpRequest($request);
        $this->clientInfo = $this->getHeaderKey('clientInfo', 0);
        $this->checkClientInfo();
        ClientRequest::setClientInfo($this->getHeaderKey('clientInfo', 1));
        ClientRequest::setRequest($request);
    }

    /**
     * @param $key
     * @return string
     */
    private function getClientInfo($key)
    {
        $val = $this->clientInfo[$key];

        return is_null($val) ? '' : $val;
    }

    /**
     * check client info.
     */
    private function checkClientInfo()
    {
        $needKey = [
            'appnm', 'appVer', 'clientType', 'model', 'os', 'screen', 'did', 'dt', 'tz', 'channel',
        ];
        array_map(function ($item) {
            if (array_key_exists($item, $this->clientInfo) == false) {
                $this->validateFail('client info validate error', ClientResponse::SYS_CLIENT_INFO);
            }
        }, $needKey);
    }

    /**
     * @throws \Exception
     */
    public function valServerToken()
    {
        $serverToken = $this->getHeaderKey(self::SERVER_TOKEN);
        $this->validateServerToken($serverToken);
    }

    /**
     * validate client signature.
     *
     * @throws \Exception
     */
    public function valClientSig()
    {
        $clientSignature = $this->getHeaderKey(self::CLIENT_SIGNATURE);
        if (empty($clientSignature)) {
            $this->validateFail('validate client signature empty');
        }

        /*将签名分成两部分，第一部分是签名，第二部分是负载，中间用半角句号连接*/
        list($base64urlEncodeSignature, $jsonPayload) = explode('.', $clientSignature, 2);

        /*先验证负载部分*/
        $payloadArray = json_decode($jsonPayload, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($payloadArray)) {
            $this->validateFail('payload json decode err');
        }

        $request_uri = isset($payloadArray['uri']) ? $payloadArray['uri'] : 0;
        if ($request_uri != '/'.$this->request->path()) {
            $this->validateFail('request uri err');
        }

        $jti = isset($payloadArray['jti']) ? $payloadArray['jti'] : '';

        // request body 的签名验证
        if (isset($payloadArray['rbd']) && !empty($payloadArray['rbd'])) {
            // 注意可能有大小写的限制
            $requestBodySignature = strtolower(substr(md5($this->base64urlEncode(file_get_contents('php://input')).$jti.$this->getClientInfo('did')), 0, 8));
            if ($requestBodySignature != strtolower($payloadArray['rbd'])) {
                $this->validateFail('系统错误');
            }
        } else {
            $this->validateFail('request body signature verify failed(234)');
        }

        $rsaPublicKeyString = config('keys.public_keys_for_verify_signature.android');

        if ($this->clientInfo['clientType'] == 'ios') {
            $rsaPublicKeyString = config('keys.public_keys_for_verify_signature.ios');
        }

        // 验证签名部分
        $publicKeyResource = openssl_get_publickey($rsaPublicKeyString);
        $publicKeyDetails  = openssl_pkey_get_details($publicKeyResource);
        if (!isset($publicKeyDetails['key']) || $publicKeyDetails['type'] !== OPENSSL_KEYTYPE_RSA) {
            $this->validateFail('This key is not compatible with RSA signatures');
        }
        $signature = $this->base64urlDecode($base64urlEncodeSignature);
        if (openssl_verify($jsonPayload, $signature, $publicKeyResource, OPENSSL_ALGO_SHA256) === 1) {
        } else {
            $this->validateFail('网络异常，请稍后重试');
        }
    }

    /**
     * @param $data
     * @return string
     */
    private function base64urlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @param $data
     * @return string
     */
    private function base64urlDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
