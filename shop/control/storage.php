<?php
/**
 *  仓库管理
 */

defined('In718Shop') or exit('Access Invalid!');

class storageControl extends BaseSellerControl
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 仓库列表
     */
    public function storage_listOp()
    {
        $model_storage = Model('storage');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $storage_list = $model_storage->getStorageList($condition, '*', 'storage_id DESC', 2000);
        Tpl::output('storage_list', $storage_list);
        self::profile_menu('storage', 'storage');
        Tpl::showpage('storage_list');
    }

    /**
     * 新增/编辑仓库信息
     */
    public function storage_addOp()
    {
        $model_storage = Model('storage');
        if (chksubmit()) {
            //保存 新增/编辑 表单
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input" => $_POST["storage_name"], "require" => "true", "message" => '仓库名称不能为空！'),
                array("input" => $_POST["storage_code"], "require" => "true", "message" => '仓库编码不能为空！'),
                //array("input" => $_POST["storage_url"], "require" => "true", "message" => 'IP不能为空！'),
                //array("input" => $_POST["storage_username"], "require" => "true", "message" => '登陆账号不能为空！'),
                //array("input" => $_POST["storage_password"], "require" => "true", "message" => '登陆密码不能为空！'),
            );
            $error = $obj_validate->validate();
            if ($error != '') {
                showValidateError($error);
            }
            $storage = $model_storage->getStorageInfo(array('storage_code' => $_POST['storage_code'], 'storage_name' => $_POST["storage_name"]));
            if ($storage && !$_POST['storage_id']) {
                showDialog('仓库信息已存在！', '', 'error');
            }
            $data = array(
                'storage_name' => $_POST['storage_name'],
                'storage_code' => $_POST['storage_code'],
                'is_picked' => $_POST['is_picked'],
                'times' => $_POST['times'],
                'by_post'=>$_POST['by_post'],
                'store_id' => $_SESSION['store_id'],
                'storage_username' => $_POST['storage_username'],
                'storage_password' => $_POST['storage_password'],
                'last_synchro_time' => 0,
                'last_synchro_member_id' => 0,
                'addtime' => time(),
                'add_member_id' => $_SESSION['member_id'],
                'last_edit_time' => time(),
                'last_edit_member_id' => $_SESSION['member_id'],
                'storage_explain' => $_POST['storage_explain'],
                'storage_url' => $_POST['storage_url'],
            );
            $storage_id = intval($_POST['storage_id']);
            if ($storage_id > 0) {
                $condition = array();
                $condition['storage_id'] = $storage_id;
                $condition['store_id'] = $_SESSION['store_id'];
                $update = $model_storage->editStorage($data, $condition);
                if (!$update) {
                    showDialog('仓库信息修改失败！', '', 'error');
                }
            } else {
                $insert = $model_storage->addStorage($data);
                if (!$insert) {
                    showDialog('仓库信息新增失败！', '', 'error');
                }
            }
            showDialog('操作成功！', 'reload', 'succ');
        } elseif (is_numeric($_GET['storage_id']) > 0) {
            //编辑
            $condition = array();
            $condition['storage_id'] = intval($_GET['storage_id']);
            $condition['store_id'] = $_SESSION['store_id'];
            $storage_info = $model_storage->getStorageInfo($condition);
            if (empty($storage_info) && !is_array($storage_info)) {
                showMessage('参数不正确！', 'index.php?act=storage&op=storage_list', 'html', 'error');
            }
            Tpl::output('storage_info', $storage_info);
        }
        Tpl::showpage('storage_add', 'null_layout');
    }

    /**
     * 登陆获取仓库信息
     */
    public function warehose_loginOp()
    {
        $model_stock = Model('stock');
        $model_storage = Model('storage');
        $res = $model_stock->login($_POST['storage_username'], $_POST['storage_password'], $_POST['storage_url']);
        $storage_info = $model_storage->getStorageInfo(array('storage_code' => $res['WarehouseCode'], 'storage_name' => $res['WarehouseName']));
        if ($storage_info) {
            echo json_encode('');
            die;
        }
        echo json_encode($res);
        die;
    }

    /**
     * 删除仓库
     */
    public function storage_delOp()
    {
        $storage_id = intval($_GET['storage_id']);
        if ($storage_id <= 0) {
            showDialog('仓库删除失败！', '', 'error');
        }
        $condition = array();
        $condition['storage_id'] = $storage_id;
        $condition['store_id'] = $_SESSION['store_id'];
        $delete = Model('storage')->delStorage($condition);
        if ($delete) {
            showDialog('仓库删除成功！', 'index.php?act=storage&op=storage_list', 'succ');
        } else {
            showDialog('仓库删除失败！', '', 'error');
        }
    }

    /**
     * 仓库对应发货人详情页
     */
    public function storage_detailOp()
    {
        $storage_id = intval($_GET['storage_id']);
        if ($storage_id <= 0) {
            showDialog('参数错误！', '', 'error');
        }
        $model_daddress = Model('daddress');
        $model_storage = Model('storage');
        $daddress_list = $model_daddress->getAddressList(array('storage_id' => $storage_id, 'store_id' => $_SESSION['store_id']));
        if (is_array($daddress_list)) {
            foreach ($daddress_list as $k => $v) {
                $daddress_list[$k]['storage_name'] = $model_storage->getfby_storage_id($storage_id, 'storage_name');
            }
        }
        Tpl::output('address_list', $daddress_list);
        Tpl::showpage('storage_detail', 'null_layout');
    }

    /**
     * 库存同步-仓库管理
     */
    public function storage_synchroOp()
    {
        $storage_id = intval($_GET['storage_id']);
        if ($storage_id <= 0) {
            showDialog('参数错误！', '', 'error');
        }
        $model_stock = Model('stock');
        $model_storage = Model('storage');
        $res = $model_stock->tongbu_stockByid($storage_id);
        $model_storage->editStorage(array('last_synchro_time' => time(), 'last_synchro_member_id' => $_SESSION['member_id']), array('storage_id' => $storage_id));
        if($res == 1){
            showDialog('库存同步成功！', 'index.php?act=storage&op=storage_list', 'succ');
        }elseif($res == 2){
            showDialog('库存同步失败！', 'index.php?act=storage&op=storage_list', 'fail');
        }else{
            showDialog('库存同步失败！API异常', 'index.php?act=storage&op=storage_list', 'fail');
        }
    }

    /**
     * 库存同步-商品列表
     */
    public function storage_synchro_goodsOp()
    {
        $goods_commonid = $_GET['goods_commonid'];
        if(!$goods_commonid){
            showDialog('参数错误！', '', 'error');
        }
        $goods_commonid_array = explode(',',$goods_commonid);
        $model_stock = Model('stock');
        if(is_array($goods_commonid_array)){
            foreach($goods_commonid_array as $key=>$value){
                $result = $model_stock->tongbu_stockBygoods_commonid($value);
                if($result == 1){
                    $succ[] = $value;
                }else{
                    $fail[] = $value;
                }
            }
        }
        if(count($succ)>0){
            $succ_m = implode(',',$succ).'同步成功！';
        }else{
            $succ_m = '无记录同步成功！';
        }
        if(count($fail)>0){
            $fail_m = implode(',',$fail).'同步失败！';
        }else{
            $fail_m = '无记录同步失败！';
        }
        $message = $succ_m.$fail_m;
        showDialog($message, 'index.php?act=store_goods_online&op=index', 'notice');
    }

    /**
     * 仓库同步管理
     */
    public function storage_logOp()
    {
        $model_storage_log = Model('storage_log');
        $model_storage = Model('storage');
        $model_member = Model('member');
        $storage_log_list = $model_storage_log->getStorageLogList(array('store_id' => $_SESSION['store_id']), '', 'storage_log_id desc', '', 10);

        if (is_array($storage_log_list)) {
            foreach ($storage_log_list as $key => $value) {
                $storage_info = $model_storage->getStorageInfo(array('storage_id' => $value['storage_id']));
                $storage_log_list[$key]['storage_name'] = $storage_info['storage_name'];
                $storage_log_list[$key]['storage_code'] = $storage_info['storage_code'];
                $storage_log_list[$key]['member_name'] = $model_member->getfby_member_id($value['member_id'], 'member_name');
                if ($value['state'] == 0) {
                    $storage_log_list[$key]['state'] = '失败';
                } elseif ($value['state'] == 1) {
                    $storage_log_list[$key]['state'] = '成功';
                }
            }
        }
        Tpl::output('storage_log_list', $storage_log_list);
        self::profile_menu('storage', 'storage_log');
        Tpl::output('show_page', $model_storage_log->showpage(2));
        Tpl::showpage('storage_log');
    }

    /**
     * 库存同步详细记录
     */
    public function storage_log_detailOp(){
        $storage_log_id = $_GET['storage_log_id'];
        if (!$storage_log_id) {
            showDialog('参数错误！', '', 'error');
        }
        $model_storage_log = Model('storage_log');
        $model_member = Model('member');
        $condition['storage_log_id'] = $storage_log_id;
        $storage_log_info = $model_storage_log->getStorageLogInfoAll($condition);
        $storage_log_info['member_name'] = $model_member->getfby_member_id($storage_log_info['member_id'],'member_name');
        $storage_log_info['state'] =  $storage_log_info['state']==0?'失败':'成功';
        $storage_log_info['goods_serial_all'] =  !$storage_log_info['goods_serial_all']?'无':$storage_log_info['goods_serial_all'];
        $storage_log_info['goods_serial_succ'] =  !$storage_log_info['goods_serial_succ']?'无':$storage_log_info['goods_serial_succ'];
        $storage_log_info['goods_serial_fail'] =  !$storage_log_info['goods_serial_fail']?'无':$storage_log_info['goods_serial_fail'];
        Tpl::output('storage_log_info', $storage_log_info);
        self::profile_menu('storage');
        Tpl::showpage('storage_log_detail');
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_type, $menu_key = '')
    {
        switch ($menu_type) {
            case 'storage':
                $menu_array = array(
                    array('menu_key' => 'storage', 'menu_name' => '仓库管理', 'menu_url' => 'index.php?act=storage&op=storage_list'),
                    array('menu_key' => 'storage_log', 'menu_name' => '仓库同步管理', 'menu_url' => 'index.php?act=storage&op=storage_log'),
                );
                break;
        }
        Tpl::output('member_menu', $menu_array);
        Tpl::output('menu_key', $menu_key);
    }
}
