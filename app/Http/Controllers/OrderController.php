<?php

namespace App\Http\Controllers;

use App\Components\Paginator;
use App\Exceptions\Order\OrderException;
use App\Services\Order\Contract\OrderInterface;
use Illuminate\Http\Request;
use Exception;
use Validator;

class OrderController extends Controller
{
    /**
     * 构造函数
     * @param Request           $request  [description]
     *
     * @return not [description]
     */
    public function __construct(OrderInterface $orderService, Request $request)
    {
        parent::__construct($request);
        $this->orderService = $orderService;
    }

    /**
     * 创建订单
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'share_id' => 'required|numeric|min:1',
//            'supplier_id' => 'required|numeric|min:1',
//            'total_fee' => 'required|numeric|min:1',
            'product_list' => 'required|array',
        ]);

        $shareId = $request->input('share_id');
//        $supplierId = $request->input('supplier_id');
//        $totalFee = $request->input('total_fee');
        $productList = $request->input('product_list');
        try {
            foreach ($productList as $item) {
                $v = Validator::make($item, [
                    'cart_id' => 'required|numeric|min:1',
                    'product_id' => 'required|numeric|min:1',
                    'count' => 'required|numeric|min:1',
//                    'price' => 'required|numeric|min:1',
//                    'fee' => 'required|numeric|min:1',
                ]);

                if ($v->fails()) {
                    Log::info(__FILE__.'('.__LINE__.'), product list validate fail', [
                        'location' => $v->messages()->toJson(JSON_UNESCAPED_SLASHES),
                    ]);
                    throw new OrderException(OrderException::ORDER_PARAM_FAIL, OrderException::DEFAULT_CODE + 1);
                }
            }
            $result = $this->orderService->create($this->user, $shareId, $productList);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess($result);
    }

    /**
     * 订单详情
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function detail(Request $request)
    {
        $this->validate($request, [
            'order_id' => 'required|numeric'
        ]);
        $orderId = $request->input('order_id');
        try {
            $result = $this->orderService->detail($this->user, $orderId);
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
        ]);

        try {
            $paginator = new Paginator($request);
            $result = $this->orderService->orderList($this->user, $paginator);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess(['order_list' => $result]);
    }

    /**
     * 申请取消订单
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function requestCancel(Request $request)
    {
        $this->validate($request, [
            'order_sn' => 'required|string',
        ]);

        $orderSn = $request->input('order_sn');
        $remark = $request->input('remark', '');
        try {
            $result = $this->orderService->requestCancel($this->user, $orderSn, $remark);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess($result);
    }
}