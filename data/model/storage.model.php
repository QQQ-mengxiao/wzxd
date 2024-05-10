<?php
/**
 * 仓库
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class storageModel extends Model {
    public function __construct() {
        parent::__construct('storage');
    }

    /**
     * 新增
     * @param unknown $data
     * @return boolean, number
     */
    public function addStorage($data) {
        return $this->insert($data);
    }

    /**
     * 删除
     * @param unknown $condition
     */
    public function delStorage($condition) {
        return $this->where($condition)->delete();
    }

    public function editStorage($data, $condition) {
        return $this->where($condition)->update($data);
    }

    /**
     * 查询单条
     * @param unknown $condition
     * @param string $fields
     */
    public function getStorageInfo($condition, $fields = '*') {
        return $this->field($fields)->where($condition)->find();
    }

    /**
     * 查询多条
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getStorageList($condition, $fields = '*', $order = '', $limit = '') {
        return $this->field($fields)->where($condition)->order($order)->limit($limit)->select();
    }
}