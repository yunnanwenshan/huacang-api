<?php

namespace App\Services\Admin\Product\Contract;


use App\Components\Paginator;

interface AdminProductInterface
{
    /**
     * 增加产品
     */
    public function addProduct(&$user, array $productParam);

    /**
     * 更新产品
     */
    public function updateProduct(&$user, array $productParam);

    /**
     * 删除产品
     */
    public function delProduct(&$user, array $productParam);

    /**
     * 产品列表
     */
    public function productList(&$user, array $productParam, Paginator $paginator);

    /**
     * 产品详情
     */
    public function productDetail(&$user, $productId);

    /**
     * 单件产品上架
     */
    public function productSellingUp(&$user, $productId);

    /**
     * 单件产品下架
     */
    public function productSellingDown(&$user, $productId);

    /**
     * 批量产品下架
     */
    public function productSellingUpBatch(&$user, array $productIds);

    /**
     * 批量产品上架
     */
    public function productSellingDownBatch(&$user, array $productIds);

    /**
     * 创建商城
     */
    public function shareProduct(&$user, array $params);
}