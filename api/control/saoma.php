<?php
/**
 * 扫码核销
 *
 *
 *
 ***/
defined('In718Shop') or exit('Access Invalid!');

class saomaControl  extends BaseControl{

    /* 链接测试
    */
    public function testOp(){
        echo $this->returnMsg(10000, '请求成功！', '');exit;
    }

    /* 扫码核销
    */
    public function hexiaoOp(){
         $member_id = intval($_GET['member_id']);
        $order_sn = $_GET['order_sn'];
        if (isset($_GET['type'])) {
            $return =  $this->sport_hexiao($order_sn,$member_id,$_GET['type']);
            exit;
        }else{

        	// sleep(50);
            $order_sn = $_GET['order_sn'];
            //获取扫码订单表列表
            $model_order = Model('order');
            $order_info = $model_order->table('order')->where(array('order_sn'=>$order_sn))->find();
            //获取扫码人信息
            $model_member = Model('member');
            $model_cw = Model('cw');
            $member_info = $model_member->table('member')->where(array('member_id'=>$member_id))->find();
            $logdata['order_id'] = $order_info['order_id'];
            $logdata['user_id'] = $member_id;
            $logdata['type'] = 2;
            if($member_info['is_store'] != 2){
                echo $this->returnMsg(10002, '此用户非商户!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                $logdata['code']  = '10002';
                $this->insert_saomalog($logdata);
                exit;
            }
            if(empty($order_info)){
                 echo $this->returnMsg(10003, '此订单无数据!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                 $logdata['code']  = '10003';
                 $this->insert_saomalog($logdata);
                 exit;
            }
            if($order_info['hexiao_time'] != 0 || $order_info['order_state'] == 40){
                echo $this->returnMsg(10004, '此订单为已收货单或已核销单，不能进行二次扫码核销!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                $logdata['code']  = '10004';
                $this->insert_saomalog($logdata);
                exit;
            }
            if( $order_info['order_state'] == 0){
                echo $this->returnMsg(10008, '此订单为已取消，不能进行入库操作!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                 $logdata['code']  = '10008';
                 $a = $this->insert_saomalog($logdata);
                exit;
            }
            /*if($order_info['lock_state'] != 0  ){
                echo $this->returnMsg(10005, '此订单被锁定，不能进行扫码核销!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                $logdata['code']  = '10005';
                $this->insert_saomalog($logdata);
                exit;
            }*/
            if( $order_info['ruku_time'] == 0){//未入库
                $order_id = $order_info['order_id'];
                $model_refund = Model('refund_return');
                $orderSn = $order_info['order_sn'];//Model('order')->getfby_order_id($order_id,'order_sn');
                //$refund_list = $model_refund->getRefundReturnList(array('order_id'=>$order_id,'refund_state'=>3),'','goods_id');
                //if(is_array($refund_list) && !empty($refund_list)){
                //  foreach ($refund_list as $goods_info){
                //      $goodsList[]['goodsCode'] = Model('goods')->getfby_goods_id($goods_info['goods_id'],'goods_serial');
                //  }
                //  $data['tenantId'] = 42;
                //  $data['orderSn'] = $orderSn;
                //  $data['orderStatus'] = 9;//9异常收货
                //  $data['goodsList'] = array_values($goodsList);
                //  $model_cw->cwAbnormalOrderOver($data);
                //}else{
                    $data = [
                        "tenantId" => 42,
                        "orderSn" => $orderSn,
                        "orderStatus" => "7"
                    ];
                    $model_cw->cwOrderOver($data);
                //}
                //$data = [
                    //"tenantId" => 42,
                    //"orderSn" => $order_sn,
                    //"orderStatus" => "7"
                //];
                //$model_cw->cwOrderOver($data);
            }
        
            $update_array = array();
            $update_array['hexiao_time']   = time();
            $update_array['finnshed_time']   = time();
            $update_array['delay_time']   = time();
            $update_array['ruku_time']   = time();
            $update_array['hexiao_user']   = $member_id;
            $update_array['hexiao_from']   = 2;
            $update_array['order_state']   = 40;
            $result = $model_order->table('order')->where(array('order_sn'=>$order_sn))->update($update_array);
            if ($result){
                //插入order_log表
                $log_data_order['order_id'] = $order_info['order_id'];
                $log_data_order['log_msg'] = '线上小程序扫码核销';
                $log_data_order['log_time'] = time();
                $log_data_order['log_role'] = '线上小程序';
                $log_data_order['log_user'] = $member_id;
                $log_data_order['log_orderstate'] = 40;
                   
                Model()->table('order_log')->insert($log_data_order);

                $is_zorder = Model()->table('order')->getfby_order_id($order_info['order_id'],'is_zorder');
                 //查询分单中确认收货的订单总和
                if($is_zorder == 1){
                    $sql = "SELECT SUM(order_amount) as 'amount' FROM 718shop_order where order_state =40 AND  is_zorder =1 AND pay_sn =".$order_info['pay_sn'];
                    $result =Model()->query($sql);
                    $sql1 = "SELECT SUM(order_amount) as 'amount' FROM 718shop_order where order_state =0 AND  is_zorder =1 AND  payment_time >0 AND pay_sn =".$order_info['pay_sn'];
                    $result1 =Model()->query($sql1);
                    if(!empty($result1)){
                        $q_money = $result[0]['amount'] + $result1[0]['amount'];
                    }else{
                        $q_money = $result[0]['amount'];
                    }
                   //echo $result1[0]['amount'];
                    //查询总单的订单金额
                    $sql2 = "SELECT order_amount FROM 718shop_order where is_zorder =0 AND order_sn =".$order_info['pay_sn'];
                    $result2 =Model()->query($sql2);
                    
                   //var_dump($result2[0]['order_amount']);
                    //只有确认收货的订单金额=总单金额，才发劵
                    if($q_money == $result2[0]['order_amount']){
                        $amount = $result[0]['order_amount'];
                    }else{
                       $amount = 0;
                    }
                }else{
                    $amount = $order_info['order_amount'];
                }
                $model_voucher = Model('voucher');
                $result=$model_voucher->mangzeng_voucher($order_info['buyer_id'], $order_info['store_id'], $amount);
                
                 //添加会员积分
                Model('points')->savePointsLog('order',array('pl_memberid'=>$order_info['buyer_id'],'pl_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
                //添加会员经验值
                Model('exppoints')->saveExppointsLog('order',array('exp_memberid'=>$order_info['buyer_id'],'exp_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
                //邀请人获得返利积分 
                $model_member = Model('member');
                $inviter_id = $model_member->table('member')->getfby_member_id($member_id,'inviter_id');
                $inviter_name = $model_member->table('member')->getfby_member_id($inviter_id,'member_name');
                $rebate_amount = ceil(0.01 * $order_info['order_amount'] * $GLOBALS['setting_config']['points_rebate']);
                Model('points')->savePointsLog('rebate',array('pl_memberid'=>$inviter_id,'pl_membername'=>$inviter_name,'pl_points'=>$rebate_amount,'member_id'=>$member_id),true);

                echo $this->returnMsg(10001, '此订单扫码核销成功!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                $logdata['code']  = '10001';
                $this->insert_saomalog($logdata);
                //裂变送券
                $result=$model_voucher->liebian_voucher($order_info['buyer_id'], $order_info['store_id']);
                exit;
            }else{
                 echo $this->returnMsg(10006, '此订单扫码核销异常!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                 $logdata['code']  = '10006';
                 $this->insert_saomalog($logdata);
                 exit;
            }
        }
        
    }
     /* 健身房一单多次扫码核销
    */
    public function sport_hexiao($order_sn,$member_id,$type){
        //获取扫码订单表列表
        $model_order = Model('order');
        $order_info = $model_order->table('order')->where(array('order_sn'=>$order_sn))->find();
        $logdata['order_id'] = $order_info['order_id'];
        $logdata['user_id'] = $member_id;
        $logdata['type'] = 3;
         if(empty($order_info)){
             echo $this->returnMsg(10003, '此订单无数据!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
             $logdata['code']  = '10003';
             $this->insert_saomalog($logdata);
             exit;
        }
        if($order_info['storage_id'] != 23){
            echo $this->returnMsg(10002, '无效码!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10002';
            $this->insert_saomalog($logdata);
            exit;
        }
        if($order_info['hexiao_time'] != 0 || $order_info['order_state'] == 40){
            echo $this->returnMsg(10004, '此订单为已收货单或已核销单，不能进行二次扫码核销!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10004';
            $this->insert_saomalog($logdata);
            exit;
        }
        if( $order_info['order_state'] == 0){
            echo $this->returnMsg(10008, '此订单为已取消，不能进行入库操作!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
             $logdata['code']  = '10008';
             $a = $this->insert_saomalog($logdata);
            exit;
        }

        /*if($order_info['lock_state'] != 0  ){
            echo $this->returnMsg(10005, '此订单被锁定，不能进行扫码核销!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10005';
            $this->insert_saomalog($logdata);
            exit;
        }*/
        $update_array = array();
        if(!empty($order_info['jin_chu'])){
            if($order_info['type'] != $type){
                echo $this->returnMsg(10009, '无效码!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                $logdata['code']  = '10009';
                $this->insert_saomalog($logdata);
                exit;
            }else{
                $now = date('Y-m-d',time());
                $first_sao = date("Y-m-d",intval($order_info['jin_time']));
                if($order_info['jin_chu'] < 3 && $now == $first_sao ){
                    $update_array['jin_chu'] = $order_info['jin_chu']+1;
                    $update_array['order_state'] =30;
                    $update_array['delay_time'] = time();

                }else{
                    //核销
                    $update_array['hexiao_time']   = time();
                    $update_array['finnshed_time']   = time();
                    $update_array['hexiao_user']   = $member_id;
                    $update_array['hexiao_from']   = 3;
                    $update_array['order_state']   = 40;
                    $result = $model_order->table('order')->where(array('order_sn'=>$order_sn))->update($update_array);
                    if ($result){
                        //插入order_log表
                        $log_data_order['order_id'] = $order_info['order_id'];
                        $log_data_order['log_msg'] = '线上小程序健身房扫码核销';
                        $log_data_order['log_time'] = time();
                        $log_data_order['log_role'] = '线上小程序';
                        $log_data_order['log_user'] = $member_id;
                        $log_data_order['log_orderstate'] = 40;
                   
                        Model()->table('order_log')->insert($log_data_order);

                        $logdata['code']  = '10001';
                        $this->insert_saomalog($logdata);
                        $model_voucher = Model('voucher');
                        $result=$model_voucher->mangzeng_voucher($order_info['buyer_id'], $order_info['store_id'], $order_info['order_amount']);
            
                         //添加会员积分
                        Model('points')->savePointsLog('order',array('pl_memberid'=>$order_info['buyer_id'],'pl_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
                        //添加会员经验值
                        Model('exppoints')->saveExppointsLog('order',array('exp_memberid'=>$order_info['buyer_id'],'exp_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
                        //邀请人获得返利积分 
                        $model_member = Model('member');
                        $inviter_id = $model_member->table('member')->getfby_member_id($member_id,'inviter_id');
                        $inviter_name = $model_member->table('member')->getfby_member_id($inviter_id,'member_name');
                        $rebate_amount = ceil(0.01 * $order_info['order_amount'] * $GLOBALS['setting_config']['points_rebate']);
                        Model('points')->savePointsLog('rebate',array('pl_memberid'=>$inviter_id,'pl_membername'=>$inviter_name,'pl_points'=>$rebate_amount,'member_id'=>$member_id),true);

                        //裂变送券
                        $result=$model_voucher->liebian_voucher($order_info['buyer_id'], $order_info['store_id']);
                        echo $this->returnMsg(10010, '此订单已核销!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                        exit;
                    }else{
                         echo $this->returnMsg(10006, '此订单扫码核销异常!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                         $logdata['code']  = '10006';
                         $this->insert_saomalog($logdata);
                         exit;
                    }
                }
            }
        }else{
            $update_array['type'] = $type;
            $update_array['jin_chu'] = 1;
            $update_array['delay_time'] = time();
            $update_array['jin_time'] =time();
            $update_array['order_state'] =30;
                
        }
        $result = $model_order->table('order')->where(array('order_sn'=>$order_sn))->update($update_array);
        if ($result){
            echo $this->returnMsg(10001, '此订单扫码成功!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
        }
        
    }
     /* 健身房扫码核销
    */
    public function sport_hexiao1($order_sn,$member_id,$type){

        //获取扫码订单表列表
        $model_order = Model('order');
        $order_info = $model_order->table('order')->where(array('order_sn'=>$order_sn))->find();
        $logdata['order_id'] = $order_info['order_id'];
        $logdata['user_id'] = $member_id;
        $logdata['type'] = 2;
        if(empty($order_info)){
             echo $this->returnMsg(10003, '此订单无数据!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
             $logdata['code']  = '10003';
             $this->insert_saomalog($logdata);
             exit;
        }
        if($order_info['storage_id'] != 23){
            echo $this->returnMsg(10002, '无效码!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10002';
            $this->insert_saomalog($logdata);
            exit;
        }
        if($order_info['hexiao_time'] != 0 || $order_info['order_state'] == 40){
            echo $this->returnMsg(10004, '此订单为已收货单或已核销单，不能进行二次扫码核销!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10004';
            $this->insert_saomalog($logdata);
            exit;
        }
        if( $order_info['order_state'] == 0){
            echo $this->returnMsg(10008, '此订单为已取消，不能进行入库操作!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
             $logdata['code']  = '10008';
             $a = $this->insert_saomalog($logdata);
            exit;
        }
        /*if($order_info['lock_state'] != 0  ){
            echo $this->returnMsg(10005, '此订单被锁定，不能进行扫码核销!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10005';
            $this->insert_saomalog($logdata);
            exit;
        }*/
        
        $update_array = array();
        $update_array['hexiao_time']   = time();
        $update_array['finnshed_time']   = time();
        $update_array['hexiao_user']   = $member_id;
        $update_array['hexiao_from']   = 3;
        $update_array['order_state']   = 40;
        $result = $model_order->table('order')->where(array('order_sn'=>$order_sn))->update($update_array);
        if ($result){
            $model_voucher = Model('voucher');
            $result=$model_voucher->mangzeng_voucher($order_info['buyer_id'], $order_info['store_id'], $order_info['order_amount']);
            
             //添加会员积分
            Model('points')->savePointsLog('order',array('pl_memberid'=>$order_info['buyer_id'],'pl_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
            //添加会员经验值
            Model('exppoints')->saveExppointsLog('order',array('exp_memberid'=>$order_info['buyer_id'],'exp_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
            //邀请人获得返利积分 
            $model_member = Model('member');
            $inviter_id = $model_member->table('member')->getfby_member_id($member_id,'inviter_id');
            $inviter_name = $model_member->table('member')->getfby_member_id($inviter_id,'member_name');
            $rebate_amount = ceil(0.01 * $order_info['order_amount'] * $GLOBALS['setting_config']['points_rebate']);
            Model('points')->savePointsLog('rebate',array('pl_memberid'=>$inviter_id,'pl_membername'=>$inviter_name,'pl_points'=>$rebate_amount,'member_id'=>$member_id),true);

            echo $this->returnMsg(10001, '此订单扫码核销成功!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10001';
            $this->insert_saomalog($logdata);
            //裂变送券
            $result=$model_voucher->liebian_voucher($order_info['buyer_id'], $order_info['store_id']);
            exit;
        }else{
             echo $this->returnMsg(10006, '此订单扫码核销异常!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
             $logdata['code']  = '10006';
             $this->insert_saomalog($logdata);
             exit;
        }
        
    }
    /* 扫码入库
    */
    public function ruku1Op(){

    	// sleep(50);
        $member_id = intval($_GET['member_id']);
        $order_sn = $_GET['order_sn'];
         $model_order = Model('order');
        //获取扫码订单表列表
        sleep(2);
        $order_info = $model_order->table('order')->field('order_id,order_state,order_sn,buyer_id')->where(array('order_sn'=>$order_sn))->find();
        $model_member = Model('member');
        $model_cw = Model('cw');
        $member_info = $model_member->table('member')->field('is_store')->where(array('member_id'=>$member_id))->find();
        $logdata['order_id'] = $order_info['order_id'];
        $logdata['user_id'] = $member_id;
        $logdata['type'] = 1;
        if($member_info['is_store'] != 2){
            echo $this->returnMsg(10002, '此用户非商户!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
             $logdata['code']  = '10002';
             $this->insert_saomalog($logdata);
            exit;
        }
       
        if(empty($order_info)){
             echo $this->returnMsg(10003, '此订单无数据!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
              $logdata['code']  = '10003';
             $this->insert_saomalog($logdata);
             exit;
        }
       /* if($order_info['lock_state'] != 0 ){
            echo $this->returnMsg(10005, '此订单被锁定，不能进行扫码核销!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10005';
             $this->insert_saomalog($logdata);
            exit;
        }*/
        if( $order_info['order_state'] == 40){
            echo $this->returnMsg(10004, '此订单为已收货单，不能进行入库操作!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
             $logdata['code']  = '10004';
             $a = $this->insert_saomalog($logdata);
            exit;
        }
        if( $order_info['order_state'] == 0){
            echo $this->returnMsg(10008, '此订单为已取消，不能进行入库操作!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
             $logdata['code']  = '10008';
             $a = $this->insert_saomalog($logdata);
            exit;
        }
        if($order_info['order_state'] == 30){
             echo $this->returnMsg(10007, '已此订单已到达自提点，不能进行二次入库!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
              $logdata['code']  = '10007';
             $this->insert_saomalog($logdata);
             exit;
        }
               
        $update_array = array();
        $update_array['ruku_time']   = time();
        $update_array['ruku_user']   = $member_id;
        $update_array['delay_time']   = time();
        $update_array['order_state']   = 30;
        $result = $model_order->table('order')->where(array('order_sn'=>$order_sn))->update($update_array);
        sleep(2);
        if ($result){
            //插入order_log表
            $log_data_order['order_id'] = $order_info['order_id'];
            $log_data_order['log_msg'] = '线上小程序扫码入库';
            $log_data_order['log_time'] = time();
            $log_data_order['log_role'] = '线上小程序';
            $log_data_order['log_user'] = $member_id;
            $log_data_order['log_orderstate'] = 30;
                   
            Model()->table('order_log')->insert($log_data_order);

             echo $this->returnMsg(10001, '此订单已到达自提点成功!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
             $logdata['code']  = '10001';
             $this->insert_saomalog($logdata);
			$order_id = $order_info['order_id'];
			$model_refund = Model('refund_return');
			$orderSn = $order_info['order_sn'];//Model('order')->getfby_order_id($order_id,'order_sn');
			//$refund_list = $model_refund->getRefundReturnList(array('order_id'=>$order_id,'refund_state'=>3),'','goods_id');
			//if(is_array($refund_list) && !empty($refund_list)){
			//	foreach ($refund_list as $goods_info){
			//		$goodsList[]['goodsCode'] = Model('goods')->getfby_goods_id($goods_info['goods_id'],'goods_serial');
			//	}
			//	$data['tenantId'] = 42;
			//	$data['orderSn'] = $orderSn;
			//	$data['orderStatus'] = 9;//9异常收货
			//	$data['goodsList'] = array_values($goodsList);
			//	$model_cw->cwAbnormalOrderOver($data);
			//}else{
				$data = [
					"tenantId" => 42,
					"orderSn" => $orderSn,
					"orderStatus" => "7"
				];
				$model_cw->cwOrderOver($data);
			//}
           
            $buyer_id = $order_info['buyer_id'];//获取token
            $order_id = $order_info['order_id'];//获取自提名称
            $buyer_info = $model_member->getMemberInfo(array('member_id' => $buyer_id),'member_wxopenid');
            $order_common_info = $model_order->getOrderCommonInfo(array('order_id' => $order_id),'reciver_ziti_id');
            $ziti_id = $order_common_info['reciver_ziti_id'];
            $ziti_info = Model('ziti_address')->getAddressInfo(array('address_id' => $ziti_id),'seller_name');
            //$seller_name = $ziti_info['seller_name'].'-取件码: '.$order_info['wms_sn'];
            // sleep(50);
            $seller_name = $ziti_info['seller_name'];
            //拼接提货柜收货码
            $code_info = Model('order_code')->field('ztg_name,code')->where(array('order_sn' => $order_sn))->find();
            if (!empty($code_info)) {
                $seller_name .= '-'.$code_info['ztg_name'].'-'.$code_info['code'];
            }
            
            $touser = $buyer_info['member_wxopenid'];
            //echo $touser."<br/>";
            include 'wxSubscribe.php';
            $wxSubscribe = new wxSubscribe();
            $output = $wxSubscribe->sendMessage($touser,$order_sn,$order_id,$seller_name);
            file_put_contents("/data/default/wzxd/log/wxSubscribe.send", $order_sn."  ".$touser."  ".$output.PHP_EOL,FILE_APPEND);
            exit;
        }else{
             echo $this->returnMsg(10006, '此订单入库异常!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
              $logdata['code']  = '10006';
             $this->insert_saomalog($logdata);
             exit;
        }
        
    }
    /**用户ID查询是否为商户
     */
    public function is_storeOp(){
        $member_info=Model()->table('member')->where(array('member_id' => $_GET['member_id']))->find();
        // sleep(50);
        if($member_info['is_store'] == 2){
            $message='商户号，可扫码核销';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$member_info);
            echo json_encode($res,320);
        }else{
            $message='普通买家';
            $res = array('code'=>'300' , 'message'=>$message,'data'=>$member_info);
            echo json_encode($res,320);
        }
    }
    /**
     * 添加扫码日志
     */
    public function insert_saomalog($logdata) {
        
       $data['order_id'] = $logdata['order_id'];
       $data['user_id'] = $logdata['user_id'];
       $data['sao_time'] = time();
       $data['type'] = $logdata['type'];
       $data['code'] = $logdata['code'];
       Model()->table('saoma_log')->insert($data);
    }
     /* WMS顺序号存储
    */
    public function wmssn1Op(){
       /* $a['onlineSn'] = '12232131';
        $a['orderList']['0']['orderSn'] = '6000000005207401';
        $a['orderList']['0']['wmsSn'] = '7401';
        $a['orderList']['1']['orderSn'] = '6000000005207501';
        $a['orderList']['1']['wmsSn'] = '7402';
        $a['orderList']['2']['orderSn'] = '6000000005207701';
        $a['orderList']['2']['wmsSn'] = '7403';*/
        $json_data = file_get_contents("php://input");
        $wms_array = json_decode($json_data,true);
        if(!empty($wms_array)){
             echo $this->returnMsg(100, '请求成功！', '');
        }else{
             echo $this->returnMsg(200, '请求异常！', '');
        }
       
        foreach ($wms_array['orderList'] as $key => $value) {
            $order_sn = $value['orderSn'];
            $wms_sn  = $value['orderNo'];
            $result=Model('order')->table('order')->where(array('order_sn'=>$order_sn))->update(array('wms_sn'=>$wms_sn));
            if(!$result){
                $order_info = Model('order')->table('order')->where(array('order_sn'=>$order_sn))->find();
                $log['order_id'] = $order_info['order_id'];
                $log['log_msg'] = '订单'.$wms_array['onlineSn'].'-'.$order_sn.'顺序号更新异常！';
                $log['log_time'] = time();
                $log['log_user'] = 'WMS';
                $log['log_orderstate'] =  $order_info['order_state'];  

                Model()->table('order_log')->insert($log);
            }
        }

        
    }
    /* WMS顺序号存储
    */
    public function wmssnOp(){
    	// sleep(50);
        ob_end_clean();
        ob_start();
        echo $this->returnMsg(100, '请求成功！', '');
        $size = ob_get_length();
        header("HTTP/1.1 200 OK");
        header("Content-Length:$size");
        header("Connection:close");
        header("Content-Type:application/json;charset=utf-8");
        ob_end_flush();
        if(ob_get_length()){
            ob_flush();
        }
        flush();
        if(function_exists("fastcgi_finish_request")){
            fastcgi_finish_request();
        }
        // sleep(1);
        ignore_user_abort(true);
        set_time_limit(300);
        $json_data = file_get_contents("php://input");
         $wms_array = json_decode($json_data,true);
            
        foreach ($wms_array['orderList'] as $key => $value) {
            $order_sn = $value['orderSn'];
            $wms_sn  = $value['orderNo'];
            $result=Model('order')->table('order')->where(array('order_sn'=>$order_sn))->update(array('wms_sn'=>$wms_sn));
            if(!$result){
                $order_info = Model('order')->table('order')->field('order_id,order_state')->where(array('order_sn'=>$order_sn))->find();
                $log['order_id'] = $order_info['order_id'];
                $log['log_msg'] = '订单'.$wms_array['onlineSn'].'-'.$order_sn.'顺序号更新异常！';
                $log['log_time'] = time();
                $log['log_user'] = 'WMS';
                $log['log_orderstate'] =  $order_info['order_state'];  

                Model()->table('order_log')->insert($log);
            }
        }
        
       
       
        
    }

    public function ruku2Op(){
        $member_id = intval($_GET['member_id']);
        $order_sn = $_GET['order_sn'];
        $model_order = Model('order');
        //获取扫码订单表列表
        $order_info = $model_order->table('order')->field('order_id,order_state,order_sn,buyer_id')->where(array('order_sn'=>$order_sn))->find();
        $model_member = Model('member');
        $member_info = $model_member->table('member')->field('is_store')->where(array('member_id'=>$member_id))->find();
        $logdata['order_id'] = $order_info['order_id'];
        $logdata['user_id'] = $member_id;
        $logdata['type'] = 1;
        if($member_info['is_store'] != 2){
            echo $this->returnMsg(10002, '此用户非商户!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10002';
            $this->insert_saomalog($logdata);
            exit;
        }
       
        if(empty($order_info)){
            echo $this->returnMsg(10003, '此订单无数据!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10003';
            $this->insert_saomalog($logdata);
            exit;
        }
        /* if($order_info['lock_state'] != 0 ){
            echo $this->returnMsg(10005, '此订单被锁定，不能进行扫码核销!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10005';
            $this->insert_saomalog($logdata);
            exit;
        }*/
        if( $order_info['order_state'] == 40){
            echo $this->returnMsg(10004, '此订单为已收货单，不能进行入库操作!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10004';
            $this->insert_saomalog($logdata);
            exit;
        }
        if( $order_info['order_state'] == 0){
            echo $this->returnMsg(10008, '此订单为已取消，不能进行入库操作!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10008';
            $this->insert_saomalog($logdata);
            exit;
        }
        if($order_info['order_state'] == 30){
            echo $this->returnMsg(10007, '已此订单已到达自提点，不能进行二次入库!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10007';
            $this->insert_saomalog($logdata);
            exit;
        }
               
        $update_array = array();
        $update_array['ruku_time']   = time();
        $update_array['ruku_user']   = $member_id;
        $update_array['delay_time']   = time();
        $update_array['order_state']   = 30;
        $result = $model_order->table('order')->where(array('order_sn'=>$order_sn))->update($update_array);
        if ($result){
            $this->send($order_info['order_id']);
            //插入order_log表
            $log_data_order['order_id'] = $order_info['order_id'];
            $log_data_order['log_msg'] = '线上小程序扫码入库';
            $log_data_order['log_time'] = time();
            $log_data_order['log_role'] = '线上小程序';
            $log_data_order['log_user'] = $member_id;
            $log_data_order['log_orderstate'] = 30;
                   
            Model()->table('order_log')->insert($log_data_order);

            echo $this->returnMsg(10001, '此订单已到达自提点成功!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10001';
            $this->insert_saomalog($logdata);
        }else{
            echo $this->returnMsg(10006, '此订单入库异常!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10006';
            $this->insert_saomalog($logdata);
            exit;
       }
    }
    //入库
    public function rukuOp(){
        $member_id = intval($_GET['member_id']);
        $order_sn = $_GET['order_sn'];
        if (isset($_GET['is_yun']) && $_GET['is_yun']==1) {
            $return =  $this->yun($order_sn,$member_id,$_GET['is_yun']);
            exit;
        }else{
            $model_order = Model('order');
            //获取扫码订单表列表
            $order_info = $model_order->table('order')->field('order_id,order_state,order_sn,buyer_id')->where(array('order_sn'=>$order_sn))->find();
            $model_member = Model('member');
            $member_info = $model_member->table('member')->field('is_store')->where(array('member_id'=>$member_id))->find();
            $logdata['order_id'] = $order_info['order_id'];
            $logdata['user_id'] = $member_id;
            $logdata['type'] = 1;
            if($member_info['is_store'] != 2){
                echo $this->returnMsg(10002, '此用户非商户!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                $logdata['code']  = '10002';
                $this->insert_saomalog($logdata);
                exit;
            }
       
            if(empty($order_info)){
                echo $this->returnMsg(10003, '此订单无数据!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                $logdata['code']  = '10003';
                $this->insert_saomalog($logdata);
                exit;
            }
            /* if($order_info['lock_state'] != 0 ){
                echo $this->returnMsg(10005, '此订单被锁定，不能进行扫码核销!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                $logdata['code']  = '10005';
                $this->insert_saomalog($logdata);
                exit;
            }*/
            if( $order_info['order_state'] == 40){
                echo $this->returnMsg(10004, '此订单为已收货单，不能进行入库操作!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                $logdata['code']  = '10004';
                $this->insert_saomalog($logdata);
                exit;
            }
            if( $order_info['order_state'] == 0){
                echo $this->returnMsg(10008, '此订单为已取消，不能进行入库操作!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                $logdata['code']  = '10008';
                $this->insert_saomalog($logdata);
                exit;
            }
            if($order_info['order_state'] == 30){
                echo $this->returnMsg(10007, '已此订单已到达自提点，不能进行二次入库!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                $logdata['code']  = '10007';
                $this->insert_saomalog($logdata);
                exit;
            }
                   
            $update_array = array();
            $update_array['ruku_time']   = time();
            $update_array['ruku_user']   = $member_id;
            $update_array['delay_time']   = time();
            $update_array['order_state']   = 30;
            $result = $model_order->table('order')->where(array('order_sn'=>$order_sn))->update($update_array);
            if ($result){
                $this->send($order_info['order_id']);
                //插入order_log表
                $log_data_order['order_id'] = $order_info['order_id'];
                $log_data_order['log_msg'] = '线上小程序扫码入库';
                $log_data_order['log_time'] = time();
                $log_data_order['log_role'] = '线上小程序';
                $log_data_order['log_user'] = $member_id;
                $log_data_order['log_orderstate'] = 30;
                       
                Model()->table('order_log')->insert($log_data_order);

                echo $this->returnMsg(10001, '此订单已到达自提点成功!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                $logdata['code']  = '10001';
                $this->insert_saomalog($logdata);
            }else{
                echo $this->returnMsg(10006, '此订单入库异常!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
                $logdata['code']  = '10006';
                $this->insert_saomalog($logdata);
                exit;
           }
       }
    }

    //仓库扫码发货
    public function yun($order_sn,$member_id,$is_yun){
        $model_order = Model('order');
        //获取扫码订单表列表
        $order_info = $model_order->table('order')->field('order_id,order_state,order_sn,buyer_id')->where(array('order_sn'=>$order_sn))->find();
        $model_member = Model('member');
        $member_info = $model_member->table('member')->field('is_store')->where(array('member_id'=>$member_id))->find();
        $logdata['order_id'] = $order_info['order_id'];
        $logdata['user_id'] = $member_id;
        $logdata['type'] = 7;//仓库扫码运输中
        if( $order_info['order_state'] > 20){
            echo $this->returnMsg(10004, '此订单为已发货或是待取货，不能进行扫码运输操作!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10004';
            $this->insert_saomalog($logdata);
            exit;
        }

        $update_array = array();
        $update_array['cang_id']   = $member_id;
        $update_array['cang_time']   = time();
        $update_array['order_state']   = 25;
        $result = $model_order->table('order')->where(array('order_sn'=>$order_sn))->update($update_array);
        if ($result){
            //插入order_log表
            $log_data_order['order_id'] = $order_info['order_id'];
            $log_data_order['log_msg'] = '仓库扫码发货';
            $log_data_order['log_time'] = time();
            $log_data_order['log_role'] = '线上小程序';
            $log_data_order['log_user'] = $member_id;
            $log_data_order['log_orderstate'] = 25;
                       
            Model()->table('order_log')->insert($log_data_order);

            echo $this->returnMsg(10001, '此订单设置成运输中状态!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10001';
            $this->insert_saomalog($logdata);
        }else{
            echo $this->returnMsg(10006, '此订单仓库发货异常!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
            $logdata['code']  = '10006';
            $this->insert_saomalog($logdata);
            exit;
        }
       
    }
    /**
     * mq发送数据
     */
    private function send($data){

        $rabbitMQ = new RabbitMQ();

//        $connection = $rabbitMQ->connection('218.28.14.169', 5672, 'wzxd', '123456');
        $connection = $rabbitMQ->connection('10.10.11.141', 5672, 'wzxd', 'WZXDRMQpython~XX2');

        if($connection){

            $rabbitMQ->sendTopic($connection,$data,'ruku_topic_exchange','ruku.#');

            $rabbitMQ->close($connection);

        }

    }
   
}
