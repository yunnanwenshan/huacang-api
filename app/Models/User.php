<?php

namespace App\Models;

use Illuminate\Foundation\Auth\Access\Authorizable;

class User extends Model
{
    use Authorizable;

    protected $table = 'user';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    const STATUS_NORMAL = 0; //正常用户
    const STATUS_BLACK = 1; //黑名单用户
    const STATUS_CANCEL = 2; //注销用户

    //标识登录来源
    const LOGIN_FROM_WEB = 0;
    const LOGIN_FROM_APP = 1;


    public function userInfo()
    {
        return $this->hasOne('App\Models\UserInfo', 'user_id', 'id');
    }

    /**
     * 设置ID返回的文本格式.
     *
     * @return string
     */
    public function getIdTextAttribute()
    {
        return (string) $this->id;
    }

    /**
     * 最小输出的用户信息.
     *
     * @return array
     */
    public function export()
    {
        $userInfo = $this->userInfo;

        return [
            'user_id' => $this->idText,
            'avatar' => $userInfo->avatar,
            'name' => $userInfo->real_name,
        ];
    }
}
