<?php

/**
 * @link http://blog.kunx.org/.
 * @copyright Copyright (c) 2016-11-20 
 * @license kunx-edu@qq.com.
 */

namespace Home\Controller;

/**
 * Description of LocationsController
 *
 * @author kunx <kunx-edu@qq.com>
 */
class LocationsController extends \Think\Controller{
    /**
     * @var \Home\Model\LocationsModel
     */
    private $_model;
    
    protected function _initialize() {
        $this->_model = D('Locations');
    }
    public function getListByParentId($parent_id) {
        if(IS_AJAX){
            $list = $this->_model->getListByParentId($parent_id);
            $this->ajaxReturn($list);
        }
    }
    
    
}
