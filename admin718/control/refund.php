<?php
/**
 * 退款管理
 **/

defined('In718Shop') or exit('Access Invalid!');
class refundControl extends SystemControl{
	const EXPORT_SIZE = 1000;
	public function __construct(){
		parent::__construct();
		$model_refund = Model('refund_return');
		$model_refund->getRefundStateArray();
	}

	/**
	 * 待处理列表
	 */
	public function refund_manageOp() {
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['refund_state'] = '2';//状态:1为处理中,2为待管理员处理,3为已完成

		$keyword_type = array('order_sn','refund_sn','store_name','buyer_name','goods_name');
		/*if (trim($_GET['key']) != '' && in_array($_GET['type'],$keyword_type)) {
			$type = $_GET['type'];
			$condition[$type] = array('like','%'.$_GET['key'].'%');
		}*/
        //lxs
        $str=$_GET['key'];
        if(trim($str) != '' && in_array($_GET['type'],$keyword_type)) {
           $type=$_GET['type'];
            $str = Model('search')->decorateSearch_pre($str);
            $condition[$type] = array('like', '%'.$str.'%');
        }
        //lxs
		if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != '') {
			$add_time_from = strtotime(trim($_GET['add_time_from']));
			$add_time_to = strtotime(trim($_GET['add_time_to']));
			if ($add_time_from !== false || $add_time_to !== false) {
				$condition['add_time'] = array('time',array($add_time_from,$add_time_to));
			}
		}
		$refund_list = $model_refund->getRefundList($condition,10);

		Tpl::output('refund_list',$refund_list);
		Tpl::output('show_page',$model_refund->showpage());
		Tpl::showpage('refund_manage.list');
	}

