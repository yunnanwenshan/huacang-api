<?php

namespace App\Http\Requests;

use Illuminate\Http\Request as HttpRequest;

class ServiceRequest
{
    /**
     * Http Request Instance.
     *
     * @var HttpRequest|null
     */
    private $httpRequest = null;

    /**
     * construct.
     *
     * @param HttpRequest $request
     */
    public function __construct(HttpRequest $request)
    {
        $this->httpRequest = $request;
    }
}
