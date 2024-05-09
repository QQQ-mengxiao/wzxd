<?php
/**
 * 用户中心-限时折扣
 *
 *
 *
 ***/


defined('In718Shop') or exit('Access Invalid!');

class store_voucher_typeControl extends BaseSellerControl {


    public function __construct() {
        parent::__construct() ;
        //读取语言包
        Language::read('member_layout,promotion_xianshi');
        //读取语言包
        Language::read('member_layout,promotion_voucher');
        //检查限时折扣是否开启
        if (intval(C('promotion_allow')) !== 1){
            showMessage(Language::get('promotion_unavailable'),'index.php?act=store','','error');
        }
        //代金券模板状态
        $this->templatestate_arr = array('usable'=>array(1,Language::get('voucher_templatestate_usable')),'disabled'=>array(2,Language::get('voucher_templatestate_disabled')));
        Tpl::output('templatestate_arr',$this->templatestate_arr);

    }

    public function indexOp() {
        $this->vouchertype_listOp();
    }

    /**
     * 发布的限时折扣活动列表
     **/
    public function vouchertype_listOp() {
      
        $voucher_type = Model('voucher_type');

        if (checkPlatformStore()) {
            Tpl::output('isOwnShop', true);
        } else {
            //查询是否存在可用套餐
            $current_quota = $model->getCurrentQuota($_SESSION['store_id']);
            Tpl::output('current_quota',$current_quota);
        }

        //查询列表
        $param = array();
        $param['voucher_t_store_id'] = $_SESSION['store_id'];
        $param['voucher_t_state'] = 1;
         $param['voucher_t_type'] = array('neq',0);
         $voucher_model=Model('voucher');
        $list = $voucher_model->table('voucher_template')->where($param)->order('voucher_t_id desc')->page(10)->select();
        if(is_array($list)){
            foreach ($list as $key=>$val){
                $condition=array();
                $condition['voucher_tid']= $val['voucher_t_id'];
                $info=$voucher_type->getvouchertypeInfo($condition);
                 if($info['is_use']==1){
                    $list[$key]['is_usename'] = '包含';
                }else{
                    $list[$key]['is_usename']='不包含' ;
                }
                   $list[$key]['is_use']= $info['is_use'];
                if($val['voucher_t_type']==1){
                    $list[$key]['voucher_t_typename'] = '品类券';
                }else{
                    $list[$key]['voucher_t_typename'] ='单品券' ;
                }
            }
        }
          // var_dump($list);die;
        $this->profile_menu('vouchertype_list');
        Tpl::output('list',$list);
        Tpl::output('show_page',$voucher_model->showpage(2));
        Tpl::showpage('store_voucher_type.index') ;
    }

    
    /**
     * 限时折扣活动管理
     **/
    public function vouchertype2_manageOp() {
        $voucher_type = Model('voucher_type');
        $tid = intval($_GET['tid']);
        $condition=array();
        $condition['voucher_tid']= $tid ;
        $info=$voucher_type->getvouchertypeInfo($condition);
        Tpl::output('xianshi_info',$info);
        $goods_arr=explode(',', $info['goods_id']);
        // 商品详细信息
        $model_goods = Model('goods');
        $condition=array();
        if(!empty($_GET['goods_name'])) {
            $condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        }
        if(!empty($_GET['goods_serial'])) {
            $condition['goods_serial'] = $_GET['goods_serial'];
        }
        $condition['goods_id']=array(in,$goods_arr);
        $goods_arrinfo = $model_goods->getGoodsList($condition,'','','','',10);
        // var_dump($goods_arr);die;
        foreach ($goods_arrinfo as $key => $value) {
           $goods_arrinfo[$key]['goods_url']=urlShop('goods','index',array('goods_id'=>$value['goods_id']));
        }
        Tpl::output('xianshi_goods_list', $goods_arrinfo);
        //输出导航
        self::profile_menu('vouchertype2_manage');
        Tpl::output('show_page',$model_goods->showpage(2));
        Tpl::showpage('store_voucher_type2.manage');
    }
    /**
     * 限时折扣活动管理
     **/
    public function vouchertype1_manageOp() {
        $voucher_type = Model('voucher_type');
        $tid = intval($_GET['tid']);
        $condition=array();
        $condition['voucher_tid']= $tid ;
        $info=$voucher_type->getvouchertypeInfo($condition);
        Tpl::output('xianshi_info',$info);
        $goodsclass_arr=explode(',', $info['goodsclass_id']);
        $where=array();
        $where['gc_id']=array(in,$goodsclass_arr);
         if(!empty($_GET['goods_name'])) {
            $where['gc_name'] = array('like', '%'.$_GET['gc_name'].'%');
        }
        
        $store_goods_class = Model('goods_class')->getTreeClassList(3, array('gc_id' => array('gt', 0)));
        foreach ($store_goods_class as $k => $v) {
            if ($v['deep'] == 1) {
                $store_goods_class[$k]['gc_name'] = '' . $v['gc_name'];
            } elseif ($v['deep'] == 2) {
                $store_goods_class[$k]['gc_name'] = '&nbsp&nbsp' . $v['gc_name'];
            } elseif ($v['deep'] == 3) {
                $store_goods_class[$k]['gc_name'] = '&nbsp&nbsp&nbsp&nbsp' . $v['gc_name'];
            }
        }
        $model_goodsclass=Model('goods_class');
        $class_list=$model_goodsclass->getGoodsClassList2($where);
        Tpl::output('xianshi_goods_list', $class_list);
        Tpl::output('store_goods_class', $store_goods_class);
        Tpl::output('vouchertype_goods_list', $vouchertype_goods_list);
        //输出导航
        self::profile_menu('vouchertype1_manage');
        Tpl::output('show_page',$model_goodsclass->showpage(2));
        Tpl::showpage('store_voucher_type1.manage');
    }

