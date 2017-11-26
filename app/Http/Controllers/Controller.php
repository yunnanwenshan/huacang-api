<?php

namespace App\Http\Controllers;

use App\Facades\ClientRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Validator;
use Log;

abstract class Controller extends BaseController
{
    /**
     * 用户clientInfo信息.
     */
    protected $info;

    /**
     * 用户.
     */
    protected $user;

    use DispatchesJobs, ValidatesRequests, AuthorizesRequests;

    public function __construct()
    {
        if (empty(ClientRequest::getRequest())) {
            ClientRequest::setRequest(app('request'));
        }
        $this->info = ClientRequest::getInfo();
        $this->user = ClientRequest::getUser();
    }

    /**
     * 验证输入的参数.
     *
     * @param Request $request
     * @param array   $rules
     * @param array   $messages
     * @param array   $customAttributes
     */
    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = Validator::make($request->all(), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            throw new HttpException(403, $validator->messages()->toJson(JSON_UNESCAPED_SLASHES));
        }
    }
}
