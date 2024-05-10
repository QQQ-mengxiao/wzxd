<?php
/**
 * 用户中心-限时折扣
 *
 *
 *
 **by 好商城V3 www.shopnc.net 运营版*/


defined('In718Shop') or exit('Access Invalid!');
class store_buy_deliverControl extends BaseSellerControl {

    const LINK_BUYDELIVER_LIST = 'index.php?act=store_buy_deliver&op=buy_deliver_list';
    const LINK_BUYDELIVER_MANAGE = 'index.php?act=store_buy_deliver&op=buy_deliver_manage&buy_deliver_id=';

    public function __construct() {
        parent::__construct() ;

        //读取语言包
        Language::read('member_layout,promotion_xianshi');
        //检查限时折扣是否开启
        if (intval(C('promotion_allow')) !== 1){
            showMessage(Language::get('promotion_unavailable'),'index.php?act=store','','error');
        }

    }

    public function indexOp() {
        $this->buy_deliver_listOp();
    }

    /**
     * 发布的即买即送活动列表
     **/
    public function buy_deliver_listOp() {
        //$model_xianshi_quota = Model('p_xianshi_quota');
        $model_buy_deliver = Model('buy_deliver');

        if (checkPlatformStore()) {
            Tpl::output('isOwnShop', true);
        }
        // else {
        //     $current_xianshi_quota = $model_xianshi_quota->getXianshiQuotaCurrent($_SESSION['store_id']);
        //     Tpl::output('current_xianshi_quota', $current_xianshi_quota);
        // }

        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        if(!empty($_GET['buy_deliver_name'])) {
            $condition['buy_deliver_name'] = array('like', '%'.$_GET['buy_deliver_name'].'%');
        }
        if(!empty($_GET['state'])) {
            $condition['state'] = intval($_GET['state']);
        }
        $buy_deliver_list = $model_buy_deliver->getBuyDeliverList($condition, 10, 'state desc');
        Tpl::output('buy_deliver_list', $buy_deliver_list);
        Tpl::output('show_page', $model_buy_deliver->showpage());
        Tpl::output('buy_deliver_state_array', $model_buy_deliver->getBuyDeliverStateArray());

        self::profile_menu('buy_delive_list');
        Tpl::showpage('store_promotion_buy_deliver.list');
    }

    /**
     * 添加即买即送活动
     **/
    public function buy_deliver_addOp() {
        $model_ziti = Model('ziti_address');
        if (checkPlatformStore()) {
            Tpl::output('isOwnShop', true);
        }
        $condition = array();
        $ziti_list = $model_ziti->getAddressList($condition);
        Tpl::output('ziti_list',$ziti_list);
        // else {
        //     $model_xianshi_quota = Model('p_xianshi_quota');
        //     $current_xianshi_quota = $model_xianshi_quota->getXianshiQuotaCurrent($_SESSION['store_id']);
        //     if(empty($current_xianshi_quota)) {
        //         showMessage(Language::get('xianshi_quota_current_error1'),'','','error');
        //     }
        //     Tpl::output('current_xianshi_quota',$current_xianshi_quota);
        // }

        //输出导航
        self::profile_menu('buy_deliver_add');
        Tpl::showpage('store_promotion_buy_deliver.add');

    }

