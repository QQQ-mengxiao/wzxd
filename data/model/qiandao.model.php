<?php
/**
 * 裂变管理
 */
defined('In718Shop') or exit('Access Invalid!');

class qiandaoModel extends Model{
    public function __construct(){
        parent::__construct('qiandao');
    }

    /**
     * 新增数据
     *
     */
    public function addqiandao($insert) {
        $result = $this->table('qiandao')->insert($insert);
        return $result;
    }

    /**
     * 获取一条数据
     */
    public function getqiandaoInfo($condition, $field = '*'){
            return $this->table('qiandao')->field($field)->where($condition)->find();
    }

    /**
     * 更新数据
     */
    public function editqiandao($update, $member_id){
            $condition['member_id'] = $member_id;
            $result = $this->table('qiandao')->where($condition)->update($update);
            return $result;
    }

    /**
     * 获取列表
     */
    public function getqiandaoList($condition, $page = 0, $field = '*', $qiandaoid = '', $limit = 0, $count = 0) {
        return $this->table('qiandao')->field($field)->where($condition)->order('qiandao_id desc')->limit($limit)->page($page, $count)->select();
    }

    /**
     * 获取个数
     */
    public function getqiandaoCount($condition) {
        return $this->table('qiandao')->where($condition)->count();
    }
	
}
