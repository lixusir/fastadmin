<?php


namespace app\common\model;


use think\Model;

class BaseModel extends Model{

    // 开启自动写入时间戳字段
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';
    protected $dateFormat = 'Y-m-d H:i:s';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    protected $deleteTime = 'deleted_time';
}