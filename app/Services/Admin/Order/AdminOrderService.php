<?php

namespace App\Services\Admin\Order;


use App\Components\Paginator;
use App\Exceptions\Admin\AdminOrderException\AdminOrderException;
use App\Models\Order;
use App\Services\Admin\Order\Contract\AdminOrderInterface;
use Carbon\Carbon;
use Log;

class AdminOrderService implements AdminOrderInterface
{
    /**
     * 更新订单
     */
    public function updateOrder(&$user, $orderId, $status, $remark)
    {
        Log::Info(__FILE__ . '(' . __LINE__ . '), update order start, ', [
            'user_id' => $user->id,
            'order_id' => $orderId,
            'status' => $status,
            'remark' => $remark,
        ]);

        $order = Order::where('user_id', $user->id)
            ->where('id', $orderId)
            ->first();
        if (empty($order)) {
            Log::info(__FILE__ . '(' . __LINE__ . '), order is null, ', [
                'order_id' => $orderId,
                'user_id' => $user->id,
                'status' => $status,
            ]);
            throw new AdminOrderException(AdminOrderException::ORDER_NOT_EXIST, AdminOrderException::DEFAULT_CODE + 1);
        }

        //检查订单状态
        if (!in_array($order->status, [Order::STATUS_INIT, Order::STATUS_PAY])) {
            Log::info(__FILE__ . '(' . __LINE__ . '), order status ', [
                'order_id' => $orderId,
                'user_id' => $user->id,
                'status' => $status,
            ]);
            throw new AdminOrderException(AdminOrderException::ORDER_NO_CANCEL, AdminOrderException::DEFAULT_CODE + 2);
        }

        $order->status = $status;
        $order->save();

        Log::info(__FILE__ . '(' . __LINE__ . '), update order successful, ', [
            'user_id' => $user->id,
            'order_id' => $orderId,
            'status' => $status,
            'remark' => $remark,
        ]);
    }

    /**
     * 更新订单
     */
    public function orderList(&$user, Paginator $paginator, $startTime, $endTime, $status)
    {
        if ($status == 0) {
            $orders = Order::where('user_id', $user->id)
                ->where('start_time', (new Carbon($startTime))->format('Y-m-d H:i:s'))
                ->where('start_time', (new Carbon($endTime))->format('Y-m-d H:i:s'))
                ->orderBy('start_time', 'desc');
        } else {
            $orders = Order::where('user_id', $user->id)
                ->where('start_time', (new Carbon($startTime))->format('Y-m-d H:i:s'))
                ->where('start_time', (new Carbon($endTime))->format('Y-m-d H:i:s'))
                ->where('status', $status)
                ->orderBy('start_time', 'desc');
        }

        $ordercollection = $paginator->query($orders);
        $rs = $ordercollection->map(function ($item){
            return $item->export();
        });

        Log::info(__FILE__ . '(' . __LINE__ . '), admin order list, ', [
            'user_id' => $user->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $status,
        ]);

        return $rs;
    }
}