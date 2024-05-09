<?php
defined('In718Shop') or exit('Access Invalid!');
class refundControl  extends BaseControl{
    /* 链接测试
    */
    public function testOp(){
        echo $this->returnMsg(100, '请求成功！', '');exit;
    }
    /**
     * 添加订单商品部分退款
     *
     */
    public function add_returnOp(){
        $model_refund = Model('refund_return');
        $order_id = intval($_POST['order_id']);
        //订单商品表编号
        $goods_id = intval($_POST['goods_id']);
        $buyer_id = intval($_POST['member_id']);
       
        if ($order_id < 1 || $goods_id < 1) {//参数验证
            echo $this->returnMsg(200, '请求参数异常!','');exit;
        }
        // sleep(50);
        $condition = array();
        $condition['buyer_id'] = $buyer_id;
        $condition['order_id'] = $order_id;
        // 获取要退款的订单信息
        $order = $model_refund->getApiRightOrderList($condition, $goods_id);
        // var_dump($order);die;
        $order_id = $order['order_id'];
        $order_amount = $order['order_amount'];//订单金额
        $order_refund_amount = $order['refund_amount'];//订单退款金额
        $goods_list = $order['goods_list'];
        $goods = $goods_list[0];
        $goods_pay_price = $goods['goods_pay_price'];//商品不加税金的价格
        // sleep(50);
        // $goods_id = $goods['goods_id'];
         $goods_id = $goods['rec_id'];
        $condition = array();
        $condition['buyer_id'] = $order['buyer_id'];
        $condition['order_id'] = $order['order_id'];
        $condition['order_goods_id'] = $goods_id;
        $condition['seller_state'] = array('lt','3');
        // 取退款退货记录
        $refund_list = $model_refund->getRefundReturnList($condition);
        $refund = array();
        if (!empty($refund_list) && is_array($refund_list)) {
            $refund = $refund_list[0];
        }
        //根据订单状态判断是否可以退款退货
        $refund_state = $model_refund->getRefundState($order);//根据订单状态判断是否可以退款退货
      
        // if ($refund['refund_id'] > 0 || $refund_state != 1) {//检查订单状态,防止页面刷新不及时造成数据错误
         if ($refund['refund_id'] > 0 ) {//检查订单状态,防止页面刷新不及时造成数据错误
            echo $this->returnMsg(300, '该商品已申请过售后!','');exit;
        }
        $refund_array = array();
        $refund_amount = floatval($_POST['refund_amount']);//退款金额
        if (($refund_amount < 0) || ($refund_amount > $goods_pay_price)) {
            $refund_amount = $goods_pay_price;
        }
        // $goods_num = intval($_POST['goods_num']);//退货数量
        // if (($goods_num < 0) || ($goods_num > $goods['goods_num'])) {
        //     $goods_num = 1;
        // }
        $goods_num = $goods['goods_num'];
        $refund_array['reason_info'] = $_POST['reason_info'];
        $pic_array = array();
        
        // $_POST['pic_info'] = json_decode(htmlspecialchars_decode($_POST['pic_info']),true);
        $pic_array['buyer'] = explode(',', $_POST['pic_info']);//上传凭证
        // $pic_array['buyer'] = $this->upload_pic();//上传凭证
        $info = serialize($pic_array);
        $refund_array['pic_info'] = $info;

        $model_trade = Model('trade');
        $order_shipped = $model_trade->getOrderState('order_shipped');//订单状态30:已发货
        // if ($order['order_state'] == $order_shipped) {
            $refund_array['order_lock'] = '2';//锁定类型:1为不用锁定,2为需要锁定
        // }
        $refund_array['refund_type'] = $_POST['refund_type'];//类型:1为退款,2为退货
        //$show_url = 'index.php?act=member_return&op=index';
        $refund_array['return_type'] = '2';//退货类型:1为不用退货,2为需要退货
        if ($refund_array['refund_type'] != '2') {
                //类型:1为退款,2为退货
                $refund_array['refund_type'] = '1';
                $refund_array['return_type'] = '1';
                //退货类型:1为不用退货,2为需要退货
        }
        //状态:1为待审核,2为同意,3为不同意
        $refund_array['seller_state'] = '1';
        $refund_array['refund_amount'] = ncPriceFormat($refund_amount);
        $refund_array['goods_num'] = $goods_num;
        $refund_array['buyer_message'] = $_POST['buyer_message'];
        $refund_array['add_time'] = time();
        // var_dump($goods);die;
        $state = $model_refund->addRefundReturn($refund_array,$order,$goods);
        $order['state_desc'] = '订单已申请售后，处理中!';
		
        $model_order = Model('order');
        // sleep(50);
        $refund_array['extend_refund_order'] = $order;
        if ($state) {
            // if ($order['order_state'] == $order_shipped) {
                $model_refund->editOrderLock($order_id);
            // }
            echo $this->returnMsg(100, '售后申请成功!',$refund_array);
        } else {
            echo $this->returnMsg(400, '售后申请失败!','');
        }
    }

