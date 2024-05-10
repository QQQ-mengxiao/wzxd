<?php
/**
 * 团长首页
 */
defined('In718Shop') or exit('Access Invalid!');
class homeControl  extends BaseControl{
	/**
	 * 自提点信息
	 */
	public function address_infoOp()
	{
		$address_id = intval($_GET['address_id']);
		$model = Model();
		$on = 'groupbuy_leader.groupbuy_leader_id=ziti_address.gl_id';
		// $field = 'ziti_address.seller_name,ziti_address.area_info,ziti_address.address,ziti_address.open_time_start,ziti_address.open_time_end,groupbuy_leader.phone_num';
		$field = 'ziti_address.*,groupbuy_leader.*';
		$where['ziti_address.address_id'] = $address_id;
		$result = $model->table('ziti_address,groupbuy_leader')->field($field)->join('left')->on($on)->where($where)->find();
    	if (empty($result)) {
    		echo $this->returnMsg(101, '用户信息异常', array());
			die;
    	}
    	echo $this->returnMsg(100, '查询成功', $result);
		die;  
	}

	/**
	 * 团长信息
	 */
	public function ladder_infoOp()
	{
		$tz_id = intval($_GET['tz_id']);
		$model_ziti = Model('ziti_address');
		$condition['gl_id'] = $tz_id;
		$condition['is_current'] = 1;
    	$address_info = $model_ziti->field('*')->where($condition)->find();
    	if (empty($address_info)) {
    		echo $this->returnMsg(101, '用户信息异常', array());
			die; 
    	}
    	if ($address_info['state'] == 2) {
    		$address_info['seller_name'] .= '（休息中）';
    	}
    	echo $this->returnMsg(100, '查询成功', array('address_info'=>$address_info));
		die;  
	}

	/**
	 * 顾客管理页面
	 */
	public function get_customerOp()
	{
		$type = intval($_GET['type']);//1:今天,2昨天,3历史
		$tz_id = intval($_GET['tz_id']);
		$model_ziti = Model('ziti_address');
		if (isset($_GET['address_id'])) {
			$address_id = $address_info['address_id'];
		}else{
			$address_info = $model_ziti->field('address_id')->where(array('gl_id' => $tz_id,'is_current' =>1))->find();
			if (empty($address_info) || !is_array($address_info)) {
				echo $this->returnMsg(101, '自提点信息异常', array());
				die;
			}
			$address_id = $address_info['address_id'];
		}
		$model = Model();
		$today = strtotime(date('Y-m-d',time()));
		$yesterday = $today-86400;
		$where = array();
		$where['order_common.reciver_ziti_id'] = $address_id;
		//如何定义完成的订单
		$where['order.order_state'] = 40;
		$where['order.is_zorder'] = array('neq',0);
		switch ($type) {
			case 1:
				$where['order.add_time'] = array('egt',$today);
				break;
			case 2:				
				$where['order.add_time'] = array('between', $yesterday.','.$today); 
				break;
			default:
				break;
		}
		$on = 'order.order_id=order_common.order_id,order.buyer_id=member.member_id';
		$field = 'member.member_avatar,order.buyer_name,SUM(order.order_amount) AS order_amount,count(*) AS count';
		$model = Model();
		$result_all = $model->table('order_common,order,member')->field($field)->join('left,left')->on($on)->where($where)->group('order.buyer_id')->select();
		$count = count($result_all);
		$result = $model->table('order_common,order,member')->field($field)->join('left,left')->on($on)->where($where)->group('order.buyer_id')->page(10,$count)->select();
		foreach ($result as $key => $value) {
			$result[$key]['member_avatar'] = 'http://117.159.3.227:8088/wzxdtest/data/upload/shop/tzavatar/'.$value['member_avatar'];
		}
		$total_page = $model->gettotalpage();
		$total_num = $model->gettotalnum();
		$data = array('customer_list'=>$result,'total_page'=>$total_page,'total_num'=>$total_num);
		echo $this->returnMsg(100, '查询成功', $data);
	}

	/**
	 * 设置营业时间
	 */
	public function set_open_timeOp()
	{
		$data = array();
		$condition = array();
		//获取营业开始时间、营业结束时间
		if (!empty($_GET['open_time_start'])) {
			$data['open_time_start'] = $_GET['open_time_start'];
		}
		if (!empty($_GET['open_time_end'])) {
			$data['open_time_end'] = $_GET['open_time_end'];
		}
		if (!empty($_GET['address_id'])) {
			$condition['address_id'] = $_GET['address_id'];
		}
		if (empty($data) || empty($condition)) {
			echo $this->returnMsg(101, '参数错误', array());
			die;
		}
		$ziti_id = Model()->table('ziti_address')->where($condition)->update($data);
		if (empty($ziti_id)) {
			echo $this->returnMsg(102, '系统繁忙', array());
			die;
		}
		echo $this->returnMsg(100, '更新成功', array());
		die;
	}


