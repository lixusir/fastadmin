<?php


namespace app\api\controller\home;


use app\api\controller\Base;

use app\admin\model\home\Package as PackageModel;

class Package extends Base
{

    public function index(){

        $package = PackageModel::field('title,price,unit,des,content')->select();


        $this->success('ok',$package);
    }
}