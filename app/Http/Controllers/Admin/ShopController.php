<?php

namespace App\Http\Controllers\Admin;

use App\Components\Paginator;
use App\Models\Product;
use App\Models\Share;
use App\Models\ShareDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use DB;

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
        $this->validate($request, [
            'page_size' => 'required|numeric',
            'page_index' => 'required|numeric',
        ]);

        $user = $this->user;

        try {
            $paginator = new Paginator($request);
            $shares = Share::where('user_id', $user->id);
            $shareCollection = $paginator->query($shares);
            $rs = $shareCollection->map(function ($item) {
                $e['share_id'] = $item->id;
                $e['name'] = $item->name;
                return $e;
            });

            $result = array_values($rs->toArray());
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess(['list' => $result, 'page' => $paginator->export()]);
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

        try {
            $paginator = new Paginator($request);
            $shares = Share::where('user_id', $this->user->id);
            $shareCollection = $paginator->query($shares);
            $shareIds = $shareCollection->pluck('id');
            $shareDetailCollection = ShareDetail::whereIn('share_id', $shareIds->toArray())->get();
            $productCollection = Product::whereIn('id', $shareDetailCollection->pluck('product_id')->toArray())->get();
            $rs = $shareCollection->map(function ($item) use ($shareDetailCollection, $productCollection) {
                $e['share_id'] = $item->id;
                $e['name'] = $item->name;
                $e['update_time'] = (new Carbon($item->update_time))->format('Y-m-d H:i:s');
                $e['products'] = $shareDetailCollection->map(function ($it) use ($productCollection) {
                    $e = $it->export();
                    $product = $productCollection->where('id', $it->product_id)->first();
                    $e['product_name'] = $product->name;
                    return $e;
                });
                return $e;
            });

            $result = array_values($rs->toArray());
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess(['page' => $paginator->export(), 'shop_list' => $result]);
    }
}