	//今日主推
    public function hotOp()
    {
    	$condition = array(
            'item_type' => 'goods',
            'special_id' => 1
        );
        $mbSpecialModel = Model('mb_special1');
        $hotInfo = $mbSpecialModel->getMbSpecialItemList($condition);
        //var_dump($hotInfo);die;
        foreach ($hotInfo[0]['item_data']['item'] as $key => $value) {
        	$goodsInfo = Model()->table('goods')->field('goods_image')->where(array('goods_id'=>$value['goods_id']))->find();
        	//$hotInfo[0]['item_data']['item'][$key]['goods_image'] = UPLOAD_SITE_URL.'/mobile/special1/s1/'.$value['goods_image'];
        	$hotInfo[0]['item_data']['item'][$key]['goods_image'] = cthumb($goodsInfo['goods_image']);
        }
        echo $this->returnMsg(100, '查询成功', $hotInfo[0]['item_data']['item']);
		die;
    }

	/**
     * 定时处理订单佣金
     * @return [type] [description]
     */
    public function crontabOp()
    {
    	header("content-type:text/html;charset=utf-8");
    	//1.判断是否正在处理请求，第一条数据作为状态
    	$log_info = Model()->table('order_log')->field('state')->where(array('log_id'=>126753))->find();
    	if (empty($log_info)) {
    		echo "数据异常";
    		die;
    	}
    	$state = $log_info['state'];
    	if ($state == 2) {
    		echo "数据处理中";
    		die;
    	}
    	//2.锁状态
    	$id = Model()->table('order_log')->where(array('log_id'=>126753))->update(array('state'=>2));
    	if (empty($id)) {
    		echo "系统异常";
    		die;
    	}
    	//3.取出10条待处理的订单，处理佣金
    	$log_list = Model()->table('order_log')->field('order_id,log_id')->where(array('state'=>0,'log_orderstate'=>40,'log_id'=>array('gt',366871)))->limit(20)->select();//390100 366871
    	if (empty($log_list)) {
    		$id = Model()->table('order_log')->where(array('log_id'=>126753))->update(array('state'=>1));
	    	if (empty($id)) {
	    		echo "系统异常";
	    		die;
	    	}
    		echo "success1";
    		exit;
    	}
    	foreach ($log_list as $key => $value) {
    		$this->order_brokerage($value);
    	}
    	//4.恢复锁状态
    	$id = Model()->table('order_log')->where(array('log_id'=>126753))->update(array('state'=>1));
    	if (empty($id)) {
    		echo "系统异常";
    		die;
    	}
    	echo "success";
    	die;
    }

