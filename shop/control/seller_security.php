<?php
/**
 * 修改密码
 **/

defined('In718Shop') or exit('Access Invalid!');

class seller_securityControl extends BaseSellerControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 修改密码
     */
    public function indexOp()
    {
        $seller_group_id = Model()->table('seller')->getfby_member_id($_SESSION['member_id'],"seller_group_id");
        Tpl::output('seller_group_id', $seller_group_id);
        Tpl::showpage('seller_security.index');
    }

    /**
     * 修改密码
     */
    public function modify_pwdOp()
    {
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
            array("input" => $_POST["password_old"], "require" => "true", "message" => '旧密码不能为空'),
            array("input" => $_POST["password_new"], "require" => "true", "message" => '新密码不能为空'),
            array("input" => $_POST["password_ensure"], "require" => "true", "validator" => "Compare", "operator" => "==", "to" => $_POST["password_new"], "message" => '两次密码输入不一致'),
        );
        $error = $obj_validate->validate();
        if ($error != '') {
            showValidateError($error);
        }

        $jiami = Logic('login');
        $passwd = $jiami->password_hash(trim($_POST['password_new']), PASSWORD_BCRYPT, array());

        $model_member = Model('member');
        $update = $model_member->editMember(array('member_id' => $_SESSION['member_id']), array('member_passwd' => $passwd));
        $message = $update ? '密码修改成功' : '密码修改失败';
        showDialog($message, 'index.php?act=seller_security&op=index', $update ? 'succ' : 'error');
    }

    /**
     * 确认密码
     */
    public function pwd_ensure_validateOp()
    {
        $password_new = $_POST['password_new'];
        $password_ensure = $_POST['password_ensure'];
        if ($password_new != $password_ensure) {
            die(json_encode(false));
        }
    }

    /**
     * 验证旧密码
     */
    public function pwd_old_validateOp()
    {
        $password_old = $_POST['password_old'];
        $model_member = Model('member');
        $jiami = Logic('login');
        $verify = $jiami->password_verify($password_old, $model_member->getfby_member_id($_SESSION['member_id'], 'member_passwd'));
        if (!$verify) {
            die(json_encode(false));
        }
    }
}
