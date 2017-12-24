<?php
namespace App\Services\Admin\Template\Contract;


use App\Components\Paginator;

interface TemplateInterface
{
    /**
     * 增加模版
     */
    public function addTemplate(&$user, $name, $className, $formList);

    /**
     * 增加模版
     */
    public function templateList(&$user, Paginator $paginator, $startTime, $endTime, $name);
    /**
     * 模版更新
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function updateTemplate(&$user, $templateId, $name, $className, $formList);

    /**
     * 模版更新
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function deleteTemplate(&$user, $templateId);
}