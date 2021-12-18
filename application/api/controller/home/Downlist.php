<?php


namespace app\api\controller\home;


use app\api\controller\Base;
use app\admin\model\home\Downlist as DModel;

class Downlist extends Base
{

    public function index(){

        $down_list = DModel::field('title,content,avatar')->select();

        $config = config('site');

        $resutl_data = [
            'down_list'     =>
                [
                    'title'     => '服务内容',
                    'list'      => $down_list,
                ],
            'images'        =>
                [
                    'title'     => '下载图片LOGO',
                    'list'      =>
                        [
                            'background'    => $config['down_background'],
                            'img'           => $config['down_img']
                        ]
                ]
        ];
        $this->success('ok',$resutl_data);
    }
}