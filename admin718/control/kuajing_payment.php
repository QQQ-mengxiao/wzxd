<?php
/**
 * 跨境支付企业参数信息
 *
 *
 *
 **/

defined('In718Shop') or exit('Access Invalid!');
class kuajing_paymentControl extends SystemControl{
	
	public function __construct(){
		parent::__construct();
		Language::read('setting');
	}


	public function indexOp(){
		$lang	= Language::getLangContent();
		$model = Model('kuajing_payment');
		$condition	= array();
		
		
		if($_POST['like_sg_name']) {
        	$condition['payment_name'] = array('like','%'.$_POST['like_sg_name'].'%');
			
			$result=$model->where($condition)->select();
		}else{
			$result=$model->page('10')->select();
		}

		Tpl::output('like_sg_name',$_POST['like_sg_name']);
		Tpl::output('result',$result);
		Tpl::output('page',$model->showpage());
		Tpl::showpage('kuajing_payment.index');
	}


	/**
     * 跨境支付企业参数增加
     */
    public function addOp() {

    	Tpl::showpage('kuajing_payment.add');

}


	/**
     * 跨境支付企业参数增加  gai
     */
    public function add_saveOp() {
       $model_payment = Model ( 'kuajing_payment' );
     	if (chksubmit()){
			//验证表单信息
			//$payment_name=$_POST['payment_name'];
				$data = array();
				$data['payment_name']       = $_POST['payment_name'];
				$data['code_guan']       = $_POST['code_guan'];
				$data['code_jian']       = $_POST['code_jian'];
				
				$model_payment->insert($data);
				$result=$model_payment->page('10')->select();
	            Tpl::output('result',$result);
				Tpl::output('page',$model_payment->showpage());
	            Tpl::showpage('kuajing_payment.index');	    
           
       }
}
	/**
     * 跨境支付企业参数编辑 xinzeng
     */
    public function editOp() {
		//$payment_id = trim($_GET['payment_id']);
	    $model = Model();
		$condition = array();
        //$condition['payment_id'] = array('in',$payment_id);
        $condition['payment_id'] = $_GET['payment_id'];
        $result = $model->table('kuajing_payment')->where($condition)->find();
		Tpl::output('result',$result);
    	Tpl::showpage('kuajing_payment.edit');
}

	/**
     * 跨境支付企业参数编辑  xinzeng
     */
   public function edit_saveOp() {
   		$model_hs = Model ( 'kuajing_payment' );
	    $lang	= Language::getLangContent();
	    $model = Model();
        $condition = array();
        $condition['payment_id'] = $_POST['payment_id'];
		$data['payment_name']       = $_POST['payment_name'];
		$data['code_guan']       = $_POST['code_guan'];
		$data['code_jian']       = $_POST['code_jian'];
		$result=$model->table('kuajing_payment')->where($condition)->update($data);
		if ($result){
			showMessage($lang['nc_common_save_succ'],'index.php?act=kuajing_payment&op=index');
		}else {
			showMessage($lang['nc_common_save_fail'],'index.php?act=kuajing_payment&op=index');
		}
	}

	
	
	/**
	 * 跨境支付企业参数删除  xinzeng
	 */
	public function delOp(){
		$lang	= Language::getLangContent();
		$payment_id = trim($_POST['payment_id']);
        $model= Model();
        $condition = array();
        $condition['payment_id'] = array('in',$payment_id);
        $result = $model->table('kuajing_payment')->where($condition)->delete();
        if($result) {
            $this->log(Language::get('hs_code_del').$_POST['payment_id'], 1);
            showMessage(Language::get('nc_common_del_succ'),'index.php?act=kuajing_payment&op=index');
        } else {
            $this->log(Language::get('hs_code_del').$_POST['payment_id'], 0);
            showMessage(Language::get('nc_common_del_fail'),'ndex.php?act=kuajing_payment&op=index');
        }


	}


	
}
