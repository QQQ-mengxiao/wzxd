<?php
/**
 * 任务队列
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class cronModel extends Model {
    public function __construct() {
       parent::__construct('cron'); 
    }

    /**
     * 取单条任务信息
     * @param array $condition
     */
    public function getCronInfo($condition = array()) {
        return $this->where($condition)->find();
    }
    /**
     * 任务队列列表
     * @param array $condition
     * @param number $limit
     * @return array
     */
    public function getCronList($condition, $limit = 100) {
        return $this->where($condition)->limit($limit)->select();
    }
    
    /**
     * 保存任务队列
     * 
     * @param unknown $insert
     * @return array
     */
    public function addCronAll($insert) {
        return $this->insertAll($insert);
    }
    
    /**
     * 保存任务队列
     * 
     * @param array $insert
     * @return boolean
     */
    public function addCron($insert) {
        return $this->insert($insert);
    }

    /**
     * 编辑任务队列
     *
     * @param array $update
     * @return boolean
     */
    public function editCron($condition,$update)
    {
        return $this->where($condition)->update($update);
    }
    
    /**
     * 删除任务队列
     * 
     * @param array $condition
     * @return array
     */
    public function delCron($condition) {
        return $this->where($condition)->delete();
    }
}