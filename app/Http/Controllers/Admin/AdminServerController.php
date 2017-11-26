<?php

namespace App\Http\Controllers\Admin;

use App;
use Cache;
use ClientRequest;
use Illuminate\Http\Request;
use Qiniu\Auth as QiniuAuth;
use Log;

class AdminServerController extends Controller
{
    private $qiniu = [
        // 七牛帐号：117994665@qq.com的ak、sk信息
        'accessKey' => 'otrfL-tLJdxxRd-qLnDYvYBhhle2laUoLHtVN0A5',
        'secretKey' => 'E3mGEwrHniH96WABzd5jy-q40eSqBYyU05OIYbkp',

        // 头像信息空间
        'media_bucket' => ' media-huacang',
    ];

    const ERROR_CODE = 12000;

    /**
     * 获取七牛上传的key.
     *
     * @param Request $request [description]
     *
     * @return Response
     */
    public function getUploadKey(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|in:1', // 1: image upload
        ]);

        $input = $request->input();
        $type = $input['type'];

        $auth = new QiniuAuth($this->qiniu['accessKey'], $this->qiniu['secretKey']);

        switch ($type) {
            case 1:
                // 头像信息空间
                $bucket = $this->qiniu['media_bucket'];
                break;
            default:
                return response()->clientError(self::ERROR_CODE + 1, '错误');
                break;
        }

        // 生成上传 Token
        $token = $auth->uploadToken($bucket);

        $result = [
            'upload_key' => $token,
            'provider' => 'qiniu',
        ];

        return response()->clientSuccess($result);
    }
}
