<?php

namespace App\Http\Controllers;

use App\Components\Paginator;
use App\Services\Product\Contract\ProductInterface;
use Illuminate\Http\Request;
use Exception;
use Log;

class ProductController extends Controller
{
    /**
     * 构造函数
     * @param Request           $request  [description]
     *
     * @return not [description]
     */
    public function __construct(ProductInterface $productService, Request $request)
    {
        parent::__construct($request);
        $this->productService = $productService;
    }

    /**
     * product/share/list 获取分享的产品列表(客户端)
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function productList(Request $request)
    {
        $this->validate($request, [
            'page_index' => 'required|numeric',
            'page_size' => 'required|numeric',
            'share_id' => 'required|numeric',
        ]);

        $shareId = $request->input('share_id');

        try {
            $paginator = new Paginator($request);
            $result = $this->productService->productList($shareId, $paginator);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess($result);
    }

    /**
     * 产品详情
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function productDetail(Request $request)
    {
        $this->validate($request, [
            'user_product_id' => 'required|numeric',
            'share_id' => 'required|numeric',
        ]);

        $userProductId = $request->input('user_product_id');
        $shareId = $request->input('share_id');

        try {
            $result = $this->productService->productDetail($userProductId, $shareId);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess($result);
    }
}