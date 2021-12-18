<?php

namespace app\admin\model\home;

use think\Model;


class Service extends Model
{

    

    

    // 表名
    protected $name = 'service';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        //'ceratetime_text'
    ];
    

    



    public function getCeratetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['ceratetime']) ? $data['ceratetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCeratetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
