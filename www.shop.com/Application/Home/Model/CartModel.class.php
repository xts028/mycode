<?php

/**
 * @link http://blog.kunx.org/.
 * @copyright Copyright (c) 2016-11-20 
 * @license kunx-edu@qq.com.
 */

namespace Home\Model;

/**
 * Description of CartModel
 *
 * @author kunx <kunx-edu@qq.com>
 */
class CartModel extends \Think\Model{
    
    /**
     * 购物车详情.
     * 1.购物车详情展示,需要知道购物车数据
     * 2.通过购物车中的商品id,获取商品详情
     * 3.通过单价和数量,计算金额
     * 4.返回数据给控制器,由控制器交给视图展示
     * @return array
     */
    public function getCartList() {
        //获取购物车数据
        //是否登录
        $total_price = 0.00;
        if ($user_id = get_user_id()) {
            //获取出来所有的购物车列表
            //select * from shop_cart where member_id=1
            //$tmp =[];
            //foreach($list as $key=>$value){
            // $tmp[$value['goods_id']] = $value['amount'];
            //}
            $cart = $this->where(['member_id'=>$user_id])->getField('goods_id,amount');
        } else {//未登录
            $cart      = cookie('CART_INFO');
        }
        
        //获取商品id列表
        $goods_ids = array_keys($cart);
        if (empty($goods_ids)) {
            $cart_detail = [];
        } else {
            $cart_detail = D('Goods')->getGoodsInfoListByGoodsIds($goods_ids);
            foreach ($cart_detail as $key => $value) {
                $value['amount']    = $cart[$key];
                $value['sub_total'] = money_format($value['amount'] * $value['shop_price']);
                $cart_detail[$key]  = $value;
                $total_price+=$value['sub_total'];
            }
        }
        $total_price = money_format($total_price);
        return [
            'total_price'=>$total_price,
            'cart_detail'=>$cart_detail,
        ];
    }
    
    /**
     * 清除购物车
     * @return type
     */
    public function cleanUp() {
        return $this->where(['member_id'=>  get_user_id()])->delete();
    }
    
    public function changeAmount($goods_id,$amount) {
        //判断有没有
        $cond = ['goods_id'=>$goods_id,'member_id'=>  get_user_id()];
        //修改购买数量
        if($amount){
            $this->where($cond)->setField('amount',$amount);
        }else{ //删除购买
            $this->where($cond)->delete();
        }
    }
    
    /**
     * 在登录的时候调用.
     * 将cookie购物车保存到数据库中
     * @return boolean
     */
    public function cookie2db() {
        //获取cookie中的购物车
        $cart = cookie('CART_INFO');
        if(empty($cart)){
            return true;
        }
        $data = [];
        $user_id = get_user_id();
        //删除已经存在的商品
        $goods_ids = array_keys($cart);
        $this->where(['goods_id'=>['in',$goods_ids],'member_id'=>$user_id])->delete();
        foreach($cart as $goods_id=>$amount){
            $data[] = [
                'goods_id'=>$goods_id,
                'amount'=>$amount,
                'member_id'=>  $user_id,
            ];
        }
        cookie('CART_INFO',null);
        return $this->addAll($data);
    }
    
}
