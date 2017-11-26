<?php

namespace App\Models;

class Template extends Model
{
    protected $table = 'template';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    //状态
    const STATUS_ENABLE = 0; //有效
    const STATUS_DISABLE = 1; //无效
}
