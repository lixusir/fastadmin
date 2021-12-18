<?php

/**
 * 首页数据
 */
namespace app\api\controller\home;

use app\admin\model\Banner;
use app\admin\model\home\Advantage;
use app\admin\model\home\Cooper;
use app\admin\model\home\Customer;
use app\admin\model\home\Des;
use app\admin\model\home\Service;
use app\api\controller\Base;
use think\Request;

class Index extends Base
{

    public function init(){

        //公司详情
        $config = config('site');

        $result_data = [
            'name'              => $config['name'],
            'logo'              => $config['logo'],
            'about_us'          => $config['about_us'],
            'auth_certifycation'=> $config['auth_certifycation'],
            'consuliting'       => $config['consuliting'],
            'cooperation'       => $config['cooperation'],
            'contact'           => $config['contact'],
            'requery_url'       => Request::instance()->domain()
        ];

        $this->success('ok',$result_data);
    }

    public function index(){

        //轮播图
        $banner = Banner::field('title,link,thumb')->order('id desc')->select();

        //行业优势
        $advantage = Advantage::field('title,logo')->order('id desc')->select();

        //企业高效之路
        $dec = Des::field('title,des,thumb')->order('id desc')->select();

        //企业服务
        $service = Service::field('title,logo,dec')->order('id desc')->select();

        //客户评价
        $customer = Customer::field('username,avatar,des')->order('id desc')->select();

        //选择我们
        $cooper = Cooper::field('title,avatar')->order('id')->select();

        $result_data = [
            'banner'        =>
                    [
                        'title' => '轮播图',
                        'list'  => $banner,
                    ],
            'advantage'     =>
                    [
                        'title' => '行业优势',
                        'list'  => $advantage
                    ],
            'dec'           =>
                    [
                        'title' => '企业高校之路',
                        'list'  => $dec,
                    ],
            'service'       =>
                    [
                        'title' => '企业服务',
                        'list'  => $service
                    ],
            'customer'      =>
                    [
                        'title' => '客户评价',
                        'list'  => $customer
                    ],
            'cooper'        =>
                    [
                        'title' => '谁选择了我们?',
                        'list'  => $cooper
                    ]
        ];


        $this->success('ok',$result_data);
    }

}