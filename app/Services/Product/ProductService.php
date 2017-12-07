<?php

namespace App\Services\Product;


use App\Components\Paginator;
use App\Exceptions\Product\ProductException;
use App\Models\Product;
use App\Models\ProductClass;
use App\Models\Share;
use App\Models\ShareDetail;
use App\Models\Template;
use App\Models\TemplateFormItem;
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
        $userProducts = UserProduct::whereIn('product_id', $productIds)
            ->where('status', UserProduct::STATUS_ONLINE)
            ->get();
        $classCollection = ProductClass::whereIn('id', $classIds)->get();
        $rs = [];
        foreach ($products as $item) {
            $class = $classCollection->where('id', $item->class_id)->first();
            $userProduct = $userProducts->where('product_id', $item->id)->first();
            $className = empty($class) ? '' : $class->name;
            $e = $item->export();
            $e['class_name'] = $className;
            if (empty($userProduct)) {
                continue;
            }
            $rs[] = array_merge($e, $userProduct->export());
        }

        Log::info(__FILE__ . '(' . __LINE__  .'), product list successful, ', [
            'share_id' => $shareId,
            'rs' => $rs,
        ]);

        $share = Share::where('id', $shareId)->first();

        return [
            'market_name' => $share->name,
            'product_list' => $rs,
        ];
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
        $userProducts = UserProduct::whereIn('product_id', $productIds)
            ->where('status', UserProduct::STATUS_ONLINE)
            ->get();
        $classIds = $products->pluck('class_id');
        $classCollection = ProductClass::whereIn('id', $classIds)->get();
        $productRs = [];
        foreach ($products as $item) {
            $class = $classCollection->where('id', $item->class_id)->first();
            $userProduct = $userProducts->where('product_id', $item->id)->first();
            if (empty($userProduct)) {
                continue;
            }
            $e = $item->export();
            $className = empty($class) ? '' : $class->name;
            $e['class_name'] = $className;
            $productRs[] = array_merge($e, $userProduct->export());
        }

        Log::info(__FILE__ . '(' . __LINE__ . '), product list, ', [
            'product_ids' => $productIds,
            'rs' => $productRs
        ]);

        return $productRs;
    }

    /**
     * 商品详情
     */
    public function productDetail($userProductId)
    {
        $userProduct = UserProduct::where('id', $userProductId)->first();
        if (empty($userProduct)) {
            Log::error(__FILE__ . '(' . __LINE__ . '), user product is null', [
                'user_product_id' => $userProductId,
            ]);
            throw new ProductException(ProductException::PRODUCT_NOT_EXIST, ProductException::DEFAULT_CODE + 1);
        }
        if (!in_array($userProduct->status, [UserProduct::STATUS_ONLINE])) {
            Log::warning(__FILE__ . '(' . __LINE__ . '), user product not online, ', [
                'user_product_id' => $userProductId
            ]);
            throw new ProductException(ProductException::PRODUCT_NO_ONLINE, ProductException::DEFAULT_CODE + 10);
        }
        $product = Product::where('id', $userProduct->product_id)->first();
        if (empty($product)) {
            Log::error(__FILE__ . '(' . __LINE__ . '), product is null, ', [
                'product_id' => $userProduct->product_id,
            ]);
            throw new ProductException(ProductException::PRODUCT_NOT_EXIST, ProductException::DEFAULT_CODE + 9);
        }

        $template = Template::where('id', $product->template_id)->first();
        $templateList = TemplateFormItem::where('template_id', $product->template_id)->get();

        Log::info(__FILE__ . '(' . __LINE__ . '), product detail, ', [
            'user_product_id' => $userProductId,
            'template_id' => $product->template_id,
        ]);

        if (!empty($template)) {
            $templateDetail['template'] = $template->export();
            $templateDetail['template']['template_item_list'] = $templateList->map(function ($item) {
                return $item->export();
            });
            $productDetail = array_merge($templateDetail, $product->export());
        } else {
            $productDetail = $product->export();
        }

        return array_merge($productDetail, $userProduct->export());
    }
}