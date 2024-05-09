<?php
/**
 * 分享图片背景管理
 *
 *
 *
 ***/


defined('In718Shop') or exit('Access Invalid!');
class store_sharepicControl extends BaseSellerControl {
	public function __construct() {
		parent::__construct();
		Language::read('member_store_brand');
	}

	public function indexOp(){
		$this->sharepic_listOp();
	}

	/**
	 * 分享图片背景列表
	 */
	public function sharepic_listOp() {
		$model_sharepic = Model('sharepic');
		$condition = array();
		$condition['store_id'] = $_SESSION['store_id'];
		if (!empty($_GET['sharepic_name'])) {
		    $condition['sharepic_name'] = array('like', '%' .$_GET['sharepic_name'] . '%');
		}

		$sharepic_list = $model_sharepic->getsharepicList($condition, '*', 10);

		Tpl::output('sharepic_list',$sharepic_list);
		Tpl::output('show_page',$model_sharepic->showpage());

		self::profile_menu('sharepic_list','sharepic_list');
		Tpl::showpage('store_sharepic.list');
	}

	/**
	 * 分享图片背景添加页面
	 */
	public function sharepic_addOp() {
		$lang	= Language::getLangContent();
		$model_sharepic = Model('sharepic');
		if($_GET['sharepic_id'] != '') {
			$sharepic_array = $model_sharepic->getsharepicInfo(array('sharepic_id' => $_GET['sharepic_id'], 'store_id' => $_SESSION['store_id']));
			// var_dump($sharepic_array);die;
			if (empty($sharepic_array)){
				showMessage($lang['wrong_argument'],'','html','error');
			}
			Tpl::output('sharepic_array',$sharepic_array);
		}

		// 一级商品分类
		$gc_list = Model('goods_class')->getGoodsClassListByParentId(0);
		Tpl::output('gc_list', $gc_list);

		Tpl::showpage('store_sharepic.add','null_layout');
	}

	/**
	 * 分享图片背景保存
	 */
	public function sharepic_saveOp() {
		$lang	= Language::getLangContent();
		$model_sharepic = Model('sharepic');
		if (chksubmit()) {
			

			/**
			 * 上传图片
			 */
			if (!empty($_FILES['sharepic_pic']['name'])){

				$upload = new UploadFile();
				$upload->set('default_dir', ATTACH_SHAREPIC);
				$upload->set('thumb_width', 150);
				$upload->set('thumb_height',80);
				// $upload->set('thumb_ext', '_small');
				// $upload->set('ifremove', true);
				$result = $upload->upfile('sharepic_pic');
				if ($result){
					$_POST['sharepic_pic'] = $upload->file_name;
				}else {
					showDialog($upload->error);
				}
			}
			// showDialog($_POST['sharepic_pic']);
			$insert_array = array();
			$insert_array['sharepic_name']      = trim($_POST['sharepic_name']);
			$insert_array['share_pic']       = $_POST['sharepic_pic'];
			$insert_array['store_id']        = $_SESSION['store_id'];

			$result = $model_sharepic->addsharepic($insert_array);
			if ($result){
				showDialog('添加成功','index.php?act=store_sharepic&op=sharepic_list','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
			}else {
				showDialog($lang['nc_common_save_fail']);
			}
		}
	}

	/**
	 * 分享图片背景修改
	 */
	public function sharepic_editOp() {
		$lang	= Language::getLangContent();
		$model_sharepic = Model('sharepic');
		if ($_POST['form_submit'] == 'ok' && intval($_POST['sharepic_id']) != 0) {
			/**
			 * 验证
			 */
			$obj_validate = new Validate();
			$obj_validate->validateparam = array(
                array("input"=>$_POST["sharepic_name"], "require"=>"true", "message"=>$lang['store_goods_sharepic_name_null']),
				
			);
			$error = $obj_validate->validate();
			if ($error != ''){
				showValidateError($error);
			}else {
				/**
				 * 上传图片
				 */
				if (!empty($_FILES['sharepic_pic']['name'])){
					$upload = new UploadFile();
					$upload->set('default_dir',ATTACH_SHAREPIC);
					$upload->set('thumb_width',	150);
					$upload->set('thumb_height',80);
					// $upload->set('thumb_ext',	'_small');
					// $upload->set('ifremove',	true);
					$result = $upload->upfile('sharepic_pic');

					if ($result){
						$_POST['sharepic_pic'] = $upload->file_name;
					}else {
						showDialog($upload->error);
					}
				}
                $where = array();
                $where['sharepic_id']       = intval($_POST['sharepic_id']);
                $update_array = array();

                $update_array['sharepic_name']     = trim($_POST['sharepic_name']);
                if (!empty($_POST['sharepic_pic'])){
                    $update_array['share_pic'] = $_POST['sharepic_pic'];
                }

                //查出原图片路径，后面会删除图片
                $sharepic_info = $model_sharepic->getsharepicInfo($where);
				$result = $model_sharepic->editsharepic($where, $update_array);
				if ($result){
					//删除老图片
					if (!empty($sharepic_info['sharepic_pic']) && $_POST['sharepic_pic']){
						@unlink(BASE_UPLOAD_PATH.DS.ATTACH_SHAREPIC.DS.$sharepic_info['sharepic_pic']);
					}
					showDialog($lang['nc_common_save_succ'],'index.php?act=store_sharepic&op=sharepic_list','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
				}else {
					showDialog($lang['nc_common_save_fail']);
				}
			}
		} else {
			showDialog($lang['nc_common_save_fail']);
		}
	}

	/**
	 * 分享图片背景删除
	 */
	public function drop_sharepicOp() {
		$model_sharepic	= Model('sharepic');
		$sharepic_id= intval($_GET['sharepic_id']);
		if ($sharepic_id > 0){
			$model_sharepic->delsharepic(array('sharepic_id'=>$sharepic_id, 'store_id' => $_SESSION['store_id']));
			showDialog(Language::get('nc_common_del_succ'),'index.php?act=store_sharepic&op=sharepic_list','succ');
		}else {
			showDialog(Language::get('nc_common_del_fail'));
		}
	}
/**
     * 设置默认发货地址
     */
   public function default_setOp() {
       $address_id = intval($_GET['address_id']);
       if ($address_id <=  0) return false;
       $condition = array();
       $condition['store_id'] = $_SESSION['store_id'];
       $update = Model('sharepic')->editsharepic($condition,array('sharepic_recommend'=>0));
       $condition['sharepic_id'] = $address_id;
       $update = Model('sharepic')->editsharepic($condition,array('sharepic_recommend'=>1));
   }
	/**
	 * 用户中心右边，小导航
	 *
	 * @param string	$menu_type	导航类型
	 * @param string 	$menu_key	当前导航的menu_key
	 * @param array 	$array		附加菜单
	 * @return
	 */
	private function profile_menu($menu_type,$menu_key='',$array=array()) {
		Language::read('member_layout');
		$lang	= Language::getLangContent();
		$menu_array		= array();
		switch ($menu_type) {
			case 'sharepic_list':
				$menu_array = array(
				    1=>array('menu_key'=>'sharepic_list', 'menu_name'=>'分享背景图', 'menu_url'=>'index.php?act=store_sharepic&op=sharepic_list')
				);
				break;
		}
		if(!empty($array)) {
			$menu_array[] = $array;
		}
		Tpl::output('member_menu',$menu_array);
		Tpl::output('menu_key',$menu_key);
	}
}
