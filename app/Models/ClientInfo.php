<?php

namespace App\Models;

use Illuminate\Foundation\Auth\Access\Authorizable;

class ClientInfo extends Model
{
    use Authorizable;

    protected $table = 'client_info';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
}
