<?php

namespace App\Components;

use App\Models\Product;
use Carbon\Carbon;
use Log;

class ProductGenerateID
{
    private $date;

    private $secOfDay;

    private $micro;

    private $productKey;

//    private $itemKey;

    private $sn;

    private $now;

    public function __construct($userId)
    {
        $this->now = Carbon::now();

        //当前项目的id
        $code = new Code();
        $uStr = $code->encodeID($userId, 5);
        $this->productKey = sprintf('%05s', $uStr);
    }

    //生成 id
    public function genID()
    {
        $this->initKeyInfo();
        $this->checkUnique();

        return $this->sn;
    }

    //检查是否重复
    public function checkUnique()
    {
        $unique = false;
        $try_num_max = 5;
        $try_num = 0;

        //检查是否是重复的order
        while ($unique == false) {
            if ($try_num > $try_num_max) {
                Log::error('init article sn fail ', [
                    'sn' => $this->sn,
                ]);
                throw new \Exception('创建产品失败', 100);
            }

            $product = Product::where('code', $this->code)->first();
            if (!empty($product)) {
                $unique = false;
                $this->initKeyInfo();
                $try_num++;
                Log::info('init product sn fail', [
                    'sn' => $this->code,
                ]);
            } else {
                $unique = true;
            }
        }
    }

    //初始化info
    public function initKeyInfo()
    {

        //生成日期前缀 例如 151003（2015年10月3日）
        $this->date = sprintf('%06s', $this->now->format('ymd'));

        //生成当天时间戳
        $this->secOfDay = $this->now->copy()->diffInSeconds($this->now->startOfDay());
        $this->secOfDay = sprintf('%05s', $this->secOfDay);

        //生成当前微秒时间戳。
        $micro = explode(' ', microtime());
        $micro = $micro[0] * 1000;
        $this->micro = sprintf('%04d', $micro);

        //生成sn
        $this->sn = 'T'.$this->date.$this->secOfDay.$this->micro.$this->productKey;
    }
}

class Code {
    //密码字典
    private $dic = array(
        0=>'0', 1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8',
        9=>'9', 10=>'A',  11=>'B', 12=>'C', 13=>'D', 14=>'E', 15=>'F',  16=>'G', 17=>'H',
        18=>'I',19=>'J',  20=>'K', 21=>'L',  22=>'M',  23=>'N', 24=>'O', 25=>'P', 26=>'Q',
        27=>'R',28=>'S',  29=>'T',  30=>'U', 31=>'V',  32=>'W',  33=>'X', 34=>'Y', 35=>'Z'
    );


    public function encodeID($int, $format=8) {
        $dics = $this->dic;
        $dnum = 36; //进制数
        $arr = array ();
        $loop = true;
        while ($loop) {
            $arr[] = $dics[bcmod($int, $dnum)];
            $int = bcdiv($int, $dnum, 0);
            if ($int == '0') {
                $loop = false;
            }
        }
        if (count($arr) < $format)
            $arr = array_pad($arr, $format, $dics[0]);

        return implode('', array_reverse($arr));
    }

    public function decodeID($ids) {
        $dics = $this->dic;
        $dnum = 36; //进制数
        //键值交换
        $dedic = array_flip($dics);
        //去零
        $id = ltrim($ids, $dics[0]);
        //反转
        $id = strrev($id);
        $v = 0;
        for ($i = 0, $j = strlen($id); $i < $j; $i++) {
            $v = bcadd(bcmul($dedic[$id {
            $i }
            ], bcpow($dnum, $i, 0), 0), $v, 0);
        }
        return $v;
    }

}