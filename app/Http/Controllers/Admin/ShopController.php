<?php

namespace App\Http\Controllers\Admin;

use App\Models\Share;
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
    public function shopList(Request $request)
    {
        $user = $this->user;
        try {
            $shares = Share::where('user_id', $user->id)->select(DB::raw('distinct name'))->get();
            $rs = $shares->map(function ($item) {
                return $item->name;
            });

            $result = array_values($rs->toArray());
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess(['list' => $result]);
    }
}