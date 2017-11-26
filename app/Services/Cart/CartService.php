<?php

namespace App\Services\Cart;


use App\Exceptions\Cart\CartException;
use App\Exceptions\Product\ProductException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductClass;
use App\Models\UserProduct;
use App\Services\Cart\Contract\CartInterface;
use Log;
use DB;

class CartService implements CartInterface
{
    /**
     * 创建或者查找一个购物车
     */
    public function cartInfo(&$user)
    {
        $cart = $this->cart($user->id);
        $item = $this->getAllItems($cart->id);
        $rs = $item->map(function ($it) {
            $e = [
                'cart_id' => $it->id,
                'product_id' => $it->product_id,
                'product_count' => $it->count,
            ];
            return $e;
        });

        return $rs->toArray();
    }

    /**
     * 将商品添加到购物车当中
     */
    public function addProductToCart(&$user, array $items)
    {
        if (empty($items)) {
            Log::info(__FILE__ . '(' . __LINE__ . ') submit info is null, ', [
                'user' => $user,
                'item' => $items,
            ]);
            throw new ProductException(ProductException::PRODUCT_IDS_IS_NULL, ProductException::DEFAULT_CODE + 2);
        }

        $productIds = array_pluck($items, 'product_id');
        $products = UserProduct::whereIn('product_id', $productIds)
            ->where('status', UserProduct::STATUS_ONLINE)
            ->select('product_id', 'selling_price')
            ->get();

        if (empty($products) || ($products->count() != count($items))) {
            Log::info(__FILE__ . '(' . __LINE__ . '), product list is null, ', [
                'user' => $user,
                'item' => $items,
            ]);
            throw new ProductException(ProductException::PRODUCT_IDS_IS_NULL, ProductException::DEFAULT_CODE + 3);
        }

        $cart = Cart::where('user_id', $user->id)->first();

        try {
            DB::beginTransaction();
            if (empty($cart)) {
                $cart = new Cart();
                $cart->user_id = $user->id;
                $cart->save();
            }
            foreach ($items as $item) {
                $product = $products->where('product_id', $item['product_id'])->first();
                if (empty($product)) {
                    Log::info(__FILE__ . '(' . __LINE__ . '), product is null, ', [
                        'user_id' => $user->id,
                        'item' => $item,
                        'items' => $items,
                    ]);
                    throw new ProductException(ProductException::PRODUCT_NOT_EXIST, ProductException::DEFAULT_CODE + 4);
                }
                $sellingPrice = empty($product) ? 0 : $product->selling_price;
                $this->add($cart->id, $item['product_id'], $item['count'], $sellingPrice);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error(__FILE__ . '(' . __LINE__ . '), add product to cart fail, ', [
                'user' => $user,
                'items' => $items,
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ]);
            throw new CartException(CartException::CART_ADD_PRODUCT_FAIL, CartException::DEFAULT_CODE + 1);
        }
    }

    /**
     * 更新商品的数目
     * @param $item_id
     * @param $qty
     * @return mixed
     */
    public function updateProduct(&$user, $productId, $amount)
    {

    }

    /**
     * 删除一个购物车项根据产品id
     * @param $item_id
     * @return bool
     */
    public function removeProductFromCartByProductId(&$user, array $productIds)
    {
        $cartItems = CartItem::where('product_id', $productIds)->get();
        if (empty($cartItems)) {
            Log::info(__FILE__ . '(' . __LINE__ . '), item ids is null, ', [
                'user' => $user,
                'product_ids' => $productIds,
            ]);
            throw new CartException(CartException::CART_REMOVE_PARAMS_INVALID, CartException::DEFAULT_CODE + 5);
        }

        $cartItemsIds = $cartItems->pluck('id');
        return $this->removeProductFromCart($user, $cartItemsIds);
    }

    /**
     * 删除一个购物车项
     * @param $item_id
     * @return bool
     */
    public function removeProductFromCart(&$user, array $itemIds)
    {
        if (empty($itemIds)) {
            Log::info(__FILE__ . '(' . __LINE__ . '), item ids is null, ', [
                'user' => $user,
                'item_ids' => $itemIds,
            ]);
            throw new CartException(CartException::CART_REMOVE_PARAMS_INVALID, CartException::DEFAULT_CODE + 2);
        }

        try {
            DB::beginTransaction();
            foreach ($itemIds as $id) {
                $this->removeItem($id);
            }
            DB::commit();

            Log::info(__FILE__ . '(' . __LINE__ . '), remove product successful, ', [
                'user_id' => $user->id,
                'item_ids' => $itemIds,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error(__FILE__ . '(' . __LINE__ . '), remove product from cart fail, ', [
                'user' => $user,
                'item_ids' => $itemIds,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
            throw new CartException(CartException::CART_REMOVE_FAIL, CartException::DEFAULT_CODE + 3);
        }
    }

    /**
     * 创建或者查找一个购物车
     */
    private function cart($userId)
    {
        $cart = Cart::where('user_id', $userId)->first();

        if(!$cart) {
            $cart = new Cart();
            $cart->user_id = $userId;
            $cart->total_value = 0;
            $cart->total_number = 0;
            $cart->save();

            return $cart;
        }

        return $cart;
    }

    /**
     * 将商品添加到购物车当中
     */
    public function add($cartId, $productId, $count, $price)
    {
        $cart_id = $cartId;
        $product_id = $productId;
        $total_price = $count * $price;

        $attributes = compact('cart_id', 'product_id', 'count', 'price', 'total_price');

        $item = $this->addItem($attributes);

        $this->updateCart($cartId);

        return $item;
    }

    /**
     * 增加购物车商品
     * @param $attributes
     * @return CartItem
     */
    private function addItem($attributes)
    {
        $cartItem = CartItem::where('cart_id', $attributes['cart_id'])
            ->where('product_id', $attributes['product_id'])
            ->first();
        if (empty($cartItem)) {
            $cartItem = new CartItem();
            $cartItem->cart_id = $attributes['cart_id'];
            $cartItem->product_id = $attributes['product_id'];
            $cartItem->count = $attributes['count'];
            $cartItem->total_price = $attributes['total_price'];
        } else {
            $cartItem->count = $cartItem->count + $attributes['count'];
            $cartItem->total_price = $cartItem->total_price + $attributes['total_price'];
        }

        $cartItem->price = $attributes['price'];
        $cartItem->save();

        return $cartItem;
    }

    /**
     * 更新购物车信息，主要总价格和商品数量
     * @param $cart_id
     * @return bool
     */
    private function updateCart($cartId)
    {
        $cart = $this->getCart($cartId);

        if(!$cart) {
            return false;
        }

        $total = $this->totalPrice($cartId);
        $count = $this->count($cartId);

        $cart->total_value = $total;
        $cart->total_number = $count;
        $cart->save();

        return true;
    }

    /**
     * 根据购物车 id 获取购物车详情
     * @param $cart_id
     * @return mixed
     */
    private function getCart($cart_id)
    {
        return Cart::findOrFail($cart_id);
    }

    /**
     * 更新商品的数目
     * @param $item_id
     * @param $qty
     * @return mixed
     */
    public function updateQty($itemId, $qty)
    {
        if($qty < 0) {
            return $this->removeItem($itemId);
        }

        return $this->updateItem($itemId, ['count' => $qty]);
    }

    /**
     * 删除一个购物车项
     * @param $item_id
     * @return bool
     */
    public function removeItem($itemId)
    {
        $item = CartItem::findOrFail($itemId);

        $item->delete();

        $this->updateCart($item->cart_id);

        return true;
    }

    /**
     *  清空购物车
     * @param $cart_id
     * @return mixed
     */
    public function removeAllItem($cartId)
    {
        $cart = Cart::findOrFail($cartId);

        $items = $this->getAllItems($cartId);

        foreach($items as $item)
        {
            $item->delete();
        }

        $this->updateCart($cartId);

        return $cart;
    }

    /**
     * 获取购物车中的所有商品
     * @param $cart_id
     * @return mixed
     */
    public function getAllItems($cartId)
    {
        $items = CartItem::where('cart_id', $cartId)->get();

        return $items;
    }

    /**
     * 获取一个特定的购物车项
     * @param $item_id
     * @return mixed
     */
    public function getItem($item_id)
    {
        $item = CartItem::findOrfail($item_id);

        return $item;
    }

    /**
     * 计算购物车内商品的数量
     * @param $cart_id
     * @return int
     */
    public function count($cart_id)
    {
        $count = 0;
        $items = $this->getAllItems($cart_id);

        if(!$items)
        {
            return $count;
        }

        foreach($items as $item)
        {
            $count += $item->count;
        }

        return $count;
    }

    /**
     * 计算购物车的总价格
     * @param $cart_id
     * @return int
     */
    public function totalPrice($cart_id)
    {
        $total_price = 0;
        $items =  $this->getAllItems($cart_id);


        if(!$items) {
            return $total_price;
        }

        foreach($items as $item) {
            $total_price += $item->total_price;
        }

        return $total_price;
    }

    /**
     * 更新一件商品的属性
     * @param $item_id
     * @param $attributes
     */
    public function updateItem($item_id, $attributes)
    {
        $item = CartItem::findOrFail($item_id);


        foreach($attributes as $key => $value)
        {
            if(in_array($key, $this->itemAttributes))
            {
                $item->$key = $value;
            }

        }

        if(!is_null(array_keys($attributes, ['count', 'price'])))
        {
            $item->total_price = $item->count * $item->price;
        }

        $item->save();

        $this->updateCart($item->cart_id);

        return $item;
    }
}