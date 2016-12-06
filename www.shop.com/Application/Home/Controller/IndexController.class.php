<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller {

    protected function _initialize() {

        $goods_categories = D('GoodsCategory')->getList();
        //取出所有的商品分类
        $this->assign('goods_categories', $goods_categories);

        //取出帮助文章列表
        $help_articles = D('ArticleCategory')->getHelpArticleList();
        $this->assign($help_articles);


        $meta_titles = [
            'index'       => '啊咿呀哟母婴商城',
            'goods'       => '商品详情',
            'address'     => '收货地址管理',
            'addressEdit' => '修改收货地址',
        ];
        $this->assign('meta_title', isset($meta_titles[ACTION_NAME]) ? $meta_titles[ACTION_NAME] : '啊咿呀哟母婴商城');
    }

    /**
     * 商城首页
     */
    public function index() {
        $this->assign('show_category', true);

        //获取多个推荐类型的商品列表
        $goods_model = D('Goods');
        $data        = [
            'is_best' => $goods_model->getListByGoodsStatus(1),
            'is_new'  => $goods_model->getListByGoodsStatus(2),
            'is_hot'  => $goods_model->getListByGoodsStatus(4),
        ];
        $this->assign($data);
        $user_info   = session('USER_INFO');
        $this->assign('user_info', $user_info);
        $this->display();
    }

    /**
     * 商品详情页
     */
    public function goods($id) {
        //获取商品详情
        $row = D('Goods')->getGoodsInfo($id);
        if (empty($row)) {
            $this->redirect('Index/index');
        }
        $this->assign('row', $row);
        $this->assign('meta_title', $row['name'] . '  -商品详情');
        $this->display();
    }

    /**
     * 展示用户的地址本.
     */
    public function address() {
        //获取收货地址
        /**
         * id  name tel province city area detail_address member_id sort is_default
         */
        $addresses = D('Address')->getList();
        $this->assign('addresses',$addresses);
        //获取省级菜单
        $provinces = D('Locations')->getListByParentId();
        $this->assign('provinces',$provinces);
        $this->display();
    }
    
    public function addressEdit($id) {
        //获取收货地址详情
        $row = D('Address')->find($id);
        $this->assign('row',$row);
        //获取省级菜单
        $provinces = D('Locations')->getListByParentId();
        $this->assign('provinces',$provinces);
        $this->display();
    }

    /**
     * 获取用户订单列表
     */
    public function orders() {
        //获取用户订单
        $orders = D('Order')->getList();
        $this->assign('orders',$orders);
        $this->assign('statuses',  \Home\Model\OrderModel::$statuses);
        $this->display();
    }
}
