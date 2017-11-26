<?php

namespace App\Services\Product;


use App\Components\Paginator;
use App\Exceptions\Product\ProductException;
use App\Models\Product;
use App\Models\ProductClass;
use App\Models\ShareDetail;
use App\Models\UserProduct;
use App\Services\Product\Contract\ProductInterface;
use Carbon\Carbon;
use Log;

class ProductService implements ProductInterface
{
    /**
     * 获取商品列表
     */
    public function productList($shareId, Paginator $paginator)
    {
        $query = ShareDetail::where('share_id', $shareId);
        $shareDetailCollection = $paginator->query($query);
        $productIds = $shareDetailCollection->pluck('product_id');
        $products = Product::whereIn('id', $productIds)->get();
        $classIds = $products->pluck('class_id');
        $classCollection = ProductClass::whereIn('id', $classIds)->get();
        $rs = $products->map(function ($item) use ($classCollection) {
            $class = $classCollection->where('id', $item->class_id)->first();
            $className = empty($class) ? '' : $class->name;
            $e = $item->export();
            $e['class_name'] = $className;
            return $e;
        });

        Log::info(__FILE__ . '(' . __LINE__  .'), product list successful, ', [
            'share_id' => $shareId,
            'rs' => $rs,
        ]);

        return $rs;
    }

    /**
     * 获取商品列表根据商品id列表
     */
    public function productListDetail(array $productIds)
    {
        if (empty($productIds)) {
            return null;
        }

        $products = Product::whereIn('id', $productIds)->get();
        $userProducts = UserProduct::whereIn('product_id', $productIds)->get();
        $classIds = $products->pluck('class_id');
        $classCollection = ProductClass::whereIn('id', $classIds)->get();
        $productRs = $products->map(function ($item) use ($classCollection, $userProducts) {
            $class = $classCollection->where('id', $item->class_id)->first();
            $userProduct = $userProducts->where('product_id', $item->id)->first();
            $e = $item->export();
            $e['class_name'] = empty($class) ? '' : $class->name;
            $e['cost_price'] = $userProduct->cost_price;
            $e['supply_price'] = $userProduct->supply_price;
            $e['selling_price'] = $userProduct->selling_price;
            $e['stock_num'] = $userProduct->stock_num;
            $e['min_sell_num'] = $userProduct->min_sell_num;
            $e['update_time'] = (new Carbon($userProduct->update_time))->format('Y-m-d H:i:s');
            return $e;
        })->toArray();

        Log::info(__FILE__ . '(' . __LINE__ . '), product list, ', [
            'product_ids' => $productIds,
            'rs' => $productRs
        ]);

        return $productRs;
    }

    /**
     * 商品详情
     */
    public function productDetail($productId)
    {
        $product = Product::where('id', $productId)
            ->where('status', Product::STATUS_ONLINE)
            ->first();
        if (empty($product)) {
            Log::error(__FILE__ . '(' . __LINE__ . '), product is null, ', [
                'product_id' => $productId,
            ]);
            throw new ProductException(ProductException::PRODUCT_NOT_EXIST, ProductException::DEFAULT_CODE + 1);
        }

        Log::info(__FILE__ . '(' . __LINE__ . '), product detail, ', [
            'product_id' => $productId,
        ]);

        return $product->detail();
    }
}