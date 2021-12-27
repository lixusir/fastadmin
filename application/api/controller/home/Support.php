<?php


namespace app\api\controller\home;


use app\api\controller\Base;
use app\common\model\Config;

class Support extends Base
{

    public function index(){



        $this->success('ok',config('site')['support']);
    }
}