<?php
/**
 * 小店帮助管理
 *
 *
 *
 ** */

defined('In718Shop') or exit('Access Invalid!');
class xd_helpControl extends SystemControl{
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 帮助列表
	 */
	public function xd_helpOp() {
		$model_help = Model('xd_help');
		$condition = array();
		if (trim($_GET['key']) != '') {
			$condition['help_title'] = array('like','%'.$_GET['key'].'%');
		}
		$page=10;
		$help_list = Model()->table('xd_help')->field('*')->where($condition)->page($page)->order('id desc')->select();
		Tpl::output('help_list',$help_list);
		Tpl::output('show_page',$model_help->showpage());
		Tpl::showpage('xd_help.list');
	}

	/**
	 * 新增帮助
	 *
	 */
	public function add_helpOp() {
		if (chksubmit()) {
		    $help_array = array();
		    $help_array['help_title'] = $_POST['help_title'];
		    $help_array['help_content'] = $_POST['content'];
		    $help_array['update_time'] = time();
		    $help_id = Model()->table('xd_help')->insert($help_array);
			if ($help_id) {
			    $this->log('新增小店帮助，编号'.$help_id);
				showMessage(Language::get('nc_common_save_succ'),'index.php?act=xd_help&op=xd_help');
			} else {
				showMessage(Language::get('nc_common_save_fail'));
			}
		}
	    Tpl::showpage('xd_help.add');
	}

	/**
	 * 编辑帮助
	 *
	 */
	public function edit_helpOp() {
		// $model_help = Model('xd_help');
		$condition = array();
		$help_id = intval($_GET['help_id']);
		$condition['id'] = $help_id;
		$help_list = Model()->table('xd_help')->field('*')->where($condition)->find();
		Tpl::output('help',$help_list);
		if (chksubmit()) {
		    $help_array = array();
		    $help_array['help_title'] = $_POST['help_title'];
		    $help_array['help_content'] = $_POST['content'];
		    $help_array['update_time'] = time();
			// if (empty($condition)) {
			// 			return false;
			// 		}
			if (is_array($help_array)) {
				$result = Model()->table('xd_help')->where($condition)->update($help_array);
				 $this->log('编辑店铺帮助，编号'.$help_id);
				showMessage(Language::get('nc_common_save_succ'),'index.php?act=xd_help&op=xd_help');
			} else {
				showMessage(Language::get('nc_common_save_fail'));
			}
		}
	    Tpl::showpage('xd_help.edit');
	}

	/**
	 * 删除帮助
	 *
	 */
	public function del_helpOp() {
		$condition = array();
		$condition['id'] = intval($_GET['help_id']);
		$result = Model()->table('xd_help')->where($condition)->delete();
		if ($result) {
		    $this->log('删除小店帮助，编号'.$condition['id']);
		    showMessage(Language::get('nc_common_del_succ'),'index.php?act=xd_help&op=xd_help');
		} else {
		    showMessage(Language::get('nc_common_del_fail'));
		}
	}
}
