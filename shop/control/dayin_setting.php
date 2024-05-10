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
        	$condition['dayin_name'] = array('like',"%".$_GET['dayin_name']."%");
        }
        
        $result=$model->field('*')->where($condition)->page(8)->order('dayin_id desc')->select();
		Tpl::output('dayin_sn',$_GET['dayin_sn']);
		Tpl::output('dayin_name',$_GET['dayin_name']);
		Tpl::output('result',$result);
		Tpl::output('show_page',$model->showpage());
		Tpl::showpage('dayinji.index');

    }


	/**
     * 云打印机增加
     */
    public function addOp() {
    	$model_daddress = Model('ziti_address');
       	$condition = array();
       	$condition['store_id'] = $_SESSION['store_id'];
       	$address_list = $model_daddress->getAddressList($condition);
       	$scondition['addtime'] = array('gt',0);
        $storage_list =Model()->table('storage')->where($scondition)->select();
		Tpl::output('address_list',$address_list);
		Tpl::output('storage_list',$storage_list);
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
		$data['is_wai']       = intval($_POST['is_wai']);
		$data['note']       = trim($_POST['note']);
		if(!empty($_POST['order_type'])){
			$data['order_type'] =  implode(",",$_POST['order_type']);
		}else{
			$data['order_type'] =  0;
		}
		$data['address_id'] = intval($_POST['address_id']);
		$data['storage_id'] = intval($_POST['storage_id']);
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

    	//打印机信息
	    $model = Model ( 'yundayin' );
	    $condition = array();
	    $dayin_id = intval($_GET['dayin_id']);
        $condition['dayin_id'] = $dayin_id;
        $dayin_info = $model->where($condition)->find();
        $dayin_info['order_type'] = explode(",", $dayin_info['order_type']);
        
       // var_dump(in_array("3", $dayin_info['order_type']));die;
        if(empty($dayin_info)) {
            showMessage('参数错误','','','error');
        }
        //自提地址列表
        $model_address = Model('ziti_address');
       	$condition = array();
       	$condition['store_id'] = $_SESSION['store_id'];
       	$address_list = $model_address->getAddressList($condition);
		Tpl::output('address_list',$address_list);
		$scondition['addtime'] = array('gt',0);
        $storage_list =Model()->table('storage')->where($scondition)->select();
        Tpl::output('storage_list',$storage_list);
		Tpl::output('address_info',$address_info);
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
		$data['is_wai']       = intval($_POST['is_wai']);
		$data['note']       = trim($_POST['note']);
		if(!empty($_POST['order_type'])){
			$data['order_type'] =  implode(",",$_POST['order_type']);
		}else{
			$data['order_type'] =  0;
		}
		$data['address_id'] = intval($_POST['address_id']);
		$data['storage_id'] = intval($_POST['storage_id']);
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