    private function order_brokerage($order_log)
    {
    	$model_pdr = Model('predeposit');
    	$order_info = model()->table('order_common,order')->field('order_common.reciver_ziti_id,order.order_sn,order.order_id,order.z_order_sn,order.payment_time,order.payment_code,order.order_amount,order.order_state,order.refund_state')->join('left')->on('order.order_id=order_common.order_id')->where(array('order.order_id'=>$order_log['order_id']))->find();
    	$ziti_id = $order_info['reciver_ziti_id'];
    	$refund_state = $order_info['refund_state'];
    	if ($refund_state == 1) {
    		//获取部分退款商品信息
    		$refund_return_list = model()->table('refund_return')->field('order_goods_id')->where(array('order_id'=>$_GET['order_id']))->select();
    		foreach ($refund_return_list as $key => $refund_return) {
    			$order_goods_ids[] = $refund_return['order_goods_id'];
    		}
    	}
    	$on = 'ziti_address.gl_id=member.groupbuy_leader_id';
    	$leader_info = model()->table('ziti_address,member')->field('member.member_id,member.member_name,ziti_address.seller_name,ziti_address.gl_id')->join('left')->on($on)->where(array('ziti_address.address_id'=>$ziti_id))->find();
    	if (empty($leader_info)) {
    		Model()->table('order_log')->where(array('log_id'=>$order_log['log_id']))->update(array('state'=>1));
    		return;//没有对应团长信息直接跳过
    	}
    	$order_goods_list = model()->table('order_goods')->field('goods_pay_price*commis_rate/100 AS commis,rec_id,goods_name,goods_num,goods_price,goods_pay_price,goods_type,commis_rate,voucher_price,goods_cost_price')->where(array('order_id'=>$order_log['order_id']))->select();
		foreach ($order_goods_list as $key => $value) {
			if (!empty($order_goods_ids) && in_array($value['rec_id'], $order_goods_ids)) {
				continue;//退款商品排除佣金
			}
			$data = array();
	        $data['pdr_sn'] = $model_pdr->makeSn();
	        $data['pdr_member_id'] = $leader_info['member_id'];
	        $data['pdr_member_name'] = $leader_info['member_name'];
	        $data['pdr_amount'] = $value['commis'];
	        $data['pdr_payment_code'] = 'order_brokerage';
	        $data['pdr_payment_name'] = '订单佣金';
	        $data['pdr_payment_state'] = 1;
	        $data['address_id'] = $ziti_id;
	        $data['pdr_trade_sn'] = $order_info['order_sn'];
	        $data['pdr_add_time'] = TIMESTAMP;
	        $insert = $model_pdr->addPdRecharge($data);
	        if ($insert) {
	        	$data1['member_id'] = $leader_info['member_id'];
	        	$data1['member_name'] = $leader_info['member_name'];
	        	$data1['amount'] = round($value['commis'],2);
	        	$data1['order_goods_id'] = $value['rec_id'];
	        	$data1['address_id'] = $ziti_id;
	        	$data1['gl_id'] = $leader_info['gl_id'];
	        	$model_pdr->changePd('order_brokerage',$data1);
	        }
	        //佣金相关的订单、商品、订单商品信息保存
	        $data2['pdr_id'] = $insert;
	        $data2['pdr_add_time'] = $data['pdr_add_time'] ;
	        $data2['order_id'] = $order_info['order_id'];
	        $data2['rec_id'] = $value['rec_id'];
	        $data2['tz_id'] = $leader_info['gl_id'];
	        $data2['ziti_id'] = $ziti_id;
	        $data2['tz_name'] = $leader_info['member_name'];
	        $data2['order_sn'] = $order_info['z_order_sn'];
	        $data2['goods_name'] = $value['goods_name'];
	        $data2['goods_num'] = $value['goods_num'];
	        $data2['ziti_name'] = $leader_info['seller_name'];
	        $data2['sub_order_sn'] = $order_info['order_sn'];
	        $data2['pay_time'] = $order_info['payment_time'];
	        $data2['goods_price'] = $value['goods_price'];
	        $data2['cost_price'] = $value['goods_cost_price'];
	        $data2['total_cost'] = $value['goods_cost_price']*$value['goods_num'];
	        $data2['total_price'] = $value['goods_price']*$value['goods_num'];
	        $data2['discount_amount'] = $value['voucher_price'];
	        $data2['amount_paid'] = $value['goods_pay_price'];
	        $data2['order_amount'] = $order_info['order_amount'];
	        $data2['pay_type_name'] = $order_info['payment_code'];
	        $data2['shipping_note'] = '';
	        $data2['order_state'] = $order_info['order_state'];
	        $data2['voucher_name'] = '';
	        $data2['cate_name'] = '';
	        $data2['commission_rate'] = $value['commis_rate'];
	        $data2['commission'] = number_format($value['commis'],2);
	        $insert2 = Model('brokerage')->insert($data2);
		}
		Model()->table('order_log')->where(array('log_id'=>$order_log['log_id']))->update(array('state'=>1));   	
    }

