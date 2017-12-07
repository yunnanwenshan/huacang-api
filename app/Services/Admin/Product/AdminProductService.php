<?php

namespace App\Services\Admin\Product;

use App\Components\Paginator;
use App\Exceptions\Admin\Product\AdminProductException;
use App\Exceptions\Product\ProductException;
use App\Models\Brands;
use App\Models\Product;
use App\Models\ProductClass;
use App\Models\Share;
use App\Models\ShareDetail;
use App\Models\UserProduct;
use App\Services\Admin\Product\Contract\AdminProductInterface;
use Carbon\Carbon;
use DB;
use Log;

class AdminProductService implements AdminProductInterface
{
    /**
     * 增加产品
     */
    private function createClass(&$user, $productParam)
    {
        //主分类
        if (!isset($productParam['sub_class_name']) || !isset($productParam['main_class_name'])
            || (mb_strlen($productParam['main_class_name']) <= 0)) {
            Log::info(__FILE__ . '(' . __LINE__ . '), main_class_name is null, ', [
                'user_id' => $user->id,
                'product_param' => $productParam,
            ]);

            throw new AdminProductException(AdminProductException::PRODUCT_PARAM_ERROR, AdminProductException::DEFAULT_CODE + 2);
        }

        //主分类
        $class = ProductClass::where('name', $productParam['main_class_name'])->first();
        if (empty($class)) {
            $class = new ProductClass();
            $class->name = $productParam['main_class_name'];
            $class->type = ProductClass::TYPE_PRODUCT;
            $class->user_id = $user->id;
            $class->save();
        }

        //二级分类
        $secondClass = ProductClass::where('parent_id', $class->id)
            ->where('name', $productParam['sub_class_name'])
            ->first();
        if (empty($secondClass)) {
            $secondClass = new ProductClass();
            $secondClass->name = $productParam['sub_class_name'];
            $secondClass->type = ProductClass::TYPE_PRODUCT;
            $secondClass->user_id = $user->id;
            $secondClass->parent_id = $class->id;
            $secondClass->save();
        }

        return $secondClass;
    }

