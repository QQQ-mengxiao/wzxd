<?php
/**
 * 满即送活动规则模型 
 **/
defined('In718Shop') or exit('Access Invalid!');
class p_ladder_ruleModel extends Model{

    public function __construct(){
        parent::__construct('p_ladder_rule');
    }

	/**
     * 读取满即送规则列表
	 * @param array $mansong_id 查询条件
	 * @param int $page 分页数
	 * @param string $order 排序
	 * @param string $field 所需字段
     * @return array 满即送套餐列表
	 *
	 */
	public function getMansongRuleListByID($mansong_id) {
        $condition = array();
        $condition['p_ladder_id'] = $mansong_id;
        // var_dump($condition);die; 	
        $mansong_rule_list = $this->where($condition)->select();
        
        return $mansong_rule_list;
	}
/*
	 * 增加 
	 * @param array $param
	 * @return bool
     *
	 */
    public function findladderByRuleID($rule_id){
    	 $condition = array();
        $condition['rule_id'] = $rule_id;
        return $this->where($condition)->find();
    }

	/*
	 * 增加 
	 * @param array $param
	 * @return bool
     *
	 */
    public function addMansongRule($param){
        return $this->insert($param);	
    }

	/*
	 * 批量增加 
	 * @param array $array
	 * @return bool
     *
	 */
    public function addMansongRuleArray($array){
        return $this->insertAll($array);	
    }

	/*
	 * 删除
	 * @param array $condition
	 * @return bool
     *
	 */
    public function delMansongRule($condition){
        return $this->where($condition)->delete();
    }
}
