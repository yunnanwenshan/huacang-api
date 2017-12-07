<?php

namespace App\Services\Product\Contract;


use App\Components\Paginator;

interface ProductInterface
{
    /**
     * 获取商品列表
     */
    public function productList($shareId, Paginator $paginator);

    /**
     * 获取商品列表根据商品id列表
     */
    public function productListDetail(array $productIds);

    /**
     * 商品详情
     */
    public function productDetail($userProductId);
}