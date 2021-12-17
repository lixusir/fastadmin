<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Config;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }

    public function getConfig(){
        $name = $this->request->param('name');
        if(!in_array($name,['h5_url'])){
            $this->error('参数错误');
        }
        $value = Config::getValue($name,'basic');
        $this->success('请求成功',$value);
    }
}
