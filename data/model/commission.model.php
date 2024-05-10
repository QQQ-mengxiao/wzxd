<?php
/**
 * 分销佣金
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class commissionModel extends Model {
        /**
     * 取得佣金列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdRechargeList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
         $a= $this->table('distribute_commission')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
        return $a;
    }
    /**
     * 取得佣金列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getCommissionList($condition = array(),$fields = '*') {
         $a= $this->table('distribute_commission')->where($condition)->field($fields)->select();
        return $a;
    }

        /**
     * 取得佣金列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
/*    public function getCommissionList($order_sn,$fields = '*') {
         $a= $this->table('distribute_commission')->where(array('order_sn'=>$order_sn))->field($fields)->select();
        return $a;
    }*/

    /**
     * 编辑
     * @param unknown $data
     * @param unknown $condition
     */
    public function editPdRecharge($data,$condition = array()) {
        return $this->table('distribute_commission')->where($condition)->update($data);
    }

    /**
     * 取得单条佣金信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getPdRechargeInfo($condition = array(), $fields = '*') {
        return $this->table('distribute_commission')->where($condition)->field($fields)->find();
    }

    /**
     * 取佣金信息总数
     * @param unknown $condition
     */
    public function getPdRechargeCount($condition = array()) {
        return $this->table('distribute_commission')->where($condition)->count();
    }

    /**
     * 取提现单信息总数
     * @param unknown $condition
     */
    public function getPdCashCount($condition = array()) {
        return $this->table('distribute_commission')->where($condition)->count();
    }

    /**
     * 取日志总数
     * @param unknown $condition
     */
    public function getPdLogCount($condition = array()) {
        return $this->table('distribute_commission')->where($condition)->count();
    }

    /**
     * 取得预存款变更日志列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdLogList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->table('pd_log')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 删除佣金提现记录
     * @param unknown $condition
     */
    public function delWdRecharge($condition) {
        return $this->table('distribute_commission')->where($condition)->delete();
    }

    /**
     * 取得佣金提现列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdCashList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->table('distribute_commission')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 添加佣金提现记录
     * @param array $data
     */
    public function addWdCash($data) {
        return $this->table('distribute_commission')->insert($data);
    }

    /**
     * 编辑佣金记录
     * @param unknown $data
     * @param unknown $condition
     */
    public function editWdCash($data,$condition = array()) {
        return $this->table('distribute_commission')->where($condition)->update($data);
    }
        /**
     * 编辑佣金记录 MJQ
     * @param unknown $data
     * @param unknown $condition
     */
    public function editcomCash($data,$condition = array()) {
         return $this->table('distribute_commission')->where($condition)->update($data); 
    }

    /**
     * 取得单条佣金信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getWdCashInfo($condition = array(), $fields = '*') {
        return $this->table('distribute_commission')->where($condition)->field($fields)->find();
    }
    /**
     * 取得单条提现信息  MJQ 
     * @param unknown $condition
     * @param string $fields
     */
    public function getcomInfo($condition = array(), $fields = '*') {
        return $this->table('distribute_commission')->where($condition)->field($fields)->find();
    }
    /**
     * 删除提现记录
     * @param unknown $condition
     */
    public function delWdCash($condition) {
        return $this->table('distribute_commission')->where($condition)->delete();
    }
}
