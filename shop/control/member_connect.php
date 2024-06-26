<?php
/**
 * 三方账户登录
 ** */


defined('In718Shop') or exit('Access Invalid!');
class member_connectControl extends BaseMemberControl {
	public function __construct() {
		parent::__construct();
		/**
		 * 读取语言包
		 */
		Language::read('member_member_qqconnect,member_member_sconnect');
	}
	/**
	 * QQ绑定
	 */
	public function qqbindOp(){
		//获得用户信息
		if (trim($this->member_info['member_qqinfo'])){
			$this->member_info['member_qqinfoarr'] = unserialize($this->member_info['member_qqinfo']);
		}
		Tpl::output('member_info',$this->member_info);
		//信息输出
		self::profile_menu('qq_bind');
		Tpl::showpage('member_qqbind');
	}
	/**
	 * QQ解绑
	 */
	public function qqunbindOp(){
		//修改密码
		$model_member	= Model('member');
		$update_arr = array();
		if ($_POST['is_editpw'] == 'yes'){
			/**
			 * 填写密码信息验证
			 */
			$obj_validate = new Validate();
			$obj_validate->validateparam = array(
				array("input"=>$_POST["new_password"],		"require"=>"true","validator"=>"Length","min"=>6,"max"=>20,"message"=>Language::get('member_qqconnect_password_null')),
				array("input"=>$_POST["confirm_password"],	"require"=>"true","validator"=>"Compare","operator"=>"==","to"=>$_POST["new_password"],"message"=>Language::get('member_qqconnect_input_two_password_again')),
			);
			$error = $obj_validate->validate();
			if ($error != ''){
				showMessage($error,'','html','error');
			}
			//$update_arr['member_passwd'] = md5(trim($_POST['new_password']));
			$jiami = Logic('login');
 			$update_arr['member_passwd'] = $jiami->password_hash(trim($_POST['new_password']),PASSWORD_BCRYPT,array());
		}
		$update_arr['member_qqopenid'] = '';
		$update_arr['member_qqinfo'] = '';
		$edit_state		= $model_member->editMember(array('member_id'=>$_SESSION['member_id']),$update_arr);

		if(!$edit_state) {
			showMessage(Language::get('member_qqconnect_password_modify_fail'),'html','error');
		}

		session_unset();
		session_destroy();
		showMessage(Language::get('member_qqconnect_unbind_success'),'index.php?act=login&ref_url='.urlencode('index.php?act=member_connect&op=qqbind'));
	}
	/**
	 * 新浪绑定
	 */
	public function sinabindOp(){
		//获得用户信息
		if (trim($this->member_info['member_sinainfo'])){
			$this->member_info['member_sinainfoarr'] = unserialize($this->member_info['member_sinainfo']);
		}
		Tpl::output('member_info',$this->member_info);
		//信息输出
		self::profile_menu('sina_bind');
		Tpl::showpage('member_sinabind');
	}
	/**
	 * 新浪解绑
	 */
	public function sinaunbindOp(){
		//修改密码
		$model_member	= Model('member');
		$update_arr = array();
		if ($_POST['is_editpw'] == 'yes'){
			/**
			 * 填写密码信息验证
			 */
			$obj_validate = new Validate();
			$obj_validate->validateparam = array(
				array("input"=>$_POST["new_password"],		"require"=>"true","validator"=>"Length","min"=>6,"max"=>20,"message"=>Language::get('member_sconnect_password_null')),
				array("input"=>$_POST["confirm_password"],	"require"=>"true","validator"=>"Compare","operator"=>"==","to"=>$_POST["new_password"],"message"=>Language::get('member_sconnect_input_two_password_again')),
			);
			$error = $obj_validate->validate();
			if ($error != ''){
				showMessage($error,'','html','error');
			}
			//$update_arr['member_passwd'] = md5(trim($_POST['new_password']));
			$jiami = Logic('login');
 			$update_arr['member_passwd'] = $jiami->password_hash(trim($_POST['new_password']),PASSWORD_BCRYPT,array());
		}
		$update_arr['member_sinaopenid'] = '';
		$update_arr['member_sinainfo'] = '';
		$edit_state		= $model_member->editMember(array('member_id'=>$_SESSION['member_id']),$update_arr);

		if(!$edit_state) {
			showMessage(Language::get('member_sconnect_password_modify_fail'),'','html','error');
		}
		session_unset();
		session_destroy();
		showMessage(Language::get('member_sconnect_unbind_success'),'index.php?act=login&ref_url='.urlencode('index.php?act=member_connect&op=sinabind'));
    }
    /**
     * 微信绑定
     */
    public function weixinbindOp(){
        //获得用户信息
        if (trim($this->member_info['weixin_info'])){
            $this->member_info['weixin_infoarr'] = unserialize($this->member_info['weixin_info']);
        }
        Tpl::output('member_info',$this->member_info);
        //信息输出
        self::profile_menu('weixin_bind');
        Tpl::showpage('member_weixinbind');
    }
    /**
     * 微信解绑
     */
    public function weixinunbindOp(){
        //修改密码
        $model_member = Model('member');
        $update_arr = array();
        if ($_POST['is_editpw'] == 'yes'){
            /**
             * 填写密码信息验证
             */
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["new_password"],      "require"=>"true","validator"=>"Length","min"=>6,"max"=>20,"message"=>Language::get('member_sconnect_password_null')),
                array("input"=>$_POST["confirm_password"],  "require"=>"true","validator"=>"Compare","operator"=>"==","to"=>$_POST["new_password"],"message"=>Language::get('member_sconnect_input_two_password_again')),
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error,'','html','error');
            }
            //$update_arr['member_passwd'] = md5(trim($_POST['new_password']));
            $jiami = Logic('login');
 			$update_arr['member_passwd'] = $jiami->password_hash(trim($_POST['new_password']),PASSWORD_BCRYPT,array());
        }
        $update_arr['weixin_unionid'] = '';
        $update_arr['weixin_info'] = '';
        $edit_state = $model_member->editMember(array('member_id'=>$_SESSION['member_id']),$update_arr);

        if(!$edit_state) {
            showMessage('保存失败','','html','error');
        }
        session_unset();
        session_destroy();
        showMessage('微信解绑成功',urlLogin('login', 'index', array('ref_url' => urlMember('member_connect', 'weixinbind'))));
    }
	/**
	 * 用户中心右边，小导航
	 *
	 * @param string	$menu_type	导航类型
	 * @param string 	$menu_key	当前导航的menu_key
	 * @param array 	$array		附加菜单
	 * @return
	 */
	private function profile_menu($menu_key='',$array=array()) {
		Language::read('member_layout');
		$lang	= Language::getLangContent();
		$menu_array		= array();
		$menu_array = array(
			1=>array('menu_key'=>'qq_bind',	'menu_name'=>$lang['nc_member_path_qq_bind'],	'menu_url'=>'index.php?act=member_connect&op=qqbind'),
			2=>array('menu_key'=>'sina_bind',	'menu_name'=>$lang['nc_member_path_sina_bind'],	'menu_url'=>'index.php?act=member_connect&op=sinabind'),
			3=>array('menu_key'=>'weixin_bind',   'menu_name'=>'微信绑定', 'menu_url'=>'index.php?act=member_connect&op=weixinbind'),
		);
		if(!empty($array)) {
			$menu_array[] = $array;
		}
		Tpl::output('member_menu',$menu_array);
		Tpl::output('menu_key',$menu_key);
	}
}
