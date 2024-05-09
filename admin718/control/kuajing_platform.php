<?php
/**
 * 电商平台跨境参数信息
 *
 *
 *
 **/

defined('In718Shop') or exit('Access Invalid!');
class kuajing_platformControl extends SystemControl{
	
	public function __construct(){
		parent::__construct();
		Language::read('setting');
	}


	public function indexOp(){
		$lang	= Language::getLangContent();
		$model = Model('kuajing_platform');
		$condition	= array();
		
		
		if($_POST['like_sg_name']) {
        	$condition['platform_name'] = array('like','%'.$_POST['like_sg_name'].'%');
			
			$result=$model->where($condition)->select();
		}else{
			$result=$model->page('10')->select();
		}

		Tpl::output('like_sg_name',$_POST['like_sg_name']);
		Tpl::output('result',$result);
		Tpl::output('page',$model->showpage());
		Tpl::showpage('kuajing_platform.index');
	}


	/**
     * 跨境平台参数增加
     */
    public function addOp() {

    	Tpl::showpage('kuajing_platform.add');

}


	/**
     * 跨境平台参数增加  gai
     */
    public function add_saveOp() {
       $model_platform = Model ( 'kuajing_platform' );
     	if (chksubmit()){
			//验证表单信息
			//$platform_name=$_POST['platform_name'];
				$data = array();
				$data['platform_name']       = $_POST['platform_name'];
				$data['code_guan']       = $_POST['code_guan'];
				$data['code_jian']       = $_POST['code_jian'];
				
				$model_platform->insert($data);
				$result=$model_platform->page('10')->select();
	            Tpl::output('result',$result);
				Tpl::output('page',$model_platform->showpage());
	            Tpl::showpage('kuajing_platform.index');	    
           
       }
}
	/**
     * 跨境平台参数编辑 xinzeng
     */
    public function editOp() {
		//$platform_id = trim($_GET['platform_id']);
	    $model = Model();
		$condition = array();
        //$condition['platform_id'] = array('in',$platform_id);
        $condition['platform_id'] = $_GET['platform_id'];
        $result = $model->table('kuajing_platform')->where($condition)->find();
		Tpl::output('result',$result);
    	Tpl::showpage('kuajing_platform.edit');
}

	/**
     * 跨境平台参数编辑  xinzeng
     */
   public function edit_saveOp() {
   		$model_hs = Model ( 'kuajing_platform' );
	    $lang	= Language::getLangContent();
	    $model = Model();
        $condition = array();
        $condition['platform_id'] = $_POST['platform_id'];
		$data['platform_name']       = $_POST['platform_name'];
		$data['code_guan']       = $_POST['code_guan'];
		$data['code_jian']       = $_POST['code_jian'];
		$result=$model->table('kuajing_platform')->where($condition)->update($data);
		if ($result){
			showMessage($lang['nc_common_save_succ'],'index.php?act=kuajing_platform&op=index');
		}else {
			showMessage($lang['nc_common_save_fail'],'index.php?act=kuajing_platform&op=index');
		}
	}

	
	
	/**
	 * 跨境平台参数删除  xinzeng
	 */
	public function delOp(){
		$lang	= Language::getLangContent();
		$platform_id = trim($_POST['platform_id']);
        $model= Model();
        $condition = array();
        $condition['platform_id'] = array('in',$platform_id);
        $result = $model->table('kuajing_platform')->where($condition)->delete();
        if($result) {
            $this->log(Language::get('hs_code_del').$_POST['platform_id'], 1);
            showMessage(Language::get('nc_common_del_succ'),'index.php?act=kuajing_platform&op=index');
        } else {
            $this->log(Language::get('hs_code_del').$_POST['platform_id'], 0);
            showMessage(Language::get('nc_common_del_fail'),'ndex.php?act=kuajing_platform&op=index');
        }


	}


	
}
