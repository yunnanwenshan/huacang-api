<?php
namespace App\Services\Order\Contract;


use App\Components\Paginator;

interface OrderInterface
{
    /**
     * 创建订单
     */
    public function create(&$user, $shareId, array $productList);

    /**
     * 申请取消订单
     */
    public function requestCancel(&$user, $orderId, $remark);

    /**
     * 订单详情
     */
    public function detail(&$user, $orderSn);

    /**
     * 订单列表
     */
    public function orderList(&$user, Paginator $paginator);
}