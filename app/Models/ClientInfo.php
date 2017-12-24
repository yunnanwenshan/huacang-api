<?php

namespace App\Models;

use Illuminate\Foundation\Auth\Access\Authorizable;

class ClientInfo extends Model
{
    use Authorizable;

    protected $table = 'client_info';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function export()
    {
        return [
            'client_id' => $this->client_id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'remark' => $this->remark,
            'mobile' => $this->mobile,
        ];
    }
}