    /*
    退货凭证照片上传
    */
    public function refund_uploadOp(){
        /**
         * 上传图片
         */
        // $goods_id = intval($_POST['goods_id']);

        $upload = new UploadFile();
        // $dir = DIR_UPLOAD_REFUND.'/'.$goods_id;
        $dir = ATTACH_PATH.DS.'refund'.DS;
        $result =$upload->set('default_dir',$dir);
        $upload->set('allow_type',array('jpg','jpeg','gif','png'));
        $result = $upload->upfile('refund');
        if ($result){
            $_POST['pic'] = $upload->file_name;
        }else {
            echo $this->returnMsg(200, '售后凭证上传失败', null);exit();
        }
        if ($result){
            $data = array();
            $data['file_name'] = $_POST['pic'];
            $data['file_path'] =$_POST['pic'];
            echo $this->returnMsg(100, '售后凭证上传成功', $data);
        }
        
    }
    /**
     * 代发货添加全部退款即取消订单
     *
     */
    public function add_refund_allOp(){
        $model_order = Model('order');
        $model_trade = Model('trade');
        $model_refund = Model('refund_return');
        $order_id = intval($_POST['order_id']);
        $condition = array();
        $condition['buyer_id'] = $_POST['member_id'];
        $condition['order_id'] = $order_id;
        $order = $model_refund->getRightOrderList($condition);
        $order_amount = $order['order_amount'];//订单金额
        $condition = array();
        $condition['buyer_id'] = $order['buyer_id'];
        $condition['order_id'] = $order['order_id'];
        $condition['goods_id'] = '0';
        $condition['seller_state'] = array('lt','3');
        $refund_list = $model_refund->getRefundReturnList($condition);
		
		
        $refund = array();
        if (!empty($refund_list) && is_array($refund_list)) {
            $refund = $refund_list[0];
        }
		//根据订单状态判断是否可以退款退货
        $refund_state = $model_refund->getRefundState($order);//根据订单状态判断是否可以退款退货
      
        if ($refund['refund_id'] > 0 || $refund_state != 0) {//检查订单状态,防止页面刷新不及时造成数据错误
            echo $this->returnMsg(300, '该商品已申请过售后!','');exit;
        }
		
        $order_paid = $model_trade->getOrderState('order_paid');//订单状态20:已付款
        // $payment_code = $order['payment_code'];//支付方式
        // if ($refund['refund_id'] > 0 || $order['order_state'] != $order_paid || $payment_code == 'offline') {//检查订单状态,防止页面刷新不及时造成数据错误
        //      echo $this->returnMsg(200, '评价晒图上传失败', null);exit();
        // }
        if(!empty($_POST['refund_amount'])){
             $refund_amount = floatval($_POST['refund_amount']);//退款金额
            if (($refund_amount < 0) || ($refund_amount > $order_amount)) {
                $refund_amount = $order_amount;
            }
        }else{
            $refund_amount = $order_amount;
        }
       

            $refund_array = array();
            $refund_array['refund_type'] = '1';//类型:1为退款,2为退货
            $refund_array['seller_state'] = '1';//状态:1为待审核,2为同意,3为不同意
            $refund_array['order_lock'] = '2';//锁定类型:1为不用锁定,2为需要锁定
            $refund_array['goods_id'] = '0';
            $refund_array['order_goods_id'] = '0';
            $refund_array['reason_id'] = '0';
            $refund_array['reason_info'] = $_POST['reason_info'];
            if( $refund_amount!=$order_amount){
              $refund_array['goods_name'] = '订单商部分退款';
            }else{
                $refund_array['goods_name'] = '订单商品全部退款';
            }
            $refund_array['refund_amount'] = $refund_amount;
            $refund_array['buyer_message'] = $_POST['buyer_message'];
            $refund_array['add_time'] = time();

            $pic_array = array();
             $pic_array['buyer'] = explode(',', $_POST['image']);//上传凭证
            $info = serialize($pic_array);
            $refund_array['pic_info'] = $info;
            $state = $model_refund->addRefundReturn($refund_array,$order);

            if ($state) {
                $model_refund->editOrderLock($order_id);
                echo $this->returnMsg(100, '发起退款成功', $state);exit();
            } else {
                echo $this->returnMsg(200, '发起退款失败',$state);exit();
            }
    }
        
    
    /**
     * 退款记录查看
     *
     */
    public function viewOp(){
        $model_refund = Model('refund_return');
        $condition = array();
        // $condition['buyer_id'] = $_GET['member_id'];
        $condition['refund_id'] = intval($_GET['refund_id']);
        $refund_list = $model_refund->getRefundReturnList($condition);
        $refund = $refund_list[0];

        $express_list  = rkcache('express',true);
        // var_dump($express_list);die;
        $refund['express_list']=array_values($express_list);
        if ($return['express_id'] > 0 && !empty($return['invoice_no'])) {
           $return_e_name=$express_list[$return['express_id']]['e_name'];
        }
        // $info['buyer'] = array();
        if(!empty($refund['pic_info'])) {
            $info = unserialize($refund['pic_info']);
            $info=$info['buyer'];
            $pic_list=array();
            foreach ($info as $key => $value) {
                $pic_list[]=UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/refund/'.$value;
            }
        }
        // var_dump($refund_list['add_time']);die;
        $refund['add_time']=date('Y-m-d H:i:s',$refund['add_time']);
        $refund['seller_time']=date('Y-m-d H:i:s',$refund['seller_time']);
        $refund['admin_time']=date('Y-m-d H:i:s',$refund['admin_time']);
        // $refund['goods_image']=cthumb($refund['goods_image'] ,'', $refund['store_id']);
        $refund['pic_info']= $pic_list;
               $condition = array();
        $condition['order_id'] = $refund['order_id'];
        $order=$model_refund->getRightOrderList($condition);
         $refund_goods=array();
               foreach ($order['goods_list'] as $k => $v) {
                    if ($refund['goods_id']==0) {
                $refund_goods[$k]['goods_image'] = cthumb($v['goods_image'], 60, $v['store_id']);
                $refund_goods[$k]['goods_price'] = $v['goods_price'];
                $refund_goods[$k]['goods_name'] = $v['goods_name'];
                $refund_goods[$k]['goods_num'] = $v['goods_num'];
                $refund_goods[$k]['goods_id'] = $v['goods_id'];
                $model_goods = Model('goods');
                $goods_info = $model_goods->getGoodsInfoByID($v['goods_id']);
                $refund_goods[$k]['is_group_ladder'] = $goods_info['is_group_ladder'];
                } else {
                  if($refund['goods_id']==$v['goods_id']){
                      $refund_goods[$k]['goods_image'] = cthumb($v['goods_image'], 60, $v['store_id']);
                $refund_goods[$k]['goods_price'] = $v['goods_price'];
                $refund_goods[$k]['goods_name'] = $v['goods_name'];
                $refund_goods[$k]['goods_num'] = $v['goods_num'];
                $refund_goods[$k]['goods_id'] = $v['goods_id'];
                $model_goods = Model('goods');
                $goods_info = $model_goods->getGoodsInfoByID($v['goods_id']);
                $refund_goods[$k]['is_group_ladder'] = $goods_info['is_group_ladder'];
                  }
               }
               }
        $refund['goods_list']=$refund_goods;
        if ($refund) {
                echo $this->returnMsg(100, 'sucess', $refund);exit();
            } else {
                echo $this->returnMsg(200, 'falie',$refund);exit();
            }
    }
    /**
     * 发货
     *
     */
    public function shipOp(){
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['buyer_id'] = $_POST['member_id'];
        $condition['refund_id'] = intval($_POST['return_id']);
        $refund_array = array();
        $refund_array['ship_time'] = time();
        $refund_array['express_id'] = $_POST['express_id'];
        $refund_array['invoice_no'] = $_POST['invoice_no'];
        $refund_array['goods_state'] = '2';
        // var_dump($refund_array);die;
        $state = $model_refund->editRefundReturn($condition, $refund_array);
        if ($state) {
            echo $this->returnMsg(100, 'sucess', $state);exit();
        } else {
             echo $this->returnMsg(200, 'falie',$state);exit();
        }
        

    }  
}