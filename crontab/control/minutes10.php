<?php
/**
 * 任务计划 - 分钟执行的任务
 */
defined('In718Shop') or exit('Access Invalid!');

class minutes10Control extends BaseCronControl
{

    public function indexOp()
    {
        //订单自动推送
        $this->auto_order_cwsubmit();
        $this->auto_order_cwsubmit_minutes10();

        //核销订单补推
        $this->auto_cwOrderOver();
    
        //库存自动同步
//		if(date("H")=='19' || date("H")=='20' || date("H")=='21'){
//			$this->auto_storage_synchro_goods();
//		}
}

    private function auto_storage_synchro_goods()
    {
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
		}die;
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
        $result = $model_cw->cwPlatGoodsSyn(42, $goods_list);
		$date = date("y-m");
            $dateday = date("y-m-d");
            $path = '/data/default/wzxd/logsmx/' . $date . '/';
            if (!is_dir($path)) {
            mkdir($path, 0777, true);
            }
            $filename = $path . $dateday . ".txt";
            if (file_exists($filename)) {
            $content = file_get_contents($filename);
            $content = $content . "\r\n-------------mx-----------\r\n" .json_encode($goods_list,320);
            file_put_contents($filename, $content);
            } else {
            file_put_contents($filename, 'mx'.json_encode($goods_list,320));
            }
		$date = date("y-m");
            $dateday = date("y-m-d");
            $path = '/data/default/wzxd/logsmx/' . $date . '/';
            if (!is_dir($path)) {
            mkdir($path, 0777, true);
            }
            $filename = $path . $dateday . ".txt";
            if (file_exists($filename)) {
            $content = file_get_contents($filename);
            $content = $content . "\r\n-------------mx-----------\r\n" .$result['msg'];
            file_put_contents($filename, $content);
            } else {
            file_put_contents($filename, 'mx'.$result['msg']);
            }
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
    }
	
	
    private function auto_order_cwsubmit(){
        $model_order = Model('order');
        $model_cw = Model('cw');
        $order_list = Model()->table('order,cw_log')->field('order.order_id,order.order_sn,order.order_amount,order.add_time,order.by_post')->join('left')->on('order.order_id=cw_log.order_id')->where(array('order.is_zorder'=>array('gt',0),'order.is_cw_completed'=>0,'cw_log.code'=>array('neq',0),'cw_log.counter'=>array('lt',5)))->select();

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
                $goods_list = $model_order->getOrderGoodsList(array('order_id'=>$item['order_id']),'goods_id,goods_serial as goodsCode,goods_name as goodsName,goods_price as goodsPrice,goods_num as goodsCount,goods_pay_price as goodsMoney,is_cw,voucher_price');
                $goodsList = [];
                $goods_amount = 0;
                $cw_sign = 0;
                $sql = "select svt.voucher_t_is_lg from 718shop_voucher_template svt left join 718shop_voucher sv on svt.voucher_t_id=sv.voucher_t_id left join 718shop_order_common soc on soc.voucher_id=sv.voucher_id where soc.order_id=".$item['order_id'];
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
                $orderList['couponType'] = $voucher_t_is_lg==1?'1':'0';
                $orderList['goodsList'] = array_values($goodsList);


                //********************区分邮寄********************//
                if($item['by_post']==2){ 
                    $orderList['shipmentFlag'] = 1;
                    $orderList['overName'] = Model()->table('order_common')->getfby_order_id($item['order_id'],'reciver_name');
                    $reciver_info = Model()->table('order_common')->getfby_order_id($item['order_id'],'reciver_info');
                    $orderList['overPhone'] = unserialize($reciver_info)['mob_phone'];
                    $orderList['overAddress'] = unserialize($reciver_info)['address'];
                    // var_dump($orderList);die;
                }else{
                    $orderList['shipmentFlag'] = 0;
                    $orderList['overName'] = '';
                    $orderList['overPhone'] = '';
                    $orderList['overAddress'] = '';
                }
                // var_dump(json_encode($orderList,320));die;
                //********************区分邮寄********************//
                
                if($orderList['goodsList']){
                    $res = $model_cw->cwOrderSubmit($orderList);
                    file_put_contents('/data/default/wzxd/qlog/cworder.log', date("Y-m-d H:i:s",time()).'--'.json_encode($orderList,320).'--'.json_encode($res,320)." \n", FILE_APPEND);
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

    public function auto_order_cwsubmit_minutes10(){
        $TIME = TIMESTAMP-600;
        $model_order = Model('order');
        $model_cw = Model('cw');
        $order_list = Model()->table('order')->field('order_id,order_sn,order_amount,add_time')->where(array('is_zorder'=>array('gt',0),'order_state'=>20,'is_cw_completed'=>0,'payment_time'=>array('gt',$TIME)))->select();
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
                $goods_list = $model_order->getOrderGoodsList(array('order_id'=>$item['order_id']),'goods_id,goods_serial as goodsCode,goods_name as goodsName,goods_price as goodsPrice,goods_num as goodsCount,goods_pay_price as goodsMoney,is_cw,voucher_price');
                $goodsList = [];
                $goods_amount = 0;
                $cw_sign = 0;
                $sql = "select svt.voucher_t_is_lg from 718shop_voucher_template svt left join 718shop_voucher sv on svt.voucher_t_id=sv.voucher_t_id left join 718shop_order_common soc on soc.voucher_id=sv.voucher_id where soc.order_id=".$item['order_id'];
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
                $orderList['couponType'] = $voucher_t_is_lg==1?'1':'0';
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

    public function auto_cwOrderOver(){
        $TIME = TIMESTAMP-600;
        // $sql = 'select order_sn from 718shop_order where delay_time>'.$TIME.' or ruku_time >'.$TIME;
        $sql = 'select order_sn from 718shop_order where ruku_time >'.$TIME;
        $order_list = Model()->query($sql);
        if($order_list){
            foreach ($order_list as $key => $value) {
                $data = [
                    "tenantId" => 42,
                    "orderSn" => $value['order_sn'],
                    "orderStatus" => "7"
                ];
            $model_cw = Model('cw');
            $model_cw->cwOrderOver($data);
            }
        }
        // print_r($order_list);
    }

}