<?php

namespace App\Models;

use Carbon\Carbon;

class ShareDetail extends Model
{
    protected $table = 'share_detail';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function export()
    {
        return [
            'user_product_id' => $this->user_product_id,
            'cost_price' => $this->cost_price,
            'supply_price' => $this->supply_price,
            'selling_price' => $this->selling_price,
            'update_time' => (new Carbon($this->update_time))->format('Y-m-d H:i:s'),
        ];
    }
}
