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

        $data = [];

        for($i = 1;$i<=350000;$i++){

           $data[] = [
               'user_id'    => $i,
               'money'      => rand(1,999),
               'before'     => rand(1,999),
               'after'      => rand(1,999),
               'memo'       => 'cs',
               'createtime' => time()
           ];

        }

        $res = (new UserMoneyLog())->insertAll($data);



        $res = Db::query("select * from ".$table." where id in ( "."select id from (select id from ".$table." limit 1,10 ".") as t"." ) ");

//        $res = UserMoneyLog::order('id desc')->paginate(15);

        dump($res);die;


        dump($this->buildPartitionSql('log','user_id'));die;




        $output->info('ok');

    }

    /** *
     * 构造获取总记录数及主键ID的sql子查询语句 * @param
     * $table 主表名称 * @param
     * $idKey 主键id字段名称 *
     * @param string $fields 其它字段名称,多个字段用英文逗号分隔 *
     * @param int $num 子表数量 * @param string $where 查询条件 * @return array
     */
    function buildPartitionSql($table,$idKey,$fields='',$num=1,$where='') {
        $countTable = [];
        $listTable = [];
        $fieldList = [$idKey];
        if ($fields) {
            $fieldList = array_merge($fieldList,explode(',',$fields));
            $fieldList = array_unique($fieldList);
        }
        $fieldStr = implode(',',$fieldList);
        for ($i = 0; $i < $num; $i++) {
            $countTable[] = sprintf('SELECT %s FROM %s_%s where 1=1 %s', $idKey, $table, ($i + 1), $where);
            $listTable[] = sprintf('SELECT %s FROM %s_%s where 1=1 %s', $fieldStr,$table, ($i + 1), $where);
        }
        $countTable = '( ' . implode(" UNION ", $countTable) . ') AS ' . $table;
        $listTable = '( ' . implode(" UNION ", $listTable) . ') AS ' . $table;
        $tables = ['countSql' => $countTable, 'listSql' => $listTable];
        return $tables;
    }

}