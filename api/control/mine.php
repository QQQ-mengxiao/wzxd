<?php
/**
 * 我的订单
 *
 *
 *
 ***/
defined('In718Shop') or exit('Access Invalid!');
defined('EBusinessID') or define('EBusinessID', 1764331);
defined('AppKey') or define('AppKey', '8085db3b-e9fc-4bb7-93df-97156537fa75');
//请求url
defined('ReqURL') or define('ReqURL', 'http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx');

class mineControl extends BaseControl
{
    /* 链接测试
    */
    public function testOp()
    {
        echo $this->returnMsg(100, '请求成功！', '');
        exit;
    }
     /* 个人中心——积分、经验值
     */
    public function mineJiOp()
    {
        // sleep(50);
        $condition['member_id'] = intval($_GET['member_id']);
        $info = Model()->table('member')->field('member_points,member_exppoints')->where($condition)->find(); 
        $info['member_id'] = $condition['member_id'];
        echo $this->returnMsg(100, '用户积分经验值查询成功', $info);
        
    }
    
     /* 积分规则描述接口
     */
    public function pointRuleOp()
    {
       
        $rule_info = Model()->table('setting')->field('value')->where(array('name'=>'points_rule'))->find();
        $info['point_rule'] = $rule_info['value'];
        echo $this->returnMsg(100, '积分规则文字描述', $info);
        
    }

     /* 会员等级
     */
    public function member_levelOp()
    {
        $member_id= intval($_GET['member_id']);
        $model_member = model('member');
        $member_level = $model_member->getmember_level($member_id);
        $member_info['member_id'] = $member_id;
        //$member_info['level'] = $member_level['level'];
        $member_info['member_level'] = $member_level['level_name'];
        
        echo $this->returnMsg(100, '会员等级',$member_info);
        
    }


    /* 我的信息列表
    */
    public function indexOp()
    {
        $member_id = intval($_POST['user_id']);
        if ($member_id == 0) {
            echo $this->returnMsg(200, '用户ID非空!', array('member_id' => $member_id));
            exit;
        }
        $model_member = model('member');
        $member_info = $model_member->table('member')->where(array('member_id' => $member_id))->find();
        if (!$member_info) {
            echo $this->returnMsg(300, '本系统无此用户!', array('member_id' => $member_id));
            exit;
        }
        // sleep(50);
        $model_order = model('order');
        $order_count = array();
        // 查询我的订单待付款订单数
        $order_count['no_pay'] = $model_order->getOrderCount(array('order_state' => 10, 'buyer_id' => $member_id));
        if (empty($order_count['no_pay'])) {
            $order_count['no_pay'] = 0;
        }
        // 查询我的订单已发货-运输中订单数
        $order_count['no_yun'] = $model_order->getOrderCount(array('order_state' => 25, 'is_zorder' => array(array('eq', 1), array('eq', 2), 'or'), 'buyer_id' => $member_id));
        if (empty($order_count['no_yun'])) {
            $order_count['no_yun'] = 0;
        }
        // 查询我的订单待发货订单数
        $order_count['no_send'] = $model_order->getOrderCount(array('order_state' => 20, 'is_zorder' => array(array('eq', 1), array('eq', 2), 'or'), 'buyer_id' => $member_id));
        if (empty($order_count['no_send'])) {
            $order_count['no_send'] = 0;
        }
        // 查询我的订单待收货订单数
        $order_count['no_receive'] = $model_order->getOrderCount(array('order_state' => 30, 'is_zorder' => array(array('eq', 1), array('eq', 2), 'or'), 'buyer_id' => $member_id));
        if (empty($order_count['no_receive'])) {
            $order_count['no_receive'] = 0;
        }
        // 查询我的订单待评价订单数
        $order_count['no_evaluation'] = $model_order->getOrderCount(array('order_state' => 40, 'evaluation_state' => 0, 'is_zorder' => array(array('eq', 1), array('eq', 2), 'or'), 'buyer_id' => $member_id, 'lock_state' => 0));
        if (empty($order_count['no_evaluation'])) {
            $order_count['no_evaluation'] = 0;
        }
        $where['buyer_id'] = $member_id;
        $where['refund_state'] = array('neq', 3);
        // 查询我的退款订单订单数
        $order_count['no_refund'] = Model('refund_return')->getRefundCount($where);
        //if(empty($order_count['no_evaluation'])){
        //$order_count['no_evaluation']  = 0;
        //} 
        // $order_list = array_values($order_list);
        //用户昵称和图片
        $order_count['member_name'] =  $member_info['member_name'];
        $order_count['member_avatar'] = UPLOAD_SITE_URL.'/'.ATTACH_AVATAR.'/'.$member_info['member_avatar'];
        //如果有用户头像的值，avatar_state 为1
        if(!empty($member_info['member_avatar'])){
            $order_count['avatar_state']  = 1;
        }else{
            $order_count['avatar_state']  = 0;
        }
        echo $this->returnMsg(100, '我的信息列表订单数', $order_count);
    }

