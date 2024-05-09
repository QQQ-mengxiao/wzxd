<?php
/**
 * 实物订单行为
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class orderLogic {
     /**
     * 扶贫API取消订单
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param string $msg 操作备注
     * @param boolean $if_update_account 是否变更账户金额
     * @param boolean $if_queue 是否使用队列
     * @return array
     */
    public function changeOrderStateApiCancel($order_info, $role, $user = '', $msg = '', $if_update_account = true, $if_quque = true) {
        try {
            $return = true;
            $model_order = Model('order');
            $model_order->beginTransaction();
            $order_id = $order_info['order_id'];

            //库存销量变更
            $goods_list = $model_order->getOrderGoodsList(array('order_id'=>$order_id));
            $data = array();
            foreach ($goods_list as $goods) {
                $data[$goods['goods_id']] = $goods['goods_num'];
            }
            if ($if_quque) {
                QueueClient::push('cancelOrderUpdateStorage', $data);
            } else {
                Logic('queue')->cancelOrderUpdateStorage($data);
            }
            //在取消订单和待发货的状态下返回代金券
            // if($order_info['order_state']==10||$order_info['order_state']==20){
                $list=$order_info['extend_order_common']['voucher_code']; //这里的编码是已经反序列化过的编码
                if(!empty($list)){
                    foreach ($list as $key => $v){
                        $k=Model('voucher')->table('voucher')->where(array('voucher_code'=>$v['voucher_code']))->select();
                        foreach($k as $voucher){
                            if($voucher['voucher_end_date']>=time()){
                                $update =Model('voucher')->editVoucher(array('voucher_state'=>1),array('voucher_id'=>$voucher['voucher_id']));
                            }
                            else{
                                $update =Model('voucher')->editVoucher(array('voucher_state'=>3),array('voucher_id'=>$voucher['voucher_id']));
                            }
                        }
                    }
                }
            // }
            if ($if_update_account) {
                $model_pd = Model('predeposit');
                //解冻充值卡
                $rcb_amount = floatval($order_info['rcb_amount']);
                if ($rcb_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $rcb_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changeRcb('order_cancel',$data_pd);
                }
                
                //解冻预存款
                $pd_amount = floatval($order_info['pd_amount']);
                if ($pd_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $pd_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changePd('order_cancel',$data_pd);
                }                
            }

            //更新订单信息
            $update_order = array('order_state' => ORDER_STATE_CANCEL, 'pd_amount' => 0);
            $update = $model_order->editOrder($update_order,array('order_id'=>$order_id));
            if (!$update) {
                $return = false;
                throw new Exception('保存失败');
            }

            //添加订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_msg'] = '取消了订单';
            $data['log_user'] = $user;
            if ($msg) {
                $data['log_msg'] .= ' ( '.$msg.' )';
            }
            $data['log_orderstate'] = ORDER_STATE_CANCEL;
            $model_order->addOrderLog($data);
            $model_order->commit();

        } catch (Exception $e) {
            $this->rollback();
            $return = false;
        }
        return $return;
    }
    /**
     * 取消订单
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param string $msg 操作备注
     * @param boolean $if_update_account 是否变更账户金额
     * @param boolean $if_queue 是否使用队列
     * @return array
     */
    public function changeOrderStateCancel($order_info, $role, $user = '', $msg = '', $if_update_account = true, $if_quque = true) {
        try {
            $model_order = Model('order');
            $model_order->beginTransaction();
            $order_id = $order_info['order_id'];
            if($order_info['order_state']==10){
                 //库存销量变更
                $goods_list = $model_order->getOrderGoodsList(array('order_id'=>$order_id));
                $data = array();
                foreach ($goods_list as $goods) {
                    $data[$goods['goods_id']] = $goods['goods_num'];
                }
                if ($if_quque) {
                    QueueClient::push('cancelOrderUpdateStorage', $data);
                } else {
                    Logic('queue')->cancelOrderUpdateStorage($data);
                }
            }
            //在取消订单和待发货的状态下返回代金券
            if($order_info['order_state']==10||$order_info['order_state']==20){
                $list=$order_info['extend_order_common']['voucher_code']; //这里的编码是已经反序列化过的编码
                if(!empty($list)){
                    foreach ($list as $key => $v){
                        $k=Model('voucher')->table('voucher')->where(array('voucher_code'=>$v['voucher_code']))->select();
                        foreach($k as $voucher){
                            if($voucher['voucher_end_date']>=time()){
                                $update =Model('voucher')->editVoucher(array('voucher_state'=>1),array('voucher_id'=>$voucher['voucher_id']));
                            }
                            else{
                                $update =Model('voucher')->editVoucher(array('voucher_state'=>3),array('voucher_id'=>$voucher['voucher_id']));
                            }
                        }
                    }
                }
            }
            if ($if_update_account) {
                $model_pd = Model('predeposit');
                //解冻充值卡
                $rcb_amount = floatval($order_info['rcb_amount']);
                if ($rcb_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $rcb_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changeRcb('order_cancel',$data_pd);
                }
                
                //解冻预存款
                $pd_amount = floatval($order_info['pd_amount']);
                if ($pd_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $pd_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changePd('order_cancel',$data_pd);
                }                
            }

            //更新订单信息
            $update_order = array('order_state' => ORDER_STATE_CANCEL, 'pd_amount' => 0);
            $update = $model_order->editOrder($update_order,array('order_id'=>$order_id));
            if (!$update) {
                throw new Exception('保存失败');
            }

            //添加订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_msg'] = '取消了订单';
            $data['log_user'] = $user;
            if ($msg) {
                $data['log_msg'] .= ' ( '.$msg.' )';
            }
            $data['log_orderstate'] = ORDER_STATE_CANCEL;
            $model_order->addOrderLog($data);
            $model_order->commit();

            return callback(true,'操作成功');

        } catch (Exception $e) {
            $this->rollback();
            return callback(false,'操作失败');
        }
    }

    /**
     * 收货
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param string $msg 操作备注
     * @return array
     */
    public function changeOrderStateApiReceive($order_info, $role, $user = '', $msg = '') {
        try {
            $return = true;
            $member_id=$order_info['buyer_id'];
            $order_id = $order_info['order_id'];
            $model_order = Model('order');

            //更新订单状态
            $update_order = array();
            $update_order['finnshed_time'] = TIMESTAMP;
            $update_order['order_state'] = ORDER_STATE_SUCCESS;
            $update = $model_order->editOrder($update_order,array('order_id'=>$order_id));
            if (!$update) {
                $return = false;
                throw new Exception('保存失败');
            }

            //添加订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] =  $role;
            $data['log_msg'] = '签收了货物';
            $data['log_user'] = $user;
            if ($msg) {
                $data['log_msg'] .= ' ( '.$msg.' )';
            }
            $data['log_orderstate'] = ORDER_STATE_SUCCESS;
            $model_order->addOrderLog($data);

            //添加会员积分
            if (C('points_isuse') == 1){
                Model('points')->savePointsLog('order',array('pl_memberid'=>$order_info['buyer_id'],'pl_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
            }
            //添加会员经验值
            Model('exppoints')->saveExppointsLog('order',array('exp_memberid'=>$order_info['buyer_id'],'exp_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
            //邀请人获得返利积分 
            $model_member = Model('member');
            $inviter_id = $model_member->table('member')->getfby_member_id($member_id,'inviter_id');
            $inviter_name = $model_member->table('member')->getfby_member_id($inviter_id,'member_name');
            $rebate_amount = ceil(0.01 * $order_info['order_amount'] * $GLOBALS['setting_config']['points_rebate']);
            Model('points')->savePointsLog('rebate',array('pl_memberid'=>$inviter_id,'pl_membername'=>$inviter_name,'pl_points'=>$rebate_amount,'member_id'=>$member_id),true);
        } catch (Exception $e) {
            $return = false;
        }
        return $return;
    }
    /**
     * 收货
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param string $msg 操作备注
     * @return array
     */
    public function changeOrderStateReceive($order_info, $role, $user = '', $msg = '') {
        try {
            $member_id=$order_info['buyer_id'];
            $order_id = $order_info['order_id'];
            $model_order = Model('order');
            //$model_cw = Model('cw');

            //更新订单状态
            $update_order = array();
            $update_order['finnshed_time'] = TIMESTAMP;
            $update_order['order_state'] = ORDER_STATE_SUCCESS;
            $update = $model_order->editOrder($update_order,array('order_id'=>$order_id));
            if (!$update) {
                throw new Exception('保存失败');
            }
			$order_sn = Model()->table('order')->getfby_order_id($order_info['order_id'],'order_sn');
			//$data = [
				//"tenantId" => 42,
				//"orderSn" => $order_sn,
				//"orderStatus" => "3"
			//];
            //$model_cw->cwOrderComplete($data);
            //$data['tenantId'] = 42;
            //$data['orderSn'] = Model()->table('order')->getfby_order_id($order_info['order_id'],'order_sn');
            //$data['orderStatus'] = 3;
            //$model_cw->cwOrderComplete($data);
            //查询分单中确认收货的订单总和
            $is_zorder = Model()->table('order')->getfby_order_id($order_info['order_id'],'is_zorder');
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
                        $amount = $q_money;
                    }else{
                        $amount = 0;
                    }
                }else{
                    $amount = $order_info['order_amount'];
                }
             //确认收货自动发放代金券
             $model_voucher = Model('voucher');
            $result=$model_voucher->mangzeng_voucher($order_info['buyer_id'], $order_info['store_id'], $amount);
            $model_voucher->liebian_voucher($order_info['buyer_id'], $order_info['store_id']);
            //添加订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = 'buyer';
            $data['log_msg'] = '签收了货物';
            $data['log_user'] = $user;
            if ($msg) {
                $data['log_msg'] .= ' ( '.$msg.' )';
            }
            $data['log_orderstate'] = ORDER_STATE_SUCCESS;
            $model_order->addOrderLog($data);

            //添加会员积分
            if (C('points_isuse') == 1){
                Model('points')->savePointsLog('order',array('pl_memberid'=>$order_info['buyer_id'],'pl_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
            }
            //添加会员经验值
            Model('exppoints')->saveExppointsLog('order',array('exp_memberid'=>$order_info['buyer_id'],'exp_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
			//邀请人获得返利积分 
			$model_member = Model('member');
			$inviter_id = $model_member->table('member')->getfby_member_id($member_id,'inviter_id');
			$inviter_name = $model_member->table('member')->getfby_member_id($inviter_id,'member_name');
			$rebate_amount = ceil(0.01 * $order_info['order_amount'] * $GLOBALS['setting_config']['points_rebate']);
			Model('points')->savePointsLog('rebate',array('pl_memberid'=>$inviter_id,'pl_membername'=>$inviter_name,'pl_points'=>$rebate_amount,'member_id'=>$member_id),true);

            return callback(true,'操作成功');
        } catch (Exception $e) {
            return callback(false,'操作失败');
        }
    }
 /**
     * 转待发货
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param float $price 运费
     * @return array
     */
    public function changeOrderStateshouhou($order_info, $role, $user = '', $msg = '') {
        try {
            $member_id=$order_info['buyer_id'];
            $order_id = $order_info['order_id'];
            $model_order = Model('order');

            //更新订单状态
            $update_order = array();
            // $update_order['finnshed_time'] = TIMESTAMP;
            $update_order['order_state'] = ORDER_STATE_PAY;
            $update = $model_order->editOrder($update_order,array('order_id'=>$order_id));
            if (!$update) {
                throw new Exception('保存失败');
            }

            //添加订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_msg'] = '已发货转待发货货物';
            $data['log_user'] = $user;
            if ($msg) {
                $data['log_msg'] .= ' ( '.$msg.' )';
            }
            $data['log_orderstate'] = ORDER_STATE_PAY;
            $model_order->addOrderLog($data);

            return callback(true,'操作成功');
        } catch (Exception $e) {
            return callback(false,'操作失败');
        }
    }
    /**
     * 更改运费
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param float $price 运费
     * @return array
     */
    public function changeOrderShipPrice($order_info, $role, $user = '', $price) {
        try {

            $order_id = $order_info['order_id'];
            $model_order = Model('order');

            $data = array();
            $data['shipping_fee'] = abs(floatval($price));
            $data['order_amount'] = array('exp','goods_amount+'.$data['shipping_fee']);
            $update = $model_order->editOrder($data,array('order_id'=>$order_id));
            if (!$update) {
                throw new Exception('保存失败');
            }
            //记录订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_user'] = $user;
            $data['log_msg'] = '修改了运费'.'( '.$price.' )';;
            $data['log_orderstate'] = $order_info['payment_code'] == 'offline' ? ORDER_STATE_PAY : ORDER_STATE_NEW;
            $model_order->addOrderLog($data);
            return callback(true,'操作成功');
        } catch (Exception $e) {
            return callback(false,'操作失败');
        }
    }
    /**
     * 更改运费
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param float $price 运费
     * @return array
     */
    public function changeOrderSpayPrice($order_info, $role, $user = '', $price) {
        try {

            $order_id = $order_info['order_id'];
            $model_order = Model('order');

            $data = array();
            $data['goods_amount'] = abs(floatval($price));
            $data['order_amount'] = array('exp','shipping_fee+'.$data['goods_amount']);
            $update = $model_order->editOrder($data,array('order_id'=>$order_id));
            if (!$update) {
                throw new Exception('保存失败');
            }
            //记录订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_user'] = $user;
            $data['log_msg'] = '修改了运费'.'( '.$price.' )';;
            $data['log_orderstate'] = $order_info['payment_code'] == 'offline' ? ORDER_STATE_PAY : ORDER_STATE_NEW;
            $model_order->addOrderLog($data);
            return callback(true,'操作成功');
        } catch (Exception $e) {
            return callback(false,'操作失败');
        }
    }
    /**
     * 回收站操作（放入回收站、还原、永久删除）
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $state_type 操作类型
     * @return array
     */
    public function changeOrderStateApiRecycle($order_info, $role, $state_type) {
        $return = true;
        $order_id = $order_info['order_id'];
        $model_order = Model('order');
        //更新订单删除状态
        $state = str_replace(array('delete','drop','restore'), array(ORDER_DEL_STATE_DELETE,ORDER_DEL_STATE_DROP,ORDER_DEL_STATE_DEFAULT), $state_type);
        $update = $model_order->editOrder(array('delete_state'=>$state),array('order_id'=>$order_id));
        if (!$update) {
            $return = false;
        } 
        return $return;
    }

    /**
     * 回收站操作（放入回收站、还原、永久删除）
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $state_type 操作类型
     * @return array
     */
    public function changeOrderStateRecycle($order_info, $role, $state_type) {
        $order_id = $order_info['order_id'];
        $model_order = Model('order');
        //更新订单删除状态
        $state = str_replace(array('delete','drop','restore'), array(ORDER_DEL_STATE_DELETE,ORDER_DEL_STATE_DROP,ORDER_DEL_STATE_DEFAULT), $state_type);
        $update = $model_order->editOrder(array('delete_state'=>$state),array('order_id'=>$order_id));
        if (!$update) {
            return callback(false,'操作失败');
        } else {
            return callback(true,'操作成功');
        }
    }

    /**
     * 发货
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @return array
     */
    public function changeOrderSend($order_info, $role, $user = '', $post = array()) {
        $order_id = $order_info['order_id'];
        $model_order = Model('order');
        $model_cw = Model('cw');
		try {
            $model_order->beginTransaction();
            $data = array();
            $data['reciver_name'] = $post['reciver_name'];
            $data['reciver_info'] = $post['reciver_info'];
            $data['deliver_explain'] = $post['deliver_explain'];
            $data['daddress_id'] = intval($post['daddress_id']);
            $data['shipping_express_id'] = intval($post['shipping_express_id']);
            $data['shipping_time'] = TIMESTAMP;

            $condition = array();
            $condition['order_id'] = $order_id;
            $condition['store_id'] = $_SESSION['store_id'];
            $update = $model_order->editOrderCommon($data,$condition);
            if (!$update) {
                throw new Exception('操作失败');
            }

            $data = array();
            $data['shipping_code']  = $post['shipping_code'];
            $data['order_state'] = ORDER_STATE_SEND;
            $data['delay_time'] = TIMESTAMP;
            $data['ruku_time'] = TIMESTAMP;
            $update = $model_order->editOrder($data,$condition);
            if (!$update) {
                throw new Exception('操作失败');
            }
            $data = [
                "tenantId" => 42,
                "orderSn" => $order_info['order_sn'],
                "orderStatus" => "7"
            ];
            $model_cw->cwOrderOver($data);
            $model_order->commit();
		} catch (Exception $e) {
		    $model_order->rollback();
		    return callback(false,$e->getMessage());
		}

		//更新表发货信息
		if ($post['shipping_express_id'] && $order_info['extend_order_common']['reciver_info']['dlyp']) {
		    $data = array();
		    $data['shipping_code'] = $post['shipping_code'];
		    $data['order_sn'] = $order_info['order_sn'];
		    $express_info = Model('express')->getExpressInfo(intval($post['shipping_express_id']));
		    $data['express_code'] = $express_info['e_code'];
		    $data['express_name'] = $express_info['e_name'];
		    Model('delivery_order')->editDeliveryOrder($data,array('order_id' => $order_info['order_id']));
		}

		//添加订单日志
		$data = array();
		$data['order_id'] = intval($_GET['order_id']);
		$data['log_role'] = 'seller';
		$data['log_user'] = $_SESSION['member_name'];
		$data['log_msg'] = '发出了货物 ( 编辑了发货信息 )';
		$data['log_orderstate'] = ORDER_STATE_SEND;
		$model_order->addOrderLog($data);

		// 发送买家消息
        $param = array();
        $param['code'] = 'order_deliver_success';
        $param['member_id'] = $order_info['buyer_id'];
        $param['param'] = array(
            'order_sn' => $order_info['order_sn'],
            'order_url' => urlShop('member_order', 'show_order', array('order_id' => $order_id))
        );
        QueueClient::push('sendMemberMsg', $param);

        return callback(true,'操作成功');
    }

    /**
     * 发货（邮寄）状态20->25
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @return array
     */
    public function changeOrderPost($order_info, $post = array()) {
        $order_id = $order_info['order_id'];
        $model_order = Model('order');
        try {
            $model_order->beginTransaction();
            $data = array();
            $data['reciver_name'] = $post['reciver_name'];
            $data['reciver_info'] = $post['reciver_info'];
            $data['deliver_explain'] = $post['deliver_explain'];
            $data['daddress_id'] = intval($post['daddress_id']);
            $data['shipping_express_id'] = intval($post['shipping_express_id']);
            $data['shipping_time'] = TIMESTAMP;

            $condition = array();
            $condition['order_id'] = $order_id;
            $condition['store_id'] = $_SESSION['store_id'];
            $update = $model_order->editOrderCommon($data,$condition);
            if (!$update) {
                throw new Exception('操作失败【订单公共表编辑失败】');
            }

            $data = array();
            $data['shipping_code']  = $post['shipping_code'];
            $data['order_state'] = 25;//ORDER_STATE_SEND;
            $data['delay_time'] = TIMESTAMP;
            // $data['ruku_time'] = TIMESTAMP;
            $update = $model_order->editOrder($data,$condition);
            if (!$update) {
                throw new Exception('操作失败【订单表编辑失败】');
            }

            //邮寄订单同步云仓//
            $data = [];
            $data ['companyCode'] = Model()->table('express')->getfby_id(intval($post['shipping_express_id']),'e_code');
            $data ['companyName'] = Model()->table('express')->getfby_id(intval($post['shipping_express_id']),'e_name');
            $data ['deliverExplain'] = $post['deliver_explain'];
            $data ['orderSn'] = $order_info['order_sn'];
            $data ['shippingCode'] = $post['shipping_code'];

            $cw_sign = Model()->table('order_goods')->where(['order_id'=>$order_id,'is_cw'=>1])->select();
            if($cw_sign){
                $result = Model('cw')->synchoronousShipment($data);
                if(!$result || json_decode($result,true)['code']!=0){
                    throw new Exception('操作失败【云仓同步失败】');
                }
            }
            //邮寄订单同步云仓//

            $model_order->commit();

        } catch (Exception $e) {
            $model_order->rollback();
            return callback(false,$e->getMessage());
        }

        //更新表发货信息
        // if ($post['shipping_express_id'] && $order_info['extend_order_common']['reciver_info']['dlyp']) {
        $data = array();
        $data['shipping_code'] = $post['shipping_code'];
        $data['order_sn'] = $order_info['order_sn'];
        $express_info = Model('express')->getExpressInfo(intval($post['shipping_express_id']));
        $data['express_code'] = $express_info['e_code'];
        $data['express_name'] = $express_info['e_name'];
        Model('delivery_order')->editDeliveryOrder($data,array('order_id' => $order_info['order_id']));
        // }

        //添加订单日志
        $data = array();
        $data['order_id'] = intval($order_id);
        $data['log_role'] = 'seller';
        $data['log_user'] = $_SESSION['member_name'];
        $data['log_msg'] = '发出了货物 ( 编辑了发货信息 )';
        $data['log_orderstate'] = 25;//ORDER_STATE_SEND;
        $model_order->addOrderLog($data);

        //发送消息队列
        $this->send(intval($order_id));

        return callback(true,'操作成功');
    }

    /**
     * 发货（邮寄）状态20->25
     * @param array $order_info
     * @param array $post
     * @return int
     */
    public function changeOrderPostByCW($order_info, $post = array()) {

        $order_id = $order_info['order_id'];
        $model_order = Model('order');
        $condition['order_id'] = $order_id;

        try {
            $model_order->beginTransaction();

            $order_common_data = [
                'deliver_explain'       => $post['deliver_explain'],
                'shipping_express_id'   => intval($post['shipping_express_id']),
                'shipping_time'         => TIMESTAMP,
            ];

            $order_common_update = $model_order->editOrderCommon($order_common_data,$condition);
            if (!$order_common_update) {
                return -1;
            }

            $order_data = [
                'shipping_code'         => $post['shipping_code'],
                'order_state'           => 25,
                'delay_time'            => TIMESTAMP,
            ];
            $order_update = $model_order->editOrder($order_data,$condition);
            if (!$order_update) {
                return -2;
            }
            $model_order->commit();
        } catch (Exception $e) {
            $model_order->rollback();
            return -3;
        }

        //更新表发货信息
        $deliver_data = [
            'shipping_code'             => $post['shipping_code'],
            'order_sn'                  => $order_info['order_sn'],
            'express_code'              => $post['e_code'],
            'express_name'              => $post['e_name'],
        ];
        Model('delivery_order')->editDeliveryOrder($deliver_data,$condition);

        //添加订单日志
        $order_log_data = [
            'order_id'                  => intval($order_id),
            'log_role'                  => 'cw',
            'log_user'                  => 'sys',
            'log_msg'                   => '【仓库】发出了货物 ( 编辑了发货信息 )',
            'log_orderstate'            => 25,//ORDER_STATE_SEND;
        ];
        $model_order->addOrderLog($order_log_data);

        //发送消息队列
        $this->send(intval($order_id));

        return 1;
    }

    /**
     * mq发送数据
     */
    private function send($data){

        $rabbitMQ = new RabbitMQ();

//        $connection = $rabbitMQ->connection('218.28.14.169', 5672, 'wzxd', '123456');
        $connection = $rabbitMQ->connection('10.10.11.141', 5672, 'wzxd', 'WZXDRMQpython~XX2');
        // $connection = $rabbitMQ->connection('localhost', 5672, 'guest', 'guest');

        if($connection){

            $rabbitMQ->sendTopic($connection,$data,'post_topic_exchange','post.#');

            $rabbitMQ->close($connection);

        }

    }

    /**
     * 收到货款
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @return array
     */
    public function changeOrderReceivePay($order_list, $role, $user = '', $post = array()) {
        $model_order = Model('order');

        try {
            $model_order->beginTransaction();

            $data = array();
            $data['api_pay_state'] = 1;
	      $update = $model_order->editOrderPay($data,array('pay_sn'=>$order_list[0]['pay_sn']));
            //$update = $model_order->editOrderPay($data,array('pay_sn'=>$order_info['pay_sn']));
            if (!$update) {
                throw new Exception('更新支付单状态失败');
            }

            $model_pd = Model('predeposit');
            foreach($order_list as $order_info) {
                $order_id = $order_info['order_id'];
                if ($order_info['order_state'] != ORDER_STATE_NEW) continue;
                //下单，支付被冻结的充值卡
                $rcb_amount = floatval($order_info['rcb_amount']);
                if ($rcb_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $rcb_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changeRcb('order_comb_pay',$data_pd);
                }

                //下单，支付被冻结的预存款
                $pd_amount = floatval($order_info['pd_amount']);
                if ($pd_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $pd_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changePd('order_comb_pay',$data_pd);
                }
            }
           // if($order_info['order_state'] == 10){
           //      $model_yundayin = Model('yundayin');
           //      $model_yundayin->_yundayin($order_info['order_sn']);
           // }
            if($order_info['order_state'] == 10 || $order_info['order_state'] == 0){
                try {
                    $model = Model();
                    $model->beginTransaction();
                    //拆单
                    $model_chaiorder = Model('chaiorder');
                    $model_chaiorder->chaidan($order_info['order_id']);
                    $model->commit();
                    //查询分单信息
                    $model_order = Model('order');
                    $condition['is_zorder'] = array('gt',0);
                    $condition['z_order_id'] = $order_info['order_id'];
                    $f_order_info = $model_order->table('order')->where($condition)->select();
                    // $f_order_info = $model_order->table('order')->where(array('z_order_id'=>$order_info['order_id'],'is_zorder'=>1))->select();
                    // print_r($f_order_info);die;
                    $model_yundayin = Model('yundayin');
                    foreach ($f_order_info as $k => $f_order) {
                        $model_yundayin->_yundayin($f_order['order_sn']);
						
						//云仓 已支付订单同步接口
						$orderList = [];
						$orderList['tenantId'] = 42;
						$orderList['orderSn'] = $f_order['order_sn'];
						$orderList['orderStatus'] = 0;
						$orderList['totalAmount'] = $f_order['order_amount'];
						$orderList['orderTime'] = date('Y-m-d H:i:s',$f_order['add_time']);
						$orderList['salerCode'] = 'WZXD';
						$orderList['salerName'] = '物资小店';
						$orderList['orderAddress'] = unserialize(Model()->table('order_common')->getfby_order_id($f_order['order_id'],'reciver_info'))['address'];
						$goods_list = $model_order->getOrderGoodsList(array('order_id'=>$f_order['order_id']),'goods_id,goods_serial as goodsCode,goods_name as goodsName,goods_price as goodsPrice,goods_num as goodsCount,goods_pay_price as goodsMoney,is_cw,voucher_price');
						$goodsList = [];
						$goods_amount = 0;
						$cw_sign = 0;
                        $sql = "select svt.voucher_t_is_lg from 718shop_voucher_template svt left join 718shop_voucher sv on svt.voucher_t_id=sv.voucher_t_id left join 718shop_order_common soc on soc.voucher_id=sv.voucher_id where soc.order_id=".$f_order['order_id'];
                        $voucher_t_is_lg = Model()->query($sql)[0]['voucher_t_is_lg'];
						foreach ($goods_list as $key=>$order_goods){
							$is_cw = $order_goods['is_cw'];
							if($is_cw == 1){
								$goodsList[$key]['orderSn'] = $f_order['order_sn'];
								if($order_goods['goodsCode']){
									$goodsList[$key]['goodsCode'] = $order_goods['goodsCode'];
								}else{
									$cw_sign = 0;
									break;
								}
								$goodsList[$key]['goodsName'] = $order_goods['goodsName'];
								$goodsList[$key]['goodsPrice'] = $order_goods['goodsPrice'];
								$goodsList[$key]['goodsCount'] = $order_goods['goodsCount'];
								$goodsList[$key]['goodsMoney'] = $order_goods['goodsMoney'];
                                $goodsList[$key]['realMoney'] = $order_goods['goodsMoney'];
                                if($voucher_t_is_lg==1){//陆港代金券
                                    $goodsList[$key]['couponType'] = '1';
                                    $goodsList[$key]['couponGoodMoney'] = $order_goods['voucher_price'];
                                }else{
                                    $goodsList[$key]['couponType'] = '0';
                                    $goodsList[$key]['couponGoodMoney'] = '0';
                                }
								$cw_sign = 1;
                                $goods_amount += $order_goods['goodsMoney'];
							}else{
								// $goods_amount += $order_goods['goodsMoney'];
								continue;
							}
						}
						unset($goods_list);
						if($cw_sign == 0){//订单里没有云仓商品，直接更新云仓确认收货标志为1
							$model_order->editOrder(array('is_cw_completed'=>1),array('order_id'=>$f_order['order_id']));
						}else{
							$orderList['totalAmount'] = $goods_amount;//$f_order['order_amount'] - $goods_amount;
							$orderList['couponType'] = $voucher_t_is_lg==1?'1':'0';
							$orderList['goodsList'] = array_values($goodsList);

                            //********************区分邮寄********************//
                            if($f_order['by_post']==2){
                                $orderList['shipmentFlag'] = 1;
                                $orderList['overName'] = Model()->table('order_common')->getfby_order_id($f_order['order_id'],'reciver_name');
                                $reciver_info = Model()->table('order_common')->getfby_order_id($f_order['order_id'],'reciver_info');
                                $orderList['overPhone'] = unserialize($reciver_info)['mob_phone'];
                                $orderList['overAddress'] = unserialize($reciver_info)['address'];
                            }else{
                                $orderList['shipmentFlag'] = 0;
                                $orderList['overName'] = '';
                                $orderList['overPhone'] = '';
                                $orderList['overAddress'] = '';
                            }
                            //********************区分邮寄********************//

                            $model_cw = Model('cw');
							if($orderList['goodsList']){
								$res = $model_cw->cwOrderSubmit($orderList);
                                file_put_contents('/data/default/wzxd/qlog/cworder.log', date("Y-m-d H:i:s",time()).'--'.json_encode($orderList,320).'--'.json_encode($res,320)." \n", FILE_APPEND);
								$cw_log_info = $model_cw->cw_logGet($f_order['order_id']);
								$cw_log = array();
								$cw_log['add_time'] = TIMESTAMP;
								$cw_log['order_id'] = $f_order['order_id'];
								$cw_log['code'] = $res['code']?$res['code']:1;
								$cw_log['msg'] = $res['msg']?$res['msg']:'';
								$cw_log['counter'] = 0;
								if($cw_log['msg'] == '提交订单成功' || $cw_log['msg'] == '请勿重复提交订单'){
									$cw_log['code'] = 0;
									$cw_log['counter'] = 5;
								}
								if($cw_log_info){
									$model_cw->cw_logUpdate($f_order['order_id'],$cw_log);
								}else{
									$model_cw->cw_logAdd($cw_log);
								}
							}
						}
                    }      
                }catch (Exception $e){
                    $model->rollback();
                    return callback(false, $e->getMessage());
                }
                    // $model_yundayin = Model('yundayin');
                    // $model_yundayin->_yundayin($order_info['order_sn']);
            }
            //更新订单状态
            $update_order = array();
            $update_order['order_state'] = ORDER_STATE_PAY;
            $update_order['payment_time'] = ($post['payment_time'] ? strtotime($post['payment_time']) : TIMESTAMP);
            $update_order['payment_code'] = $post['payment_code'];
            $update = $model_order->editOrder($update_order,array('pay_sn'=>$order_info['pay_sn'],'order_state'=>ORDER_STATE_NEW));
            if (!$update) {
                throw new Exception('操作失败');
            }

            $model_order->commit();
        } catch (Exception $e) {
            $model_order->rollback();
            return callback(false,$e->getMessage());
        }

        foreach($order_list as $order_info) {
			//防止重复发送消息
			if ($order_info['order_state'] != ORDER_STATE_NEW) continue;
            $order_id = $order_info['order_id'];
            // 支付成功发送买家消息
            $param = array();
            $param['code'] = 'order_payment_success';
            $param['member_id'] = $order_info['buyer_id'];
            $param['param'] = array(
                    'order_sn' => $order_info['order_sn'],
                    'order_url' => urlShop('member_order', 'show_order', array('order_id' => $order_info['order_id']))
            );
            QueueClient::push('sendMemberMsg', $param);

            // 支付成功发送店铺消息
            $param = array();
            $param['code'] = 'new_order';
            $param['store_id'] = $order_info['store_id'];
            $param['param'] = array(
                    'order_sn' => $order_info['order_sn']
            );
            QueueClient::push('sendStoreMsg', $param);

            //添加订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_user'] = $user;
            $data['log_msg'] = '收到了货款 ( 支付平台交易号 : '.$post['trade_no'].' )';
            $data['log_orderstate'] = ORDER_STATE_PAY;
            $model_order->addOrderLog($data);            
        }

        return callback(true,'操作成功');
    }
}