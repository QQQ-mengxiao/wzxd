<?php
/**
 *一卡通数据库操作
 */
defined('In718Shop') or exit('Access Invalid!');

class chaiorderModel extends Model {
   /**
     * 合并支付——拆单
     * @return array
    */
    public function chaidan($order_id){
       //总单号
        $order_id = $order_id;
        // $model=Model();
        // $model->beginTransaction();
        $model_order = Model('order');
        $z_order_info = $model_order->table('order')->where(array('order_id'=>$order_id))->find();
        //print_r($z_order_info);die;
        if($z_order_info['is_zorder'] == 2){
            //无须拆单，更新z_order_id值 is_zorder =2（非拆单）既是总单又是分单 
			$cw_info = $model_order->getOrderGoodsInfo(array('order_id'=>$order_id,'is_cw'=>1));
            //$result = $model_order->table('order')->where(array('order_id'=>$order_id))->update(array('z_order_id'=>$order_id,'is_zorder'=>2));
			if($cw_info){
				$result = $model_order->table('order')->where(array('order_id'=>$order_id))->update(array('z_order_id'=>$order_id,'is_zorder'=>2));
			}else{
				$result = $model_order->table('order')->where(array('order_id'=>$order_id))->update(array('z_order_id'=>$order_id,'is_zorder'=>2,'is_cw_completed'=>1));
			}
            if (!$result){
                 throw new Exception('订单(非拆单)更新失败[更新总单号]');
            }
        }else{  
            //20221017mx判断是否拆过单
            $order_sn = Model()->table('order')->getfby_order_id($order_id,'order_sn');
            $order_count = Model()->table('order')->where(array('z_order_sn'=>$order_sn))->count();
            if($order_count>1){
                throw new Exception('订单(拆单)失败[该订单已拆单]');
            }
            $result = $model_order->table('order')->where(array('order_id'=>$order_id))->update(array('z_order_id'=>$order_id));
            if (!$result){
                 throw new Exception('订单(拆单)更新失败[更新总单号]');
            }
            $z_order_common = $model_order->table('order_common')->where(array('order_id'=>$order_id))->find();
            //print_r($z_order_common);die;
            $field  = 'order_goods.*,goods.deliverer_id,goods.is_group_ladder';
            $z_order_goods = Model()->table('order_goods,goods')->field($field)->join('left join')->on('goods.goods_id=order_goods.goods_id')->where(array('order_id'=>$order_id))->select();
            //print_r($z_order_goods);die;

            //根据仓库 拆分商品
            $fen_goods = array();
            foreach ($z_order_goods as $k => $goods_list) {
                //根据发货人ID查找仓库id
                $storage_info =Model()->table('daddress')->where(array('address_id'=>$goods_list['deliverer_id']))->find();
                //如果找不到仓库，默认仓库为中心仓
                if(!empty($storage_info['storage_id'])){
                    $storage_id = $storage_info['storage_id'];
                }else{
                    $storage_id = 4;
                }
                $fen_goods[$storage_id][] =  $goods_list;
            }

            //print_r($fen_goods);die;
            //生成分单
            $i = 0;
            foreach ($fen_goods as $storage_id => $fen_goods_list) {
                $order = array();
                $order_common = array();
                $order_goods = array();
    
                //分单order表信息
                $k = time()+$i;
                $i = $i +1;
                $order_sn =(date('y',time()) % 9+3).sprintf('%05d', $order_id).sprintf('%010d', $k);
                $order['order_sn'] = $order_sn;
                //邮寄地址
                $order['address_you_id'] = $z_order_info['address_you_id'];
                $order['by_post'] = $z_order_info['by_post'];
                $order['pay_sn'] = $z_order_info['pay_sn'];
                $order['store_id'] = $z_order_info['store_id'];
                $order['store_name'] = $z_order_info['store_name'];
                $order['buyer_id'] = $z_order_info['buyer_id'];
                $order['buyer_name'] = $z_order_info['buyer_name'];
                $order['buyer_email'] = $z_order_info['buyer_email'];
                $order['add_time'] = time();
                $order['payment_code'] =  $z_order_info['payment_code'];
                $order['payment_time'] =  $z_order_info['payment_time'];
                //增加分享人和分享公司
                $order['share_id'] = $z_order_info['share_id'];
                $order['company_id'] = $z_order_info['company_id'];
                $order['order_state'] = $z_order_info['order_state'];
                $order['rcb_amount'] = $z_order_info['rcb_amount'];
                $order['pd_amount'] = $z_order_info['pd_amount'];
                $order['storage_id'] = $storage_id;
                $order['delay_time'] = $z_order_info['delay_time'];
                $order['order_from'] = $z_order_info['order_from'];
                $order['is_mode'] = $z_order_info['is_mode'];
                $order['is_zorder'] = 1;
                $order['z_order_id'] = $order_id;
                $order['z_order_sn'] = $z_order_info['z_order_sn'];
                $order['chai_time'] = time();
                $order['goods_amount'] = array_sum(array_column($fen_goods_list, 'goods_pay_price'));
                    //只有即买即送有运费
                if($fen_goods_list[0]['is_group_ladder'] == 5){
                    $order['shipping_fee'] = $z_order_info['shipping_fee'];
                }else{
                    $order['shipping_fee'] = 0;
                }
                $order['order_amount'] = floatval($order['goods_amount']) + floatval( $order['shipping_fee']);
                if(intval($z_order_info['card_amount']) > 0  && $z_order_info['payment_code'] == 'zihpay'){
                    $order['card_amount'] =  $order['order_amount'];
                }else{
                    $order['card_amount'] =  0;
                }
                // print_r($order);
                // echo '<br/><br/>';
                //插入订单表
                $forder_id = Model()->table('order')->insert($order);
               
                if (!$forder_id) {
                    throw new Exception('拆单订单保存失败[未生成拆单订单数据]');
                }
                //print_r($fen_goods_list);die;

                //order_common表数据
                $order_common['order_id'] = $forder_id;
                $order_common['store_id'] = $z_order_common['store_id'];
                $order_common['order_message'] = $z_order_common['order_message'];
                $order_common['voucher_code'] = $z_order_common['voucher_code'];
                $order_common['voucher_id'] = $z_order_common['voucher_id'];
                $order_common['voucher_price'] = $z_order_common['voucher_price'];
                $order_common['reciver_info']= $z_order_common['reciver_info'];
                $order_common['reciver_name'] = $z_order_common['reciver_name'];
                $order_common['reciver_province_id'] = $z_order_common['reciver_province_id'];
                $order_common['reciver_city_id'] = $z_order_common['reciver_city_id'];
                $order_common['invoice_info'] = $z_order_common['invoice_info'];
                $order_common['promotion_info'] = $z_order_common['promotion_info'];
                $order_common['dlyo_pickup_code'] = $z_order_common['dlyo_pickup_code'];
                $order_common['is_ziti'] = $z_order_common['is_ziti'];
                //自提点id
                $order_common['reciver_ziti_id'] = $z_order_common['reciver_ziti_id'];
                //即买即送，详细地址
                $order_common['mall_info'] = $z_order_common['mall_info'];
                $order_common['ladder_discount'] = $z_order_common['ladder_discount'];
                $order_common['ziti_ladder_time'] = $z_order_common['ziti_ladder_time'];//阶梯折扣商品ziti时间
                // //发票信息
                // $order_common['invoice_info'] = $this->_logic_buy_1->createInvoiceData($input_invoice_info);
    
                // //保存促销信息
                // if(is_array($store_mansong_rule_list[$store_id])) {
                //     $order_common['promotion_info'] = addslashes($store_mansong_rule_list[$store_id][1]['desc']);
                // }
                //插入订单扩展表
                $order_id1 = Model()->table('order_common')->insert($order_common);
                if (!$order_id1) {
                    throw new Exception('拆单订单保存失败[未生成订单扩展数据]');
                }

                //订单商品表
                foreach ($fen_goods_list as $k => $f_goods_list) {
                    $order_goods[$k]['order_id'] = $forder_id;
                    $order_goods[$k]['goods_id'] = $f_goods_list['goods_id'];
                    $order_goods[$k]['goods_name'] = $f_goods_list['goods_name'];
                    $order_goods[$k]['goods_price'] = $f_goods_list['goods_price'];
                    $order_goods[$k]['goods_num'] = $f_goods_list['goods_num'];
                    $order_goods[$k]['goods_serial'] = $f_goods_list['goods_serial'];//Model()->table('goods')->getfby_goods($f_goods_list['goods_id'],'goods_serial');
                    $order_goods[$k]['goods_barcode'] = $f_goods_list['goods_barcode'];
                    $order_goods[$k]['deliverer_id'] = $f_goods_list['deliverer_id'];//Model()->table('goods')->getfby_goods($f_goods_list['goods_id'],'deliverer_id');
                    $order_goods[$k]['is_cw'] = Model()->table('goods')->getfby_goods_id($f_goods_list['goods_id'],'is_cw');
                    $order_goods[$k]['goods_image'] = $f_goods_list['goods_image'];
                    $order_goods[$k]['goods_pay_price'] = $f_goods_list['goods_pay_price'];
                    $order_goods[$k]['store_id'] = $f_goods_list['store_id'];
                    $order_goods[$k]['buyer_id'] = $f_goods_list['buyer_id'];
                    $order_goods[$k]['goods_type'] = $f_goods_list['goods_type'];
                    $order_goods[$k]['promotions_id'] = $f_goods_list['promotions_id'];
                    $order_goods[$k]['commis_rate'] = $f_goods_list['commis_rate'];
                    $order_goods[$k]['gc_id'] = $f_goods_list['gc_id'];
                    $order_goods[$k]['kuajing_info'] = $f_goods_list['kuajing_info'];
                    $order_goods[$k]['points_send'] = $f_goods_list['points_send'];
                    $order_goods[$k]['goods_commission'] = $f_goods_list['goods_commission'];
                    $order_goods[$k]['goods_cost_price'] = $f_goods_list['goods_cost_price'];
                    $order_goods[$k]['voucher_price'] = $f_goods_list['voucher_price'];
                    $order_goods[$k]['promotion_info'] = $f_goods_list['promotion_info'];
                }
                //print_r($order_goods);die;
                //插入订单商品表
                $insert = Model()->table('order_goods')->insertAll($order_goods);
                if (!$insert) {
                    throw new Exception('拆单订单保存失败[未生成商品数据]');
                }
            }
            //echo $this->returnMsg(10001, '此订单拆单成功!', array('order_id'=>$order_id));
                //exit;
        }
    }
}
