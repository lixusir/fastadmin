<?php

namespace app\common\command;

use app\common\model\User;
use app\common\model\UserMoneyLog;
use tests\thinkphp\library\think\urlTest;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Env;

class Cs  extends Command
{
    protected $model = null;
    protected $code = [];

    protected function configure()
    {
        $this->setName('cs')->setDescription('测试');
    }

    protected function execute(Input $input, Output $output)
    {


        $usermoneylog = new UserMoneyLog([],2);

        $usermoneylog->setlog(1,rand(1,999),'测试');

        /*for($o=1;$o<=5000;$o++){

            for($j=1;$j<=50;$j++){

                UserMoneyLog::setlog($j,rand(1,999),'测试');

            }

        }die('ok');*/


        /*$money_list = $this->buildPartitionSql('fa_user_money_log','id,user_id,money',20,'','log');

        $pindex = 1;

        $psize = 20;

        $count = Db::query("select count(*) from ".$money_list." ");

        $list = Db::query("select * from ".$money_list." where id in (select id from (select id from ".$money_list." order by id desc  limit 400000,10 ) as t  ) order by id desc ");*/







        $output->info('ok');

    }

    /** *
     * 构造获取总记录数及主键ID的sql子查询语句 * @param
     * $table 主表名称 * @param
     * $idKey 主键id字段名称 *
     * @param string $fields 其它字段名称,多个字段用英文逗号分隔 *
     * @param int $num 子表数量 * @param string $where 查询条件 * @return array
     */
    function buildPartitionSql($table,$fields='',$num=1,$where='',$alias) {

        $listTable = [];

        for ($i = 0; $i < $num; $i++) {

            $listTable[] = sprintf('SELECT %s FROM %s%s where 1=1 %s', $fields,$table, ($i + 1), $where);

        }

        $listTable = '( ' . implode(" UNION ALL ", $listTable) . ') AS ' . $alias;

        return $listTable;
    }

}