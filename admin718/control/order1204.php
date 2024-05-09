<?php defined('In718Shop') or exit('Access Invalid!');?>

<?php
/**
 * 交易管理 
 */

defined('In718Shop') or exit('Access Invalid!');
class orderControl extends SystemControl{
    /**
     * 每次导出订单数量
     * @var int
     */
	const EXPORT_SIZE = 1000;

	public function __construct(){
		parent::__construct();
		Language::read('trade');
	}

	public function indexOp(){
	    $model_order = Model('order');
        $condition	= array();
        if($_GET['order_sn']) {
        	$condition['order_sn'] = $_GET['order_sn'];
        }
        if($_GET['store_name']) {
            $condition['store_name'] = $_GET['store_name'];
        }
        if($_GET['reciver_name']) {
        	
        	 $model_ordergood= Model('order_common');
        	 $array=$model_ordergood->where(array('reciver_name'=>$_GET['reciver_name']))->select();
        	  	 foreach ($array as $k => $v) {
      	 }
      	 $arr2 = array_reduce($array, create_function('$result, $v', '$result[] = $v["order_id"];return $result;'));
        	  $condition['order_id'] = array('in',$arr2);
        }
        if(in_array($_GET['order_state'],array('0','10','20','30','40'))){
        	$condition['order_state'] = $_GET['order_state'];
        }
        if($_GET['payment_code']) {
			if($_GET['payment_code'] == 'wxpay'){
				$condition['payment_code'] = array(array('like','%wxpay%'),array('like','%wx_saoma%'),'or');
				//$condition['_op'] = 'or';
			}else{
            $condition['payment_code'] = $_GET['payment_code'];
			}
        }
        if($_GET['buyer_name']) {
            $condition['buyer_name'] = $_GET['buyer_name'];
        }
        // $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_time']);
        // $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_time']);
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$_GET['query_start_time']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$_GET['query_end_time']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_time']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_time']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('between',array($start_unixtime,$end_unixtime));
        }
        //xinzeng支付时间 11.2
        $if_start_time_pay = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_time_pay']);
        $if_end_time_pay = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_time_pay']);
        $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_time_pay']) : null;
        $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_time_pay']): null;
        if ($start_unixtime_pay || $end_unixtime_pay) {
            $condition['payment_time'] = array('time',array($start_unixtime_pay,$end_unixtime_pay));
        }
//xinzeng订单完成时间 0331
        $if_start_time_finish = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_time_finish']);
        $if_end_time_finish = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_time_finish']);
        $start_unixtime_finish = $if_start_time_finish ? strtotime($_GET['query_start_time_finish']) : null;
        $end_unixtime_finish = $if_end_time_finish ? strtotime($_GET['query_end_time_finish']): null;
        if ($start_unixtime_finish || $end_unixtime_finish) {
            $condition['finnshed_time'] = array('time',array($start_unixtime_finish,$end_unixtime_finish));
        }

        $order_list	= $model_order->getOrderList($condition,30);
		
        foreach ($order_list as $order_id => $order_info) {
            //显示取消订单
            $order_list[$order_id]['if_cancel'] = $model_order->getOrderOperateState('system_cancel',$order_info);
            //显示收到货款
            $order_list[$order_id]['if_system_receive_pay'] = $model_order->getOrderOperateState('system_receive_pay',$order_info);
        }
        //显示支付接口列表(搜索)
        $payment_list = Model('payment')->getPaymentOpenList();
        Tpl::output('payment_list',$payment_list);
