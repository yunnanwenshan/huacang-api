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
    public function updateOrder(&$user, $orderSn, $status, $remark)
    {
        Log::Info(__FILE__ . '(' . __LINE__ . '), update order start, ', [
            'user_id' => $user->id,
            'order_id' => $orderSn,
            'status' => $status,
            'remark' => $remark,
        ]);

        $order = Order::where('user_id', $user->id)
            ->where('sn', $orderSn)
            ->first();
        if (empty($order)) {
            Log::info(__FILE__ . '(' . __LINE__ . '), order is null, ', [
                'order_id' => $orderSn,
                'user_id' => $user->id,
                'status' => $status,
            ]);
            throw new AdminOrderException(AdminOrderException::ORDER_NOT_EXIST, AdminOrderException::DEFAULT_CODE + 1);
        }

        //订单已经被取消
        if (in_array($order->status, [Order::STATUS_CANCELED])) {
            Log::info(__FILE__ . '(' . __LINE__ . '), order canceled, ', [
                'user_id' => $user->id,
                'order_id' => $orderSn,
                'status' => $status,
                'remark' => $remark,
            ]);
            throw new AdminOrderException(AdminOrderException::ORDER_CANCELED, AdminOrderException::DEFAULT_CODE + 3);
        }

        //订单已经完成
        if ($order->status == Order::STATUS_FINISHED) {
            throw new AdminOrderException(AdminOrderException::ORDER_FINISHED, AdminOrderException::DEFAULT_CODE + 4);
        }

        //用户已经申请取消订单


        switch ($status) {
            case Order::STATUS_CANCELED:
                //检查订单状态
                if (!in_array($order->status, [Order::STATUS_INIT, Order::STATUS_PAY, Order::STATUS_CLIENT_REQUEST_CANCEL])) {
                    Log::info(__FILE__ . '(' . __LINE__ . '), order status ', [
                        'order_id' => $orderSn,
                        'user_id' => $user->id,
                        'status' => $status,
                    ]);
                    throw new AdminOrderException(AdminOrderException::ORDER_NO_CANCEL, AdminOrderException::DEFAULT_CODE + 2);
                }

                break;
            case Order::STATUS_FINISHED:
                if ($order->status == Order::STATUS_CLIENT_REQUEST_CANCEL) {
                    throw new AdminOrderException(AdminOrderException::ORDER_USER_REQUEST, AdminOrderException::DEFAULT_CODE + 7);
                }
                if (!in_array($order->status, [Order::STATUS_REFUND])) {
                    throw new AdminOrderException(AdminOrderException::ORDER_NO_FINISHED . Order::STATUS_TO_DESC[$order->status],
                        AdminOrderException::DEFAULT_CODE + 5);
                }
                break;
            default:
                Log::info(__FILE__ . '(' . __LINE__ . '), default update order, ', [
                    'user_id' => $user->id,
                    'order_id' => $orderSn,
                    'status' => $status,
                    'remark' => $remark,
                ]);
                throw new AdminOrderException(AdminOrderException::ORDER_ORTHER_OP, AdminOrderException::DEFAULT_CODE + 6);
        }

        $affectRow = Order::where('sn', $orderSn)
            ->where('user_id', $user->id)
            ->where('status', '!=', $status)
            ->update(['status' => $status]);

        Log::info(__FILE__ . '(' . __LINE__ . '), update order successful, ', [
            'user_id' => $user->id,
            'order_id' => $orderSn,
            'status' => $status,
            'remark' => $remark,
            'affectRow' => $affectRow,
        ]);

        return [
            'order_sn' => $orderSn,
        ];
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