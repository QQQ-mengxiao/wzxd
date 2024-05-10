<?php
/**
 * 配送方式设置
 */



defined('In718Shop') or exit('Access Invalid!');

class pei_settingControl extends BaseSellerControl {
    // public function __construct() {
    //     parent::__construct();
    //     Language::read('member_store_index,deliver');
    // }

    /**
     * 配送方式列表
     */
    public function pei_listOp() {
       $condition = array();
       // $model_pei = Model('peisong');
       //$condition['store_id'] = $_SESSION['store_id'];
       $pei_list =Model()->table('peisong')->where($condition)->select();
       $model_daddress = Model('daddress');
       $condition1['address_id'] = array('gt',0);
       $deliever_list = $model_daddress->where($condition1)->select();
       //print_r( $deliever_list);
       $d=array();
       foreach ($deliever_list as $b => $v) {
            $deliver_id = $v['address_id'];
            $d[$deliver_id]= $v['seller_name'];
       }
       foreach ($pei_list as $key => $value) {
            $a=array();
            $pei_list[$key]['deliever_id'] = explode(",", $value['deliever_id']);
            foreach ($pei_list[$key]['deliever_id'] as $k => $v) {
                $a[]=$d[$v];
            }
            $pei_list[$key]['deliever_id']=implode(",",$a);   
        
           
       }
      // print_r($pei_list);die;
       Tpl::output('pei_list',$pei_list);
       // self::profile_menu('daddress','daddress');
       Tpl::showpage('peisong.peilist');
    }

    /**
     * 新增/编辑配送方式
     */
    public function pei_addOp() {
        //$model_daddress = Model('ziti_address');
        if (chksubmit()) {
            //保存 新增/编辑 表单
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["p_name"],"require"=>"true","message"=>"配货方式名称不能为空！"),
                array("input"=>$_POST["deliever_id"],"require"=>"true","message"=>"关联发货人不能为空！")
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showValidateError($error);
            }

            $data = array(
                'p_name' => $_POST['p_name'],
                'note' => $_POST['note'],
            );
           if(!empty($_POST['deliever_id'])){
                  $data['deliever_id'] =  implode(",",$_POST['deliever_id']);
                }else{
                    $data['deliever_id'] =  0;
                }
            $pei_id = intval($_POST['pei_id']);
            if ($pei_id > 0){
                $condition = array();
                $condition['id'] = $pei_id;
                $update = Model()->table('peisong')->where($condition)->update($data);
                if (!$update){
                    showDialog('编辑配货方式失败！','','error');
                }
            } else {
                $insert = Model()->table('peisong')->insert($data);
                if (!$insert){ 
                    // $data=implode(',', $data);
                    showDialog( $insert,'','error');
                }
            }
            showDialog('操作成功!','reload','succ','CUR_DIALOG.close()');
        } elseif (is_numeric($_GET['pei_id']) > 0) {
            //编辑
            $condition = array();
            $condition['id'] = intval($_GET['pei_id']);
            $pei_info = Model()->table('peisong')->field('*')->where($condition)->find();
             $pei_info['deliever_id'] = explode(",", $pei_info['deliever_id']);
            if (empty($pei_info) && !is_array($pei_info)){
                showMessage('参数不正确!','index.php?act=pei_setting&op=pei_list','html','error');
            }
            Tpl::output('pei_info',$pei_info);
        }
        $model_daddress = Model('daddress');
        $condition1['address_id'] = array('gt',0);
        $deliever_list = $model_daddress->where($condition1)->select();
        Tpl::output('daddress_list',$deliever_list);
        //print_r($deliever_list);die;
        Tpl::showpage('peisong.pei_add','null_layout');
    }

    /**
     * 删除配送方式
     */
    public function pei_delOp() {
        $pei_id = intval($_GET['pei_id']);
        if ($pei_id <=  0) {
            showDialog('删除配货方式失败','','error');
        }
        $condition['id'] =  $pei_id ;
        $delete =  Model()->table('peisong')->where($condition)->delete();
        if ($delete){
            showDialog('配货方式删除成功！','index.php?act=pei_setting&op=pei_list','succ');
        }else {
            showDialog('删除配货方式失败','','error');
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
