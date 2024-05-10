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
        $model_member = Model('member');
        $member_info = $model_member->table('member')->where(array('member_id'=>$member_id))->find();
        if($member_info['is_store'] != 2){
            echo $this->returnMsg(10002, '此用户非商户!', array('member_id'=>$member_id,'order_sn'=>$order_sn));exit;
        }
        $model_order = Model('order');
        //获取扫码订单表列表
        $order_info = $model_order->table('order')->where(array('order_sn'=>$order_sn))->find();
        if(empty($order_info)){
             echo $this->returnMsg(10003, '此订单无数据!', array('member_id'=>$member_id,'order_sn'=>$order_sn));exit;
        }
		if($order_info['hexiao_time'] != 0 || $order_info['order_state'] == 40){
            echo $this->returnMsg(10004, '此订单为已收货单或已核销单，不能进行二次扫码核销!', array('member_id'=>$member_id,'order_sn'=>$order_sn));exit;
        }
        if($order_info['refund_state'] != 0 ){
            echo $this->returnMsg(10005, '此订单为退款单，不能进行扫码核销!', array('member_id'=>$member_id,'order_sn'=>$order_sn));exit;
        }
        
        $update_array = array();
        $update_array['hexiao_time']   = time();
        $update_array['finnshed_time']   = time();
        $update_array['hexiao_user']   = $member_id;
        $update_array['hexiao_from']   = 2;
        $update_array['order_state']   = 40;
        $result = $model_order->table('order')->where(array('order_sn'=>$order_sn))->update($update_array);
        if ($result){
              Model('points')->savePointsLog('order',array('pl_memberid'=>$order_info['buyer_id'],'pl_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
            echo $this->returnMsg(10001, '此订单扫码核销成功!', array('member_id'=>$member_id,'order_sn'=>$order_sn));exit;
        }else{
             echo $this->returnMsg(10006, '此订单扫码核销异常!', array('member_id'=>$member_id,'order_sn'=>$order_sn));exit;
        }
        
    }
    /**用户ID查询是否为商户
     */
    public function is_storeOp(){
        $member_info=Model()->table('member')->where(array('member_id' => $_GET['member_id']))->find();
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
   
}