//echo '<pre>';var_dump($order_list);echo "</pre>";die;
        Tpl::output('order_list',$order_list);
        Tpl::output('show_page',$model_order->showpage());
        Tpl::showpage('order.index');
	}

	/**
	 * 平台订单状态操作
	 *
	 */
	public function change_stateOp() {
        $order_id = intval($_GET['order_id']);
        if($order_id <= 0){
            showMessage(L('miss_order_number'),$_POST['ref_url'],'html','error');
        }
        $model_order = Model('order');

        //获取订单详细
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_info	= $model_order->getOrderInfo($condition);

        if ($_GET['state_type'] == 'cancel') {
            $result = $this->_order_cancel($order_info);
        } elseif ($_GET['state_type'] == 'receive_pay') {
            $result = $this->_order_receive_pay($order_info,$_POST);
        }
        if (!$result['state']) {
            showMessage($result['msg'],$_POST['ref_url'],'html','error');
        } else {
            showMessage($result['msg'],$_POST['ref_url']);
        }
	}

	/**jinp06281808
	 * 平台订单报关状态——（ crossborder_pay_change_state )操作
	 */
	 
	public function crossborder_pay_change_stateOp() {
        $order_id = intval($_GET['order_id']);
        if($order_id <= 0){
            showMessage(L('miss_order_number'),$_POST['ref_url'],'html','error');
        }
        $model_order = Model('order');



        //获取订单详细
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_info	= $model_order->getOrderInfo($condition);


        //jinp07061441

       // if($order_info['order_state'] > 10 ){

                //zb_code 07061036

                $model_order_log = Model('order_log');
                $logdata = $model_order_log->where(array('order_id'=>$order_id,'log_msg'=>array('like','%支付平台交易号%')))->select();
                $tradeNo = explode(' ',$logdata[0]['log_msg']);
                $pay_number = $tradeNo[4];
                //$update_pay_number = array('pay_number' => $pay_number);
                //$update_1 = $model_order->editOrder($update_pay_number,array('order_id'=>$order_id));
                Tpl::output('order_info_jp',array('pay_number'=>$pay_number));
                
             //}

        if ($_GET['crossborder_pay_state'] == '1') {
            $result = $this->crossborder_order_change($order_info);
        }elseif ($_GET['crossborder_pay_state'] == '3') {
            $result = $this->crossborder_order_hand_change_new($order_info,$_POST);
        }elseif ($_GET['crossborder_pay_state'] == '2') {
            $result = $this->crossborder_order_hand_change($order_info,$_POST);
        }
        
        if (!$result['state']) {
            showMessage($result['msg'],$_POST['ref_url'],'html','error');
        } else {
            showMessage($result['msg'],$_POST['ref_url']);
        }
	}

