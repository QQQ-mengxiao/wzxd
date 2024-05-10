<?php
/**
 * 裂变红包管理
 */
defined('In718Shop') or exit('Access Invalid!');

class qd_rulerModel extends Model{
    public function __construct(){
        parent::__construct('qd_ruler');
    }

    /**
     * 新增红包数据
     *
     */
    public function addqd_ruler($insert) {
        $result = $this->table('qd_ruler')->insert($insert);
        return $result;
    }

    /**
     * 获取一条红包数据
     */
    public function getqd_rulerInfo($condition, $field = '*'){
            return $this->table('qd_ruler')->field($field)->where($condition)->find();
    }

    /**
     * 更新红包数据
     */
    public function editqd_ruler($update, $qd_rulerid){
            $condition['id'] = $qd_rulerid;
            $result = $this->table('qd_ruler')->where($condition)->update($update);
            return $result;
    }

    /**
     * 获取红包列表
     */
    public function getqd_rulerList($condition, $page = 0, $field = '*', $qd_rulerid = '', $limit = 0, $count = 0) {
        return $this->table('qd_ruler')->field($field)->where($condition)->order('id asc')->limit($limit)->page($page, $count)->select();
    }

    /**
     * 获取红包个数
     */
    public function getqd_rulerCount($condition) {
        return $this->table('qd_ruler')->where($condition)->count();
    }
	
}
