<?php
/**
 * 网站设置
 *
 *
 *
 ***/

class kuajing_hsControl extends SystemControl{
	
	public function __construct(){
		parent::__construct();
		Language::read('setting');
	}


	public function startOp(){
		$lang	= Language::getLangContent();
		$model = Model('ctax_hs');
		$condition	= array();
		if($_POST['like_sg_name']) {
        	$condition['hs'] = $_POST['like_sg_name'];
			
			$result=$model->where($condition)->select();
		}else{
			$result=$model->page('10')->select();
		}


		Tpl::output('like_sg_name',$_POST['like_sg_name']);
		Tpl::output('result',$result);
		Tpl::output('page',$model->showpage());
		Tpl::showpage('kuajing_hs.index');
	}
	/**
     * 检索
     */
	//public function findOp(){
		//$lang	= Language::getLangContent();
		//$model_hs = Model ( 'ctax_hs' );
		
		//$hs_code=trim($_POST['like_sg_name']);
		//$condition = array();
        //$condition['hs'] = array('in',$hs_code);
		//$condition['hs'] = array('like', '%' . $hs_code . '%');
		
        //$result = $model_hs->where($condition)->select();
		//Tpl::output('like_sg_name',$hs_code);
		//Tpl::output('result',$result);
		//Tpl::showpage('kuajing_hs.index');
		
	//}

	/**
     * 跨境HS参数增加
     */
    public function addOp() {

    	Tpl::showpage('kuajing_hs.add');

}


	/**
     * 跨境HS参数增加  gai
     */
    public function add_hsOp() {
       $model_hs = Model ( 'ctax_hs' );
     	if (chksubmit()){
			//验证表单信息
			$hs_code=$_POST['hs'];
			$condition = array();
            $condition['hs'] = array('in',$hs_code);
            $count = $model_hs->where($condition)->count();
            if ($count >= 1) {
			    showMessage('保存失败，此HS码已保存不能重复输入','index.php?act=kuajing_hs&op=start');
            }
			$obj_validate = new Validate();
			$obj_validate->validateparam = array(
				array("input"=>$_POST["hs"],"require"=>"true","message"=>'请输入hs码'),
				array("input"=>$_POST["xiaofie_rate"],"require"=>"true","validator"=>"Number","message"=>'请输入消费税'),
				array("input"=>$_POST["zengzhi_rate"],"require"=>"true","validator"=>"Number","message"=>'请输入增值税')
			);
			$error = $obj_validate->validate();
			$error = $obj_validate->validate();
			if ($error != ''){
				showMessage($error);
			}else {
			   if(isset($_POST['hs'])&&isset($_POST['xiaofei_rate'])&&isset($_POST['zengzhi_rate'])&&$_POST['hs']!=null&&$_POST['xiaofei_rate']!=null&&$_POST['zengzhi_rate']!=null) {
				$data = array();
				$data['id']         = $_POST['id'];
				$data['hs']         = $_POST['hs'];
				//$data['guan_rate']         = $_POST['guan_rate'];
				$data['xiaofei_rate']       = $_POST['xiaofei_rate'];
				$data['zengzhi_rate']       = $_POST['zengzhi_rate'];
				$data['tax_rate']           = $model_hs->getgoodsTax_Rate($data['xiaofei_rate'],$data['zengzhi_rate']);

				$model_hs->insert($data);
				$result=$model_hs->page('30')->select();
	            Tpl::output('result',$result);
				Tpl::output('page',$model_hs->showpage());
	            Tpl::showpage('kuajing_hs.index');

			    }
           }
       }
}
	/**
     * 跨境HS参数编辑 xinzeng
     */
    public function editOp() {
		$hs_id = trim($_GET['hs_id']);
	    $model = Model();
		$condition = array();
        $condition['id'] = array('in',$hs_id);
        $result = $model->table('ctax_hs')->where($condition)->find();
		Tpl::output('hs',$result);
    	Tpl::showpage('kuajing_hs.edit');
}

	/**
     * 跨境HS参数编辑  xinzeng
     */
   public function edit_hsOp() {
   		$model_hs = Model ( 'ctax_hs' );
	    $lang	= Language::getLangContent();
		$hs_id = $_POST['id'];
	    $model = Model();
        $condition = array();
        $condition['id'] = array('in',$hs_id);
		$data['hs']         = $_POST['hs'];
		//$data['guan_rate']         = $_POST['guan_rate'];
		$data['xiaofei_rate']       = $_POST['xiaofei_rate'];
		$data['zengzhi_rate']       = $_POST['zengzhi_rate'];
		$data['tax_rate']           = $model_hs->getgoodsTax_Rate($data['xiaofei_rate'],$data['zengzhi_rate']);
		$result=$model->table('ctax_hs')->where($condition)->update($data);
		if ($result){
			showMessage($lang['nc_common_save_succ'],'index.php?act=kuajing_hs&op=start');
		}else {
			showMessage($lang['nc_common_save_fail'],'index.php?act=kuajing_hs&op=start');
		}
	}

	
	
	/**
	 * 跨境HS参数删除  xinzeng
	 */
	public function del_hsOp(){
		$lang	= Language::getLangContent();
		$hs_id = trim($_POST['hs_id']);
        $model= Model();
        $condition = array();
        $condition['id'] = array('in',$hs_id);
        $result = $model->table('ctax_hs')->where($condition)->delete();
        if($result) {
            $this->log(Language::get('hs_code_del').$_POST['hs_id'], 1);
            showMessage(Language::get('nc_common_del_succ'),'index.php?act=kuajing_hs&op=start');
        } else {
            $this->log(Language::get('hs_code_del').$_POST['hs_id'], 0);
            showMessage(Language::get('nc_common_del_fail'),'ndex.php?act=kuajing_hs&op=start');
        }


	}



    /**
	 * ajax操作
	 */
	public function ajaxOp(){
		switch ($_GET['branch']){
			/**
			 * 店铺等级：验证是否有重复的名称
			 */
			case 'check_grade_name':
				if ($this->checkGradeName($_GET)){
					echo 'true'; exit;
				}else{
					echo 'false'; exit;
				}
				break;
			case 'check_grade_sort':
				if ($this->checkGradeSort($_GET)){
					echo 'true'; exit;
				}else{
					echo 'false'; exit;
				}
				break;
		}
	}
	/**
	 * 查询店铺等级名称是否存在
	 */
	private function checkGradeName($param){
		$model_grade = Model('store_grade');
		$condition['sg_name'] = $param['sg_name'];
		$condition['no_sg_id'] = $param['sg_id'];
		$list = $model_grade->getGradeList($condition);
		if (empty($list)){
			return true;
		}else {
			return false;
		}
	}

	
}
