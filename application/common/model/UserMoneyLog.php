<?php

namespace app\common\model;


use think\Db;
use think\Env;
use think\Model;

class UserMoneyLog extends BaseModel
{


    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';

    protected $updateTime = false;

    public static function setlog($user_id,$money,$memo){


        $table = Env::get('database.prefix').'user_money_log'.bcadd($user_id%20,1);

        $count = Db::query("SELECT count(*) as c FROM information_schema.TABLES t WHERE t.TABLE_NAME = '". $table ."'");

        if(!isset($count[0]['c']) || $count[0]['c']< 1){

            $sql = "CREATE TABLE `".$table."`  (
                          `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                          `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员ID',
                          `money` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '变更余额',
                          `before` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '变更前余额',
                          `after` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '变更后余额',
                          `memo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '备注',
                          `createtime` int(10) NULL DEFAULT NULL COMMENT '创建时间',
                          PRIMARY KEY (`id`) USING BTREE
                        ) ENGINE = InnoDB AUTO_INCREMENT = 11000002 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员余额变动表' ROW_FORMAT = DYNAMIC;";

            Db::query($sql);

        }

        $res = Db::table($table)->insert([
            'user_id'   => $user_id,
            'money'     => $money,
            'memo'      => $memo,
            'createtime'=> time()
        ]);

        dump($res);

    }
}