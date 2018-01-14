<?php

namespace App\Services\Admin\PriceLog;


use App\Models\PriceOpLog;
use App\Services\Admin\PriceLog\Contract\PriceLogInterface;

class PriceLogService implements PriceLogInterface
{
    /**
     * 储存价格变化信息
     */
    public function recordPriceLog(
        $productId, $userProductId, $shareId, $strategy, $orderSn,
        $userId, $priceType, $sourcePrice, $price,
        $expireTime, $remark, $type, $operator)
    {
        $priceLog = new PriceOpLog();
        $priceLog->product_id = $productId;
        $priceLog->user_product_id = $userProductId;
        $priceLog->share_id = $shareId;
        $priceLog->strategy = $strategy;
        $priceLog->order_sn = $orderSn;
        $priceLog->user_id = $userId;
        $priceLog->price_type = $priceType;
        $priceLog->source_price = $sourcePrice;
        $priceLog->price = $price;
        $priceLog->expire_time = $expireTime;
        $priceLog->remark = $remark;
        $priceLog->type = $type;
        $priceLog->operator = $operator;
        $priceLog->save();
    }
}