    /* 我的——订单列表
    */
    public function order_listOp()
    {
        $order_state = intval($_POST['order_state']);
        $member_id = intval($_POST['user_id']);
        if ($member_id == 0) {
            echo $this->returnMsg(200, '用户ID非空!', array('member_id' => $member_id));
            exit;
        }
        // sleep(50);
        $model_member = model('member');
        $member_info = $model_member->table('member')->where(array('member_id' => $member_id))->find();
        if (!$member_info) {
            echo $this->returnMsg(300, '本系统无此用户!', array('member_id' => $member_id));
            exit;
        }
        $model_order = model('order');
        $order_list = array();
        // 查询我的订单订单列表
        if ($order_state == 10) {
            // $order_list = $model_order->apiGetOrderList(array('order_state' => $order_state, 'buyer_id' => $member_id), '*', 'order_id desc', '', array('order_common', 'order_goods', 'store'));
            $order_list = $model_order->apiGetOrderListStatePage($member_id," AND order_state=".$order_state);
        }
        if ($order_state == 20 || $order_state == 30 || $order_state == 25) {
            // sleep(50);
            // $order_list = $model_order->apiGetOrderList(array('order_state' => $order_state, 'is_zorder' => array(array('eq', 1), array('eq', 2), 'or'), 'buyer_id' => $member_id), '*', 'order_id desc', '', array('order_common', 'order_goods', 'store'));
            $order_list = $model_order->apiGetOrderListStatePage($member_id," AND order_state=".$order_state." AND is_zorder>0");
        }
        if ($order_state == 40) {
            // sleep(50);
            // $order_list = $model_order->apiGetOrderList(array('order_state' => $order_state, 'evaluation_state' => 0, 'is_zorder' => array(array('eq', 1), array('eq', 2), 'or'), 'buyer_id' => $member_id), '*', 'order_id desc', '', array('order_common', 'order_goods', 'store'));
            $order_list = $model_order->apiGetOrderListStatePage($member_id," AND order_state=".$order_state." AND evaluation_state=0 AND is_zorder>0");
        }
        $model_refund_return = Model('refund_return');
        $order_list = $model_refund_return->getGoodsRefundList($order_list);
        foreach ($order_list as $key => $value) {
            $order_list[$key]['add_time'] = date('Y-m-d H:i:s', $value['add_time']);
            $order_list[$key]['payment_time'] = date('Y-m-d H:i:s', $value['payment_time']);
            $order_list[$key]['finnshed_time'] = date('Y-m-d H:i:s', $value['finnshed_time']);
            $biaoshi=0;
            foreach ($value['extend_order_goods'] as $k => $v) {
                if($v['refund']!=1){
                      $biaoshi = 1;
                }
                $order_list[$key]['extend_order_goods'][$k]['by_post'] = $value['by_post'];
            }
            if($value['lock_state']>0){
              $order_list[$key]['tk_state'] = 1;//退款中
            }else{
                if($biaoshi==1){
                   $order_list[$key]['tk_state'] = 0;//有单独退款正在退款或者已完成消失
                }else{
                  $order_list[$key]['tk_state'] = 2;//显示为退款
                }
            }
        }
        if (empty($order_list)) {
            echo $this->returnMsg(400, '无订单记录!', '');
            exit;
        }
        $order_list = array_values($order_list);
        echo $this->returnMsg(100, '我的信息-订单列表查询成功!', $order_list);
    }

    /* 我的——所有订单列表
    */
    public function allorder_listOp()
    {
        $member_id = intval($_POST['user_id']);
        $num_page = intval($_POST['num_page']);
        if ($member_id <= 0) {
            echo $this->returnMsg(200, '用户ID非空!', array('member_id' => $member_id));
            exit;
        }
        $model_member = model('member');
        $member_info = $model_member->table('member')->where(array('member_id' => $member_id))->find();
        if (!$member_info) {
            echo $this->returnMsg(300, '本系统无此用户!', array('member_id' => $member_id));
            exit;
        }
        $model_order = model('order');
        $order_list = array();
        $condition = " AND NOT(is_zorder=0 AND order_state IN (20,25,30,40))";
        $order_list = $model_order->apiGetOrderListPage($member_id,$num_page,$condition);
        $sql_count = "SELECT count(*) AS page_count FROM 718shop_order WHERE buyer_id = " . $member_id . " AND delete_state = 0".$condition;
        $count_result = Model()->query($sql_count);
        $page_count = $count_result[0]['page_count'];
        $max_page_num = ceil($page_count/10);//echo $sql_count;die;
        $model_refund_return = Model('refund_return');
        $order_list = $model_refund_return->getGoodsRefundList($order_list);
        foreach ($order_list as $key => $value) {
            $order_list[$key]['add_time'] = date('Y-m-d H:i:s', $value['add_time']);
            $order_list[$key]['payment_time'] = date('Y-m-d H:i:s', $value['payment_time']);
            $order_list[$key]['finnshed_time'] = date('Y-m-d H:i:s', $value['finnshed_time']);
             $biaoshi=0;
            foreach ($value['extend_order_goods'] as $k => $v) {
                if($v['refund']!=1){
                      $biaoshi=1;
                }
                $order_list[$key]['extend_order_goods'][$k]['by_post'] = $value['by_post'];
            }
            if($value['lock_state']>0){
              $order_list[$key]['tk_state'] = 1;//退款中
            }else{
                if($biaoshi==1){
                   $order_list[$key]['tk_state'] = 0;//有单独退款正在退款或者已完成消失
                }else{
                  $order_list[$key]['tk_state'] = 2;//显示为退款
                }
            }
            // if ($value['is_zorder'] == 0 && in_array($value['order_state'], array(20, 30, 40))) {
            //     unset($order_list[$key]);
            // }
        }

        if (empty($order_list)) {
            $orderList['end'] = 1;
            echo $this->returnMsg(400, '无订单记录!', $orderList);
            exit;
        }
        $order_list = array_values($order_list);
        $orderList['order_list'] = $order_list;
        $orderList['end'] = 0;
        if($num_page > $max_page_num){
            $orderList['end'] = 1;
            $orderList['order_list'] = [];
        }
        echo $this->returnMsg(100, '我的-所有订单列表查询成功!', $orderList);
    }

