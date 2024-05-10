<?php
/**
 * 拆红包记录管理
 */
defined('In718Shop') or exit('Access Invalid!');

class open_recordsModel extends Model{
    public function __construct(){
        parent::__construct('open_records');
    }

    /**
     * 新增记录数据
     *
     */
    public function addOpenrecords($insert) {
        $result = $this->table('open_records')->insert($insert);
        return $result;
    }

    /**
     * 获取一条记录数据
     */
    public function getOpenrecordsInfo($condition, $field = '*'){
        return $this->table('open_records')->field($field)->where($condition)->find();
    }

    /**
     * 获取记录列表
     */
    public function getOpenrecordsList($condition, $page = 0, $field = '*', $order = '', $limit = 0, $count = 0) {
        return $this->table('open_records')->field($field)->where($condition)->order($order)->limit($limit)->page($page, $count)->select();
    }

    /**
     * 获取记录个数
     */
    public function getOpenrecordsCount($condition) {
        return $this->table('open_records')->where($condition)->count();
    }

}
