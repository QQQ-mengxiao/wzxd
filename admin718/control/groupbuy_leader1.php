<?php
/**
 * 团长管理
 */

defined('In718Shop') or exit('Access Invalid!');

class groupbuy_leaderControl extends SystemControl{
	public function __construct(){
		parent::__construct();
	}
	/**
	 * 团长列表
	 */
	public function groupbuy_leader_listOp(){

		$model_groupbuy_leader = Model('groupbuy_leader');

		$condition = [];
		$condition['ziti_address.state'] = array('neq',4);

		if($_GET['search_seller_name']){
			$condition['ziti_address.seller_name'] = array('like','%'.$_GET['search_seller_name'].'%');
		}

		$start_add_time = strtotime($_GET['start_add_time']);
		$end_add_time = strtotime($_GET['end_add_time']);

		if($start_add_time || $end_add_time){
        	$condition['ziti_address.add_time'] = array('between',"$start_add_time,$end_add_time");
		}

		if($_GET['search_state']!=''){
			$condition['ziti_address.state'] = $_GET['search_state'];
		}

		if($_GET['have_license']!=''){
			$condition['groupbuy_leader.have_license'] = $_GET['have_license'];
		}

		$field = 'groupbuy_leader.groupbuy_leader_id,groupbuy_leader.wx_avatar,groupbuy_leader.phone_num,ziti_address.add_time,ziti_address.address_id,ziti_address.seller_name,ziti_address.area_info,ziti_address.address,ziti_address.open_time_start,ziti_address.open_time_end,ziti_address.xie_time_start,ziti_address.xie_time_end,ziti_address.state,groupbuy_leader.have_license';
		$groupbuy_leader_list = $model_groupbuy_leader->getGroupbuyLeaderAndZitiAddressList($condition,$field,'right');

		Tpl::output('groupbuy_leader_list',$groupbuy_leader_list);
		Tpl::output('page',$model_groupbuy_leader->showpage());
		Tpl::showpage('groupbuy_leader.index');
	}

	/**
	 * ajax开启关闭自提点
	 */
	public function ajaxStateOp(){

		$model_ziti_address = Model('ziti_address');

		$address_id = intval($_GET['id']);
		$state = intval($_GET['value']);

		$ziti_address_info = $model_ziti_address->getAddressInfo(array('address_id'=>$address_id,'state'=>0));//是否未审核

		if($ziti_address_info){
			echo '自提点未审核不能更改状态';
			exit();
		}

        $result = $model_ziti_address->editAddress(array('state' => $state),array('address_id' => $address_id));

        if($result){
			echo 1;
			exit();
		}else{
			echo '更改自提点状态失败';
			exit();
		}

	}

	/**
	 * 自提点详细信息查看
	 */
	public function ziti_address_infoOp(){

		$model_groupbuy_leader = Model('groupbuy_leader');
		$model_ziti_address = Model('ziti_address');

		if (chksubmit()){

			//更新自提点地址
			$ziti_address['seller_name'] = $_POST['seller_name'];
			$ziti_address['area_info'] = $_POST['area_info']?$_POST['area_info']:$_POST['ziti_area_info'];
			$ziti_address['address'] = $_POST['address'];
			$ziti_address['open_time_start'] = $_POST['open_time_start'];
			$ziti_address['open_time_end'] = $_POST['open_time_end'];
			$ziti_address['state'] = $_POST['state'];

			$ziti_address_result = $model_ziti_address->editAddress($ziti_address,array('address_id' => $_POST['address_id']));

			if(!$ziti_address_result){
				showMessage('自提点信息编辑失败【自提点信息编辑失败】');
			}

			//更新团长电话
			$groupbuy_leader['phone_num'] = $_POST['phone_num'];

			$groupbuy_leader_result = $model_groupbuy_leader->editGroupbuyLeader(array('groupbuy_leader_id' => $_POST['groupbuy_leader_id']),$groupbuy_leader);

			if(!$groupbuy_leader_result){
				showMessage('自提点信息编辑失败【团长信息编辑失败】');
			}

			showMessage('自提点信息编辑成功', urlAdmin('groupbuy_leader', 'groupbuy_leader_list'));

		}

		$condition['ziti_address.address_id'] = intval($_GET['address_id']);

		$field = 'groupbuy_leader.groupbuy_leader_id,groupbuy_leader.wx_avatar,groupbuy_leader.phone_num,ziti_address.add_time,ziti_address.address_id,ziti_address.seller_name,ziti_address.area_info,ziti_address.address,ziti_address.open_time_start,ziti_address.open_time_end,ziti_address.xie_time_start,ziti_address.xie_time_end,ziti_address.state,groupbuy_leader.have_license,groupbuy_leader.id_photo_front,groupbuy_leader.id_photo_back,ziti_address.ziti_photo';
		$ziti_address_info = $model_groupbuy_leader->getGroupbuyLeaderAndZitiAddressInfo($condition,$field);

		Tpl::output('ziti_address_info',$ziti_address_info);
		Tpl::showpage('groupbuy_leader.ziti_address_info');
	}