    /* 我的——订单详情
    */
    public function order_infoOp()
    {
        $order_id = intval($_POST['order_id']);
        $member_id = intval($_POST['user_id']);
        if ($order_id <= 0) {
            echo $this->returnMsg(200, '订单ID非空!', array('member_id' => $member_id));
            exit;
        }
        if ($member_id == 0) {
            echo $this->returnMsg(200, '用户ID非空!', array('member_id' => $member_id));
            exit;
        }
        $model_member = model('member');
        $member_info = $model_member->table('member')->where(array('member_id' => $member_id))->find();
        if (!$member_info) {
            echo $this->returnMsg(300, '本系统无此用户!', array('member_id' => $member_id));
            exit;
        }

        $model_order = Model('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $member_id;
        $order_info = $model_order->getFpOrderInfo($condition, array('order_goods', 'order_common', 'store'));

        if (empty($order_info) || $order_info['delete_state'] == 2) {
            echo $this->returnMsg(400, '本系统无此订单!', array('order_id' => $order_id));
            exit;
        }
        // 
        $model_refund_return = Model('refund_return');
        $order_list = array();
        $order_list[$order_id] = $order_info;
        // 根据订单取商品的退款退货状态
        $order_list = $model_refund_return->getGoodsRefundList($order_list, 1);
        $order_info = $order_list[$order_id];
        $refund_all = $order_info['refund_list'][0];
        if (!empty($refund_all) && $refund_all['seller_state'] < 3) {
            //订单全部退款商家审核状态:1为待审核,2为同意,3为不同意
            $order_info['refund_all'] = $refund_all;
        }
        $refund_list = $model_refund_return->table('refund_return')->where($condition)->order('refund_id desc')->select();
// var_dump($refund_list);die;
        //显示系统自动取消订单日期
        if ($order_info['order_state'] == 10) {

            $order_info['order_cancel_day'] = $order_info['add_time'] + 1440 * 60;
        }

        //显示快递信息
        if ($order_info['shipping_code'] != '') {
            $express = rkcache('express', true);
            $order_info['express_info']['e_code'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_code'];
            $order_info['express_info']['e_name'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_name'];
            $order_info['express_info']['e_url'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_url'];
        }

        //显示系统自动收货时间 
        if ($order_info['order_state'] == 30) {
            if ($order_info['is_mode'] == 0) {
                $order_info['order_confirm_day'] = $order_info['delay_time'] + 10 * 24 * 3600;
            } elseif ($order_info['is_mode'] == 2) {
                $order_info['order_confirm_day'] = $order_info['delay_time'] + 25 * 24 * 3600;
            }

        }

        //如果订单已取消，取得取消原因、时间，操作人
        if ($order_info['order_state'] == 0) {
            $order_info['close_info'] = $model_order->getOrderLogInfo(array('order_id' => $order_info['order_id']), 'log_id desc');
        }
        $order_info['goods_count'] = 0;
        $order_info['goods_amount'] = 0;
        $biaoshi = 0;
        foreach ($order_info['extend_order_goods'] as $key => $value) {
            $order_info['extend_order_goods'][$key]['goods_image'] = $model_order->cthumb($value['goods_image'], 240, $value['store_id']);
            $order_info['extend_order_goods'][$key]['goods_type_cn'] = $model_order->orderGoodsType($value['goods_type']);
            // $order_info['extend_order_goods'][$key]['goods_type']=orderTypeName($order_info['order_type']);
            $order_info['extend_order_goods'][$key]['goods_type'] = goodsTypeName($value['goods_type']);
            // if ($value['goods_type'] == 4) {
            //             $model_xianshi_goods = Model('p_xianshi_goods');
            //             $model_xianshi = Model('p_xianshi');
            //             $condition_xs=array();
            //             $condition_xs['goods_id']=$value['goods_id'];
            //             $condition_xs['end_time'] = array('gt', TIMESTAMP);
            //             $xianshigoods = $model_xianshi_goods->getXianshiGoodsInfo( $condition_xs);
            //             // $xianshigoods = $model_xianshi_goods->getXianshiGoodsInfo(array('goods_id' => $value['goods_id'] ));
            //             $xianshi_info = $model_xianshi->getXianshiInfo(array('xianshi_id' => $xianshigoods['xianshi_id']));
            //            $order_info['extend_order_goods'][$key]['xianshi_type']=$xianshi_info['xianshi_type'];
            //             if($xianshi_info['xianshi_type']==1){
            //             $order_info['extend_order_goods'][$key]['goods_type']='限时秒杀';
            //             }else{
            //                 $order_info['extend_order_goods'][$key]['goods_type']='限时折扣';
            //             }
                        
            //     }
            // $value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
            // var_dump($refund_all['goods_id']);die;
            // $array_refund[]=$value['refund'];
            if ($value['refund'] != 1) {
                $biaoshi = 1;
            }
            if (!empty($refund_list[0]) && $refund_list[0]['goods_id'] == '0' && $order_info['lock_state'] > 0) {
                $order_info['extend_order_goods'][$key]['refund'] = '0';
            }
            if ($value['goods_type'] == 6) {
                $order_info['zengpin_list'][] = $value;
            } else {
                $order_info['goods_count'] += $value['goods_num'];
            }
            // else {
            //     $order_info['goods_list'][] = $value;
            // }
            $order_info['goods_amount'] += $value['goods_price'];
        }
        // var_dump($order_info['lock_state']);die;
        if ($order_info['lock_state'] > 0) {
            $order_info['tk_state'] = 1;//退款中
        } else {
            if ($biaoshi == 1) {
                $order_info['tk_state'] = 0;//有单独退款正在退款或者已完成消失
            } else {
                $order_info['tk_state'] = 2;//显示为退款
            }
        }
        $order_info['goods_amount'] = ncPriceFormat($order_info['goods_amount']);
        // if (empty($order_info['zengpin_list'])) {
        //     $order_info['goods_count'] = count($order_info['goods_list']);
        // }else {
        //     $order_info['goods_count'] = count($order_info['goods_list']) + 1;
        // }

        //卖家发货信息
        if (!empty($order_info['extend_order_common']['daddress_id'])) {
            $daddress_info = Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_common']['daddress_id']));
            $order_info['extend_order_common']['daddress_id'] = $daddress_info;

        }
        $order_info['add_time'] = date('Y-m-d H:i:s', $order_info['add_time']);
        $order_info['payment_time'] = date('Y-m-d H:i:s', $order_info['payment_time']);
        $order_info['finnshed_time'] = date('Y-m-d H:i:s', $order_info['finnshed_time']);
		$ziti_ladder_time = Model()->table('order_common')->getfby_order_id($order_id,'ziti_ladder_time');
        $order_info['ziti_ladder_time'] = $ziti_ladder_time==0?'':date('Y-m-d H:i:s', $ziti_ladder_time);

         $b = mb_strpos($order_info['promotion_info'],"title") + mb_strlen("title='");
         $e = mb_strpos($order_info['promotion_info'],"' target=") - $b;
         $c = mb_substr($order_info['promotion_info'],$b,$e);
         //var_dump($b);
        echo $this->returnMsg(100, '订单详情查询成功!', $order_info);
    }
    //截取指定2个字符之间字符串
    public function getNeedBetween($kw1,$mark1,$mark2){
        
        $ed =stripos($kw,$mark2);
        var_dump($st);
        var_dump($ed);die;
        /*if(($st==false||$ed==false)||$st>=$ed)
            return 0;
        $kw=substr($kw,($st+1),($ed-$st-1));*/
        return $kw;
    }
    /**
     * 我的——买家订单状态操作(取消订单、收货、订单回收站)
     *
     */
    public function change_stateOp()
    {
        $state_type = $_POST['state_type'];
        $order_id = intval($_POST['order_id']);
        $member_id = intval($_POST['member_id']);
        // 操作备注
        $state_info = $_POST['state_info'] == '' ? '' : $_POST['state_info'];
        if ($order_id <= 0 || $member_id <= 0) {
            echo $this->returnMsg(200, '用户ID或订单ID异常!', array('member_id' => $member_id, 'order_id' => $order_id));
            exit;
        }
        $model_order = Model('order');
        $model_cw = Model('cw');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $member_id;
        $order_info = $model_order->getFpOrderInfo($condition, array('order_common'));
        // 取消订单
        if ($state_type == 'order_cancel') {
            if ($order_info['order_state'] == 0) {
                echo $this->returnMsg(400, '订单为已取消单，无需取消操作!', '');
                exit;
            }
            $result = $this->order_cancel($order_info, $order_info['buyer_name'], $state_info);
        } else if ($state_type == 'order_receive') {
            // 收货
            if ($order_info['order_state'] == 40) {
                echo $this->returnMsg(400, '订单已收货，无需再次收货操作!', '');
                exit;
            }
            $result = $this->order_receive($order_info);
            if ($result) {
                $order_sn = Model()->table('order')->getfby_order_id($order_id, 'order_sn');
                $data = [
                    "tenantId" => 42,
                    "orderSn" => $order_sn,
                    "orderStatus" => "3"
                ];
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
                        $amount= $result[0]['amount'];
                    }else{
                        $amount = 0;
                    }
                }else{
                    $amount = $order_info['order_amount'];
                }
                //var_dump($order['amount']);die;
                //$model_cw->cwOrderComplete($data);
                $model_voucher = Model('voucher');
             //     if($order_info['buyer_id']==28956){
             //   var_dump( $amount);die;
             // }
                $res = $model_voucher->mangzeng_voucher($order_info['buyer_id'], $order_info['store_id'], $amount);
                //var_dump( $res);die;
                $model_voucher->liebian_voucher($order_info['buyer_id'], $order_info['store_id']);
                //$data['tenantId'] = 42;
                //$data['orderSn'] = Model()->table('order')->getfby_order_id($order_id,'z_order_sn');
                //$data['orderStatus'] = 3;
            }

        } else if (in_array($state_type, array('order_delete', 'order_drop', 'order_restore'))) {
            //订单放进回收箱
            $result = $this->order_recycle($order_info, $state_type);
        } else {
            exit();
        }

        if ($result) {
            $new_info = $model_order->getFpOrderInfo($condition, array('order_common'));
            if ($res['state']) {
                $new_info['voucher_list'] = $res['voucher'];
            } else {
                $new_info['voucher_list'] = null;
            }//
            // var_dump($new_info);die;
            echo $this->returnMsg(100, '订单状态更改成功!', $new_info);
        } else {
            echo $this->returnMsg(100, '订单状态更改失败!', $new_info);
        }
    }

    /**
     * 退款记录列表页
     *
     */
    public function refund_listOp()
    {

        $model_refund = Model('refund_return');
        $condition = array();
        $buyer_id = intval($_POST['member_id']);
        $state = intval($_POST['state']);
        if ($buyer_id <= 0) {
            echo $this->returnMsg(200, '用户ID异常!', '');
            exit;
        } else {
            $condition['buyer_id'] = $buyer_id;
        }
        //退款处理状态，1为查询未处理的退款订单列表，其余则为全部
        if ($state == 1) {
            $condition['refund_state'] = array('lt', 3);
        }
        ///取退款记录
        $refund_list['refund_list'] = $model_refund->getRefundReturnList($condition);
        foreach ($refund_list['refund_list'] as $key => $value) {
            $refund_list['refund_list'][$key]['add_time'] = date('Y-m-d H:i:s', $value['add_time']);
            $refund_list['refund_list'][$key]['seller_time'] = date('Y-m-d H:i:s', $value['seller_time']);
            $refund_list['refund_list'][$key]['admin_time'] = date('Y-m-d H:i:s', $value['admin_time']);
            $condition = array();
            $condition['order_id'] = $value['order_id'];
            $order = $model_refund->getRightOrderList($condition);
            $refund_goods = array();
            foreach ($order['goods_list'] as $k => $v) {
                if ($value['goods_id'] == 0) {
                    $refund_goods[$k]['goods_image'] = cthumb($v['goods_image'], 60, $v['store_id']);
                    $refund_goods[$k]['goods_price'] = $v['goods_price'];
                    $refund_goods[$k]['goods_name'] = $v['goods_name'];
                    $refund_goods[$k]['goods_num'] = $v['goods_num'];
                    $refund_goods[$k]['goods_id'] = $v['goods_id'];
                    $model_goods = Model('goods');
                    $goods_info = $model_goods->getGoodsInfoByID($v['goods_id']);
                    $refund_goods[$k]['is_group_ladder'] = $goods_info['is_group_ladder'];
                     $refund_goods[$k]['by_post'] = Model()->table('order')->getfby_order_id($v['order_id'],'by_post');
                } else {
                    if ($value['goods_id'] == $v['goods_id']) {
                        $refund_goods[$k]['goods_image'] = cthumb($v['goods_image'], 60, $v['store_id']);
                        $refund_goods[$k]['goods_price'] = $v['goods_price'];
                        $refund_goods[$k]['goods_name'] = $v['goods_name'];
                        $refund_goods[$k]['goods_num'] = $v['goods_num'];
                        $refund_goods[$k]['goods_id'] = $v['goods_id'];
                        $model_goods = Model('goods');
                        $goods_info = $model_goods->getGoodsInfoByID($v['goods_id']);
                        $refund_goods[$k]['is_group_ladder'] = $goods_info['is_group_ladder'];
                         $refund_goods[$k]['by_post'] = Model()->table('order')->getfby_order_id($v['order_id'],'by_post');
                    }
                }
               

            }
            $refund_list['refund_list'][$key]['goods_list'] = $refund_goods;
        }
        if (empty($refund_list)) {
            echo $this->returnMsg(300, '无售后订单记录!', '');
            exit;
        }

        // //获得退货退款的店铺列表
        $store_list = $model_refund->getRefundStoreList($refund_list['refund_list']);
        $refund_list['store_list'] = $store_list;
        echo $this->returnMsg(100, '售后列表查询成功!', $refund_list);
    }

    /*
    我的——评价图片上传
    */
    public function evaluate_uploadOp()
    {
        /**
         * 上传图片
         */
        $goods_id = intval($_POST['goods_id']);

        $upload = new UploadFile();
        $dir = DIR_UPLOAD_EVALUATE . '/' . $goods_id;
        $result = $upload->set('default_dir', $dir);
        $upload->set('allow_type', array('jpg', 'jpeg', 'gif', 'png'));
        $result = $upload->upfile('evaluate');
        if ($result) {
            $_POST['pic'] = $upload->file_name;
        } else {
            echo $this->returnMsg(200, '评价晒图上传失败', null);
            exit();
        }
        if ($result) {
            $data = array();
            $data['file_goods_id'] = $goods_id;
            $data['file_name'] = $_POST['pic'];
            $data['file_path'] = UPLOAD_SITE_URL . '/' . $dir . '/' . $_POST['pic'];
            $res = array('code' => '100', 'message' => '评价晒图上传成功', 'data' => $data);
            echo json_encode($res, 320);
        }

    }

    /**
     * 我的——发表评价(待优化)
     *
     */
    public function evaluate_addOp()
    {

        $order_id = intval($_POST['order_id']);
        $member_id = intval($_POST['member_id']);
        $member_name = $_POST['member_name'];
        $_POST['formData'] = htmlspecialchars_decode($_POST['formData']);
        $_POST['formData'] = json_decode(stripslashes($_POST['formData']), true);
        $goods = $_POST['formData'];
        // var_dump($goods);die;
        // $evaluate_image = $this->evaluate_upload($goods_id);
        // echo $this->returnMsg(300, '22', $evaluate_image);
        if ($order_id <= 0 || $member_id <= 0) {
            echo $this->returnMsg(200, '用户ID或订单ID异常!', '');
            exit;
        }

        $model_order = Model('order');
        $model_store = Model('store');
        $model_evaluate_goods = Model('evaluate_goods');
        // $model_evaluate_store = Model('evaluate_store');

        //获取订单信息
        $order_info = $model_order->getOrderInfo(array('order_id' => $order_id));
        //判断订单身份
        if ($order_info['buyer_id'] != $member_id) {
            echo $this->returnMsg(300, '用户不是该订单的购买者,不能进行评论!', '');
            exit;
        }
        //订单为'已收货'状态，并且未评论
        $order_info['evaluate_able'] = $model_order->getOrderOperateState('evaluation', $order_info);
        if (empty($order_info) || !$order_info['evaluate_able']) {
            echo $this->returnMsg(400, '订单为空或订单不可评论!', '');
            exit;
        }

        //查询店铺信息
        $store_info = $model_store->getStoreInfoByID($order_info['store_id']);
        if (empty($store_info)) {
            echo $this->returnMsg(600, '店铺信息为空!', '');
            exit;
        }

        //获取订单商品
        $order_goods = $model_order->getOrderGoodsList(array('order_id' => $order_id));
        if (empty($order_goods)) {
            echo $this->returnMsg(500, '订单中没有商品需要评论!', '');
            exit;
        }
        // 商品评价信息集合
        $evaluate_goods_array = array();
        $goodsid_array = array();
        foreach ($order_goods as $value) {
            foreach ($goods as $k => $v) {
                if ($value['goods_id'] == $v['goods_id']) {
                    $evaluate_score = intval($v['score']);
                    //如果未评分，默认为5分
                    if ($evaluate_score <= 0 || $evaluate_score > 5) {
                        $evaluate_score = 5;
                    }
                    //默认评语
                    $evaluate_comment = $v['comment'];
                    if (empty($evaluate_comment)) {
                        $evaluate_comment = '商品物超所值，物美价廉，性价比不错，推荐！';
                    }
                    //晒单照片
                    if (!empty($v['evaluate_image'])) {
                        $evaluate_image = implode(',', $v['evaluate_image']);
                    } else {
                        $evaluate_image = '';
                    }
                }
            }
            $evaluate_goods_info = array();
            $evaluate_goods_info['geval_orderid'] = $order_id;
            $evaluate_goods_info['geval_orderno'] = $order_info['order_sn'];
            $evaluate_goods_info['geval_ordergoodsid'] = $value['rec_id'];
            $evaluate_goods_info['geval_goodsid'] = $value['goods_id'];
            $evaluate_goods_info['geval_goodsname'] = $value['goods_name'];
            $evaluate_goods_info['geval_goodsprice'] = $value['goods_price'];
            $evaluate_goods_info['geval_goodsimage'] = $value['goods_image'];
            $evaluate_goods_info['geval_scores'] = $evaluate_score;
            $evaluate_goods_info['geval_content'] = $evaluate_comment;
            $evaluate_goods_info['geval_image'] = $evaluate_image;
            $evaluate_goods_info['geval_isanonymous'] = $_POST['anony'] ? 1 : 0;
            $evaluate_goods_info['geval_addtime'] = time();
            $evaluate_goods_info['geval_storeid'] = $store_info['store_id'];
            $evaluate_goods_info['geval_storename'] = $store_info['store_name'];
            $evaluate_goods_info['geval_frommemberid'] = $member_id;
            $evaluate_goods_info['geval_frommembername'] = $member_name;

            $evaluate_goods_array[] = $evaluate_goods_info;

            $goodsid_array[] = $value['goods_id'];

        }
        // var_dump($evaluate_goods_array);die;
        $result = $model_evaluate_goods->addEvaluateGoodsArray($evaluate_goods_array, $goodsid_array);
        // $store_desccredit = intval($_POST['store_desccredit']);
        // if($store_desccredit <= 0 || $store_desccredit > 5) {
        //     $store_desccredit= 5;
        // }
        // $store_servicecredit = intval($_POST['store_servicecredit']);
        // if($store_servicecredit <= 0 || $store_servicecredit > 5){
        //         $store_servicecredit = 5;
        //     }
        // $store_deliverycredit = intval($_POST['store_deliverycredit']);
        // if($store_deliverycredit <= 0 || $store_deliverycredit > 5) {
        //     $store_deliverycredit = 5;
        // }
        // //添加店铺评价
        // if (!$store_info['is_own_shop']) {
        //     $evaluate_store_info = array();
        //     $evaluate_store_info['seval_orderid'] = $order_id;
        //     $evaluate_store_info['seval_orderno'] = $order_info['order_sn'];
        //     $evaluate_store_info['seval_addtime'] = time();
        //     $evaluate_store_info['seval_storeid'] = $store_info['store_id'];
        //     $evaluate_store_info['seval_storename'] = $store_info['store_name'];
        //     $evaluate_store_info['seval_memberid'] = $member_id;
        //     $evaluate_store_info['seval_membername'] = $member_name;
        //     $evaluate_store_info['seval_desccredit'] = $store_desccredit;
        //     $evaluate_store_info['seval_servicecredit'] = $store_servicecredit;
        //         $evaluate_store_info['seval_deliverycredit'] = $store_deliverycredit;
        // }
        // $model_evaluate_store->addEvaluateStore($evaluate_store_info);

        //更新订单信息并记录订单日志
        $state = $model_order->editOrder(array('evaluation_state' => 1), array('order_id' => $order_id));
        $model_order->editOrderCommon(array('evaluation_time' => time()), array('order_id' => $order_id));
        if ($state) {
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = 'buyer';
            $data['log_msg'] = '已评价了订单';
            $model_order->addOrderLog($data);
        }
        if ($result) {
            echo $this->returnMsg(100, '订单评价成功!', '');
        } else {
            echo $this->returnMsg(200, '订单评价失败!', '');
        }

    }

    /**
     * 取消订单
     */
    private function order_cancel($order_info, $member_name, $state_info)
    {
        $model_order = Model('order');
        $logic_order = Logic('order');

        $if_allow = $model_order->getOrderOperateState('buyer_cancel', $order_info);
        if (!$if_allow) {
            echo $this->returnMsg(300, '无权操作!', '');
            exit;
        }

        $result = $logic_order->changeOrderStateApiCancel($order_info, 'buyer', $member_name, $state_info);
        if($result){
            $this->send($order_info['order_id']);
            $sql = "SELECT order_id FROM 718shop_order where buyer_id = ".$order_info['buyer_id']." AND ( order_state > 0 OR lock_state > 0 )";
            $result1 =Model()->query($sql);
            if(empty($result1)){
                $model_member = Model('member');
                $update_array['is_xinren']   = 1;
                $update = $model_member->table('member')->where(array('member_id'=>$order_info['buyer_id']))->update($update_array);
            }
            
        }
        return $result;
    }

    /**
     * mq发送数据
     */
    private function send($data){

        $rabbitMQ = new RabbitMQ();

        $connection = $rabbitMQ->connection('10.10.11.141', 5672, 'wzxd', 'WZXDRMQpython~XX2');

        if($connection){

            $rabbitMQ->sendTopic($connection,$data,'order_cancel_topic_exchange','cancel.#');

            $rabbitMQ->close($connection);

        }

    }

    /**
     * 收货
     */
    private function order_receive($order_info)
    {
        $model_order = Model('order');
        $logic_order = Logic('order');
        $if_allow = $model_order->getOrderOperateState('receive', $order_info);
        if (!$if_allow) {
            echo $this->returnMsg(300, '无权操作!', '');
            exit;
        }

        $result = $logic_order->changeOrderStateApiReceive($order_info, 'buyer', $order_info['buyer_name']);
        return $result;
    }

    /**
     * 回收站
     */
    private function order_recycle($order_info, $state_type)
    {
        $model_order = Model('order');
        $logic_order = Logic('order');
        $state_type = str_replace(array('order_delete', 'order_drop', 'order_restore'), array('delete', 'drop', 'restore'), $state_type);
        $if_allow = $model_order->getOrderOperateState($state_type, $order_info);
        if (!$if_allow) {
            echo $this->returnMsg(300, '无权操作!', '');
            exit;
        }

        $result = $logic_order->changeOrderStateApiRecycle($order_info, 'buyer', $state_type);
        return $result;
    }

    /**
     * 从第三方取快递信息
     *
     */
    public function get_expressOp()
    {
        if($_GET['order_id']>0){
            $order_id = $_GET['order_id'];
        }else{
            $order_id = Model()->table('order')->getfby_order_sn($_GET['order_sn'],'order_id');
        }

        $sql = 'SELECT so.order_sn,so.shipping_code,se.e_code,soc.reciver_info,se.e_name FROM 718shop_order so LEFT JOIN 718shop_order_common soc ON so.order_id=soc.order_id LEFT JOIN 718shop_express se ON soc.shipping_express_id=se.id WHERE so.order_id='.$order_id;

        $order_info = Model()->query($sql)[0];

        $order_info['mob_phone'] = unserialize($order_info['reciver_info'])['mob_phone'];

        unset($order_info['reciver_info']);

        if ($order_info['e_code'] == 'SF') {
        $requestData = array(
                'OrderCode' => $order_info['order_sn'],
                'ShipperCode' => $order_info['e_code'],
                'LogisticCode' => $order_info['shipping_code'],
                'CustomerName' => substr($order_info['mob_phone'], -4),
            );
        } else {
            $requestData = array(
                'OrderCode' => $order_info['order_sn'],
                'ShipperCode' => $order_info['e_code'],
                'LogisticCode' => $order_info['shipping_code'],
        );
        }

        $requestData = json_encode($requestData, true);

        $datas = array(
            'EBusinessID' => EBusinessID,
            'RequestType' => '1002',//8001
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, AppKey);
        $result = $this->sendPost(ReqURL, $datas);

        $result = json_decode($result, true);

        if (!$result['Traces']) {
            $result['LogisticCode'] = $order_info['shipping_code'];
            $result['ShipperCode'] = $order_info['e_name'];
            $result['Traces'] = [];
            $array['result'] = $result;
			exit(json_encode($array));
		}

        $result['Traces'] = array_reverse($result['Traces']);
        $result['ShipperCode'] = $order_info['e_name'];

        $output = array();

        $output['result'] = $result;

        echo json_encode($output);
    }

    /**
     *  post提交数据
     * @param string $url 请求Url
     * @param array $datas 提交的数据
     * @return url响应返回的html
     */
    function sendPost($url, $datas)
    {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if (empty($url_info['port'])) {
            $url_info['port'] = 80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader .= "Host:" . $url_info['host'] . "\r\n";
        $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader .= "Connection:close\r\n\r\n";
        $httpheader .= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets .= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }

    /**
     * 电商Sign签名生成
     * @param data 内容
     * @param appkey Appkey
     * @return DataSign签名
     */
    function encrypt($data, $appkey)
    {
        return urlencode(base64_encode(md5($data . $appkey)));
    }

    /**
     * 获取客服电话
     */
    public function service_numberOp()
    {
        $model_setting = Model('setting');
        $list_setting = $model_setting->getListSetting();
        if ($list_setting) {
            $message = 'sucess';
            $res = array('code' => '100', 'message' => $message, 'data' => $list_setting['service_number']);
            echo json_encode($res, 320);
        } else {
            $message = 'fail';
            $res = array('code' => '300', 'message' => $message, 'data' => '');
            echo json_encode($res, 320);
        }
    }

    /**
     * 获取关于我们
     */
    public function about_usOp()
    {
        $model_setting = Model('setting');
        $list_setting = $model_setting->getListSetting();
        if ($list_setting) {
            $message = 'sucess';
            $res = array('code' => '100', 'message' => $message, 'data' => $list_setting['about_us']);
            echo json_encode($res, 320);
        } else {
            $message = 'fail';
            $res = array('code' => '300', 'message' => $message, 'data' => '');
            echo json_encode($res, 320);
        }
    }
      /* 我的——所有团购订单列表
    */
    public function tgorder_listOp()
    {
        $member_id = intval($_POST['user_id']);
        $start_time=$_POST['start_time'];
        $end_time=$_POST['end_time'];
        $where=array();
        $where['share_id']=$member_id;
        $start_unixtime = $start_time ? strtotime($start_time) : null;
        $end_unixtime = $end_time ? strtotime( $end_time): null;
        if ($start_unixtime && $end_unixtime) {
            $where['add_time'] = array('between',array($start_unixtime,$end_unixtime));
        }

        if ($member_id <= 0) {
            echo $this->returnMsg(200, '用户ID非空!', array('member_id' => $member_id));
            exit;
        }
        $model_member = model('member');
        $member_info = $model_member->table('member')->where(array('member_id' => $member_id))->find();
        if (!$member_info) {
            echo $this->returnMsg(300, '本系统无此用户!', array('member_id' => $member_id));
            exit;
        }
        $model_order = model('order');
         $order_list = $model_order->apiGetOrderList($where, '*', 'order_id desc', '', array('order_common', 'order_goods'));
        foreach ($order_list as $key => $value) {
            $order_list[$key]['add_time'] = date('Y-m-d H:i:s', $value['add_time']);
            $order_list[$key]['payment_time'] = date('Y-m-d H:i:s', $value['payment_time']);
            $order_list[$key]['finnshed_time'] = date('Y-m-d H:i:s', $value['finnshed_time']);
            $order_list[$key]['state_desc'] = $model_order->orderState1($value);
            $order_list[$key]['reciver_name'] = $value['extend_order_common']['reciver_name'];
            unset($order_list[$key]['extend_order_common']);
            if ($value['is_zorder'] == 0 && in_array($value['order_state'], array(20, 30, 40))) {
                unset($order_list[$key]);
            }
        }

        if (empty($order_list)) {
            echo $this->returnMsg(400, '无订单记录!', '');
            exit;
        }
        $order_list = array_values($order_list);
        echo $this->returnMsg(100, '我的-所有订单列表查询成功!', $order_list);
    }

    /* 微信昵称，头像上传
    */
    public function nicnameOp(){
        $member_id = intval($_POST['member_id']);
       
        $data=array();
        $data['member_name'] = $_POST['member_name'];
        $data['member_avatar'] = $_POST['member_avatar'];
        $result=Model('member')->editMember(array('member_id'=>$member_id),$data);
        if ($result) {
            echo $this->returnMsg(200, '微信昵称上传成功', null);
        }else{
            echo $this->returnMsg(400, '微信昵称上传失败', null);
        }
        
    }
    /*
    用户头像上传
    */
    public function nicpic_uploadOp(){
        $member_id = intval($_POST['member_id']);
        $upload = new UploadFile();
        $result =$upload->set('default_dir',ATTACH_AVATAR);
        $result = $upload->upfile('avatar');
        if ($result){
            $avatar = $upload->file_name;
        }else {
            echo $this->returnMsg(400, '图片上传失败', null);exit();
        }
        if ($result){
            $data = array();
            $data['member_id'] = $member_id;
            $data['file_name'] = $avatar;

            $data['file_path'] =UPLOAD_SITE_URL.'/'.ATTACH_AVATAR.'/'.$avatar;
            if (!empty($type)) {
                $data['type'] = $type;
            }
            /**
             * 整理为json格式
             */
            echo $this->returnMsg(200, '图片上传成功', $data);
        }
    }
}