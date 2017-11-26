<?php

namespace App\Http\Controllers\Admin;

use App\Components\Paginator;
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
        $this->validate($request, [
            'page_size' => 'required|numeric',
            'page_index' => 'required|numeric',
        ]);

        $user = $this->user;

        try {
            $paginator = new Paginator($request);
            $shares = Share::where('user_id', $user->id)->select(DB::raw('distinct name'));
            $shareCollection = $paginator->query($shares);
            $rs = $shareCollection->map(function ($item) {
                return $item->name;
            });

            $result = array_values($rs->toArray());
        } catch (Exception $e) {
            return response()->clientFail($e->getCode(), $e->getMessage());
        }
        return response()->clientSuccess(['list' => $result, 'page' => $paginator->export()]);
    }
}