	/**
	 * 团长审核
	 */
	public function groupbuy_leader_reviewOp(){

		$model_groupbuy_leader = Model('groupbuy_leader');

		$condition['ziti_address.state'] = array('in',[0,4]);

		if($_GET['search_seller_name']){
			$condition['ziti_address.seller_name'] = array('like','%'.$_GET['search_seller_name'].'%');
		}

		$start_add_time = strtotime($_GET['start_add_time']);
		$end_add_time = strtotime($_GET['end_add_time']);

		if($start_add_time || $end_add_time){
        	$condition['ziti_address.add_time'] = array('between',"$start_add_time,$end_add_time");
		}

		if($_GET['state']!=''){
			$condition['ziti_address.state'] = $_GET['state'];
		}

		if($_GET['have_license']!=''){
			$condition['groupbuy_leader.have_license'] = $_GET['have_license'];
		}

		$field = 'groupbuy_leader.groupbuy_leader_id,groupbuy_leader.wx_avatar,groupbuy_leader.phone_num,ziti_address.add_time,ziti_address.address_id,ziti_address.seller_name,ziti_address.area_info,ziti_address.address,ziti_address.state,groupbuy_leader.have_license,ziti_address.review_msg';
		$groupbuy_leader_list = $model_groupbuy_leader->getGroupbuyLeaderAndZitiAddressList($condition,$field);

		Tpl::output('groupbuy_leader_list',$groupbuy_leader_list);
		Tpl::output('page',$model_groupbuy_leader->showpage());
		Tpl::showpage('groupbuy_leader_review.index');
	}

	/**
	 * 自提点审核详细信息查看
	 */
	public function ziti_address_review_infoOp(){

		$model_groupbuy_leader = Model('groupbuy_leader');
		$model_ziti_address = Model('ziti_address');

		if (chksubmit()){

			//更新自提点审核状态
			$ziti_address['state'] = $_POST['state'];
			$ziti_address['review_msg'] = $_POST['review_msg'];

			$ziti_address_result = $model_ziti_address->editAddress($ziti_address,array('address_id' => $_POST['address_id']));

			if(!$ziti_address_result){
				showMessage('自提点审核失败');
			}

			showMessage('自提点审核成功', urlAdmin('groupbuy_leader', 'groupbuy_leader_review'));

		}

		$condition['ziti_address.address_id'] = intval($_GET['address_id']);

		$field = 'groupbuy_leader.groupbuy_leader_id,groupbuy_leader.wx_avatar,groupbuy_leader.phone_num,ziti_address.add_time,ziti_address.address_id,ziti_address.seller_name,ziti_address.area_info,ziti_address.address,ziti_address.state,groupbuy_leader.have_license,groupbuy_leader.id_photo_front,groupbuy_leader.id_photo_back,ziti_address.ziti_photo,ziti_address.review_msg';
		$ziti_address_info = $model_groupbuy_leader->getGroupbuyLeaderAndZitiAddressInfo($condition,$field);

		Tpl::output('ziti_address_info',$ziti_address_info);
		Tpl::showpage('groupbuy_leader.ziti_address_review_info');
	}

	/**
	 * 歇业审核
	 */
	public function groupbuy_leader_break_reviewOp(){

		$model_groupbuy_leader = Model('groupbuy_leader');

		$condition['ziti_address.state'] = 1;//正常营业状态
		$condition['ziti_address.xie_state'] = array('egt',2);//申请歇业待审核，审核失败

		if($_GET['search_seller_name']){
			$condition['ziti_address.seller_name'] = array('like','%'.$_GET['search_seller_name'].'%');
		}

		$start_add_time = strtotime($_GET['start_add_time']);
		$end_add_time = strtotime($_GET['end_add_time']);

		if($start_add_time || $end_add_time){
        	$condition['ziti_address.add_time'] = array('between',"$start_add_time,$end_add_time");
		}

		if($_GET['xie_state']!=''){
			$condition['ziti_address.xie_state'] = $_GET['xie_state'];
		}

		if($_GET['have_license']!=''){
			$condition['groupbuy_leader.have_license'] = $_GET['have_license'];
		}

		$field = 'groupbuy_leader.groupbuy_leader_id,groupbuy_leader.wx_avatar,groupbuy_leader.phone_num,ziti_address.add_time,ziti_address.address_id,ziti_address.seller_name,ziti_address.area_info,ziti_address.address,ziti_address.state,groupbuy_leader.have_license,ziti_address.xie_state,ziti_address.xie_time_start,ziti_address.xie_time_end,xie_reason.content';
		$groupbuy_leader_list = $model_groupbuy_leader->getGroupbuyLeaderAndZitiAddressAndReasonList($condition,$field);

		Tpl::output('groupbuy_leader_list',$groupbuy_leader_list);
		Tpl::output('page',$model_groupbuy_leader->showpage());
		Tpl::showpage('groupbuy_leader_break_review.index');
	}

    /**
     * ajax审核歇业
     */
    public function ajaxBreakStateOp() {

        if (chksubmit()) {

            $address_id = $_POST['address_id'];

			if(!$address_id){
            	showDialog('参数错误', 'reload');
			}

            $update = array();
            $update['xie_state'] = trim($_POST['state']);
            $update['xie_time'] = TIMESTAMP;

			$address_info = Model('ziti_address')->getAddressInfo(array('address_id'=>$address_id),'xie_time_start,xie_time_end');
			if($_POST['state']==1 && TIMESTAMP>=$address_info['xie_time_start'] && TIMESTAMP<=$address_info['xie_time_end']){
				$update['state'] = 2;
			}

            $where = array();
            $where['address_id'] = $address_id;

            $result = Model('ziti_address')->editAddress($update, $where);
			if($result){
            	showDialog('审核成功', 'reload', 'succ');
			}else{
				showDialog('审核失败', 'reload');
			}
        }

        Tpl::output('address_id', $_GET['address_id']);
        Tpl::showpage('groupbuy_leader.break_review', 'null_layout');
    }
}
