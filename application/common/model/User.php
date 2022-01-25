<?php

namespace app\common\model;

use fast\Random;
use think\Db;
use think\Env;
use think\Model;

/**
 * 会员模型
 */
class User extends Model
{

    protected $name = 'user';


    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'url',
    ];

    /**
     * 获取个人URL
     * @param string $value
     * @param array  $data
     * @return string
     */
    public function getUrlAttr($value, $data)
    {
        return "/u/" . $data['id'];
    }

    /**
     * 获取头像
     * @param string $value
     * @param array  $data
     * @return string
     */
    public function getAvatarAttr($value, $data)
    {
        if (!$value) {
            //如果不需要启用首字母头像，请使用
            //$value = '/assets/img/avatar.png';
            $value = letter_avatar($data['nickname']);
        }
        return $value;
    }

    /**
     * 获取会员的组别
     */
    public function getGroupAttr($value, $data)
    {
        return UserGroup::get($data['group_id']);
    }

    /**
     * 获取验证字段数组值
     * @param string $value
     * @param array  $data
     * @return  object
     */
    public function getVerificationAttr($value, $data)
    {
        $value = array_filter((array)json_decode($value, true));
        $value = array_merge(['email' => 0, 'mobile' => 0], $value);
        return (object)$value;
    }

    /**
     * 设置验证字段
     * @param mixed $value
     * @return string
     */
    public function setVerificationAttr($value)
    {
        $value = is_object($value) || is_array($value) ? json_encode($value) : $value;
        return $value;
    }

    /**
     * 变更会员余额
     * @param int    $money   余额
     * @param int    $user_id 会员ID
     * @param string $memo    备注
     */
    public static function money($money, $user_id, $memo)
    {
        Db::startTrans();
        try {
            $user = self::lock(true)->find($user_id);
            if ($user && $money != 0) {
                $before = $user->money;
                //$after = $user->money + $money;
                $after = function_exists('bcadd') ? bcadd($user->money, $money, 2) : $user->money + $money;
                //更新会员信息
                $user->save(['money' => $after]);
                //写入日志
                MoneyLog::create(['user_id' => $user_id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
    }

    /**
     * 变更会员积分
     * @param int    $score   积分
     * @param int    $user_id 会员ID
     * @param string $memo    备注
     */
    public static function score($score, $user_id, $memo)
    {
        Db::startTrans();
        try {
            $user = self::lock(true)->find($user_id);
            if ($user && $score != 0) {
                $before = $user->score;
                $after = $user->score + $score;
                $level = self::nextlevel($after);
                //更新会员信息
                $user->save(['score' => $after, 'level' => $level]);
                //写入日志
                ScoreLog::create(['user_id' => $user_id, 'score' => $score, 'before' => $before, 'after' => $after, 'memo' => $memo]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
    }

    /**
     * 根据积分获取等级
     * @param int $score 积分
     * @return int
     */
    public static function nextlevel($score = 0)
    {
        $lv = array(1 => 0, 2 => 30, 3 => 100, 4 => 500, 5 => 1000, 6 => 2000, 7 => 3000, 8 => 5000, 9 => 8000, 10 => 10000);
        $level = 1;
        foreach ($lv as $key => $value) {
            if ($score >= $value) {
                $level = $key;
            }
        }
        return $level;
    }

    /**
     * 生成邀请码
     * @param int $length
     * @return string
     */
    public static function generateInviteCode($length = 8)
    {
        $code = Random::alnum($length);
        $result = self::where('invite_code', $code)->count();
        if ($result > 0) {
            return self::generateInviteCode();
        }
        return $code;
    }

    public function get_info($tab){

        return $this->name.$tab;
    }

    public static function add_user($params,$last_id){

        $table = Env::get('database.prefix').'user_'.bcadd($last_id%15,1);

        $count = Db::query("select count(*) as c from information_schema.TABLES t where t.TABLE_NAME = '".$table."'");

        if(!isset($count[0]['c']) || $count[0]['c']< 1){

            $sql = "CREATE TABLE `".$table."`  (
                      `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
                      `group_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '组别ID',
                      `username` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '用户名',
                      `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '昵称',
                      `password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '密码',
                      `salt` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '密码盐',
                      `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '电子邮箱',
                      `mobile` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '手机号',
                      `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '头像',
                      `level` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '等级',
                      `gender` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '性别',
                      `birthday` date NULL DEFAULT NULL COMMENT '生日',
                      `bio` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '格言',
                      `money` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '余额',
                      `score` int(11) NOT NULL DEFAULT 0 COMMENT '积分',
                      `successions` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT '连续登录天数',
                      `maxsuccessions` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT '最大连续登录天数',
                      `prevtime` int(11) NULL DEFAULT NULL COMMENT '上次登录时间',
                      `logintime` int(11) NULL DEFAULT NULL COMMENT '登录时间',
                      `loginip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '登录IP',
                      `loginfailure` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '失败次数',
                      `joinip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '加入IP',
                      `jointime` int(11) NULL DEFAULT NULL COMMENT '加入时间',
                      `createtime` int(11) NULL DEFAULT NULL COMMENT '创建时间',
                      `updatetime` int(11) NULL DEFAULT NULL COMMENT '更新时间',
                      `token` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT 'Token',
                      `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '状态',
                      `verification` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '验证',
                      `parent` int(11) NOT NULL DEFAULT 0,
                      `chain` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                      `invite_code` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '邀请码',
                      `is_give` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1=已赠送积分',
                      PRIMARY KEY (`id`) USING BTREE,
                      INDEX `username`(`username`) USING BTREE,
                      INDEX `email`(`email`) USING BTREE,
                      INDEX `mobile`(`mobile`) USING BTREE
                    ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员表' ROW_FORMAT = DYNAMIC;";

            Db::query($sql);

        }

        $params = array_merge($params,[
            'id'        =>$last_id,
            'createtime'=> time(),
            'updatetime'=> time(),
        ]);


        Db::table($table)->insert($params);

    }
}