    /**
     * 保存添加的即买即送活动
     **/
    public function buy_deliver_saveOp() {
        //验证输入
        $buy_deliver_name = trim($_POST['buy_deliver_name']);
        // $start_time = strtotime($_POST['start_time']);
        // $end_time = strtotime($_POST['end_time']);
        // $lower_limit = intval($_POST['lower_limit']);
        // if($lower_limit <= 0) {
        //     $lower_limit = 1;
        // }
        if(empty($buy_deliver_name)) {
            showDialog(Language::get('xianshi_name_error'));
        }

        $model_ziti = Model('ziti_address');
        $ziti_id = $_POST['ziti_id'];
        $condition_ziti = array('address_id' => $ziti_id);
        $ziti_info = $model_ziti->getAddressInfo($condition_ziti);
        $ziti_name = $ziti_info['seller_name'];
        $city_id = $ziti_info['city_id'];
        $area_id = $ziti_info['area_id'];
        $area_info = $ziti_info['area_info'];
        $ziti_address = $ziti_info['address'];
        // if($start_time >= $end_time) {
        //     showDialog(Language::get('greater_than_start_time'));
        // }

        // if (!checkPlatformStore()) {
        //     //获取当前套餐
        //     $model_xianshi_quota = Model('p_xianshi_quota');
        //     $current_xianshi_quota = $model_xianshi_quota->getXianshiQuotaCurrent($_SESSION['store_id']);
        //     if(empty($current_xianshi_quota)) {
        //         showDialog('没有可用限时折扣套餐,请先购买套餐');
        //     }
        //     $quota_start_time = intval($current_xianshi_quota['start_time']);
        //     $quota_end_time = intval($current_xianshi_quota['end_time']);
        //     if($start_time < $quota_start_time) {
        //         showDialog(sprintf(Language::get('xianshi_add_start_time_explain'),date('Y-m-d',$current_xianshi_quota['start_time'])));
        //     }
        //     if($end_time > $quota_end_time) {
        //         showDialog(sprintf(Language::get('xianshi_add_end_time_explain'),date('Y-m-d',$current_xianshi_quota['end_time'])));
        //     }
        // }
        //生成活动
        $model_buy_deliver = Model('buy_deliver');
        $param = array();
        $param['buy_deliver_name'] = $buy_deliver_name;
        $param['buy_deliver_title'] = $_POST['buy_deliver_title'];
        $param['buy_deliver_explain'] = $_POST['buy_deliver_explain'];
        $param['ziti_id'] = $ziti_id;
        $param['ziti_name'] = $ziti_name;
        $param['city_id'] = $city_id;
        $param['area_id'] = $area_id;
        $param['area_info'] = $area_info;
        $param['ziti_address'] = $ziti_address;
        //$param['buy_deliver_erea'] = $_POST['buy_deliver_erea'];
        // $param['quota_id'] = $current_xianshi_quota['quota_id'] ? $current_xianshi_quota['quota_id'] : 0;
        // $param['start_time'] = $start_time;
        // $param['end_time'] = $end_time;
        $param['store_id'] = $_SESSION['store_id'];
        $param['store_name'] = $_SESSION['store_name'];
        $param['member_id'] = $_SESSION['member_id'];
        $param['member_name'] = $_SESSION['member_name'];
        // $param['lower_limit'] = $lower_limit;
        $result = $model_buy_deliver->addBuyDeliver($param);
        if($result) {
            $this->recordSellerLog('添加即买即送活动，活动名称：'.$buy_deliver_name.'，活动编号：'.$result);
            // 添加计划任务
            $this->addcron(array('exetime' => $param['end_time'], 'exeid' => $result, 'type' => 7), true);
            showDialog("即买即送添加成功",self::LINK_BUYDELIVER_MANAGE.$result,'succ','',3);
        }else {
            showDialog("即买即送添加失败");
        }
    }

    /**
     * 编辑即买即送活动
     **/
    public function buy_deliver_editOp() {
        $model_buy_deliver = Model('buy_deliver');
        $model_ziti = Model('ziti_address');
        $condition = array();
        $ziti_list = $model_ziti->getAddressList($condition);
        Tpl::output('ziti_list',$ziti_list);

        $buy_deliver_info = $model_buy_deliver->getBuyDeliverInfoByID($_GET['buy_deliver_id']);
        if(empty($buy_deliver_info) || !$buy_deliver_info['editable']) {
            showMessage(L('param_error'),'','','error');
        }

        Tpl::output('buy_deliver_info', $buy_deliver_info);
        //输出导航
        self::profile_menu('buy_deliver_edit');
        Tpl::showpage('store_promotion_buy_deliver.add');
    }

