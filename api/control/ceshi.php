<?php
//defined('In718Shop') or exit('Access Invalid!');
require_once BASE_ROOT_PATH . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
class ceshiControl extends BaseControl
{

    //模型对象
    private $_model_search;
    //每页显示商品评价数
    const PAGESIZE = 10;
    //页数
    const PAGENUM = 1;

    public function cwOrderSubmitOp(){
        $model_order = Model('order');
        $orderList = [];
        $orderList['tenantId'] = 42;
        $orderList['orderSn'] = '7000000020470101 ';
        $orderList['orderStatus'] = 0;
        $orderList['totalAmount'] = 10;
        $orderList['orderTime'] = '2022-03-16 11:33:10';
        $orderList['salerCode'] = 'WZXD';
        $orderList['salerName'] = '物资小店';
        $orderList['orderAddress'] = unserialize(Model()->table('order_common')->getfby_order_id(190183,'reciver_info'))['address'];
        $goods_list = $model_order->getOrderGoodsList(array('order_id'=>190183),'goods_id,goods_serial as goodsCode,goods_name as goodsName,goods_price as goodsPrice,goods_num as goodsCount,goods_pay_price as goodsMoney,is_cw');
        $goodsList = [];
        $goods_amount = 0;
        $cw_sign = 0;
        foreach ($goods_list as $key=>$order_goods){
            $is_cw = $order_goods['is_cw'];
            if($is_cw == 1){
                $goodsList[$key]['orderSn'] = '7000000015302701';
                $goodsList[$key]['goodsCode'] = $order_goods['goodsCode'];
                $goodsList[$key]['goodsName'] = $order_goods['goodsName'];
                $goodsList[$key]['goodsPrice'] = $order_goods['goodsPrice'];
                $goodsList[$key]['goodsCount'] = $order_goods['goodsCount'];
                $goodsList[$key]['goodsMoney'] = $order_goods['goodsMoney'];
                $cw_sign = 1;
            }else{
                $goods_amount += $order_goods['goodsMoney'];
                continue;
            }
        }
        unset($goods_list);
        $orderList['totalAmount'] = $goods_amount;
        $orderList['goodsList'] = array_values($goodsList);
        $model_cw = Model('cw');
		//print_r($orderList);
		//$url = "http://219.157.200.44:8081/cloud-admin/cwApi/cwOrderSubmit";
		//$res = $model_cw->Post_curls($url, json_encode($orderList,320));
		print_r(json_encode($orderList,true));die;
        $model_cw->cwOrderSubmit($orderList);
    }

    public function cwOrderCompleteOp()
    {
        $data = [
            "tenantId" => 42,
            "orderSn" => "71789191645273076",
            "orderStatus" => "3"
        ];
        $model_cw = Model('cw');
        $model_cw->cwOrderComplete($data);
    }

    public function cwOrderCancleOp()
    {
        $order_id = '68034';
        $order_sn = Model()->table('order')->getfby_order_id($order_id,'order_sn');
        echo $order_sn;die;
        $data = [
            "tenantId" => 1,
            "orderSn" => "20210426180035769",
            "orderStatus" => "4"
        ];
        $model_cw = Model('cw');
        $model_cw->cwOrderCancle($data);
    }

