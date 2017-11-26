<?php

namespace App\Http\Requests;

use App\Contracts\ClientRequest as Request;
use App\Facades\ClientAuth;
use App\Facades\WebAuth;
use App\Http\Auth\Client;
use App\Http\Auth\Develop;
use App\Http\Auth\Web;
use App\Models\User;
use App\Services\User\CommonTrait\ExtractJwtElem;
use Cache;
use Cookie;
use Log;

class ClientRequest implements Request
{
    use ExtractJwtElem;

    /**
     * 用户信息.
     */
    public $user;

    /**
     * 客户端上传的信息.
     */
    public $clientInfo;

    /**
     * 客户端请求request.
     */
    public $request;

    /**
     * Get client info and User info.
     *
     * @return mixed
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get client info and User info.
     *
     * @return mixed
     */
    public function set($clientInfo, $userId, $ticket)
    {
        $this->setClientInfo($clientInfo);
        $this->setUser($userId, $ticket);
    }

    /**
     * Set client info.
     *
     * @return mixed
     */
    public function setClientInfo($clientInfo)
    {
        $data = json_decode($clientInfo, true);
        $this->clientInfo = $data;
    }

    /**
     * Set client info and User info.
     *
     * @return mixed
     */
    public function setUser($userId, $ticket)
    {
        if (!empty($userId)) {
            $this->user = User::where('id', $userId)->first();
        } elseif (!empty($ticket)) {
            $this->user = User::where('token', $ticket)->first();
        } else {
            Log::warning(__FILE__.'('.__LINE__.'), ', [
                'userId' => $userId,
                'ticket' => $ticket,
                'this->user' => $this->user,
            ]);
        }
    }

    /**
     * 获取指定key的value.
     *
     * @param string $key [description]
     *
     * @return
     */
    public function getClientInfo($key = null)
    {
        return array_get($this->clientInfo, $key);
    }

    /**
     * 获取clientInfo.
     *
     * @param string $key [description]
     *
     * @return
     */
    public function getInfo()
    {
        return $this->clientInfo;
    }

    /**
     * 获取指定key的value.
     *
     * @param string $key [description]
     *
     * @return
     */
    public function getUser()
    {
        if ($this->user == null) {
            try {
                if (config('app.debug') && starts_with($this->request->path(), 'debug')) {
                    $develop = new Develop();
                    $develop->setRequest($this->request);
                    $develop->validate();

                    /*Web需要通过web的验证*/
                } elseif (starts_with($this->request->path(), 'web')) {
                    $serverToken = empty(Cookie::get(Web::SERVER_TOKEN)) ? $this->request->header(Web::SERVER_TOKEN) : Cookie::get(Web::SERVER_TOKEN);
                    if (!empty($serverToken)) {
                        WebAuth::setHttpRequest($this->request);
                        WebAuth::valServerToken();
                    }
                    /*其他的通过client的验证*/
                } elseif (starts_with($this->request->path(), 'app')) {
                    $serverToken = $this->request->header(Client::SERVER_TOKEN);
                    if (!empty($serverToken)) {
                        ClientAuth::setRequest($this->request);
                        ClientAuth::validate();
                    }
                }
            } catch (\Exception $e) {
                $this->user = null;
            }
        }

        return $this->user;
    }
}