    /**
     * 编辑保存即买即送活动
     **/
    public function buy_deliver_edit_saveOp() {
        
        $buy_deliver_id = $_POST['buy_deliver_id'];

        $model_buy_deliver = Model('buy_deliver');
        $model_buy_deliver_goods = Model('buy_deliver_goods');
        $buy_deliver_info = $model_buy_deliver->getBuyDeliverInfoByID($buy_deliver_id, $_SESSION['store_id']);
        if(empty($buy_deliver_info) || !$buy_deliver_info['editable']) {
            showMessage(L('param_error'),'','','error');
        }

        $model_ziti = Model('ziti_address');
        $ziti_id = $_POST['ziti_id'];
        $condition_ziti = array('address_id' => $ziti_id);
        $ziti_info = $model_ziti->getAddressInfo($condition_ziti);
        $ziti_name = $ziti_info['seller_name'];
        $city_id = $ziti_info['city_id'];
        $area_id = $ziti_info['area_id'];
        $area_info = $ziti_info['area_info'];
        $ziti_address = $ziti_info['address'];

        //验证输入
        $buy_deliver_name = trim($_POST['buy_deliver_name']);
        if(empty($buy_deliver_name)) {
            showDialog(Language::get('xianshi_name_error'));
        }
        //生成活动
        $param = array();
        $param['buy_deliver_name'] = $buy_deliver_name;
        $param['buy_deliver_title'] = $_POST['buy_deliver_title'];
        $param['buy_deliver_explain'] = $_POST['buy_deliver_explain'];
        
        $param['ziti_id'] = $ziti_id;
        $param['ziti_name'] = $ziti_name;
        $param['city_id'] = $city_id;
        $param['area_id'] = $area_id;
        $param['area_info'] = $area_info;
        $param['ziti_address'] = $ziti_address;

        $result = $model_buy_deliver->editBuyDeliver($param, array('buy_deliver_id'=>$buy_deliver_id));
        $update_buy_deliver_goods = array();
        $update_buy_deliver_goods['buy_deliver_name'] = $buy_deliver_name;
        $update_buy_deliver_goods['buy_deliver_title'] = $_POST['buy_deliver_title'];
        $update_buy_deliver_goods['buy_deliver_explain'] = $_POST['buy_deliver_explain'];
        //$result1 = $model_buy_deliver_goods->editBuyDeliverGoods($update_buy_deliver_goods, array('buy_deliver_id'=>$buy_deliver_id));
        if($result) {
            $this->recordSellerLog('编辑即买即送活动，活动名称：'.$xianshi_name.'，活动编号：'.$buy_deliver_id);
            showDialog(Language::get('nc_common_op_succ'),self::LINK_BUYDELIVER_LIST,'succ','',3);
        }else {
            showDialog(Language::get('nc_common_op_fail'));
        }
    }

    /**
     * 即买即送活动删除
     **/
    public function buy_deliver_delOp() {
        $buy_deliver_id = intval($_POST['buy_deliver_id']);

        $model_buy_deliver = Model('buy_deliver');

        $data = array();
        $data['result'] = true;

        $buy_deliver_info = $model_buy_deliver->getBuyDeliverInfoByID($buy_deliver_id, $_SESSION['store_id']);
        if(!$buy_deliver_info) {
            showDialog(L('param_error'));
        }

        $result = $model_buy_deliver->delBuyDeliver(array('buy_deliver_id'=>$buy_deliver_id));

        if($result) {
            $this->recordSellerLog('删除即买即送活动，活动名称：'.$buy_deliver_info['buy_deliver_name'].'活动编号：'.$buy_deliver_id);
            showDialog(L('nc_common_op_succ'), urlShop('store_buy_deliver', 'buy_deliver_list'), 'succ');
        } else {
            showDialog(L('nc_common_op_fail'));
        }
    }


    /**
     * 即买即送活动管理
     **/
    public function buy_deliver_manageOp() {
        $model_buy_deliver = Model('buy_deliver');
        $model_buy_deliver_goods = Model('buy_deliver_goods');

        $buy_deliver_id = intval($_GET['buy_deliver_id']);
        $buy_deliver_info = $model_buy_deliver->getBuyDeliverInfoByID($buy_deliver_id, $_SESSION['store_id']);
        if(empty($buy_deliver_info)) {
            showDialog(L('param_error'));
        }
        Tpl::output('buy_deliver_info',$buy_deliver_info);
        //获取即买即送商品列表
        $condition = array();
        if(!empty($_GET['goods_name'])) {
            $condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        }        
        $condition['buy_deliver_id'] = $buy_deliver_id;
        //$buy_deliver_goods_list = $model_buy_deliver_goods->getBuyDeliverGoodsExtendList($condition,5);
        $buy_deliver_goods_list = $model_buy_deliver_goods->getBuyDeliverGoodsExtendListShop($condition,5);
        Tpl::output('buy_deliver_goods_list', $buy_deliver_goods_list);
        //输出导航
        self::profile_menu('buy_deliver_manage');
        Tpl::output('show_page',$model_buy_deliver->showpage(2));
        Tpl::showpage('store_promotion_buy_deliver.manage');
    }

