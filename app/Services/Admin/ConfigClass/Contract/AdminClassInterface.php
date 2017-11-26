<?php

namespace App\Services\Admin\ConfigClass\Contract;


interface AdminClassInterface
{
    /**
     * class/list
     */
    public function classList(&$user, $type);

    /**
     * 品牌列表
     */
    public function brandsList(&$user);
}