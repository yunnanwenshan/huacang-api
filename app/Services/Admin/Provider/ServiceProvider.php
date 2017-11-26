<?php

namespace App\Services\Admin\Provider;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        //模版服务
        $this->app->bind('App\Services\Admin\Template\Contract\TemplateInterface', 'App\Services\Admin\Template\TemplateService');

        //后台管理订单服务
        $this->app->bind('App\Services\Admin\Order\Contract\AdminOrderInterface', 'App\Services\Admin\Order\AdminOrderService');

        //后台产品管理服务
        $this->app->bind('App\Services\Admin\Product\Contract\AdminProductInterface', 'App\Services\Admin\Product\AdminProductService');

        //后台分类服务
        $this->app->bind('App\Services\Admin\ConfigClass\Contract\AdminClassInterface', 'App\Services\Admin\ConfigClass\ClassService');
    }
}
