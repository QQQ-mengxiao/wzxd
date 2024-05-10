<?php
/**
 * 卖家实物订单管理
 *
 **/


defined('In718Shop') or exit('Access Invalid!');
class store_cw_overControl extends BaseSellerControl {

	/**
	 * 订单列表
	 *
	 */
	public function order_overOp() {
        //当前登录账号
        $seller_id = $_SESSION['seller_id'];
        if($_SESSION['seller_name'] == 'shop02'){
            $address_sql="SELECT *  FROM `718shop_ziti_address` WHERE `store_id` = 4";
            $address_list =Model()->query($address_sql);

            $a =array();
            foreach ($address_list as $key => $value) {
                $a[] = $value['address_id'];
            }
            $seller_group['ziti_limits'] = implode(',', $a);
            
        }else{
            $seller = Model('seller')->table('seller')->where(array('seller_id'=>$seller_id))->find();
            $seller_group = Model('seller_group')->table('seller_group')->where(array('group_id'=>$seller['seller_group_id']))->find();
            //print_r($seller_group );
            //登录账号自提地址权限
            $ziti_limits = explode(',', $seller_group['ziti_limits']);
            //显示自提地址列表(搜索)
            $condition2 = array();
            $model_daddress = Model('ziti_address');
            $address_sql="SELECT *  FROM `718shop_ziti_address` WHERE `address_id` IN  (".$seller_group['ziti_limits'].")";
            $address_list =Model()->query($address_sql);
        }
        
        Tpl::output('address_list',$address_list); 

        $model_order = Model('order');
        $condition = array();
        $condition['order.store_id'] = $_SESSION['store_id'];
        if ($_GET['order_sn'] != '') {
            $condition['order.order_sn'] = $_GET['order_sn'];
        }
        if ($_GET['buyer_name'] != '') {
            $condition['order.buyer_name'] = array('like','%'.$_GET['buyer_name'].'%');
        }
        //自提地点
        if ($_GET['address_id'] != '') {
            $condition['order_common.reciver_ziti_id']  = $_GET['address_id'];
        }else{
            $condition['order_common.reciver_ziti_id']  = array('in',$seller_group['ziti_limits']);
        }

        //发货人姓名 新增 11.1
        if($_GET['senderusername']!=''){
            $sql="SELECT * from `718shop_order_goods` where kuajing_info like '%".$_GET['senderusername']."%'";
            $kuajing_info=Model()->query($sql);
            $order_id=array();
            for($i=0;$i<count($kuajing_info);$i++){
                $order_id[$i]=$kuajing_info[$i]['order_id'];
            }

            $condition['order.order_id']=array('in',$order_id);
        }

	    if ($_GET['consignee_name'] != '') {
        $condition['order_common.reciver_name']=$_GET['consignee_name'];
        }

        if ($_GET['is_mode'] != '') {
            $condition['order.is_mode'] = $_GET['is_mode'];
        }
        $allow_state_array = array('state_new','state_pay','state_send','state_success','state_cancel');
        if (in_array($_GET['state_type'],$allow_state_array)) {
            $condition['order.order_state'] = str_replace($allow_state_array,
                    array(ORDER_STATE_NEW,ORDER_STATE_PAY,ORDER_STATE_SEND,ORDER_STATE_SUCCESS,ORDER_STATE_CANCEL), $_GET['state_type']);
        } elseif($_GET['state_type'] == 'cw') {
            $_GET['state_type'] = 'cw';
        }else {
            $_GET['state_type'] = 'store_order';
        }
		if($_GET['state_type'] == 'state_pay'){
			$condition['order.is_zorder'] = array('gt',0); 
		}
		if($_GET['state_type'] == 'state_send'){
			$condition['order.is_zorder'] = array('gt',0); 
		}
		if($_GET['state_type'] == 'state_success'){
			$condition['order.is_zorder'] = array('gt',0); 
		}
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['order.add_time'] = array('between',array($start_unixtime,$end_unixtime));
        }

