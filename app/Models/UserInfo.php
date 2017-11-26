<?php

namespace App\Models;

class UserInfo extends Model
{
    protected $table = 'user_info';

    // 自定义时间相关字段
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
