<?php

namespace App\Models;

use Carbon\Carbon;

class Order extends Model
{
    protected $table = 'orders';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    //订单状态：0:已下单 1 待支付， 2 待发货， 3 已发货， 4 待审核， 5 待退款， 6 已完成， 7 已取消， 8 客户申请取消
    const STATUS_INIT = 0;               //已下单
    const STATUS_PAY = 1;                //待支付
    const STATUS_SEND_PRODUCT = 2;       //待发货
    const STATUS_SENDED_PRODUCT = 3;     //已发货
    const STATUS_AUDIT = 4;              //待审核
    const STATUS_REFUND = 5;             //待退款
    const STATUS_FINISHED = 6;           //已完成
    const STATUS_CANCELED = 7;           //已取消
    const STATUS_CLIENT_REQUEST_CANCEL = 8; //客户申请取消

    const STATUS_TO_DESC = [
        self::STATUS_INIT => '已下单',
        self::STATUS_PAY => '待支付',
        self::STATUS_SEND_PRODUCT => '待发货',
        self::STATUS_SENDED_PRODUCT => '已发货',
        self::STATUS_AUDIT => '待审核',
        self::STATUS_REFUND => '待退款',
        self::STATUS_FINISHED => '已完成',
        self::STATUS_FINISHED => '已取消',
        self::STATUS_CLIENT_REQUEST_CANCEL => '客户申请取消',
    ];

    public function export()
    {
        return [
            'sn' => $this->sn,
            'user_id' => $this->user_id,
            'share_id' => $this->share_id,
            'supplier_id' => $this->supplier_id,
            'total_fee' => $this->total_fee,
            'product_list' => json_decode($this->order_detail, true),
            'status' => $this->status,
            'start_time' => (new Carbon($this->start_time))->timestamp,
            'end_time' => !strncmp($this->end_time, '0000-00-00 00:00:00', strlen('0000-00-00 00:00:00')) ? 0 :(new Carbon($this->end_time))->timestamp,
            'remark' => $this->remark,
        ];
    }
}
