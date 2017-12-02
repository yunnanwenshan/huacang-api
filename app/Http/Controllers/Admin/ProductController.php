<?php
namespace App\Http\Controllers\Admin;

use App\Components\Paginator;
use App\Services\Admin\Product\Contract\AdminProductInterface;
use Illuminate\Http\Request;
use Exception;

class ProductController extends Controller
{
    /**
     * 构造函数，
     *
     * @param Request           $request  [description]
     *
     * @return not [description]
     */
    public function __construct(AdminProductInterface $productService, Request $request)
    {
        parent::__construct($request);
        $this->productService = $productService;
    }

    /**
     * 增加商品
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function addProduct(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'class_id' => 'required|numeric',
            'main_class_name' => 'required|string',
            'sub_class_name' => 'required|string',
            'type' => 'required|in:1,2',
            'code' => 'required',
            'recommend' => 'required|string',
            'brands' => 'required|string',
            'valid_time' => 'required|string',
            'cost_price' => 'required|numeric',
            'supply_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock_num' => 'required|numeric',
            'min_sell_num' => 'required|numeric',
            'detail' => 'required|string',
            'sale_type' => 'required|in:1,2',
            'template_id' => 'required|numeric',
            'main_img' => 'required|url',
            'sub_img' => 'required|array',
        ]);

        $params['name'] = $request->input('name');
        $params['class_id'] = $request->input('class_id');
        $params['main_class_name'] = $request->input('main_class_name');
        $params['sub_class_name'] = $request->input('sub_class_name');
        $params['type'] = $request->input('type');
        $params['code'] = $request->input('code');
        $params['recommend'] = $request->input('recommend');
        $params['brands'] = $request->input('brands');
        $params['valid_time'] = $request->input('valid_time');
        $params['cost_price'] = $request->input('cost_price');
        $params['supply_price'] = $request->input('supply_price');
        $params['selling_price'] = $request->input('selling_price');
        $params['stock_num'] = $request->input('stock_num');
        $params['min_sell_num'] = $request->input('min_sell_num');
        $params['detail'] = $request->input('detail');
        $params['sale_type'] = $request->input('sale_type');
        $params['template_id'] = $request->input('template_id');
        $params['main_img'] = $request->input('main_img');
        $params['sub_img'] = $request->input('sub_img');

        try {
            $this->productService->addProduct($this->user, $params);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess();
    }

    /**
     * 更新产品
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function updateProduct(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|numeric',
            'name' => 'required|string',
            'class_id' => 'required|numeric',
            'main_class_name' => 'string',
            'sub_class_name' => 'string',
            'type' => 'required|in:1,2',
            'code' => 'required',
            'recommend' => 'required|string',
            'brands' => 'required|string',
            'valid_time' => 'required|string',
            'cost_price' => 'required|numeric',
            'supply_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock_num' => 'required|numeric',
            'min_sell_num' => 'required|numeric',
            'detail' => 'required|string',
            'sale_type' => 'required|in:1,2',
            'template_id' => 'required|numeric',
            'main_img' => 'required|string',
            'sub_img' => 'required|array',
        ]);

        $params['product_id'] = $request->input('product_id');
        $params['name'] = $request->input('name');
        $params['class_id'] = $request->input('class_id');
        $params['main_class_name'] = $request->input('main_class_name');
        $params['sub_class_name'] = $request->input('sub_class_name');
        $params['type'] = $request->input('type');
        $params['code'] = $request->input('code');
        $params['recommend'] = $request->input('recommend');
        $params['brands'] = $request->input('brands');
        $params['valid_time'] = $request->input('valid_time');
        $params['cost_price'] = $request->input('cost_price');
        $params['supply_price'] = $request->input('supply_price');
        $params['selling_price'] = $request->input('selling_price');
        $params['stock_num'] = $request->input('stock_num');
        $params['min_sell_num'] = $request->input('min_sell_num');
        $params['detail'] = $request->input('detail');
        $params['sale_type'] = $request->input('sale_type');
        $params['template_id'] = $request->input('template_id');
        $params['main_img'] = $request->input('main_img');
        $params['sub_img'] = $request->input('sub_img');

        try {
            $this->productService->updateProduct($this->user, $params);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess();
    }

    /**
     * 删除产品
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function delProduct(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|numeric',
        ]);

        $params['product_id'] = $request->input('product_id');
        try {
            $this->productService->delProduct($this->user, $params);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess();
    }

    /**
     * 产品列表
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function productList(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'numeric',
            'name' => 'string',
            'class_id' => 'numeric',
            'brands' => 'string',
            'status' => 'in:1,2,3,4',
            'start_time' => 'string',
            'end_time' => 'string',
            'page_index' => 'required|numeric',
            'page_size' => 'required|numeric',
        ]);

        $params['product_id'] = $request->input('product_id', null);
        $params['name'] = $request->input('name', null);
        $params['class_id'] = $request->input('class_id', null);
        $params['brands'] = $request->input('brands', null);
        $params['start_time'] = $request->input('start_time', null);
        $params['end_time'] = $request->input('end_time', null);
        $params['status'] = $request->input('status', null);

        try {
            $paginator = new Paginator($request);
            $result = $this->productService->productList($this->user, $params, $paginator);

        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess(['product_list' => $result, 'page' => $paginator->export()]);
    }

    /**
     * 产品上架
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function productSellingUp(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|numeric',
        ]);
        $productId= $request->input('product_id');
        try {
            $this->productService->productSellingUp($this->user, $productId);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess();

    }

    /**
     * 产品下架
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function productSellingDown(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|numeric',
        ]);
        $productId= $request->input('product_id');
        try {
            $this->productService->productSellingDown($this->user, $productId);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess();
    }

    /**
     * 产品批量上架
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function productSellingUpBatch(Request $request)
    {
        $this->validate($request, [
            'product_ids' => 'required|array',
        ]);
        $productId= $request->input('product_id');
        try {
            $result = $this->productService->productSellingUpBatch($this->user, $productId);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess($result);
    }

    /**
     * 产品批量下架
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function productSellingDownBatch(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|array',
        ]);
        $productId= $request->input('product_id');
        try {
            $result = $this->productService->productSellingDownBatch($this->user, $productId);
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
            'product_id' => 'required|numeric',
        ]);

        $productId = $request->input('product_id');
        try {
            $result = $this->productService->productDetail($this->user, $productId);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess($result);
    }

    /**
     * 分享产品
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function shareProduct(Request $request)
    {
        $this->validate($request, [
            'product_ids' => 'required|array',
            'share_id' => 'sometimes|numeric',
            'market_name' => 'sometimes|string',
        ]);

        $param['product_ids'] = $request->input('product_ids');
        $param['share_id'] = $request->input('share_id');
        $param['market_name'] = $request->input('market_name');

        try {
            $result = $this->productService->shareProduct($this->user, $param);
            $result['url'] = 'http://www.baidu.com';
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess($result);

    }
}