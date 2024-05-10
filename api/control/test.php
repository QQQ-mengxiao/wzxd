<?php
/**
 * 扫码核销
 *
 *
 *
 ***/
defined('In718Shop') or exit('Access Invalid!');
class testControl  extends BaseControl{

    /* 链接测试
    */
    public function testOp(){
        $order_sn = '6000000005169601';
		$model_order = Model('order');
		$order_info = $model_order->table('order')->where(array('order_sn'=>$order_sn))->find();
		$buyer_id = $order_info['buyer_id'];//获取token
		$order_id = $order_info['order_id'];//获取自提名称
		$buyer_info = Model('member')->getMemberInfo(array('member_id' => $buyer_id));
		$order_common_info = $model_order->getOrderCommonInfo(array('order_id' => $order_id));
		$ziti_id = $order_common_info['reciver_ziti_id'];
		$ziti_info = Model('ziti_address')->getAddressInfo(array('address_id' => 3));
		$seller_name = $ziti_info['seller_name'];
		$touser = $buyer_info['member_wxopenid'];
		//echo $touser."<br/>";
		include 'wxSubscribe.php';
		$wxSubscribe = new wxSubscribe();
		$output = $wxSubscribe->sendMessage($touser,$order_id,$seller_name,$buyer_id);
		var_dump($output);
		file_put_contents("/home/wwwroot/default/wzxd/log/wxSubscribe.send", $order_sn."  ".$touser."  ".$output.PHP_EOL,FILE_APPEND);
    }   
      public function indexOp(){  
      	 $model_voucher = Model('voucher');
             //     if($order_info['buyer_id']==28956){
             //   var_dump( $amount);die;
             // }
                $res = $model_voucher->mangzeng_voucher(28956, 4, '101.00');
     	 // $model_refund = Model('refund_return');
     	 // $refund=$model_refund->getRefundReturnInfo(array('refund_id' => 7513));
       //          $output = Model('wxsend')->sendMessage('o1OXp5R4qZNDRmm-9VThZEEgaLis',$refund);
       //          var_dump( $output);die;
     }
}
// defined('In718Shop') or exit('Access Invalid!');
// class testApiControl extends BaseControl{
// 	public function indexOp()
// 	{
// 		$order_sn = '6000000005169601';
// 		$model_order = Model('order');
// 		$order_info = $model_order->table('order')->where(array('order_sn'=>$order_sn))->find();
// 		$buyer_id = $order_info['buyer_id'];//获取token
// 		$order_id = $order_info['order_id'];//获取自提名称
// 		$buyer_info = Model('member')->getMemberInfo(array('member_id' => $buyer_id));
// 		$order_common_info = $model_order->getOrderCommonInfo(array('order_id' => $orderid));
// 		$ziti_id = $order_common_info['recive_ziti_id'];
// 		$ziti_info = Model('ziti_address')->getAddressInfo(array('address_id' => $ziti_id));
// 		$seller_name = $ziti_info['seller_name'];
// 		$touser = $buyer_info['member_wxopenid'];
// 		include 'wxSubscribe.php';
// 		$wxSubscribe = new wxSubscribe();
// 		$output = $wxSubscribe->sendMessage($touser,$order_sn,$ziti_name);
// 		file_put_contents("subscribe.send", $touser."_".$order_sn."_".$output.PHP_EOL,FILE_APPEND);
// 	}
// }