    /**
     * 选择活动商品
     **/
    public function goods_selectOp() {
        $model_goods = Model('goods');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['is_deleted'] = 0;
        if($_GET['goods_name']) {
            $condition['goods_name'] = array('like', '%' . $_GET['goods_name'] . '%');
        }
        if($_GET['goods_serial']) {
            $condition['goods_serial'] = array('like', '%' . $_GET['goods_serial'] . '%');
        }
        //$condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        //$condition['goods_serial'] = $_GET['goods_serial'];
        $goods_list = $model_goods->getGoodsListForPromotion2($condition, '*', 10,);

        Tpl::output('tid', $_GET['tid']);
        Tpl::output('goods_list', $goods_list);
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::showpage('store_voucher_type2.goods', 'null_layout');
    }

    /**
     * 限时折扣商品添加
     **/
    public function vouchertype_goods_addOp() {  
        $goods_id = intval($_POST['goods_id']);
        $vouchertype_id = intval($_POST['xianshi_id']);
        $model_goods = Model('goods');
        $model_vouchertype = Model('voucher_type');
        $data = array();
        $data['result'] = true;
        $goods_info = $model_goods->getGoodsInfoByID($goods_id);
        if(empty($goods_info) || $goods_info['store_id'] != $_SESSION['store_id']) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
        $condition=array();
        $condition['voucher_tid']= $vouchertype_id ;
        $vouchertype_info=$model_vouchertype->getvouchertypeInfo($condition);
        if(!$vouchertype_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
        if(!empty($vouchertype_info['goods_id'])){ 
            $goods_arr=explode(',', $vouchertype_info['goods_id']);
            $updategoods_arr=array_push($goods_arr,$goods_id);  
            $updategoods_arr=array_unique($goods_arr);
            $update=array(); 
            $update['goods_id']=implode(',', $updategoods_arr);  
            // 商品详细信息
            $model_goods = Model('goods');
            $condition=array();
            $condition['goods_id']=$goods_id;
            $goods_arrinfo = $model_goods->getGoodsInfo($condition);
            $data['xianshi_goods'] = $goods_arrinfo;
        }else{
             // 商品详细信息
            $model_goods = Model('goods');
            $condition=array();
            $condition['goods_id']=$goods_id;
            $goods_arrinfo = $model_goods->getGoodsInfo($condition);
            $data['xianshi_goods'] = $goods_arrinfo;
            $update=array(); 
            $update['goods_id']= $goods_id;  
        }
        $vouchertype_goods_info = $model_vouchertype->editvouchertype($update,array('voucher_tid'=>$vouchertype_id));
        if($vouchertype_goods_info) {
            $data['result'] = true;
            $data['message'] = '添加成功';
            $data['xianshi_goods'] = $goods_arrinfo;
        } else {
            $data['result'] = false;
            $data['message'] = L('param_error');
        }
        echo json_encode($data);die;
    }

    
    /**
     * 限时折扣商品删除
     **/
    public function vouchertype_goods_deleteOp() {
        $model_vouchertype = Model('voucher_type');

        $data = array();
        $data['result'] = true;

        $vouchertype_goods_id = intval($_POST['vouchertype_goods_id']);
        $vouchertype_goods_info =Model('goods')->getGoodsInfo(array('goods_id'=>$vouchertype_goods_id));
        if(!$vouchertype_goods_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
        $condition=array();
        $condition['voucher_tid']= intval($_POST['xianshi_id']);
        // exit(json_encode($_POST['xianshi_id']));
        $vouchertype_info=$model_vouchertype->getvouchertypeInfo($condition);
        if(!$vouchertype_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
        $goods_arr=explode(',', $vouchertype_info['goods_id']);
        foreach ($goods_arr as $key=>$value)
        {
          if ($value == $vouchertype_goods_id)
            unset($goods_arr[$key]);
        }
        $update=array(); 
        $update['goods_id']=implode(',', $goods_arr);  
        $vouchertype_goods_info = $model_vouchertype->editvouchertype($update,array('voucher_tid'=>$_POST['xianshi_id']));
        echo json_encode($data);die;
    }
 /**
     * 代金券分类添加
     **/
    public function vouchertype_goodsclass_addOp() {  
        $stc_id = intval($_POST['stc_id']);
        $vouchertype_id = intval($_POST['xianshi_id']);
        
        $model_goods_class = Model('goods_class');
        $model_vouchertype = Model('voucher_type');
        $data = array();
        $data['result'] = true;
        $goodsclass_info = $model_goods_class->getGoodsClassInfoById($stc_id);
        if(empty($goodsclass_info) ) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
        $condition=array();
        $condition['voucher_tid']= $vouchertype_id ;
        $vouchertype_info=$model_vouchertype->getvouchertypeInfo($condition);
        if(!$vouchertype_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        } 
        $goodsclass_arr=explode(',', $vouchertype_info['goodsclass_id']);
         
        
        if($goodsclass_info['gc_parent_id']==0){
            $gc_parent_id = $goodsclass_info['gc_id'];
            $goods_class = $model_goods_class->getChildClassByFirstId_vouchertype($gc_parent_id);
            $addclass= array_keys($goods_class['class']);
        }else{
            $goods_class2 = $model_goods_class->getGoodsClassInfoById($goodsclass_info['gc_parent_id']);
            if($goods_class2['gc_parent_id']==0){
                 $goods_class = $model_goods_class->getChildClass($goodsclass_info['gc_id']);
                 foreach ($goods_class as $key => $value) {
                    $addclass[]=$value['gc_id'];
                 }
                    foreach ($addclass as $key=>$value)
                        {
                          if ($value == $goodsclass_info['gc_id'])
                            unset($addclass[$key]);
                        }
                       $addclass=array_values($addclass);

            }else{
                $addclass=array($goodsclass_info['gc_id']);
            }
        }
         if(!empty($vouchertype_info['goodsclass_id'])){
                $updategoods_arr=array_unique(array_merge($goodsclass_arr,$addclass));
                $update=array(); 
                $update['goodsclass_id']=implode(',', $updategoods_arr);
         }else{
                $update=array(); 
                $update['goodsclass_id']=implode(",", $addclass);
         }   
        $result = $model_vouchertype->editvouchertype($update,array('voucher_tid'=>$vouchertype_id));
        if($result) {
            $data['result'] = true;
            $data['message'] = '添加成功';
            $data['xianshi_goods'] = $goodsclass_info;
        } else {
            $data['result'] = false;
            $data['message'] = L('param_error');
        }
        echo json_encode($data);die;
    }

    
    /**
     * 代金券分类删除
     **/
    public function vouchertype_goodsclass_deleteOp() {
        $model_vouchertype = Model('voucher_type');
        $data = array();
        $data['result'] = true;
        $stc_id = intval($_POST['class_id']);
        $model_goods_class = Model('goods_class');
        $goodsclass_info = $model_goods_class->getGoodsClassInfoById($stc_id);
        if(!$goodsclass_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
        $condition=array();
        $condition['voucher_tid']= intval($_POST['xianshi_id']);
        $vouchertype_info=$model_vouchertype->getvouchertypeInfo($condition);
        if(!$vouchertype_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
        $goods_arr=explode(',', $vouchertype_info['goodsclass_id']);
        foreach ($goods_arr as $key=>$value)
        {
          if ($value == $stc_id)
            unset($goods_arr[$key]);
        }
        $update=array(); 
        $update['goodsclass_id']=implode(',', $goods_arr);  
        $vouchertype_goods_info = $model_vouchertype->editvouchertype($update,array('voucher_tid'=>$_POST['xianshi_id']));
        echo json_encode($data);die;
    }
    /**
     * 设置默认发货地址
     */
   public function use_setOp() {
       $t_id = intval($_GET['t_id']);
        $is_use = intval($_GET['is_use']);
        $model_vouchertype = Model('voucher_type');
       if ($t_id <=  0) return false;
       $condition = array();
       $condition['voucher_tid'] = $t_id;
        $vouchertype_info=$model_vouchertype->getvouchertypeInfo($condition);
     
       if (!chksubmit()) {
            Tpl::output('vouchertype_info', $vouchertype_info);
            Tpl::showpage('seller_vouchertype.use','null_layout');
            exit();
        } else {
            if( $is_use==1){
           $update = Model('voucher_type')->editvouchertype(array('is_use'=>0),$condition);
       }else{
            $update = Model('voucher_type')->editvouchertype(array('is_use'=>1),$condition);
       }
        }
        if (!$update) {
              showDialog('修改失败','','error',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
        } else {
            $this->recordSellerLog('修改代金券类型是否包含，id：' . $t_id . '修改之前为' . $is_use);
            showDialog('修改成功','reload','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
        }
   }
    /**
     * 用户中心右边，小导航
     *
     * @param string	$menu_type	导航类型
     * @param string 	$menu_key	当前导航的menu_key
     * @param array 	$array		附加菜单
     * @return
     */
    private function profile_menu($menu_key='') {
        $menu_array = array(
            1=>array('menu_key'=>'vouchertype_list','menu_name'=>'代金券类型管理','menu_url'=>'index.php?act=store_voucher_type&op=vouchertype_list'),
        );
        switch ($menu_key){
        	case 'vouchertype_add':
                $menu_array[] = array('menu_key'=>'vouchertype_add','menu_name'=>Language::get('promotion_join_active'),'menu_url'=>'index.php?act=store_promotion_vouchertype&op=vouchertype_add');
        		break;
        	case 'vouchertype_edit':
                $menu_array[] = array('menu_key'=>'vouchertype_edit','menu_name'=>'编辑活动','menu_url'=>'javascript:;');
        		break;
        	case 'vouchertype_quota_add':
                $menu_array[] = array('menu_key'=>'vouchertype_quota_add','menu_name'=>Language::get('promotion_buy_product'),'menu_url'=>'index.php?act=store_promotion_vouchertype&op=vouchertype_quota_add');
        		break;
        	case 'vouchertype1_manage':
                $menu_array[] = array('menu_key'=>'vouchertype1_manage','menu_name'=>'分类设置','menu_url'=>'index.php?act=store_voucher_type&op=voucher_type1_manage&vouchertype_id='.$_GET['vouchertype_id']);
        		break;
			case 'vouchertype2_manage':
                $menu_array[] = array('menu_key' => 'vouchertype2_manage', 'menu_name' => '商品管理', 'menu_url' => 'index.php?act=store_voucher_type&op=vouchertype2_manage&vouchertype_id=' . $_GET['vouchertype_id']);
                break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }
	


    public function vouchertype_goods_add_allOp(){
        $goods_ids = $_GET['goods_id'];
        $goods_id_arr = explode(',',$goods_ids);
        array_pop($goods_id_arr);
        $goods_list = Model('goods')->getGoodsList(array('goods_id'=>array('in',$goods_id_arr)),'goods_id,goods_name,goods_price,goods_image');
//        echo '<pre>';var_dump($goods_list);die;
        Tpl::output('vouchertype_id', $_GET['vouchertype_id']);
        Tpl::output('goods_list', $goods_list);
        Tpl::showpage('store_promotion_vouchertype.goods_add','null_layout');
    }


}
