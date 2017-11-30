<?php

namespace App\Models;

class Product extends Model
{
    protected $table = 'product';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function export()
    {
        return [
            'product_id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'valid_time' => $this->valid_time,
            'detail' => $this->detail,
            'template_id' => $this->template_id,
            'user_id' => $this->user_id,
            'main_img' => $this->main_img,
            'sub_img' => json_decode($this->sub_img, true),
            'brands' => $this->brands,
        ];
    }
}
