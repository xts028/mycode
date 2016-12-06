<?php

/**
 * @link http://blog.kunx.org/.
 * @copyright Copyright (c) 2016-11-20 
 * @license kunx-edu@qq.com.
 */

namespace Home\Controller;

/**
 * Description of AddressController
 *
 * @author kunx <kunx-edu@qq.com>
 */
class AddressController extends \Think\Controller{
    //put your code here
    
    /**
     * @var \Home\Model\AddressModel
     */
    private $_model;
    
    protected function _initialize() {
        $this->_model = D('Address');
    }
    
    /**
     * 添加收货地址
     */
    public function add() {
        if(IS_POST){
            if($this->_model->create()===false){
                $this->error(get_error($this->_model));
            }
            if($this->_model->addAddress()===false){
                $this->error(get_error($this->_model));
            }
            $this->success('添加成功',U('Index/address'));
        }else{
            $this->error('来路不合法',U('Index/address'));
        }
    }
}