    /**
     * 选择活动商品
     **/
    public function goods_selectOp() {
        $model_goods = Model('goods');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        $goods_list = $model_goods->getGoodsListForPromotion($condition, '*', 10, 'buy_deliver');
        Tpl::output('goods_list', $goods_list);
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::showpage('store_promotion_buy_deliver.goods', 'null_layout');
    }

    /**
     * 即买即送商品添加
     **/
    public function buy_deliver_goods_addOp() {
        $goods_id = intval($_POST['goods_id']);
        $buy_deliver_id = intval($_POST['buy_deliver_id']);
        //$xianshi_price = floatval($_POST['xianshi_price']);
		//$xianshi_app_price = floatval($_POST['xianshi_app_price']);

        $model_goods = Model('goods');
        $model_buy_deliver = Model('buy_deliver');
        $model_buy_deliver_goods = Model('buy_deliver_goods');

        $data = array();
        $data['result'] = true;


        $goods_info = $model_goods->getGoodsInfoByID($goods_id);
        if(empty($goods_info) || $goods_info['store_id'] != $_SESSION['store_id']) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
        //基于id，更改goods_type
        

        $buy_deliver_info = $model_buy_deliver->getBuyDeliverInfoByID($buy_deliver_id, $_SESSION['store_id']);
        if(!$buy_deliver_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }

        //检查商品是否已经参加同时段活动
        // $condition = array();
        // $condition['end_time'] = array('gt', $xianshi_info['start_time']);
        // $condition['goods_id'] = $goods_id;
        // $xianshi_goods = $model_xianshi_goods->getXianshiGoodsExtendList($condition);
        // if(!empty($xianshi_goods)) {
        //     $data['result'] = false;
        //     $data['message'] = '该商品已经参加了同时段活动';
        //     echo json_encode($data);die;
        // }

        //添加到活动商品表
        $param = array();
        $param['buy_deliver_id'] = $buy_deliver_info['buy_deliver_id'];
        $param['buy_deliver_name'] = $buy_deliver_info['buy_deliver_name'];
        $param['buy_deliver_title'] = $buy_deliver_info['buy_deliver_title'];
        $param['buy_deliver_explain'] = $buy_deliver_info['buy_deliver_explain'];
        $param['goods_id'] = $goods_info['goods_id'];
        $param['store_id'] = $goods_info['store_id'];
        $param['goods_name'] = $goods_info['goods_name'];
        $param['goods_price'] = $goods_info['goods_price'];
		//市场价
		//$param['goods_marketprice'] = $goods_info['goods_marketprice'];
  //       $param['xianshi_price'] = $xianshi_price;
		// $param['xianshi_app_price'] = $xianshi_app_price;
        $param['goods_image'] = $goods_info['goods_image'];
        // $param['start_time'] = $xianshi_info['start_time'];
        // $param['end_time'] = $xianshi_info['end_time'];
        // $param['lower_limit'] = $xianshi_info['lower_limit'];

        $result = array();
        $buy_deliver_goods_info = $model_buy_deliver_goods->addBuyDeliverGoods($param);
        if($buy_deliver_goods_info) {
            $update = array('is_group_ladder' => 5);
            $goods_condition = array('goods_id' => $goods_id);
            $model_goods->editGoods($update,$goods_condition);
            $result['result'] = true;
            $data['message'] = '添加成功';
            $data['buy_deliver_goods'] = $buy_deliver_goods_info;

            // 自动发布动态
            // goods_id,store_id,goods_name,goods_image,goods_price,goods_freight,xianshi_price
   //          $data_array = array();
   //          $data_array['goods_id']         = $goods_info['goods_id'];
   //          $data_array['store_id']         = $_SESSION['store_id'];
   //          $data_array['goods_name']       = $goods_info['goods_name'];
   //          $data_array['goods_image']      = $goods_info['goods_image'];
   //          $data_array['goods_price']      = $goods_info['goods_price'];
			// //市场价
			// //$data_array['goods_marketprice'] = $goods_info['goods_marketprice'];
   //          $data_array['goods_freight']    = $goods_info['goods_freight'];
   //          $data_array['xianshi_price']    = $xianshi_price;
			// $data_array['xianshi_app_price']    = $xianshi_app_price;
   //          $this->storeAutoShare($data_array, 'xianshi');
   //          $this->recordSellerLog('添加即买即送商品，活动名称：'.$buy_deliver_info['xianshi_name'].'，商品名称：'.$goods_info['goods_name']);

            // 添加任务计划
            // $this->addcron(array('type' => 2, 'exeid' => $goods_info['goods_id'], 'exetime' => $param['start_time']));
        } else {
            $data['result'] = false;
            $data['message'] = L('param_error');
        }
        echo json_encode($data);die;
    }

