<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Model;

/**
 * Description of GoodsModel
 *
 * @author Administrator
 */
class GoodsModel extends \Think\Model{
    /**
     * 获取指定推荐状态的商品列表
     * @param integer $goods_status 推荐状态值.
     * @return array
     */
    public function getListByGoodsStatus($goods_status) {
        return $this->where('goods_status & '. $goods_status)->order('sort')->select();
    }
    
    /**
     * 获取商品信息,包括商品详情和相册
     * @param type $id
     * @return type
     */
    public function getGoodsInfo($id) {
        $row = $this->where(['status'=>1,'is_on_sale'=>1])->find($id);
        $row['brand_name'] = M('Brand')->where(['id'=>$row['brand_id']])->getField('name');
        //获取商品详情
        $row['content']=M('GoodsIntro')->where(['goods_id'=>$row['id']])->getField('content');
        //获取商品相册
        $row['gallery']=M('GoodsGallery')->where(['goods_id'=>$row['id']])->getField('path',true);
        return $row;
    }
    
    /**
     * 获取商品的信息,通过商品id数组.
     * @param array $goods_ids 商品id数组.
     * @return type
     */
    public function getGoodsInfoListByGoodsIds(array $goods_ids) {
        return $this->where(['id'=>['in',$goods_ids],'status'=>1,'is_on_sale'=>1])->getField('id,name,logo,shop_price');
    }
    
    /**
     * [
     *  ''=>[
     *      logo:
     *      shop_price:
     *      name
     *      amount
     *      sub_total
     *  ]
     * ]
     */
    
    public function cutdownStock($id,$stock) {
        return $this->where(['id'=>$id])->setDec('stock', $stock);
    }
}
