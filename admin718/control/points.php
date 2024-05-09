<?php
/**
 * 积分管理
 **/

defined('In718Shop') or exit('Access Invalid!');
class pointsControl extends SystemControl{
	const EXPORT_SIZE = 5000;
	public function __construct(){
		parent::__construct();
		Language::read('points');
		//判断系统是否开启积分功能
		if (C('points_isuse') != 1){
			showMessage(Language::get('admin_points_unavailable'),'index.php?act=dashboard&op=welcome','','error');
		}
	}

	/**
	 * 积分添加
	 */
	public function addpointsOp(){
		if (chksubmit()){

			$obj_validate = new Validate();
			$obj_validate->validateparam = array(
				array("input"=>$_POST["member_id"], "require"=>"true", "message"=>Language::get('admin_points_member_error_again')),
				array("input"=>$_POST["pointsnum"], "require"=>"true",'validator'=>'Compare','operator'=>' >= ','to'=>1,"message"=>Language::get('admin_points_points_min_error'))
			);
			$error = $obj_validate->validate();
			if ($error != ''){
				showMessage($error,'','','error');
			}
			//查询会员信息
			$obj_member = Model('member');
			$member_id = intval($_POST['member_id']);
			$member_info = $obj_member->getMemberInfo(array('member_id'=>$member_id));

			if (!is_array($member_info) || count($member_info)<=0){
				showMessage(Language::get('admin_points_userrecord_error'),'index.php?act=points&op=addpoints','','error');
			}

			$pointsnum = intval($_POST['pointsnum']);
			if ($_POST['operatetype'] == 2 && $pointsnum > intval($member_info['member_points'])){
				showMessage(Language::get('admin_points_points_short_error').$member_info['member_points'],'index.php?act=points&op=addpoints','','error');
			}

			$obj_points = Model('points');
			$insert_arr['pl_memberid'] = $member_info['member_id'];
			$insert_arr['pl_membername'] = $member_info['member_name'];
			$admininfo = $this->getAdminInfo();
			$insert_arr['pl_adminid'] = $admininfo['id'];
			$insert_arr['pl_adminname'] = $admininfo['name'];
			if ($_POST['operatetype'] == 2){
				$insert_arr['pl_points'] = -$_POST['pointsnum'];
			}else {
				$insert_arr['pl_points'] = $_POST['pointsnum'];
			}
			if ($_POST['pointsdesc']){
				$insert_arr['pl_desc'] = trim($_POST['pointsdesc']);
			} else {
				$insert_arr['pl_desc'] = Language::get('admin_points_system_desc');
			}
			$result = $obj_points->savePointsLog('system',$insert_arr,true);
			if ($result){
				$this->log(L('admin_points_mod_tip').$member_info['member_name'].'['.(($_POST['operatetype'] == 2)?'':'+').strval($insert_arr['pl_points']).']',null);
				showMessage(Language::get('nc_common_save_succ'),'index.php?act=points&op=addpoints');
			}else {
				showMessage(Language::get('nc_common_save_fail'),'index.php?act=points&op=addpoints','','error');
			}
		}else {
			Tpl::showpage('points.add');
		}
	}
	public function checkmemberOp(){
		$name = trim($_GET['name']);
		if (!$name){
			echo ''; die;
		}
		/**
		 * 转码
		 */
		if(strtoupper(CHARSET) == 'GBK'){
			$name = Language::getGBK($name);
		}
		$obj_member = Model('member');
		$member_info = $obj_member->getMemberInfo(array('member_name'=>$name));
		if (is_array($member_info) && count($member_info)>0){
			if(strtoupper(CHARSET) == 'GBK'){
				$member_info['member_name'] = Language::getUTF8($member_info['member_name']);
			}
			echo json_encode(array('id'=>$member_info['member_id'],'name'=>$member_info['member_name'],'points'=>$member_info['member_points']));
		}else {
			echo ''; die;
		}
	}
	/**
	 * 积分日志列表
	 */
	public function pointslogOp(){
		$condition_arr = array();
		$condition_arr['pl_membername_like'] = trim($_GET['mname']);
		$condition_arr['pl_adminname_like'] = trim($_GET['aname']);
		if ($_GET['stage']){
			$condition_arr['pl_stage'] = trim($_GET['stage']);
		}
		$condition_arr['saddtime'] = strtotime($_GET['stime']);
		$condition_arr['eaddtime'] = strtotime($_GET['etime']);
        if($condition_arr['eaddtime'] > 0) {
            $condition_arr['eaddtime'] += 86400;
        }
		$condition_arr['pl_desc_like'] = trim($_GET['description']);
		//分页
		$page	= new Page();
		$page->setEachNum(10);
		$page->setStyle('admin');
		//查询积分日志列表
		$points_model = Model('points');
		$list_log = $points_model->getPointsLogList($condition_arr,$page,'*','');
		//信息输出
		Tpl::output('show_page',$page->show());
		Tpl::output('list_log',$list_log);
		Tpl::showpage('pointslog');
	}

