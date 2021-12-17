<?php


namespace app\api\controller;


use addons\wanlshop\library\WanlChat\WanlChat;
use app\common\controller\Api;
use app\common\library\Sms;
use fast\Random;
use think\Env;
use think\Validate;

class Login extends Api{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
        //WanlChat 即时通讯调用
        $this->wanlchat = new WanlChat();
        $this->auth->setAllowFields(['id','username','nickname','mobile','avatar','level','gender','birthday','bio','money','score','invite_code','successions','maxsuccessions','prevtime','logintime','loginip','jointime']);
    }

    /**
     * 会员登录
     * @ApiMethod   (POST)
     * @param string $account  账号
     * @param string $password 密码
     */
    public function login()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isPost()) {
            $account = $this->request->post('account');
            $password = $this->request->post('password');
            $client_id = $this->request->post('client_id');
            if (!$account || !$password) {
                $this->error(__('Invalid parameters'));
            }
            $ret = $this->auth->login($account, $password);
            if ($ret) {
                if($client_id){
                    $this->wanlchat->bind($client_id, $this->auth->id);
                }
                $data = [
                    'userinfo' => $this->auth->getUserinfo(),
                    'statistics' => $this->statistics()
                ];
                $this->success(__('Logged in successful'), $data);
            } else {
                $this->error($this->auth->getError());
            }
        }
        $this->error(__('非法请求'));
    }

    /**
     * 手机验证码登录
     *
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        $client_id = $this->request->post('client_id');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!isMobilePhone($mobile)) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != '1') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            if($client_id){
                $this->wanlchat->bind($client_id, $this->auth->id);
            }
            $data = [
                'userinfo' => $this->auth->getUserinfo(),
                'statistics' => $this->statistics()
            ];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     * @param string $mobile   手机号
     * @param string $code   验证码
     */
    public function register()
    {
        $mobile = $this->request->request('mobile');
        $code = $this->request->request('code');
        $client_id = $this->request->post('client_id');
        //$password = $this->request->request('password');
        //$email = $this->request->request('email');
        $post = request()->post();
        $validate = new Validate([
            'mobile'        => 'require|length:11,11',
            'code'          => 'require',
            'password'      => 'require',
        ],[],[
            'mobile'        =>'手机号',
            'code'          =>'验证码',
            'password'      =>'登录密码',
        ]);
        if (!$validate->check($post)) {
            $this->error($validate->getError());
        }
        if ($mobile && !isMobilePhone($mobile)) {
            $this->error(__('Mobile is incorrect'));
        }
        $ret = Sms::check($mobile, $code, 'register');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        $ret = $this->auth->register($mobile, request()->post());
        if ($ret) {
            if($client_id){
                $this->wanlchat->bind($client_id, $this->auth->id);
            }
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }


    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 数据统计 - 内部使用，开发者不要调用
     */
    public function statistics()
    {
        $user_id = $this->auth->id;
        // 查询订单
        $order = model('app\api\model\wanlshop\Order')
            ->where('user_id', $user_id)
            ->select()->toArray();
        $orderCount = array_count_values(array_column($order,'state'));
        // 物流列表
        $logistics = [];
        foreach ($order as $value)
        {
            if($value['state'] >=3 && $value['state'] <=6){
                //需要查询的订单
            }
        }
        // 查询动态 、收藏夹、关注店铺、足迹、红包卡券
        $data = [
            'dynamic' => [
                'collection' => model('app\api\model\wanlshop\GoodsFollow')->where('user_id', $user_id)->count(),
                'concern' => model('app\api\model\wanlshop\ShopFollow')->where('user_id', $user_id)->count(),
                'footprint' => model('app\api\model\wanlshop\Record')->where('user_id', $user_id)->count(),
                'coupon' => model('app\api\model\wanlshop\CouponReceive')->where(['user_id' => $user_id, 'state' => '1'])->count(),
                'accountbank' => model('app\api\model\wanlshop\PayAccount')->where('user_id', $user_id)->count()
            ],
            'order' => [
                'pay' => isset($orderCount[1]) ? $orderCount[1] : 0,
                'delive' => isset($orderCount[2]) ? $orderCount[2] : 0,
                'receiving' => isset($orderCount[3]) ? $orderCount[3] : 0,
                'evaluate' => isset($orderCount[4]) ? $orderCount[4] : 0,
                'customer' => model('app\api\model\wanlshop\Refund')->where(['state' => ['in','1,2,3,6'], 'user_id' => $this->auth->id])->count()
            ],
            'logistics' => $logistics
        ];
        return $data;
    }
}
