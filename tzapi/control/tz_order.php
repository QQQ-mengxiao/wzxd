<?php
/**
 * 我的订单
 *
 *
 *
 ***/
defined('In718Shop') or exit('Access Invalid!');
class tz_orderControl extends BaseControl
{

    /* 我的信息列表   is_assistant   is_assistantis_assistantis_assistantis_assistant
    */
    public function indexOp()
    {   
        // $tz_id = intval($_GET['tz_id']);
        // $is_assistant = intval($_GET['is_assistant']);
        // $model_groupbuy_leader = Model('groupbuy_leader');
        // $where=array();
        // $where['groupbuy_leader_id']= $tz_id;
        // $tz_info = $model_groupbuy_leader->getGroupbuyLeaderInfo($where);
        // // var_dump($tz_info);die;
        // if (!$tz_info) {
        //     echo $this->returnMsg(300, '本系统无此用户!', array('tz_id' => $tz_id ));
        //     exit;
        // }
        if(intval($_GET['is_assistant'])==1){
          $ziti_id=intval($_GET['ziti_id']);
        }else{
            $tz_id = intval($_GET['tz_id']);
            $ziti_address_info = Model('ziti_address')->getAddressInfo(array('gl_id'=>$tz_id,'is_current'=>1));
            $ziti_id=$ziti_address_info['address_id'];
        }

        
       
        $model_order = Model('order');
        $order_count = array();
        // 查询我的订单待付款订单数
        // $condition=" AND order_state!=10 AND is_zorder>0 AND delete_state =0";
        // $order_list = $model_order->tzGetOrderListStatePage($ziti_id,$condition );
        
        // $order_idarr=array_column($order_list,'order_id');
        $condition=" AND order_state=30 AND is_zorder>0 AND delete_state = 0";//待提货
        $order_list_send = $model_order->tzGetOrderListStatePage($ziti_id,$condition );
        // var_dump($order_list_send);die;
        $order_count['is_send'] = count($order_list_send);
        $condition=" AND order_state=40 AND is_zorder>0 AND delete_state = 0";
        $order_list_receive = $model_order->tzGetOrderListStatePage($ziti_id,$condition );
        $order_count['is_receive'] = count($order_list_receive);
        $refund=array();
        $refund['order_id'] = array('in',$order_idarr);
        $refund['refund_state'] = array('neq', 3);
        // 查询我的退款订单订单数
        // var_dump($order_idarr);die;
        $order_count['is_refund'] = Model('refund_return')->getRefundCount($refund);
        echo $this->returnMsg(100, '我的信息列表订单数', $order_count);
    }

