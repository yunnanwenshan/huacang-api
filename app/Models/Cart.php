<?php
namespace App\Models;

class Cart extends Model {
    protected $table = 'carts';

    // 自定义时间相关字段
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
}