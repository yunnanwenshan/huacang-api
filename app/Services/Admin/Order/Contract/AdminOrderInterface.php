<?php

namespace App\Services\Admin\Order\Contract;


use App\Components\Paginator;

interface AdminOrderInterface
{
    /**
     * 更新订单
     */
    public function updateOrder(&$user, $orderSn, $status, $remark);

    /**
     * 订单列表
     */
    public function orderList(&$user, Paginator $paginator, $startTime, $endTime, $status);
}