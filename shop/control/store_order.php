<?php
/**
 * 卖家实物订单管理
 *
 **/


defined('In718Shop') or exit('Access Invalid!');
class store_orderControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct();
        Language::read('member_store_index');
         $model_refund = Model('refund_return');
        $model_refund->getRefundStateArray();
    }

	/**
	 * 订单列表
	 *
	 */


	public function indexOp() {
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
            //增加自提id为0的，处理包邮
            // $seller_group['ziti_limits'] .= ',0';
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
        
        //print_r($address_list);die;
        Tpl::output('address_list',$address_list);

        //获取仓库列表
        $storage_list = Model('storage')->field('storage_id,storage_name')->select();
        Tpl::output('storage_list',$storage_list);

        //获取发货人列表
        $deliverer = Model('daddress')->table('daddress')->field('address_id,seller_name')->where( array('store_id' =>4 ))->select();
        Tpl::output('deliverer',$deliverer);

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
        }
        // else{
        //     $condition['order_common.reciver_ziti_id']  = array('in',$seller_group['ziti_limits']);
        // }
        //增加仓库和是否包邮
        //自提地点
        if ($_GET['storage_id'] != '') {
            $condition['order.storage_id']  = $_GET['storage_id'];
        }
        if ($_GET['by_post'] != '') {
            $condition['order.by_post']  = $_GET['by_post'];
        }

        // //发货人姓名 新增 11.1

        // if($_GET['senderusername']!=''){
        //     $sql="SELECT * from `718shop_order_goods` where kuajing_info like '%".$_GET['senderusername']."%'";
        //     $kuajing_info=Model()->query($sql);
        //     $order_id=array();
        //     for($i=0;$i<count($kuajing_info);$i++){
        //         $order_id[$i]=$kuajing_info[$i]['order_id'];
        //     }

        //     $condition['order.order_id']=array('in',$order_id);
        // }

	    if ($_GET['consignee_name'] != '') {
        $condition['order_common.reciver_name']=$_GET['consignee_name'];
        }

        if ($_GET['is_mode'] != '') {
            $condition['order.is_mode'] = $_GET['is_mode'];
        }

        if ($_GET['payment_code'] != '') {
            $condition['order.payment_code'] = $_GET['payment_code'];
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

         //搜索发货人
        if ($_GET['deliverer_id'] != '') {
            $deliverer_id  = $_GET['deliverer_id'];
            // $deliever_order_or = Model('order_goods')->field('distinct order_id')->where(array('deliverer_id' =>$deliverer_id ))->order('order_id desc')->select();
            $deliever_order_or = Model('order_goods')->table('order_goods,order')->on('order_goods.order_id=order.order_id')->field('distinct order.order_id')->where(array('order_goods.deliverer_id' =>$deliverer_id ,'order.order_state'=>$condition['order.order_state']))->order('order.order_id desc')->select();
            if(!empty($deliever_order_or)){
                 $deliever_order_array = array();
                foreach ($deliever_order_or as $value) {
                    $deliever_order_array[] = $value['order_id'];
                }
                $deliever_order = join(",",$deliever_order_array );                
            }else{
                $deliever_order = '';
            }
            $condition['order.order_id'] = array('in',$deliever_order);
        }
        Tpl::output('deliverer_id',$deliverer_id);
        // if(!empty($condition['order.order_state'])&&$condition['order.order_state'] !=10){
        //     $condition['order.is_zorder'] = array('gt',0);
        // }
        // $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        // $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        // $_GET['query_start_date'] = $_GET['query_start_date'] ? $_GET['query_start_date'] : date('Y-m-d H:i:s', time() - 7 * 86400);

        if($_GET['query_start_date']=="" && $_GET['query_end_date']=="" && $_GET['buyer_name']=="" && $_GET['deliverer_id']=="" && $_GET['consignee_name']=="" && $_GET['address_id']=="" && $_GET['storage_id']=="" && $_GET['by_post']=="" && $_GET['order_sn']=="" && $_GET['payment_code']=="" && $_GET['goods_name']==""){
            $_GET['query_start_date'] = date('Y-m-d H:i:s', time() - 90 * 86400);
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

        if ($_GET['goods_name'] != "") {
            $condition['order_goods.goods_name'] = array('like','%'.$_GET['goods_name'].'%');
        }

        if($_GET['state_type'] == 'cw'){
            $condition['cw_log.code'] = array('neq',0);
            $condition['order.order_state'] = array('gt',19);
            $order_cw_info = $model_order->getOrderGoodsList(array('is_cw'=>1),'order_id');
            $condition['order.order_id'] = array('in',array_unique(array_column($order_cw_info,'order_id')));
            $order_list = $model_order->getOrderList2cw($consignee_name, $condition, 20, '*', '', '', array('order_goods', 'order_common', 'member'), '', 'type');
        }else {
            $order_list = $model_order->getOrderList2($consignee_name, $condition, 20, '*', '', '', array('order_goods', 'order_common', 'member'), '', 'type');
        }
        //$order_list = $model_order->getOrderList2($consignee_name,$condition, 20, '*', '','', array('order_goods','order_common','member'),'','type');
	    //print_r($order_list);
		//break;
        $model_refund_return = Model('refund_return');
        $order_list = $model_refund_return->getGoodsRefundList($order_list);
        //页面中显示那些操作

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

			//订单推送情况
			//$model_cw = Model('cw');
           $order_info['cw_code'] = Model()->table('cw_log')->getfby_order_id($order_info['order_id'],'code')==0?Model()->table('cw_log')->getfby_order_id($order_info['order_id'],'code'):1;
		   //$cw_log_info = $model_cw->cw_logGet($order_info['order_id']);
		   //$order_info['cw_code'] = $cw_log_info['code'];
           $order_info['cw_msg'] = Model()->table('cw_log')->getfby_order_id($order_info['order_id'],'msg');

            $order_info['cw'] = 0;
             $order_info['biaoshi']=0;

             $condition2 = array();
            $condition2['buyer_id'] = $order_info['buyer_id'];
            $condition2['order_id'] = $order_info['order_id'];
            $condition2['goods_id'] = '0';
            $condition2['seller_state'] = array('lt','3');
            $refund_list = $model_refund_return->getRefundReturnList($condition2);
            if (!empty($refund_list) && is_array($refund_list)) {
                        $refund = $refund_list[0];
                    }
        	foreach ($order_info['extend_order_goods'] as $value) {
        	    $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
        	    $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
                //edit0420
        	     $value['goods_type_cn'] = goodsTypeName($value['goods_type']);
                //    if ($value['goods_type'] == 4) {
                //        $model_xianshi_goods = Model('p_xianshi_goods');
                //         $model_xianshi = Model('p_xianshi');
                //         $xianshigoods = $model_xianshi_goods->getXianshiGoodsInfo(array('xianshi_goods_id' => $value['promotions_id'] ));
                //         $xianshi_info = $model_xianshi->getXianshiInfo(array('xianshi_id' => $xianshigoods['xianshi_id']));
                //        $order_info['extend_order_goods'][$key]['xianshi_type']=$xianshi_info['xianshi_type'];
                //         if($xianshi_info['xianshi_type']==1){
                //         $value['goods_type_cn']='限时秒杀';
                //         }else{
                //            $value['goods_type_cn']='限时折扣';
                //         }
                        
                // }
                 // var_dump($refund['refund_id']);die;
                  if ($refund['refund_id'] > 0 ) {
                    $value['refund']=0;
                   }
                 if($value['is_cw']==1){
                    $order_info['cw'] = 1;
                 }

                 if($value['refund']!=1){
                      $order_info['biaoshi']=1;
                }   
				// switch ($order_info['order_type']){
    //                 case 0:
    //                     $value['goods_type_cn'] = '';
    //                     break;
    //                 case 1:
    //                     $value['goods_type_cn'] = '阶梯价';
    //                     break;
    //                 case 2:
    //                     $value['goods_type_cn'] = '团购';
    //                     break;
    //                 case 3:
    //                     $value['goods_type_cn'] = '新人专享';
    //                     break;
    //                 case 4:
    //                     $value['goods_type_cn'] = '限时秒杀';
    //                     break;
    //                 case 5:
    //                     $value['goods_type_cn'] = '即买即送';
    //                     break;
    //             }
        	    $value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
        	    // if ($value['goods_type'] == 5) {
        	    //     $order_info['zengpin_list'][] = $value;
        	    // } else {
        	        $order_info['goods_list'][] = $value;
        	    // }
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
            //剔除订单列表已付款的总单保留分单和集市总单又是分单的所有状态
            //$arraystates = array(20,30,40);
            //if($order_info['is_zorder']==0 && in_array($order_info['order_state'], $arraystates)){
                 //unset($order_list[$key]);
            //}

        }
        //echo '<pre>';var_dump($order_list);die;
         
        Tpl::output('order_list',$order_list);
        Tpl::output('show_page',$model_order->showpage());
        self::profile_menu('list',$_GET['state_type']);

        Tpl::showpage('store_order.index');
	}


	/**
	 * 卖家订单详情
	 *
	 */
	public function show_orderOp() {
		Language::read('member_member_index');
	    $order_id = intval($_GET['order_id']);
	    if ($order_id <= 0) {
	        showMessage(Language::get('wrong_argument'),'','html','error');
	    }
	    $model_order = Model('order');
	    $condition = array();
        $condition['order_id'] = $order_id;
        $condition['store_id'] = $_SESSION['store_id'];
	    $order_info = $model_order->getOrderInfo($condition,array('order_common','order_goods','member'));
	    if (empty($order_info)) {
	        showMessage(Language::get('store_order_none_exist'),'','html','error');
	    }

        $model_refund_return = Model('refund_return');
        $order_list = array();
        $order_list[$order_id] = $order_info;
        $order_list = $model_refund_return->getGoodsRefundList($order_list,1);//订单商品的退款退货显示
        $order_info = $order_list[$order_id];
        $refund_all = $order_info['refund_list'][0];
        if (!empty($refund_all) && $refund_all['seller_state'] < 3) {//订单全部退款商家审核状态:1为待审核,2为同意,3为不同意
            Tpl::output('refund_all',$refund_all);
        }

        //显示锁定中
        $order_info['if_lock'] = $model_order->getOrderOperateState('lock',$order_info);

    	//显示调整运费
    	$order_info['if_modify_price'] = $model_order->getOrderOperateState('modify_price',$order_info);
		
		//显示调整价格
    	$order_info['if_spay_price'] = $model_order->getOrderOperateState('spay_price',$order_info);

        //显示取消订单
        $order_info['if_cancel'] = $model_order->getOrderOperateState('buyer_cancel',$order_info);

    	//显示发货
    	$order_info['if_send'] = $model_order->getOrderOperateState('send',$order_info);

        //显示物流跟踪
        $order_info['if_deliver'] = $model_order->getOrderOperateState('deliver',$order_info);

        //显示系统自动取消订单日期
        if ($order_info['order_state'] == ORDER_STATE_NEW) {
            //$order_info['order_cancel_day'] = $order_info['add_time'] + ORDER_AUTO_CANCEL_DAY * 24 * 3600;
			// by 
			//$order_info['order_cancel_day'] = $order_info['add_time'] + ORDER_AUTO_CANCEL_TIME + 3 * 24 * 3600;
			$order_info['order_cancel_day'] = $order_info['add_time'] + ORDER_AUTO_CANCEL_TIME * 60;
        }

        //显示快递信息
        if ($order_info['shipping_code'] != '') {
            $express = rkcache('express',true);
            $order_info['express_info']['e_code'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_code'];
            $order_info['express_info']['e_name'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_name'];
            $order_info['express_info']['e_url'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_url'];
        }

        //显示系统自动收获时间
        if ($order_info['order_state'] == ORDER_STATE_SEND) {
            if($order_info['is_mode']==0) {
                $order_info['order_confirm_day'] = $order_info['delay_time'] + ORDER_AUTO_RECEIVE_DAY * 24 * 3600;
            }elseif ($order_info['is_mode']==2){
                $order_info['order_confirm_day'] = $order_info['delay_time'] + JIHUO_ORDER_AUTO_RECEIVE_DAY * 24 * 3600;
            }
			//by www.mxshopcissi.net
//			$order_info['order_confirm_day'] = $order_info['delay_time'] + ORDER_AUTO_RECEIVE_DAY + 15 * 24 * 3600;
        }

        //如果订单已取消，取得取消原因、时间，操作人
        if ($order_info['order_state'] == ORDER_STATE_CANCEL) {
            $order_info['close_info'] = $model_order->getOrderLogInfo(array('order_id'=>$order_info['order_id']),'log_id desc');
        }
foreach ($order_info['extend_order_goods'] as $k => $v) {
            $p[]=$v['goods_num']*$v['goods_price'];
        }
        $all_price=array_sum($p);
        $count=count($order_info['extend_order_goods'])-1;
        foreach ($order_info['extend_order_goods'] as $key =>$value) {
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
            $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
            $value['goods_type_cn'] = goodsTypeName($value['goods_type']);//20210115SLKEDIT
            $value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
    //           if ($value['goods_type'] == 4) {
    // $model_xianshi_goods = Model('p_xianshi_goods');
    //                     $model_xianshi = Model('p_xianshi');
    //                     $xianshigoods = $model_xianshi_goods->getXianshiGoodsInfo(array('xianshi_goods_id' => $value['promotions_id'] ));
    //                     $xianshi_info = $model_xianshi->getXianshiInfo(array('xianshi_id' => $xianshigoods['xianshi_id']));
    //                    $order_info['extend_order_goods'][$key]['xianshi_type']=$xianshi_info['xianshi_type'];
    //                     if($xianshi_info['xianshi_type']==1){
    //                     $value['goods_type_cn']='限时秒杀';
    //                     }else{
    //                        $value['goods_type_cn']='限时折扣';
    //                     }
                        
    //             }
        //     $voucher_price=$order_info['extend_order_common']['voucher_price'];
        //     $goods_price=$value['goods_num']*$value['goods_price'];
        //     $rate=$goods_price/$all_price;
        //             $rate=floor($rate*100)/100;
        // if($key==$count){
        //      if($key==0){
        //          $value['voucher_price']= $voucher_price;
        //      }else{
        //           $last=$voucher_price-$all_voucher_price;
        //          // $value['voucher_price']=floor($last*1000)/1000;
        //          $value['voucher_price']=$last;

        //      }
        //  }else{
        //      $value['voucher_price']= $voucher_price*$rate;
        //  }
        //  // var_dump($rate);die;
        //  $all_voucher_price+=$value['voucher_price'];
            // if ($value['goods_type'] == 5) {
            //     $order_info['zengpin_list'][] = $value;
            // } else {
                $order_info['goods_list'][] = $value;
            // }
        }
        
        if (empty($order_info['zengpin_list'])) {
            $order_info['goods_count'] = count($order_info['goods_list']);
        } else {
            $order_info['goods_count'] = count($order_info['goods_list']) + 1;
        }
		
		switch($order_info['order_type']){
			case 0:
				$order_info['order_type_info'] = '';
				break;
			case 1:
				$order_info['order_type_info'] = '阶梯价';
				break;
			case 2:
				$order_info['order_type_info'] = '团购';
				break;
			case 3:
				$order_info['order_type_info'] = '新人专享';
				break;
			case 4:
				$order_info['order_type_info'] = '限时秒杀';
				break;
			case 5:
				$order_info['order_type_info'] = '即买即送';
				break;
		}
         //详细地址
        $mall_info = Model('order')->getOrderCommonInfo(array('order_id'=>$order_id));
        $order_info['mall_info'] = $mall_info['mall_info'];

	    Tpl::output('order_info',$order_info);

        //发货信息
        if (!empty($order_info['extend_order_common']['daddress_id'])) {
            $daddress_info = Model('daddress')->getAddressInfo(array('address_id'=>$order_info['extend_order_common']['daddress_id']));
            Tpl::output('daddress_info',$daddress_info);
        }

        //入库/平台发货信息展示
        $fahuo_info = '无信息';
        if($order_info['order_state'] >= 30){
        	if(!empty($order_info['ruku_user'])){
        		$ru_user =  Model()->table('member')->field('member_name')->where(array('member_id' =>$order_info['ruku_user'] ))->find(); 
        		// $fahuo_info['fa_state'] = '扫码入库';
        		// $fahuo_info['fa_userid'] = $order_info['ruku_user'];
        		// $fahuo_info['fa_username'] = $ru_user['member_name'];
        		$fahuo_info = '<br/>发货方式：扫码入库；<br/>发货人id:'.$order_info['ruku_user'].'<br/>发货人名称：'.$ru_user['member_name'];
        	}else{
        		$ru_user =  Model()->table('order_log')->field('log_role, log_user')->where(array('order_id' =>$order_info['order_id'],'log_orderstate' =>'30'))->find(); 
        		if(!empty($ru_user)){
        			// $fahuo_info['fa_state'] = '平台发货';
        			// $fahuo_info['fa_userid'] = $ru_user['log_role'];
        			// $fahuo_info['fa_username'] = $ru_user['log_user'];
        			$fahuo_info = '<br/>发货方式：平台发货；<br/>发货人角色:'.$ru_user['log_role'].'<br/>发货人名称：'.$ru_user['log_user'];
        		}else{
                      $ru_user =  Model()->table('order_log')->field('log_role, log_user')->where(array('order_id' =>$order_info['order_id'],'log_orderstate' =>'25'))->find(); 
                    if(!empty($ru_user)){
                        // $fahuo_info['fa_state'] = '平台发货';
                        // $fahuo_info['fa_userid'] = $ru_user['log_role'];
                        // $fahuo_info['fa_username'] = $ru_user['log_user'];
                        $fahuo_info = '<br/>发货方式：平台发货；<br/>发货人角色:'.$ru_user['log_role'].'<br/>发货人名称：'.$ru_user['log_user'];
                    }else{
                        $fahuo_info = '<br/>未进行发货处理，直接进行确认收货！';
                    }
        		}
        		
        		
        	}
        }
        Tpl::output('fahuo_info',$fahuo_info);

        //核销/平台自动收货信息展示
        if($order_info['order_state']= 40){
        	if(!empty($order_info['hexiao_user'])){
        		$shou_user =  Model()->table('member')->field('member_name')->where(array('member_id' =>$order_info['hexiao_user'] ))->find(); 
        		// $fahuo_info['fa_state'] = '扫码入库';
        		// $fahuo_info['fa_userid'] = $order_info['ruku_user'];
        		// $fahuo_info['fa_username'] = $ru_user['member_name'];
        		$shouhuo_info = '<br/>收货方式：扫码核销；<br/>扫码人id:'.$order_info['hexiao_user'].'<br/>扫码人名称：'.$shou_user['member_name'];
        	}else{
        		$shou_user =  Model()->table('order_log')->field('log_role, log_user')->where(array('order_id' =>$order_info['order_id'],'log_orderstate' =>'40'))->find(); 
        		// $fahuo_info['fa_state'] = '平台发货';
        		// $fahuo_info['fa_userid'] = $ru_user['log_role'];
        		// $fahuo_info['fa_username'] = $ru_user['log_user'];
        		$shouhuo_info = '<br/>收货方式：自动收货；<br/>收货人角色:'.$shou_user['log_role'].'<br/>收货人人名称：'.$shou_user['log_user'];
        	}
        }
        Tpl::output('shouhuo_info',$shouhuo_info);
		Tpl::showpage('store_order.show');
	}
    /**
     * 卖家订单打印小票
     *
     */
    public function print_xiaopiaoOp() {
       
        $order_id   = intval($_GET['order_id']);

        $model_order = Model('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['store_id'] = $_SESSION['store_id'];
        $order_info = $model_order->getOrderInfo($condition, array('order_common'));
        //打印机信息
        $model_dayin = Model('yundayin');
        $dayin_info = $model_dayin->where(array('state'=>1))->order('dayin_id desc')->select();
        if(!chksubmit()) {
            Tpl::output('dayin_info',$dayin_info);
            Tpl::output('order_info',$order_info);
            Tpl::output('order_id',$order_info['order_id']);
            Tpl::showpage('store_order.print_xiaopiao','null_layout');
            exit();
        } else {
            //打印机信息
            $dayin_id = intval($_POST['dayin_id']);
            $dayinji_info = $model_dayin->where(array('dayin_id'=>$dayin_id))->find();
           
            $order_detail = $model_order->getFpOrderInfo(array('order_sn'=>$order_info["order_sn"]),array('order_common','order_goods','member'));
            //设置打印机接口参数
            $SN = $dayinji_info['dayin_sn'];      //*必填*：打印机编号，必须要在管理后台里添加打印机或调用API接口添加之后，才能调用API
            $state = 0;

           //调用小票机打印订单接口
            $dy_result = Model('yundayin')->sd_printorder($SN,$order_detail,0);
            
            //打印日志记录
            $logdata['order_id'] =  $order_id;
            $logdata['dayin_id'] = $dayinji_info['dayin_id'];
            $address_id = $order_detail['extend_order_common']['reciver_ziti_id'] ;
            $logdata['address_id'] = $address_id;
            $logdata['is_refund'] = 0;
            $logdata['from_dayin'] = 2;
            //判断接口是否成功，不成功重复请求
            if($dy_result['code'] == 1){
                $dy_result['arr'] = json_decode($dy_result['json'] ,true);
               
                //正确例子
                if($dy_result['arr']['ret'] == 0){
                    //更新订单发送状态
                    $condition1['order_id'] = $order_id;
                    $updata['dayin_state'] = 1;
                    $model_order->table('order')->where($condition1)->update($updata);
                    //添加订单日志
                    $model_dayin->sd_insert_dayinlog($dy_result['arr'], $dy_result['json'], $logdata);
                    $state = 1;
                }else{
                    //添加订单日志
                   $model_dayin->sd_insert_dayinlog($dy_result['arr'], $dy_result['json'], $logdata);
                }              
            }else{
                //添加订单日志
                $model_dayin->sd_insert_dayinlog($dy_result['arr'], $dy_result['json'], $logdata);
            }
            
            if (!$state) {
                showDialog('打印小票失败！','','error',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
            } else {
                showDialog('打印小票成功！','reload','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
            }
            
        }
        
    }
	/**
	 * 卖家订单状态操作
	 *
	 */
	public function change_stateOp() {
		$state_type	= $_GET['state_type'];
		$order_id	= intval($_GET['order_id']);

		$model_order = Model('order');
		$condition = array();
		$condition['order_id'] = $order_id;
		$condition['store_id'] = $_SESSION['store_id'];
		$order_info	= $model_order->getOrderInfo($condition);

		if ($_GET['state_type'] == 'order_cancel') {
		    $result = $this->_order_cancel($order_info,$_POST);
        } else if ($_GET['state_type'] == 'order_receive') {
            $result = $this->_order_receive($order_info, $_POST);
		} elseif ($_GET['state_type'] == 'modify_price') {
		    $result = $this->_order_ship_price($order_info,$_POST);
		} elseif ($_GET['state_type'] == 'spay_price') {
			$result = $this->_order_spay_price($order_info,$_POST);
    		 } elseif ($_GET['state_type'] == 'shouhou') {
            $result = $this->_order_shohou($order_info,$_POST);
            }
        if (!$result['state']) {
            showDialog($result['msg'],'','error',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
        } else {
            showDialog($result['msg'],'reload','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
        }
	}
     /**
     * 已发货转
     */
    private function _order_shohou($order_info, $post) {
        if (!chksubmit()) {
            Tpl::output('order_info', $order_info);
            Tpl::showpage('seller_order.shouhou','null_layout');
            exit();
        } else {
            $model_order = Model('order');
            $logic_order = Logic('order');
          
            return $logic_order->changeOrderStateshouhou($order_info,'seller',$_SESSION['member_name']);
        }
    }
     /**
     * 收货
     */
    private function _order_receive($order_info, $post) {
        if (!chksubmit()) {
            Tpl::output('order_info', $order_info);
            Tpl::showpage('seller_order.receive','null_layout');
            exit();
        } else {
            $model_order = Model('order');
            $logic_order = Logic('order');
            $if_allow = $model_order->getOrderOperateState('receive',$order_info);
            if (!$if_allow) {
                return callback(false,'无权操作');
            }
            return $logic_order->changeOrderStateReceive($order_info,'seller',$_SESSION['member_name']);
        }
    }

	/**
	 * 取消订单
	 * @param unknown $order_info
	 */
	private function _order_cancel($order_info, $post) {
	    $model_order = Model('order');
	    $logic_order = Logic('order');

        if(!chksubmit()) {
            Tpl::output('order_info',$order_info);
            Tpl::output('order_id',$order_info['order_id']);
            Tpl::showpage('store_order.cancel','null_layout');
            exit();
        } else {
            $if_allow = $model_order->getOrderOperateState('store_cancel',$order_info);
            if (!$if_allow) {
                return callback(false,'无权操作');
            }
            $msg = $post['state_info1'] != '' ? $post['state_info1'] : $post['state_info'];
            $this->send($order_info['order_id']);
            return $logic_order->changeOrderStateCancel($order_info,'seller',$_SESSION['member_name'], $msg);
        }
	}

    /**
     * mq发送数据
     */
    private function send($data){

        $rabbitMQ = new RabbitMQ();

        $connection = $rabbitMQ->connection('10.10.11.141', 5672, 'wzxd', 'WZXDRMQpython~XX2');

        if($connection){

            $rabbitMQ->sendTopic($connection,$data,'order_cancel_topic_exchange','cancel.#');

            $rabbitMQ->close($connection);

        }

    }

	/**
	 * 修改运费
	 * @param unknown $order_info
	 */
	private function _order_ship_price($order_info, $post) {
	    $model_order = Model('order');
	    $logic_order = Logic('order');
	    if(!chksubmit()) {
	        Tpl::output('order_info',$order_info);
	        Tpl::output('order_id',$order_info['order_id']);
            Tpl::showpage('store_order.edit_price','null_layout');
            exit();
        } else {
            $if_allow = $model_order->getOrderOperateState('modify_price',$order_info);
            if (!$if_allow) {
                return callback(false,'无权操作');
            }
            return $logic_order->changeOrderShipPrice($order_info,'seller',$_SESSION['member_name'],$post['shipping_fee']);           
        }

	}
	/**
	 * 修改商品价格
	 * @param unknown $order_info
	 */
	private function _order_spay_price($order_info, $post) {
        $model_order = Model('order');
	    $logic_order = Logic('order');
	    if(!chksubmit()) {
	        Tpl::output('order_info',$order_info);
	        Tpl::output('order_id',$order_info['order_id']);
            Tpl::showpage('store_order.edit_spay_price','null_layout');
            exit();
        } else {
            $if_allow = $model_order->getOrderOperateState('spay_price',$order_info);
            if (!$if_allow) {
                return callback(false,'无权操作');
            }
            return $logic_order->changeOrderSpayPrice($order_info,'seller',$_SESSION['member_name'],$post['goods_amount']); 
	    }
	}

	public function cwsubmitOp() {
        $order_id = intval($_GET['order_id']);
        $model_order = Model('order');
        $model_cw = Model('cw');
        $cw_info = $model_cw->cw_logGet($order_id);
        if($cw_info && $cw_info['code'] == 0){
            showDialog('请勿重复提交', 'reload');
        }
        $order_info = $model_order->getOrderInfo(array('order_id'=>$order_id));
        //云仓 已支付订单同步接口
        $orderList = array();
        $orderList['tenantId'] = 42;
        $orderList['orderSn'] = $order_info['order_sn'];
        $orderList['orderStatus'] = 0;
        $orderList['totalAmount'] = $order_info['order_amount'];
        $orderList['orderTime'] = date('Y-m-d H:i:s',$order_info['add_time']);
        $orderList['salerCode'] = 'WZXD';
        $orderList['salerName'] = '物资小店';
        $orderList['orderAddress'] = unserialize(Model()->table('order_common')->getfby_order_id($order_id,'reciver_info'))['address'];
        $goods_list = $model_order->getOrderGoodsList(array('order_id'=>$order_id),'goods_id,goods_serial as goodsCode,goods_name as goodsName,goods_price as goodsPrice,goods_num as goodsCount,goods_pay_price as goodsMoney,is_cw');
        $goodsList = [];
        $goods_amount = 0;
        $cw_sign = 0;
        foreach ($goods_list as $key=>$order_goods){
            $is_cw = $order_goods['is_cw'];
            if($is_cw == 1){
                $goodsList[$key]['orderSn'] = $order_info['order_sn'];
                $goodsList[$key]['goodsCode'] = $order_goods['goodsCode'];
                $goodsList[$key]['goodsName'] = $order_goods['goodsName'];
                $goodsList[$key]['goodsPrice'] = $order_goods['goodsPrice'];
                $goodsList[$key]['goodsCount'] = $order_goods['goodsCount'];
                $goodsList[$key]['goodsMoney'] = $order_goods['goodsMoney'];
                $goodsList[$key]['realMoney'] = $order_goods['goodsMoney'];
                $cw_sign = 1;
            }else{
                $goods_amount += $order_goods['goodsMoney'];
                continue;
            }
        }
        unset($goods_list);
        if($cw_sign == 0){//订单里没有云仓商品，直接更新云仓确认收货标志为1
            $model_order->editOrder(array('is_cw_completed'=>1),array('order_id'=>$order_id));
        }
        $orderList['totalAmount'] = $order_info['order_amount'] - $goods_amount;
        $orderList['goodsList'] = array_values($goodsList);
        $res = $model_cw->cwOrderSubmit($orderList);
        $cw_log_info = $model_cw->cw_logGet($order_id);
        $cw_log = array();
        $cw_log['add_time'] = TIMESTAMP;
        $cw_log['order_id'] = $order_id;
        $cw_log['code'] = $res['code']?$res['code']:1;
        $cw_log['msg'] = $res['msg']?$res['msg']:'';
		if($cw_log['code'] == 1 && $cw_log['msg'] == '请勿重复提交订单'){
			$cw_log['code'] = 0;
		}
        if($cw_log_info){
            $model_cw->cw_logUpdate($order_id,$cw_log);
        }else{
            $model_cw->cw_logAdd($cw_log);
        }
        showDialog($cw_log['msg'],'reload');
    }
	

	public function cwsubmitallOp(){
		$model_order = Model('order');
		$model_cw = Model('cw');
		//$condition['cw_log.code'] = array('neq',0);
        //$condition['order.order_state'] = array('gt',19);
        $order_cw_info = $model_order->getOrderGoodsList(array('is_cw'=>1),'order_id');
        $condition['order.order_id'] = 107745;//array('in',array_unique(array_column($order_cw_info,'order_id')));
        $order_list = $model_order->getOrderList2($consignee_name, $condition, 20, 'order_id', '', '', array('order_goods', 'order_common', 'member'), '', 'type');//echo '<pre>';var_dump($order_list);die;
		if($order_list){
			foreach($order_list as $item){
				$order_id = $item['order_id'];
				$cw_info = $model_cw->cw_logGet($order_id);
				if($cw_info && $cw_info['code'] == 0){
					showDialog('请勿重复提交', 'reload');
				}
				$order_info = $model_order->getOrderInfo(array('order_id'=>$order_id));
				//云仓 已支付订单同步接口
				$orderList = array();
				$orderList['tenantId'] = 42;
				$orderList['orderSn'] = $order_info['order_sn'];
				$orderList['orderStatus'] = 0;
				$orderList['totalAmount'] = $order_info['order_amount'];
				$orderList['orderTime'] = date('Y-m-d H:i:s',$order_info['add_time']);
				$orderList['salerCode'] = 'WZXD';
				$orderList['salerName'] = '物资小店';
				$orderList['orderAddress'] = unserialize(Model()->table('order_common')->getfby_order_id($order_id,'reciver_info'))['address'];
				$goods_list = $model_order->getOrderGoodsList(array('order_id'=>$order_id),'goods_id,goods_serial as goodsCode,goods_name as goodsName,goods_price as goodsPrice,goods_num as goodsCount,goods_pay_price as goodsMoney,is_cw');
				$goodsList = [];
				$goods_amount = 0;
				$cw_sign = 0;
				foreach ($goods_list as $key=>$order_goods){
					$is_cw = $order_goods['is_cw'];
					if($is_cw == 1){
						$goodsList[$key]['orderSn'] = $order_info['order_sn'];
						$goodsList[$key]['goodsCode'] = $order_goods['goodsCode'];
						$goodsList[$key]['goodsName'] = $order_goods['goodsName'];
						$goodsList[$key]['goodsPrice'] = $order_goods['goodsPrice'];
						$goodsList[$key]['goodsCount'] = $order_goods['goodsCount'];
						$goodsList[$key]['goodsMoney'] = $order_goods['goodsMoney'];
                        $goodsList[$key]['realMoney'] = $order_goods['goodsMoney'];
						$cw_sign = 1;
					}else{
						$goods_amount += $order_goods['goodsMoney'];
						continue;
					}
				}
				unset($goods_list);
				if($cw_sign == 0){//订单里没有云仓商品，直接更新云仓确认收货标志为1
					$model_order->editOrder(array('is_cw_completed'=>1),array('order_id'=>$order_id));
				}
				$orderList['totalAmount'] = $order_info['order_amount'] - $goods_amount;
				$orderList['goodsList'] = array_values($goodsList);
				$res = $model_cw->cwOrderSubmit($orderList);
				$cw_log_info = $model_cw->cw_logGet($order_id);
				$cw_log = array();
				$cw_log['add_time'] = TIMESTAMP;
				$cw_log['order_id'] = $order_id;
				$cw_log['code'] = $res['code']?$res['code']:1;
				$cw_log['msg'] = $res['msg']?$res['msg']:'';
				if($cw_log['code'] == 1 && $cw_log['msg'] == '请勿重复提交订单'){
					$cw_log['code'] = 0;
				}
				if($cw_log_info){
					$model_cw->cw_logUpdate($order_id,$cw_log);
				}else{
					$model_cw->cw_logAdd($cw_log);
				}
			}
		}
		echo '<pre>';var_dump($order_list);die;
	}

    /**
     * 修改收货地址
     * @return boolean
     */
    public function edit_addressOp() {
        $model_order = Model('order');
        $model_daddress = Model('ziti_address');
        $order_id = $_GET['order_id'];
        $condition['order_id'] = $order_id;
        $condition['store_id'] = $_SESSION['store_id'];
        $order_info = $model_order->getOrderInfo($condition,array('order_common','order_goods','member'));
        //echo '<pre>';var_dump($order_info);
        $address_list = $model_daddress->getAddressList([]);
        Tpl::output('order_info',$order_info);
        Tpl::output('address_list',$address_list);
        Tpl::showpage('store_order_show.edit_address','null_layout');
    }
    
    /**
     * 修改收货地址保存
     * @return boolean
     */
    public function edit_address_saveOp() {
        $update['reciver_name'] = $_POST['reciver_name'];
        $update['reciver_ziti_id'] = $_POST['address'];
        $model_order = Model('order');
        $model_ziti_address = Model('ziti_address');
        $ziti_info = $model_ziti_address->getAddressInfo(array('address_id'=>$_POST['address']),'seller_name,city_id,area_info,address');
        $order_info = $model_order->getOrderCommonInfo(array('order_id' => $_POST['order_id']),'reciver_info');
        $reciver_info = unserialize($order_info['reciver_info']);
        $reciver_info['phone'] = $_POST['phone'];
        $reciver_info['mob_phone'] = $_POST['phone'];
        $reciver_info['tel_phone'] = $_POST['phone'];
        $reciver_info['address'] = $ziti_info['area_info'].' '.$ziti_info['address'].' '.$ziti_info['seller_name'];
        $reciver_info['area'] = $ziti_info['area_info'];
        $reciver_info['street'] = $ziti_info['address'];
        $update['reciver_info'] = serialize($reciver_info);
        $update['reciver_city_id'] = $ziti_info['city_id'];
        $update['mall_info'] = $_POST['mall_info'];
        $result = $model_order->editOrderCommon($update,array('order_id' => $_POST['order_id']));
        if($result){
            showDialog('修改成功', 'reload', 'succ');
        }else{
            showDialog('修改失败', 'reload', 'fail');
        }
    }
    /**
     * 添加订单商品部分退款
     *
     */
    public function add_refundOp(){
        $model_refund = Model('refund_return');
        $condition = array();
        $reason_list = $model_refund->getReasonList($condition);//退款退货原因
        Tpl::output('reason_list',$reason_list);
        $order_id = intval($_GET['order_id']);
        $goods_id = intval($_GET['goods_id']);//订单商品表编号
        // var_dump($order_id);var_dump($goods_id);die;
        if ($order_id < 1 || $goods_id < 1) {//参数验证
            showDialog(Language::get('wrong_argument'),'index.php?act=store_order&op=index','error');
        }
        $condition = array();
        // $condition['buyer_id'] = $_GET['buyer_id'];
        $condition['order_id'] = $order_id;
        $order = $model_refund->getRightOrderList($condition, $goods_id);
        $order_id = $order['order_id'];
        $order_amount = $order['order_amount'];//订单金额
        $order_refund_amount = $order['refund_amount'];//订单退款金额
        $goods_list = $order['goods_list'];
        $goods = $goods_list[0];
        $goods_pay_price = $goods['goods_pay_price'];//商品不加税金的价格
        Tpl::output('goods',$goods);

        $goods_id = $goods['rec_id'];
        $condition = array();
        $condition['buyer_id'] = $order['buyer_id'];
        $condition['order_id'] = $order['order_id'];
        $condition['order_goods_id'] = $goods_id;
        $condition['seller_state'] = array('lt','3');
        $refund_list = $model_refund->getRefundReturnList($condition);
        $refund = array();
        if (!empty($refund_list) && is_array($refund_list)) {
            $refund = $refund_list[0];
        }
        $refund_state = $model_refund->getRefundState($order);//根据订单状态判断是否可以退款退货
        // var_dump($refund_state);die;
        if ($refund['refund_id'] > 0 ) {//检查订单状态,防止页面刷新不及时造成数据错误
            showDialog(Language::get('wrong_argument'),'index.php?act=store_order&op=index','error');
        }
        if (chksubmit() ){

            $refund_array = array();
            $refund_amount = floatval($_POST['refund_amount']);//退款金额
            if (($refund_amount < 0) || ($refund_amount > $goods_pay_price)) {
            $refund_amount = $goods_pay_price;
        }
            $goods_num = $goods['goods_num'];
            $refund_array['reason_info'] = '';
            $reason_id = intval($_POST['reason_id']);//退货退款原因
            $refund_array['reason_id'] = $reason_id;
            $reason_array = array();
            $reason_array['reason_info'] = '其他';
            $reason_list[0] = $reason_array;
            if (!empty($reason_list[$reason_id])) {
                $reason_array = $reason_list[$reason_id];
                $refund_array['reason_info'] = $reason_array['reason_info'];
            }

            $pic_array = array();
            $pic_array['buyer'] = $this->upload_pic();//上传凭证
            $info = serialize($pic_array);
            $refund_array['pic_info'] = $info;

            $model_trade = Model('trade');
            $order_shipped = $model_trade->getOrderState('order_shipped');$refund_array['order_lock'] = '2';//锁定类型:1为不用锁定,2为需要锁定
      $refund_array['order_lock'] = '2';//锁定类型:1为不用锁定,2为需要锁定
        // }
        $refund_array['refund_type'] = $_POST['refund_type'];//类型:1为退款,2为退货
        //$show_url = 'index.php?act=member_return&op=index';
        $refund_array['return_type'] = '2';//退货类型:1为不用退货,2为需要退货
        if ($refund_array['refund_type'] != '2') {
                //类型:1为退款,2为退货
                $refund_array['refund_type'] = '1';
                $refund_array['return_type'] = '1';
                //退货类型:1为不用退货,2为需要退货
        }
            $refund_array['seller_state'] = '1';//状态:1为待审核,2为同意,3为不同意
            $refund_array['refund_amount'] = ncPriceFormat($refund_amount);
            $refund_array['goods_num'] = $goods_num;
            $refund_array['buyer_message'] = $_POST['buyer_message'];
            $refund_array['add_time'] = time();
            $state = $model_refund->addRefundReturn($refund_array,$order,$goods);

            if ($state) {
                // $refund=$model_refund->getRefundReturnInfo(array('refund_id' => $state));
                // $buyer_info = Model('member')->getMemberInfo(array('member_id' => $order['buyer_id']),'member_wxopenid');
                // $output = Model('wxsend')->sendMessage($buyer_info['member_wxopenid'],$refund);
                $model_refund->editOrderLock($order_id);
                $show_url = 'index.php?act=store_refund&op=index';
                showDialog(Language::get('nc_common_save_succ'),$show_url,'succ');
            } else {
                showDialog(Language::get('nc_common_save_fail'),'reload','error');
            }
        }
        Tpl::showpage('store_refund_add');
    }
    /**
     * 添加全部退款即取消订单
     *
     */
    public function add_refund_allOp(){
        $model_order = Model('order');
        $model_trade = Model('trade');
        $model_refund = Model('refund_return');
        $order_id = intval($_GET['order_id']);
        $condition = array();
        // $condition['buyer_id'] = $_SESSION['member_id'];
        $condition['order_id'] = $order_id;
        $order = $model_refund->getRightOrderList($condition);
        Tpl::output('order',$order);
        $order_amount = $order['order_amount'];//订单金额
        $condition = array();
        $condition['buyer_id'] = $order['buyer_id'];
        $condition['order_id'] = $order['order_id'];
        $condition['goods_id'] = '0';
        $condition['seller_state'] = array('lt','3');
        $refund_list = $model_refund->getRefundReturnList($condition);
        $refund = array();
        if (!empty($refund_list) && is_array($refund_list)) {
            $refund = $refund_list[0];
        }
        //根据订单状态判断是否可以退款退货
        $refund_state = $model_refund->getRefundState($order);//根据订单状态判断是否可以退款退货
      
        if ($refund['refund_id'] > 0 ) {//检查订单状态,防止页面刷新不及时造成数据错误
             showDialog(Language::get('nc_common_save_fail'),'reload','error');
        }
        
        $order_paid = $model_trade->getOrderState('order_paid');//订单状态20:已付款
        // $payment_code = $order['payment_code'];//支付方式
        // if ($refund['refund_id'] > 0 || $order['order_state'] != $order_paid || $payment_code == 'offline') {//检查订单状态,防止页面刷新不及时造成数据错误
        //      echo $this->returnMsg(200, '评价晒图上传失败', null);exit();
        // }
        if(!empty($_POST['refund_amount'])){
             $refund_amount = floatval($_POST['refund_amount']);//退款金额
            if (($refund_amount < 0) || ($refund_amount > $order_amount)) {
                $refund_amount = $order_amount;
            }
        }else{
            $refund_amount = $order_amount;
        }
        if (chksubmit()) {
            $refund_array = array();
            $refund_array['refund_type'] = '1';//类型:1为退款,2为退货
            $refund_array['seller_state'] = '1';//状态:1为待审核,2为同意,3为不同意
            $refund_array['order_lock'] = '2';//锁定类型:1为不用锁定,2为需要锁定
            $refund_array['goods_id'] = '0';
            $refund_array['order_goods_id'] = '0';
            $refund_array['reason_id'] = '0';
            $refund_array['reason_info'] = '取消订单，全部退款';
            $refund_array['goods_name'] = '订单商品全部退款';
            $refund_array['refund_amount'] = ncPriceFormat($order_amount);
            $refund_array['buyer_message'] = $_POST['buyer_message'];
            $refund_array['add_time'] = time();

            $pic_array = array();
            $pic_array['buyer'] = $this->upload_pic();//上传凭证
            $info = serialize($pic_array);
            $refund_array['pic_info'] = $info;
            $state = $model_refund->addRefundReturn($refund_array,$order);

            if ($state) {
                //  $refund=$model_refund->getRefundReturnInfo(array('refund_id' => $state));
                // $buyer_info = Model('member')->getMemberInfo(array('member_id' => $order['buyer_id']),'member_wxopenid');
                // $output = Model('wxsend')->sendMessage($buyer_info['member_wxopenid'],$refund);
                $model_refund->editOrderLock($order_id);
                showDialog(Language::get('nc_common_save_succ'),'index.php?act=store_refund&op=index','succ');
            } else {
                showDialog(Language::get('nc_common_save_fail'),'reload','error');
            }
        }
        Tpl::showpage('store_refund_all');
    }
    /**
     * 上传凭证
     *
     */
    private function upload_pic() {
        $refund_pic = array();
        $refund_pic[1] = 'refund_pic1';
        $refund_pic[2] = 'refund_pic2';
        $refund_pic[3] = 'refund_pic3';
        $pic_array = array();
        $upload = new UploadFile();
        $dir = ATTACH_PATH.DS.'refund'.DS;
        $upload->set('default_dir',$dir);
        $upload->set('allow_type',array('jpg','jpeg','gif','png'));
        $count = 1;
        foreach($refund_pic as $pic) {
            if (!empty($_FILES[$pic]['name'])){
                $result = $upload->upfile($pic);
                if ($result){
                    $pic_array[$count] = $upload->file_name;
                    $upload->file_name = '';
                } else {
                    $pic_array[$count] = '';
                }
            }
            $count++;
        }
        return $pic_array;
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
            array('menu_key'=>'state_new',			'menu_name'=>Language::get('nc_member_path_wait_pay'),	'menu_url'=>'index.php?act=store_order&op=index&state_type=state_new'),
            array('menu_key'=>'state_pay',	        'menu_name'=>Language::get('nc_member_path_wait_send'),	'menu_url'=>'index.php?act=store_order&op=store_order&state_type=state_pay'),
            array('menu_key'=>'state_send',		    'menu_name'=>"待取货",	    'menu_url'=>'index.php?act=store_order&op=index&state_type=state_send'),
            array('menu_key'=>'state_success',		'menu_name'=>Language::get('nc_member_path_finished'),	'menu_url'=>'index.php?act=store_order&op=index&state_type=state_success'),
            array('menu_key'=>'state_cancel',		'menu_name'=>Language::get('nc_member_path_canceled'),	'menu_url'=>'index.php?act=store_order&op=index&state_type=state_cancel'),
			array('menu_key'=>'cw','menu_name'=>'云仓待推','menu_url'=>'index.php?act=store_order&op=index&state_type=cw')
            );
            break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }
}
