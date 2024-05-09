<?php
/**
 * 通知栏管理
 *
 *
 * */

defined('In718Shop') or exit('Access Invalid!');
class noticeboardControl extends SystemControl{
	public function __construct(){
		parent::__construct();
		Language::read('noticeboard');
	}

	/**
	 * 通知栏
	 */
	public function noticeboardOp(){
		$lang	= Language::getLangContent();
		$model_noticeboard = Model('noticeboard');
		$condition=  array();
		/**
		 * 删除
		 */
		if (chksubmit()){
			if (is_array($_POST['del_id']) && !empty($_POST['del_id'])){
				$del_str=implode(',',$_POST['del_id']);
				$where  = "where no_id in (".$del_str.")";
			    Db::delete("noticeboard",$where);
				showMessage('删除成功');
			}else {
				showMessage('删除失败');
			}
		}
		/**
		 * 分页
		 */
		$page	= new Page();
		$page->setEachNum(10);
		$page->setStyle('admin');
		$noticeboard_list = $model_noticeboard->getnoticeboardList($condition,$page);
		// var_dump($noticeboard_list);die;
		Tpl::output('noticeboard_list',$noticeboard_list);
		Tpl::output('page',$page->show());
		Tpl::showpage('noticeboard.index');
	}

	/**
	 * 通知栏 添加
	 */
	public function noticeboard_addOp(){
		$lang	= Language::getLangContent();
		$model_noticeboard = Model('noticeboard');
		if (chksubmit()){
			/**
			 * 验证
			 */
			$obj_validate = new Validate();
			$obj_validate->validateparam = array(
				array("input"=>$_POST["nav_title"], "require"=>"true", "message"=>$lang['noticeboard_add_partner_null'])
			);

			$error = $obj_validate->validate();
			if ($error != ''){
				showMessage($error);
			}else {

				$insert_array = array();
				$insert_array['no_title'] = trim($_POST['nav_title']);
				$insert_array['no_content'] = trim($_POST['nav_content']);
				$insert_array['is_open'] = intval($_POST['nav_new_open']);
				$insert_array['no_url'] = trim($_POST['nav_url']);
				if($insert_array['is_open']  ==1){
                Model()->table('noticeboard')->where(array('is_open'=>1))->update(array('is_open'=>0));
				}				
				$result = $model_noticeboard->add($insert_array);
				if ($result){
					showMessage('新增成功','index.php?act=noticeboard&op=noticeboard');
				}else {
					showMessage('新增失败','index.php?act=noticeboard&op=noticeboard');
				}
			}
		}

		Tpl::showpage('noticeboard.add');
	}

	/**
	 * 通知栏 编辑
	 */
	public function noticeboard_editOp(){
		$lang	= Language::getLangContent();
		$model_noticeboard = Model('noticeboard');
		if (chksubmit()){
			/**
			 * 验证
			 */
			$obj_validate = new Validate();
			$obj_validate->validateparam = array(
				array("input"=>$_POST["nav_title"], "require"=>"true", "message"=>$lang['noticeboard_add_partner_null'])
			);
			$error = $obj_validate->validate();
			if ($error != ''){
				showMessage($error);
			}else {

				$update_array = array();
				$update_array['no_id'] = intval($_POST['nav_id']);
				$update_array['no_title'] = trim($_POST['nav_title']);
				$update_array['no_content'] = trim($_POST['nav_content']);
				$update_array['is_open'] = intval($_POST['nav_new_open']);
				$update_array['no_url'] = trim($_POST['nav_url']);
				// var_dump($update_array);die;
				if($update_array['is_open']  ==1){
                Model()->table('noticeboard')->where(array('is_open'=>1))->update(array('is_open'=>0));
				}				
				$result = $model_noticeboard->update($update_array);
				if ($result){
					showMessage('编辑成功','index.php?act=noticeboard&op=noticeboard');
				}else {
					showMessage('编辑失败','index.php?act=noticeboard&op=noticeboard');
				}
			}
		}
		$noticeboard_array = $model_noticeboard->getOnenoticeboard(intval($_GET['no_id']));
		if (empty($noticeboard_array)){
			showMessage($lang['param_error']);
		}

		Tpl::output('noticeboard_array',$noticeboard_array);
		Tpl::showpage('noticeboard.edit');
	}

	/**
	 * 删除通知栏
	 */
	public function noticeboard_delOp(){
		$lang	= Language::getLangContent();
		$model_noticeboard = Model('noticeboard');
		if (intval($_GET['no_id']) > 0){
			$model_noticeboard->del(intval($_GET['no_id']));
			showMessage('删除成功','index.php?act=noticeboard&op=noticeboard');
		}else {
			showMessage('删除失败','index.php?act=noticeboard&op=noticeboard');
				
		}
	}

	/**
	 * ajax操作
	 */
	public function ajaxOp(){
		switch ($_GET['branch']){
			/**
			 * 通知栏 排序
			 */
			case 'nav_sort':
				$model_noticeboard = Model('noticeboard');
				$update_array = array();
				$update_array['nav_id'] = intval($_GET['id']);
				$update_array[$_GET['column']] = trim($_GET['value']);
				$result = $model_noticeboard->update($update_array);
				dkcache('nav');
				echo 'true';exit;
				break;
		}
	}
}
