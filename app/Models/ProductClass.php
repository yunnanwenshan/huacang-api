<?php

namespace App\Models;

class ProductClass extends Model
{
    protected $table = 'class';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    //模版分类
    const TYPE_PRODUCT = 0; //产品分类
    const TYPE_TEMPLATE = 1; //模版分类
}
