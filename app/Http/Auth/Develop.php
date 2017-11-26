<?php

namespace App\Http\Auth;

use App\Contracts\ClientRequestAuth;
use App\Facades\ClientRequest;
use Illuminate\Http\Request as HttpRequest;
use Log;

class Develop extends Base implements ClientRequestAuth
{
    /**
     * User Request Validate.
     *
     * @return mixed
     */
    public function validate()
    {
        $this->valClientSig();
        $this->valServerToken();
    }

    /**
     * Set Request Header Info.
     *
     * @param HttpRequest $request
     * @return mixed
     */
    public function setRequest(HttpRequest $request)
    {
        $this->request = $request;
        ClientRequest::setClientInfo($request->header('clientInfo'));
        ClientRequest::setRequest($request);
    }

    /**
     * @throws \Exception
     */
    public function valServerToken()
    {
    }

    /**
     * validate client signature.
     *
     * @throws \Exception
     */
    public function valClientSig()
    {
        $userId = $this->request->input('dev_user_id', 0);
        ClientRequest::setUser($userId, 0);
    }
}
