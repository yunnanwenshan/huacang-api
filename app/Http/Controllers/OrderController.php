<?php

namespace App\Http\Controllers;

use App\Components\Paginator;
use App\Exceptions\Order\OrderException;
use App\Services\Order\Contract\OrderInterface;
use Illuminate\Http\Request;
use Exception;
use Validator;
use Log;

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
     * 直接创建订单
     */
    public function createDirect(Request $request)
    {
        $this->validate($request, [
            'share_id' => 'required|numeric',
            'total_fee' => 'required|numeric',
            'product_list' => 'required|array',
        ]);

        $shareId = $request->input('share_id');
        $totalFee = $request->input('total_fee');
        $productList = $request->input('product_list');

        try {
            foreach ($productList as $item) {
                $v = Validator::make($item, [
                    'user_product_id' => 'required|numeric',
                    'count' => 'required|numeric',
//                    'price' => 'required|numeric',
//                    'fee' => 'required|numeric',
                ]);

                if ($v->fails()) {
                    Log::info(__FILE__.'('.__LINE__.'), product list validate fail', [
                        'location' => $v->messages()->toJson(JSON_UNESCAPED_SLASHES),
                    ]);
                    throw new OrderException(OrderException::ORDER_PARAM_FAIL, OrderException::DEFAULT_CODE + 1);
                }
            }
            $result = $this->orderService->createDirect($this->user, $shareId, $productList, $totalFee);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess($result);
    }

    /**
     * 跟进购物车创建订单
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'share_id' => 'required|numeric',
//            'supplier_id' => 'required|numeric',
//            'total_fee' => 'required|numeric',
            'product_list' => 'required|array',
        ]);

        $shareId = $request->input('share_id');
//        $supplierId = $request->input('supplier_id');
//        $totalFee = $request->input('total_fee');
        $productList = $request->input('product_list');
        try {
            foreach ($productList as $item) {
                $v = Validator::make($item, [
                    'cart_id' => 'required|numeric',
                    'user_product_id' => 'required|numeric',
                    'count' => 'required|numeric',
//                    'price' => 'required|numeric',
//                    'fee' => 'required|numeric',
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
            'order_sn' => 'required|string'
        ]);
        $orderSn = $request->input('order_sn');
        try {
            $result = $this->orderService->detail($this->user, $orderSn);
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