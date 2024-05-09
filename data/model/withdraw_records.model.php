<?php
/**
 * 提现记录管理
 */
defined('In718Shop') or exit('Access Invalid!');

class withdraw_recordsModel extends Model{
    public function __construct(){
        parent::__construct('withdraw_records');
    }

    /**
     * 新增记录数据
     *
     */
    public function addWithdrawrecords($insert) {
        $result = $this->table('withdraw_records')->insert($insert);
        return $result;
    }

    /**
     * 更新记录数据
     */
    public function editWithdrawrecords($update, $withdraw_recordid){
        $condition['withdraw_recordid'] = $withdraw_recordid;
        $result = $this->table('withdraw_records')->where($condition)->update($update);
        return $result;
    }

    /**
     * 获取一条记录数据
     */
    public function getWithdrawrecordsInfo($condition, $field = '*'){
        return $this->table('withdraw_records')->field($field)->where($condition)->find();
    }

    /**
     * 获取记录列表
     */
    public function getWithdrawrecordsList($condition, $page = 0, $field = '*', $withdraw_recordid = '', $limit = 0, $count = 0) {
        return $this->table('withdraw_records')->field($field)->where($condition)->order('withdraw_recordid desc')->limit($limit)->page($page, $count)->select();
    }

    /**
     * 获取记录个数
     */
    public function getWithdrawrecordsCount($condition) {
        return $this->table('withdraw_records')->where($condition)->count();
    }

}