/**jinp 1222
	 * 平台订单微信报关状态——（ crossborder_pay_change_state )操作
	 */
	 
	public function wx_saoma_customOp() {
        $order_id = intval($_GET['order_id']);
        if($order_id <= 0){
            showMessage(L('miss_order_number'),$_POST['ref_url'],'html','error');
        }
        $model_order = Model('order');



        //获取订单详细
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_info	= $model_order->getOrderInfo($condition);


        //jinp07061441

       // if($order_info['order_state'] > 10 ){

                //zb_code 07061036

                $model_order_log = Model('order_log');
                $logdata = $model_order_log->where(array('order_id'=>$order_id,'log_msg'=>array('like','%支付平台交易号%')))->select();
                $tradeNo = explode(' ',$logdata[0]['log_msg']);
                $pay_number = $tradeNo[4];
                //$update_pay_number = array('pay_number' => $pay_number);
                //$update_1 = $model_order->editOrder($update_pay_number,array('order_id'=>$order_id));
                Tpl::output('order_info_jp',array('pay_number'=>$pay_number));
                
             //}

        if ($_GET['crossborder_pay_state'] == '1') {
            $result = $this->crossborder_order_change($order_info);
        }elseif ($_GET['wx_saoma_state'] == '3') {
            $result = $this->wx_saoma_order_renew($order_info,$_POST);
        }elseif ($_GET['wx_saoma_state'] == '2') {
            $result = $this->wx_saoma_order($order_info,$_POST);
        }
        
        if (!$result['state']) {
            showMessage($result['msg'],$_POST['ref_url'],'html','error');
        } else {
            showMessage($result['msg'],$_POST['ref_url']);
        }
	}

	/**
	 * jinp手动推单（copy the 收到货款 _order_receive_pay)
	 * @throws Exception
	 */

	private function wx_saoma_order($order_info, $post) {
	    $order_id = $order_info['order_id'];
	    $model_order = Model('order');
	    $logic_order = Logic('order');
	    //jinp07050848
	    //$if_allow = $model_order->getOrderOperateState('system_receive_pay',$order_info);
	    //if (!$if_allow) {
	    //    return callback(false,'无权操作');
	    //}

	    if (!chksubmit()) {
	        Tpl::output('order_info',$order_info);
	        //显示支付接口列表
	        $payment_list = Model('payment')->getPaymentOpenList();
	        //去掉预存款和货到付款
	        foreach ($payment_list as $key => $value){
	           //if ($value['payment_code'] == 'predeposit' || $value['payment_code'] == 'offline') {
	            //   unset($payment_list[$key]);
	           // }
	        }
	        Tpl::output('payment_list',$payment_list);
	        Tpl::showpage('customs');
	        exit();
	    }
	    $order_list	= $model_order->getOrderList(array('pay_sn'=>$order_info['pay_sn'],'crossborder_pay_state'=>0));

	    //jinp07040834
	    //$order_list	= $model_order->getOrderList(array('pay_sn'=>$order_info['pay_sn'] ));
	    //$result = $logic_order->changeCrossborderOrderHandState($order_list,'system',$this->admin_info['name'],$post);

	    $result = $logic_order->changeCrossborderOrderState($order_list,'system',$this->admin_info['name'],$post);


	    
        //if ($result['state']) {
        //    $this->log('将订单改为推单成功,'.L('order_number').':'.$order_info['order_sn'],1);
        //}
	    return $result;
	}

	/**
	 * jinp手动推单（copy the 收到货款 _order_receive_pay)
	 * @throws Exception
	 */

	private function wx_saoma_order_renew($order_info, $post) {
	    $order_id = $order_info['order_id'];
	    $model_order = Model('order');
	    $logic_order = Logic('order');
	    //jinp07050848
	    //$if_allow = $model_order->getOrderOperateState('system_receive_pay',$order_info);
	    //if (!$if_allow) {
	    //    return callback(false,'无权操作');
	    //}

	    if (!chksubmit()) {
	        Tpl::output('order_info',$order_info);
	        //显示支付接口列表
	        $payment_list = Model('payment')->getPaymentOpenList();
	        //去掉预存款和货到付款
	        foreach ($payment_list as $key => $value){
	           //if ($value['payment_code'] == 'predeposit' || $value['payment_code'] == 'offline') {
	            //   unset($payment_list[$key]);
	           // }
	        }
	        Tpl::output('payment_list',$payment_list);
	        Tpl::showpage('customs_renew');
	        exit();
	    }
	    $order_list	= $model_order->getOrderList(array('pay_sn'=>$order_info['pay_sn'],'crossborder_pay_state'=>0));

	    //jinp07040834
	    //$order_list	= $model_order->getOrderList(array('pay_sn'=>$order_info['pay_sn'] ));
	    //$result = $logic_order->changeCrossborderOrderHandState($order_list,'system',$this->admin_info['name'],$post);

	    $result = $logic_order->changeCrossborderOrderState($order_list,'system',$this->admin_info['name'],$post);


	    
        //if ($result['state']) {
        //    $this->log('将订单改为推单成功,'.L('order_number').':'.$order_info['order_sn'],1);
        //}
	    return $result;
	}

	/**
	 * jinp更改跨境推单状态
	 */
	private function crossborder_order_change($order_info) {
	    $order_id = $order_info['order_id'];
	    $model_order = Model('order');
	    $logic_order = Logic('order');
	    //$if_allow = $model_order->getOrderOperateState('system_cancel',$order_info);
	    //if (!$if_allow) {
	    //   return callback(false,'无权操作');
	    // }

	    $result =  $logic_order->changeCrossborderOrderState($order_info,'system', $this->admin_info['name']);
	    
        if ($result['state']) {
            $this->log(L('order_log_cancel').','.L('order_number').':'.$order_info['order_sn'],1);
        }
        
        return $result;
	}

	/**
	 * jinp手动推单（copy the 收到货款 _order_receive_pay)
	 * @throws Exception
	 */

	private function crossborder_order_hand_change($order_info, $post) {
	    $order_id = $order_info['order_id'];
	    $model_order = Model('order');
	    $logic_order = Logic('order');
	    //jinp07050848
	    //$if_allow = $model_order->getOrderOperateState('system_receive_pay',$order_info);
	    //if (!$if_allow) {
	    //    return callback(false,'无权操作');
	    //}

	    if (!chksubmit()) {
	        Tpl::output('order_info',$order_info);
	        //显示支付接口列表
	        $payment_list = Model('payment')->getPaymentOpenList();
	        //去掉预存款和货到付款
	        foreach ($payment_list as $key => $value){
	           //if ($value['payment_code'] == 'predeposit' || $value['payment_code'] == 'offline') {
	            //   unset($payment_list[$key]);
	           // }
	        }
	        Tpl::output('payment_list',$payment_list);
	        Tpl::showpage('order.crossborder_state');
	        exit();
	    }
	    $order_list	= $model_order->getOrderList(array('pay_sn'=>$order_info['pay_sn'],'crossborder_pay_state'=>0));

	    //jinp07040834
	    //$order_list	= $model_order->getOrderList(array('pay_sn'=>$order_info['pay_sn'] ));
	    //$result = $logic_order->changeCrossborderOrderHandState($order_list,'system',$this->admin_info['name'],$post);

	    $result = $logic_order->changeCrossborderOrderState($order_list,'system',$this->admin_info['name'],$post);


	    
        //if ($result['state']) {
        //    $this->log('将订单改为推单成功,'.L('order_number').':'.$order_info['order_sn'],1);
        //}
	    return $result;
	}

	/**
	 * jinp手动推单(新) 07231606 ****
	 * @throws Exception
	 */

	private function crossborder_order_hand_change_new($order_info, $post) {
	    $order_id = $order_info['order_id'];
	    $model_order = Model('order');
	    $logic_order = Logic('order');
	    //jinp07050848
	    //$if_allow = $model_order->getOrderOperateState('system_receive_pay',$order_info);
	    //if (!$if_allow) {
	    //    return callback(false,'无权操作');
	    //}

	    if (!chksubmit()) {
	        Tpl::output('order_info',$order_info);
	        //显示支付接口列表
	        $payment_list = Model('payment')->getPaymentOpenList();
	        //去掉预存款和货到付款
	        foreach ($payment_list as $key => $value){
	           //if ($value['payment_code'] == 'predeposit' || $value['payment_code'] == 'offline') {
	            //   unset($payment_list[$key]);
	           // }
	        }
	        Tpl::output('payment_list',$payment_list);
	        Tpl::showpage('order.crossborder_state_new');
	        exit();
	    }
	    $order_list	= $model_order->getOrderList(array('pay_sn'=>$order_info['pay_sn'],'crossborder_pay_state'=>0));

	    //jinp07040834
	    //$order_list	= $model_order->getOrderList(array('pay_sn'=>$order_info['pay_sn'] ));
	    //$result = $logic_order->changeCrossborderOrderHandState($order_list,'system',$this->admin_info['name'],$post);

	    $result = $logic_order->changeCrossborderOrderState($order_list,'system',$this->admin_info['name'],$post);


	    
        //if ($result['state']) {
        //    $this->log('将订单改为推单成功,'.L('order_number').':'.$order_info['order_sn'],1);
        //}
	    return $result;
	}


	/**
	 * 系统取消订单
	 */
	private function _order_cancel($order_info) {
	    $order_id = $order_info['order_id'];
	    $model_order = Model('order');
	    $logic_order = Logic('order');
	    $if_allow = $model_order->getOrderOperateState('system_cancel',$order_info);
	    if (!$if_allow) {
	        return callback(false,'无权操作');
	    }
	    $result =  $logic_order->changeOrderStateCancel($order_info,'system', $this->admin_info['name']);
        if ($result['state']) {
            $this->log(L('order_log_cancel').','.L('order_number').':'.$order_info['order_sn'],1);
        }
        return $result;
	}

	/**
	 * 系统收到货款
	 * @throws Exception
	 */
	private function _order_receive_pay($order_info, $post) {
	    $order_id = $order_info['order_id'];
	    $model_order = Model('order');
	    $logic_order = Logic('order');
	    $if_allow = $model_order->getOrderOperateState('system_receive_pay',$order_info);
	    if (!$if_allow) {
	        return callback(false,'无权操作');
	    }

	    if (!chksubmit()) {
	        Tpl::output('order_info',$order_info);
	        //显示支付接口列表
	        $payment_list = Model('payment')->getPaymentOpenList();
	        //去掉预存款和货到付款
	        foreach ($payment_list as $key => $value){
	            if ($value['payment_code'] == 'predeposit' || $value['payment_code'] == 'offline') {
	               unset($payment_list[$key]);
	            }
	        }
	        Tpl::output('payment_list',$payment_list);
	        Tpl::showpage('order.receive_pay');
	        exit();
	    }
	    $order_list	= $model_order->getOrderList(array('pay_sn'=>$order_info['pay_sn'],'order_state'=>ORDER_STATE_NEW));
	    $result = $logic_order->changeOrderReceivePay($order_list,'system',$this->admin_info['name'],$post);
        if ($result['state']) {
            $this->log('将订单改为已收款状态,'.L('order_number').':'.$order_info['order_sn'],1);
        }
	    return $result;
	}

	/**
	 * 查看订单
	 *
	 */
	public function show_orderOp(){
	    $order_id = intval($_GET['order_id']);
	    if($order_id <= 0 ){
	        showMessage(L('miss_order_number'));
	    }
        $model_order	= Model('order');
        $order_info	= $model_order->getOrderInfo(array('order_id'=>$order_id),array('order_goods','order_common','store'));

        //订单变更日志
		$log_list	= $model_order->getOrderLogList(array('order_id'=>$order_info['order_id']));
		Tpl::output('order_log',$log_list);

		//退款退货信息
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['order_id'] = $order_info['order_id'];
        $condition['seller_state'] = 2;
        $condition['admin_time'] = array('gt',0);
        $return_list = $model_refund->getReturnList($condition);
        Tpl::output('return_list',$return_list);

        //退款信息
        $refund_list = $model_refund->getRefundList($condition);
        Tpl::output('refund_list',$refund_list);

		//卖家发货信息
		if (!empty($order_info['extend_order_common']['daddress_id'])) {
		    $daddress_info = Model('daddress')->getAddressInfo(array('address_id'=>$order_info['extend_order_common']['daddress_id']));
		    Tpl::output('daddress_info',$daddress_info);
		}

		Tpl::output('order_info',$order_info);
        Tpl::showpage('order.view');
	}

	/**
	 * 导出
	 *
	 */
	public function export_step1Op(){
		$lang	= Language::getLangContent();

	    $model_order = Model('order');
        $condition	= array();
        if($_GET['order_sn']) {
        	$condition['order_sn'] = $_GET['order_sn'];
        }
        if($_GET['store_name']) {
            $condition['store_name'] = $_GET['store_name'];
        }
        if(in_array($_GET['order_state'],array('0','10','20','30','40'))){
        	$condition['order_state'] = $_GET['order_state'];
        }
        /*if($_GET['payment_code']) {
            $condition['payment_code'] = $_GET['payment_code'];
        }*/
		if($_GET['payment_code']) {
			if($_GET['payment_code'] == 'wxpay'){
				$condition['payment_code'] = array(array('like','%wxpay%'),array('like','%wx_saoma%'),'or');
				$condition['_op'] = 'or';
			}else{
            $condition['payment_code'] = $_GET['payment_code'];
			}
        }
        if($_GET['buyer_name']) {
            $condition['buyer_name'] = $_GET['buyer_name'];
        }
         if($_GET['reciver_name']) {
        	
        	 $model_ordergood= Model('order_common');
        	 $array=$model_ordergood->where(array('reciver_name'=>$_GET['reciver_name']))->select();
        	  	 foreach ($array as $k => $v) {
      	 }
      	 $arr2 = array_reduce($array, create_function('$result, $v', '$result[] = $v["order_id"];return $result;'));
        	  $condition['order_id'] = array('in',$arr2);
        }
         $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$_GET['query_start_time']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$_GET['query_end_time']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_time']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_time']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('between',array($start_unixtime,$end_unixtime));
        }
