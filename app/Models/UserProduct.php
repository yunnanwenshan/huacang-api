<?php

namespace App\Models;

use Carbon\Carbon;

class UserProduct extends Model
{
    protected $table = 'user_product';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    //产品状态
    const STATUS_ONLINE = 1; //已上架
    const STATUS_INIT = 2; //未上架
    const STATUS_OFFLINE = 3; //已下架
    const STATUS_DELETED = 4; //已删除

    public function export()
    {
        return [
            'product_id' => $this->id,
            'cost_price' => $this->cost_price,
            'supply_price' => $this->supply_price,
            'selling_price' => $this->selling_price,
            'stock_num' => $this->stock_num,
            'min_sell_num' => $this->min_sell_num,
            'update_time' => (new Carbon($this->update_time))->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'recommend' => $this->recommend,
        ];
    }
}
