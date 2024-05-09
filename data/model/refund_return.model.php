<?php
/**
 * 退款退货
 **/

defined('In718Shop') or exit('Access Invalid!');

class refund_returnModel extends Model
{

    /**
     * 取得退单数量
     * @param unknown $condition
     */
    public function getRefundReturn($condition)
    {
        return $this->table('refund_return')->where($condition)->count();
    }

    /**
     * 增加退款退货
     *
     * @param
     * @return int
     */
    public function addRefundReturn($refund_array, $order = array(), $goods = array())
    {
        if (!empty($order) && is_array($order)) {
            $refund_array['order_id'] = $order['order_id'];
            $refund_array['order_sn'] = $order['order_sn'];
            $refund_array['store_id'] = $order['store_id'];
            $refund_array['store_name'] = $order['store_name'];
            $refund_array['buyer_id'] = $order['buyer_id'];
            $refund_array['buyer_name'] = $order['buyer_name'];
        }
        if (!empty($goods) && is_array($goods)) {
            $refund_array['goods_id'] = $goods['goods_id'];
            $refund_array['order_goods_id'] = $goods['rec_id'];
            $refund_array['order_goods_type'] = $goods['goods_type'];
            $refund_array['goods_name'] = $goods['goods_name'];
            $refund_array['commis_rate'] = $goods['commis_rate'];
            $refund_array['goods_image'] = $goods['goods_image'];
        }
        $refund_array['refund_sn'] = $this->getRefundsn($refund_array['store_id']);
        $refund_id = $this->table('refund_return')->insert($refund_array);

        // 发送商家提醒
        $param = array();
        if (intval($refund_array['refund_type']) == 1) { // 退款
            $param['code'] = 'refund';
        } else { // 退货
            $param['code'] = 'return';
        }
        $param['store_id'] = $order['store_id'];
        $type = $refund_array['order_lock'] == 2 ? '售前' : '售后';
        $param['param'] = array(
            'type' => $type,
            'refund_sn' => $refund_array['refund_sn'],
        );
        QueueClient::push('sendStoreMsg', $param);

        return $refund_id;
    }

    /**
     * 订单锁定
     *
     * @param
     * @return bool
     */
    public function editOrderLock($order_id)
    {
        $order_id = intval($order_id);
        if ($order_id > 0) {
            $condition = array();
            $condition['order_id'] = $order_id;
            $data = array();
            $data['lock_state'] = array('exp', 'lock_state+1');
            $model_order = Model('order');
            $result = $model_order->editOrder($data, $condition);
            return $result;
        }
        return false;
    }

    /**
     * 订单解锁
     *
     * @param
     * @return bool
     */
    public function editOrderUnlock($order_id)
    {
        $order_id = intval($order_id);
        if ($order_id > 0) {
            $condition = array();
            $condition['order_id'] = $order_id;
            $condition['lock_state'] = array('egt', '1');
            $data = array();
            $data['lock_state'] = array('exp', 'lock_state-1');
            $data['delay_time'] = time();
            $model_order = Model('order');
            $result = $model_order->editOrder($data, $condition);
            return $result;
        }
        return false;
    }