/**
	 * jinp0802-待处理列表
	 */
	public function refund_manage_jpOp() {
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['refund_state'] = '2';//状态:1为处理中,2为待管理员处理,3为已完成

		$keyword_type = array('order_sn','refund_sn','store_name','buyer_name','goods_name');
		/*if (trim($_GET['key']) != '' && in_array($_GET['type'],$keyword_type)) {
			$type = $_GET['type'];
			$condition[$type] = array('like','%'.$_GET['key'].'%');
		}*/
        //lxs
        $str=$_GET['key'];
        if(trim($str) != '' && in_array($_GET['type'],$keyword_type)) {
            $type=$_GET['type'];
            $str = Model('search')->decorateSearch_pre($str);
            $condition[$type] = array('like', '%'.$str.'%');
        }
        //lxs
		if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != '') {
			$add_time_from = strtotime(trim($_GET['add_time_from']));
			$add_time_to = strtotime(trim($_GET['add_time_to']));
			if ($add_time_from !== false || $add_time_to !== false) {
				$condition['add_time'] = array('time',array($add_time_from,$add_time_to));
			}
		}
		$refund_list = $model_refund->getRefundList($condition,10);

		Tpl::output('refund_list',$refund_list);
		Tpl::output('show_page',$model_refund->showpage());
		Tpl::showpage('order.crossborder_state_new_jp0802');
	}

	/**
	 * 所有记录
	 */
	public function refund_allOp() {
		$model_refund = Model('refund_return');
		$condition = array();

		$keyword_type = array('order_sn','refund_sn','store_name','buyer_name','goods_name');
		/*if (trim($_GET['key']) != '' && in_array($_GET['type'],$keyword_type)) {
			$type = $_GET['type'];
			$condition[$type] = array('like','%'.$_GET['key'].'%');
		}*/
        //lxs
        $str=$_GET['key'];
        if(trim($str) != '' && in_array($_GET['type'],$keyword_type)) {
            $type=$_GET['type'];
            $str = Model('search')->decorateSearch_pre($str);
            $condition[$type] = array('like', '%'.$str.'%');
        }
        //lxs
		if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != '') {
			$add_time_from = strtotime(trim($_GET['add_time_from']));
			$add_time_to = strtotime(trim($_GET['add_time_to']));
			if ($add_time_from !== false || $add_time_to !== false) {
				$condition['add_time'] = array('time',array($add_time_from,$add_time_to));
			}
		}
		$refund_list = $model_refund->getRefundList($condition,10);
		Tpl::output('refund_list',$refund_list);
		Tpl::output('show_page',$model_refund->showpage());
		Tpl::showpage('refund_all.list');
	}

	/**
	 * 退款处理页
	 *
	 */
	public function editOp() {
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['refund_id'] = intval($_GET['refund_id']);
		
		//jinp 0801 --S--
		//$model_pd = Model('predeposit');
		$model_member = Model('member');
		$model_cw = Model('cw');

		$model_order = Model('order');

		//$member_info = $model_member->getMemberInfoByID($_GET['refund_id']);
		//jinp 0801 --E--

		$refund_list = $model_refund->getRefundList($condition);//取refund_return表里的数据
		$refund = $refund_list[0];
        //获取一卡通卡号
        $member_id=$refund['buyer_id'];
        $cardno=Model()->table('member_card')->where(array('member_id'=>$member_id))->find();
        $refund['cardno']=$cardno['cardno'];
        //add
        $refund['gonghao']=$cardno['gonghao'];
        //获取管理员信息
        $admin=$this->getAdminInfo();
        $con['admin_id']=$admin['id'];
        $admin_info=Model('admin')->infoAdmin($con);
        $refund['admin_name']=$admin_info['admin_name'];
        $refund['admin_id'] = $admin_info['admin_id'];

		if (chksubmit()) {
			if ($refund['refund_state'] != '2') {//检查状态,防止页面刷新不及时造成数据错误
				showMessage(Language::get('nc_common_save_fail'));
			}
			$order_id = $refund['order_id'];
            $tenantId = 42;

			$refund_array = array();
			$refund_array['admin_time'] = time();
			$refund_array['refund_state'] = '3';//状态:1为处理中,2为待管理员处理,3为已完成
			$refund_array['admin_message'] = $_POST['admin_message'];
			if ($_POST['state'] == 1) {
				$this->refund_brokerage($_GET['refund_id']);
			}

			$state = $model_refund->editOrderRefund_jp($refund);
			if ($state) {
			    $model_refund->editRefundReturn($condition, $refund_array);
				$refund_id = intval($_GET['refund_id']);
                $order_info = $model_order->getOrderInfo(array('order_id' => $order_id), [], 'order_sn,order_amount,refund_amount');
                if($refund['goods_id'] == 0){//部分退款或者部分退货，根据当前退款单$refund中的goods_id进行查询，如果goods_id为0，则认为是全部退款，即为全部退货。
                    $data = [
                        "tenantId" => $tenantId,
                        "orderSn" => $order_info['order_sn'],//订单编号  orderSn    String类型
                        "returnStatus" => "1",//退货状态 returnStatus  String类型    退货状态 0部分  如果部分退货需要传参商品编码  1全部 2无
                        "returnTime" => date('Y-m-d H:i:s', time()),//退货时间  returnTime   String类型  2021-01-01 12:13:14
                        "orderStatus" => "6"//订单状态  orderStatus  String类型  6已退货
                    ];
                    $model_cw->cwOrderRefund($data);//全部退货
                }else{//如果goods_id不为0，则认为是单品退款，通过$refund信息中的order_id定位到订单信息，
                    if ($order_info['order_amount'] == $order_info['refund_amount']) {//如果订单信息中order_amount==refund_amount，即退款金额等于订单实际支付金额，做全部退货处理；
                        $data = [
                            "tenantId" => $tenantId,
                            "orderSn" => $order_info['order_sn'],//订单编号  orderSn    String类型
                            "returnStatus" => "1",//退货状态 returnStatus  String类型    退货状态 0部分  如果部分退货需要传参商品编码  1全部 2无
                            "returnTime" => date('Y-m-d H:i:s', time()),//退货时间  returnTime   String类型  2021-01-01 12:13:14
                            "orderStatus" => "6"//订单状态  orderStatus  String类型  6已退货
                        ];
                        $model_cw->cwOrderRefund($data);//全部退货
                    }else{//如果order_amount!=refund_amount，即退款金额不等于订单实际支付金额，通过检索$refund信息中goods_id和order_id定位到order_goods信息，
                        $order_goods_info = $model_order->getOrderGoodsInfo(array('order_id'=>$order_id,'goods_id'=>$refund['goods_id']),'goods_pay_price');
                        $goods_pay_price = $order_goods_info['goods_pay_price'];
                        if($refund['refund_amount'] == $goods_pay_price){//如果$refund信息中的refund_amount等于order_goods中的goods_pay_price，即退款金额等于该商品的支付金额，做部分退货处理；
                    $goods_list = Model('order')->getOrderGoodsList(array('order_id' => $order_id), 'goods_id,goods_serial as goodsCode');
                    if ($goods_list) {
                        foreach ($goods_list as $key => $value) {
                            $refund_info = Model('refund_return')->getRefundReturnInfo(array('refund_id' => $refund_id, 'order_id' => $order_id, 'goods_id' => $value['goods_id'], 'seller_state' => 2, 'refund_state' => 3));
                            if ($refund_info) {
                                unset($goods_list[$key]['goods_id']);
                            } else {
                                unset($goods_list[$key]);
                            }
                        }
                    }
                    if ($goods_list) {
                        $data = [
                                    "tenantId" => $tenantId,
                            "orderSn" => $order_info['order_sn'],//订单编号  orderSn    String类型
                            "returnStatus" => "0",//退货状态 returnStatus  String类型    退货状态 0部分  如果部分退货需要传参商品编码  1全部 2无
                            "returnTime" => date('Y-m-d H:i:s', time()),//退货时间  returnTime   String类型  2021-01-01 12:13:14
                            "orderStatus" => "6",//订单状态  orderStatus  String类型  6已退货
                            "goodsList" => array_values($goods_list)
                        ];
                    }
                            $model_cw->cwOrderRefund($data);//部分退货
                        }else{//如果$refund信息中的refund_amount不等于order_goods中的goods_pay_price，即退款金额不等于该商品的实际支付金额，即修改了单品退款金额，做部分退款处理。
                            $goods_list = Model('order')->getOrderGoodsList(array('order_id' => $order_id), 'goods_id,goods_serial as goodsCode');
                            if ($goods_list) {
                                foreach ($goods_list as $key => $value) {
                                    $refund_info = Model('refund_return')->getRefundReturnInfo(array('refund_id' => $refund_id, 'order_id' => $order_id, 'goods_id' => $value['goods_id']), 'refund_amount,reason_info');
                                    $goods_list[$key]['backMoney'] = $refund_info['refund_amount'];
                                    $goods_list[$key]['refundReason'] = $refund_info['reason_info'];
                                    if ($refund_info) {
                                        unset($goods_list[$key]['goods_id']);
                                    } else {
                                        unset($goods_list[$key]);
                                    }
                }
                            }
                            $data = [
                                "tenantId" => $tenantId,
                                "orderSn" => $order_info['order_sn'],
                                "returnStatus" => "2",//退货状态 returnStatus  String类型    退货状态 0部分  如果部分退货需要传参商品编码  1全部 2无
                                "refundStatus" => "2",//是否退货  refundStatus   String类型  0无退货1有退货2仅退款
                                "returnTime" => date('Y-m-d H:i:s', time()),//"2021-06-01 10:07:14",
                                "orderStatus" => "8",//订单状态  orderStatus  String类型  8已退款
                                "goodsList" => array_values($goods_list)
                            ];
                            $model_cw->cwOrderBack($data);//部分退款
                        }
                    }
                }

			    //添加打印功能
			    $model_yundayin = Model('yundayin');
			    $refund_order_info=Model()->table('order')->where(array('order_id'=>$order_id))->find();
			    $model_yundayin-> _yundayin($refund_order_info['order_sn'],$is_refund = 1,$refund['goods_id']);
			    
			    //jinp 0802 --S--
			    $member_info = $model_member->getMemberInfoByID($refund['buyer_id']);

			    //$order_info = $model_order->getOrderInfo(array('order_id'=> $refund['order_id']));

			   // $model_pd->changePd('order_pay',$refund_array);

				/****  mx  这一段放到refund_return.model.php
			    $pdc_sn = $model_pd->makeSn();
    			$data = array();
    			$data['pdc_sn'] = $pdc_sn;
	    		$data['pdc_member_id'] = $member_info['member_id'];
	    		$data['pdc_member_name'] = $member_info['member_name'];
	    		$data['pdc_amount'] = $refund['refund_amount']-$order_info['rcb_amount']-$order_info['pd_amount'] ;
	    		//$data['pdc_amount'] = $data['pdc_amount']-$order_info['pd_amount'];//此时$order_info['pd_amount']已经为0了
	    		$data['pdc_bank_name'] = "支付宝/微信";
	    		$data['pdc_bank_no'] = $refund['order_sn'];
	    		$data['pdc_bank_user'] = $member_info['member_name'];
	    		$data['pdc_add_time'] = TIMESTAMP;
	    		$data['pdc_payment_state'] = 0;
	    		$data['order_sn'] = $refund['order_sn'];
	    		$data['pay_sn'] = $order_info['pay_sn'];
	    		$data['refund_id'] = intval($_GET['refund_id']);
	    		$insert = $model_pd->addPdCash($data);*/

    			// 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $refund['buyer_id'];
                $param['param'] = array(
                    'refund_url' => urlShop('member_refund', 'view', array('refund_id' => $refund['refund_id'])),
                    'refund_sn' => $refund['refund_sn']
                );
                QueueClient::push('sendMemberMsg', $param);

			    $this->log('退款确认，退款编号'.$refund['refund_sn']);

			   	//微信通知
                $buyer_info = Model('member')->getMemberInfo(array('member_id' => $refund['buyer_id']),'member_wxopenid');
                $output = Model('wxsend_admin_refund')->sendMessage($buyer_info['member_wxopenid'],$refund,1,$refund_array);
			 
				 // jinp 0730 S 添加提现功能代码

			    
				//验证支付密码
				//if (md5($_POST['password']) != $member_info['member_paypwd']) {
				//    showDialog('支付密码错误','','error');
				//}
				//验证金额是否足够
				//if (floatval($member_info['available_predeposit']) < $pdc_amount){
				//	showDialog(Language::get('predeposit_cash_shortprice_error'),'index.php?act=predeposit&op=pd_cash_list','error');
				//}
				//try {
				    //$model_pd->beginTransaction();
				    //$pdc_sn = $model_pd->makeSn();
	    			
	    			//if (!$insert) {
	    			//    throw new Exception(Language::get('predeposit_cash_add_fail'));
	    			//}
	    			//冻结可用预存款
	    			//$data = array();
	    			//$data['member_id'] = $member_info['member_id'];
	    			//$data['member_name'] = $member_info['member_name'];
	    			//$data['amount'] = $pdc_amount;
	    			//$data['order_sn'] = $pdc_sn;
	    			//$model_pd->changePd('cash_apply',$data);
	    			//$model_pd->commit();
	    			//showDialog(Language::get('predeposit_cash_add_success'),'index.php?act=predeposit&op=pd_cash_list','succ','CUR_DIALOG.close()');
				//} catch (Exception $e) {
				 //   $model_pd->rollback();
				//    showDialog($e->getMessage(),'index.php?act=predeposit&op=pd_cash_list','error');
				//}

				//$this->log('退款再次确认，jp-1退款编号'.$refund['refund_sn']);



			    // jinp 0730 E

			    //showMessage('why is it not success jp-0802?','index.php?act=refund&op=refund_manage');
			    showMessage(Language::get('nc_common_save_succ'),'index.php?act=refund&op=refund_manage');
				//showMessage(Language::get('nc_common_save_succ'),'index.php?act=member_security&op=auth&type=pd_cash');

			} else {
				showMessage(Language::get('nc_common_save_fail'));
			}
		}
		Tpl::output('refund',$refund);

		Tpl::output('member_info',$member_info);

		$info['buyer'] = array();
	    if(!empty($refund['pic_info'])) {
	        $info = unserialize($refund['pic_info']);
	    }
		Tpl::output('pic_list',$info['buyer']);
		Tpl::showpage('refund.edit');
	}

