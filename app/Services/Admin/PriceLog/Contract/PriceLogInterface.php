<?php
namespace App\Services\Admin\PriceLog\Contract;

interface PriceLogInterface
{
    /**
     * 储存价格变化信息
     */
    public function recordPriceLog(
        $productId, $userProductId, $shareId, $strategy, $orderSn,
        $userId, $priceType, $sourcePrice, $price,
        $expireTime, $remark, $type, $operator);
}