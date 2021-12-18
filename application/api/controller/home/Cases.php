<?php


namespace app\api\controller\home;


use app\admin\model\home\Cooper;
use app\admin\model\home\Feedback;
use app\api\controller\Base;

class Cases extends Base
{

    public function index(){

        $feedback = Feedback::field('username,avatar,des')->order('id desc')->select();

        $cooper = Cooper::field('title,avatar')->order('id desc')->select();


        $result_data = [
            'feedback'      =>
                    [
                        'title' => '用户反馈',
                        'list'  => $feedback
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