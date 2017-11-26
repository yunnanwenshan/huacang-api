<?php

namespace App\Services\Admin\ConfigClass;


use App\Models\Brands;
use App\Models\ProductClass;
use App\Services\Admin\ConfigClass\Contract\AdminClassInterface;
use Log;
use DB;

class ClassService implements AdminClassInterface
{
    /**
     * class/list
     */
    public function classList(&$user, $type)
    {
        $parentClasses = ProductClass::where('type', $type)
            ->where('parent_id', 0)
            ->get();
        if (empty($parentClasses)) {
            return [];
        }

        $classIds = $parentClasses->pluck('id');
        $subClasses = ProductClass::where('type', $type)
            ->whereIn('parent_id', $classIds)
            ->get();

        $rs = $parentClasses->map(function ($item) use ($subClasses) {
            $subClass = $subClasses->where('parent_id', $item->id);
            $e['class_id'] = $item->id;
            $e['name'] = $item->name;
            $rs = $subClass->map(function ($em) {
                $k['class_id'] = $em->id;
                $k['name'] = $em->name;
                return $k;
            });
            $e['sub_class'] = array_values($rs->toArray());
            return $e;
        });

        Log::info(__FILE__ . '(' . __LINE__ . '), class list successful, ', [
            'user_id' => $user->id,
            'type' => $type,
            'rs' => $rs,
        ]);

        return [
            'class_list' => $rs->toArray(),
        ];
    }

    /**
     * 品牌列表
     */
    public function brandsList(&$user)
    {
        $brands = Brands::select(DB::raw('distinct brands'))->get();
        $rs = $brands->map(function ($item) {
            return $item->brands;
        });

        Log::info(__FILE__ . '(' . __LINE__ . '), brands, ', [
            'user_id' => $user->id,
            'brands' => $rs,
        ]);

        return [
            'brand_list' => $rs->toArray(),
        ];
    }
}