        if ($_GET['skip_off'] == 1) {
            $condition['order.order_state'] = array('neq',ORDER_STATE_CANCEL);
        }

        $order_list = $model_order->getOrderList2('', $condition, 20, '*', '', '', array('order_goods', 'order_common', 'member'), '', 'type');
        foreach ($order_list as $key => $order_info) {
        
            //显示收货
           $order_info['if_receive'] = $model_order->getOrderOperateState('receive',$order_info);
           //显示取消订单
           $order_info['if_cancel'] = $model_order->getOrderOperateState('store_cancel',$order_info);

           //显示调整运费
           $order_info['if_modify_price'] = $model_order->getOrderOperateState('modify_price',$order_info);
           
           //显示修改价格
           $order_info['if_spay_price'] = $model_order->getOrderOperateState('spay_price',$order_info);

           //显示发货
           $order_info['if_send'] = $model_order->getOrderOperateState('send',$order_info);

           //显示锁定中
           $order_info['if_lock'] = $model_order->getOrderOperateState('lock',$order_info);

           //显示物流跟踪
           $order_info['if_deliver'] = $model_order->getOrderOperateState('deliver',$order_info);

           $order_info['cw'] = 0;
           foreach ($order_info['extend_order_goods'] as $value) {
               $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
               $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
               //edit0420
                $value['goods_type_cn'] = goodsTypeName($value['goods_type']);
                  if ($value['goods_type'] == 4) {
                       $model_xianshi = Model('p_xianshi');
                       $xianshi_info = $model_xianshi->getXianshiInfo(array('xianshi_id' => $value['promotions_id']));
                      $order_info['extend_order_goods'][$key]['xianshi_type']=$xianshi_info['xianshi_type'];
                       if($xianshi_info['xianshi_type']==1){
                       $value['goods_type_cn']='限时秒杀';
                       }else{
                          $value['goods_type_cn']='限时折扣';
                       }
                       
               }
                if($value['is_cw']==1){
                   $order_info['cw'] = 1;
                }
               $value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
                   $order_info['goods_list'][] = $value;
           }

           if (empty($order_info['zengpin_list'])) {
               $order_info['goods_count'] = count($order_info['goods_list']);
           } else {
               $order_info['goods_count'] = count($order_info['goods_list']) + 1;
           }
           
           $order_list[$key] = $order_info;
            //详细地址
           $mall_info = Model('order')->getOrderCommonInfo(array('order_id'=>$order_info['order_id']));
           $order_list[$key]['mall_info'] = $mall_info['mall_info'];
       }
        // echo '<pre>';print_r($order_list);die;
        Tpl::output('order_list',$order_list);
        Tpl::output('show_page',$model_order->showpage());
        self::profile_menu('list',$_GET['state_type']);

        Tpl::showpage('store_cw.over');
	}

    /**
     * 云仓补推订单(收货)
     */
    public function cw_overOp() {
        $order_sn = $_GET['order_sn'];
        if(!$order_sn){
            die('参数错误');
        }
        $data = [
            "tenantId" => 42,
            "orderSn" => $order_sn,
            "orderStatus" => "7"
        ];
        $res = Model('cw')->cwOrderOver($data);
        print_r(json_encode($data));
        print_r($res);
    }

	/**
	 * 用户中心右边，小导航
	 *
	 * @param string	$menu_type	导航类型
	 * @param string 	$menu_key	当前导航的menu_key
	 * @return
     */
    private function profile_menu($menu_type='',$menu_key='') {
        Language::read('member_layout');
        switch ($menu_type) {
        	case 'list':
            $menu_array = array(
            array('menu_key'=>'store_order',		'menu_name'=>Language::get('nc_member_path_all_order'),	'menu_url'=>'index.php?act=store_order'),
            );
            break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }
}
