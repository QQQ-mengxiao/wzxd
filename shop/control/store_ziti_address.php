<?php
/**
 * 发货设置
 */



defined('In718Shop') or exit('Access Invalid!');

class store_ziti_addressControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct();
        Language::read('member_store_index,deliver');
    }

    /**
     * 发货地址列表
     */
    public function daddress_listOp() {
       Language::read('member_member_index');
       $model_daddress = Model('ziti_address');
       $condition = array();
       $condition['store_id'] = $_SESSION['store_id'];
       $address_list = $model_daddress->getAddressList($condition,'*','',20);
       $week=array(
        "7"=>"周日","1"=>"周一","2"=>"周二","3"=>"周三","4"=>"周四","5"=>"周五","6"=>"周六");
       foreach ($address_list as $key => $value) {
        if ($value['week']==0) {
            $address_list[$key]['week']='每天';
        } else {
               $a=array();
               $address_list[$key]['week'] = explode(",", $value['week']);
               foreach ($address_list[$key]['week'] as $k => $v) {
                   $a[]=$week[$v];
               }
                 $address_list[$key]['week']=implode(",",$a);
        }
        
           
       }
       // var_dump($address_list);die;
       Tpl::output('address_list',$address_list);
       self::profile_menu('daddress','daddress');
       Tpl::showpage('store_ziti_address.daddress_list');
    }

    /**
     * 新增/编辑发货地址
     */
    public function daddress_addOp() {
        Language::read('member_member_index');
        $lang   = Language::getLangContent();
        $model_daddress = Model('ziti_address');
        if (chksubmit()) {
            //保存 新增/编辑 表单
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["seller_name"],"require"=>"true","message"=>$lang['store_daddress_receiver_null']),
                array("input"=>$_POST["area_id"],"require"=>"true","validator"=>"Number","message"=>$lang['store_daddress_wrong_area']),
                array("input"=>$_POST["city_id"],"require"=>"true","validator"=>"Number","message"=>$lang['store_daddress_wrong_area']),
                array("input"=>$_POST["region"],"require"=>"true","message"=>$lang['store_daddress_area_null']),
                array("input"=>$_POST["address"],"require"=>"true","message"=>$lang['store_daddress_address_null'])
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showValidateError($error);
            }

            $data = array(
                'store_id' => $_SESSION['store_id'],
                'seller_name' => $_POST['seller_name'],
                'area_id' => $_POST['area_id'],
                'city_id' => $_POST['city_id'],
                'area_info' => $_POST['region'],
                'address' => $_POST['address'],
                'time' => $_POST['time'],
            );
           if(!empty($_POST['week'])){
                  $data['week'] =  implode(",",$_POST['week']);
                }else{
                    $data['week'] =  0;
                }
            $address_id = intval($_POST['address_id']);
            if ($address_id > 0){
                $condition = array();
                $condition['address_id'] = $address_id;
                $condition['store_id'] = $_SESSION['store_id'];
                $update = $model_daddress->editAddress($data,$condition);
                if (!$update){
                    showDialog($lang['store_daddress_modify_fail'],'','error');
                }
            } else {
                $insert = $model_daddress->addAddress($data);
                if (!$insert){ 
                    // $data=implode(',', $data);
                    showDialog( $insert,'','error');
                }
            }
            showDialog($lang['nc_common_op_succ'],'reload','succ','CUR_DIALOG.close()');
        } elseif (is_numeric($_GET['address_id']) > 0) {
            //编辑
            $condition = array();
            $condition['address_id'] = intval($_GET['address_id']);
            $condition['store_id'] = $_SESSION['store_id'];
            $address_info = $model_daddress->getAddressInfo($condition);
             $address_info['week'] = explode(",", $address_info['week']);
            if (empty($address_info) && !is_array($address_info)){
                showMessage($lang['store_daddress_wrong_argument'],'index.php?act=store_ziti_address&op=daddress_list','html','error');
            }
            Tpl::output('address_info',$address_info);
        }
        Tpl::showpage('store_ziti_address.daddress_add','null_layout');
    }

    /**
     * 删除发货地址
     */
    public function daddress_delOp() {
        $address_id = intval($_GET['address_id']);
        if ($address_id <=  0) {
            showDialog(Language::get('store_daddress_del_fail'),'','error');
        }
        $condition = array();
        $condition['address_id'] = $address_id;
        $condition['store_id'] = $_SESSION['store_id'];
        $delete = Model('ziti_address')->delAddress($condition);
        if ($delete){
            showDialog(Language::get('store_daddress_del_succ'),'index.php?act=store_ziti_address&op=daddress_list','succ');
        }else {
            showDialog(Language::get('store_daddress_del_fail'),'','error');
        }
    }

    /**
     * 设置默认发货地址
     */
   public function daddress_default_setOp() {
       $address_id = intval($_GET['address_id']);
       if ($address_id <=  0) return false;
       $condition = array();
       $condition['store_id'] = $_SESSION['store_id'];
       $update = Model('ziti_address')->editAddress(array('is_default'=>0),$condition);
       $condition['address_id'] = $address_id;
       $update = Model('ziti_address')->editAddress(array('is_default'=>1),$condition);
   }


    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_type, $menu_key = '') {
        Language::read('member_layout');
        switch ($menu_type) {
            case 'daddress':
                $menu_array = array(
                array('menu_key'=>'daddress',   'menu_name'=>Language::get('store_deliver_daddress_list'),  'menu_url'=>'index.php?act=store_ziti_address&op=daddress_list'),
                );
                break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }
}
