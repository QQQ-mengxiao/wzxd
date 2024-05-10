<?php
/**
 * 买家 我的分销佣金记录
 **/
defined('In718Shop') or exit('Access Invalid!');

class member_commissionControl extends BaseMemberControl {

    public function __construct() {
        parent::__construct();
        Language::read('member_member_index');
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string	$menu_type	导航类型
	 * @param string 	$menu_key	当前导航的menu_key
	 * @return
	 */
	private function profile_menu($menu_key='') {
	    Language::read('member_layout');
	    $menu_array = array(
	        array('menu_key'=>'member_order_commission','menu_name'=>'我的佣金记录', 'menu_url'=>'index.php?act=member_commission&op=member_commission&recycle=1'),
	    );
	    Tpl::output('member_menu',$menu_array);
	    Tpl::output('menu_key',$menu_key);
	}

        /**
     * 用户分销佣金记录
     *
     * @param string    
     * @param string    
     * @return
     */
     public function indexOp() {
        //搜索
        $condition = array();
        $condition['buyer_id'] = $_SESSION['member_id'];
        if ($_GET['order_sn'] != '') {
            $condition1['order_sn'] = $_GET['order_sn'];
        }
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition1['create_time'] = array('time',array($start_unixtime,$end_unixtime));
        }
        if ($_GET['comstate_search'] != ''){
            $condition1['commission_state'] = $_GET['comstate_search'];
        }
        //var_dump($condition);die;
        $model_pd = Model('member_commission');
        $condition1['member_id'] = $condition['buyer_id'];
        $recharge_list = $model_pd->getMcomList($condition1,15,'*','create_time desc');
        //var_dump($recharge_list);die;
        //佣金入账总额
        $condition2['member_id'] = $condition['buyer_id'];
        $condition2['commission_state'] = 2;
        $commission_list = $model_pd->getMcomInfo($condition2);
        //var_dump($commission_list);die;
        $commission_amount = 0;
        foreach($commission_list as $v){
            $commission_amount += $v['commission'];
        }


        //信息输出
        Tpl::output('commission_amount',$commission_amount);
        Tpl::output('list',$recharge_list);
        Tpl::output('show_page',$model_pd->showpage());

        self::profile_menu($_GET['member_commission'] ? 'member_order_commission' : 'member_order_commission');
        Tpl::showpage('member_order.commission');
    }
}