	/**
	 * 积分日志列表导出
	 */
	public function export_step1Op(){
		$condition_arr = array();
		$condition_arr['pl_membername_like'] = trim($_GET['mname']);
		$condition_arr['pl_adminname_like'] = trim($_GET['aname']);
		if ($_GET['stage']){
			$condition_arr['pl_stage'] = trim($_GET['stage']);
		}
		$condition_arr['saddtime'] = strtotime($_GET['stime']);
		$condition_arr['eaddtime'] = strtotime($_GET['etime']);
        if($condition_arr['eaddtime'] > 0) {
            $condition_arr['eaddtime'] += 86400;
        }
		$condition_arr['pl_desc_like'] = trim($_GET['description']);
		$page	= new Page();
		$page->setEachNum(self::EXPORT_SIZE);
		$points_model = Model('points');
		$list_log = $points_model->getPointsLogList($condition_arr,$page,'*','');
		if (!is_numeric($_GET['curpage'])){
			$count = $page->getTotalNum();
			$array = array();
			if ($count > self::EXPORT_SIZE ){	//显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}
				Tpl::output('list',$array);
				Tpl::output('murl','index.php?act=pointslog&op=pointslog');
				Tpl::showpage('export.excel');
			}else{	//如果数量小，直接下载
				$this->createExcel($list_log);
			}
		}else{	//下载
			$this->createExcel($list_log);
		}
	}

	/**
	 * 生成excel
	 *
	 * @param array $data
	 */
	private function createExcel($data = array()){
		Language::read('export');
		import('libraries.excel');
		$excel_obj = new Excel();
		$excel_data = array();
		//设置样式
		$excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
		//header
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_pi_member'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_pi_system'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_pi_point'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_pi_time'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_pi_jd'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_pi_ms'));
		$state_cn = array(Language::get('admin_points_stage_regist'),Language::get('admin_points_stage_login'),Language::get('admin_points_stage_comments'),Language::get('admin_points_stage_order'),Language::get('admin_points_stage_system'),Language::get('admin_points_stage_pointorder'),Language::get('admin_points_stage_app'));
		foreach ((array)$data as $k=>$v){
			$tmp = array();
			$tmp[] = array('data'=>$v['pl_membername']);
			$tmp[] = array('data'=>$v['pl_adminname']);
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['pl_points']));
			$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['pl_addtime']));
			$tmp[] = array('data'=>str_replace(array('regist','login','comments','order','system','pointorder','app'),$state_cn,$v['pl_stage']));
			$tmp[] = array('data'=>$v['pl_desc']);

			$excel_data[] = $tmp;
		}
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset(L('exp_pi_jfmx'),CHARSET));
		$excel_obj->generateXML($excel_obj->charset(L('exp_pi_jfmx'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
	}
     /**
     * 连续签到送积分设置
     *
     */
	// public function serialsignOp(){
 //        $model_setting = Model('setting');
 //        $list_setting = $model_setting->getListSetting();
 //        $serial_sign = $list_setting['serial_points_reg']?unserialize($list_setting['serial_points_reg']):0;
 //        if (chksubmit()) {
 //            // $points = $_POST['points'];
 //            // $serial_sign_rule['serial_points_reg'] = $points;
 //             $points1 = $_POST['points1'];
 //             $points2 = $_POST['points2'];
 //            $serial_sign_rule['serial_points_reg'] = serialize(array('points1'=>$points1,'points2'=>$points2));
 //            // $list_setting=serialize($points);
 //            $result = $model_setting->updateSetting($serial_sign_rule);
 //            if ($result) {
 //                $this->log('连续签到送积分编辑', 1);
 //                showDialog(L('nc_common_save_succ'), 'reload', 'succ');
 //            } else {
 //                $this->log('连续签到送积分编辑', 0);
 //                showDialog(L('nc_common_save_fail'));
 //            }
 //        }
 //        Tpl::output('serial_sign',$serial_sign);
 //        Tpl::showpage('points.serialsign');
 //    }
     	public function serialsignOp(){
        $model_qd_ruler= Model('qd_ruler');
        $condition=array();
        $serial_sign= $model_qd_ruler->getqd_rulerList($condition);
        // var_dump($_POST['day5']);die;
        if (chksubmit()) {
        	// var_dump($_POST['day5']);die;
             $points1=$_POST['points1'];
             $exp1 = $_POST['exp1'];
             $update_arr1= array('points' =>$points1 , 'exp' =>$exp1 ,);
             $result = $model_qd_ruler->editqd_ruler($update_arr1,1);
             $points2 = $_POST['points2']; 
             $exp2 = $_POST['exp2'];
             $day2=$_POST['day2'];
             $update_arr2= array('points' =>$points2 , 'exp' =>$exp2 ,'days' =>$day2 ,);
             $result = $model_qd_ruler->editqd_ruler($update_arr2,2);
             $points3= $_POST['points3'];
             $exp3 = $_POST['exp3'];
             $day3=$_POST['day3'];
             $update_arr3= array('points' =>$points3 , 'exp' =>$exp3 ,'days' =>$day3);
             $result = $model_qd_ruler->editqd_ruler($update_arr3,3);
             $points4 = $_POST['points4'];
             $exp4 = $_POST['exp4'];
             $day4=$_POST['day4'];
             $update_arr4= array('points' =>$points4 , 'exp' =>$exp4,'days' =>$day4);
             $result = $model_qd_ruler->editqd_ruler($update_arr4,4);
             $points5 = $_POST['points5'];
             $exp5= $_POST['exp5'];
             $day5=$_POST['day5'];
             $update_arr5= array('points' =>$points5, 'exp' =>$exp5,'days' =>$day5);
             $result5 = $model_qd_ruler->editqd_ruler($update_arr5,5);
           
            if ($result5) {
                $this->log('连续签到编辑', 1);
                showDialog(L('nc_common_save_succ'), 'reload', 'succ');
            } else {
                $this->log('连续签到编辑', 0);
                showDialog(L('nc_common_save_fail'));
            }
        }
        Tpl::output('serial_sign',$serial_sign);
        Tpl::showpage('points.serialsign');
    }
}