    //退款
    public function cwOrderBackOp()
    {
        // $refund_list = $model_refund->getRefundList($condition);//取refund_return表里的数据
		// $refund = $refund_list[0];
        // //获取一卡通卡号
        // $member_id=$refund['buyer_id'];
        // $cardno=Model()->table('member_card')->where(array('member_id'=>$member_id))->find();
        // $refund['cardno']=$cardno['cardno'];
        // //获取管理员信息
        // $admin=$this->getAdminInfo();
        // $con['admin_id']=$admin['id'];
        // $admin_info=Model('admin')->infoAdmin($con);
        // $refund['admin_name']=$admin_info['admin_name'];
// $data = 
		// // if (chksubmit()) {
		// 	// if ($refund['refund_state'] != '2') {//检查状态,防止页面刷新不及时造成数据错误
		// 		// showMessage(Language::get('nc_common_save_fail'));
		// 	// }
		// 	$order_id = $refund['order_id'];
            $tenantId = 42;

		// 	$refund_array = array();
		// 	$refund_array['admin_time'] = time();
		// 	$refund_array['refund_state'] = '3';//状态:1为处理中,2为待管理员处理,3为已完成
		// 	$refund_array['admin_message'] = $_POST['admin_message'];
		// 	$state = $model_refund->editOrderRefund_jp($refund);
			// if ($state) {
			    // $model_refund->editRefundReturn($condition, $refund_array);
				// $refund_id = 5297;
                $order_id = 311045;
                $refund_id = 9730;
                $order_info = Model('order')->getOrderInfo(array('order_id' => $order_id), [], 'order_sn,order_amount,refund_amount');
               $refund_time = Model()->table('refund_return')->getfby_order_id($order_id,'admin_time');
                // // if($refund['goods_id'] == 0){//部分退款或者部分退货，根据当前退款单$refund中的goods_id进行查询，如果goods_id为0，则认为是全部退款，即为全部退货。
                   $data = [
                       "tenantId" => $tenantId,
                       "orderSn" => $order_info['order_sn'],//订单编号  orderSn    String类型
                       "returnStatus" => "1",//退货状态 returnStatus  String类型    退货状态 0部分  如果部分退货需要传参商品编码  1全部 2无
                       "returnTime" => date('Y-m-d H:i:s', $refund_time),//退货时间  returnTime   String类型  2021-01-01 12:13:14
                       "orderStatus" => "6"//订单状态  orderStatus  String类型  6已退货
                   ];
                   print_r(json_encode($data));
                   die;
                    $res = Model('cw')->cwOrderRefund($data);//全部退货
                
                print_r($res);die;
                // }else{//如果goods_id不为0，则认为是单品退款，通过$refund信息中的order_id定位到订单信息，
//                     if ($order_info['order_amount'] == $order_info['refund_amount']) {//如果订单信息中order_amount==refund_amount，即退款金额等于订单实际支付金额，做全部退货处理；
//                         $data = [
//                             "tenantId" => $tenantId,
//                             "orderSn" => $order_info['order_sn'],//订单编号  orderSn    String类型
//                             "returnStatus" => "1",//退货状态 returnStatus  String类型    退货状态 0部分  如果部分退货需要传参商品编码  1全部 2无
//                             "returnTime" => date('Y-m-d H:i:s', time()),//退货时间  returnTime   String类型  2021-01-01 12:13:14
//                             "orderStatus" => "6"//订单状态  orderStatus  String类型  6已退货
//                         ];
                        // print_r(json_encode($data,320));die;
//                        $res = Model('cw')->cwOrderRefund($data);//全部退货
//                        print_r($data);print_r($res);die;
                //     }else{//如果order_amount!=refund_amount，即退款金额不等于订单实际支付金额，通过检索$refund信息中goods_id和order_id定位到order_goods信息，
//                         $order_goods_info = $model_order->getOrderGoodsInfo(array('order_id'=>$order_id,'goods_id'=>$refund['goods_id']),'goods_pay_price');
//                         $goods_pay_price = $order_goods_info['goods_pay_price'];
//                         if($refund['refund_amount'] == $goods_pay_price){//如果$refund信息中的refund_amount等于order_goods中的goods_pay_price，即退款金额等于该商品的支付金额，做部分退货处理；
                     $goods_list = Model('order')->getOrderGoodsList(array('order_id' => $order_id), 'goods_id,goods_serial as goodsCode');
                //     if ($goods_list) {
                         foreach ($goods_list as $key => $value) {
                             $refund_info = Model('refund_return')->getRefundReturnInfo(array('refund_id' => $refund_id, 'order_id' => $order_id, 'goods_id' => $value['goods_id'], 'seller_state' => 2, 'refund_state' => 3));
                             if ($refund_info) {
                                 unset($goods_list[$key]['goods_id']);
                             } else {
                                 unset($goods_list[$key]);
                             }
                         }
                //     }
                //     if ($goods_list) {
                         $data = [
                                     "tenantId" => $tenantId,
                             "orderSn" => $order_info['order_sn'],//订单编号  orderSn    String类型
                             "returnStatus" => "0",//退货状态 returnStatus  String类型    退货状态 0部分  如果部分退货需要传参商品编码  1全部 2无
                             "returnTime" => date('Y-m-d H:i:s', time()),//退货时间  returnTime   String类型  2021-01-01 12:13:14
                             "orderStatus" => "6",//订单状态  orderStatus  String类型  6已退货
                             "goodsList" => array_values($goods_list)
                         ];
//                         print_r($data);die;
                        $res = Model('cw')->cwOrderRefund($data);//全部退货
                        print_r($data);print_r($res);die;
                //     }
                //             $model_cw->cwOrderRefund($data);//部分退货
                //         }else{//如果$refund信息中的refund_amount不等于order_goods中的goods_pay_price，即退款金额不等于该商品的实际支付金额，即修改了单品退款金额，做部分退款处理。
                //             $goods_list = Model('order')->getOrderGoodsList(array('order_id' => $order_id), 'goods_id,goods_serial as goodsCode');
                //             if ($goods_list) {
                //                 foreach ($goods_list as $key => $value) {
                //                     $refund_info = Model('refund_return')->getRefundReturnInfo(array('refund_id' => $refund_id, 'order_id' => $order_id, 'goods_id' => $value['goods_id']), 'refund_amount,reason_info');
                //                     $goods_list[$key]['backMoney'] = $refund_info['refund_amount'];
                //                     $goods_list[$key]['refundReason'] = $refund_info['reason_info'];
                //                     if ($refund_info) {
                //                         unset($goods_list[$key]['goods_id']);
                //                     } else {
                //                         unset($goods_list[$key]);
                //                     }
                // }
                            // }
                            // $data = [
                            //     "tenantId" => $tenantId,
                            //     "orderSn" => $order_info['order_sn'],
                            //     "returnStatus" => "2",//退货状态 returnStatus  String类型    退货状态 0部分  如果部分退货需要传参商品编码  1全部 2无
                            //     "refundStatus" => "2",//是否退货  refundStatus   String类型  0无退货1有退货2仅退款
                            //     "returnTime" => date('Y-m-d H:i:s', time()),//"2021-06-01 10:07:14",
                            //     "orderStatus" => "8",//订单状态  orderStatus  String类型  8已退款
                            //     "goodsList" => array_values($goods_list)
                            // ];
                            // $model_cw->cwOrderBack($data);//部分退款
                        // }
                    // }
                // }
            // }
    }

