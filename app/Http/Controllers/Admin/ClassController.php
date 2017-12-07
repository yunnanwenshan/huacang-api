<?php

namespace App\Http\Controllers\Admin;
use App\Services\Admin\ConfigClass\Contract\AdminClassInterface;
use Illuminate\Http\Request;
use Exception;
use Log;

class ClassController extends Controller
{
    /**
     * 构造函数，
     *
     * @param Request           $request  [description]
     *
     * @return not [description]
     */
    public function __construct(Request $request, AdminClassInterface $classService)
    {
        parent::__construct($request);
        $this->classService = $classService;
    }

    /**
     * 用户分类列表
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function classList(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|in:0,1' //type": 1, //0:产品分类 1 模板分类
        ]);

        $type = $request->input('type');

        try {
            $result = $this->classService->classList($this->user, $type);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess($result);
    }

    /**
     * 产品分类列表
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function brandsList(Request $request)
    {
        try {
            $result = $this->classService->brandsList($this->user);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess($result);
    }
}