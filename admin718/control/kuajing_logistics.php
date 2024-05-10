<?php
/**
 * 跨境物流参数信息
 *
 *
 *
 **/

defined('In718Shop') or exit('Access Invalid!');
class kuajing_logisticsControl extends SystemControl{
	
	public function __construct(){
		parent::__construct();
		Language::read('setting');
	}


	public function indexOp(){
		$lang	= Language::getLangContent();
		$model = Model('kuajing_logistics');
		$condition	= array();
		
		
		if($_POST['like_sg_name']) {
        	$condition['logistics_name'] = array('like','%'.$_POST['like_sg_name'].'%');
			
			$result=$model->where($condition)->select();
		}else{
			$result=$model->page('10')->select();
		}

		Tpl::output('like_sg_name',$_POST['like_sg_name']);
		Tpl::output('result',$result);
		Tpl::output('page',$model->showpage());
		Tpl::showpage('kuajing_logistics.index');
	}


	/**
     * 跨境物流参数增加
     */
    public function addOp() {

    	Tpl::showpage('kuajing_logistics.add');

}


	/**
     * 跨境物流参数增加  gai
     */
    public function add_saveOp() {
       $model_logistics = Model ( 'kuajing_logistics' );
     	if (chksubmit()){
			//验证表单信息
			//$logistics_name=$_POST['logistics_name'];
				$data = array();
				$data['logistics_name']       = $_POST['logistics_name'];
				$data['code_guan']       = $_POST['code_guan'];
				$data['code_jian']       = $_POST['code_jian'];
				
				$model_logistics->insert($data);
				$result=$model_logistics->page('10')->select();
	            Tpl::output('result',$result);
				Tpl::output('page',$model_logistics->showpage());
	            Tpl::showpage('kuajing_logistics.index');	    
           
       }
}
	/**
     * 跨境平台参数编辑 xinzeng
     */
    public function editOp() {
		//$logistics_id = trim($_GET['logistics_id']);
	    $model = Model();
		$condition = array();
        //$condition['logistics_id'] = array('in',$logistics_id);
        $condition['logistics_id'] = $_GET['logistics_id'];
        $result = $model->table('kuajing_logistics')->where($condition)->find();
		Tpl::output('result',$result);
    	Tpl::showpage('kuajing_logistics.edit');
}

	/**
     * 跨境平台参数编辑  xinzeng
     */
   public function edit_saveOp() {
   		$model_hs = Model ( 'kuajing_logistics' );
	    $lang	= Language::getLangContent();
	    $model = Model();
        $condition = array();
        $condition['logistics_id'] = $_POST['logistics_id'];
		$data['logistics_name']       = $_POST['logistics_name'];
		$data['code_guan']       = $_POST['code_guan'];
		$data['code_jian']       = $_POST['code_jian'];
		$result=$model->table('kuajing_logistics')->where($condition)->update($data);
		if ($result){
			showMessage($lang['nc_common_save_succ'],'index.php?act=kuajing_logistics&op=index');
		}else {
			showMessage($lang['nc_common_save_fail'],'index.php?act=kuajing_logistics&op=index');
		}
	}

	
	
	/**
	 * 跨境平台参数删除  xinzeng
	 */
	public function delOp(){
		$lang	= Language::getLangContent();
		$logistics_id = trim($_POST['logistics_id']);
        $model= Model();
        $condition = array();
        $condition['logistics_id'] = array('in',$logistics_id);
        $result = $model->table('kuajing_logistics')->where($condition)->delete();
        if($result) {
            $this->log(Language::get('hs_code_del').$_POST['logistics_id'], 1);
            showMessage(Language::get('nc_common_del_succ'),'index.php?act=kuajing_logistics&op=index');
        } else {
            $this->log(Language::get('hs_code_del').$_POST['logistics_id'], 0);
            showMessage(Language::get('nc_common_del_fail'),'ndex.php?act=kuajing_logistics&op=index');
        }


	}


	
}
