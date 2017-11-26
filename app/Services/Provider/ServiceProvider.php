<?php

namespace App\Services\Provider;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        //订单服务
        $this->app->bind('App\Services\Order\Contract\OrderInterface', 'App\Services\Order\OrderService');

        //购物车
        $this->app->bind('App\Services\Cart\Contract\CartInterface', 'App\Services\Cart\CartService');

        //商品
        $this->app->bind('App\Services\Product\Contract\ProductInterface', 'App\Services\Product\ProductService');
    }
}
