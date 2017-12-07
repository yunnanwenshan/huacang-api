<?php
namespace App\Http\Controllers\Admin;

use App\Components\Paginator;
use App\Models\Order;
use App\Services\Admin\Order\Contract\AdminOrderInterface;
use Illuminate\Http\Request;
use Exception;
use Log;

class OrderController extends Controller
{
    /**
     * 构造函数，
     *
     * @param Request           $request  [description]
     *
     * @return not [description]
     */
    public function __construct(AdminOrderInterface $orderService, Request $request)
    {
        parent::__construct($request);
        $this->orderService = $orderService;
    }

    /**
     * 取消订单
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function Cancel(Request $request)
    {
        $this->validate($request, [
            'order_sn' => 'required|string',
            'remark' => 'sometimes|string',
        ]);

        $orderSn = $request->input('order_sn');
        $remark = $request->input('remark');

        try {
            $status = Order::STATUS_CANCELED;
            $result = $this->orderService->updateOrder($this->user, $orderSn, $status, $remark);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess($result);
    }

    /**
     * 订单完成
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function orderFinish(Request $request)
    {
        $this->validate($request, [
            'order_sn' => 'required|string',
            'remark' => 'sometimes|string',
        ]);

        $orderSn = $request->input('order_sn');
        $remark = $request->input('remark');

        try {
            $status = Order::STATUS_FINISHED;
            $result = $this->orderService->updateOrder($this->user, $orderSn, $status, $remark);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess($result);
    }

    /**
     * 订单列表
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function orderList(Request $request)
    {
        $this->validate($request, [
            'page_index' => 'required|numeric',
            'page_size' => 'required|numeric',
            'status' => 'sometimes|numeric|in:0,1,2,3,4,5,6,7,8',
            'start_time' => 'sometimes|string',
            'end_time' => 'sometimes|string',
        ]);

        $status = $request->input('status', null);
        $startTime = $request->input('start_time', null);
        $endTime = $request->input('end_time', null);

        try {
            $paginator = new Paginator($request);
            $result = $this->orderService->orderList($this->user, $paginator, $startTime, $endTime, $status);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess(['order_list' => $result, 'page' => $paginator->export()]);
    }
}