<?php

/**
 * @link http://blog.kunx.org/.
 * @copyright Copyright (c) 2016-11-20 
 * @license kunx-edu@qq.com.
 */

namespace Home\Model;

/**
 * Description of LocationsModel
 *
 * @author kunx <kunx-edu@qq.com>
 */
class LocationsModel extends \Think\Model {

    //put your code here
    public function getListByParentId($parent_id = 0) {
        return $this->where(['parent_id' => $parent_id])->select();
    }

}