    /**
     * 即买即送商品删除
     **/
    public function buy_deliver_goods_deleteOp() {
        $model_buy_deliver_goods = Model('buy_deliver_goods');
        $model_buy_deliver = Model('buy_deliver');
        $model_goods = Model('goods');

        $data = array();
        $data['result'] = true;

        $buy_deliver_goods_id = intval($_POST['buy_deliver_goods_id']);
        //echo json_encode($data);die;
        $buy_deliver_goods_info = $model_buy_deliver_goods->getBuyDeliverGoodsInfoByID($buy_deliver_goods_id);
        if(!$buy_deliver_goods_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
        $buy_deliver_info = $model_buy_deliver->getBuyDeliverInfoByID($buy_deliver_goods_info['buy_deliver_id'], $_SESSION['store_id']);
        if(!$buy_deliver_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }

        if(!$model_buy_deliver_goods->delBuyDeliverGoods(array('buy_deliver_goods_id'=>$buy_deliver_goods_id))) {
            $data['result'] = false;
            $data['message'] = "即买即送商品删除失败";
            echo json_encode($data);die;
        }
        //更改商品状态
        $goods_id = $buy_deliver_goods_info['goods_id'];
        $update = array('is_group_ladder' => 0);
        $goods_condition = array('goods_id' => $goods_id);
        $model_goods->editGoods($update,$goods_condition);

        // 添加对列修改商品促销价格
         QueueClient::push('updateGoodsPromotionPriceByGoodsId', $buy_deliver_goods_info['goods_id']);

        $this->recordSellerLog('删除即买即送商品，活动名称：'.$buy_deliver_info['buy_deliver_name'].'，商品名称：'.$buy_deliver_goods_info['goods_name']);
        echo json_encode($data);die;
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
            1=>array('menu_key'=>'buy_deliver_list','menu_name'=>Language::get('promotion_active_list'),'menu_url'=>'index.php?act=store_buy_deliver&op=buy_deliver_list'),
        );
        switch ($menu_key){
        	case 'buy_deliver_add':
                $menu_array[] = array('menu_key'=>'buy_deliver_add','menu_name'=>Language::get('promotion_join_active'),'menu_url'=>'index.php?act=store_buy_deliver&op=buy_deliver_add');
        		break;
        	case 'buy_deliver_edit':
                $menu_array[] = array('menu_key'=>'buy_deliver_edit','menu_name'=>'编辑活动','menu_url'=>'javascript:;');
        	// 	break;
        	// case 'xianshi_quota_add':
         //        $menu_array[] = array('menu_key'=>'xianshi_quota_add','menu_name'=>Language::get('promotion_buy_product'),'menu_url'=>'index.php?act=store_promotion_xianshi&op=xianshi_quota_add');
        	// 	break;
        	case 'buy_deliver_manage':
                $menu_array[] = array('menu_key'=>'buy_deliver_manage','menu_name'=>Language::get('promotion_goods_manage'),'menu_url'=>'index.php?act=store_buy_deliver&op=buy_deliver_manage&buy_deliver_id='.$_GET['buy_deliver_id']);
        		break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }
}
