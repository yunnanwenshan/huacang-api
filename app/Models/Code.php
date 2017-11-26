<?php

namespace App\Models;

class Code extends Model
{
    protected $table = 'mobile_code';

    // 自定义时间相关字段
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    //验证码类型
    const CODE_LOGIN = 1;            //登录验证码
    const CODE_CHANGEMOBILE = 2;     //换绑手机号验证码
    const CODE_EXIT_DEPOSIT = 3;     //h5 验证码
}