//xinzeng支付时间 11.2
        $if_start_time_pay = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_time_pay']);
        $if_end_time_pay = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_time_pay']);
        $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_time_pay']) : null;
        $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_time_pay']): null;
        if ($start_unixtime_pay || $end_unixtime_pay) {
            $condition['payment_time'] = array('time',array($start_unixtime_pay,$end_unixtime_pay));
        }

        //xinzeng订单完成时间 0331
        $if_start_time_finish = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_time_finish']);
        $if_end_time_finish = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_time_finish']);
        $start_unixtime_finish = $if_start_time_finish ? strtotime($_GET['query_start_time_finish']) : null;
        $end_unixtime_finish = $if_end_time_finish ? strtotime($_GET['query_end_time_finish']): null;
        if ($start_unixtime_finish || $end_unixtime_finish) {
            $condition['finnshed_time'] = array('time',array($start_unixtime_finish,$end_unixtime_finish));
        }

		if (!is_numeric($_GET['curpage'])){
			$count = $model_order->getOrderCount($condition);
			$array = array();
			/*if ($count > self::EXPORT_SIZE ){	//显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}
				Tpl::output('list',$array);
				Tpl::output('murl','index.php?act=order&op=index');
				Tpl::showpage('export.excel');
			}else{	*///如果数量小，直接下载
				$data = $model_order->getOrderList($condition,'','*','order_id desc',$count);
				$this->createExcel($data);
			//}
		}else{	//下载
			$limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
			$limit2 = self::EXPORT_SIZE;
			$data = $model_order->getOrderList($condition,'','*','order_id desc',"{$limit1},{$limit2}");
			$this->createExcel($data);
		}
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
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_no'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_store'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_buyer'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'订单来源');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_xtimd'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'实际支付金额');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'商品总额(不含税,不含运费)');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'税金');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_yfei'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'支付时间');//新增 支付时间 11.2
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'订单完成时间');//新增 订单完成时间 0401
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_paytype'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_state'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => '备注');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'退款金额');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_storeid'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_buyerid'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_bemail'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'收货人姓名');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'收货人电话');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'商家处理状态');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'平台确认');//xinzeng
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'商家意见');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'管理员意见');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'商品分类');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'买家留言');

		//data
		foreach ((array)$data as $k=>$v){
			$tmp = array();
			$tmp[] = array('data'=>'NC'.$v['order_sn']);
			$tmp[] = array('data'=>$v['store_name']);
			$tmp[] = array('data'=>$v['buyer_name']);
            switch ($v['order_from'])
            {
                case 1:
                    $tmp[] = array('data'=>'PC');
                    break;
                case 2:
                    $tmp[] = array('data'=>'WAP');
                    break;
                case 3:
                    $tmp[] = array('data'=>'ANDROID');
                    break;
                case 4:
                    $tmp[] = array('data'=>'IOS');
                    break;
                default:
                    $tmp[] = array('data'=>'');
            }
			$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['add_time']));
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['order_amount']));
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['goods_amount']));
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['store_tax_total']));
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['shipping_fee']));
			$tmp[] = array('data'=>empty($v['payment_time'])?'':date('Y-m-d H:i:s',$v['payment_time']));//新增支付时间 11.2
			$tmp[] = array('data'=>empty($v['finnshed_time'])?'':date('Y-m-d H:i:s',$v['finnshed_time']));//新增订单完成时间 0401
			$tmp[] = array('data'=>orderPaymentName($v['payment_code']));
