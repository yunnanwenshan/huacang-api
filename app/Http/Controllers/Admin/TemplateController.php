<?php

namespace App\Http\Controllers\Admin;

use App\Components\Paginator;
use App\Models\Template;
use App\Services\Admin\Template\Contract\TemplateInterface;
use Illuminate\Http\Request;
use Exception;

class TemplateController extends Controller
{
    /**
     * 构造函数，
     *
     * @param Request           $request  [description]
     *
     * @return not [description]
     */
    public function __construct(TemplateInterface $templateService, Request $request)
    {
        parent::__construct($request);
        $this->templateService = $templateService;
    }

    /**
     * 模版增加
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function addTemplate(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'class_name' => 'required|string',
            'form_list' => 'required|array',
        ]);

        $name = $request->input('name');
        $className = $request->input('class_name');
        $formList = $request->input('form_list');

        try {
            $this->templateService->addTemplate($this->user, $name, $className, $formList);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess();

    }

    /**
     * 模版列表
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function templateList(Request $request)
    {
        $this->validate($request, [
            'page_index' => 'required|numeric',
            'page_size' => 'required|numeric',
            'start_time' => 'required|string',
            'end_time' => 'required|string'
        ]);

        $startTime = $request->input('start_time');
        $endTime = $request->input('ent_time');

        try {
            $paginator = new Paginator($request);
            $result = $this->templateService->templateList($this->user, $paginator, $startTime, $endTime);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess(['template_list' => $result]);
    }

    /**
     * 模版名称列表
     */
    public function nameList(Request $request)
    {
        try {
            $templates = Template::where('user_id', $this->user->id)->select('id', 'template_name')->get();
            $result = $templates->map(function ($item) {
                $e['template_id'] = $item->id;
                $e['name'] = $item->template_name;
                return $e;
            });
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess(['template_list' => $result]);
    }
}