<?php
/**
 * 网站设置
 */

defined('In718Shop') or exit('Access Invalid!');
class mine_settingControl extends SystemControl{
	public function __construct(){
		parent::__construct();
		Language::read('setting');
	}

	/**
	 * 防灌水设置
	 */
	public function baseOp(){
		$model_setting = Model('setting');
		if (chksubmit()){
			$update_array = array();
			$update_array['service_number'] = $_POST['service_number'];
			$update_array['about_us'] = $_POST['about_us'];
			$result = $model_setting->updateSetting($update_array);
			if ($result === true){
				$this->log('w',1);
				showMessage(L('nc_common_save_succ'));
			}else {
				$this->log(L('nc_edit,dis_dump'),0);
				showMessage(L('nc_common_save_fail'));
			}
		}
		$list_setting = $model_setting->getListSetting();
		Tpl::output('mine_setting',$list_setting);
		Tpl::showpage('mine_setting');
	}

	
}
