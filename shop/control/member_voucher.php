<?php
/**
 * 代金券
 *
 * */


defined('In718Shop') or exit('Access Invalid!');
class member_voucherControl extends BaseMemberControl{
	public function __construct() {
		parent::__construct();
		Language::read('member_layout,member_voucher');
		//判断系统是否开启代金券功能
		if (intval(C('voucher_allow')) !== 1){
			showMessage(Language::get('member_voucher_unavailable'),urlShop('member', 'home'),'html','error');
		}
	}
	/*
	 * 默认显示代金券模版列表
	 */
	public function indexOp() {
        $this->voucher_listOp() ;
    }

	/*
	 * 获取代金券模版详细信息
	 */
    public function voucher_listOp(){
		$model = Model('voucher');
        $list = $model->getMemberVoucherList($_SESSION['member_id'], $_GET['select_detail_state'], 10);

		//取已经使用过并且未有voucher_order_id的代金券的订单ID
		$used_voucher_code = array();
		$voucher_order = array();
		if (!empty($list)) {
		    foreach ($list as $v) {
		        if ($v['voucher_state'] == 2 && empty($v['voucher_order_id'])) {
		            $used_voucher_code[] = $v['voucher_code'];
		        }
		    }
		}
        if (!empty($used_voucher_code)) {
            $order_list = Model('order')->getOrderCommonList(array('voucher_code'=>array('in',$used_voucher_code)),'order_id,voucher_code');
            if (!empty($order_list)) {
                foreach ($order_list as $v) {
                    $voucher_order[$v['voucher_code']] = $v['order_id'];
                    $model->editVoucher(array('voucher_order_id'=>$v['order_id']),array('voucher_code'=>$v['voucher_code']));
                }
            }
        }

		Tpl::output('list', $list);
		Tpl::output('voucherstate_arr', $model->getVoucherStateArray());
        Tpl::output('show_page',$model->showpage(2)) ;
        $this->profile_menu('voucher_list');
        Tpl::showpage('member_voucher.list');
    }
	public function donateOp(){

		$t_id=intval($_GET['voucher_id']);
		if ($t_id <= 0){
			showMessage(Language::get('wrong_argument'),'index.php?act=member_voucher&op=voucher_list','html','error');
		}
		$model_voucher=Model('voucher');
		$donate_voucher=$model_voucher->table('voucher')->where(array('voucher_id'=>$t_id))->find();
		$donate_voucher['voucher_state_text']='未使用';
		$donate_voucher['voucher_store_name']= Model('store')->getfby_store_id($donate_voucher['voucher_store_id'],'store_name');
		$val=$model_voucher->table('voucher_template')->where(array('voucher_t_id'=>$donate_voucher['voucher_t_id']))->find();
		if (empty($val['voucher_t_customimg']) || !file_exists(BASE_UPLOAD_PATH.DS.ATTACH_VOUCHER.DS.$val['voucher_t_store_id'].DS.$val['voucher_t_customimg'])){
			$donate_voucher['voucher_t_customimg'] = UPLOAD_SITE_URL.DS.defaultGoodsImage(60);
		}else{
			$donate_voucher['voucher_t_customimg'] = UPLOAD_SITE_URL.DS.ATTACH_VOUCHER.DS.$val['voucher_t_store_id'].DS.str_ireplace('.', '_small.', $val['voucher_t_customimg']);
		}
		Tpl::output('list', $donate_voucher);
		Tpl::output('voucherstate_arr', $model_voucher->getVoucherStateArray());
		$this->profile_menu('donate_list');
		Tpl::showpage('member_voucher.donate');
	}
	public function donatenOp(){
		$voucher_id=intval($_POST['tid']);
		$obj_validate = new Validate();
		$error = $obj_validate->validate();
		if($voucher_id<=0){
			$error.=Language::get('wrong_argument');
		}
		//$voucher=Model('member')->getby_member_name($_POST['voucher_name']);
		$voucher=Model('member')->getby_member_id($_POST['member_id']);

		if(!$voucher){
			//$error.='好友用户名不存在或者不能为空';
			$error.='好友不存在或者好友id不能为空';
		}
		if($error){
			showDialog($error,'reload','error');
		}else{
			$data=array();
			$data['voucher_owner_id']=$voucher['member_id'];
			$data['voucher_owner_name']=$voucher['member_name'];
			$result=Model('voucher')->table('voucher')->where(array('voucher_id'=>$voucher_id))->update($data);
			$para=array();
			$para['donate_id']=$_SESSION['member_id'];
			$para['donate_name']=Model('member')->getfby_member_id($_SESSION['member_id'],'member_name');
			$para['accept_id']=$voucher['member_id'];
			$para['accept_name']=$voucher['member_name'];
			$para['voucher_id']=$voucher_id;
			$para['voucher_code']=Model('voucher')->getfby_voucher_id($voucher_id,'voucher_code');
			$para['voucher_desc']=Model('voucher')->getfby_voucher_id($voucher_id,'voucher_desc');
			if($result){
				Model('voucher')-> addvoucherLog($para);
				showDialog('代金券转赠成功','index.php?act=member_voucher&op=index','succ');
			}else{
				showDialog('代金券转赠失败','index.php?act=member_voucher&op=index','error');
			}
		}
	}

	public function donateListOp(){

		$member_id=intval($_SESSION['member_id']);
		$data=array();
		if(intval($_GET['donate'])==0){
			$data['accept_id']=$member_id;
		}else{
			$data['donate_id']=$member_id;
		}
		/*if($_GET['txt_startdate']&&$_GET['txt_enddate']){
            $data['add_time']=array(array('egt',strtotime($_GET['txt_startdate'])),array('lt',strtotime($_GET['txt_enddate'])),'and');
        }*/
		$list=Model('voucher')->table('donate_log')->where($data)->page(10)->order('add_time desc')->select();
		foreach($list as $key=>$voucher){
			$list[$key]['voucher_price']=Model('voucher')->table('voucher')->getfby_voucher_id($voucher['voucher_id'],'voucher_price');
		}
		$this->profile_menu('donate_list');
		Tpl::output('show_page',Model('voucher')->showpage(2)) ;
		Tpl::output('list',$list);
		Tpl::showpage('member_voucherdonate.list');
	}

	/**
	 * 用户中心右边，小导航
	 *
	 * @param string	$menu_type	导航类型
	 * @param string 	$menu_key	当前导航的menu_key
	 * @param array 	$array		附加菜单
	 * @return
	 */
	private function profile_menu($menu_key='') {
		$menu_array = array(
			1=>array('menu_key'=>'voucher_list','menu_name'=>Language::get('nc_myvoucher'),'menu_url'=>'index.php?act=member_voucher&op=voucher_list'),
			2=>array('menu_key'=>'donate_list','menu_name'=>'代金券转赠与接收记录','menu_url'=>'index.php?act=member_voucher&op=donateList'),
		);
		Tpl::output('member_menu',$menu_array);
		Tpl::output('menu_key',$menu_key);
    }

}
