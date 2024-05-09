<?php
/**
 *会员卡
 */
defined('In718Shop') or exit('Access Invalid!');

class vip_cardModel extends Model {
    public function __construct(){
        parent::__construct('vip_card');
    }
    /**
     * 获取一条会员卡信息
     */
    public function getVipCardInfo($condition = array(), $fields = '*'){
        return $this->table('vip_card')->where($condition)->field($fields)->find();
    }
    /**
     * 获取会员卡信息列表
     */
    public function getVipCardList($condition = array(), $page = 0, $order = 'vip_card_id desc', $limit = ''){
        return $this->table('vip_card')->where($condition)->page($page)->order($order)->limit($limit)->select();
    }

    /**
     * @param $where
     * @param string $field
     * @param string $group
     * @return mixed
     */
    public function getVipCardCount($condition = array(), $field = '*', $group = ''){
        $count = $this->table('vip_card')->field($field)->where($condition)->group($group)->count();
        return $count;
    }
    /**
     * 修改会员卡信息，一般用于激活，修改是否使用字段数据
     */
    public function editVipCard($condition,$data){
        $update = $this->table('vip_card')->where($condition)->update($data);
        return $update;
    }
    /**
     * 新增会员卡信息
     */
    public function addVipCard($data) {
        QueueClient::push('createVipCardInfo', array('count' => $data['count'], 'vip_card_prefix' => $data['vip_card_prefix'], 'vip_card_grade' => $data['vip_card_grade']));
    }
    /**
     * 新增会员卡
     */
    public function addVipCardAll($insert) {
        return $this->insertAll($insert);
    }
    /**
     * 删除会员卡
     */
    public function delVipCarddById($id){
        return $this->where(array(
            'vip_card_id' => $id,
            'is_used' => 0
        ))->delete();
    }

}
