<?php

namespace App\Services\Admin\Template;

use App\Exceptions\Admin\Template\TemplateException;
use App\Models\ProductClass;
use App\Models\Template;
use App\Models\TemplateFormItem;
use App\Services\Admin\Template\Contract\TemplateInterface;
use Carbon\Carbon;
use Exception;
use DB;
use Log;

class TemplateService implements TemplateInterface
{
    /**
     * 增加模版
     */
    public function addTemplate(&$user, $name, $className, $formList)
    {
        try {
            DB::begintransaction();

            //增加分类
            $class = new ProductClass();
            $class->name = $className;
            $class->parent_id = 0;
            $class->type = ProductClass::TYPE_TEMPLATE;
            $class->user_id = $user->id;
            $class->save();

            //增加模版信息
            $template = new Template();
            $template->template_name = $name;
            $template->user_id = $user->id;
            $template->class_id = $class->id;
            $template->status = Template::STATUS_ENABLE;
            $template->save();

            //模版详情
            $templateFormList = new TemplateFormItem();
            $templateFormList->template_id = $template->id;
            $templateFormList->form_name = $name;
            $templateFormList->form_content = json_encode($formList);
            $templateFormList->save();

            Log::Info(__FILE__ . '(' . __LINE__ . '), add template successful, ', [
                'user_id' => $user->id,
                'name' => $name,
                'class_name' => $className,
                'form_list' => $formList,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error(__FILE__ . '(' . __LINE__ . '), add template, ', [
                'user_id' => $user->id,
                'name' => $name,
                'class_name' => $className,
                'form_list' => $formList,
            ]);
            throw new TemplateException(TemplateException::TEMPLATE_ADD_FAIL, TemplateException::DEFAULT_CODE + 1);
        }
    }

    /**
     * 增加模版
     */
    public function templateList(&$user, $paginator, $startTime, $endTime)
    {
        $templates = Template::where('template.user_id', $user->id)
            ->join('class', 'template.class_id', '=', 'class.id')
            ->join('template_form_item', 'template.id', '=', 'template_form_item.template_id')
            ->where('template.create_time', '>=', (new Carbon($startTime))->format('Y-m-d H:i:s'))
            ->where('template.create_time', '<=', (new Carbon($endTime))->format('Y-m-d H:i:s'))
            ->where('template.status', Template::STATUS_ENABLE)
            ->select('template.user_id', 'template.template_name', 'class.name as class_name', 'template.update_time', 'template_form_item.form_content')
            ->get();

        if (empty($templates)) {
            return [];
        }

        $rs = $templates->map(function ($item) {
            $e['name'] = $item->template_name;
            $e['class_name'] = $item->class_name;
            $e['update_time'] = (new Carbon($item->update_time))->format('Y-m-d H:i:s');
            $e['form_list'] = $item->form_content;
            return $e;
        });

        Log::info(__FILE__ . '(' . __LINE__ . '), temlate list, ', [
            'user_id' => $user->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'rs' => $rs,
        ]);

        return $rs;
    }
}