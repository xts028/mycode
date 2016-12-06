<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Model;

/**
 * Description of GoodsCategoryModel
 *
 * @author Administrator
 */
class GoodsCategoryModel extends \Think\Model{
    /**
     * 获取商品分类列表
     * @return array
     */
    public function getList() {
        return $this->order('lft')->select();
    }
}
