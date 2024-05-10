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

		$start_add_time = strtotime($_GET['start_add_time']);
		$end_add_time = strtotime($_GET['end_add_time']);

		if($start_add_time || $end_add_time){
			$condition['add_time'] = ['between',"$start_add_time,$end_add_time"];
		}

		if($_GET['search_groupbuy_leader_id']!=''){
			$condition['groupbuy_leader_id'] = $_GET['search_groupbuy_leader_id'];
		}

		if($_GET['search_wx_nickname']!=''){
			$condition['wx_nickname'] = ['like','%'.$_GET['search_wx_nickname'].'%'];
		}

		$field = 'groupbuy_leader_id,wx_avatar,wx_nickname,phone_num,add_time';
		$groupbuy_leader_list = $model_groupbuy_leader->getGroupbuyLeaderList($condition,$field,10,'groupbuy_leader_id desc');

		if($groupbuy_leader_list){
			foreach($groupbuy_leader_list as $key=>$value){
				$groupbuy_leader_list[$key]['ziti_address_count'] = $model_groupbuy_leader->getGroupbuyLeaderZitiAddressCount(['gl_id'=>$value['groupbuy_leader_id']]);
				$groupbuy_leader_list[$key]['assistant_count'] = $model_groupbuy_leader->getGroupbuyLeaderAssistantCount(['gl_id'=>$value['groupbuy_leader_id']]);
			}
		}

		Tpl::output('groupbuy_leader_list',$groupbuy_leader_list);
		Tpl::output('page',$model_groupbuy_leader->showpage());

		Tpl::showpage('groupbuy_leader.index');
	}

	/**
	 * 团长详情
	 */
	public function groupbuy_leader_infoOp(){
		$groupbuy_leader_id = $_GET['groupbuy_leader_id'];

		$model_groupbuy_leader = Model('groupbuy_leader');

		$condition = ['gl_id'=>$groupbuy_leader_id];

		//团长信息
		$groupbuy_leader_info = $model_groupbuy_leader->getGroupbuyLeaderInfo(['groupbuy_leader_id'=>$groupbuy_leader_id]);
		$groupbuy_leader_info['id_photo_front'] = UPLOAD_SITE_URL . '/' . DIR_UPLOAD_GLID_FRONT . '/' . $groupbuy_leader_id . '/' . $groupbuy_leader_info['id_photo_front'];
		$groupbuy_leader_info['id_photo_back'] = UPLOAD_SITE_URL . '/' . DIR_UPLOAD_GLID_BACK . '/' . $groupbuy_leader_id . '/' . $groupbuy_leader_info['id_photo_back'];

		//自提点列表
		$groupbuy_leader_ziti_address_list 	= $model_groupbuy_leader->getGroupbuyLeaderZitiAddressList($condition);

		//团长助手列表
		$groupbuy_leader_assistant_list		= $model_groupbuy_leader->getGroupbuyLeaderAssistantList($condition);
		
		Tpl::output('groupbuy_leader_info',$groupbuy_leader_info);
		Tpl::output('groupbuy_leader_ziti_address_list',$groupbuy_leader_ziti_address_list);
		Tpl::output('groupbuy_leader_assistant_list',$groupbuy_leader_assistant_list);

		Tpl::showpage('groupbuy_leader.detail');
	}

	/**
	 * 自提点列表
	 */
	public function ziti_address_listOp(){
		$model_groupbuy_leader = Model('groupbuy_leader');

		$condition = [];

		$start_add_time = strtotime($_GET['start_add_time']);
		$end_add_time = strtotime($_GET['end_add_time']);

		if($start_add_time || $end_add_time){
			$condition['ziti_address.add_time'] = ['between',"$start_add_time,$end_add_time"];
		}

		if($_GET['search_groupbuy_leader_id']!=''){
			$condition['ziti_address.gl_id'] = $_GET['search_groupbuy_leader_id'];
		}

		if($_GET['search_wx_nickname']!=''){
			$condition['groupbuy_leader.wx_nickname'] = ['like','%'.$_GET['search_wx_nickname'].'%'];
		}

		if($_GET['search_seller_name']!=''){
			$condition['ziti_address.seller_name'] = ['like','%'.$_GET['search_seller_name'].'%'];
		}

		if($_GET['search_phone_num']!=''){
			$condition['ziti_address.phone_num'] = $_GET['search_phone_num'];
		}

		if($_GET['state']!=''){
			$condition['ziti_address.state'] = $_GET['state'];
		}

		if($_GET['have_license']!=''){
			$condition['ziti_address.have_license'] = $_GET['have_license'];
		}

		$ziti_address_list = $model_groupbuy_leader->getGroupbuyLeaderAndZitiAddressList($condition,'groupbuy_leader.wx_nickname,ziti_address.address_id,ziti_address.seller_name,ziti_address.area_info,ziti_address.address,ziti_address.phone_num,ziti_address.add_time,ziti_address.gl_id,ziti_address.have_license,ziti_address.ziti_photo,ziti_address.state','right',10,'ziti_address.address_id desc');
		
		Tpl::output('ziti_address_list',$ziti_address_list);
		Tpl::output('page',$model_groupbuy_leader->showpage());

		Tpl::showpage('groupbuy_leader.ziti_address_list');
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
			$ziti_address['phone_num'] = $_POST['phone_num'];
			$ziti_address['have_license'] = $_POST['have_license'];
			
			/**
			 * 上传图片
			 */
			if ($_FILES['link_pic']['name'] != ''){
				$upload = new UploadFile();
				$gl_ld = Model()->table('ziti_address')->getfby_address_id($_POST['address_id'],'gl_id');
				$upload->set('default_dir',DIR_UPLOAD_ZITI.'/'.$gl_ld);
				$upload->set('allow_type', array('jpg', 'jpeg', 'gif', 'png'));
				$result = $upload->upfile('link_pic');
				if ($result){
					$ziti_address['ziti_photo'] = $upload->file_name;
				}else {
					showMessage($upload->error);
				}
			}

			$ziti_address_result = $model_ziti_address->editAddress($ziti_address,array('address_id' => $_POST['address_id']));

			if(!$ziti_address_result){
				showMessage('自提点信息编辑失败【自提点信息编辑失败】');
			}

			showMessage('自提点信息编辑成功', urlAdmin('groupbuy_leader', 'ziti_address_list'));

		}

		$condition['ziti_address.address_id'] = intval($_GET['address_id']);

		$field = 'groupbuy_leader.groupbuy_leader_id,groupbuy_leader.wx_nickname,ziti_address.gl_id,ziti_address.phone_num,ziti_address.add_time,ziti_address.address_id,ziti_address.seller_name,ziti_address.area_info,ziti_address.address,ziti_address.open_time_start,ziti_address.open_time_end,ziti_address.xie_time_start,ziti_address.xie_time_end,ziti_address.state,ziti_address.have_license,ziti_address.ziti_photo';
		$ziti_address_info = $model_groupbuy_leader->getGroupbuyLeaderAndZitiAddressInfo($condition,$field,'right');

		Tpl::output('ziti_address_info',$ziti_address_info);
		Tpl::showpage('groupbuy_leader.ziti_address_info');
	}

	/**
	 * 自提点审核
	 */
	public function ziti_address_reviewOp(){

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

		$field = 'groupbuy_leader.groupbuy_leader_id,groupbuy_leader.wx_avatar,groupbuy_leader.wx_nickname,ziti_address.phone_num,ziti_address.add_time,ziti_address.address_id,ziti_address.seller_name,ziti_address.area_info,ziti_address.address,ziti_address.state,ziti_address.have_license,ziti_address.review_msg';
		$groupbuy_leader_list = $model_groupbuy_leader->getGroupbuyLeaderAndZitiAddressList($condition,$field,'',10,'add_time desc');

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

			showMessage('自提点审核成功', urlAdmin('groupbuy_leader', 'ziti_address_review'));

		}

		$condition['ziti_address.address_id'] = intval($_GET['address_id']);

		$field = 'groupbuy_leader.groupbuy_leader_id,groupbuy_leader.wx_avatar,ziti_address.phone_num,ziti_address.add_time,ziti_address.address_id,ziti_address.seller_name,ziti_address.area_info,ziti_address.address,ziti_address.state,ziti_address.have_license,groupbuy_leader.id_photo_front,groupbuy_leader.id_photo_back,ziti_address.ziti_photo,ziti_address.review_msg';
		$ziti_address_info = $model_groupbuy_leader->getGroupbuyLeaderAndZitiAddressInfo($condition,$field);

		Tpl::output('ziti_address_info',$ziti_address_info);
		Tpl::showpage('groupbuy_leader.ziti_address_review_info');
	}

	/**
	 * 歇业审核
	 */
	public function ziti_address_break_reviewOp(){

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

		$field = 'groupbuy_leader.groupbuy_leader_id,groupbuy_leader.wx_avatar,groupbuy_leader.wx_nickname,ziti_address.phone_num,ziti_address.add_time,ziti_address.address_id,ziti_address.seller_name,ziti_address.area_info,ziti_address.address,ziti_address.state,ziti_address.have_license,ziti_address.xie_state,ziti_address.xie_time_start,ziti_address.xie_time_end,xie_reason.content';
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

    /**
     * ajax更改团长
     */
    public function ajaxEditZitiAddressOp() {
		$model_groupbuy_leader = Model('groupbuy_leader');
		// $ziti_address_list = $model_groupbuy_leader->getGroupbuyLeaderAndZitiAddressList(['ziti_address.gl_id'=>91,'ziti_address.address_id'=>['neq',47]],'ziti_address.address_id','right',0,'address_id desc');
		// echo '<pre>';print_r($ziti_address_list);die;

        if (chksubmit()) {
			
            $groupbuy_leader_id = $_POST['groupbuy_leader_id'];
            $ori_groupbuy_leader_id = $_POST['ori_groupbuy_leader_id'];
            $address_id = $_POST['address_id'];
			$ziti_photo = Model()->table('ziti_address')->getfby_address_id($address_id,'ziti_photo');
			
			if(!$address_id){
            	showDialog('参数错误', 'reload');
			}

			$model_ziti_address = Model('ziti_address');

			//解绑
			if($groupbuy_leader_id == 0){
				$gl_id = '';
			}else{
				$gl_id = $groupbuy_leader_id;
			}

			//默认自提点问题
			$current = $this->current_ziti_address($gl_id,$ori_groupbuy_leader_id,$address_id);
			// showDialog($current, 'reload');
			if(!$current){
            	showDialog('编辑失败，自提点默认状态修改失败', 'reload');
			}

			$result = $model_ziti_address->editAddress(['gl_id'=>$gl_id],['address_id'=>$address_id]);

			if(!$result){
            	showDialog('编辑失败，自提点编辑失败', 'reload');
			}
			
			//移动图片位置
			$ori_dir = '/data/default/wzxd/data/upload/' . DIR_UPLOAD_ZITI . '/' . $ori_groupbuy_leader_id . '/' . $ziti_photo;
			$dir = '/data/default/wzxd/data/upload/' . DIR_UPLOAD_ZITI . '/' . $groupbuy_leader_id . '/' . $ziti_photo;
			mkdir('/data/default/wzxd/data/upload/' . DIR_UPLOAD_ZITI . '/' . $groupbuy_leader_id,0777);
			$rename_result = rename($ori_dir,$dir);

			if($rename_result){
				showDialog('编辑成功', 'reload', 'succ');
			}else{
				showDialog('编辑失败，图片移动失败', 'reload');
			}
        }
		if(!$_GET['address_id']){
			showDialog('自提点ID不能为空', 'reload');
		}

		$groupbuy_leader_id = '';
		$wx_nickname = '';
		
		if($_GET['groupbuy_leader_id']!='undefined'){
			$groupbuy_leader_id = $_GET['groupbuy_leader_id'];
			$wx_nickname = Model()->table('groupbuy_leader')->getfby_groupbuy_leader_id($groupbuy_leader_id,'wx_nickname');
		}

		//获取全部团长信息
		$groupbuy_leader_list = $model_groupbuy_leader->getGroupbuyLeaderList([],'groupbuy_leader_id,wx_nickname',10000,'groupbuy_leader_id desc');
		
        Tpl::output('groupbuy_leader_list', $groupbuy_leader_list);
        Tpl::output('groupbuy_leader_id', $groupbuy_leader_id);
        Tpl::output('wx_nickname', $wx_nickname);
        Tpl::output('address_id', $_GET['address_id']);

        Tpl::showpage('groupbuy_leader.edit_ziti_address', 'null_layout');
    }

	/**
	 * 团长助手列表
	 */
	public function groupbuy_leader_assistant_listOp(){
		
		$model_groupbuy_leader = Model('groupbuy_leader');
		
		$condition = [];

		if($_GET['search_groupbuy_leader_id']){
			$condition['groupbuy_leader.groupbuy_leader_id'] = $_GET['search_groupbuy_leader_id'];
		}

		if($_GET['search_wx_nickname']){
			$condition['groupbuy_leader.wx_nickname'] = array('like','%'.$_GET['search_wx_nickname'].'%');
		}

		if($_GET['search_username']){
			$condition['groupbuy_leader_assistant.username'] = array('like','%'.$_GET['search_username'].'%');
		}

		if($_GET['search_name']){
			$condition['groupbuy_leader_assistant.name'] = array('like','%'.$_GET['search_name'].'%');
		}

		if($_GET['search_phone_number']){
			$condition['groupbuy_leader_assistant.phone_number'] = $_GET['search_phone_number'];
		}

		$start_add_time = strtotime($_GET['start_add_time']);
		$end_add_time = strtotime($_GET['end_add_time']);

		if($start_add_time || $end_add_time){
        	$condition['groupbuy_leader_assistant.add_time'] = array('between',"$start_add_time,$end_add_time");
		}

		if($_GET['state']!=''){
			$condition['groupbuy_leader_assistant.state'] = $_GET['state'];
		}

		$assistant_list = $model_groupbuy_leader->getGroupbuyLeaderAndAssistantList($condition,'groupbuy_leader.groupbuy_leader_id,groupbuy_leader.wx_nickname,groupbuy_leader_assistant.gl_assistant_id,groupbuy_leader_assistant.username,groupbuy_leader_assistant.name,groupbuy_leader_assistant.phone_number,groupbuy_leader_assistant.add_time,groupbuy_leader_assistant.state,groupbuy_leader_assistant.remark','right',10,'gl_assistant_id desc');
		
		Tpl::output('assistant_list',$assistant_list);
		Tpl::output('page',$model_groupbuy_leader->showpage());

		Tpl::showpage('groupbuy_leader.assistant_list');
	}

	/**
	 * 默认自提点问题
	 * @param $gl_id 新团长id
	 * @param $o_gl_id 原团长id
	 * @param $address_id 自提点id
	 */
	private function current_ziti_address($gl_id, $o_gl_id, $address_id){
		$model_ziti_address = Model('ziti_address');
		$model_groupbuy_leader = Model('groupbuy_leader');

		if($gl_id == 0 && $o_gl_id == 0){
			//如果修改前后都是0，不用做任何修改
			return 1;
		}

		//编辑自提点了
		$o_ziti_address_count = $model_groupbuy_leader->getGroupbuyLeaderZitiAddressCount(['gl_id'=>$o_gl_id,'address_id'=>['neq',$address_id]]);
		//判断原团长（除当前自提点以外）是否还有自提点
		if($o_ziti_address_count>0){
			//如果有则判断原团长的自提点中是否还有默认自提点
			$ziti_address_current_count = $model_groupbuy_leader->getGroupbuyLeaderZitiAddressCount(['gl_id'=>$o_gl_id,'address_id'=>['neq',$address_id],'is_current'=>1]);
			if($ziti_address_current_count == 1){
				$return1 = 1;
				//如果有则不做改变，如果没有则判断原团长id是否为0，不为0挑最新一条自提点作为默认自提点，为0不处理
			}else{
				if($o_gl_id>0){
					$ziti_address_list = $model_groupbuy_leader->getGroupbuyLeaderAndZitiAddressList(['ziti_address.gl_id'=>$o_gl_id,'ziti_address.address_id'=>['neq',$address_id]],'ziti_address.address_id','right',0,'address_id desc');
					$new_address_id = $ziti_address_list[0]['address_id'];
					$return1 = $model_ziti_address->editAddress(['is_current'=>1],['address_id'=>$new_address_id]) && $model_ziti_address->editAddress(['is_current'=>0],['address_id'=>$address_id]);
				}else{
					$ziti_address_count = $model_groupbuy_leader->getGroupbuyLeaderZitiAddressCount(['gl_id'=>$gl_id,'address_id'=>['neq',$address_id]]);
					//判断新团长（除当前自提点以外）是否还有自提点
					if($ziti_address_count>0){
						//如果有则不做改变（新团长如果有自提点则一定存在默认）
						$return1 = $model_ziti_address->editAddress(['is_current'=>0],['address_id'=>$address_id]);
					}else{
						//如果没有则该团长是单团长（名下没有自提点），直接修改该自提点为默认自提点
						$return1 = $model_ziti_address->editAddress(['is_current'=>1],['address_id'=>$address_id]);
					}
				}
			}
		}else{
		//如果没了，团长属于单团长状态了，更改自提点默认值
			$return1 = $model_ziti_address->editAddress(['is_current'=>0],['address_id'=>$address_id]);
		}

		$model_groupbuy_leader_assistant = Model('groupbuy_leader_assistant');
		//判断原团长是否有设置该自提点为默认的助手
		$assistant_list = $model_groupbuy_leader->getAssistantList(['gl_id'=>$o_gl_id,'default_ziti_id'=>$address_id],'gl_assistant_id',0,'gl_assistant_id desc');
		if(count($assistant_list)>0){
		//如果有则修改该团长助手的默认自提点为原团长自提点列表里的第一个自提点
			$ziti_address_list = $model_groupbuy_leader->getGroupbuyLeaderAndZitiAddressList(['ziti_address.gl_id'=>$o_gl_id,'ziti_address.address_id'=>['neq',$address_id]],'ziti_address.address_id','right',0,'address_id desc');
			$new_address_id = $ziti_address_list[0]['address_id'];
			$model_groupbuy_leader_assistant = Model('groupbuy_leader_assistant');
			foreach($assistant_list as $assistant){
				$model_groupbuy_leader_assistant->editGroupbuyLeaderAssistant(['gl_assistant_id'=>$assistant['gl_assistant_id']],['default_ziti_id'=>$new_address_id]);
			}
			$return2 = 1;
		//如果则判断新团长是否有默认自提点为空的助手
		}else{
			$assistant_list = Model()->query('select gl_assistant_id from 718shop_groupbuy_leader_assistant where gl_id='.$gl_id.' and default_ziti_id is null');
			if(count($assistant_list)>0){
				//如果有则查询新团长是否有默认自提点
				$ziti_address_current_count = $model_groupbuy_leader->getGroupbuyLeaderZitiAddressCount(['gl_id'=>$gl_id,'address_id'=>['neq',$address_id],'is_current'=>1]);
				$new_address_id = $model_ziti_address->getAddressInfo(['gl_id'=>$gl_id,'address_id'=>['neq',$address_id],'is_current'=>1],'address_id')['address_id'];
				if($ziti_address_current_count>0){
					foreach($assistant_list as $assistant){
						$model_groupbuy_leader_assistant->editGroupbuyLeaderAssistant(['gl_assistant_id'=>$assistant['gl_assistant_id']],['default_ziti_id'=>$new_address_id]);
					}
				}else{
					foreach($assistant_list as $assistant){
						$model_groupbuy_leader_assistant->editGroupbuyLeaderAssistant(['gl_assistant_id'=>$assistant['gl_assistant_id']],['default_ziti_id'=>$address_id]);
					}
				}
			}
			$return2 = 1;
		}
		return $return1 && $return2;
	}
}
