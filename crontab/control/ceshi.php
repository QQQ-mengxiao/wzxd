<?php
/**
 * 任务计划 - 天执行的任务
 *
 * 
 *
 *
 * 
 */
defined('In718Shop') or exit('Access Invalid!');

class ceshiControl extends BaseCronControl {

    /**
     * 默认方法
     */
    public function indexOp() {
		echo 1;die;
        //订单七天确认收货数据发送云仓
        $this->_cwOrderCompleteNew();
    }

	
	private function _cwOrderCompleteNew(){
		$model_cw = Model('cw');
        $order_list = Model('order')->getOrderList(array('order_id'=>array('gt',0),'order_state'=>40,'finnshed_time'=>array('between',[TIMESTAMP-432000,TIMESTAMP-259200]),'is_cw_completed'=>0,'is_zorder'=>array('neq',0)),'','order_id,order_sn,order_amount,refund_amount');
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
                        "tenantId"=>1,
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
					$orderSn = $order_info['order_sn'];//Model('order')->getfby_order_id($order_id,'order_sn');
					//$refund_list = $model_refund->getRefundReturnList(array('order_id'=>$order_id,'refund_state'=>3),'','goods_id');
					//if(is_array($refund_list) && !empty($refund_list)){
					//	foreach ($refund_list as $goods_info){
					//		$goodsList[]['goodsCode'] = Model('goods')->getfby_goods_id($goods_info['goods_id'],'goods_serial');
					//	}
					//	$data1['tenantId'] = 42;
					//	$data1['orderSn'] = $orderSn;
					//	$data1['orderStatus'] = 9;//9异常收货
					//	$data1['goodsList'] = array_values($goodsList);
					//	$model_cw->cwAbnormalOrderOver($data1);
					//}else{
						$data1 = [
							"tenantId" => 42,
							"orderSn" => $orderSn,
							"orderStatus" => "7"
						];
						$model_cw->cwOrderOver($data1);
					//}
				}
				
				$result = $model_cw->cwOrderCompleteNew($data);
				if($result['code']==0){
					Model('order')->editOrder(array('is_cw_completed'=>1),array('order_id'=>$v['order_id']));
				}
            }
        }
    }
}