<?php

/**
 * @link http://blog.kunx.org/.
 * @copyright Copyright (c) 2016-11-21 
 * @license kunx-edu@qq.com.
 */

namespace Home\Controller;

/**
 * Description of OrderController
 *
 * @author kunx <kunx-edu@qq.com>
 */
class OrderController extends \Think\Controller {

    /**
     * @var \Home\Model\OrderModel 
     */
    private $_model;

    protected function _initialize() {
        $this->_model = D('Order');
    }

    //put your code here
    public function create() {
        if (!IS_POST) {
            $this->error('来路不合法');
        }

        //收集数据
        if ($this->_model->create() === false) {
            $this->error(get_error($this->_model));
        }
        //执行添加
        if ($this->_model->addOrder() === false) {
            $this->error(get_error($this->_model));
        }
        //跳转到成功提示页面
        $this->success('创建成功', U('Cart/flow3'));
    }

    public function pay($id) {
        //获取订单信息
        $order = $this->_model->where(['member_id' => get_user_id(), 'status' => \Home\Model\OrderModel::STATUS_WAIT_PAY])->find($id);
        if (empty($order)) {
            $this->error('无符合的订单');
        }
        //判断支付方式
        switch ($order['payment_id']) {
            case 1:
                $this->_wxpay($order);
                break;
        }
        //调用具体的支付代码
    }

    /**
     * 容易出现问题的地方:
     * 1.订单多久没有支付,微信方就过期
     * 2.订单已经支付
     * 3.如果需要使用自己的二维码接口,需要转化为没有://伪协议的字符串,可以使用base64编码
     * @param type $order
     */
    private function _wxpay($order) {
        //微信支付


        ini_set('date.timezone', 'Asia/Shanghai');

        vendor('Wxpay.Autoloader'); // "../lib/WxPay.Api.php";
        /**
         * 流程：
         * 1、组装包含支付信息的url，生成二维码
         * 2、用户扫描二维码，进行支付
         * 3、确定支付之后，微信服务器会回调预先配置的回调地址，在【微信开放平台-微信支付-支付配置】中进行配置
         * 4、在接到回调通知之后，用户进行统一下单支付，并返回支付信息以完成支付（见：native_notify.php）
         * 5、支付完成之后，微信服务器会通知支付成功
         * 6、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
         */
        $notify = new \NativePay();

        //模式二
        /**
         * 流程：
         * 1、调用统一下单，取得code_url，生成二维码
         * 2、用户扫描二维码，进行支付
         * 3、支付完成之后，微信服务器会通知支付成功
         * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
         */
        $input       = new \WxPayUnifiedOrder();
        $input->SetBody("啊咿呀哟商城订单:" . $order['id']);
//        $input->SetAttach("test");
        $input->SetOut_trade_no(\WxPayConfig::MCHID . 'test_' . $order['id'] . mt_rand(1000, 9999));
        $total_price = ($order['total_money'] + $order['delivery_price']) * 100;
//        $total_price = (int)$total_price;
        $input->SetTotal_fee($total_price);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 60000));
        $input->SetGoods_tag("test");
        $url         = U('Order/wxpayNotify', '', true, true);
        $input->SetNotify_url($url);
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($order['id']);
        $result      = $notify->GetPayUrl($input);
//        var_dump($result);
//        exit;
        $url2        = $result["code_url"];
//        $url2 = urlencode($result["code_url"]);
        $url2        = base64_encode($url2);
        $url         = U('qrcode', ['text' => $url2]);

        //微信二维码接口
//        echo <<<EOF
//        <img alt="模式二扫码支付" src="http://paysdk.weixin.qq.com/example/qrcode.php?data=$url2" style="width:150px;height:150px;"/>
//EOF;
//        exit;
        echo <<<EOF
        <img alt="模式二扫码支付" src="$url" style="width:150px;height:150px;"/>
EOF;
    }

    public function qrcode($text) {
        vendor('phpqrcode');
        $text = base64_decode($text);
        \QRcode::png($text);
    }

    /**
     * 释放超时订单
     */
    public function free() {
        set_time_limit(0);
        while (true) {
            echo date('[Y-m-d H:i:s] :') . "ready\n";
            $this->_model->cancelOrder();
            echo date('[Y-m-d H:i:s] :') . "done\n" . str_repeat('=', 40) . "\n";
            sleep(10);
        }
    }

}