    /* 我的——订单列表
    */
    public function order_listOp()
    {
        $order_state = intval($_POST['order_state']);
         // $tz_id = intval($_POST['tz_id']);
         $num_page = intval($_POST['num_page']);
        // $model_groupbuy_leader = Model('groupbuy_leader');
        // $where=array();
        // $where['groupbuy_leader_id']= $tz_id;
        // $tz_info = $model_groupbuy_leader->getGroupbuyLeaderInfo($where);
        // if ($tz_id == 0) {
        //     echo $this->returnMsg(200, '用户ID非空!', array('tz_id' => $tz_id));
        //     exit;
        // }
       
        // $ziti_id=$ziti_address_info['address_id'];
        if(intval($_GET['is_assistant'])==1){
          $ziti_id=intval($_GET['ziti_id']);
        }else{
            $tz_id = intval($_GET['tz_id']);
            $ziti_address_info = Model('ziti_address')->getAddressInfo(array('gl_id'=>$tz_id,'is_current'=>1));
            $ziti_id=$ziti_address_info['address_id'];
        }
          $ziti_address_info = Model('ziti_address')->getAddressInfo(array('address_id'=>$ziti_id));
        if (!$ziti_address_info) {
            echo $this->returnMsg(300, '本系统无此用户!', array('ziti_id' => $ziti_id));
            exit;
        }
        $model_order = Model('order');
        $order_list = array();
        if ( $order_state == 30) {
            $condition=" AND order_state=".$order_state."  AND is_zorder>0";
            $order_list = $model_order->tzGetOrderListPage($ziti_id,$num_page,$condition);
            // $sql_count = "SELECT count(*) AS page_count FROM 718shop_order WHERE order_id IN (SELECT order_id FROM 718shop_order_common WHERE reciver_ziti_id = " . $ziti_id . " AND delete_state = 0 ".$condition.")";
            $sql_count = "SELECT count(*) AS page_count FROM 718shop_order WHERE order_id IN (SELECT order_id FROM 718shop_order_common WHERE reciver_ziti_id = " . $ziti_id.") AND delete_state = 0 AND by_post = 1".$condition;
                $count_result = Model()->query($sql_count);
        $page_count = $count_result[0]['page_count'];
        $max_page_num = ceil($page_count/10);//echo $sql_count;die;
        }
        if ($order_state == 40) {
            $condition=" AND order_state=".$order_state."  AND is_zorder>0";
            $order_list = $model_order->tzGetOrderListPage($ziti_id,$num_page,$condition);
            // $sql_count = "SELECT count(*) AS page_count FROM 718shop_order WHERE order_id IN (SELECT order_id FROM 718shop_order_common WHERE reciver_ziti_id = " . $ziti_id . " AND delete_state = 0 ".$condition.")";
            $sql_count = "SELECT count(*) AS page_count FROM 718shop_order WHERE order_id IN (SELECT order_id FROM 718shop_order_common WHERE reciver_ziti_id = " . $ziti_id.") AND delete_state = 0 AND by_post = 1".$condition;
        $count_result = Model()->query($sql_count);
        $page_count = $count_result[0]['page_count'];
        $max_page_num = ceil($page_count/10);//echo $sql_count;die;
        }
        // var_dump($sql_count);die;
        $model_refund_return = Model('refund_return');
        $order_list = $model_refund_return->getGoodsRefundList($order_list);
        foreach ($order_list as $key => $value) {
            $order_list[$key]['add_time'] = date('Y-m-d H:i:s', $value['add_time']);
            $order_list[$key]['payment_time'] = date('Y-m-d', $value['payment_time']);
            $order_list[$key]['finnshed_time'] = date('Y-m-d H:i:s', $value['finnshed_time']);
            $biaoshi=0;
            $order_list[$key]['extend_order_goods'] = array_values($value['extend_order_goods']);
             $yongjin=0;
            foreach ($value['extend_order_goods'] as $k => $v) {
                $yongjin+=$v['goods_pay_price']*$v['commis_rate'];
                if($v['refund']!=1){
                      $biaoshi=1;
                }            
            }
            $order_list[$key]['yongjin'] =ncPriceFormat($yongjin*0.01);
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
        $order_list = array_values($order_list);
        $orderList['order_list'] = $order_list;
        $orderList['end'] = 0;
        if($num_page > $max_page_num){
            $orderList['end'] = 1;
            $orderList['order_list'] = [];
        }
        echo $this->returnMsg(100, '我的信息-订单列表查询成功!', $orderList);
    }

    /* 我的——所有订单列表
    */
    public function allorder_listOp(){   
        // var_dump($_POST);die;
        $condition = "";
        if($_POST['order_sn']){
            $condition .= " AND order_sn like '%".$_POST['order_sn']."%'";
        }
        if ($_POST['startdate'] && $_POST['enddate']) {
            $starttime=strtotime($_POST['startdate']);
            $endtime=strtotime($_POST['enddate']);
            $condition .= " AND payment_time BETWEEN $starttime AND $endtime ";
        }
        elseif ($_POST['startdate']) {
            $starttime=strtotime($_POST['startdate']);
            $condition .= " AND payment_time>$starttime ";
        }
        elseif ($_POST['enddate']) {
             $endtime=strtotime($_POST['enddate']);
            $condition .= " AND payment_time<$endtime ";
        }
        //今天
        if($_POST['day']=='1'){
            $zero=strtotime(date('Ymd',time()));
            $condition .= " AND payment_time>$zero ";
        }
        if($_POST['day']=='2'){
            $start=strtotime('yesterday');
            $end=strtotime(date('Ymd',time()));
            $condition .= " AND payment_time BETWEEN $start AND $end ";
        }
        // var_dump($condition);die;
         if(intval($_POST['is_assistant'])==1){
          $ziti_id=intval($_POST['ziti_id']);
        }else{
            $tz_id = intval($_POST['tz_id']);
            $ziti_address_info = Model('ziti_address')->getAddressInfo(array('gl_id'=>$tz_id,'is_current'=>1));
            $ziti_id=$ziti_address_info['address_id'];
        }
        // var_dump($tz_id );die;
        // $tz_id = intval($_POST['tz_id']);
        // $model_groupbuy_leader = Model('groupbuy_leader');
        // $where=array();
        // $where['groupbuy_leader_id']= $tz_id;
        // $tz_info = $model_groupbuy_leader->getGroupbuyLeaderInfo($where);
        $num_page = intval($_POST['num_page']);
        // // var_dump($tz_id);die;
        // if ($tz_id <= 0) {
        //     echo $this->returnMsg(200, '用户ID非空!', array('tz_id' => $tz_id));
        //     exit;
        // }
        
        // if (!$tz_info) {
        //     echo $this->returnMsg(300, '本系统无此用户!', array('tz_id' => $tz_id));
        //     exit;
        // }
        //   $ziti_address_info = Model('ziti_address')->getAddressInfo(array('gl_id'=>$tz_id,'is_current'=>1));
        // $ziti_id=$ziti_address_info['address_id'];
        $model_order = model('order');
        $order_list = array();
        // $condition .= " AND (is_zorder !=0 AND order_state!=10)";
        $condition .= " AND NOT (is_zorder =0 or order_state<=10)";
        $order_list = $model_order->tzGetOrderListPage($ziti_id,$num_page,$condition);
        // var_dump($num_page);die;
        $sql_count = "SELECT count(*) AS page_count FROM 718shop_order WHERE order_id IN (SELECT order_id FROM 718shop_order_common WHERE reciver_ziti_id = ".$ziti_id.") AND delete_state = 0 AND by_post = 1".$condition;
         // $sql_count = "SELECT count(*) AS page_count FROM 718shop_order WHERE order_id IN (SELECT order_id FROM 718shop_order_common WHERE reciver_ziti_id = " . $ziti_id.") AND delete_state = 0".$condition;
        $count_result = Model()->query($sql_count);
        // var_dump($count_result);die;
        $page_count = $count_result[0]['page_count'];
        $max_page_num = ceil($page_count/10);//echo $sql_count;die;
        $model_refund_return = Model('refund_return');
        $order_list = $model_refund_return->getGoodsRefundList($order_list);
       
        foreach ($order_list as $key => $value) {
            $order_list[$key]['add_time'] = date('Y-m-d H:i:s', $value['add_time']);
            $order_list[$key]['payment_time'] = date('Y-m-d', $value['payment_time']);
            $order_list[$key]['finnshed_time'] = date('Y-m-d H:i:s', $value['finnshed_time']);
             $biaoshi=0;
              $order_list[$key]['extend_order_goods'] = array_values($value['extend_order_goods']);
              $yongjin=0;
            foreach ($value['extend_order_goods'] as $k => $v) {
                $yongjin+=$v['goods_pay_price']*$v['commis_rate'];

                if($v['refund']!=1){
                      $biaoshi=1;
                }            
            }
            $order_list[$key]['yongjin'] = ncPriceFormat($yongjin*0.01);
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

        // if (empty($order_list)) {
        //     $orderList['end'] = 1;
        //     echo $this->returnMsg(400, '无订单记录!', $orderList);
        //     exit;
        // }
        $order_list = array_values($order_list);
        $orderList['order_list'] = $order_list;
        $orderList['end'] = 0;
        // var_dump($max_page_num);die;
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
        if ($order_id <= 0) {
            echo $this->returnMsg(200, '订单ID非空!', array('member_id' => $member_id));
            exit;
        }

        $model_order = Model('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_info = $model_order->getFpOrderInfo($condition, array('order_goods', 'order_common'));

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
         $yongjin=0;
        foreach ($order_info['extend_order_goods'] as $key => $value) {
             $yongjin+=$value['goods_pay_price']*$value['commis_rate'];
            $order_info['extend_order_goods'][$key]['goods_image'] = $model_order->cthumb($value['goods_image'], 240, $value['store_id']);
            $order_info['extend_order_goods'][$key]['goods_type_cn'] = $model_order->orderGoodsType($value['goods_type']);
            // $order_info['extend_order_goods'][$key]['goods_type']=orderTypeName($order_info['order_type']);
            $model_goods = Model('goods');
            $goods_info = $model_goods->getGoodsInfoByID($value['goods_id']);
            

            if(empty(array_values(unserialize($goods_info['goods_spec']))[0])){
                    $refund_goods[$k]['goods_spec'] ='无';
                }else{
                    $order_info['extend_order_goods'][$key]['goods_spec'] = array_values(unserialize($goods_info['goods_spec']))[0];
                }
            $order_info['extend_order_goods'][$key]['goods_type'] = goodsTypeName($value['goods_type']);
            if ($value['goods_type'] == 4) {
                        $model_xianshi_goods = Model('p_xianshi_goods');
                        $model_xianshi = Model('p_xianshi');
                        $condition_xs=array();
                        $condition_xs['goods_id']=$value['goods_id'];
                        $condition_xs['end_time'] = array('gt', TIMESTAMP);
                        $xianshigoods = $model_xianshi_goods->getXianshiGoodsInfo( $condition_xs);
                        // $xianshigoods = $model_xianshi_goods->getXianshiGoodsInfo(array('goods_id' => $value['goods_id'] ));
                        $xianshi_info = $model_xianshi->getXianshiInfo(array('xianshi_id' => $xianshigoods['xianshi_id']));
                       $order_info['extend_order_goods'][$key]['xianshi_type']=$xianshi_info['xianshi_type'];
                        if($xianshi_info['xianshi_type']==1){
                        $order_info['extend_order_goods'][$key]['goods_type']='限时秒杀';
                        }else{
                            $order_info['extend_order_goods'][$key]['goods_type']='限时折扣';
                        }
                        
                }
            if ($value['refund'] != 1) {
                $biaoshi = 1;
            }
            if (!empty($refund_list[0]) && $refund_list[0]['goods_id'] == '0' && $order_info['lock_state'] > 0) {
                $order_info['extend_order_goods'][$key]['refund'] = '0';
            }
            if ($value['goods_type'] == 5) {
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
        $order_info['yongjin']=ncPriceFormat($yongjin*0.01);
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

        //卖家发货信息
        if (!empty($order_info['extend_order_common']['daddress_id'])) {
            $daddress_info = Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_common']['daddress_id']));
            $order_info['extend_order_common']['daddress_id'] = $daddress_info;

        }
        $order_info['add_time'] = date('Y-m-d H:i:s', $order_info['add_time']);
        $order_info['payment_time'] = date('Y-m-d', $order_info['payment_time']);
        if($order_info['finnshed_time']>0){
           $order_info['finnshed_time'] = date('Y-m-d', $order_info['finnshed_time']);
        }else{
            $order_info['finnshed_time'] ='';
        }
        
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
     * 退款记录列表页
     *
     */
    public function refund_listOp()
    {

        $model_refund = Model('refund_return');
        $model_order = Model('order');
        $where = array();
        if(intval($_POST['is_assistant'])==1){
          $ziti_id=intval($_POST['ziti_id']);
        }else{
            $tz_id = intval($_POST['tz_id']);
            $ziti_address_info = Model('ziti_address')->getAddressInfo(array('gl_id'=>$tz_id,'is_current'=>1));
            $ziti_id=$ziti_address_info['address_id'];
        }
        // $tz_id = intval($_POST['tz_id']);
        // $model_groupbuy_leader = Model('groupbuy_leader');
        // $where=array();
        // $where['groupbuy_leader_id']= $tz_id;
        // $tz_info = $model_groupbuy_leader->getGroupbuyLeaderInfo($where);
        // if (!$tz_info) {
        //     echo $this->returnMsg(300, '本系统无此用户!','');
        //     exit;
        // }

        //  $ziti_address_info = Model('ziti_address')->getAddressInfo(array('gl_id'=>$tz_id,'is_current'=>1));

        // $ziti_id=$ziti_address_info['address_id'];
        $condition=array();
        $condition['order_common.reciver_ziti_id']=$ziti_id;//待提货
        $condition['order.is_zorder']=array(array('eq', 1), array('eq', 2), 'or');
         $condition['order.order_state']=array('in',array(0,20,30,40));
         $condition['order.by_post']=1;
        $order_list = $model_order->getOrderList2('', $condition, 0, 'order.order_id', '', 10000000);
        $order_idarr=array_column($order_list,'order_id');
        // var_dump($order_idarr);die;
        //退款处理状态，1为查询未处理的退款订单列表，其余则为全部
        $where = array();
        $where['order_id']=array('in',$order_idarr);
        if ($state == 1) {
            $where['refund_state'] = array('lt', 3);
        }

        ///取退款记录
        $refund_list['refund_list'] = $model_refund->getRefundReturnList($where);;
        foreach ($refund_list['refund_list'] as $key => $value) {
            $refund_list['refund_list'][$key]['add_time'] = date('Y-m-d', $value['add_time']);
            $refund_list['refund_list'][$key]['seller_time'] = date('Y-m-d H:i:s', $value['seller_time']);
            $refund_list['refund_list'][$key]['admin_time'] = date('Y-m-d H:i:s', $value['admin_time']);
            $condition = array();
            $condition['order_id'] = $value['order_id'];
            $order = $model_refund->getRightOrderList($condition);
            $refund_list['refund_list'][$key]['orderadd_time'] =date('Y-m-d H:i:s', $order['add_time']); 
            $refund_list['refund_list'][$key]['reciver_name'] = $order['extend_order_common']['reciver_name'];
            $refund_list['refund_list'][$key]['phone'] = $order['extend_order_common']['reciver_info']['phone'];
            // var_dump( $order);die;
            
            $refund_goods = array();
            foreach ($order['goods_list'] as $k => $v) {
                if ($value['goods_id'] == 0) {
                    $refund_goods[$k]['goods_image'] = cthumb($v['goods_image'], 60, $v['store_id']);
                    $refund_goods[$k]['goods_price'] = $v['goods_price'];
                    $refund_goods[$k]['goods_pay_price'] = $v['goods_pay_price'];
                    $refund_goods[$k]['goods_name'] = $v['goods_name'];
                    $refund_goods[$k]['goods_num'] = $v['goods_num'];
                    $refund_goods[$k]['goods_id'] = $v['goods_id'];
                    $model_goods = Model('goods');
                    $goods_info = $model_goods->getGoodsInfoByID($v['goods_id']);
                    $refund_goods[$k]['is_group_ladder'] = $goods_info['is_group_ladder'];
                } else {
                    if ($value['goods_id'] == $v['goods_id']) {
                $refund_goods[$k]['goods_image'] = cthumb($v['goods_image'], 60, $v['store_id']);
                $refund_goods[$k]['goods_price'] = $v['goods_price'];
                $refund_goods[$k]['goods_pay_price'] = $v['goods_pay_price'];
                $refund_goods[$k]['goods_name'] = $v['goods_name'];
                $refund_goods[$k]['goods_num'] = $v['goods_num'];
                $refund_goods[$k]['goods_id'] = $v['goods_id'];
                $model_goods = Model('goods');
                $goods_info = $model_goods->getGoodsInfoByID($v['goods_id']);
                $refund_goods[$k]['is_group_ladder'] = $goods_info['is_group_ladder'];
                    }
                }


            }
            $refund_list['refund_list'][$key]['goods_list'] = $refund_goods;
            if($value['refund_state']==3){
               if($value['seller_state']==2){
                if($refund_list['refund_list'][$key]['goods_id']==0){
                  $refund_list['refund_list'][$key]['tk_desc']='已全额退款';
                }else{
                   foreach ($refund_list['refund_list'][$key]['goods_list'] as $k2 => $v2) {
                    if( $v2['goods_id']==$value['goods_id']){
                     if($v2['goods_pay_price']==$value['refund_amount']){
                        $refund_list['refund_list'][$key]['tk_desc']='已全额退款';
                     }else{
                        $refund_list['refund_list'][$key]['tk_desc']='已部分退款';
                     }
                    }
                   }
                }
                  
               }else{
                $refund_list['refund_list'][$key]['tk_desc']='拒绝退款';
               }
            }else{
               $refund_list['refund_list'][$key]['tk_desc']='退款中';
            }
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
 /**
     * 我的——买家订单状态操作(取消订单、收货、订单回收站)
     *
     */
    public function shouhuoOp()
    {
        $order_id = intval($_POST['order_id']);
        $model_order = Model('order');
        $model_cw = Model('cw');
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_info = $model_order->getFpOrderInfo($condition, array('order_common'));
        
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
                $model_voucher = Model('voucher');
                $res = $model_voucher->mangzeng_voucher($order_info['buyer_id'], $order_info['store_id'], $amount);
                $model_voucher->liebian_voucher($order_info['buyer_id'], $order_info['store_id']);
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
     * 收货
     */
    private function order_receive($order_info)
    {
        $model_order = Model('order');
        $logic_order = Logic('order');
        $if_allow = $model_order->getOrderOperateState('receive', $order_info);
        $ziti_address_info = Model('ziti_address')->getAddressInfo(array('address_id'=>$order_info['extend_order_common']['reciver_ziti_id']));
        if (!$if_allow) {
            echo $this->returnMsg(300, '无权操作!', '');
            exit;
        }

        $result = $logic_order->changeOrderStateApiReceive($order_info, '团长', '团长id'.$ziti_address_info['gl_id']);
        return $result;
    }

    
}