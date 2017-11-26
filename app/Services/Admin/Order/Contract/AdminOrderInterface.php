<?php

namespace App\Services\Admin\Order\Contract;


use App\Components\Paginator;

interface AdminOrderInterface
{
    /**
     * 更新订单
     */
    public function updateOrder(&$user, $orderId, $status, $remark);

    /**
     * 更新订单
     */
    public function orderList(&$user, Paginator $paginator, $startTime, $endTime, $status);
}