    //完成订单最新接口（用户确认收货的订单）：包含订单部分完成和全部完成
    public function cwOrderCompleteNewOp(){
        $order_id = '439395';
        $order_info = Model('order')->getOrderInfo(array('order_id' => $order_id), [], 'order_sn,order_amount,refund_amount');
        $refund_info = Model('refund_return')->getRefundReturnInfo(array('order_id' => $order_id));
        if($order_info['refund_amount']==0){//订单无退款，全部完成
            $data = [
                "tenantId"=>42,
                "orderSn"=>$order_info['order_sn'],//订单编号  orderSn    String类型
                "completeStatus"=>"0",//完成状态 completeStatus  String类型 全部0
                "orderStatus"=>"3"//订单状态  orderStatus  String类型  3已完成
            ];
        }else{//订单有退款，部分完成
            $goods_list = Model('order')->getOrderGoodsList(array('order_id' => $order_id), 'goods_id,goods_serial as goodsCode');
            if ($goods_list) {
                foreach ($goods_list as $key => $value) {
                    $refund_info = Model('refund_return')->getRefundReturnInfo(array('order_id' => $order_id, 'goods_id' => $value['goods_id'], 'seller_state' => 2, 'refund_state' => 3));
                    if ($refund_info) {
                        unset($goods_list[$key]);
                    }else{
						unset($goods_list[$key]['goods_id']);
					}
                }
            }
            $data=[
                "tenantId"=> 42,
                "orderSn"=>$order_info['order_sn'],//订单编号  orderSn    String类型
                "completeStatus"=>"1",//完成状态 completeStatus  String类型 部分:1,如果部分完成需要传参商品编码
                "orderStatus"=>"3",//订单状态  orderStatus  String类型  3已完成
                "goodsList"=>array_values($goods_list)//商品列表  goodsList   数组array
            ];
        }
        // print_r(json_encode($data,320));die;
        $res = Model('cw')->cwOrderCompleteNew($data);
        print_r(json_encode($data,320));print_r($res);die;
    }

    public function cwOrderOverOp()
    {
        $order = Model('order')->getOrderList(array('order_sn'=>array('in',[95280861706501224,95264731706152960,9000000038565901,9000000038403901])),'','order_sn');
        foreach ($order as $key => $value) {
            $data = [
                "tenantId" => 42,
                "orderSn" => $value['order_sn'],
                "orderStatus" => "7"
            ];
            $url = 'http://10.10.11.61:8083/cloud-admin/cwApi/cwOrderOver';
            $res = Model('cw')->Post_curls($url, json_encode($data));
            $res = json_decode($res, true);
            print_r(json_encode($data));
            print_r($res);echo '---';
        }
    }

