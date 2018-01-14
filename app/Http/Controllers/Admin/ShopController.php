<?php

namespace App\Http\Controllers\Admin;

use App\Components\Paginator;
use App\Exceptions\Admin\Shop\ShopException;
use App\Exceptions\Product\ProductException;
use App\Models\PriceOpLog;
use App\Models\Product;
use App\Models\Share;
use App\Models\ShareDetail;
use App\Models\UserProduct;
use App\Services\Admin\PriceLog\Contract\PriceLogInterface;
use App\Services\Admin\PriceLog\PriceLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use DB;
use Log;
use Validator;

class ShopController extends Controller
{
    /**
     * 构造函数，
     *
     * @param Request           $request  [description]
     *
     * @return not [description]
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     * 商城列表
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function marketNameList(Request $request)
    {
        $user = $this->user;

        try {
            $shares = Share::where('user_id', $user->id)
                ->where('status', 0)
                ->get();
            $rs = $shares->map(function ($item) {
                $e['share_id'] = $item->id;
                $e['name'] = $item->name;
                return $e;
            });

            $result = array_values($rs->toArray());
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess(['list' => $result]);
    }

    /**
     * 商城列表
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function shopList(Request $request)
    {
        $this->validate($request, [
            'page_size' => 'required|numeric',
            'page_index' => 'required|numeric',
        ]);

        $shopName = $request->input('shop_name', null);
        $startTime = $request->input('start_time,', null);
        $endTime = $request->input('end_time', null);

        try {
            $paginator = new Paginator($request);
            $sql = 'select * from share where status = 0 and user_id = ' . $this->user->id;
            if (!empty($shopName)) {
                $sql = $sql . ' and name = \'' . $shopName . '\'';
            }
            if (!empty($startTime)) {
                $sql = $sql . ' and update_time >= ' . $startTime;
            }
            if (!empty($endTime)) {
                $sql = $sql . ' and update_time <= ' . $endTime;
            }

            $shops = DB::select($sql);
            $shareCollection = $paginator->queryArray($shops);
            $shareIds = $shareCollection->pluck('id');
            $shareDetailCollection = ShareDetail::whereIn('share_id', $shareIds->toArray())->get();
            $userProductIds = $shareDetailCollection->pluck('user_product_id')->toArray();
            $userProducts = UserProduct::whereIn('id', $userProductIds)->get();
            $productCollection = Product::whereIn('id', $userProducts->pluck('product_id')->toArray())->get();
            $rs = $shareCollection->map(function ($item) use ($shareDetailCollection, $userProducts, $productCollection) {
                $e['share_id'] = $item->id;
                $e['name'] = $item->name;
                $e['update_time'] = (new Carbon($item->update_time))->format('Y-m-d H:i:s');
                $shareDetail = $shareDetailCollection->where('share_id', $e['share_id']);
                $e['products'] = array_values($shareDetail->map(function ($it) use ($userProducts, $productCollection) {
                    $e = $it->export();
                    $userProduct = $userProducts->where('id', $it->user_product_id)->first();
                    if (empty($userProduct)) {
                        Log::info(__FILE__ . '(' . __LINE__ . '), user product is null, ', [
                            'it' => $it,
                        ]);
                        return [];
                    }
                    $e['status'] = $userProduct->status;
                    $product = $productCollection->where('id', $userProduct->product_id)->first();
                    if (empty($product)) {
                        Log::info(__FILE__ . '(' . __LINE__ . '), product is null, ', [
                            'it' => $it,
                        ]);
                        return [];
                    }
                    $e['product_name'] = $product->name;
                    $e['main_img'] = $product->main_img;
                    return $e;
                })->toArray());
                return $e;
            });

            $result = array_values($rs->toArray());
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess(['page' => $paginator->export(), 'shop_list' => $result]);
    }

    /**
     * 创建商城
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'market_name' => 'required|string|min:1'
        ]);

        $marketName = $request->input('market_name');
        try {
            $share = Share::where('name', $marketName)
                ->where('status', 0)
                ->first();
            if (!empty($share)) {
                throw new ShopException(ShopException::SHOP_EXIST, ShopException::DEFAULT_CODE + 1);
            }
            $share = new Share();
            $share->user_id = $this->user->id;
            $share->name = $marketName;
            $share->save();
            Log::info(__FILE__ . '(' . __LINE__ . '), create market name successful, ', [
                'user_id' => $this->user->id,
                'name' => $marketName,
            ]);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess(['share_id' => $share->id]);
    }

    /**
     * 更新商城
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'share_id' => 'required|numeric',
            'market_name' => 'required|string|min:1'
        ]);

        $shareId = $request->input('share_id');
        $marketName = $request->input('market_name');
        try {
            $share = Share::where('name', $marketName)
                ->where('status', 0)
                ->first();
            if (empty($share)) {
                throw new ShopException(ShopException::SHOP_EXIST, ShopException::DEFAULT_CODE + 1);
            }
            $share = Share::where('id', $shareId)
                ->where('status', 0)
                ->where('user_id', $this->user->id)
                ->first();
            if (empty($share)) {
                throw new ShopException(ShopException::SHOP_NOT_EXIST, ShopException::DEFAULT_CODE + 2);
            }
            $share->user_id = $this->user->id;
            $share->name = $marketName;
            $share->save();
            Log::info(__FILE__ . '(' . __LINE__ . '), update market name successful, ', [
                'user_id' => $this->user->id,
                'share_id' => $shareId,
                'name' => $marketName,
            ]);
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess();
    }

    /**
     * 更新商城内产品价格
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function priceUpdate(Request $request, PriceLogInterface $priceService)
    {
        $this->validate($request, [
            'share_id' => 'required|numeric',
            'user_product_id' => 'required|numeric',
            'cost_price' => 'required|numeric',
            'supply_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
        ]);

        $shareId = $request->input('share_id');
        $userProductId = $request->input('user_product_id');
        $costPrice = $request->input('cost_price');
        $supplyPrice = $request->input('supply_price');
        $sellingPrice = $request->input('selling_price');

        try {
            $share = Share::where('id', $shareId)
                ->where('user_id', $this->user->id)
                ->where('status', 0)
                ->first();
            if (empty($share)) {
                throw new ShopException(ShopException::SHOP_EXIST, ShopException::DEFAULT_CODE + 1);
            }
            $shareDetail = ShareDetail::where('share_id', $shareId)
                ->where('user_product_id', $userProductId)
                ->first();
            if (empty($shareDetail)) {
                throw new ShopException(ShopException::SHOP_PRODUCT_NO_EXIST, ShopException::DEFAULT_CODE + 6);
            }
            try {
                DB::begintransaction();


                //成本价格
                $priceService->recordPriceLog(0, $userProductId, $shareId, 0, '', $this->user->id, PriceOpLog::PRICE_TYPE_1,
                    $shareDetail->cost_price, $costPrice, '', '', PriceOpLog::TYPE_4, $this->user->id);
                //供应价
                $priceService->recordPriceLog(0, $userProductId, $shareId, 0, '', $this->user->id, PriceOpLog::PRICE_TYPE_2,
                    $shareDetail->supply_price, $supplyPrice, '', '', PriceOpLog::TYPE_4, $this->user->id);
                //销售价
                $priceService->recordPriceLog(0, $userProductId, $shareId, 0, '', $this->user->id, PriceOpLog::PRICE_TYPE_3,
                    $shareDetail->selling_price, $sellingPrice, '', '', PriceOpLog::TYPE_4, $this->user->id);
                $shareDetail->cost_price = $costPrice;
                $shareDetail->supply_price = $supplyPrice;
                $shareDetail->selling_price = $sellingPrice;
                $shareDetail->save();
                DB::commit();

                Log::info(__FILE__ . '(' . __LINE__ . '), shop update price successful, ', [
                    'user_id' => $this->user->id,
                    'share_id' => $shareId,
                    'user_product_id' => $userProductId,
                    'cost_price' => $costPrice,
                    'supply_price' => $supplyPrice,
                    'selling_price' => $sellingPrice,
                ]);
            } catch (Exception $e) {
                DB::rollback();
                Log::error(__FILE__ . '(' . __LINE__ . '), shop update price fail, ', [
                    'user_id' => $this->user->id,
                    'share_id' => $shareId,
                    'user_product_id' => $userProductId,
                    'cost_price' => $costPrice,
                    'supply_price' => $supplyPrice,
                    'selling_price' => $sellingPrice,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ]);
            }

        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess();
    }

    /**
     * 删除商城
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            'share_id' => 'required|numeric',
        ]);

        $shareId = $request->input('share_id');

        try {
            $share = Share::where('id', $shareId)
                ->where('status', 0)
                ->where('user_id', $this->user->id)->first();
            if (empty($share)) {
                throw new ShopException(ShopException::SHOP_NOT_EXIST, ShopException::DEFAULT_CODE + 3);
            }
            $share->status = 1;
            $share->save();
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess();
    }

    /**
     * 更新商城
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function productAdd(Request $request)
    {
        $this->validate($request, [
            'share_id' => 'required|numeric',
            'products' => 'required|array',
        ]);

        $shareId = $request->input('share_id');
        $products = $request->input('products');

        try {
            foreach ($products as $item) {
                $v = Validator::make($item, [
                    'user_product_id' => 'required|numeric',
                    'cost_price' => 'required|numeric',
                    'supply_price' => 'required|numeric',
                    'selling_price' => 'required|numeric',
                ]);

                if ($v->fails()) {
                    Log::info(__FILE__.'('.__LINE__.'), product list validate fail', [
                        'location' => $v->messages()->toJson(JSON_UNESCAPED_SLASHES),
                    ]);
                    throw new ProductException(ProductException::PRODUCT_PARAM_VALID, ProductException::DEFAULT_CODE + 14);
                }
            }
            $share = Share::where('id', $shareId)->where('user_id', $this->user->id)->where('status', 0)->first();
            if (empty($share)) {
                throw new ShopException(ShopException::SHOP_NOT_EXIST, ShopException::DEFAULT_CODE + 4);
            }

            $userProducts = UserProduct::where('user_id', $this->user->id)->whereIn('id', array_pluck($products, 'user_product_id'))->get();
            if ($userProducts->count() != count($products)) {
                throw new ProductException(ProductException::PRODUCT_NOT_EXIST, ProductException::DEFAULT_CODE + 15);
            }

            //商品较验
            foreach ($userProducts as $item) {
                if ($item->status != UserProduct::STATUS_ONLINE) {
                    throw new ProductException(ProductException::PRODUCT_NOT_EXIST, ProductException::DEFAULT_CODE + 16);
                }
            }

            //检查商品是否已存在商店中
            $shareDetailCount = ShareDetail::where('share_id', $shareId)->whereIn('user_product_id', array_pluck($products, 'user_product_id'))->count();
            if ($shareDetailCount != 0) {
                throw new ShopException(ShopException::SHOP_PRODUCT_EXIST, ShopException::DEFAULT_CODE + 5);
            }

            DB::begintransaction();
            foreach ($products as $item) {
                $shareDetail = new ShareDetail();
                $shareDetail->share_id = $shareId;
                $shareDetail->user_product_id = $item['user_product_id'];
                $shareDetail->cost_price = $item['cost_price'];
                $shareDetail->supply_price = $item['supply_price'];
                $shareDetail->selling_price = $item['selling_price'];
                $shareDetail->save();
            }
            DB::commit();

            Log::info(__FILE__ . '(' . __LINE__ . '), add product to shop successful, ', [
                'user_id' => $this->user->id,
                'share_id' => $shareId,
                'products' => $products,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess();
    }

    /**
     * 删除产品从商城
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function productRemove(Request $request)
    {
        $this->validate($request, [
            'share_id' => 'required|numeric',
            'products' => 'required|array',
        ]);

        $shareId = $request->input('share_id');
        $products = $request->input('products');
        try {
            foreach ($products as $item) {
                $v = Validator::make($item, [
                    'user_product_id' => 'required|numeric',
                    'cost_price' => 'required|numeric',
                    'supply_price' => 'required|numeric',
                    'selling_price' => 'required|numeric',
                ]);

                if ($v->fails()) {
                    Log::info(__FILE__.'('.__LINE__.'), product list validate fail', [
                        'location' => $v->messages()->toJson(JSON_UNESCAPED_SLASHES),
                    ]);
                    throw new ProductException(ProductException::PRODUCT_PARAM_VALID, ProductException::DEFAULT_CODE + 14);
                }
            }
            $share = Share::where('id', $shareId)->where('user_id', $this->user->id)->where('status', 0)->first();
            if (empty($share)) {
                throw new ShopException(ShopException::SHOP_NOT_EXIST, ShopException::DEFAULT_CODE + 4);
            }
            $userProducts = UserProduct::where('user_id', $this->user->id)->whereIn('id', array_pluck($products, 'user_product_id'))->get();
            if ($userProducts->count() != count($products)) {
                throw new ProductException(ProductException::PRODUCT_NOT_EXIST, ProductException::DEFAULT_CODE + 15);
            }
            DB::begintransaction();
            foreach ($products as $item) {
                $affectRow = ShareDetail::where('share_id', $shareId)
                    ->where('user_product_id', $item['user_product_id'])
                    ->limit(1)
                    ->update($item);
                if ($affectRow == 0) {
                    throw new ProductException(ProductException::PRODUCT_MARKET_CREATE_FAIL, ProductException::DEFAULT_CODE + 16);
                }
            }


            DB::commit();
            Log::info(__FILE__ . '(' . __LINE__ . '), update share product, ', [
                'share_id' => $shareId,
                'products' => $products,
                'user_id' => $this->user->id,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess();
    }
}