public function edit_disagreeOp() {
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['refund_id'] = intval($_GET['refund_id']);
		$refund_list = $model_refund->getRefundList($condition);
		$refund = $refund_list[0];
		if (chksubmit()) {
			if ($refund['refund_state'] != '2') {//检查状态,防止页面刷新不及时造成数据错误
				showMessage(Language::get('nc_common_save_fail'));
			}
			$order_id = $refund['order_id'];
			$refund_array = array();
			$refund_array['admin_time'] = time();
			$refund_array['refund_state'] = '3';//状态:1为处理中,2为待管理员处理,3为已完成
			//jinp1012修改seller_state
			$refund_array['seller_state'] = '3';
			$refund_array['admin_message'] = $_POST['admin_message'];
			$state = $model_refund->editOrderRefund_disagree($refund);
			if ($state) {
			    $model_refund->editRefundReturn($condition, $refund_array);

    			// 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $refund['buyer_id'];
                $param['param'] = array(
                    'refund_url' => urlShop('member_refund', 'view', array('refund_id' => $refund['refund_id'])),
                    'refund_sn' => $refund['refund_sn']
                );
                QueueClient::push('sendMemberMsg', $param);
				
			   	//微信通知
				$buyer_info = Model('member')->getMemberInfo(array('member_id' => $refund['buyer_id']),'member_wxopenid');
				$output = Model('wxsend_admin_refund')->sendMessage($buyer_info['member_wxopenid'],$refund,0,$refund_array);
				

			    $this->log('退款平台不同意，退款编号'.$refund['refund_sn']);
				showMessage(Language::get('nc_common_save_succ'),'index.php?act=refund&op=refund_manage');
			} else {
				showMessage(Language::get('nc_common_save_fail'));
			}
		}
		Tpl::output('refund',$refund);
		$info['buyer'] = array();
	    if(!empty($refund['pic_info'])) {
	        $info = unserialize($refund['pic_info']);
	    }
		Tpl::output('pic_list',$info['buyer']);
		Tpl::showpage('refund.edit_disagree');
	}
	/**
	 * 退款记录查看页
	 *
	 */
	public function viewOp() {
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['refund_id'] = intval($_GET['refund_id']);
		$refund_list = $model_refund->getRefundList($condition);
		$refund = $refund_list[0];
		Tpl::output('refund',$refund);
		$info['buyer'] = array();
	    if(!empty($refund['pic_info'])) {
	        $info = unserialize($refund['pic_info']);
	    }
		Tpl::output('pic_list',$info['buyer']);
		Tpl::showpage('refund.view');
	}

	/**
	 * 退款退货原因
	 */
	public function reasonOp() {
		$model_refund = Model('refund_return');
		$condition = array();

		$reason_list = $model_refund->getReasonList($condition,10);
		Tpl::output('reason_list',$reason_list);
		Tpl::output('show_page',$model_refund->showpage());

		Tpl::showpage('refund_reason.list');
	}

	/**
	 * 新增退款退货原因
	 *
	 */
	public function add_reasonOp() {
		$model_refund = Model('refund_return');
		if (chksubmit()) {
		    $reason_array = array();
		    $reason_array['reason_info'] = $_POST['reason_info'];
		    $reason_array['sort'] = intval($_POST['sort']);
		    $reason_array['update_time'] = time();

		    $state = $model_refund->addReason($reason_array);
			if ($state) {
			    $this->log('新增退款退货原因，编号'.$state);
				showMessage(Language::get('nc_common_save_succ'),'index.php?act=refund&op=reason');
			} else {
				showMessage(Language::get('nc_common_save_fail'));
			}
		}
		Tpl::showpage('refund_reason.add');
	}

	/**
	 * 编辑退款退货原因
	 *
	 */
	public function edit_reasonOp() {
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['reason_id'] = intval($_GET['reason_id']);
		$reason_list = $model_refund->getReasonList($condition);
		$reason = $reason_list[$condition['reason_id']];
		if (chksubmit()) {
		    $reason_array = array();
		    $reason_array['reason_info'] = $_POST['reason_info'];
		    $reason_array['sort'] = intval($_POST['sort']);
		    $reason_array['update_time'] = time();
			$state = $model_refund->editReason($condition, $reason_array);
			if ($state) {
			    $this->log('编辑退款退货原因，编号'.$condition['reason_id']);
				showMessage(Language::get('nc_common_save_succ'),'index.php?act=refund&op=reason');
			} else {
				showMessage(Language::get('nc_common_save_fail'));
			}
		}
		Tpl::output('reason',$reason);
		Tpl::showpage('refund_reason.edit');
	}

	/**
	 * 删除退款退货原因
	 *
	 */
	public function del_reasonOp() {
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['reason_id'] = intval($_GET['reason_id']);
		$state = $model_refund->delReason($condition);
		if ($state) {
		    $this->log('删除退款退货原因，编号'.$condition['reason_id']);
		    showMessage(Language::get('nc_common_del_succ'),'index.php?act=refund&op=reason');
		} else {
		    showMessage(Language::get('nc_common_del_fail'));
		}
	}
	
	/**
     * 微信退款
     *
     */
    public function wxpayOp() {
        $result = array('state'=>'false','msg'=>'参数错误，微信退款失败');
        $refund_id = intval($_GET['refund_id']);
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = $refund_id;
        $condition['refund_state'] = '1';
        $detail_array = $model_refund->getDetailInfo($condition);//退款详细
        if(!empty($detail_array) && in_array($detail_array['refund_code'],array('wxpay','wx_jsapi','wx_saoma'))) {
            $order = $model_refund->getPayDetailInfo($detail_array);//退款订单详细
            $refund_amount = $order['pay_refund_amount'];//本次在线退款总金额
            if ($refund_amount > 0) {
                $wxpay = $order['payment_config'];
                define('WXPAY_APPID', $wxpay['appid']);
                define('WXPAY_MCHID', $wxpay['mchid']);
                define('WXPAY_KEY', $wxpay['key']);
                $total_fee = $order['pay_amount']*100;//微信订单实际支付总金额(在线支付金额,单位为分)
                $refund_fee = $refund_amount*100;//本次微信退款总金额(单位为分)
                $api_file = BASE_PATH.DS.'api'.DS.'refund'.DS.'wxpay'.DS.'WxPay.Api.php';
                include $api_file;
                $input = new WxPayRefund();
                $input->SetTransaction_id($order['trade_no']);//微信订单号
                $input->SetTotal_fee($total_fee);
                $input->SetRefund_fee($refund_fee);
                $input->SetOut_refund_no($detail_array['batch_no']);//退款批次号
                $input->SetOp_user_id(WxPayConfig::MCHID);
                $data = WxPayApi::refund($input);
                if(!empty($data) && $data['return_code'] == 'SUCCESS') {//请求结果
                    if($data['result_code'] == 'SUCCESS') {//业务结果
                        $detail_array = array();
                        $detail_array['pay_amount'] = ncPriceFormat($data['refund_fee']/100);
                        $detail_array['pay_time'] = time();
                        $model_refund->editDetail(array('refund_id'=> $refund_id), $detail_array);
                        $result['state'] = 'true';
                        $result['msg'] = '微信成功退款:'.$detail_array['pay_amount'];
                        
                        $refund = $model_refund->getRefundReturnInfo(array('refund_id'=> $refund_id));
                        $consume_array = array();
                        $consume_array['member_id'] = $refund['buyer_id'];
                        $consume_array['member_name'] = $refund['buyer_name'];
                        $consume_array['consume_amount'] = $detail_array['pay_amount'];
                        $consume_array['consume_time'] = time();
                        $consume_array['consume_remark'] = '微信在线退款成功（到账有延迟），退款退货单号：'.$refund['refund_sn'];
                        QueueClient::push('addConsume', $consume_array);
                    } else {
                        $result['msg'] = '微信退款错误,'.$data['err_code_des'];//错误描述
                    }
                } else {
                    $result['msg'] = '微信接口错误,'.$data['return_msg'];//返回信息
                }
            }
        }
        exit(json_encode($result));
    }

    /**
     * 支付宝退款
     *
     */
    public function alipayOp() {
        $refund_id = intval($_GET['refund_id']);
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = $refund_id;
        $condition['refund_state'] = '1';
        $detail_array = $model_refund->getDetailInfo($condition);//退款详细
        if(!empty($detail_array) && $detail_array['refund_code'] == 'alipay') {
            $order = $model_refund->getPayDetailInfo($detail_array);//退款订单详细
            $refund_amount = $order['pay_refund_amount'];//本次在线退款总金额
            if ($refund_amount > 0) {
                $payment_config = $order['payment_config'];
                $alipay_config = array();
                $alipay_config['seller_email'] = $payment_config['alipay_account'];
                $alipay_config['partner'] = $payment_config['alipay_partner'];
                $alipay_config['key'] = $payment_config['alipay_key'];
                $api_file = BASE_PATH.DS.'api'.DS.'refund'.DS.'alipay'.DS.'alipay.class.php';
                include $api_file;
                $alipaySubmit = new AlipaySubmit($alipay_config);
                $parameter = getPara($alipay_config);
                $batch_no = $detail_array['batch_no'];
                $b_date = substr($batch_no,0,8);
                if($b_date != date('Ymd')) {
                    $batch_no = date('Ymd').substr($batch_no, 8);//批次号。支付宝要求格式为：当天退款日期+流水号。
                    $model_refund->editDetail(array('refund_id'=> $refund_id), array('batch_no'=> $batch_no));
                }
                $parameter['batch_no'] = $batch_no;
                $parameter['detail_data'] = $order['trade_no'].'^'.$refund_amount.'^协商退款';//数据格式为：原交易号^退款金额^理由
                $pay_url = $alipaySubmit->buildRequestParaToString($parameter);
                @header("Location: ".$pay_url);
            }
        }
    }
	

	/**
	 * 导出
	 *
	 */
	public function export_step1Op(){
		$lang	= Language::getLangContent();
		$model_order = Model('refund_return');
		$condition	= array();
		$condition['refund_type']='1';//1为退款，2为退货
        $keyword_type = array('order_sn','refund_sn','store_name','buyer_name','goods_name');
        if (trim($_GET['key']) != '' && in_array($_GET['type'],$keyword_type)) {
            $type = $_GET['type'];
            $condition[$type] = array('like','%'.$_GET['key'].'%');
        }
        if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != '') {
            $add_time_from = strtotime(trim($_GET['add_time_from']));
            $add_time_to = strtotime(trim($_GET['add_time_to']));
            if ($add_time_from !== false || $add_time_to !== false) {
                $condition['add_time'] = array('time',array($add_time_from,$add_time_to));
            }
        }
		if (!is_numeric($_GET['curpage'])){
			$count = $model_order->getRefundCount($condition);  //获取退款的数量
			$array = array();
			/*if ($count > self::EXPORT_SIZE ){	//显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}
				Tpl::output('list',$array);
				Tpl::output('murl','index.php?act=refund&op=refund_all');
				Tpl::showpage('export.excel');
			}else{*/	//如果数量小，直接下载
				$data = $model_order->getRefundList($condition,'','*','order_sn desc',$count);
				$this->createExcel($data);
			//}
		}else{	//下载
			$limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
			$limit2 = self::EXPORT_SIZE;
			$data = $model_order->getRefundList($condition,'','*','order_sn desc',"{$limit1},{$limit2}");
			$this->createExcel($data);
		}
	}

	/**
	 * 退款佣金处理
	 * @param  [type] $refund_id [description]
	 * @return [type]            [description]
	 */
	private function refund_brokerage($refund_id)
	{
    	$condition = array();
    	$condition['refund_id'] = $refund_id;
    	$model_refund = Model('refund_return');
    	$model_pdr = Model('predeposit');
    	$refund_info = $model_refund->getRefundReturnInfo($condition,'order_id,refund_sn,order_goods_id,goods_num,refund_amount,commis_rate');
    	//order_id获取订单信息
    	$order_info1 = model()->table('order')->field('order_state,order_sn')->where(array('order_id'=>$refund_info['order_id']))->find();
    	if ($order_info1['order_state'] == 40) {
    		$order_info = model()->table('order_common')->field('reciver_ziti_id')->where(array('order_id'=>$refund_info['order_id']))->find();
	    	//var_dump($order_info);die;
	    	$ziti_id = $order_info['reciver_ziti_id'];
	    	$on = 'ziti_address.gl_id=member.groupbuy_leader_id';
	    	$leader_info = model()->table('ziti_address,member')->field('member.member_id,member.member_name')->join('left')->on($on)->where(array('ziti_address.address_id'=>$ziti_id))->find();
	    	if (empty($leader_info['member_id'])) {
	    		return;//团长信息不存在返回
	    	}
	    	if ($refund_info['order_goods_id'] == 0) {
	    		//获取订单商品表信息
	    		$order_goods_list = model()->table('order_goods')->field('goods_price*goods_num*commis_rate/100 AS commis,rec_id')->where(array('order_id'=>$refund_info['order_id']))->select();
	    		foreach ($order_goods_list as $key => $value) {
	    			$data2 = array();
			        $data2['pdr_sn'] = $model_pdr->makeSn();
			        $data2['pdr_member_id'] = $leader_info['member_id'];
			        $data2['pdr_member_name'] = $leader_info['member_name'];
			        $data2['pdr_amount'] -= $value['commis'];
			        $data2['pdr_payment_code'] = 'refund_brokerage';
			        $data2['pdr_payment_name'] = '退款佣金';
			        $data2['pdr_payment_state'] = 1;
			        $data2['pdr_trade_sn'] = $order_info1['order_sn'];
			        $data2['pdr_add_time'] = TIMESTAMP;
			        $insert = $model_pdr->addPdRecharge($data2);
			        if ($insert) {
			        	$data1['member_id'] = $leader_info['member_id'];
			        	$data1['member_name'] = $leader_info['member_name'];
			        	$data1['amount'] = round($value['commis'],2);
			        	$data1['order_goods_id'] = $value['rec_id'];
			        	$model_pdr->changePd('refund_brokerage',$data1);
			        }
	    		}
	    	}else{
	    		//获取订单商品表信息
	    		$order_goods_info = model()->table('order_goods')->field('goods_price*goods_num*commis_rate/100 AS commis')->where(array('rec_id'=>$refund_info['order_goods_id']))->find();
	    		$data2 = array();
		        $data2['pdr_sn'] = $model_pdr->makeSn();
		        $data2['pdr_member_id'] = $leader_info['member_id'];
		        $data2['pdr_member_name'] = $leader_info['member_name'];
		        $data2['pdr_amount'] -= $order_goods_info['commis'];
		        $data2['pdr_payment_code'] = 'refund_brokerage';
		        $data2['pdr_payment_name'] = '退款佣金';
		        $data2['pdr_payment_state'] = 1;
		        $data2['pdr_trade_sn'] = $refund_info['refund_sn'];
		        $data2['pdr_add_time'] = TIMESTAMP;
		        $insert = $model_pdr->addPdRecharge($data2);
		        if ($insert) {
		        	$data1['member_id'] = $leader_info['member_id'];
		        	$data1['member_name'] = $leader_info['member_name'];
		        	$data1['amount'] = $order_goods_info['commis'];
		        	$data1['order_goods_id'] = $order_goods_info['rec_id'];
		        	$model_pdr->changePd('refund_brokerage',$data1);
		        }
	    	}
    	}
	}

	/**
	 * 生成excel
	 *
	 * @param array $data
	 */
	private function createExcel2($data = array()){
		Language::read('export');
		import('libraries.excel');
		$excel_obj = new Excel();
		$excel_data = array();
		//设置样式
		$excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
		//header
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_ordersn'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_refundsn'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_store_name'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('return_goods_name'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_buyer'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_add_time'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_refund'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'商家审核');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'平台确认');
		//data
		foreach ((array)$data as $k=>$v){
			$tmp = array();
			$tmp[] = array('data'=>'NC'.$v['order_sn']);
			$tmp[] = array('data'=>'NC'.$v['refund_sn']);
			$tmp[] = array('data'=>$v['store_name']);
			$tmp[] = array('data'=>$v['goods_name']);
			$tmp[] = array('data'=>$v['buyer_name']);
			$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['add_time']));
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['refund_amount']));
			//商家审核状态
			if($v['seller_state']=='1')
			{
				$tmp[] = array('data'=>L('refund_state_confirm'));
			}
			if($v['seller_state']=='2')
			{
				$tmp[] = array('data'=>L('refund_state_yes'));
			}
			if ($v['seller_state']=='3')
			{
				$tmp[] = array('data'=>L('refund_state_no'));
			}
			//平台确认
			if($v['seller_state']=='2')
			{
				if($v['refund_state']=='1')
				{
					$tmp[] = array('data'=>'处理中');
				}
				if ($v['refund_state']=='2')
				{
					$tmp[] = array('data'=>'待处理');
				}
				if ($v['refund_state']=='3')
				{
					$tmp[] = array('data'=>'已完成');
				}
			}
			else
			{
				$tmp[] = array('data'=>'无');
			}
			//$tmp[] = array('data'=>$v['refund_state']);
			$excel_data[] = $tmp;
		}
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset(L('refund_add'),CHARSET));
		$excel_obj->generateXML($excel_obj->charset(L('refund_add'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
	}
	/**
	 * 生成excel
	 *
	 * @param array $data
	 */
	private function createExcel($data = array()){
		Language::read('export');
		import('libraries.excel');
		$excel_obj = new Excel();
		$excel_data = array();
		//设置样式
		$excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
		//header
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_ordersn'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_refundsn'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_store_name'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('return_goods_name'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_buyer'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_add_time'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'退款时间');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_refund'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'退货数量');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'商家审核');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'平台确认');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'支付方式');
		// $excel_data[0][] = array('styleid'=>'s_title','data'=>'工号');
		//data
		foreach ((array)$data as $k=>$v){
			$tmp = array();
			$tmp[] = array('data'=>'NC'.$v['order_sn']);
			$tmp[] = array('data'=>'NC'.$v['refund_sn']);
			$tmp[] = array('data'=>$v['store_name']);
			$tmp[] = array('data'=>$v['goods_name']);
			$tmp[] = array('data'=>$v['buyer_name']);
			$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['add_time']));
			if($v['admin_time']>0){
             $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['admin_time']));
			}else{
                $tmp[] = array('data'=>'无');
			}
			
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['refund_amount']));
			$tmp[] = array('data'=>$v['goods_num']);
			//商家审核状态
			if($v['seller_state']=='1')
			{
				$tmp[] = array('data'=>L('refund_state_confirm'));
			}
			if($v['seller_state']=='2')
			{
				$tmp[] = array('data'=>L('refund_state_yes'));
			}
			if ($v['seller_state']=='3')
			{
				$tmp[] = array('data'=>L('refund_state_no'));
			}
			//平台确认
			if($v['seller_state']=='2')
			{
				if($v['refund_state']=='1')
				{
					$tmp[] = array('data'=>'处理中');
				}
				if ($v['refund_state']=='2')
				{
					$tmp[] = array('data'=>'待处理');
				}
				if ($v['refund_state']=='3')
				{
					$tmp[] = array('data'=>'已完成');
				}
			}else{
				$tmp[] = array('data'=>'无');
			}
			$payment_code= Model('order')->getfby_order_id($v['order_id'], 'payment_code');
			if($payment_code=='zihpay'){
			//   $cardno=Model()->table('member_card')->where(array('member_id'=>$v['buyer_id']))->limit(1)->find();
			// if($cardno){
   //          $card_info=Model('card')->getMemberCardInfo($cardno['cardno']);
   //          // var_dump($card_info);die;
            $tmp[] = array('data'=>'zihpay');
   //          $tmp[] = array('data'=>$card_info['PersonalID']);
   //           }else{
   //           	$tmp[] = array('data'=>'zihpay');
   //          	$tmp[] = array('data'=>'');
   //             }
            }else{
            	 $tmp[] = array('data'=>'wxpay');
            	// $tmp[] = array('data'=>'');
            }
			// $tmp[] = array('data'=>$v['refund_state']);
			$excel_data[] = $tmp;
		}
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset(L('refund_add'),CHARSET));
		$excel_obj->generateXML($excel_obj->charset(L('refund_add'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
	}
}
