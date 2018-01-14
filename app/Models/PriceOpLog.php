<?php

namespace App\Models;

use Illuminate\Foundation\Auth\Access\Authorizable;

class PriceOpLog extends Model
{
    use Authorizable;

    protected $table = 'price_op_log';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    //价格类型：1 成本价， 2 供应价 3 销售价
    const PRICE_TYPE_1 = 1;
    const PRICE_TYPE_2 = 2;
    const PRICE_TYPE_3 = 3;

    //修改修改类型：1 创建产品时初始价格 2 用户更新产品价格变动 3 加入商城价格变动 4 更新商城产品价格 5 商城给用户调价 6 商城给订单调价
    const TYPE_1 = 1;
    const TYPE_2 = 2;
    const TYPE_3 = 3;
    const TYPE_4 = 4;
    const TYPE_5 = 5;
    const TYPE_6 = 6;
}