    //根据refund_id处理退货佣金
    public function refund_brokerageOp()
    {
    	$condition = array();
    	$condition['refund_id'] = $_GET['refund_id'];
    	$model_refund = Model('refund_return');
    	$model_pdr = Model('predeposit');
    	$refund_info = $model_refund->getRefundReturnInfo($condition,'order_id,refund_sn,order_goods_id,goods_num,refund_amount,commis_rate');
    	if (empty($refund_info)) {
    		echo "退款信息异常";
    		die;
    	}
    	//order_id获取订单信息
    	$order_info1 = model()->table('order')->field('order_state')->where(array('order_id'=>$refund_info['order_id']))->find();
    	if (empty($order_info1) || $order_info1['order_state'] != 40) {
    		echo "order no finish";
    		die;
    	}
    	$order_info = model()->table('order_common')->field('reciver_ziti_id')->where(array('order_id'=>$refund_info['order_id']))->find();
    	//var_dump($order_info);die;
    	$ziti_id = $order_info['reciver_ziti_id'];
    	$on = 'ziti_address.gl_id=member.groupbuy_leader_id';
    	$leader_info = model()->table('ziti_address,member')->field('member.member_id,member.member_name')->join('left')->on($on)->where(array('ziti_address.address_id'=>$ziti_id))->find();
    	if ($refund_info['order_goods_id'] == 0) {
    		//获取订单商品表信息
    		$order_goods_list = model()->table('order_goods')->field('goods_price*goods_num*commis_rate/100 AS commis,rec_id')->where(array('order_id'=>$refund_info['order_id']))->select();
    		foreach ($order_goods_list as $key => $value) {
    			$data = array();
		        $data['pdr_sn'] = $model_pdr->makeSn();
		        $data['pdr_member_id'] = $leader_info['member_id'];
		        $data['pdr_member_name'] = $leader_info['member_name'];
		        $data['pdr_amount'] = $value['commis'];
		        $data['pdr_payment_code'] = 'refund_brokerage';
		        $data['pdr_payment_name'] = '退款佣金';
		        $data['pdr_payment_state'] = 1;
		        $data['pdr_trade_sn'] = $refund_info['refund_sn'];
		        $data['pdr_add_time'] = TIMESTAMP;
		        $insert = $model_pdr->addPdRecharge($data);
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
    		$order_goods_info = model()->table('order_goods')->field('goods_price*goods_num*commis_rate AS commis')->where(array('rec_id'=>$refund_info['order_goods_id']))->find();
    		$data = array();
	        $data['pdr_sn'] = $model_pdr->makeSn();
	        $data['pdr_member_id'] = $leader_info['member_id'];
	        $data['pdr_member_name'] = $leader_info['member_name'];
	        $data['pdr_amount'] = '-'.$order_goods_info['commis'];
	        $data['pdr_payment_code'] = 'refund_brokerage';
	        $data['pdr_payment_name'] = '退款佣金';
	        $data['pdr_payment_state'] = 1;
	        $data['pdr_trade_sn'] = $refund_info['refund_sn'];
	        $data['pdr_add_time'] = TIMESTAMP;
	        $insert = $model_pdr->addPdRecharge($data);
	        if ($insert) {
	        	$data1['member_id'] = $leader_info['member_id'];
	        	$data1['member_name'] = $leader_info['member_name'];
	        	$data1['amount'] = $order_goods_info['commis'];
	        	$data1['order_goods_id'] = $order_goods_info['rec_id'];
	        	$model_pdr->changePd('refund_brokerage',$data1);
	        }
    	}
    	echo "success";die;
    }

    public function order_brokerageOp()
    {
    	$model_pdr = Model('predeposit');
    	$order_info = model()->table('order_common,order')->field('order_common.reciver_ziti_id,order.order_sn,order.refund_state')->join('left')->on('order.order_id=order_common.order_id')->where(array('order.order_id'=>$_GET['order_id']))->find();
    	$ziti_id = $order_info['reciver_ziti_id'];
    	$refund_state = $order_info['refund_state'];
    	if ($refund_state == 1) {
    		//获取部分退款商品信息
    		$refund_return_list = model()->table('refund_return')->field('order_goods_id')->where(array('order_id'=>$_GET['order_id']))->select();
    		foreach ($refund_return_list as $key => $refund_return) {
    			$order_goods_ids[] = $refund_return['order_goods_id'];
    		}
    	}
    	$on = 'ziti_address.gl_id=member.groupbuy_leader_id';
    	$leader_info = model()->table('ziti_address,member')->field('member.member_id,member.member_name')->join('left')->on($on)->where(array('ziti_address.address_id'=>$ziti_id))->find();
    	$order_goods_list = model()->table('order_goods')->field('goods_price*goods_num*commis_rate/100 AS commis,rec_id')->where(array('order_id'=>$_GET['order_id']))->select();
		foreach ($order_goods_list as $key => $value) {
			if (in_array($value['rec_id'], $order_goods_ids)) {
				continue;//退款商品排除佣金
			}
			$data = array();
	        $data['pdr_sn'] = $model_pdr->makeSn();
	        $data['pdr_member_id'] = $leader_info['member_id'];
	        $data['pdr_member_name'] = $leader_info['member_name'];
	        $data['pdr_amount'] = $value['commis'];
	        $data['pdr_payment_code'] = 'order_brokerage';
	        $data['pdr_payment_name'] = '订单佣金';
	        $data['pdr_payment_state'] = 1;
	        $data['pdr_trade_sn'] = $order_info['order_sn'];
	        $data['pdr_add_time'] = TIMESTAMP;
	        $insert = $model_pdr->addPdRecharge($data);
	        if ($insert) {
	        	$data1['member_id'] = $leader_info['member_id'];
	        	$data1['member_name'] = $leader_info['member_name'];
	        	$data1['amount'] = round($value['commis'],2);
	        	$data1['order_goods_id'] = $value['rec_id'];
	        	$model_pdr->changePd('order_brokerage',$data1);
	        }
		}
    	echo "success";die;    	
    }
}
?>