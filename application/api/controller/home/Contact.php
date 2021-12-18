<?php


namespace app\api\controller\home;


use app\api\controller\Base;

class Contact extends Base
{


    public function index(){

        $config = config('site');

        $result_data = [
            'phone'        => $config['phone'],
            'fax'          => $config['fax'],
            'address'      => $config['address'],
            'contact_code' => $config['contact_code'],
        ];

        $this->success('ok',$result_data);
    }
}