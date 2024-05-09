<?php
/**
 * 会员管理
 *
 *
 *
 ** */

defined('In718Shop') or exit('Access Invalid!');

class member_gradeControl extends SystemControl{
	public function __construct(){
		parent::__construct();
		Language::read('member');
	}
	/**
     * 设置积分获取规则
     */
    public function gui_addOp(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $point_arr = array();
            $point_arr['value'] = $_POST['pointsdesc']?$_POST['pointsdesc']:'';
            $result = $model_setting->where(array('name'=>'points_rule'))->update($point_arr);
            if ($result === true){
                // $this->log(L('nc_edit,nc_exppoints_manage,nc_exppoints_setting'),1);
                showMessage(L('nc_common_save_succ'));
            }else {
                showMessage(L('nc_common_save_fail'));
            }           
        }
        $points_rule = $model_setting->table('setting')->field('value')->where(array('name' => 'points_rule'))->find();
       
        // $list_setting['exppoints_rule'] = $list_setting['exppoints_rule']?unserialize($list_setting['exppoints_rule']):array();
        Tpl::output('points_rule',$points_rule);
        Tpl::showpage('point_rule');
    }
	/**
	 * 会员管理
	 */
	public function indexOp(){
	    $model_setting = Model('setting');
	    $list_setting = $model_setting->getListSetting();
	    $list_setting['member_grade'] = $list_setting['member_grade']?unserialize($list_setting['member_grade']):array();
	    if (chksubmit()){
    	    $update_arr = array();
    	    if($_POST['mg']){
    	        $mg_arr = array();
    	        $i = 0;
    	        foreach($_POST['mg'] as $k=>$v){
    	            $mg_arr[$i]['level'] = $i;
//    	            $mg_arr[$i]['level_name'] = 'V'.$i;
    	            $mg_arr[$i]['level_name'] = $v['level_name'];
        			//所需经验值
        			$mg_arr[$i]['exppoints'] = intval($v['exppoints']);
                    $mg_arr[$i]['points_grade'] = $v['points_grade'];
					//$mg_arr[$i]['discount'] = intval($v['discount'])?intval($v['discount']):'100';
					$mg_arr[$i]['discount'] = $v['discount']?$v['discount']:'100';
        			$i++;
    	        }
    	        $update_arr['member_grade'] = serialize($mg_arr);
    	    } else {
    	        $update_arr['member_grade'] = '';
    	    }
    	    $result = true;
    	    if ($update_arr){
    	        $result = $model_setting->updateSetting($update_arr);
    	    }
    	    if ($result){
    	    	//改变规则后更新商品活动表中的所有会员及商品的促销价格
    	    	$model_goods = Model('goods');
		        $goodscommon_list = $model_goods->getGoodsCommonList(array('is_vip_price'=>1),'goods_commonid',0);
		        $goods_commonidarr=array_column($goodscommon_list,'goods_commonid');
		        $condition=array();
		        $condition['goods_commonid']=array('in', $goods_commonidarr);
		        $condition['is_deleted']=0;
		        $goods_list = $model_goods->getGoodsList($condition, 'goods_id,goods_price,hui_discount');
		        $model_goodspromotion=Model('goods_promotion'); 
		        // var_dump($goods_list);die;
		        foreach ($goods_list as $key => $value) {
		        	$vipgoods=array();
                    $vipgoods['goods_id']=$value['goods_id'];
                    $vipgoods['promotion_type']=30;
                    $vipgoods_info=$model_goodspromotion->getgoods_promotionInfo($vipgoods); 
                     if(!empty($vipgoods_info)){
                          $model_goodspromotion->updategoods_promotion_vip($value['goods_id'],$value['goods_price'],$value['hui_discount']);
                        }else{
                             $model_goodspromotion->addgoods_promotion_vip($value['goods_id'],$value['goods_price'],$value['hui_discount']);
                        }
		        }
		        //改变规则后更新商品活动表中的所有会员及商品的促销价格
    	        $this->log(L('nc_edit,nc_member_grade'),1);
				showDialog(L('nc_common_save_succ'),'reload','succ');
    	    } else {
    	        $this->log(L('nc_edit,nc_member_grade'),0);
				showDialog(L('nc_common_save_fail'));
    	    }
	    } else {
	        Tpl::output('list_setting',$list_setting);
		    Tpl::showpage('member.grade');
	    }
	}
}