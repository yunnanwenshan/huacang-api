<?php

namespace App\Services\Admin\Order;


use App\Components\Paginator;
use App\Exceptions\Admin\AdminOrderException\AdminOrderException;
use App\Models\Order;
use App\Models\User;
use App\Services\Admin\Order\Contract\AdminOrderInterface;
use Carbon\Carbon;
use Log;
use DB;

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
     * 订单列表
     */
    public function orderList(&$user, Paginator $paginator, $startTime, $endTime, $status)
    {
        $sql = 'select * from orders o where o.user_id = ?';
        if (!empty($startTime)) {
            $sql = $sql . ' and o.start_time >= \'' . (new Carbon($startTime))->format('Y-m-d H:i:s') . '\'';
        }
        if (!empty($endTime)) {
            $sql = $sql . ' and o.ent_time <= \'' . (new Carbon($endTime))->format('Y-m-d H:i:s') . '\'';
        }
        if (!empty($status)) {
            $sql = $sql . ' and o.status = ' . $status;
        }
        $sql = $sql . ' order by o.start_time desc';
        $orders = DB::select($sql, [$user->id]);
        $ordercollection = $paginator->queryArray($orders);
        $userList = User::where('id', $ordercollection->pluck('user_id')->toArray())->get();
        $rs = $ordercollection->map(function ($item) use($userList) {
            $user = $userList->where('id', $item->user_id)->first();
            $e['order_sn'] = $item->sn;
            $e['user_id'] = $user->id;
            $e['user_name'] = $user->user_name;
            $e['user_phone'] = $user->mobile;
            $e['share_sn'] = $item->share_id;
            $e['total_fee'] = $item->total_fee;
            $e['status'] = $item->status;
            $e['star_time'] = $item->start_time;
            $e['end_time'] = $item->end_time;
            $e['remark'] = $item->remark;
            $e['product_list'] = json_decode($item->order_detail, true);
            return $e;
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