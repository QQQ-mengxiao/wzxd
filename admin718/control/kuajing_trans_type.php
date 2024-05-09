<?php
/**
 * 跨境运输方式
 *
 *
 *
 **/

defined('In718Shop') or exit('Access Invalid!');
class kuajing_trans_typeControl extends SystemControl{
	
	public function __construct(){
		parent::__construct();
		Language::read('setting');
	}


	public function indexOp(){
		$lang	= Language::getLangContent();
		$model = Model('kuajing_trans_type');
		$condition	= array();
		
		
		if($_POST['like_sg_name']) {
        	$condition['trans_type_name'] = array('like','%'.$_POST['like_sg_name'].'%');
			
			$result=$model->where($condition)->select();
		}else{
			$result=$model->page('10')->select();
		}

		Tpl::output('like_sg_name',$_POST['like_sg_name']);
		Tpl::output('result',$result);
		Tpl::output('page',$model->showpage());
		Tpl::showpage('kuajing_trans_type.index');
	}


	/**
     * 跨境运输方式
     */
    public function addOp() {

    	Tpl::showpage('kuajing_trans_type.add');

}


	/**
     * 跨境运输方式  gai
     */
    public function add_saveOp() {
       $model_trans_type = Model ( 'kuajing_trans_type' );
     	if (chksubmit()){
			//验证表单信息
			//$trans_type_name=$_POST['trans_type_name'];
				$data = array();
				$data['trans_type_name']       = $_POST['trans_type_name'];
				$data['code_guan']       = $_POST['code_guan'];
				$data['code_jian']       = $_POST['code_jian'];
				
				$model_trans_type->insert($data);
				$result=$model_trans_type->page('10')->select();
	            Tpl::output('result',$result);
				Tpl::output('page',$model_trans_type->showpage());
	            Tpl::showpage('kuajing_trans_type.index');	    
           
       }
}
	/**
     * 跨境运输方式 xinzeng
     */
    public function editOp() {
		//$trans_type_id = trim($_GET['trans_type_id']);
	    $model = Model();
		$condition = array();
        //$condition['trans_type_id'] = array('in',$trans_type_id);
        $condition['trans_type_id'] = $_GET['trans_type_id'];
        $result = $model->table('kuajing_trans_type')->where($condition)->find();
		Tpl::output('result',$result);
    	Tpl::showpage('kuajing_trans_type.edit');
}

	/**
     * 跨境运输方式  xinzeng
     */
   public function edit_saveOp() {
   		$model_hs = Model ( 'kuajing_trans_type' );
	    $lang	= Language::getLangContent();
	    $model = Model();
        $condition = array();
        $condition['trans_type_id'] = $_POST['trans_type_id'];
		$data['trans_type_name']       = $_POST['trans_type_name'];
		$data['code_guan']       = $_POST['code_guan'];
		$data['code_jian']       = $_POST['code_jian'];
		$result=$model->table('kuajing_trans_type')->where($condition)->update($data);
		if ($result){
			showMessage($lang['nc_common_save_succ'],'index.php?act=kuajing_trans_type&op=index');
		}else {
			showMessage($lang['nc_common_save_fail'],'index.php?act=kuajing_trans_type&op=index');
		}
	}

	
	
	/**
	 * 跨境运输方式  xinzeng
	 */
	public function delOp(){
		$lang	= Language::getLangContent();
		$trans_type_id = trim($_POST['trans_type_id']);
        $model= Model();
        $condition = array();
        $condition['trans_type_id'] = array('in',$trans_type_id);
        $result = $model->table('kuajing_trans_type')->where($condition)->delete();
        if($result) {
            $this->log(Language::get('hs_code_del').$_POST['trans_type_id'], 1);
            showMessage(Language::get('nc_common_del_succ'),'index.php?act=kuajing_trans_type&op=index');
        } else {
            $this->log(Language::get('hs_code_del').$_POST['trans_type_id'], 0);
            showMessage(Language::get('nc_common_del_fail'),'ndex.php?act=kuajing_trans_type&op=index');
        }


	}


	
}
