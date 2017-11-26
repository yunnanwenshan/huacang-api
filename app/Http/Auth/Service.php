<?php

namespace App\Http\Auth;

use App\Contracts\ServiceRequestAuth;
use Illuminate\Http\Request as HttpRequest;
use Log;

class Service extends Base implements ServiceRequestAuth
{
    /**
     * Service Token.
     */
    const SERVICE_TOKEN = 'Service-Token';

    /**
     * User Request Validate.
     *
     * @return mixed
     */
    public function validate()
    {
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
        $this->getHeaderKey(self::SERVICE_TOKEN);
    }
}
