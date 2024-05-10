<?php
/**
 * 电商平台跨境参数信息
 *
 *
 *
 **/

defined('In718Shop') or exit('Access Invalid!');
class kuajing_trans_toolControl extends SystemControl{
	
	public function __construct(){
		parent::__construct();
		Language::read('setting');
	}


	public function indexOp(){
		$lang	= Language::getLangContent();
		$model = Model('kuajing_trans_tool');
		$condition	= array();
		
		
		if($_POST['like_sg_name']) {
        	$condition['tool_name'] = array('like','%'.$_POST['like_sg_name'].'%');
			
			$result=$model->where($condition)->select();
		}else{
			$result=$model->page('10')->select();
		}

		Tpl::output('like_sg_name',$_POST['like_sg_name']);
		Tpl::output('result',$result);
		Tpl::output('page',$model->showpage());
		Tpl::showpage('kuajing_trans_tool.index');
	}


	/**
     * 跨境平台参数增加
     */
    public function addOp() {

    	Tpl::showpage('kuajing_trans_tool.add');

}


	/**
     * 跨境平台参数增加  gai
     */
    public function add_saveOp() {
       $model_tool = Model ( 'kuajing_trans_tool' );
     	if (chksubmit()){
			//验证表单信息
			//$tool_name=$_POST['tool_name'];
				$data = array();
				$data['tool_name']       = $_POST['tool_name'];
				$data['code_guan']       = $_POST['code_guan'];
				$data['code_jian']       = $_POST['code_jian'];
				
				$model_tool->insert($data);
				$result=$model_tool->page('10')->select();
	            Tpl::output('result',$result);
				Tpl::output('page',$model_tool->showpage());
	            Tpl::showpage('kuajing_trans_tool.index');	    
           
       }
}
	/**
     * 跨境平台参数编辑 xinzeng
     */
    public function editOp() {
		//$tool_id = trim($_GET['tool_id']);
	    $model = Model();
		$condition = array();
        //$condition['tool_id'] = array('in',$tool_id);
        $condition['tool_id'] = $_GET['tool_id'];
        $result = $model->table('kuajing_trans_tool')->where($condition)->find();
		Tpl::output('result',$result);
    	Tpl::showpage('kuajing_trans_tool.edit');
}

	/**
     * 跨境平台参数编辑  xinzeng
     */
   public function edit_saveOp() {
   		$model_hs = Model ( 'kuajing_trans_tool' );
	    $lang	= Language::getLangContent();
	    $model = Model();
        $condition = array();
        $condition['tool_id'] = $_POST['tool_id'];
		$data['tool_name']       = $_POST['tool_name'];
		$data['code_guan']       = $_POST['code_guan'];
		$data['code_jian']       = $_POST['code_jian'];
		$result=$model->table('kuajing_trans_tool')->where($condition)->update($data);
		if ($result){
			showMessage($lang['nc_common_save_succ'],'index.php?act=kuajing_trans_tool&op=index');
		}else {
			showMessage($lang['nc_common_save_fail'],'index.php?act=kuajing_trans_tool&op=index');
		}
	}

	
	
	/**
	 * 跨境平台参数删除  xinzeng
	 */
	public function delOp(){
		$lang	= Language::getLangContent();
		$tool_id = trim($_POST['tool_id']);
        $model= Model();
        $condition = array();
        $condition['tool_id'] = array('in',$tool_id);
        $result = $model->table('kuajing_trans_tool')->where($condition)->delete();
        if($result) {
            $this->log(Language::get('hs_code_del').$_POST['tool_id'], 1);
            showMessage(Language::get('nc_common_del_succ'),'index.php?act=kuajing_trans_tool&op=index');
        } else {
            $this->log(Language::get('hs_code_del').$_POST['tool_id'], 0);
            showMessage(Language::get('nc_common_del_fail'),'ndex.php?act=kuajing_trans_tool&op=index');
        }


	}


	
}
