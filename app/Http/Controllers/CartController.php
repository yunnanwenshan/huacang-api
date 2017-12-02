<?php

namespace App\Http\Controllers;

use App\Exceptions\Cart\CartException;
use App\Services\Cart\Contract\CartInterface;
use App\Services\Product\Contract\ProductInterface;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Collection;
use Validator;
use Log;

class CartController extends Controller
{
    /**
     * 构造函数
     * @param Request           $request  [description]
     *
     * @return not [description]
     */
    public function __construct(CartInterface $cartService, ProductInterface $productService, Request $request)
    {
        parent::__construct($request);
        $this->cartService = $cartService;
        $this->productService = $productService;
    }

    /**
     * 购物车详情，包括购物车内的商品详情
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function cartDetail(Request $request)
    {
        try {
            $result = $this->cartService->cartInfo($this->user);
            $productIds = array_pluck($result, 'product_id');
            $productList = $this->productService->productListDetail($productIds);
            $productListCollection = new Collection($productList);
            $rs = array_map(function ($item) use ($productListCollection) {
                $product = $productListCollection->where('product_id', $item['product_id'])->first();
                $item['name'] = $product['name'];
                return array_merge($item, $product);
            }, $result);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess(["cart_list" => $rs]);
    }

    /**
     * 增加一个新商品类型到购物车
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function addProductToCart(Request $request)
    {
        $this->validate($request, [
            'product_list' => 'required|array',
        ]);

        $productList = $request->input('product_list');

        try {
            foreach ($productList as $item) {
                $v = Validator::make($item, [
                    'product_id' => 'required|numeric',
                    'count' => 'required|numeric',
                ]);

                if ($v->fails()) {
                    Log::info(__FILE__.'('.__LINE__.'), product list validate fail', [
                        'location' => $v->messages()->toJson(JSON_UNESCAPED_SLASHES),
                    ]);
                    throw new CartException(CartException::CART_REMOVE_PARAMS_INVALID, CartException::DEFAULT_CODE + 1);
                }
            }
            $result = $this->cartService->addProductToCart($this->user, $productList);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess($result);
    }

    /**
     * 删除购物车中的商品项
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function delProductFromCart(Request $request)
    {
        $this->validate($request, [
            'cart_list' => 'required|array',
        ]);

        $cartList = $request->input('cart_list', 0);
        $user = $this->user;

        try {
            $result = $this->cartService->removeProductFromCart($user, $cartList);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess($result);
    }

    /**
     * 增加单件商品数量
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
//    public function incrProductToCart(Request $request)
//    {
//        $this->validate($request, [
//            'product_id' => 'required|numeric',
//            'count' => 'required|numeric',
//        ]);
//
//        $user = $this->user;
//        $productId = $request->input('product_id', 0);
//        $count = $request->input('count', 0);
//
//        try {
//            $result = '';
//        } catch (Exception $e) {
//            return response()->clientFail($e->getCode(), $e->getMessage());
//        }
//
//        return response()->clientSuccess($result);
//    }

    /**
     * 清空购物车
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
//    public function clearCart(Request $request)
//    {
//        $user = $this->user;
//
//        try {
//            $result = '';
//        } catch (Exception $e) {
//            return response()->clientFail($e->getCode(), $e->getMessage());
//        }
//
//        return response()->clientSuccess($result);
//    }
}