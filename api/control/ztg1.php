<?php
defined('In718Shop') or exit('Access Invalid!');
class ztgControl  extends BaseControl{
	/*验证送货员、订单信息
	*/
	public function checkInfoOp(){
	    $member_id = intval($_GET['member_id']);
	    $order_sn = $_GET['order_sn'];
	    $model_order = Model('order');
	    //获取扫码订单表列表
	    $order_info = $model_order->table('order')->where(array('order_sn'=>$order_sn))->find();
	    $model_member = Model('member');
	    $model_cw = Model('cw');
	    $member_info = $model_member->table('member')->where(array('member_id'=>$member_id))->find();
	    $logdata['order_id'] = $order_info['order_id'];
	    $logdata['user_id'] = $member_id;
	    $logdata['type'] = 1;
	    if($member_info['is_store'] != 2){
	        echo $this->returnMsg(10002, '此用户非商户!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
	         $logdata['code']  = '10002';
	         $this->insert_saomalog($logdata);
	        exit;
	    }
	   
	    if(empty($order_info)){
	         echo $this->returnMsg(10003, '此订单无数据!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
	          $logdata['code']  = '10003';
	         $this->insert_saomalog($logdata);
	         exit;
	    }
	    if( $order_info['order_state'] == 40){
	        echo $this->returnMsg(10004, '此订单为已收货单或已到达自提点，不能进行入库操作!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
	         $logdata['code']  = '10004';
	         $a = $this->insert_saomalog($logdata);
	        exit;
	    }
	    if( $order_info['order_state'] == 0){
	        echo $this->returnMsg(10008, '此订单为已取消，不能进行入库操作!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
	         $logdata['code']  = '10008';
	         $a = $this->insert_saomalog($logdata);
	        exit;
	    }
	    if($order_info['order_state'] == 30){
	         echo $this->returnMsg(10007, '已此订单已到达自提点，不能进行二次入库!', array('member_id'=>$member_id,'order_sn'=>$order_sn));
	          $logdata['code']  = '10007';
	         $this->insert_saomalog($logdata);
	         exit;
	    }
		echo $this->returnMsg(10001, '订单数据正常', array('member_id'=>$member_id,'order_sn'=>$order_sn));
		$logdata['code']  = '10002';
		$this->insert_saomalog($logdata);
		exit;
	}
	/**
     * 添加扫码日志
     */
    public function insert_saomalog($logdata) {
        
       $data['order_id'] = $logdata['order_id'];
       $data['user_id'] = $logdata['user_id'];
       $data['sao_time'] = time();
       $data['type'] = $logdata['type'];
       $data['code'] = $logdata['code'];
       Model()->table('saoma_log')->insert($data);
    }
    /**
     *  
     * 获取自提柜收货码 用户定义 收货码保存及接口 中转数据
     * @param string $value [description]
     */
    public function reciverCodeOp($value='')
    {
    	$member_id = intval($_GET['member_id']);
	    $order_sn = $_GET['order_sn'];
	    $ztg_name = $_GET['ztg_name'];
	    $code = $_GET['code'];
	    if (empty($ztg_name) || empty($code)) {
	    	echo $this->returnMsg(10010, '请求参数异常', array('member_id'=>$member_id,'order_sn'=>$order_sn));
			exit;
	    }
	    $model_order = Model('order');
	    $data = array();
	    $data['order_sn'] = $order_sn;
	    $data['ztg_name'] = $ztg_name;
	    $data['code'] = $code;
	    $insert = Model()->table('order_code')->insert($data);
	    if (!$insert) {
	    	echo $this->returnMsg(10009, '操作异常', array('member_id'=>$member_id,'order_sn'=>$order_sn));
			exit;
	    }
	    //更新收货码后，进行入库操作
    	$result = file_get_contents('http://117.159.3.227:8088/wzxd/api/index.php?act=saoma&op=ruku&member_id='.$member_id.'&order_sn='.$order_sn);
    	echo($result);
    	die;    
    }
}
?>