//			$tmp[] = array('data'=>orderState($v));
          //订单状态
            if($v['refund_state']=='1'){
                $tmp[] = array('data'=>'部分退款');
            }
            else if($v['refund_state']=='2'){
                $tmp[] = array('data'=>'全部退款');
            }else{
			$tmp[] = array('data'=>orderState($v));
            }
            //备注
            $order_id = $v['order_id'];
            $a = array('order_id' => $order_id);
            $model_refund_return = Model('refund_return');
            $result = $model_refund_return->getRefundReturnList($a);
            if ($result) {
            	 $num = array_search(max($result),$result);
                if ($result[$num]['refund_type'] == 1) {
                    if ($result[$num]['refund_state'] == 1 || $result[$num]['refund_state'] == 2) {
                        $tmp[] = array('data' => '退款中'); //退款中

                    } else if ($result[$num]['refund_state'] == 3) {
                    	if ($result[$num]['seller_state']==2) {
                    		 $tmp[] = array('data' => '退款完成'); //退款完成
                    	}else if ($result[$num]['seller_state']==3) {
                    		 $tmp[] = array('data' => '退款失败'); //退款失败
                    	}

                    } else {
                        $tmp[] = array('data' => '');
                    }
                } elseif ($result[$num]['refund_type'] == 2) {
                    if ($result[$num]['refund_state'] == 1 || $result[$num]['refund_state'] == 2) {
                        $tmp[] = array('data' => '退款退货中');//退款退货中

                    } else if ($result[$num]['refund_state'] == 3) {
           
                        if ($result[$num]['seller_state']==2) {
                        	 $tmp[] = array('data' => '退款退货完成'); //退款退货完成
                        }else if($result[$num]['seller_state']==3){
                        	 $tmp[] = array('data' => '退款退货失败'); //退款退货失败
                        }

                    } else {
                        $tmp[] = array('data' => '');
                    }
                }
            } else {
                $tmp[] = array('data' => '');

            }
            $tmp[] = array('data'=>$v['refund_amount']);
                       
            //$tmp[] = array('data'=>$v['refund_amount']);
			$tmp[] = array('data'=>$v['store_id']);
			$tmp[] = array('data'=>$v['buyer_id']);
			$tmp[] = array('data'=>$v['buyer_email']);
			//收货人姓名
			$name = Model()->query("SELECT reciver_name  FROM `718shop_order_common` where order_id=\"$order_id\" ");
			$tmp[]=array('data'=>$name[0]['reciver_name']);
			//收货人电话
			$info=Model()->query("SELECT reciver_info  FROM `718shop_order_common` where order_id=\"$order_id\" ");
			foreach ($info as $key => $value) {
				if ($value) {
					foreach ($value as $k => $v) {
						$info1=unserialize($v);
						$phone=$info1['phone'];
					}
				}
			}
			$tmp[]=array('data'=>$phone);
            $list=Model()->query("SELECT *  FROM `718shop_refund_return` where order_id=\"$order_id\" ");
            //获取数组中下标最大的
            //$key1 = array_search(max($list),$list);
            //var_dump($key1);die;
            //商家处理状态
            if ($list) {
                $key1 = array_search(max($list),$list);
                if($list[$key1]['seller_state']=='1'){
                    $tmp[] = array('data'=>'待审核');
                }else if($list[$key1]['seller_state']=='2'){
                    $tmp[] = array('data'=>'同意');
                }else if($list[$key1]['seller_state']=='3'){
                    $tmp[] = array('data'=>'不同意');
                }else{
                    $tmp[] = array('data'=>'');
                }
            }else{
                $tmp[] = array('data'=>'');
            }

            //平台确认
            if ($list) {
                $key1 = array_search(max($list),$list);
                if($list[$key1]['seller_state']=='2'){
                    if($list[$key1]['refund_state']=='1'){
                        $tmp[] = array('data'=>'处理中');
                    }else if($list[$key1]['refund_state']=='2'){
                        $tmp[] = array('data'=>'待管理员处理');
                    }else if($list[$key1]['refund_state']=='3'){
                        $tmp[] = array('data'=>'已完成');
                    }else{
                        $tmp[] = array('data'=>'无');
                    }
                }else{
                    $tmp[] = array('data'=>'无');
                }
            }else{
                $tmp[] = array('data'=>'');
            }
            //商家意见
            if ($list) {
                $tmp[]=array('data'=>$list[$key1]['seller_message']);
            }else{
                $tmp[] = array('data'=>'');
            }

            //管理员意见
            if ($list) {
                $tmp[]=array('data'=>$list[$key1]['admin_message']);
            }else{
                $tmp[] = array('data'=>'');
            }

            //商品分类
            $goodsid=Model()->query("SELECT goods_id FROM `718shop_order_goods` WHERE order_id=\"$order_id\"");
            $goods_id=$goodsid[0]['goods_id'];
            $gc_id=Model()->query("SELECT gc_id FROM `718shop_order_goods` WHERE goods_id=\"$goods_id\"");
            $gcid=$gc_id[0]['gc_id'];
            $gc_name=Model()->query("SELECT gc_name FROM `718shop_goods_class` WHERE gc_id=\"$gcid\" LIMIT 10 ");
            $tmp[]=array('data'=>$gc_name[0]['gc_name']);

            //买家留言
            //$orderid=Model()->query("SELECT order_id FROM `718shop_order` WHERE  order_id=\"$order_id\"");
            //$order_id=$orderid[0]['order_id'];
            $order_message=Model()->query("SELECT order_message FROM `718shop_order_common` WHERE  order_id=\"$order_id\"");
            $tmp[]=array('data'=>$order_message[0]['order_message']);
            $excel_data[] = $tmp;
        }
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset(L('exp_od_order'),CHARSET));
		$excel_obj->generateXML($excel_obj->charset(L('exp_od_order'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
	}
}
