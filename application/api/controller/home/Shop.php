<?php


namespace app\api\controller\home;


use app\api\controller\Base;

use app\admin\model\home\Shop as ShopModel;

class Shop extends Base
{

    public function index(){

        $shop = ShopModel::field('avatar,title,des,content')->select();

        $this->success('ok',$shop);
    }
}