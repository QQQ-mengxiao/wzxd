<?php
/**
 * 用户佣金记录
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class member_commissionModel extends Model {
        /**
     * 取得佣金列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getMcomList($condition1 = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
         $a= $this->table('distribute_commission')->where($condition1)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
        return $a;
    }

    /**
     * 取得单条佣金信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getMcomInfo($condition2,$fields = 'commission') {
        $a = $this->table('distribute_commission')->where($condition2)->field($fields)->select();
        return $a;
    }

    /**
     * 取佣金信息总数
     * @param unknown $condition
     */
    public function getMcomCount($condition = array()) {
        return $this->table('distribute_commission')->where($condition)->count();
    }

    /**
     * 取日志总数
     * @param unknown $condition
     */
    public function getMcomLogCount($condition = array()) {
        return $this->table('distribute_commission')->where($condition)->count();
    }

    /**
     * 取得预存款变更日志列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getMcomLogList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->table('pd_log')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 删除佣金记录
     * @param unknown $condition
     */
    public function delMcom($condition) {
        return $this->table('distribute_commission')->where($condition)->delete();
    }
    /**
     * 编辑佣金记录
     * @param unknown $data
     * @param unknown $condition
     */
    public function editMcom($data,$condition = array()) {
        return $this->table('distribute_commission')->where($condition)->update($data);
    }
}