    public function addProduct(&$user, array $productParam)
    {
        try {
            DB::begintransaction();
            if (empty($productParam['class_id'])) {
                $class = $this->createClass($user, $productParam);
            } else {
                if (!isset($productParam['class_id']) || empty($productParam['class_id'])) {
                    Log::info(__FILE__ . '(' . __LINE__ . '), class_id is null, ', [
                        'user_id' => $user->id,
                        'product_param' => $productParam,
                    ]);
                    $class = $this->createClass($user, $productParam);
                } else {
                    $class = ProductClass::where('id', $productParam['class_id'])->first();
                    if (empty($class)) {
                        $class = $this->createClass($user, $productParam);
                    }
                }
            }

            //品牌
            if (!isset($productParam['brands']) || (mb_strlen($productParam['brands']) <= 0)) {
                Log::info(__FILE__ . '(' . __LINE__ . '), brands is null, ', [
                    'user_id' => $user->id,
                    'product_param' => $productParam,
                ]);

                throw new AdminProductException(AdminProductException::PRODUCT_PARAM_ERROR, AdminProductException::DEFAULT_CODE + 4);
            }

            $brands = Brands::where('user_id', $user->id)->where('brands', $productParam['brands'])->first();
            if (empty($brands)) {
                $brands = new Brands();
                $brands->user_id = $user->id;
                $brands->brands = $productParam['brands'];
                $brands->save();
            }

            //产品表
            $product = new Product();
            $product->name = $productParam['name'];
            $product->type = $productParam['type'];
            $product->class_id = $class->id;
            $product->brand_id = $brands->id;
            $product->code = $productParam['code'];
            $product->brands = $brands->brands;
            $product->valid_time = (new Carbon($productParam['valid_time']));
            $product->main_img = $productParam['main_img'];
            $product->sub_img = json_encode($productParam['sub_img']);
            $product->detail = $productParam['detail'];
            $product->template_id = $productParam['template_id'];
            $product->user_id = $user->id;
            $product->save();

            //产品与用户关系
            $userProduct = new UserProduct();
            $userProduct->product_id = $product->id;
            $userProduct->status = UserProduct::STATUS_INIT;
            $userProduct->cost_price = $productParam['cost_price'];
            $userProduct->supply_price = $productParam['supply_price'];
            $userProduct->selling_price = $productParam['selling_price'];
            $userProduct->stock_num = $productParam['stock_num'];
            $userProduct->min_sell_num = $productParam['min_sell_num'];
            $userProduct->recommend = $productParam['recommend'];
            $userProduct->save();

            Log::info(__FILE__ . '(' . __LINE__ . '), add product successful, ', [
                'user_id' => $user->id,
                'params' => $productParam,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error(__FILE__ . '(' . __LINE__ . '), add product fail, ', [
                'user_id' => $user->id,
                'product_params' => $productParam,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
            throw new AdminProductException(AdminProductException::PRODUCT_ADD_FAIL, AdminProductException::DEFAULT_CODE + 1);
        }
    }


    /**
     * 更新产品
     */
    public function updateProduct(&$user, array $productParam)
    {
        try {
            DB::begintransaction();

            //创建分类
            if (empty($productParam['class_id'])) {
                $class = $this->createClass($user, $productParam);
            } else {
                if (!isset($productParam['class_id']) || empty($productParam['class_id'])) {
                    Log::info(__FILE__ . '(' . __LINE__ . '), class_id is null, ', [
                        'user_id' => $user->id,
                        'product_param' => $productParam,
                    ]);

                    throw new AdminProductException(AdminProductException::PRODUCT_PARAM_ERROR, AdminProductException::DEFAULT_CODE + 3);
                }

                $class = ProductClass::where('id', $productParam['class_id'])->first();
                if (empty($class)) {
                    $class = $this->createClass($user, $productParam);
                }
            }

            $product = Product::where('id', $productParam['product_id'])
                ->where('user_id', $user->id)
                ->first();
            if (empty($product)) {
                Log::info(__FILE__ . '(' . __LINE__ . '), the product not exist, ', [
                    'user_id' => $user->id,
                    'product_param' => $productParam,
                ]);
                throw new AdminProductException(AdminProductException::PRODUCT_NOT_EXIST, AdminProductException::DEFAULT_CODE + 5);
            }

            $brands = Brands::where('brands', $productParam['brands'])->first();
            $product->name = $productParam['name'];
            $product->type = $productParam['type'];
            $product->class_id = $class->id;
            $product->brand_id = empty($brands) ? 0 : $brands->id;
            $product->code = $productParam['code'];
            $product->brands = $productParam['brands'];
            $product->valid_time = (new Carbon($productParam['valid_time']));
            $product->main_img = $productParam['main_img'];
            $product->sub_img = json_encode($productParam['sub_img']);
            $product->detail = $productParam['detail'];
            $product->template_id = $productParam['template_id'];
            $product->user_id = $user->id;
            $product->save();

            $affectRow = UserProduct::where('product_id', $product->id)
                ->update([
                    'cost_price' => $productParam['cost_price'],
                    'supply_price' => $productParam['supply_price'],
                    'selling_price' => $productParam['selling_price'],
                    'stock_num' => $productParam['stock_num'],
                    'recommend' => $productParam['recommend'],
                    'min_sell_num' => $productParam['min_sell_num'],
                ]);
            DB::commit();

            Log::info(__FILE__ . '(' . __LINE__ . '), update product successuful', [
                'user_id' => $user->id,
                'product_param' => $productParam,
                'affect_row' => $affectRow,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error(__FILE__ . '(' . __LINE__ . '), update product fail, ', [
                'user_id' => $user->id,
                'product_params' => $productParam,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
            throw new AdminProductException(AdminProductException::PRODUCT_UPDATE_FAIL, AdminProductException::DEFAULT_CODE + 6);
        }

    }

    /**
     * 删除产品
     */
    public function delProduct(&$user, array $productParam)
    {
        //检查商品是否存在
        $product = Product::where('id', $productParam['product_id'])->where('user_id', $user->id)->first();
        if (empty($product)) {
            Log::info(__FILE__ . '(' . __LINE__ . '), product not exist, ', [
                'user_id' => $user->id,
                'product_param' => $productParam,
            ]);
            throw new AdminProductException(AdminProductException::PRODUCT_NOT_EXIST, AdminProductException::DEFAULT_CODE + 7);
        }

        $affectRow = UserProduct::where('product_id', $productParam['product_id'])
            ->update(['status' => UserProduct::STATUS_DELETED]);

        Log::info(__FILE__ . '(' . __LINE__ . '), delete product successful, ', [
            'user_id' => $user->id,
            'product_param' => $productParam,
            'affect_row' => $affectRow,
        ]);
    }

    /**
     * 产品列表
    */
    public function productList(&$user, array $productParam, Paginator $paginator)
    {
        $sql = 'select pr.id, pr.name, pr.code, pr.class_id, pr.brand_id, up.id as user_product_id, up.cost_price, up.min_sell_num, up.supply_price, up.selling_price, up.stock_num, up.update_time, up.status from product pr INNER JOIN user_product up on pr.id = up.product_id';
        //产品id
        if (!empty($productParam['product_id'])) {
            $sql = $sql . ' where pr.id = ' . $productParam['product_id'];
        }

        //产品名称
        if (!empty($productParam['name'])) {
            if (str_contains($sql, 'where')) {
                $sql = $sql . ' and name = \'' . $productParam['name'] . '\'';
            } else {
                $sql = $sql . ' where pr.name = \'' . $productParam['name'] . '\'';
            }
        }

        //产品分类
        if (!empty($productParam['class_id'])) {
            if (str_contains($sql, 'where')) {
                $sql = $sql . ' and pr.class_id = ' . $productParam['class_id'];
            } else {
                $sql = $sql . ' where pr.class_id = ' . $productParam['class_id'];
            }
        }

        //产品品牌
        if (!empty($productParam['brands'])) {
            if (str_contains($sql, 'where')) {
                $sql = $sql . ' and pr.brands = \'' . $productParam['brands'] . '\'';
            } else {
                $sql = $sql . ' where pr.brands = \'' . $productParam['brands'] . '\'';
            }
        }

        //产品状态
        if (!empty($productParam['status'])) {
            if (str_contains($sql, 'where')) {
                $sql = $sql . ' and up.status = ' . $productParam['status'];
            } else {
                $sql = $sql . ' where up.status = ' . $productParam['status'];
            }
        }

        //开始时间
        if (!empty($productParam['start_time'])) {
            if (str_contains($sql, 'where')) {
                $sql = $sql . ' and up.update_time >= \'' . $productParam['start_time'] . '\'';
            } else {
                $sql = $sql . ' where up.update_time >= \'' . $productParam['start_time'] . '\'';
            }
        }

        //结束日期
        if (!empty($productParam['end_time'])) {
            if (str_contains($sql, 'where')) {
                $sql = $sql . ' and up.update_time <= \'' . $productParam['end_time'] . '\'';
            } else {
                $sql = $sql . ' where up.update_time <= \'' . $productParam['end_time'] . '\'';
            }
        }

//        $productsQuery = Product::where('product.id', $productParam['product_id'])
//            ->where('product.name', $productParam['name'])
//            ->where('product.class_id', $productParam['class_id'])
//            ->where('product.brands', $productParam['brands'])
//            ->where('user_product.status', $productParam['status'])
//            ->where('user_product.update_time', '>=', $productParam['start_time'])
//            ->where('user_product.update_time', '<=', $productParam['end_time'])
//            ->join('user_product', 'product.id', '=', 'user_product.product_id')
//            ->select('product.id', 'product.name', 'product.code', 'product.class_id', 'product.brand_id',
//                'user_product.cost_price', 'user_product.min_sell_num', 'user_product.supply_price', 'user_product.selling_price', 'user_product.stock_num', 'user_product.update_time');

        $productsQuery = DB::select($sql);

        $products = $paginator->queryArray($productsQuery);
        $classeIds = $products->pluck('class_id');

        $classeCollection = ProductClass::whereIn('id', $classeIds->toArray())->get();
        $rs = $products->map(function ($item) use($classeCollection) {
            $e['product_id'] = $item->id;
            $e['user_product_id'] = $item->user_product_id;
            $e['name'] = $item->name;
            $e['code'] = $item->code;
            $e['class_id'] = $item->class_id;
            $e['cost_price'] = $item->cost_price;
            $e['supply_price'] = $item->supply_price;
            $e['selling_price'] = $item->selling_price;
            $e['stock_num'] = $item->stock_num;
            $e['min_sell_num'] = 0;
            $e['update_time'] = (new Carbon($item->update_time))->format('Y-m-d H:i:s');
            $class = $classeCollection->where('id', $item->class_id)->first();
            $e['class_name'] = empty($class) ? '' : $class->name;
            $e['status'] = $item->status;
            return $e;
        });

        $result = $rs->toArray();

        Log::info(__FILE__ . '(' . __LINE__ . '), product list successful, ', [
            'user_id' => $user->id,
            'product_params' => $productParam,
            '$result' => $result,
            'productsQuery' => $productsQuery,
        ]);

        return $result;
    }

    /**
     * 产品详情
     */
    public function productDetail(&$user, $productId)
    {
        $product = Product::where('product.user_id', $user->id)
            ->where('product.id', $productId)
            ->join('user_product', 'product.id', '=', 'user_product.product_id')
            ->select('product.brands', 'product.type', 'product.class_id','product.template_id', 'product.detail', 'product.main_img', 'product.sub_img', 'product.id', 'product.name', 'product.code', 'product.class_id', 'product.brand_id',
                'user_product.cost_price', 'user_product.min_sell_num', 'user_product.supply_price',
                'user_product.selling_price', 'user_product.stock_num', 'user_product.update_time', 'user_product.id as user_product_id',
                'user_product.recommend', 'user_product.status', 'user_product.supply_price', 'user_product.cost_price')
            ->first();

        if (empty($product)) {
            Log::info(__FILE__ . '(' . __LINE__ . '), product is null, ', [
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
            throw new AdminProductException(AdminProductException::PRODUCT_NOT_EXIST, AdminProductException::DEFAULT_CODE + 8);
        }

        return [
            'user_product_id' => $product->user_product_id,
            'product_id' => $product->id,
            'name' => $product->name,
            'class_id' => $product->class_id,
            'type' => $product->type,
            'product_code' => $product->code,
            'recommend' => $product->recommend,
            'brands' => $product->brands,
            'valid_time' => (new Carbon($product->valid_time))->format('Y-m-d H:i:s'),
            'cost_price' => $product->cost_price,
            'supply_price' => $product->supply_price,
            'selling_price' => $product->selling_price,
            'stock_num' => $product->stock_num,
            'min_sell_num' => $product->min_sell_num,
            'detail' => $product->detail,
            'sale_type' => $product->status == 1 ? 1 : 2,
            'template_id' => $product->template_id,
            'user_id' => $user->id,
            'main_img' => $product->main_img,
            'sub_img' => json_decode($product->sub_img, true),
        ];
    }

    /**
     * 单件产品上架
     */
    public function productSellingUp(&$user, $productId)
    {
        $prouct = Product::where('id', $productId)
            ->where('user_id', $user->id)
            ->first();
        if (empty($prouct)) {
            throw new AdminProductException(AdminProductException::PRODUCT_NOT_EXIST, AdminProductException::DEFAULT_CODE + 9);
        }

        $affectRow = UserProduct::where('product_id', $productId)
            ->update(['status' => UserProduct::STATUS_ONLINE]);

        Log::info(__FILE__ . '(' . __LINE__ . '), product selling up successful, ', [
            'user_id' => $user->id,
            'product_id' => $productId,
            'affect_row' => $affectRow,
        ]);
    }

    /**
     * 单件产品下架
     */
    public function productSellingDown(&$user, $productId)
    {
        $prouct = Product::where('id', $productId)
            ->where('user_id', $user->id)
            ->first();
        if (empty($prouct)) {
            throw new AdminProductException(AdminProductException::PRODUCT_NOT_EXIST, AdminProductException::DEFAULT_CODE + 10);
        }

        $affectRow = UserProduct::where('product_id', $productId)
            ->update(['status' => UserProduct::STATUS_OFFLINE]);

        Log::info(__FILE__ . '(' . __LINE__ . '), product selling down successful, ', [
            'user_id' => $user->id,
            'product_id' => $productId,
            'affect_row' => $affectRow,
        ]);
    }

    /**
     * 批量产品下架
     */
    public function productSellingUpBatch(&$user, array $productIds)
    {
        $products = Product::whereIn('id', $productIds)
            ->where('user_id', $user->id)
            ->select('id')
            ->get();

        if ($products->count() <= 0) {
            Log::info(__FILE__ . '(' . __LINE__ . '), products count is zero, ', [
                'user_id' => $user->id,
                'product_ids' => $productIds,
            ]);
            throw new AdminProductException(AdminProductException::PRODUCT_PRODUCT_COUNT_ZERO, AdminProductException::DEFAULT_CODE + 11);
        }

        //更新产品
        $affectRow = UserProduct::whereIn('product_id', $productIds)
            ->update(['status' => UserProduct::STATUS_ONLINE]);

        Log::info(__FILE__ . '(' . __LINE__ . '), product selling up batch successful, ', [
            'user_id' => $user->id,
            'product_ids' => $productIds,
            'affect_row' => $affectRow,
        ]);
    }

    /**
     * 批量产品上架
     */
    public function productSellingDownBatch(&$user, array $productIds)
    {
        $products = Product::whereIn('id', $productIds)
            ->where('user_id', $user->id)
            ->get();

        if ($products->count() <= 0) {
            Log::info(__FILE__ . '(' . __LINE__ . '), products count is zero, ', [
                'user_id' => $user->id,
                'product_ids' => $productIds,
            ]);
            throw new AdminProductException(AdminProductException::PRODUCT_PRODUCT_COUNT_ZERO, AdminProductException::DEFAULT_CODE + 12);
        }

        //更新产品状态
        $affectRow = UserProduct::whereIn('product_id', $productIds)
            ->update(['status' => UserProduct::STATUS_OFFLINE]);

        Log::info(__FILE__ . '(' . __LINE__ . '), product selling down batch successful, ', [
            'user_id' => $user->id,
            'product_ids' => $productIds,
            'affect_row' => $affectRow,
        ]);
    }

    /**
     * 创建商城
     */
    public function shareProduct(&$user, array $params)
    {
        if (empty($params['market_name']) || (count($params['product_ids']) <= 0)) {
            Log::info(__FILE__ . '(' . __LINE__ . '), share product error, ', [
                'user_id' => $user->id,
                'params' => $params,
            ]);
            throw new ProductException(ProductException::PRODUCT_MARKET_NAME_IS_NULL, ProductException::DEFAULT_CODE + 5);
        }

        try {
            DB::begintransaction();
            $shareId = $params['share_id'];
            //新创建商城
            if (empty($shareId)) {
                $this->createShop($user, $params);
            } else {
                $share = Share::where('id', $params['share_id'])->where('user_id', $user->id)->first();
                if (empty($share)) {
                    //创建商城
                    $this->createShop($user, $params);
                } else {
                    //更新商城
                    $this->updateShop($user, $params);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error(__FILE__ . '(' . __LINE__ . '), share product fail, ', [
                'user_id' => $user->id,
                'params' => $params,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
            throw new ProductException($e->getMessage(), ProductException::DEFAULT_CODE + 6);
        }
    }

    //创建商城
    private function createShop(&$user, $params)
    {
        $userProductsCollection = $this->getUserProducts($user, $params);

        $share = Share::where('user_id', $user->id)->first();
        if (!empty($share)) {
            $this->updateShop($user, $params);
        } else {
            $share = Share::where('name', $params['market_name'])->first();
            if (!empty($share)) {
                throw new ProductException(ProductException::PRODUCT_MARKET_NAME_EXISTED, ProductException::DEFAULT_CODE + 8);
            }

            //创建商城
            $share = new Share();
            $share->user_id = $user->id;
            $share->name = $params['market_name'];
            $share->url = ''; //TODO:是否想要写商城url地址
            $share->save();

            $userProducts = $userProductsCollection->toArray();
            foreach ($userProducts as $product) {
                $shareDetail = new ShareDetail();
                $shareDetail->share_id = $share->id;
                $shareDetail->product_id = $product['product_id'];
                $shareDetail->cost_price = $product['cost_price'];
                $shareDetail->supply_price = $product['supply_price'];
                $shareDetail->save();
            }

            Log::info(__FILE__ . '(' . __LINE__ . '), create shop successful, ', [
                'user_id' => $user->id,
                'params' => $params,
            ]);
        }
    }

    //更新商城
    private function updateShop(&$user, $params)
    {
        $userProductsCollection = $this->getUserProducts($user, $params);
        $shareDetailCollection = ShareDetail::whereIn('product_id', $params['product_ids'])->get();
        foreach ($params['product_ids'] as $item) {
            $shareDetail = $shareDetailCollection->where('product_id', $item)->first();
            $userProduct = $userProductsCollection->where('product_id', $item)->first();
            if (empty($userProduct)) {
                Log::info(__FILE__ . '(' . __LINE__ . '), update shop continue, ', [
                    'user_id' => $user->id,
                    'params' => $params,
                ]);
                continue;
            }
            if (empty($shareDetail)) {
                //新增加商店商品
                $shareDetail = new ShareDetail();
                $shareDetail->share_id = $params['share_id'];
                $shareDetail->product_id = $item;
                $shareDetail->cost_price = $userProduct->cost_price;
                $shareDetail->supply_price = $userProduct->supply_price;
                $shareDetail->save();

                Log::info(__FILE__ . '(' . __LINE__ . '), add share detail, ', [
                    'share_detail' => $shareDetail,
                    'user_id' => $user->id,
                    'params' => $params,
                ]);
            } else {
                //更新价格
                $affactRow = ShareDetail::where('share_id', $params['share_id'])
                    ->where('product_id', $item)
                    ->update([
                        'cost_price' => $userProduct->cost_price,
                        'supply_price' => $userProduct->supply_price,
                    ]);
                Log::info(__FILE__ . '(' . __LINE__ . '), share detail update, ', [
                    'affact_row' => $affactRow,
                    'user_id' => $user->id,
                    'params' => $params,
                ]);
            }
        }

        Log::info(__FILE__ . '(' . __LINE__ . '), update shop successful, ', [
            'user_id' => $user->id,
            'params' => $params,
        ]);
    }

    private function getUserProducts(&$user, $params)
    {
        $productCount = Product::whereIn('id', $params['product_ids'])->where('user_id', $user->id)->count();
        $userProductsCollection = UserProduct::whereIn('product_id', $params['product_ids'])->get();
        if (($productCount != count($params['product_ids'])) || ($userProductsCollection->count() != count($params['product_ids']))) {
            Log::info(__FILE__ . '(' . __LINE__ . '), share product error 2, ', [
                'user_id' => $user->id,
                'params' => $params,
            ]);
            throw new ProductException(ProductException::PRODUCT_MARKET_NAME_IS_NULL, ProductException::DEFAULT_CODE + 6);
        }

        return $userProductsCollection;
    }
}