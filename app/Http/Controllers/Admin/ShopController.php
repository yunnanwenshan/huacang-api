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
use Log;

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
            $shares = Share::where('user_id', $user->id)->get();
            $rs = $shares->map(function ($item) {
//                $e['share_id'] = $item->id;
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
            $sql = 'select * from share where user_id = ' . $this->user->id;
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
            $productCollection = Product::whereIn('id', $shareDetailCollection->pluck('product_id')->toArray())->get();
            $rs = $shareCollection->map(function ($item) use ($shareDetailCollection, $productCollection) {
                $e['share_id'] = $item->id;
                $e['name'] = $item->name;
                $e['update_time'] = (new Carbon($item->update_time))->format('Y-m-d H:i:s');
                $shareDetail = $shareDetailCollection->where('share_id', $e['share_id']);
                $e['products'] = array_values($shareDetail->map(function ($it) use ($productCollection) {
                    $e = $it->export();
                    $product = $productCollection->where('id', $it->product_id)->first();
                    $e['product_name'] = $product->name;
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
}