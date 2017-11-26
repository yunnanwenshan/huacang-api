<?php

namespace App\Models;

class CartItem extends Model
{
    protected $table = 'cart_items';

    // 自定义时间相关字段
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
}