    //退货订单申请：包含订单部分退货和全部退货
    public function cwOrderRefundOp()
    {
        $refund_id = '5296';
        $order_id = '183143';
        $order_info = Model('order')->getOrderInfo(array('order_id' => $order_id), [], 'order_sn,order_amount,refund_amount');
        if ($order_info['order_amount'] == $order_info['refund_amount']) {//退款金额等于订单实际支付金额，做全部退货处理
            $data = [
                "tenantId" => 42,
                "orderSn" => $order_info['order_sn'],//订单编号  orderSn    String类型
                "returnStatus" => "1",//退货状态 returnStatus  String类型    退货状态 0部分  如果部分退货需要传参商品编码  1全部 2无
                "returnTime" => date('Y-m-d H:i:s', time()),//退货时间  returnTime   String类型  2021-01-01 12:13:14
                "orderStatus" => "6"//订单状态  orderStatus  String类型  6已退货
            ];
            print_r(json_encode($data, 320));
        } else {//退款金额不等于订单实际支付金额，做部分退款处理
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
                    "tenantId" => 42,
                    "orderSn" => $order_info['order_sn'],//订单编号  orderSn    String类型
                    "returnStatus" => "0",//退货状态 returnStatus  String类型    退货状态 0部分  如果部分退货需要传参商品编码  1全部 2无
                    "returnTime" => date('Y-m-d H:i:s', time()),//退货时间  returnTime   String类型  2021-01-01 12:13:14
                    "orderStatus" => "6",//订单状态  orderStatus  String类型  6已退货
                    "goodsList" => array_values($goods_list)
                ];
//                print_r(json_encode($data, 320));
//                die;
            }
        }
        $res = Model('cw')->cwOrderRefund($data);print_r($res);
    }

    public function cwAbnormalOrderOverOp(){
        $tenantId = 1;
        $order_id = '51384';
        $model_refund = Model('refund_return');
        $model_cw = Model('cw');
        $orderSn = Model('order')->getfby_order_id($order_id,'order_sn');
        $refund_list = $model_refund->getRefundReturnList(array('order_id'=>$order_id,'refund_state'=>3),'','goods_id');
        if(is_array($refund_list) && !empty($refund_list)){
            foreach ($refund_list as $goods_info){
                $goodsList[]['goodsCode'] = Model('goods')->getfby_goods_id($goods_info['goods_id'],'goods_serial');
            }
        }
        $data['tenantId'] = $tenantId;
        $data['orderSn'] = $orderSn;
        $data['orderStatus'] = 9;//9异常收货
        $data['goodsList'] = array_values($goodsList);
        $model_cw->cwAbnormalOrderOver($data);
        print_r(json_encode($goodsList));
    }
	
	public function synOp(){
		$model_setting = Model('setting');
        $model_cw = Model('cw');
        $model_goods = Model('goods');
        $auto_cw = $model_setting->getRowSetting('auto_cw');
        if ($auto_cw['value'] == 0) {
            return;
        }
        //$goods_list = $model_goods->getGoodsList(array('is_cw' => 1), 'goods_serial as goodsCode');
		$goods_list = $model_goods->getGoodsList(array('is_cw' => 1), 'goods_id,goods_serial as goodsCode','','','20000');
        if($goods_list){
            foreach ($goods_list as $key=>$value){
                if(!$value['goodsCode']){
                    $goods_list[$key]['goodsCode'] = $model_goods->getfby_goods_id($value['goods_id'],'goods_barcode');
                }
                unset($goods_list[$key]['goods_id']);
            }
        }
		$count = count($goods_list);
		if($count>1000){
			$page = ceil($count / 1000);
            for ($i = 1; $i <= $page; $i++) {
                //$limit1[] = ($i - 1) * 1000;
                //$limit2[] = $i * 1000 > $count ? $count : $i * 1000;
				$model_cw->cwPlatGoodsSyn(42, array_slice($goods_list,($i - 1) * 1000,($i * 1000 > $count ? $count : $i * 1000)-(($i - 1) * 1000)+1));echo 'ceshi';
				usleep(10000000);
            }
		}
		die;
        if($goods_list){
            foreach ($goods_list as $key=>$value){
                if(!$value['goodsCode']){
                    $goods_list[$key]['goodsCode'] = $model_goods->getfby_goods_id($value['goods_id'],'goods_barcode');
                }
                unset($goods_list[$key]['goods_id']);
            }
        }
        $result = $model_cw->cwPlatGoodsSyn(42, $goods_list);var_dump($result);
	}
	
	public function auto_order_cwsubmitOp(){
        $model_order = Model('order');
        $model_cw = Model('cw');
		$order_list = Model('order')->getOrderList(array('order_sn'=>array('in',[7000000014598001])));
        // $order_list = Model()->query("select * from 718shop_order where payment_time between 1648108380 and 1648114860 and is_zorder>0");//order_sn,order_amount,add_time,order_id
        // print_r($order_list);die;
		// $order_list = Model()->table('order,cw_log')->field('order.order_id,order.order_sn,order.order_amount,order.add_time')->join('left')->on('order.order_id=cw_log.order_id')->where(array('order.order_sn'=>6000000012080701))->select();var_dump($order_list);die;
		if($order_list){
			foreach($order_list as $item){
				$orderList = array();
                $orderList['tenantId'] = '42';
                $orderList['orderSn'] = $item['order_sn'];
                $orderList['orderStatus'] = '0';
                $orderList['totalAmount'] = $item['order_amount'];
                $orderList['orderTime'] = date('Y-m-d H:i:s',$item['add_time']);
                $orderList['salerCode'] = 'WZXD';
                $orderList['salerName'] = '物资小店';
                $orderList['orderAddress'] = unserialize(Model()->table('order_common')->getfby_order_id($item['order_id'],'reciver_info'))['address'];
                $goods_list = $model_order->getOrderGoodsList(array('order_id'=>$item['order_id']),'goods_id,goods_serial as goodsCode,goods_name as goodsName,goods_price as goodsPrice,goods_num as goodsCount,goods_pay_price as goodsMoney,is_cw');
                $goodsList = [];
                $goods_amount = 0;
                $cw_sign = 0;
                $sql = "select svt.voucher_t_is_lg from 718shop_voucher_template svt left join 718shop_voucher sv on svt.voucher_t_id=sv.voucher_t_id left join 718shop_order_common soc on soc.voucher_id=sv.voucher_id where soc.order_id=".$order_id;
                $voucher_t_is_lg = Model()->query($sql)[0]['voucher_t_is_lg'];
                foreach ($goods_list as $key=>$order_goods){
                    $is_cw = $order_goods['is_cw'];
                    if($is_cw == 1){
                        $goodsList[$key]['orderSn'] = $item['order_sn'];
                        $goodsList[$key]['goodsCode'] = $order_goods['goodsCode'];
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
						$model_order->editOrder(array('is_cw_completed'=>1),array('order_id'=>$item['order_id']));
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
                }
                $orderList['totalAmount'] = $item['order_amount'] - $goods_amount;
                $orderList['goodsList'] = array_values($goodsList);//print_r(json_encode($orderList,320));die;
				if($orderList['goodsList']){var_dump($orderList);die;
					$res = $model_cw->cwOrderSubmit($orderList);print_r(json_encode($orderList,320));print_r($res);echo '---';
					// $cw_log_info = $model_cw->cw_logGet($item['order_id']);
					// $cw_log = array();
					// $cw_log['add_time'] = TIMESTAMP;
					// $cw_log['order_id'] = $item['order_id'];
					// $cw_log['code'] = $res['code']?$res['code']:1;
					// $cw_log['msg'] = $res['msg']?$res['msg']:'';
					// if($cw_log['msg'] == '提交订单成功' || $cw_log['msg'] == '请勿重复提交订单'){
					// 	$cw_log['code'] = 0;
					// 	$cw_log['counter'] = 5;
					// }else{
					// 	$cw_log['counter'] = array('exp','counter+1');
					// }//var_dump($cw_log);//die;
					// if($cw_log_info){
					// 	$model_cw->cw_logUpdate($item['order_id'],$cw_log);
					// }else{
					// 	$model_cw->cw_logAdd($cw_log);
					// }
				}
			}
		}
    }
	//云仓同步库存接口测试
    public function cwPlatGoodsSynOp(){
		$model_cw = Model('cw');
		$goodsList[]['goodsCode'] = 'd02010050401';
		$data['tenantId'] = 42;
        $data['goodsList'] = $goodsList;
        $url = 'http://219.157.200.44:8081/cloud-admin/cwApi/cwPlatGoodsSyn';
        $res = $model_cw->Post_curls($url, json_encode($data,320));print_r($res);die;
        $res = json_decode($res, ture);
    }
	
	public function _cwOrderCompleteNewOp(){
		$model_cw = Model('cw');
        //$order_list = Model('order')->getOrderList(array('order_id'=>array('gt',0),'order_state'=>40,'finnshed_time'=>array('between',[TIMESTAMP-432000,TIMESTAMP-259200]),'is_cw_completed'=>0,'is_zorder'=>array('neq',0)),'','order_id,order_sn,order_amount,refund_amount');
		$order_list = Model('order')->getOrderList(array('order_sn'=>array('in',[7000000015049501]),'is_zorder'=>array('neq',0)),'','order_id,order_sn,order_amount,refund_amount');
		//var_dump($order_list);die;
        if($order_list){
            foreach ($order_list as $k=>$v){
                $order_info = Model('order')->getOrderInfo(array('order_id' => $v['order_id']), [], 'order_sn,order_amount,refund_amount,ruku_time');
                //查询是否有未处理的退款单
                $refund_info = Model('refund_return')->getRefundReturnInfo(array('order_id' => $v['order_id'],'refund_state'=>array('in',['1','2'])));
                if($refund_info){
                    continue;
                }
                if($order_info['refund_amount']==0){//订单无退款，全部完成
                    $data = [
                        "tenantId"=>42,
                        "orderSn"=>$order_info['order_sn'],//订单编号  orderSn    String类型
                        "completeStatus"=>"0",//完成状态 completeStatus  String类型 全部0
                        "orderStatus"=>"3"//订单状态  orderStatus  String类型  3已完成
                    ];
                }else{//订单有退款，部分完成
                    $goods_list = Model('order')->getOrderGoodsList(array('order_id' => $v['order_id']), 'goods_id,goods_serial as goodsCode');
                    if ($goods_list) {
                        foreach ($goods_list as $key => $value) {
                            $refund_info = Model('refund_return')->getRefundReturnInfo(array('order_id' => $v['order_id'], 'goods_id' => $value['goods_id'], 'seller_state' => 2, 'refund_state' => 3));
                            if ($refund_info) {
                                unset($goods_list[$key]);
                            } else {
                                unset($goods_list[$key]['goods_id']);
                            }
                        }
                    }
                    $data=[
                        "tenantId"=> 42,
                        "orderSn"=>$order_info['order_sn'],//订单编号  orderSn    String类型
                        "completeStatus"=>"1",//完成状态 completeStatus  String类型 部分:1,如果部分完成需要传参商品编码
                        "orderStatus"=>"3",//订单状态  orderStatus  String类型  3已完成
                        "goodsList"=>array_values($goods_list)//商品列表  goodsList   数组array
                    ];
                }
				
				//if( $order_info['ruku_time'] == 0){//未入库
					$order_id = $order_info['order_id'];
					//$model_refund = Model('refund_return');
					$orderSn = $order_info['order_sn'];
					//$refund_list = $model_refund->getRefundReturnList(array('order_id'=>$order_id,'refund_state'=>3),'','goods_id');
					//if(is_array($refund_list) && !empty($refund_list)){
					//	foreach ($refund_list as $goods_info){
					//		$goodsList[]['goodsCode'] = Model('goods')->getfby_goods_id($goods_info['goods_id'],'goods_serial');
					//	}
					//	$data1['tenantId'] = 42;
					//	$data1['orderSn'] = $orderSn;
					//	$data1['orderStatus'] = 9;//9异常收货
					//	$data1['goodsList'] = array_values($goodsList);var_dump($data1);die;
					//	$model_cw->cwAbnormalOrderOver($data1);
					//}else{
						// $data1 = [
						// 	"tenantId" => 42,
						// 	"orderSn" => $orderSn,
						// 	"orderStatus" => "7"
						// ];
						// $model_cw->cwOrderOver($data1);
					//}
				//}
				
				$result = $model_cw->cwOrderCompleteNew($data);
				if($result['code']==0){
					Model('order')->editOrder(array('is_cw_completed'=>1),array('order_id'=>$v['order_id']));
				}
            }
        }
	}
		
		//退货退款
		public function orderBackRefundOp(){
			$condition = array();
				$refund_id = '2334';
				$order_id = '95052';
				$tenantId = 42;
		$condition['refund_id'] = $refund_id;
		
		$model_member = Model('member');
		$model_cw = Model('cw');

		$model_order = Model('order');
		$model_refund = Model('refund_return');
		$refund_list = $model_refund->getRefundList($condition);//取refund_return表里的数据
		$refund = $refund_list[0];
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

		}
		
		public function timeOp(){
            echo phpinfo();die;
			echo date("i");
		}

        public function auto_cwOrderOverOp(){
        $model_order = Model('order');
        $model_cw = Model('cw');
        $order_list = Model()->table('order')->field('order_id,order_sn,order_amount,add_time')->where(array('is_zorder'=>array('gt',0),'is_cw_completed'=>0,'payment_time'=>array('BETWEEN',array(1642730400,1642734000))))->select();
        // print_r($order_list);die;
        if($order_list){
            foreach($order_list as $item){
                $orderList = array();
                $orderList['tenantId'] = '42';
                $orderList['orderSn'] = $item['order_sn'];
                $orderList['orderStatus'] = '0';
                $orderList['totalAmount'] = $item['order_amount'];
                $orderList['orderTime'] = date('Y-m-d H:i:s',$item['add_time']);
                $orderList['salerCode'] = 'WZXD';
                $orderList['salerName'] = '物资小店';
                $orderList['orderAddress'] = unserialize(Model()->table('order_common')->getfby_order_id($item['order_id'],'reciver_info'))['address'];
                $goods_list = $model_order->getOrderGoodsList(array('order_id'=>$item['order_id']),'goods_id,goods_serial as goodsCode,goods_name as goodsName,goods_price as goodsPrice,goods_num as goodsCount,goods_pay_price as goodsMoney,is_cw');
                $goodsList = [];
                $goods_amount = 0;
                $cw_sign = 0;
                foreach ($goods_list as $key=>$order_goods){
                    $is_cw = $order_goods['is_cw'];
                    if($is_cw == 1){
                        $goodsList[$key]['orderSn'] = $item['order_sn'];
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
                    $model_order->editOrder(array('is_cw_completed'=>1),array('order_id'=>$item['order_id']));
                }
                $orderList['totalAmount'] = $item['order_amount'] - $goods_amount;
                $orderList['goodsList'] = array_values($goodsList);
                if($orderList['goodsList']){
                    $res = $model_cw->cwOrderSubmit($orderList);
                    $cw_log = array();
                    $cw_log['add_time'] = TIMESTAMP;
                    $cw_log['order_id'] = $item['order_id'];
                    $cw_log['code'] = $res['code']?$res['code']:1;
                    $cw_log['msg'] = $res['msg']?$res['msg']:'';
                    if($cw_log['msg'] == '提交订单成功' || $cw_log['msg'] == '请勿重复提交订单'){
                        $cw_log['code'] = 0;
                        $cw_log['counter'] = 5;
                    }else{
                        $cw_log['counter'] = array('exp','counter+1');
                    }
                    $model_cw->cw_logUpdate($item['order_id'],$cw_log);
                }
            }
        }
        }

        public function cwMqOp(){
            $model_order = Model('order');
            $f_order = $model_order->table('order')->where(['order_id'=>52613])->select();
            // print_r($f_order);die;
            // $cw_info = $model_order->getOrderGoodsInfo(array('order_id' => $f_order['order_id'], 'is_cw' => 1));
            // if ($cw_info) {
            //     //拼接数据发送mq并记录日志表
                $this->rabbitmq_send($f_order);
            // }else{//订单里没有云仓商品，直接更新云仓确认收货标志为1
            //     $model_order->editOrder(array('is_cw_completed' => 1), array('order_id' => $f_order['order_id']));
            // }
        }

        /**
     * 发送数据到云仓rabbitmq
     */
    private function rabbitmq_send($f_order)
    {
        //拼接json数据
        $model_order = Model('order');
        //云仓 已支付订单同步接口
        $orderList = [];
        $orderList['tenantId'] = 42;
        $orderList['orderSn'] = $f_order['order_sn'];
        $orderList['orderStatus'] = 0;
        $orderList['totalAmount'] = $f_order['order_amount'];
        $orderList['orderTime'] = date('Y-m-d H:i:s', $f_order['add_time']);
        $orderList['salerCode'] = 'WZXD';
        $orderList['salerName'] = '物资小店';
        $orderList['orderAddress'] = unserialize(Model()->table('order_common')->getfby_order_id($f_order['order_id'], 'reciver_info'))['address'];
        $goods_list = $model_order->getOrderGoodsList(array('order_id' => $f_order['order_id']), 'goods_id,goods_serial,goods_barcode,goods_name as goodsName,goods_price as goodsPrice,goods_num as goodsCount,goods_pay_price as goodsMoney,is_cw');
        $goodsList = [];
        $goods_amount = 0;
        foreach ($goods_list as $key => $order_goods) {
            $is_cw = $order_goods['is_cw'];
            if ($is_cw == 1) {
                $goodsList[$key]['orderSn'] = $f_order['order_sn'];
                if ($order_goods['goods_serial']) {
                    $goodsList[$key]['goodsCode'] = $order_goods['goods_serial'];
                } else {
                    $goodsList[$key]['goodsCode'] = $order_goods['goods_barcode'];
                }
                $goodsList[$key]['goodsName'] = $order_goods['goodsName'];
                $goodsList[$key]['goodsPrice'] = $order_goods['goodsPrice'];
                $goodsList[$key]['goodsCount'] = $order_goods['goodsCount'];
                $goodsList[$key]['goodsMoney'] = $order_goods['goodsMoney'];
                $goodsList[$key]['realMoney'] = $order_goods['goodsMoney'];
            } else {
                $goods_amount += $order_goods['goodsMoney'];
                continue;
            }
        }
        unset($goods_list);
        $orderList['totalAmount'] = $f_order['order_amount'] - $goods_amount;
        $orderList['goodsList'] = array_values($goodsList);
        $json_data = json_encode($orderList,320);

        //发送mq
        $this->send($json_data);
        //记录日志
        // $this->rabbitmq_log($f_order['order_id'], $json_data);
    }

    /**
     * mq发送数据
     */
    private function send($json_data){
        $connection = new AMQPStreamConnection('218.28.14.169', 5672, 'wzxd', '123456');
		$channel = $connection->channel();

		$channel->exchange_declare('ql_topic_exchange', 'topic', false, true, false);

		$routing_key = 'orderSubmit.#';
		$data = $json_data;

		$msg = new AMQPMessage($data,array('content_encoding'=>'UTF-8','content_type'=>'text/plain'));

		$channel->basic_publish($msg, 'ql_topic_exchange', $routing_key);

		$channel->close();
		$connection->close();
    }

    /**
     * 记录日志
     */
    private function rabbitmq_log($order_id, $content)
    {
        $rabbitmq_log['order_id'] = $order_id;
        $rabbitmq_log['content'] = $content;
        $rabbitmq_log['add_time'] = time();
        $rabbitmq_log['type'] = 2;
        Model()->table('rabbitmq_log')->insert($rabbitmq_log);
    }


    public function ceshimqOp(){
        $model_setting = Model('setting');
        $model_cw = Model('cw');
        $model_goods = Model('goods');
        $auto_cw = $model_setting->getRowSetting('auto_cw');
        if ($auto_cw['value'] == 0) {
            return;
        }
        //$goods_list = $model_goods->getGoodsList(array('is_cw' => 1), 'goods_serial as goodsCode');
		$goods_list = $model_goods->getGoodsList(array('is_cw' => 1,'is_deleted'=>0), 'goods_id,goods_serial as goodsCode','','','20000');
        if($goods_list){
            foreach ($goods_list as $key=>$value){
                if(!$value['goodsCode']){
                    $goods_list[$key]['goodsCode'] = $model_goods->getfby_goods_id($value['goods_id'],'goods_barcode');
                }
                unset($goods_list[$key]['goods_id']);
            }
        }
		$count = count($goods_list);
		if($count>500){
			$page = ceil($count / 500);
            for ($i = 1; $i <= $page; $i++) {
                //$limit1[] = ($i - 1) * 1000;
                //$limit2[] = $i * 1000 > $count ? $count : $i * 1000;
				$result = $model_cw->cwPlatGoodsSyn(42, array_slice($goods_list,($i - 1) * 500,($i * 500 > $count ? $count : $i * 500)-(($i - 1) * 500)+1));
				if ($result['code'] == 0) {
					if (is_array($result['data']) && count($result['data']) > 0) {
						$goods_serial = [];
						foreach ($result['data'] as $item) {
							$res = $model_goods->editGoods(array('goods_storage' => $item['saleInventory']), array('goods_serial' => $item['goodsCode'],'is_cw'=>1));
							if ($res) {
								$goods_serial[] = $item['goodsCode'];
							}
						}
						$contentLog = '自动同步商品库存，商家货号：' . implode(',', $goods_serial);
						$seller_info = array();
						$seller_info['log_content'] = $contentLog;
						$seller_info['log_time'] = TIMESTAMP;
						$seller_info['log_seller_id'] = '0';//$_SESSION['seller_id'];
						$seller_info['log_seller_name'] = '系统定时任务';//$_SESSION['seller_name'];
						$seller_info['log_store_id'] = 4;//$_SESSION['store_id'];
						$seller_info['log_seller_ip'] = getIp();
						$seller_info['log_url'] = $_GET['act'].'&'.$_GET['op'];
						$seller_info['log_state'] = 1;
						$model_seller_log = Model('seller_log');
						$model_seller_log->addSellerLog($seller_info);
					}
				}
				usleep(20000000);
			}
		}
    }

    public function _cwOrderCompleteNewPostOp(){
		$model_cw = Model('cw');
        $order_list = Model('order')->getOrderList(array('order_id'=>485362));
        if($order_list){
            foreach ($order_list as $k=>$v){
                $order_info = Model('order')->getOrderInfo(array('order_id' => $v['order_id']), [], 'order_sn,order_amount,refund_amount,ruku_time');
                //查询是否有未处理的退款单
                $refund_info = Model('refund_return')->getRefundReturnInfo(array('order_id' => $v['order_id'],'refund_state'=>array('in',['1','2'])));
                if($refund_info){
                    continue;
                }
                if($order_info['refund_amount']==0){//订单无退款，全部完成
                    $data = [
                        "tenantId"=>42,
                        "orderSn"=>$order_info['order_sn'],//订单编号  orderSn    String类型
                        "completeStatus"=>"0",//完成状态 completeStatus  String类型 全部0
                        "orderStatus"=>"3"//订单状态  orderStatus  String类型  3已完成
                    ];
                }else{//订单有退款，部分完成
                    $goods_list = Model('order')->getOrderGoodsList(array('order_id' => $v['order_id']), 'goods_id,goods_serial as goodsCode');
                    if ($goods_list) {
                        foreach ($goods_list as $key => $value) {
                            $refund_info = Model('refund_return')->getRefundReturnInfo(array('order_id' => $v['order_id'], 'goods_id' => $value['goods_id'], 'seller_state' => 2, 'refund_state' => 3));
                            if ($refund_info) {
                                unset($goods_list[$key]);
                            } else {
                                unset($goods_list[$key]['goods_id']);
                            }
                        }
                    }
                    $data=[
                        "tenantId"=> 42,
                        "orderSn"=>$order_info['order_sn'],//订单编号  orderSn    String类型
                        "completeStatus"=>"1",//完成状态 completeStatus  String类型 部分:1,如果部分完成需要传参商品编码
                        "orderStatus"=>"3",//订单状态  orderStatus  String类型  3已完成
                        "goodsList"=>array_values($goods_list)//商品列表  goodsList   数组array
                    ];
                }
				
				if( $order_info['ruku_time'] == 0){//未入库
					$order_id = $order_info['order_id'];
					$model_refund = Model('refund_return');
					$orderSn = $order_info['order_sn'];
                    $data1 = [
                        "tenantId" => 42,
                        "orderSn" => $orderSn,
                        "orderStatus" => "7"
                    ];
                    $a = $model_cw->cwOrderOver($data1);
				}
				$result = $model_cw->cwOrderCompleteNew($data);
                print_r($result);die;
				if($result['code']==0){
					Model('order')->editOrder(array('is_cw_completed'=>1),array('order_id'=>$v['order_id']));
				}
            }
        }
    }

}
