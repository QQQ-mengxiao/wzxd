<?php
/**
 * 云打印机设备管理
 *
 *
 *
 **/

defined('In718Shop') or exit('Access Invalid!');

class dayin_settingControl extends BaseSellerControl {
	const LINK_dayin_LIST = 'index.php?act=dayin_setting&op=dayin_list';
	/**
     * 云打印机列表管理
     **/
    public function dayin_listOp() {

    	$model = Model('yundayin');
		$condition	= array();
    	 if(!empty($_GET['dayin_sn'])) {
            $condition['dayin_sn'] = array('like', '%'.$_GET['dayin_sn'].'%');
        }
        if(!empty($_GET['dayin_name'])) {
            $$condition['dayin_name'] = array('like', '%'.$_GET['dayin_name'].'%');
        }
        $result=$model->where($condition)->order('dayin_id desc')->page('8')->select();
        
		Tpl::output('dayin_sn',$_GET['dayin_sn']);
		Tpl::output('dayin_name',$_GET['dayin_name']);
		Tpl::output('result',$result);
		Tpl::output('page',$model->showpage());
		Tpl::showpage('dayinji.index');

    }


	/**
     * 云打印机增加
     */
    public function addOp() {

    	Tpl::showpage('dayinji.add');

}


	/**
     * 增加保存云打印机  
     */
    public function add_saveOp() {
       $model_tool = Model ( 'yundayin' );
       
       //验证表单信息
		//$tool_name=$_POST['tool_name'];
		$data = array();
		$data['dayin_sn']       = trim($_POST['dayin_sn']);
		$data['dayin_name']       = trim($_POST['dayin_name']);
		$data['dayin_key']       = trim($_POST['dayin_key']);
		$data['dayin_user']       = trim($_POST['dayin_user']);
		$data['ukey']       = trim($_POST['ukey']);
		$data['mobile']       = trim($_POST['mobile']);
		$data['note']       = trim($_POST['note']);
		
		$result = $model_tool->insert($data);
		if ($result){
			showMessage('新增云打印机成功!',self::LINK_dayin_LIST,'succ','',3);
		}else {
			showMessage('新增云打印机失败!',self::LINK_dayin_LIST);
		}
	}
	/**
     * 编辑云打印机
     */
    public function editOp() {
	    $model = Model ( 'yundayin' );
	    $condition = array();
        $condition['dayin_id'] = intval($_GET['dayin_id']);
        $dayin_info = $model->where($condition)->find();
        if(empty($dayin_info)) {
            showMessage('参数错误','','','error');
        }
		Tpl::output('dayin_info',$dayin_info);
		// //输出导航
    	//self::profile_menu('xianshi_edit');
    	Tpl::showpage('dayinji.add');
}

	/**
     * 编辑保存云打印机 
     */
   public function edit_saveOp() {
   		$model = Model ( 'yundayin' );

   		$condition = array();
        $condition['dayin_id'] = intval($_POST['dayin_id']);

        $data['dayin_sn']       = trim($_POST['dayin_sn']);
		$data['dayin_name']       = trim($_POST['dayin_name']);
		$data['dayin_key']       = trim($_POST['dayin_key']);
		$data['dayin_user']       = trim($_POST['dayin_user']);
		$data['ukey']       = trim($_POST['ukey']);
		$data['mobile']       = trim($_POST['mobile']);
		$data['note']       = trim($_POST['note']);
		
		$result=$model->where($condition)->update($data);
		if ($result){
			showMessage('编辑云打印机成功!',self::LINK_dayin_LIST,'succ','',3);
		}else {
			showMessage('编辑云打印机失败!',self::LINK_dayin_LIST);
		}
	}

	/**
	 * 云打印机 删除
	 */
	public function delOp(){
		
		$model = Model ( 'yundayin' );

   		$condition = array();
        $condition['dayin_id'] = intval($_POST['dayin_id']);
        
        $result = $model->where($condition)->delete();
        if($result) {
            showMessage('删除云打印机成功！',self::LINK_dayin_LIST);
        } else {
            showMessage('删除云打印机失败！',self::LINK_dayin_LIST);
        }

	}
	
}
