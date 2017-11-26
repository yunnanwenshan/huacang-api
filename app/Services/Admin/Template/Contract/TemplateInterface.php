<?php
namespace App\Services\Admin\Template\Contract;


interface TemplateInterface
{
    /**
     * 增加模版
     */
    public function addTemplate(&$user, $name, $className, $formList);

    /**
     * 增加模版
     */
    public function templateList(&$user, $paginator, $startTime, $endTime);
}