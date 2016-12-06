<?php

/**
 * @link http://blog.kunx.org/.
 * @copyright Copyright (c) 2016-11-20 
 * @license kunx-edu@qq.com.
 */

namespace Home\Model;

/**
 * Description of AddressModel
 *
 * @author kunx <kunx-edu@qq.com>
 */
class AddressModel extends \Think\Model{
    //put your code here
    
    public function addAddress() {
        $user_id = get_user_id();
        if(!empty($this->data['is_default'])){
            //将其他的地址都取消默认
            $this->where(['member_id'=>$user_id])->setField('is_default',0);
        }
        $this->data['member_id'] = $user_id;
        return $this->add();
    }
    
    public function getList() {
        return $this->where(['member_id'=>  get_user_id()])->order('is_default desc')->select();
    }
    
    public function getAddressInfoById($id) {
        return $this->where(['member_id'=>  get_user_id()])->find($id);
    }
    
    
}
