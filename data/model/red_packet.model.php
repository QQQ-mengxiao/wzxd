<?php
/**
 * 裂变红包管理
 */
defined('In718Shop') or exit('Access Invalid!');

class red_packetModel extends Model{
    public function __construct(){
        parent::__construct('red_packet');
    }

    /**
     * 新增红包数据
     *
     */
    public function addRedpacket($insert) {
        $result = $this->table('red_packet')->insert($insert);
        return $result;
    }

    /**
     * 获取一条红包数据
     */
    public function getRedpacketInfo($condition, $field = '*'){
            return $this->table('red_packet')->field($field)->where($condition)->find();
    }

    /**
     * 更新红包数据
     */
    public function editRedpacket($update, $red_packetid){
            $condition['red_packetid'] = $red_packetid;
            $result = $this->table('red_packet')->where($condition)->update($update);
            return $result;
    }

    /**
     * 获取红包列表
     */
    public function getRedpacketList($condition, $page = 0, $field = '*', $red_packetid = '', $limit = 0, $count = 0) {
        return $this->table('red_packet')->field($field)->where($condition)->order('red_packetid desc')->limit($limit)->page($page, $count)->select();
    }

    /**
     * 获取红包个数
     */
    public function getRedpacketCount($condition) {
        return $this->table('red_packet')->where($condition)->count();
    }
	
}
