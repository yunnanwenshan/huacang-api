<?php

namespace App\Services\Cart\Contract;

interface CartInterface
{
    /**
     * 创建或者查找一个购物车
     */
    public function cartInfo(&$user);

    /**
     * 将商品添加到购物车当中
     */
    public function addProductToCart(&$user, array $item);

    /**
     * 更新商品的数目
     * @param $item_id
     * @param $qty
     * @return mixed
     */
    public function updateProduct(&$user, $productId, $amount);

    /**
     * 删除购物车中商品项目
     * @param $item_id
     * @return bool
     */
    public function removeProductFromCart(&$user, array $itemIds);
}