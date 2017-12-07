<?php
namespace App\Services\Order;

use App\Components\GenerateID;
use App\Components\Paginator;
use App\Exceptions\Order\OrderException;
use App\Models\Order;
use App\Models\Product;
use App\Models\Share;
use App\Models\ShareDetail;
use App\Models\UserProduct;
use App\Services\Cart\Contract\CartInterface;
use App\Services\Order\Contract\OrderInterface;
use Carbon\Carbon;
use Log;
use DB;

class OrderService implements OrderInterface
{
    /**
     * 构造函数
     */
    public function __construct(CartInterface $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * 直接创建订单
     */
    public function createDirect(&$user, $shareId, array $productList, $totalFee)
    {
        //1. 检查提交上来的产品是否为有效产品, 无效报错
        $count = count($productList);
        if ($count <= 0) {
            throw new OrderException(OrderException::ORDER_NULL, OrderException::DEFAULT_CODE + 6);
        }

        //TODO: 1.1 检查shareid是否有效
        //TODO: 检查订单是否来做购物车
        $share = Share::where('id', $shareId)->first();
        if (empty($share)) {
            throw new OrderException(OrderException::ORDER_MARKET_NOT_EXIST, OrderException::DEFAULT_CODE + 13);
        }
        $userProductIds = array_pluck($productList, 'user_product_id');
        $userProducts = UserProduct::whereIn('id', $userProductIds)->where('status', UserProduct::STATUS_ONLINE)->get();
        if ($count != $userProducts->count()) {
            Log::info(__FILE__ . '(' . __LINE__ . '), there is a little the product offlining, ', [
                'user_id' => $user->id,
                'share_id' => $shareId,
                'product_list' => $productList,
                'user_products_count' => $userProducts->count(),
                'count' => $count,
            ]);
            throw new OrderException(OrderException::ORDER_PRODUCT_OFFLINE, OrderException::DEFAULT_CODE + 7);
        }
        $productIds = $userProducts->pluck('product_id')->toArray();
        $shareDetailList = ShareDetail::where('share_id', $share->id)
            ->whereIn('product_id', $productIds)
            ->get();
        $shareCount = $shareDetailList->count();
        if ($count != $shareCount) {
            Log::info(__FILE__ . '(' . __LINE__ . '), share detail not equal to productList, ', [
                'user_id' => $user->id,
                'share_id' => $shareId,
                'share_count' => $shareCount,
                'count' => $count,
            ]);
            throw new OrderException(OrderException::ORDER_MARKET_NOT_GOOD, OrderException::DEFAULT_CODE + 14);
        }

        //计算订单总价
        $realTotalFee = 0;
        $detail = array();
        foreach ($productList as $item) {
            $product = $userProducts->where('id', $item['user_product_id'])->first();
            //较验库存是否足够
            if (($item['count'] != 0) && ($item['count'] > $product->stock_num)) {
                Log::info(__FILE__ . '(' . __LINE__ . '), product stock_num insufficient, ', [
                    'user_id' => $user->id,
                    'share_id' => $shareId,
                    'product_list' => $productList,
                    'product' => $product,
                    'item' => $item,
                ]);
                throw new OrderException(OrderException::ORDER_PRODUCT_STOCK_INSUFFICIENT, OrderException::DEFAULT_CODE + 8);
            }
            $realTotalFee = $realTotalFee + $product->selling_price * $item['count'];
            $item['price'] = $product->selling_price;
            $item['fee'] = $product->selling_price * $item['count'];
            $detail[] = $item;
        }

        if ($realTotalFee != $totalFee) {
            Log::info(__FILE__ . '(' . __LINE__ . '), total fee no equal, ', [
                'real_total_fee' => $realTotalFee,
                'total_fee' => $totalFee,
            ]);
            throw new OrderException(OrderException::ORDER_TOTAL_FEE_NO_EQUAL, OrderException::DEFAULT_CODE + 15);
        }

        try {
            DB::begintransaction();

            //2.扣减库存, TODO:多用户并发扣减库存可能会失败
            foreach ($productList as $item) {
                $product = $userProducts->where('id', $item['user_product_id'])->first();
                if ($item['count'] != 0) {
                    $affectRow = UserProduct::where('id', $product->id)
                        ->where('stock_num', $product->stock_num)
                        ->where('selled_num', $product->selled_num)
                        ->update([
                            'stock_num' => $product->stock_num - $item['count'],
                            'selled_num' => $product->selled_num + $item['count']
                        ]);
                    if ($affectRow == 0) {
                        Log::info(__FILE__ . '(' . __LINE__ . '), sub stock_num fail, ', [
                            'user_id' => $user->id,
                            'share_id' => $shareId,
                            'product_list' => $productList,
                            'product' => $product,
                            'item' => $item,
                        ]);
                        throw new OrderException(OrderException::ORDER_PRODUCT_STOCK_FAIL, OrderException::DEFAULT_CODE + 9);
                    }
                }
            }

            //4.订单创建
            $generator = new GenerateID($user->id);
            $order = new Order();
            $order->user_id = $user->id;
            $order->sn = $generator->genID();
            $order->share_id = $shareId;
            $order->total_fee = $totalFee;
            $order->status = Order::STATUS_INIT;
            $order->order_detail = json_encode($detail);
            $order->start_time = Carbon::now();
            $order->save();
            DB::commit();

            Log::info(__FILE__ . '(' . __LINE__ . '), create order successful, ', [
                'user_id' => $user->id,
                'share_id' => $shareId,
                'total_fee' => $totalFee,
                'product_list' => $productList,
            ]);

            return [
                'order_sn' => $order->sn,
            ];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error(__FILE__ . '(' . __LINE__ . '), create order fail, ', [
                'user_id' => $user->id,
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ]);
            throw new OrderException(OrderException::ORDER_CREATE_FAIL, OrderException::DEFAULT_CODE + 5);
        }
    }

    /**
     * 创建订单
     */
    public function create(&$user, $shareId, array $productList)
    {
        //1. 检查提交上来的产品是否为有效产品, 无效报错
        $count = count($productList);
        if ($count <= 0) {
            throw new OrderException(OrderException::ORDER_NULL, OrderException::DEFAULT_CODE + 6);
        }

        //TODO: 1.1 检查shareid是否有效
        //TODO: 检查订单是否来做购物车
        $share = Share::where('id', $shareId)->first();
        if (empty($share)) {
            throw new OrderException(OrderException::ORDER_MARKET_NOT_EXIST, OrderException::DEFAULT_CODE + 13);
        }

        $productIds = array_pluck($productList, 'product_id');
        $userProducts = UserProduct::where('product_id', $productIds)
            ->where('status', UserProduct::STATUS_ONLINE)
            ->get();
        $productCount = $userProducts->count();
        if ($count != $productCount) {
            Log::info(__FILE__ . '(' . __LINE__ . '), there is a little the product offlining, ', [
                'user_id' => $user->id,
                'share_id' => $shareId,
                'product_list' => $productList,
            ]);
            throw new OrderException(OrderException::ORDER_PRODUCT_OFFLINE, OrderException::DEFAULT_CODE + 7);
        }

        //计算订单总价
        $totalFee = 0;
        $detail = array();
        foreach ($productList as $item) {
            $product = $userProducts->where('product_id', $item['product_id'])->first();
            //较验库存是否足够
            if ($item['count'] > $product->stock_num) {
                Log::info(__FILE__ . '(' . __LINE__ . '), product stock_num insufficient, ', [
                    'user_id' => $user->id,
                    'share_id' => $shareId,
                    'product_list' => $productList,
                    'product' => $product,
                    'item' => $item,
                ]);
                throw new OrderException(OrderException::ORDER_PRODUCT_STOCK_INSUFFICIENT, OrderException::DEFAULT_CODE + 8);
            }
            $totalFee = $totalFee + $product->selling_price * $item['count'];
            $item['price'] = $product->selling_price;
            $item['fee'] = $product->selling_price * $item['count'];
            $detail[] = $item;
        }

        try {
            DB::begintransaction();

            //2.扣减库存, TODO:多用户并发扣减库存可能会失败
            foreach ($productList as $item) {
                $product = $userProducts->where('product_id', $item['product_id'])->first();
                $affectRow = UserProduct::where('product_id', $product->product_id)
                    ->where('stock_num', $product->stock_num)
                    ->where('selled_num', $product->selled_num)
                    ->update([
                        'stock_num' => $product->stock_num - $item['count'],
                        'selled_num' => $product->selled_num + $item['count']
                    ]);
                if ($affectRow == 0) {
                    Log::info(__FILE__ . '(' . __LINE__ . '), sub stock_num fail, ', [
                        'user_id' => $user->id,
                        'share_id' => $shareId,
                        'product_list' => $productList,
                        'product' => $product,
                        'item' => $item,
                    ]);
                    throw new OrderException(OrderException::ORDER_PRODUCT_STOCK_FAIL, OrderException::DEFAULT_CODE + 9);
                }
            }

            //3.将用户需要购买的商品从购物车中清除
            $cartItemdIds = array_pluck($productList, 'cart_id');
            $this->cartService->removeProductFromCart($user, $cartItemdIds);

            //4.订单创建
            $generator = new GenerateID($user->id);
            $order = new Order();
            $order->user_id = $user->id;
            $order->sn = $generator->genID();
            $order->share_id = $shareId;
            $order->total_fee = $totalFee;
            $order->status = Order::STATUS_INIT;
            $order->order_detail = json_encode($detail);
            $order->start_time = Carbon::now();
            $order->save();
            DB::commit();

            Log::info(__FILE__ . '(' . __LINE__ . '), create order successful, ', [
                'user_id' => $user->id,
                'share_id' => $shareId,
                'total_fee' => $totalFee,
                'product_list' => $productList,
            ]);

             return [
                 'order_sn' => $order->sn,
             ];
        } catch (\Exception $e) {
            DB::rollback();
            Log::error(__FILE__ . '(' . __LINE__ . '), create order fail, ', [
                'user_id' => $user->id,
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ]);
            throw new OrderException(OrderException::ORDER_CREATE_FAIL, OrderException::DEFAULT_CODE + 5);
        }
    }

    /**
     * 申请取消订单
     */
    public function requestCancel(&$user, $orderSn, $remark)
    {
        $order = Order::where('user_id', $user->id)
            ->where('sn', $orderSn)
            ->first();
        if (empty($order)) {
            Log::error(__FILE__ . '(' . __LINE__ . '), order is null, ', [
                'user_id' => $user->id,
                'order_id' => $orderSn,
                'remard' => $remark,
            ]);
            throw new OrderException(OrderException::ORDER_NOT_EXIST, OrderException::DEFAULT_CODE + 3);
        }

        //订单已被取消
        if ($order->status == Order::STATUS_CANCELED) {
            throw new OrderException(OrderException::ORDER_CANCEL, OrderException::DEFAULT_CODE + 10);
        }

        //订单已完成
        if ($order->status == Order::STATUS_FINISHED) {
            throw new OrderException(OrderException::ORDER_FINISHED, OrderException::DEFAULT_CODE + 11);
        }

        $affectRow = Order::where('user_id', $user->id)
            ->where('sn', $orderSn)
            ->whereIn('status', [Order::STATUS_INIT, Order::STATUS_PAY, Order::STATUS_SEND_PRODUCT, Order::STATUS_SENDED_PRODUCT, Order::STATUS_AUDIT, Order::STATUS_REFUND])
            ->update(['remark' => $remark, 'status' => Order::STATUS_CLIENT_REQUEST_CANCEL]);

        if ($affectRow == 0) {
            Log::info(__FILE__ . '(' . __LINE__ . '), request cancel successful, ', [
                'user_id' => $user->id,
                'order_id' => $orderSn,
                'status' => $order->status,
            ]);
            throw new OrderException(OrderException::ORDER_NOT_ALLOWED_CANCEL, OrderException::DEFAULT_CODE + 12);
        }

        Log::info(__FILE__ . '(' . __LINE__ . '), request cancel successful, ', [
            'user_id' => $user->id,
            'order_id' => $orderSn,
            'affect_row' => $affectRow,
        ]);
    }

    /**
     * 订单详情
     */
    public function detail(&$user, $orderSn)
    {
        $order = Order::where('user_id', $user->id)
            ->where('sn', $orderSn)
            ->first();
        if (empty($order)) {
            Log::error(__FILE__ . '(' . __LINE__ . '), order is null, ', [
                'user_id' => $user->id,
                'order_id' => $orderSn,
            ]);
            throw new OrderException(OrderException::ORDER_NOT_EXIST, OrderException::DEFAULT_CODE + 4);
        }

        $rs = $order->export();
        Log::Info(__FILE__ . '(' . __LINE__  .'), order detail, ', [
            'user_id' => $user->id,
            'order_id' => $orderSn,
            'rs' => $rs,
        ]);

        return $order->export();
    }

    /**
     * 订单列表
     */
    public function orderList(&$user, Paginator $paginator)
    {
        $orders = Order::where('user_id', $user->id);
        $orderCollection = $paginator->query($orders);
        $productDetail = $orderCollection->pluck('order_detail')->toArray();
        $userProductIds = [];
        foreach ($productDetail as $pd) {
            $jsonPd = json_decode($pd, true);
            $userProductIds = array_merge($userProductIds, array_pluck($jsonPd, 'user_product_id'));
        }

        $userProducts = UserProduct::whereIn('id', $userProductIds)
            ->select('id', 'product_id')
            ->get();
        $userProductIds = $userProducts->pluck('product_id')->toArray();
        $products = Product::whereIn('id', $userProductIds)
            ->select('id', 'name', 'main_img', 'sub_img')
            ->get();

        //返回结果
        $rs = [];
        foreach ($orderCollection as $order) {
            $product = json_decode($order->order_detail, true);
            if (json_last_error()) {
                continue;
            }
            $mainImgs = [];
            foreach ($product as $pr) {
                $pd = $userProducts->where('id', $pr['user_product_id'])->first();
                if (empty($pd)) {
                    continue;
                }
                $prd = $products->where('id', $pd->product_id)->first();
                $mainImgs[] = empty($prd) ? "" : $prd['main_img'];
            }
            $e = $order->export();
            $e['product_list'] = $mainImgs;
            $rs[] = $e;
        }

        Log::info(__FILE__ . '(' . __LINE__ . '), order list, ', [
            'user_id' => $user->id,
        ]);

        return $rs;
    }
}
