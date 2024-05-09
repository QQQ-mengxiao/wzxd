<?php
defined('In718Shop') or exit('Access Invalid!');
    
/*物流收货*/
class ShouhuoControl extends BaseControl{
    
    /**
     收货单列表
     */
    public function shou_listOp(){
    	//团长ID
        //$tz_id =$_GET['tz_id'];
        //自提点
        $zitiAddress = $_POST['address_id'];
        //当前页码
        $pageNum = empty($_POST["pageNum"])?1:$_POST["pageNum"];
        $data['page_now'] = $pageNum;
        //每页展示条数
        $pageSize = 10; 
        $data['page_size'] = $pageSize;
        if(!empty($_POST['order_sn'])){
            $where = "718shop_order.order_sn LIKE '%".$_POST['order_sn']."%' AND ";
        }else{
            $where = '1 AND ';
        }
        //今日时间
        $time = date('n.j',time());
        //今日收货单列表总条数
        //$zong_page_sql = "SELECT COUNT(718shop_order.order_id) total_order FROM 718shop_order_common LEFT JOIN 718shop_order ON 718shop_order.order_id=718shop_order_common.order_id WHERE ".$where."718shop_order.order_state=20 AND 718shop_order.is_zorder> 0 AND 718shop_order_common.reciver_ziti_id=".$zitiAddress." AND 718shop_order.order_id NOT IN (SELECT DISTINCT order_id FROM 718shop_order_goods WHERE goods_name REGEXP '团购' AND goods_name NOT REGEXP '团购".$time."')";
        $zong_page_sql = "SELECT COUNT(718shop_order.order_id) total_order FROM 718shop_order_common LEFT JOIN 718shop_order ON 718shop_order.order_id=718shop_order_common.order_id WHERE ".$where." 718shop_order_common.reciver_ziti_id=".$zitiAddress." AND 718shop_order.order_state=25  AND 718shop_order.by_post = 1 AND 718shop_order.is_zorder> 0 ";
        //echo  $zong_page_sql ;die ;
        $zong_page_array = Model()->query($zong_page_sql);

        if(!empty($zong_page_array)){
            #总页码
            $data['end_page'] = ceil($zong_page_array[0]['total_order']/$pageSize);
            $data['total_order_num'] = $zong_page_array[0]['total_order'];
        }else{
            $data['end_page'] = 0;
            $data['total_order_num'] = 0;
        }

        //分页查询订单列表
        //$zong_order_sql = "SELECT 718shop_order.order_id,718shop_order.order_sn,718shop_order.buyer_id,718shop_order.buyer_name,718shop_order.payment_time,718shop_order.order_amount,718shop_order.order_state,718shop_order_common.reciver_ziti_id,718shop_order_common.reciver_info FROM 718shop_order_common LEFT JOIN 718shop_order ON 718shop_order.order_id=718shop_order_common.order_id WHERE ".$where." 718shop_order.order_state=20 AND 718shop_order.is_zorder> 0 AND 718shop_order_common.reciver_ziti_id=".$zitiAddress." AND 718shop_order.order_id NOT IN (SELECT DISTINCT order_id FROM 718shop_order_goods WHERE goods_name REGEXP '团购' AND goods_name NOT REGEXP '团购".$time."') ORDER BY order_id ASC LIMIT ". (($pageNum - 1) * $pageSize) . "," . $pageSize;
        $zong_order_sql = "SELECT 718shop_order.order_id,718shop_order.order_sn,718shop_order.buyer_id,718shop_order.buyer_name,718shop_order.payment_time,718shop_order.order_amount,718shop_order.order_state,718shop_order_common.reciver_ziti_id,718shop_order_common.reciver_info FROM 718shop_order_common LEFT JOIN 718shop_order ON 718shop_order.order_id = 718shop_order_common.order_id WHERE ".$where."  718shop_order_common.reciver_ziti_id=".$zitiAddress." AND 718shop_order.order_state=25 AND 718shop_order.is_zorder> 0 ORDER BY 718shop_order.order_id ASC LIMIT ". (($pageNum - 1) * $pageSize) . "," . $pageSize;
       
        $data['total_order'] = Model()->query($zong_order_sql);

        foreach ($data['total_order'] as $key => $order) {
            $order_goods = Model()->table('order_goods')->field('goods_id,goods_name,goods_num, goods_price,goods_image')->where(array('order_id' => $order['order_id']))->order('goods_id ASC')->select();
            foreach ($order_goods as $key1 => $goods) {
                $model_order = Model('order');
                $order_goods[$key1]['goods_image'] = $model_order->cthumb($goods['goods_image'], 240, 4);
                $order_goods[$key1]['goods_price'] = ncPriceFormat($goods['goods_price']);
            }
            $data['total_order'][$key]['order_goods'] = $order_goods;
            $data['total_order'][$key]['payment_time'] = date('Y-m-d H:i:s', $order['payment_time']);
            $data['total_order'][$key]['order_amount'] = ncPriceFormat($order['order_amount']);
            $data['total_order'][$key]['reciver_info'] = unserialize($order['reciver_info']);
        }
        $result['code'] = 100;
        $result['message'] = '今日收货单列表';
        $result['data'] = $data;
        //echo $zong_order_sql;die;
        echo json_encode($result,320);die;
    }
}
