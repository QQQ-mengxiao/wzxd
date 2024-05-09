<?php
/**
 * 预存款
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class ziti_balanceModel extends Model {

    /**
     * 添加自提点余额账户
     * @param array $data
     */
    public function addZitiBalance($data) {
        return $this->table('ziti_balance')->insert($data);
    }

    /**
     * 编辑
     * @param unknown $data
     * @param unknown $condition
     */
    public function editZitiBalance($data,$condition = array()) {
        return $this->table('ziti_balance')->where($condition)->update($data);
    }

    /**
     * 取得单条信息
     */
    public function getZitiBalanceInfo($condition = array(), $fields = '*') {
        return $this->table('ziti_balance')->where($condition)->field($fields)->find();
    }

    /**
     * 取列表
     */
    public function getZitiBalanceList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->table('ziti_balance')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

}
