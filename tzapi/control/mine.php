<?php
defined('In718Shop') or exit('Access Invalid!');
    
/*我的*/
class MineControl extends BaseControl{
    
    /**
     今日成交数(订单数和顾客数)
     */
    public function yu_countOp(){
    	//团长ID
        //$tz_id =$_GET['tz_id'];
        //自提点
        $zitiAddress = $_GET['address_id'];
        //今日开始时间戳
        $beginToday = mktime(0,0,0,date('m',time()),date('d',time()),date('Y',time()));
        //今日结束时间戳 
        $endToday = mktime(23,59,59,date('m',time()),date('d',time()),date('Y',time()));
    
        //今日订单成交数（单数）
        $pay_ordersum_sql = "SELECT COUNT(718shop_order.order_id) total_order FROM 718shop_order_common LEFT JOIN 718shop_order ON 718shop_order.order_id=718shop_order_common.order_id WHERE 718shop_order_common.reciver_ziti_id=".$zitiAddress." AND 718shop_order.payment_time >=".$beginToday." AND 718shop_order.payment_time <= ".$endToday." AND 718shop_order.is_zorder>0 AND 718shop_order.by_post=1 AND 718shop_order.order_state >0";
        $pay_ordersum_array = Model()->query($pay_ordersum_sql);
        if(!empty($pay_ordersum_array)){
            $data['total_order_num'] = $pay_ordersum_array[0]['total_order'];
        }else{
            $data['total_order_num'] = 0;
        }
       
       //今日成交数（买家个数）
       $pay_buyersum_sql = "SELECT COUNT(DISTINCT 718shop_order.buyer_id) total_buyer FROM 718shop_order_common LEFT JOIN 718shop_order ON 718shop_order.order_id=718shop_order_common.order_id WHERE 718shop_order_common.reciver_ziti_id=".$zitiAddress." AND 718shop_order.payment_time >=".$beginToday." AND 718shop_order.payment_time <= ".$endToday." AND 718shop_order.is_zorder>0 AND 718shop_order.by_post=1 AND 718shop_order.order_state >0";
        
        $pay_buyersum_array = Model()->query($pay_buyersum_sql);
        if(!empty($pay_buyersum_array)){
            $data['total_buyer_num'] = $pay_buyersum_array[0]['total_buyer'];
        }else{
            $data['total_buyer_num'] = 0;
        }

        //今日预计收入
        $pay_income_sql = "SELECT SUM(goods_pay_price*commis_rate*0.01) AS yu_income FROM 718shop_order_goods WHERE order_id IN (SELECT DISTINCT 718shop_order.order_id FROM 718shop_order_common LEFT JOIN 718shop_order ON 718shop_order.order_id=718shop_order_common.order_id WHERE 718shop_order_common.reciver_ziti_id=".$zitiAddress." AND 718shop_order.payment_time >= ".$beginToday." AND 718shop_order.payment_time <= ".$endToday." AND 718shop_order.is_zorder>0 AND 718shop_order.by_post=1 AND 718shop_order.order_state >0) AND commis_rate > 0 ORDER BY order_id ASC";
        $pay_income_array = Model()->query($pay_income_sql);
        if(!empty($pay_income_array)){
            $data['total_income_num'] = number_format($pay_income_array[0]['yu_income'],2);
        }else{
            $data['total_income_num'] = 0.00;
        }

        $result['code'] = 100;
        $result['message'] = '今日预计成交数(订单数/顾客数/收入)';
        $result['data'] = $data;
        echo json_encode($result,320);die;
    }
}
