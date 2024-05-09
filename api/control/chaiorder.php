<?php
defined('In718Shop') or exit('Access Invalid!');
    
class ChaiorderControl extends BaseControl{   
  public function member_card1Op() {
    echo 'zhi';
    /*$model_card=Model('card');
    $sql = "SELECT * FROM `718shop_member_card` WHERE `status` = 1 and `gonghao` IS NULL ";
    $member_cardinfo =Model()->query($sql);
    foreach ($member_cardinfo as $key => $value) {
       $card_info = $model_card->getMemberCardInfo($value['cardno']);
       if($card_info){
          $condition['member_id']=$value['member_id'];
          $update['gonghao']=$card_info['PersonalID'];
          $result=Model()->table('member_card')->where($condition)->update($update);
       }
    }*/
  }
  //满送代金劵-自动收货测试
public function mansongOp(){
  
  /**
     * 订单自动完成
     */
        $_break = false;
        $model_order = Model('order');
        $logic_order = Logic('order');
        $condition = array();
        // $condition['store_id'] = array('in','1,2,4,7,11,12,16,18,20,26,28,29,30,31');
        //$condition['store_id'] = array('not in','17,19,22,23,24,32,33,34');
        $condition['store_id'] = 4;
        $condition['order_state'] = ORDER_STATE_SEND;
        $condition['lock_state'] = 0;
        $condition['delay_time'] = array('lt',TIMESTAMP - ORDER_AUTO_RECEIVE_DAY * 86400);
        $condition['is_mode'] = 0;//一般贸易
        //分批，每批处理100个订单，最多处理5W个订单
        for ($i = 0; $i < 500; $i++){
            if ($_break) {
                break;
            }
            $order_list = $model_order->getOrderList($condition, '', '*', 'delay_time asc', 100);
            if (empty($order_list)) break;
            print_r($order_list);die;
            foreach ($order_list as $order_info) {
                $result = $logic_order->changeOrderStateReceive($order_info,'system','系统','超期未收货系统自动完成订单');
                if (!$result['state']) {
                    $this->log('实物订单超期未收货自动完成订单失败SN:'.$order_info['order_sn']); $_break = true; break;
                }
            }
        }
 
}
   /**
     * 收货
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param string $msg 操作备注
     * @return array
     */
    public function mansong_changeOrderStateReceive($order_info, $role, $user = '', $msg = '') {

        try {
          
            $member_id=$order_info['buyer_id'];
            $order_id = $order_info['order_id'];
            $model_order = Model('order');
            //$model_cw = Model('cw');

            //更新订单状态
            // $update_order = array();
            // $update_order['finnshed_time'] = TIMESTAMP;
            // $update_order['order_state'] = ORDER_STATE_SUCCESS;
            // $update = $model_order->editOrder($update_order,array('order_id'=>$order_id));
            // if (!$update) {
            //     throw new Exception('保存失败');
            // }
            $order_sn = $order_info['order_sn'];
      //$data = [
        //"tenantId" => 42,
        //"orderSn" => $order_sn,
        //"orderStatus" => "3"
      //];
            //$model_cw->cwOrderComplete($data);
            //$data['tenantId'] = 42;
            //$data['orderSn'] = Model()->table('order')->getfby_order_id($order_info['order_id'],'order_sn');
            //$data['orderStatus'] = 3;
            //$model_cw->cwOrderComplete($data);
            //查询分单中确认收货的订单总和
            $is_zorder = Model()->table('order')->getfby_order_id($order_info['order_id'],'is_zorder');
           // var_dump($is_zorder);die;
                if($is_zorder == 1){
                    $sql = "SELECT SUM(order_amount) as 'amount' FROM 718shop_order where order_state =40 AND  is_zorder =1 AND pay_sn =".$order_info['pay_sn'];
                    $result =Model()->query($sql);
                    $sql1 = "SELECT SUM(order_amount) as 'amount' FROM 718shop_order where order_state =0 AND  is_zorder =1 AND  payment_time >0 AND pay_sn =".$order_info['pay_sn'];
                    $result1 =Model()->query($sql1);
                    if(!empty($result1)){
                        $q_money = $result[0]['amount'] + $result1[0]['amount'];
                    }else{
                        $q_money = $result[0]['amount'];
                    }
                   //echo $result1[0]['amount'];
                    //查询总单的订单金额
                    $sql2 = "SELECT order_amount FROM 718shop_order where is_zorder =0 AND order_sn =".$order_info['pay_sn'];
                    $result2 =Model()->query($sql2);
                    
                   //var_dump($result2[0]['order_amount']);
                    //只有确认收货的订单金额=总单金额，才发劵
                    if($q_money == $result2[0]['order_amount']){
                        $amount = $q_money;
                    }else{
                        $amount = 0;
                    }
                }else{
                    $amount = $order_info['order_amount'];
                }
               
             //确认收货自动发放代金券
             $model_voucher = Model('voucher');
            $result=$model_voucher->mangzeng_voucher($order_info['buyer_id'], $order_info['store_id'], $amount);
            //$model_voucher->liebian_voucher($order_info['buyer_id'], $order_info['store_id']);
            // //添加订单日志
            // $data = array();
            // $data['order_id'] = $order_id;
            // $data['log_role'] = 'buyer';
            // $data['log_msg'] = '签收了货物';
            // $data['log_user'] = $user;
            // if ($msg) {
            //     $data['log_msg'] .= ' ( '.$msg.' )';
            // }
            // $data['log_orderstate'] = ORDER_STATE_SUCCESS;
            // $model_order->addOrderLog($data);

      //       //添加会员积分
      //       if (C('points_isuse') == 1){
      //           Model('points')->savePointsLog('order',array('pl_memberid'=>$order_info['buyer_id'],'pl_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
      //       }
      //       //添加会员经验值
      //       Model('exppoints')->saveExppointsLog('order',array('exp_memberid'=>$order_info['buyer_id'],'exp_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
      // //邀请人获得返利积分 
      // $model_member = Model('member');
      // $inviter_id = $model_member->table('member')->getfby_member_id($member_id,'inviter_id');
      // $inviter_name = $model_member->table('member')->getfby_member_id($inviter_id,'member_name');
      // $rebate_amount = ceil(0.01 * $order_info['order_amount'] * $GLOBALS['setting_config']['points_rebate']);
      // Model('points')->savePointsLog('rebate',array('pl_memberid'=>$inviter_id,'pl_membername'=>$inviter_name,'pl_points'=>$rebate_amount,'member_id'=>$member_id),true);

            return callback(true,'操作成功');
        } catch (Exception $e) {
            return callback(false,'操作失败');
        }
    }
 /**
  /**
  总单拆成订单
  */
  public function chaidanOp(){
    //总单id
    $order_id = $_GET['order_id'];
    try {
      $model = Model();
      $model->beginTransaction();
      //拆单
      $model_chaiorder = Model('chaiorder');
      $model_chaiorder->chaidan($order_id);

      $model->commit();
      //查询分单信息
      $model_order = Model('order');
      $condition['is_zorder']  = array('gt',0);
      $condition['z_order_id'] = $order_id;
       $f_order_info = $model_order->table('order')->where($condition)->select();
      // $f_order_info = $model_order->table('order')->where(array('z_order_id'=>$order_id,'is_zorder'=>1))->select();
      // echo 'zhi';
      print_r($f_order_info);die;
      $model_yundayin = Model('yundayin');
      foreach ($f_order_info as $k => $f_order) {
        $model_yundayin->_yundayin($f_order['order_sn']);
      }      

    }catch (Exception $e){
        $model->rollback();
        return callback(false, $e->getMessage());
    }
  } 
  function test1212Op() {
    $model_card=Model('card');
     $card_info = $model_card->getMemberCardInfo($_GET['cardno']);
     print_r($card_info);die;
    $sql = "SELECT * FROM `718shop_member_card` WHERE `status` = 1 and `gonghao` IS NULL ";
    $member_cardinfo =Model()->query($sql);
    foreach ($member_cardinfo as $key => $value) {
       $card_info = $model_card->getMemberCardInfo($value['cardno']);
       if($card_info){
          $condition['member_id']=$value['member_id'];
          $update['gonghao']=$card_info['PersonalID'];
          $result=Model()->table('member_card')->where($condition)->update($update);
       }
    }
    var_dump($result);die;

}
   /**
  更新发货人
  */
  public function updatedelieverOp(){

    $result = Model()->table('goods')->where(array('deliverer_id '=>0))->select();
    foreach ($result as $key => $value) {
      $com = Model()->table('goods_common')->where(array('goods_commonid'=>$value['goods_commonid']))->find();
      $condition1['goods_commonid'] = $com['goods_commonid'];
        $updata['deliverer_id'] = $com['deliverer_id'];
            $a = Model()->table('goods')->where($condition1)->update($updata);

    }
    if($a){
      echo '成功';
    }else{
      echo 'bu成功';
    }
  } 
  /**
  查询
  */
  public function selectdelieverOp(){
    $sql="SELECT 718shop_goods.* FROM 718shop_goods JOIN 718shop_goods_common ON 718shop_goods.goods_commonid=718shop_goods_common.goods_commonid where 718shop_goods.deliverer_id !=  718shop_goods_common.deliverer_id ";
    $result =Model()->query($sql);
        // $on = 'goods.goods_commonid=goods_common.goods_commonid';
        // $condition['goods.deliverer_id'] = array('neq','goods_common.deliverer_id');
        // $result = $this->table('goods,goods_common')->join('left')->on($on)->where($condition)->select();
      var_dump(count($result));
        print_r($result);die;
    $result = Model()->table('goods')->where(array('deliverer_id '=>0))->select();
    foreach ($result as $key => $value) {
      $com = Model()->table('goods_common')->where(array('goods_commonid'=>$value['goods_commonid']))->find();
      $condition1['goods_commonid'] = $com['goods_commonid'];
        $updata['deliverer_id'] = $com['deliverer_id'];
            $a = Model()->table('goods')->where($condition1)->update($updata);

    }
    if($a){
      echo '成功';
    }else{
      echo 'bu成功';
    }
  } 
  /*打印小票*/
  public function dayin1Op(){
    /*$model_order = Model('order');
    $condition['order_sn'] = $_GET['order_sn'];
    $order_info = $model_order->getFpOrderInfo($condition,array('order_common','order_goods','member'));*/
    $model_yundayin = Model('yundayin');
    //$model_yundayin-> sd_printorder('221503365',$order_info,0,1);
    $model_yundayin-> _yundayin1($_GET['order_sn'],0,0);
    echo 'zhi';
  } 
  /*打印小票*/
  public function dayin2Op(){
 
    echo 'zhi';
  } 
  /*打印小票*/
  public function dayinOp(){
    $order_sn = $_GET['order_sn'];
    $model_yundayin = Model('yundayin1');
    $model_yundayin->_yundayin($order_sn,0,0);
    echo 'over!';
  } 
   /**
     * 订单自动完成
     */
    public function auto_completeOp() {
      //健身房订单扫码核销
       $_break = false;
      $model_order = Model('order');
      $logic_order = Logic('order');
      $jian_con['jin_time'] = array('gt',0);
      $jian_con['order_state'] = 20;
      $jian_con['refund_state'] = 0;
      $model_order = Model('order');
      //分批，每批处理100个订单，最多处理5W个订单
      for ($i = 0; $i < 500; $i++){
          if ($_break) {
             break;
          }
          
          $jian_order_list = $model_order->getOrderList($jian_con, '', '*', 'jin_time asc', 100);
          if (empty($jian_order_list)) break;
          foreach ($jian_order_list as $order_info) {
            $result = $logic_order->changeOrderStateReceive($order_info,'system','系统','健身房订单超期未收货系统自动完成订单');
            if (!$result['state']) {
              $this->log('实物订单超期未收货自动完成订单失败SN:'.$order_info['order_sn']); $_break = true; break;
            }
          }
          
      }
     
  


        //实物订单发货后，一般贸易订单超期自动收货完成
        $_break = false;
        $model_order = Model('order');
        $logic_order = Logic('order');
        $condition = array();
        // $condition['store_id'] = array('in','1,2,4,7,11,12,16,18,20,26,28,29,30,31');
        $condition['store_id'] = array('not in','17,19,22,23,24,32,33,34');
        $condition['order_state'] = 30;
        $condition['lock_state'] = 0;
        $condition['delay_time'] = array('lt',time());
        $condition['is_mode'] = 0;//一般贸易
        //分批，每批处理100个订单，最多处理5W个订单
        for ($i = 0; $i < 500; $i++){
            if ($_break) {
                break;
            }
            $order_list = $model_order->getOrderList($condition, '', '*', 'delay_time asc', 100);
            if (empty($order_list)) break;
            
            foreach ($order_list as $order_info) {
                $result = $logic_order->changeOrderStateReceive($order_info,'system','系统','超期未收货系统自动完成订单');
                if (!$result['state']) {
                    $this->log('实物订单超期未收货自动完成订单失败SN:'.$order_info['order_sn']); $_break = true; break;
                }
                
                 //查询分单中确认收货的订单总和
                if($order_info['is_zorder'] == 1){
                    $sql = "SELECT SUM(order_amount) as 'amount' FROM 718shop_order where order_state =40 AND  is_zorder =1 AND pay_sn =".$order_info['pay_sn'];
                    $result =Model()->query($sql);
                    $sql1 = "SELECT SUM(order_amount) as 'amount' FROM 718shop_order where order_state =0 AND  is_zorder =1 AND  payment_time >0 AND pay_sn =".$order_info['pay_sn'];
                    $result1 =Model()->query($sql1);
                    if(!empty($result1)){
                        $q_money = $result[0]['amount'] + $result1[0]['amount'];
                    }else{
                        $q_money = $result[0]['amount'];
                    }
                   //echo $result1[0]['amount'];
                    //查询总单的订单金额
                    $sql2 = "SELECT order_amount FROM 718shop_order where is_zorder =0 AND order_sn =".$order_info['pay_sn'];
                    $result2 =Model()->query($sql2);
                    
                   //var_dump($result2[0]['order_amount']);
                    //只有确认收货的订单金额=总单金额，才发劵
                    if($q_money == $result2[0]['order_amount']){
                        $amount = $result[0]['order_amount'];
                    }else{
                       $amount = 0;
                    }
                }else{
                    $amount = $order_info['order_amount'];
                }
                $model_voucher = Model('voucher');
                $model_voucher->mangzeng_voucher($order_info['buyer_id'], $order_info['store_id'], $amount);
            }
        }
            //一般贸易和集货实物订单发货后，入驻商店铺所有订单超期自动收货完成
        $_break = false;
        $model_order = Model('order');
        $logic_order = Logic('order');
        $condition = array();
        $condition['store_id'] = array('in','17,19,22,23,24,32,33,34');
        $condition['order_state'] = ORDER_STATE_SEND;
        $condition['lock_state'] = 0;
        $condition['delay_time'] = array('lt',TIMESTAMP - 25 * 86400);
        // $condition['is_mode'] = 0;//一般贸易
        //分批，每批处理100个订单，最多处理5W个订单
        for ($i = 0; $i < 500; $i++){
            if ($_break) {
                break;
            }
            $order_list = $model_order->getOrderList($condition, '', '*', 'delay_time asc', 100);
            if (empty($order_list)) break;
            foreach ($order_list as $order_info) {
                $result = $logic_order->changeOrderStateReceive($order_info,'system','系统','超期未收货系统自动完成订单');
                if (!$result['state']) {
                    $this->log('实物订单超期未收货自动完成订单失败SN:'.$order_info['order_sn']); $_break = true; break;
                }
                
            }
        }
        //    //一般贸易实物订单发货后，个别入驻商跨境商品超期自动收货完成
        // $_break = false;
        // $model_order = Model('order');
        // $logic_order = Logic('order');
        // $condition = array();
        // $condition['store_id'] = 33;
        // $condition['order_state'] = ORDER_STATE_SEND;
        // $condition['lock_state'] = 0;
        // $condition['delay_time'] = array('lt',TIMESTAMP - 15 * 86400);
        // // 部分商品模式此店铺下所有商品//$condition['is_mode'] = 0;//一般贸易
        // //分批，每批处理100个订单，最多处理5W个订单
        // for ($i = 0; $i < 500; $i++){
        //     if ($_break) {
        //         break;
        //     }
        //     $order_list = $model_order->getOrderList($condition, '', '*', 'delay_time asc', 100);
        //     if (empty($order_list)) break;
        //     foreach ($order_list as $order_info) {
        //         $result = $logic_order->changeOrderStateReceive($order_info,'system','系统','超期未收货系统自动完成订单');
        //         if (!$result['state']) {
        //             $this->log('实物订单超期未收货自动完成订单失败SN:'.$order_info['order_sn']); $_break = true; break;
        //         }
        //     }
        // }
        //实物订单发货后，超期自动收货完成--MX集货
        $_break = false;
        $condition = array();
        $condition['order_state'] = ORDER_STATE_SEND;
        $condition['lock_state'] = 0;
        $condition['store_id'] = array('not in','17,19,22,23,24,32,33,34');
        // $condition['store_id'] = array('in','1,2,4,7,11,12,16,18,20,26,28,29,30,31');
        $condition['delay_time'] = array('lt',TIMESTAMP - JIHUO_ORDER_AUTO_RECEIVE_DAY * 86400);//集货
        $condition['is_mode'] = 2;//集货
        //分批，每批处理100个订单，最多处理5W个订单
        for ($i = 0; $i < 500; $i++){
            if ($_break) {
                break;
            }
            $order_list = $model_order->getOrderList($condition, '', '*', 'delay_time asc', 100);
            if (empty($order_list)) break;
            foreach ($order_list as $order_info) {
                $result = $logic_order->changeOrderStateReceive($order_info,'system','系统','超期未收货系统自动完成订单(集货)');
                if (!$result['state']) {
                    $this->log('实物订单超期未收货自动完成订单(集货)失败SN:'.$order_info['order_sn']); $_break = true; break;
                }
            }
        }
    
        //积分兑换订单发货后，超期自动收货完成--MX
        $model_pointorder = Model('pointorder');
        $_break = false;
        $condition = array();
        $condition['point_orderstate'] = 30;
        $condition['point_shippingtime'] = array('lt',TIMESTAMP - 7 * 86400);
        //分批，每批处理100个订单，最多处理5W个订单
        for ($i = 0; $i < 500; $i++) {
            if ($_break) {
                break;
            }
            $pointorder_list = $model_pointorder->getPointOrderList($condition, '*', ' ', ' ', 'point_shippingtime asc');
            if (empty($pointorder_list)) break;
            foreach ($pointorder_list as $pointorder_info) {
                $point_buyerid = $pointorder_info['point_buyerid'];
                $point_orderid = $pointorder_info['point_orderid'];
                //更新订单状态
                $update_pointorder = array();
                $update_pointorder['point_finnshedtime'] = TIMESTAMP;
                $update_pointorder['point_orderstate'] = ORDER_STATE_SUCCESS;
                $update = $model_pointorder->editPointOrder(array('point_orderid' => $point_orderid, 'point_buyerid' => $point_buyerid), $update_pointorder);
                if (!$update) {
                    throw new Exception('保存失败');
                }
                if (!$result['state']) {
                    $this->log('实物订单超期未收货自动完成订单(集货)失败SN:'.$order_info['order_sn']); $_break = true; break;
                }
            }
        }

    }
}