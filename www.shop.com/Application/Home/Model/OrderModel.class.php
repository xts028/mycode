<?php

/**
 * @link http://blog.kunx.org/.
 * @copyright Copyright (c) 2016-11-21 
 * @license kunx-edu@qq.com.
 */

namespace Home\Model;

/**
 * Description of OrderModel
 *
 * @author kunx <kunx-edu@qq.com>
 */
class OrderModel extends \Think\Model {

    const STATUS_CANCEL       = 0;
    const STATUS_WAIT_PAY     = 1;
    const STATUS_WAIT_SEND    = 2;
    const STATUS_WAIT_RECEIVE = 3;
    const STATUS_FINISH       = 4;

    public static $statuses = [
        self::STATUS_CANCEL       => '已取消',
        self::STATUS_WAIT_PAY     => '待支付',
        self::STATUS_WAIT_SEND    => '待发货',
        self::STATUS_WAIT_RECEIVE => '待收货',
        self::STATUS_FINISH       => '已完成',
    ];
    protected $_map         = array(
        'payment' => 'payment_id',
    );

    /**
     * TODO::自动验证
     */
    protected $_auto = [
        ['create_time', NOW_TIME],
        ['status', self::STATUS_WAIT_PAY],
        ['member_id', 'get_user_id', self::MODEL_INSERT, 'function']
    ];

    //put your code here
    public function addOrder() {
        $this->startTrans();
        //收集数据
        //获取详细的收货地址
        $address_id                   = I('post.address_id');
        $address                      = D('Address')->getAddressInfoById($address_id);
        $this->data['name']           = $address['name'];
        $this->data['tel']            = $address['tel'];
        $this->data['province_name']  = $address['province_name'];
        $this->data['city_name']      = $address['city_name'];
        $this->data['area_name']      = $address['area_name'];
        $this->data['detail_address'] = $address['detail_address'];

        //获取配送方式信息
        $delivery_id = $this->data['delivery_id'];
        switch ($delivery_id) {
            case 1:
                $this->data['delivery_price'] = 25.00;
                $this->data['delivery_name']  = '顺丰速递';
                break;
            case 2:
                $this->data['delivery_price'] = 10.00;
                $this->data['delivery_name']  = '天天快递';
                break;
            case 3:
                $this->data['delivery_price'] = 0.00;
                $this->data['delivery_name']  = '自提';
                break;
        }

        //获取支付方式信息
        $payment_id = $this->data['payment_id'];
        switch ($payment_id) {
            case 1:
                $this->data['payment_name'] = '微信支付';
                break;
            case 2:
                $this->data['payment_name'] = '银联在线';
                break;
            case 3:
                $this->data['payment_name'] = '邮局汇款';
                break;
            case 4:
                $this->data['payment_name'] = '货到付款';
                break;
        }

        //获取订单总金额
        $cart = D('Cart')->getCartList();
        if (empty($cart['cart_detail'])) {
            $this->error = '客官,不买点什么?';
            $this->rollback();
            return false;
        }

        $this->data['total_money'] = $cart['total_price'];
        //检查库存是否充足
        //where id=100 and stock>5 
        //select count(*) from goods where (id=1 and stock>=5) or (id=2 and stock>=2)
        $cond                      = [
            '_logic' => 'OR',
        ];
        foreach ($cart['cart_detail'] as $goods) {
            $cond[] = [
                'id'    => $goods['id'],
                'stock' => ['egt', $goods['amount']],
            ];
        }
        $goods_model = M('Goods');
        $count       = $goods_model->where($cond)->count();
        if ($count != count($cart['cart_detail'])) {
            $this->error = '库存不足';
            $this->rollback();
            return false;
        } else {
            foreach ($cart['cart_detail'] as $goods) {
                if ($goods_model->where(['id' => $goods['id']])->setDec('stock', $goods['amount']) === false) {
                    $this->error = '下单失败';
                    $this->rollback();
                    return false;
                }
            }
        }
//        $cond = [
//            [
//                'id'=>''
//            ]
//        ];
        //扣库存
        //创建订单,获取订单编号
        if (!$oid = $this->add()) {
            $this->rollback();
            return false;
        }

        //创建订单详情信息
        $order_detail = [];
        foreach ($cart['cart_detail'] as $goods) {
            $order_detail[] = [
                'oid'         => $oid,
                'goods_id'    => $goods['id'],
                'goods_name'  => $goods['name'],
                'logo'        => $goods['logo'],
                'shop_price'  => $goods['shop_price'],
                'amount'      => $goods['amount'],
                'total_price' => $goods['sub_total'],
            ];
        }
        if (M('OrderDetail')->addAll($order_detail) === false) {
            $this->error = '创建订单失败';
            $this->rollback();
            return false;
        }

        //清空购物车
        if (D('Cart')->cleanUp() === false) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 获取当前用户的订单列表
     * @return type
     */
    public function getList() {
        $list = $this->where(['member_id' => get_user_id()])->order('create_time desc')->select();
        foreach ($list as $key => $value) {
            $value['goods_list'] = M('OrderDetail')->where(['oid' => $value['id']])->select();
            $list[$key]          = $value;
        }
        return $list;
    }

    /**
     * 将超时订单库存释放.
     */
    public function cancelOrder() {
        //找到超时的订单
        $cond = [
            'status'      => self::STATUS_WAIT_PAY,
            //create_time   <  now_time-900
            'create_time' => ['lt', NOW_TIME - 900],
        ];
        $oids = $this->where($cond)->getField('id', true);
        if (empty($oids)) {
            return true;
        }

        //获取订单详情,以便还原库存
        $list = M('OrderDetail')->where(['oid' => ['in', $oids]])->getField('id,goods_id,amount');
        $data = []; //键名是商品id,键值是数量
        foreach ($list as $goods) {
            if (isset($data[$goods['goods_id']])) {
                $data[$goods['goods_id']]+=$goods['amount'];
            } else {
                $data[$goods['goods_id']] = $goods['amount'];
            }
        }
        $goods_model = M('Goods');
        foreach ($data as $goods_id => $amount) {
            $goods_model->where(['id' => $goods_id])->setInc('stock', $amount);
        }
        //把订单状态改为0,表示已取消
        $this->where(['id' => ['in', $oids]])->setField('status', self::STATUS_CANCEL);
    }

}
