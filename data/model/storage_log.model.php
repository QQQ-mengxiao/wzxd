<?php
/**
 * 仓库记录
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class storage_logModel extends Model {
    public function __construct() {
        parent::__construct('storage_log');
    }

    /**
     * 新增
     * @param unknown $data
     * @return boolean, number
     */
    public function addStorageLog($data) {
        return $this->insert($data);
    }

    /**
     * 删除
     * @param unknown $condition
     */
    public function delStorage($condition) {
        return $this->where($condition)->delete();
    }

    public function editStorageLog($data, $condition) {
        return $this->where($condition)->update($data);
    }

    /**
     * 查询单条
     * @param unknown $condition
     * @param string $fields
     */
    public function getStorageLogInfo($condition, $fields = '*') {
        return $this->field($fields)->where($condition)->find();
    }

    /**
     * 查询多条
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getStorageLogList($condition, $fields = '*', $order = '', $limit = '',$page='') {
        return $this->field($fields)->where($condition)->order($order)->page($page)->limit($limit)->select();
    }

    /**
     * 连表查询
     */
    public function getStorageLogInfoAll($condition){
        $storage_log_id = $condition['storage_log_id'];
        return Model()->table('storage_log,storage')->field('storage_log.*,storage.*')->join('left')->where(array('storage_log.storage_log_id'=>$storage_log_id))->on('storage_log.storage_id=storage.storage_id')->find();
    }
}