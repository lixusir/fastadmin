<?php


namespace app\index\controller;


use app\common\controller\Frontend;

class Game extends Frontend

{
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    public function index(){

        return $this->view->fetch();

    }
}