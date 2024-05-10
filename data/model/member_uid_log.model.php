<?php
/**
 * 操作线下余额日志模型
 */
defined('In718Shop') or exit('Access Invalid!');

class member_uid_logModel extends Model {
    public function __construct(){
        parent::__construct('member_uid_log');
    }

    /**
     * 增加日志
     * @param array $data [description]
     */
    public function addLog($data = array())
    {
        return $this->insert($data);
    }
    /**
     * 更新日志
     * @param  array  $data      [description]
     * @param  array  $condition [description]
     * @return [type]            [description]
     */
    public function editLog($data = array(),$condition = array())
    {
        return $this->where($condition)->update($data);
    }
}
