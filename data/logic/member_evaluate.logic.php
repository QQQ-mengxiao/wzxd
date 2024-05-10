<?php
/**
 * 评价行为
 *
 * 
 *
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class member_evaluateLogic {

    public function evaluateListDity($goods_eval_list) {
        foreach($goods_eval_list as $key=>$value){
			$goods_eval_list[$key]['member_avatar'] = getMemberAvatarForID($value['geval_frommemberid']);
		}
		return $goods_eval_list;
    }
}
