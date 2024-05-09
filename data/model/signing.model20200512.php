<?php
/**
 * 预约信息
 **/

defined('In718Shop') or exit('Access Invalid!');

class signingModel extends Model
{



    /**
     * 取预约信息记录
     *
     * @param
     * @return array
     */
    public function getSigningList($condition = array(), $page = '', $fields = '*', $limit = '')
    {
        $result = $this->table('goods_reservation')->field($fields)->where($condition)->page($page)->limit($limit)->order('id desc')->select();
        return $result;
    }
    public function getSigningCount($condition)
    {
        return $this->table('goods_reservation')->where($condition)->count();
    }
   

   
    /**
     * 取一条记录
     *
     * @param
     * @return array
     */
    public function getSigningInfo($condition = array(), $fields = '*')
    {
        return $this->table('goods_reservation')->where($condition)->field($fields)->find();
    }

    

}
