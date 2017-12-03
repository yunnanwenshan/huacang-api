<?php

namespace App\Http\Controllers\Admin;

use App\Components\Paginator;
use App\Exceptions\User\AdminUserException;
use App\Models\Order;
use App\Models\Product;
use App\Models\Share;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use DB;

class AdminUserController extends Controller
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
     * 用户列表
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function userList(Request $request)
    {
        $this->validate($request, [
            'page_index' => 'required|numeric',
            'page_size' => 'required|numeric',
        ]);

        $user = $this->user;
        $paginator = new Paginator($request);

        try {
            //查找商家信息
            $shares = Share::where('user_id', $user->id)->orderBy('id', 'desc')->first();
            if (empty($shares)) {
                throw new AdminUserException(AdminUserException::USER_NO_MARKET, AdminUserException::DEFAULT_CODE + 1);
            }
            $orders = Order::where('share_id', $shares->id)
                ->where('status', Order::STATUS_FINISHED)
                ->groupBy('user_id')
                ->select('share_id', 'user_id', DB::raw('sum(total_fee) as total_fee'))
                ->get();
            $orderCollections = $paginator->queryArray($orders);
            $userIds = $orderCollections->pluck('user_id')->toArray();
            $users = User::whereIn('id', $userIds)->get();
            $userOrders = Order::where('share_id', $shares->id)
                ->where('status', Order::STATUS_FINISHED)
                ->whereIn('user_id', $userIds)
                ->select('user_id', 'order_detail', 'share_id')
                ->get();

            $rs = $orderCollections->map(function ($item) use ($users, $userOrders) {
                $user = $users->where('id', $item->user_id)->first();
                $e['client_id'] = $item->user_id;
                $e['mobile'] = $user->mobile;
                $e['total_fee'] = $item->total_fee;
                $ors = $userOrders->where('user_id', $item->user_id)->where('share_id', $item->share_id)->get();
                $e['product_list'] = $ors->map(function ($it) {
                    $orderDetail = json_decode($it->order_detail, true);
                    $product = Product::where('id', $orderDetail['product_id'])->first();
                    $i['product_id'] = $orderDetail['product_id'];
                    $i['product_name'] = $product->name;
                    $i['buy_num'] = $orderDetail['num'];
                    $i['price'] = $orderDetail['price'];
                    $i['total_fee'] = $orderDetail['num'] * $orderDetail['price'];
                    return $i;
                });
            });

            $result = array_values($rs->toArray());
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess(['page' => $paginator->export(), 'client_list' => $result, 'market_name' => $shares->name]);
    }
}