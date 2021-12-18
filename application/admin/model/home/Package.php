<?php

namespace app\admin\model\home;

use think\Model;


class Package extends Model
{

    

    

    // 表名
    protected $name = 'package';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







}
