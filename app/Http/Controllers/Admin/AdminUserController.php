<?php

namespace App\Http\Controllers\Admin;

use App\Components\Paginator;
use App\Exceptions\Admin\User\AdminUserException;
use App\Exceptions\User\UserException;
use App\Models\ClientInfo;
use App\Models\Order;
use App\Models\Share;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Http\Request;
use Exception;
use DB;
use Log;

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
            $shares = Share::where('user_id', $user->id)->where('status', 0)->get();
            if (empty($shares) || ($shares->count() <= 0)) {
                throw new AdminUserException(AdminUserException::USER_NO_MARKET, AdminUserException::DEFAULT_CODE + 1);
            }
            $shareIds = $shares->pluck('id')->toArray();
            $orders = Order::whereIn('share_id', $shareIds)
//                ->where('status', Order::STATUS_FINISHED)
                ->groupBy('user_id')
                ->groupBy('share_id')
                ->select('share_id', 'user_id', DB::raw('sum(total_fee) as total_fee'))
                ->get();
            $orderCollections = $paginator->queryArray($orders);
            $userIds = $orderCollections->pluck('user_id')->toArray();
            $users = User::whereIn('id', $userIds)->get();
            $userOrders = Order::whereIn('share_id', $shareIds)
                ->whereIn('status', [
                    Order::STATUS_FINISHED,
                    Order::STATUS_INIT,
                    Order::STATUS_PAY,
                    Order::STATUS_SEND_PRODUCT,
                    Order::STATUS_SENDED_PRODUCT,
                    Order::STATUS_AUDIT,
                    Order::STATUS_REFUND,
                ])
                ->whereIn('user_id', $userIds)
                ->select('user_id', 'order_detail', 'share_id')
                ->get();

            $clientInfos = ClientInfo::where('user_id', $user->id)->whereIn('client_id', $userIds)->get();

            $rs = $orderCollections->map(function ($item) use ($users, $userOrders, $shares, $clientInfos) {
                $share = $shares->where('id', $item->share_id)->first();
                $user = $users->where('id', $item->user_id)->first();
                $clientInfo = $clientInfos->where('user_id', $user->id)->where('client_id', $item->user_id)->first();
                $e['share'] = [
                    'share_id' => $share->id,
                    'market_name' => $share->name,
                ];
                $e['user'] = [
                    'client_id' => $user->id,
                    'user_name' => empty($user->user_name) ? '' : $user->user_name,
                    'client_name' => empty($clientInfo) ? '' : $clientInfo->name,
                    'mobile' => $user->mobile,
                ];
                $e['client_id'] = $item->user_id;
                $e['mobile'] = $user->mobile;
                $e['total_fee'] = $item->total_fee;
                $ors = $userOrders->where('user_id', $item->user_id)->where('share_id', $item->share_id);
                $productList = array();
                $ors->map(function ($it) use (&$productList) {
                    $orderDetail = json_decode($it->order_detail, true);
                    if (json_last_error()) {
                        return;
                    }
                    foreach ($orderDetail as $detail) {
                        $product = UserProduct::where('user_product.id', $detail['user_product_id'])
                            ->join('product', 'user_product.product_id', '=', 'product.id')
                            ->select('product.name')
                            ->first();
                        $i['user_product_id'] = $detail['user_product_id'];
                        $i['product_name'] = $product->name;
                        $i['buy_num'] = $detail['count'];
                        $i['price'] = $detail['price'];
                        $i['total_fee'] = $detail['count'] * $detail['price'];
                        $productList[] = $i;
                    }
                });
                $e['product_list'] = $productList;
                return $e;
            });

            $result = array_values($rs->toArray());
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }

        return response()->clientSuccess(['page' => $paginator->export(), 'client_list' => $result]);
    }

    /**
     * 用户信息表
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function clientInfo(Request $request)
    {
        $this->validate($request, [
            'client_id' => 'required|numeric',
        ]);
        $clientId = $request->input('client_id');

        try {
            $clientInfo = ClientInfo::where('user_id', $this->user->id)->where('client_id', $clientId)->first();
            if (empty($clientInfo)) {
                throw new UserException(UserException::ADMIN_CLIENT_INFO_NOT_EXIST, UserException::DEFAULT_CODE + 47);
            }
            $user = User::where('id', $clientId)->first();
            if (empty($user)) {
                throw new UserException(UserException::AUTH_USER_NOT_EXIST, UserException::DEFAULT_CODE + 48);
            }
            $rs = $clientInfo->export();
            $rs['user_name'] = empty($user->user_name) ? '' : $user->user_name;
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess($rs);
    }

    /**
     * 更新用户信息表数据
     *
     * @param Request $request [description]
     *
     * @return Response [description]
     */
    public function updateClientInfo(Request $request)
    {
        $this->validate($request, [
            'client_id' => 'required|numeric',
            'mobile' => 'sometimes',
            'name' => 'sometimes|string',
            'remark' => 'sometimes|string',
        ]);
        $clientId = $request->input('client_id');
        $mobile = $request->input('mobile');
        $name = $request->input('name');
        $remark = $request->input('remark');

        try {
            $params = [];
            if (!empty($mobile)) {
                $params['mobile'] = $mobile;
            }
            if (!empty($name)) {
                $params['name'] = $name;
            }
            if (!empty($remark)) {
                $params['remark'] = $remark;
            }
            if (!empty($params)) {
                $affectRow = ClientInfo::where('user_id', $this->user->id)
                    ->where('client_id', $clientId)
                    ->limit(1)
                    ->update($params);
                if ($affectRow == 0) {
                    throw new UserException(UserException::ADMIN_CLIENT_INFO_UPDATE_FAIL, UserException::DEFAULT_CODE + 49);
                }
                Log::info(__FILE__ . '(' . __LINE__ . '), update client info successful, ', [
                    'user_id' => $this->user->id,
                    'client_id' => $clientId,
                    'affectRow' => $affectRow,
                    'mobile' => $mobile,
                    'name' => $name,
                ]);
            }
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess();
    }
}