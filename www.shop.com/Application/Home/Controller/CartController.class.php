<?php

/**
 * @link http://blog.kunx.org/.
 * @copyright Copyright (c) 2016-11-20 
 * @license kunx-edu@qq.com.
 */

namespace Home\Controller;

/**
 * Description of CartController
 *
 * @author kunx <kunx-edu@qq.com>
 */
class CartController extends \Think\Controller {

    /**
     * @var \Home\Model\CartModel 
     */
    private $_model;

    protected function _initialize() {
        $titles = [
            'flow1'=>'购物车列表',
            'flow2'=>'结算',
            'flow3'=>'成功',
        ];
        $this->assign('title', isset($titles[ACTION_NAME])?$titles[ACTION_NAME]:'啊咿呀哟');
        $this->_model = D('Cart');
    }

    public function add2cart($goods_id, $amount) {
        //判断用户是否登录了,如果登录了,存入mysql,否则存入cookie
        if ($user_id = get_user_id()) {
            //判断数据库中是不是已经有了此商品
            $cond      = [
                'goods_id'  => $goods_id,
                'member_id' => $user_id
            ];
            //以前已经将该商品放入购物车了,就加数量
            if ($db_amount = $this->_model->where($cond)->getField('amount')) {
                $this->_model->where($cond)->setInc('amount', $amount);
            } else { //以前购物车中没有此商品,就加记录
                $data = [
                    'goods_id'  => $goods_id,
                    'member_id' => $user_id,
                    'amount'    => $amount,
                ];
                $this->_model->add($data);
            }
        } else {
            $cart = cookie('CART_INFO');
            //购物车中已经有了此商品,就加数量
            if (isset($cart[$goods_id])) {
                $cart[$goods_id] += $amount;
            } else {
                $cart[$goods_id] = $amount;
            }
            //将数据保存到cookie中
            cookie('CART_INFO', $cart, 604800);
        }
        //跳转到购物车页面,避免重复提交.
        $this->success('添加成功', U('flow1'));
    }

    //购物车列表
    public function flow1() {
        $this->assign($this->_model->getCartList());

        $this->display();
    }

    /**
     * 填写详细的订单信息
     */
    public function flow2() {
        //判断是否登录了
        if(!$user_id = get_user_id()){
            cookie('referer',__SELF__);
            $this->error('本店不招待无名之辈',U('Member/login'));
        }
        //获取收货地址
        $addresses = D('Address')->getList();
        $this->assign('addresses',$addresses);
        //获取数据展示
        $this->assign($this->_model->getCartList());
        $this->display();
    }

    /**
     * 成功提示
     */
    public function flow3() {
        $this->display();
    }

    public function changeAmount($goods_id, $amount) {
        //是否登录
        if ($user_id = get_user_id()) {
            $this->_model->changeAmount($goods_id,$amount);
        } else {
            $cart = cookie('CART_INFO');
            if ($amount == 0) {
                unset($cart[$goods_id]);
            } else {
                $cart[$goods_id] = $amount;
            }
            //将数据保存到cookie中
            cookie('CART_INFO', $cart, 604800);
        }
//        $this->error('修改失败');
        $this->success('修改成功');
    }

}