    /**
     * 修改记录
     *
     * @param
     * @return bool
     */
    public function editRefundReturn($condition, $data)
    {
        if (empty($condition)) {
            return false;
        }
        if (is_array($data)) {
            $result = $this->table('refund_return')->where($condition)->update($data);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 平台确认退款处理
     *
     * @param
     * @return bool
     */
    public function editOrderRefund($refund)
    {
        $refund_id = intval($refund['refund_id']);
        if ($refund_id > 0) {
            Language::read('model_lang_index');
            $order_id = $refund['order_id']; //订单编号
            $field = 'order_id,buyer_id,buyer_name,store_id,order_sn,order_amount,payment_code,order_state,refund_amount,rcb_amount,card_amount,z_order_id,z_order_sn,is_zorder';
            $model_order = Model('order');
            $order = $model_order->getOrderInfo(array('order_id' => $order_id), array('order_common'), $field);
            $z_order_sn = $order['z_order_sn'];
            $zorder_info = $model_order->getOrderInfo(array('order_sn' => $z_order_sn), array(), 'order_amount'); //获取订单总表信息

            $model_predeposit = Model('predeposit');
            try {
                $this->beginTransaction();
                $order_amount = $order['order_amount']; //订单金额
                $rcb_amount = $order['rcb_amount']; //充值卡支付金额
                $predeposit_amount = $order_amount - $order['refund_amount'] - $rcb_amount; //可退预存款金额

                //一卡通退款
                $pd_amount = $order['pd_amount'];
                $card_amount = $order['card_amount'];

                if (($rcb_amount > 0) && ($predeposit_amount <= 0)) { //退充值卡     //充值卡全部支付
                    $log_array = array();
                    $log_array['member_id'] = $order['buyer_id'];
                    $log_array['member_name'] = $order['buyer_name'];
                    $log_array['order_sn'] = $order['order_sn'];
                    $log_array['amount'] = $refund['refund_amount'];

                    $state = $model_predeposit->changeRcb('refund', $log_array); //增加买家可用充值卡金额
                } else if ($predeposit_amount > 0) { //退预存款     //充值卡部分支付或未支付
                    $log_array = array();
                    $log_array['member_id'] = $order['buyer_id'];
                    $log_array['member_name'] = $order['buyer_name'];
                    $log_array['order_sn'] = $order['order_sn'];
                    $log_array['amount'] = $refund['refund_amount'];
                    $state = $model_predeposit->changePd('refund', $log_array); //增加买家可用预存款金额
                }

                //退一卡通支付金额
                if ($card_amount > 0) {
                    $data_card = array();
                    $data_card['cardno'] = $refund['cardno'];
                    $data_card['member_id'] = $order['buyer_id'];
                    $data_card['member_name'] = $order['buyer_name'];
                    $data_card['order_sn'] = $order['order_sn'];
                    $data_card['amount'] = $refund['refund_amount'];
                    $data_card['log_admin'] = $refund['admin_name'];
                    //这里返回值与预存款等分开
                    $card_state = $model_predeposit->changeCard('refund', $data_card);
                    $order_state = $order['order_state'];
                    $model_trade = Model('trade');
                    $order_paid = $model_trade->getOrderState('order_paid'); //订单状态20:已付款
                    if ($card_state && $order_state >= $order_paid) {
                        //order_log表
                        Logic('order')->changeOrderStateCancel($order, 'system', '系统', '商品全部退款完成取消订单', false);
                    }
                    //避免一卡通退款时向pd_cash表里插数据
                    if ($card_state) {
                        $order_array = array();
                        $order_amount = $order['order_amount']; //订单金额
                        $refund_amount = $order['refund_amount'] + $refund['refund_amount']; //退款金额
                        $order_array['refund_state'] = ($order_amount - $refund_amount) > 0 ? 1 : 2;
                        $order_array['refund_amount'] = ncPriceFormat($refund_amount);
                        $order_array['pd_amount'] = ncPriceFormat($log_array['amount']);
                        $order_array['delay_time'] = time();
                        $card_state = $model_order->editOrder($order_array, array('order_id' => $order_id)); //更新订单退款
                        if ($card_state && $refund['order_lock'] == '2') {
                            $card_state = $this->editOrderUnlock($order_id);
                        }
                        $this->commit();
                        return '2';
                    }
                }
                $order_state = $order['order_state'];
                $model_trade = Model('trade');
                $order_paid = $model_trade->getOrderState('order_paid'); //订单状态20:已付款
                if ($state && $order_state >= $order_paid) {
                    Logic('order')->changeOrderStateCancel($order, 'system', '系统', '商品全部退款完成取消订单', false);
                }
                if ($state) {
                    $order_array = array();
                    $order_amount = $order['order_amount']; //订单金额
                    $refund_amount = $order['refund_amount'] + $refund['refund_amount']; //退款金额
                    $order_array['refund_state'] = ($order_amount - $refund_amount) > 0 ? 1 : 2;
                    $order_array['refund_amount'] = ncPriceFormat($refund_amount);
                    $order_array['pd_amount'] = ncPriceFormat($log_array['amount']);
                    $order_array['delay_time'] = time();
                    $state = $model_order->editOrder($order_array, array('order_id' => $order_id)); //更新订单退款
                }
                if ($state && $refund['order_lock'] == '2') {
                    $state = $this->editOrderUnlock($order_id); //订单解锁
                }
                $this->commit();
                return $state;
            } catch (Exception $e) {
                $this->rollback();
                return false;
            }
        }
        return false;
    }

    public function editOrderRefund_disagree($refund)
    {
        $refund_id = intval($refund['refund_id']);
        if ($refund_id > 0) {
            Language::read('model_lang_index');
            $order_id = $refund['order_id']; //订单编号
            $field = 'order_id,buyer_id,buyer_name,store_id,order_sn,order_amount,payment_code,order_state,refund_amount,rcb_amount';
            $model_order = Model('order');
            $order = $model_order->getOrderInfo(array('order_id' => $order_id), array(), $field);

            $model_predeposit = Model('predeposit');
            try {
                $this->beginTransaction();
                $order_amount = $order['order_amount']; //订单金额
                $rcb_amount = $order['rcb_amount']; //充值卡支付金额
                $predeposit_amount = $order_amount - $order['refund_amount'] - $rcb_amount; //可退预存款金额

                //if (($rcb_amount > 0) && ($refund['refund_amount'] > $predeposit_amount)) {//退充值卡
                //    $log_array = array();
                //    $log_array['member_id'] = $order['buyer_id'];
                //    $log_array['member_name'] = $order['buyer_name'];
                //    $log_array['order_sn'] = $order['order_sn'];
                //    $log_array['amount'] = $refund['refund_amount'];
                //    if ($predeposit_amount > 0) {
                //        $log_array['amount'] = $refund['refund_amount']-$predeposit_amount;
                //   }
                //    $state = $model_predeposit->changeRcb('refund', $log_array);//增加买家可用充值卡金额
                //}
                //if ($predeposit_amount > 0) {//退预存款
                //    $log_array = array();
                //    $log_array['member_id'] = $order['buyer_id'];
                //    $log_array['member_name'] = $order['buyer_name'];
                //    $log_array['order_sn'] = $order['order_sn'];
                //    $log_array['amount'] = $refund['refund_amount'];//退预存款金额
                //    if ($refund['refund_amount'] > $predeposit_amount) {
                //        $log_array['amount'] = $predeposit_amount;
                //    }
                //    $state = $model_predeposit->changePd('refund', $log_array);//增加买家可用预存款金额
                //}

                $order_state = $order['order_state'];
                $model_trade = Model('trade');
                $order_paid = $model_trade->getOrderState('order_paid'); //订单状态20:已付款
                //if ($state && $order_state >= $order_paid) {
                //    Logic('order')->changeOrderStateCancel($order, 'system', '系统', '商品全部退款完成取消订单',false);
                //}
                $state = 1;
                // if ($state) {
                //     $order_array = array();
                //     $order_amount = $order['order_amount'];//订单金额
                //     $refund_amount = $order['refund_amount'] + $refund['refund_amount'];//退款金额
                //     //$order_array['refund_state'] = ($order_amount-$refund_amount) > 0 ? 1:2;
                //     $order_array['refund_state'] = ($order_amount - $refund_amount) > 0 ? 1 : 0;
                //     //$order_array['refund_amount'] = ncPriceFormat($refund_amount);
                //     //$order_array['delay_time'] = time();
                //     $state = $model_order->editOrder($order_array, array('order_id' => $order_id));//更新订单退款
                // }
                if ($state && $refund['order_lock'] == '2') {
                    $state = $this->editOrderUnlock($order_id); //订单解锁
                }
                $this->commit();
                return $state;
            } catch (Exception $e) {
                $this->rollback();
                return false;
            }
        }
        return false;
    }

    /**
     * 平台确认退款处理
     *
     * @param
     * @return bool
     */
    public function editOrderRefund_jp($refund)
    {
        $refund_id = intval($refund['refund_id']);
        if ($refund_id > 0) {
            Language::read('model_lang_index');
            $order_id = $refund['order_id']; //订单编号
            $field = 'order_id,buyer_id,buyer_name,store_id,order_sn,pay_sn,order_amount,payment_code,order_state,refund_amount,rcb_amount,pd_amount,card_amount,z_order_id,z_order_sn,is_zorder'; //mx 多获取预存款支付金额一列
            $model_order = Model('order');
            $order = $model_order->getOrderInfo(array('order_id' => $order_id), array('order_common'), $field);
            $z_order_sn = $order['z_order_sn'];
            $zorder_info = $model_order->getOrderInfo(array('order_sn' => $z_order_sn), array(), 'order_amount'); //获取订单总表信息
            //$order_pay = $model_order->getOrderPayList(array('pay_sn'=>$order['pay_sn']));//获取order_pay列表

            $model_predeposit = Model('predeposit');
            $model_member = Model('member');
            $member_info = $model_member->getMemberInfoByID($refund['buyer_id']);

            try {
                $this->beginTransaction();
                $rcb_amount = $order['rcb_amount']; //充值卡支付金额
                $pd_amount = $order['pd_amount']; //预存款支付金额
                $card_amount = $order['card_amount']; //一卡通支付金额
                $refund_last = $order['refund_amount']; //上次退款金额，未退款是0，有过退款则为前次退款金额和
                $refund_now = $refund['refund_amount']; //本次退款金额
                if ($refund_last < $rcb_amount) { //上次退款金额小于充值卡支付的金额，充值卡没退完
                    if ($refund_now <= $rcb_amount - $refund_last) { //本次退款金额小于或等于充值卡剩余的金额
                        $log_array = array();
                        $log_array['member_id'] = $order['buyer_id'];
                        $log_array['member_name'] = $order['buyer_name'];
                        $log_array['order_sn'] = $order['order_sn'];
                        $log_array['amount'] = $refund_now; //充值卡退得到的金额为本次申请的退款金额
                        $state = $model_predeposit->changeRcb('refund', $log_array); //增加买家可用充值卡金额
                    } else { //本次退款金额大于充值卡剩余的金额
                        //                      //先退充值卡剩余金额
                        $log_array = array();
                        $log_array['member_id'] = $order['buyer_id'];
                        $log_array['member_name'] = $order['buyer_name'];
                        $log_array['order_sn'] = $order['order_sn'];
                        $log_array['amount'] = $rcb_amount - $refund_last; //充值卡退得到的金额为充值卡剩余金额
                        $state = $model_predeposit->changeRcb('refund', $log_array); //增加买家可用充值卡金额
                        if ($refund_now - ($rcb_amount - $refund_last) <= $pd_amount) { //本次退款金额-充值卡剩余金额的剩余金额  如果小于或等于预存款支付金额
                            //n-(r-l)
                            $log_array = array();
                            $log_array['member_id'] = $order['buyer_id'];
                            $log_array['member_name'] = $order['buyer_name'];
                            $log_array['order_sn'] = $order['order_sn'];
                            $log_array['amount'] = $refund_now - ($rcb_amount - $refund_last);
                            $state = $model_predeposit->changePd('refund', $log_array); //增加买家可用预存款金额
                        } else { //本次退款金额-充值卡剩余金额的剩余金额  如果大于预存款支付金额，预存款全退完
                            //p
                            $log_array = array();
                            $log_array['member_id'] = $order['buyer_id'];
                            $log_array['member_name'] = $order['buyer_name'];
                            $log_array['order_sn'] = $order['order_sn'];
                            $log_array['amount'] = $pd_amount;
                            $state = $model_predeposit->changePd('refund', $log_array); //增加买家可用预存款金额
                            //三方支付   //n-(r-l)-p
                            $pdc_sn = $model_predeposit->makeSn();
                            $data = array();
                            $data['pdc_sn'] = $pdc_sn;
                            $data['pdc_member_id'] = $member_info['member_id'];
                            $data['pdc_member_name'] = $member_info['member_name'];
                            $data['pdc_amount'] = $refund_now - ($rcb_amount - $refund_last) - $pd_amount;
                            $data['pdc_bank_name'] = "支付宝/微信";
                            $data['pdc_bank_no'] = $refund['order_sn'];
                            $data['pdc_bank_user'] = $member_info['member_name'];
                            $data['pdc_add_time'] = TIMESTAMP;
                            $data['pdc_payment_state'] = 0;
                            $data['order_sn'] = $refund['order_sn'];
                            $data['pay_sn'] = $order['pay_sn'];
                            $data['refund_id'] = $refund['refund_id'];
                            $state = $model_predeposit->addPdCash($data);
                        }
                    }
                } else { //上次退款金额大于等于充值卡支付的金额，充值卡已退完
                    if ($refund_last - $rcb_amount < $pd_amount) { //上次退款剩余金额小于预存款金额，预存款没退完
                        if ($refund_now <= $pd_amount - ($refund_last - $rcb_amount)) { //本次退款金额小于等于上次退款剩余金额
                            $log_array = array();
                            $log_array['member_id'] = $order['buyer_id'];
                            $log_array['member_name'] = $order['buyer_name'];
                            $log_array['order_sn'] = $order['order_sn'];
                            $log_array['amount'] = $refund_now;
                            $state = $model_predeposit->changePd('refund', $log_array); //增加买家可用预存款金额
                        } else { //本次退款金额大于上次退款剩余金额
                            //先退预存款
                            $log_array = array();
                            $log_array['member_id'] = $order['buyer_id'];
                            $log_array['member_name'] = $order['buyer_name'];
                            $log_array['order_sn'] = $order['order_sn'];
                            $log_array['amount'] = $pd_amount - ($refund_last - $rcb_amount);
                            $state = $model_predeposit->changePd('refund', $log_array); //增加买家可用预存款金额
                            //n-(p-(l-r))
                            $pdc_sn = $model_predeposit->makeSn();
                            $data = array();
                            $data['pdc_sn'] = $pdc_sn;
                            $data['pdc_member_id'] = $member_info['member_id'];
                            $data['pdc_member_name'] = $member_info['member_name'];
                            $data['pdc_amount'] = $refund_now - ($pd_amount - ($refund_last - $rcb_amount));
                            $data['pdc_bank_name'] = "支付宝/微信";
                            $data['pdc_bank_no'] = $refund['order_sn'];
                            $data['pdc_bank_user'] = $member_info['member_name'];
                            $data['pdc_add_time'] = TIMESTAMP;
                            $data['pdc_payment_state'] = 0;
                            $data['order_sn'] = $refund['order_sn'];
                            $data['pay_sn'] = $order['pay_sn'];
                            $data['refund_id'] = $refund['refund_id'];
                            $state = $model_predeposit->addPdCash($data);
                        }
                    } else { //上次退款剩余金额大于等于预存款金额，预存款退完了
                        $pdc_sn = $model_predeposit->makeSn();
                        $data = array();
                        $data['pdc_sn'] = $pdc_sn;
                        $data['pdc_member_id'] = $member_info['member_id'];
                        $data['pdc_member_name'] = $member_info['member_name'];
                        $data['pdc_amount'] = $refund_now;
                        $data['pdc_bank_name'] = "支付宝/微信";
                        $data['pdc_bank_no'] = $refund['order_sn'];
                        $data['pdc_bank_user'] = $member_info['member_name'];
                        $data['pdc_add_time'] = TIMESTAMP;
                        $data['pdc_payment_state'] = 0;
                        $data['order_sn'] = $refund['order_sn'];
                        $data['pay_sn'] = $order['pay_sn'];
                        $data['refund_id'] = $refund['refund_id'];

                        //线下余额退款
                        if ($order['payment_code'] == 'offpay') {
                            $uid_time = time();
                            $member_uid_model = Model('member_uid');
                            $member_uid_log_model = Model('member_uid_log');
                            $uid = $member_uid_model->getUid($refund['buyer_id']);
                            if (!$uid) {
                                return false;
                            }
                            //退款请求参数
                            $data_post = array('type' => 1, 'amount' => $refund_now, 'orderSn' => $refund['refund_sn'], 'uid' => $uid);
                            //查询线下余额增加日志
                            $balance_response = $member_uid_model->selectBalance($uid);
                            if (is_array($balance_response) && $balance_response['code'] == 0) {
                                $select_log_data = array('member_id' => $refund['buyer_id'], 'uid' => $uid, 'action' => 2, 'log_time' => $uid_time, 'content' => '查询成功，余额：' . $balance_response['balance'], 'result' => 2);
                                $member_uid_log_model->addLog($select_log_data);
                            } else {
                                $select_log_data = array('member_id' => $refund['buyer_id'], 'uid' => $uid, 'action' => 2, 'log_time' => $uid_time, 'content' => '查询失败', 'result' => 2);
                                $member_uid_log_model->addLog($select_log_data);
                                return false;
                            }
                            $refund_response = $member_uid_model->payOrRefundBalance($data_post);
                            if (is_array($refund_response) && $refund_response['code'] == 0) {
                                $data['pdc_bank_name'] = "线下余额";
                                //记录日志
                                $pay_log_data = array('member_id' => $refund['buyer_id'], 'uid' => $uid, 'action' => 4, 'log_time' => $uid_time, 'content' => $refund['refund_sn'] . '退款成功，退款金额：' . $refund_now, 'result' => 2);
                                $member_uid_log_model->addLog($pay_log_data);
                            } else {
                                $pay_log_data = array('member_id' => $refund['buyer_id'], 'uid' => $uid, 'action' => 4, 'log_time' => $uid_time, 'content' => $refund['refund_sn'] . '退款失败', 'result' => 3);
                                $member_uid_log_model->addLog($pay_log_data);
                                return false;
                            }
                        }
                        if ($order['payment_code'] == 'online') {
                            if ($refund_now > 0) {
                                $model_payment = Model('mb_payment');
                                $payment_info = $model_payment->getMbPaymentInfo(array('payment_id' => 2)); //接口参数
                                $wxpay = $payment_info['payment_config'];
                                define('WXPAY_APPID', $wxpay['appId']);
                                define('WXPAY_MCHID', $wxpay['partnerId']);
                                define('WXPAY_KEY', $wxpay['apiKey']);
                                $total_fee = $zorder_info['order_amount'] * 100; //微信订单实际支付总金额(在线支付金额,单位为分)
                                $refund_fee = $refund_now * 100; //本次微信退款总金额(单位为分)
                                $api_file = BASE_PATH . DS . 'api' . DS . 'refund' . DS . 'wxpay' . DS . 'WxPay.Api.php';
                                include $api_file;
                                $input = new WxPayRefund();
                                $input->SetOut_trade_no($z_order_sn); //微信订单号
                                $input->SetTotal_fee($total_fee);
                                $input->SetRefund_fee($refund_fee);
                                $input->SetOut_refund_no($refund['refund_sn']); //退款批次号
                                $input->SetOp_user_id(WxPayConfig::MCHID);
                                $wxpay_result = WxPayApi::refund($input);
                                if (empty($wxpay_result) || $wxpay_result['return_code'] != 'SUCCESS' || $wxpay_result['result_code'] != 'SUCCESS') { //请求结果
                                    // $wxpay_result_json = json_encode($wxpay_result,320);
                                    // file_put_contents('wxRefund.log', $refund['refund_sn'].$wxpay_result_json."\r\n", FILE_APPEND | LOCK_EX);
                                    return false;
                                    //showMessage("微信退款失败,",'index.php?act=predeposit&op=pd_cash_list');
                                    //$result['msg'] = '微信接口错误,'.$data['return_msg'];//返回信息
                                }
                            }
                        }

                        $state = $model_predeposit->addPdCash($data);
                    }
                }
                //集团餐卡退款
                if ($order['payment_code'] == 'jicardpay') {
                    //查询用户餐卡卡号
                    $member_uid_model = Model('member_uid');
                    $model_wzcard = Model('wzcard');
                    $uid = $member_uid_model->getUid($order['buyer_id']);
                    //1. 集团餐卡余额查询
                    $data = array('uid' => $uid);
                    //测试地址
                    //$url = "http://171.15.132.170:8083/api/smallprogram/center/selectCardInfo";
                    //正式地址
                    //$url = "https://xls.zhonghaokeji.net/api/smallprogram/center/selectCardInfo";
                    $url = "https://xls.zitcloud.cn/api/smallprogram/center/selectCardInfo";
                    $ji_response = $this->selectJiBalance($url, $data);
                    // return $ji_response['code'];
                    if (!empty($ji_response) && $ji_response['code'] == 0) {

                        $card_data['cardNo'] = $ji_response['cardNo'];
                        $card_data['member_id'] = $order['buyer_id'];
                        $card_data['member_name'] = $order['buyer_name'];
                        //退款单号
                        $card_data['order_sn'] = $refund['refund_sn'];
                        $card_data['log_admin'] = $refund['admin_id'];
                        $card_data['amount'] = $refund['refund_amount'];

                        //发起退款请求
                        $state = $model_wzcard->changeCard('refund', $card_data);
                    }

                }
                //一卡通支付金额
                if ($card_amount > 0 && $order['payment_code'] == 'zihpay') {
                    //应该是分为部分退款和全额退款
                    $card_data = array();
                    $card_data['cardno'] = $refund['cardno'];
                    $card_data['gonghao'] = $refund['gonghao'];
                    $card_data['member_id'] = $order['buyer_id'];
                    $card_data['member_name'] = $order['buyer_name'];
                    $card_data['order_sn'] = $order['order_sn'].$refund['refund_id'];
                    $card_data['log_admin'] = $refund['admin_name'];
                    //部分退款
                    if ($refund['refund_amount'] < $card_amount) {
                        $card_data['amount'] = $refund['refund_amount'];
                    } //全部退款
                    else {
                        // $card_data['member_id']=$order['buyer_id'];
                        // $card_data['member_name']=$order['buyer_name'];
                        // $card_data['order_sn']=$order['order_sn'];
                        $card_data['amount'] = $card_amount;
                        // $state=$model_predeposit->changeCard('refund',$card_data);
                    }
                    $result = $model_predeposit->changeCard('refund', $card_data);
                }
                //新零售余额支付金额
                if ($order['payment_code'] == 'newpay') {
                    $card_data = array();
                    $card_data['cardno'] = $refund['cardno'];
                    //add
                    $card_data['gonghao'] = $refund['gonghao'];
                    $card_data['member_id'] = $order['buyer_id'];
                    $card_data['member_name'] = $order['buyer_name'];
                    $card_data['order_sn'] = $order['order_sn'].$refund['refund_id'];
                    $card_data['log_admin'] = $refund['admin_name'];
                    $card_data['amount'] = $refund['refund_amount'];
                    $result = $model_predeposit->exchangeCard('refund', $card_data);
                }
                $order_state = $order['order_state'];
                $model_trade = Model('trade');
                $order_paid = $model_trade->getOrderState('order_paid'); //订单状态20:已付款
                // if ($state && $order_state >= $order_paid) {
                //     Logic('order')->changeOrderStateCancel($order, 'system', '系统', '商品全部退款完成取消订单', false);
                // }
                if ($state || $result['state'] == 0) {
                    $order_array = array();
                    $order_amount = $order['order_amount']; //订单金额
                    $refund_amount = $order['refund_amount'] + $refund['refund_amount']; //退款金额
                    $order_array['refund_state'] = ($order_amount - $refund_amount) > 0 ? 1 : 2;
                    // if( $order_amount== $refund_amount){
                    if ($order_array['refund_state'] == 2) {
                        Logic('order')->changeOrderStateCancel($order, 'system', '系统', '商品全部退款完成取消订单', false);
                    }
                    $order_array['refund_amount'] = ncPriceFormat($refund_amount);
                    $order_array['pd_amount'] = $order['pd_amount'];
                    $order_array['delay_time'] = time();
                    $state = $model_order->editOrder($order_array, array('order_id' => $order_id)); //更新订单退款
                }
                if ($state && $refund['order_lock'] == '2') {
                    $state = $this->editOrderUnlock($order_id); //订单解锁
                }
                $this->commit();
                return $state;
            } catch (Exception $e) {
                $this->rollback();
                return false;
            }
        }
        return false;
    }
    private function selectJiBalance($url, $data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        //判断返回数据是否json格式
        if (is_null(json_decode($response))) {
            return false;
        }
        return json_decode($response, true);
    }
    /**
     * 增加退款详细
     *
     * @param
     * @return int
     */
    public function addDetail($refund, $order)
    {
        $detail_array = array();
        $detail_array['refund_id'] = $refund['refund_id'];
        $detail_array['order_id'] = $refund['order_id'];
        $detail_array['batch_no'] = date('YmdHis') . $refund['refund_id']; //批次号。支付宝要求格式为：当天退款日期+流水号。
        $detail_array['refund_amount'] = ncPriceFormat($refund['refund_amount']);
        $detail_array['refund_code'] = 'predeposit';
        $detail_array['refund_state'] = '1';
        $detail_array['add_time'] = time();
        if (!empty($order['trade_no']) && in_array($order['payment_code'], array('wxpay', 'wx_jsapi', 'wx_saoma'))) { //微信支付
            $api_file = BASE_PATH . DS . 'api' . DS . 'refund' . DS . 'wxpay' . DS . 'WxPay.Config.php';
            if ($order['payment_code'] == 'wxpay') {
                $api_file = BASE_PATH . DS . 'api' . DS . 'refund' . DS . 'wxpay' . DS . 'WxPayApp.Config.php';
            }
            include $api_file;
            $apiclient_cert = WxPayConfig::SSLCERT_PATH;
            $apiclient_key = WxPayConfig::SSLKEY_PATH;
            if (!empty($apiclient_cert) && !empty($apiclient_key)) { //验证商户证书路径设置
                $detail_array['refund_code'] = $order['payment_code'];
            }
        }
        if (!empty($order['trade_no']) && $order['payment_code'] == 'alipay') { //支付宝
            $detail_array['refund_code'] = 'alipay';
        }
        $result = $this->table('refund_detail')->insert($detail_array);
        return $result;
    }

    /**
     * 增加退款退货原因
     *
     * @param
     * @return int
     */
    public function addReason($reason_array)
    {
        $reason_id = $this->table('refund_reason')->insert($reason_array);
        return $reason_id;
    }

    /**
     * 修改退款详细记录
     *
     * @param
     * @return bool
     */
    public function editDetail($condition, $data)
    {
        if (empty($condition)) {
            return false;
        }
        if (is_array($data)) {
            $result = $this->table('refund_detail')->where($condition)->update($data);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 修改退款退货原因记录
     *
     * @param
     * @return bool
     */
    public function editReason($condition, $data)
    {
        if (empty($condition)) {
            return false;
        }
        if (is_array($data)) {
            $result = $this->table('refund_reason')->where($condition)->update($data);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 删除退款退货原因记录
     *
     * @param
     * @return bool
     */
    public function delReason($condition)
    {
        if (empty($condition)) {
            return false;
        } else {
            $result = $this->table('refund_reason')->where($condition)->delete();
            return $result;
        }
    }

    /**
     * 退款退货原因记录
     *
     * @param
     * @return array
     */
    public function getReasonList($condition = array(), $page = '', $limit = '', $fields = '*')
    {
        $result = $this->table('refund_reason')->field($fields)->where($condition)->page($page)->limit($limit)->order('sort asc,reason_id desc')->key('reason_id')->select();
        return $result;
    }

    /**
     * 取退款退货记录
     *
     * @param
     * @return array
     */
    public function getRefundReturnList($condition = array(), $page = '', $fields = '*', $limit = '')
    {
        $result = $this->table('refund_return')->field($fields)->where($condition)->page($page)->limit($limit)->order('refund_id desc')->select();
        return $result;
    }

    /**
     * 取退款记录
     *
     * @param
     * @return array
     */
    public function getRefundList($condition = array(), $page = '')
    {
        $condition['refund_type'] = '1'; //类型:1为退款,2为退货
        $result = $this->getRefundReturnList($condition, $page);
        return $result;
    }

    /**
     * 取退货记录
     *
     * @param
     * @return array
     */
    public function getReturnList($condition = array(), $page = '')
    {
        $condition['refund_type'] = '2'; //类型:1为退款,2为退货
        $result = $this->getRefundReturnList($condition, $page);
        return $result;
    }

    /**
     * 退款退货申请编号
     *
     * @param
     * @return array
     */
    public function getRefundsn($store_id)
    {
        $result = mt_rand(100, 999) . substr(100 + $store_id, -3) . date('ymdHis');
        return $result;
    }

    /**
     * 退款详细记录
     *
     * @param
     * @return array
     */
    public function getDetailInfo($condition = array(), $fields = '*')
    {
        return $this->table('refund_detail')->where($condition)->field($fields)->find();
    }

    /**
     * 订单在线退款计算
     *
     * @param
     * @return array
     */
    public function getPayDetailInfo($detail_array)
    {
        $condition = array();
        $condition['order_id'] = $detail_array['order_id'];
        $model_order = Model('order');
        $order = $model_order->getOrderInfo($condition); //订单详细
        $order['pay_amount'] = ncPriceFormat($order['order_amount'] - $order['rcb_amount'] - $order['pd_amount']); //在线支付金额=订单总价格-充值卡支付金额-预存款支付金额
        $out_amount = $order['pay_amount'] - $order['refund_amount']; //可在线退款金额

        $refund_amount = $detail_array['refund_amount']; //本次退款总金额
        if ($refund_amount > $out_amount) {
            $refund_amount = $out_amount;
        }
        $order['pay_refund_amount'] = ncPriceFormat($refund_amount);
        $condition = array();
        $payment_config = array();
        $condition['payment_code'] = $order['payment_code'];
        if (in_array($order['payment_code'], array('wxpay', 'wx_jsapi'))) { //手机客户端微信支付
            if ($order['payment_code'] == 'wx_jsapi') {
                $condition['payment_code'] = 'wxpay_jsapi';
            }
            $model_payment = Model('mb_payment');
            $payment_info = $model_payment->getMbPaymentInfo($condition); //接口参数
            $payment_info = $payment_info['payment_config'];
            if ($order['payment_code'] == 'wxpay') {
                $payment_config['appid'] = $payment_info['wxpay_appid'];
                $payment_config['mchid'] = $payment_info['wxpay_partnerid'];
                $payment_config['key'] = $payment_info['wxpay_partnerkey'];
            }
            if ($order['payment_code'] == 'wx_jsapi') {
                $payment_config['appid'] = $payment_info['appId'];
                $payment_config['mchid'] = $payment_info['partnerId'];
                $payment_config['key'] = $payment_info['apiKey'];
            }
        } else {
            if ($order['payment_code'] == 'wx_saoma') {
                $condition['payment_code'] = 'wxpay';
            }
            $model_payment = Model('payment');
            $payment_info = $model_payment->getPaymentInfo($condition); //接口参数
            $payment_config = unserialize($payment_info['payment_config']);
        }
        $order['payment_config'] = $payment_config;
        return $order;
    }

    /**
     * 取一条记录
     *
     * @param
     * @return array
     */
    public function getRefundReturnInfo($condition = array(), $fields = '*')
    {
        return $this->table('refund_return')->where($condition)->field($fields)->find();
    }

    /**
     * 根据订单取商品的退款退货状态
     *
     * @param
     * @return array
     */
    public function getGoodsRefundList($order_list = array(), $order_refund = 0)
    {
        $order_ids = array(); //订单编号数组
        $order_ids = array_keys($order_list);
        $model_trade = Model('trade');
        $condition = array();
        $condition['order_id'] = array('in', $order_ids);
        $refund_list = $this->table('refund_return')->where($condition)->order('refund_id desc')->select();
        $refund_goods = array(); //已经提交的退款退货商品
        if (!empty($refund_list) && is_array($refund_list)) {
            foreach ($refund_list as $key => $value) {
                $order_id = $value['order_id']; //订单编号
                $goods_id = $value['order_goods_id']; //订单商品表编号
                if (empty($refund_goods[$order_id][$goods_id])) {
                    $refund_goods[$order_id][$goods_id] = $value;
                    if ($order_refund > 0) { //订单下的退款退货所有记录
                        $order_list[$order_id]['refund_list'] = $refund_goods[$order_id];
                    }
                }
            }
        }
        if (!empty($order_list) && is_array($order_list)) {
            foreach ($order_list as $key => $value) {
                $order_id = $key;
                $goods_list = $value['extend_order_goods']; //订单商品
                $order_state = $value['order_state']; //订单状态
                $order_paid = $model_trade->getOrderState('order_paid'); //订单状态20:已付款
                $payment_code = $value['payment_code']; //支付方式
                // if ($order_state == $order_paid && $payment_code != 'offline') {//已付款未发货的非货到付款订单可以申请取消
                //     $order_list[$order_id]['refund'] = '1';
                // } elseif ($order_state > $order_paid && !empty($goods_list) && is_array($goods_list)) {//已发货后对商品操作
                if (!empty($goods_list) && is_array($goods_list)) { //已发货后对商品操作
                    $refund = $this->getRefundState($value); //根据订单状态判断是否可以退款退货
                    foreach ($goods_list as $k => $v) {
                        $goods_id = $v['rec_id']; //订单商品表编号
                        if ($v['goods_pay_price'] >= 0) { //实际支付额大于0的可以退款
                            // $v['refund'] = $refund;
                            $v['refund'] = 1;
                        }
                        if (!empty($refund_goods[$order_id][$goods_id])) {
                            // var_dump($refund_goods[$order_id][$goods_id]);die;
                            $seller_state = $refund_goods[$order_id][$goods_id]['seller_state']; //卖家处理状态:1为待审核,2为同意,3为不同意
                            $admin_state = $refund_goods[$order_id][$goods_id]['refund_state'];
                            if ($seller_state == 3) {
                                $order_list[$order_id]['extend_complain'][$goods_id] = '1'; //不同意可以发起退款投诉
                            } else {
                                // $v['refund'] = '0';//已经存在处理中或同意的商品不能再操作
                                if ($seller_state == 2) {
                                    if ($admin_state == 3) {
                                        $v['refund'] = '2'; //退款完成
                                    } else {
                                        $v['refund'] = '0'; //处理中
                                    }
                                } else {
                                    $v['refund'] = '0'; //处理中
                                }
                            }
                            $v['extend_refund'] = $refund_goods[$order_id][$goods_id];
                        }
                        $goods_list[$k] = $v;
                    }
                }
                $order_list[$order_id]['extend_order_goods'] = $goods_list;
            }
        }
        return $order_list;
    }

    /**
     * 根据订单判断投诉订单商品是否可退款
     *
     * @param
     * @return array
     */
    public function getComplainRefundList($order, $order_goods_id = 0)
    {
        $list = array();
        $refund_list = array(); //已退或处理中商品
        $refund_goods = array(); //可退商品
        if (!empty($order) && is_array($order)) {
            $order_id = $order['order_id'];
            $order_list[$order_id] = $order;
            $order_list = $this->getGoodsRefundList($order_list);
            $order = $order_list[$order_id];
            $goods_list = $order['extend_order_goods'];
            $order_amount = $order['order_amount']; //订单金额
            $order_refund_amount = $order['refund_amount']; //订单退款金额
            foreach ($goods_list as $k => $v) {
                $goods_id = $v['rec_id']; //订单商品表编号
                if ($order_goods_id > 0 && $goods_id != $order_goods_id) {
                    continue;
                }
                $v['refund_state'] = 3;
                if (!empty($v['extend_refund'])) {
                    $v['refund_state'] = $v['extend_refund']['seller_state']; //卖家处理状态为3,不同意时能退款
                }
                if ($v['refund_state'] > 2) { //可退商品
                    $goods_pay_price = $v['goods_pay_price']; //商品实际成交价
                    if ($order_amount < ($goods_pay_price + $order_refund_amount)) {
                        $goods_pay_price = $order_amount - $order_refund_amount;
                        $v['goods_pay_price'] = $goods_pay_price;
                    }
                    $v['goods_refund'] = $v['goods_pay_price'];
                    $refund_goods[$goods_id] = $v;
                } else { //已经存在处理中或同意的商品不能再退款
                    $refund_list[$goods_id] = $v;
                }
            }
        }
        $list = array(
            'refund' => $refund_list,
            'goods' => $refund_goods,
        );
        return $list;
    }
/**
 * 扶贫获取退款订单信息
 *
 * @param
 * @return array
 */
    public function getApiRightOrderList($order_condition, $order_goods_id = 0)
    {
        $model_order = Model('order');
        $order_info = $model_order->getFpOrderInfo($order_condition, array('order_common', 'store'));

        $order_id = $order_info['order_id'];

        // $store = $order_info['extend_store'];
        $order_common = $order_info['extend_order_common'];
        if ($order_common['shipping_express_id'] > 0) {
            $express = rkcache('express', true);
        }

        $condition = array();
        $condition['order_id'] = $order_id;
        if ($order_goods_id > 0) {
            $condition['goods_id'] = $order_goods_id; //订单商品表编号
        }
        $goods_list = $model_order->getOrderGoodsList($condition);
        $order_info['goods_list'] = $goods_list;

        return $order_info;
    }
    /**
     * 详细页右侧订单信息
     *
     * @param
     * @return array
     */
    public function getRightOrderList($order_condition, $order_goods_id = 0)
    {
        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo($order_condition, array('order_common', 'store'));
        Tpl::output('order', $order_info);
        $order_id = $order_info['order_id'];

        $store = $order_info['extend_store'];
        Tpl::output('store', $store);
        $order_common = $order_info['extend_order_common'];
        Tpl::output('order_common', $order_common);
        if ($order_common['shipping_express_id'] > 0) {
            $express = rkcache('express', true);
            Tpl::output('e_code', $express[$order_common['shipping_express_id']]['e_code']);
            Tpl::output('e_name', $express[$order_common['shipping_express_id']]['e_name']);
        }

        $condition = array();
        $condition['order_id'] = $order_id;
        if ($order_goods_id > 0) {
            $condition['rec_id'] = $order_goods_id; //订单商品表编号
        }
        $goods_list = $model_order->getOrderGoodsList($condition);
        Tpl::output('goods_list', $goods_list);
        $order_info['goods_list'] = $goods_list;

        return $order_info;
    }

    /**
     * 根据订单状态判断是否可以退款退货
     *
     * @param
     * @return array
     */
    public function getRefundState($order)
    {
        $refund = '0'; //默认不允许退款退货
        $order_state = $order['order_state']; //订单状态
        $model_trade = Model('trade');
        $order_shipped = $model_trade->getOrderState('order_shipped'); //30:已发货
        $order_completed = $model_trade->getOrderState('order_completed'); //40:已收货
        switch ($order_state) {
            case $order_shipped:
                $payment_code = $order['payment_code']; //支付方式
                if ($payment_code != 'offline') { //货到付款订单在没确认收货前不能退款退货
                    $refund = '1';
                }
                break;
            case $order_completed:
                $order_refund = $model_trade->getMaxDay('order_refund'); //15:收货完成后可以申请退款退货
                $delay_time = $order['delay_time'] + 60 * 60 * 24 * $order_refund;
                if ($delay_time > time()) {
                    $refund = '1';
                }
                break;
            default:
                $refund = '0';
                break;
        }

        return $refund;
    }

    /**
     * 向模板页面输出退款退货状态
     *
     * @param
     * @return array
     */
    public function getRefundStateArray($type = 'all')
    {
        Language::read('refund');
        $state_array = array(
            '1' => Language::get('refund_state_confirm'),
            '2' => Language::get('refund_state_yes'),
            '3' => Language::get('refund_state_no'),
        ); //卖家处理状态:1为待审核,2为同意,3为不同意
        Tpl::output('state_array', $state_array);

        $admin_array = array(
            '1' => '处理中',
            '2' => '待处理',
            '3' => '已完成',
        ); //确认状态:1为买家或卖家处理中,2为待平台管理员处理,3为退款退货已完成
        Tpl::output('admin_array', $admin_array);

        $state_data = array(
            'seller' => $state_array,
            'admin' => $admin_array,
        );
        if ($type == 'all') {
            return $state_data;
        }
//返回所有
        return $state_data[$type];
    }

    /**
     * 退货退款数量
     *
     * @param array $condition
     * @return int
     */
    public function getRefundReturnCount($condition)
    {
        return $this->table('refund_return')->where($condition)->count();
    }

    /**
     * 取得退款数量
     * @param unknown $condition
     */
    public function getRefundCount($condition)
    {
        $condition['refund_type'] = 1;
        return $this->table('refund_return')->where($condition)->count();
    }

    /**
     * 取得退款退货数量
     * @param unknown $condition
     */
    public function getReturnCount($condition)
    {
        $condition['refund_type'] = 2;
        return $this->table('refund_return')->where($condition)->count();
    }

    /*
     *  获得退货退款的店铺列表
     *  @param array $complain_list
     *  @return array
     */
    public function getRefundStoreList($list)
    {
        $store_ids = array();
        if (!empty($list) && is_array($list)) {
            foreach ($list as $key => $value) {
                $store_ids[] = $value['store_id']; //店铺编号
            }
        }
        $field = 'store_id,store_name,member_id,member_name,seller_name,store_company_name,store_qq,store_ww,store_phone,store_domain';
        return Model('store')->getStoreMemberIDList($store_ids, $field);
    }

}
