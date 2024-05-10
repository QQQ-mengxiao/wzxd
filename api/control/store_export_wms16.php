<?php
/**
 * 商家中心订单导出
 *
 *
 *
 **/
defined('In718Shop') or exit('Access Invalid!');

class store_export_wms16Control extends BaseControl
{
    public function __construct()
    {
      
    }
    // public function indexOp()
    // {
    //   $this->send_goods();
    //   $this->send_orders();
    //   die;
    // }
    /**
     * 发送商品数据 需要两次配送20点场数据
     * @return [type] [description]
     */
    public function send_goodsOp()
    {
      //增加供货商id和type，type，10：全部，20：预售，30：非预售
      $type = isset($_GET['type'])?$_GET['type']:10;
      $supplierCode = isset($_GET['type'])?$_GET['type']:000;//000处理是否有问题，supplierCode为数组
      //根据供货商获取发货人id
      $time = time();
      $time_end = date("Y-m-d",time());
      $time_end .= " 20:00:02";      
      $online_sn = strtotime($time_end);
      $post_data = array();
      $post_data['onlineSn'] = $online_sn;//导表结束时间戳+供货商编号（全部默认0000）
      $post_data['tenantId'] = 45;
      $post_data['salerCode'] = 'WZXD';
      $post_data['salerName'] = '物资小店';
      $post_data['overTime'] = $time;
      //遍历循环数据发货人编号及对应发货信息
      //1.获取需要分拣的仓库id
      $storage_list = Model('storage')->getStorageList(array('is_picked' => 1,'times' => 2),'storage_id');
      $storage_ids = '';
      $count = count($storage_list);
      foreach ($storage_list as $k => $v) {
        if ($k < $count-1) {
          $storage_ids .= $v['storage_id'].',';
        }else{
          $storage_ids .= $v['storage_id'];
        }
      }
      //2.获取需要分拣的发货人id
      $daddress_list = Model('daddress')->getAddressList(array('storage_id' => array('in', $storage_ids)),'address_id,seller_name,wms_id');
      // var_dump($daddress_list);die;
      $address_ids = array();
      foreach ($daddress_list as $key => $value) {
        //发货人去重
        if (in_array($value['address_id'],$daddress_ids)) {
          continue;
        }
        $address_ids[] = $value['address_id'];
        $post_data['overList'][$key]['goodsList'] = $this->export_order($value['address_id'],$type);
        if (empty($post_data['overList'][$key]['goodsList'])) {
          unset($post_data['overList'][$key]);
          continue;
        }
        $post_data['overList'][$key]['supplierCode'] = $value['wms_id'];//wms系统对应id
        $post_data['overList'][$key]['supplierId'] = $value['address_id'];
        $post_data['overList'][$key]['supplierName'] = $value['seller_name'];
      }
      $post_data['overList'] = array_values($post_data['overList']);
      //print_r($post_data['overList']);die;
      $post_data_json = json_encode($post_data,320);
      var_dump($post_data_json);die;
      $this->send_url('http://wms.zo718.cn:8081/wms-admin/onlineApi/onlineOverDetail',$post_data_json);

      //暂时不回执处理

    }

    public function test()
    {
      $post_data_json = '';
      var_dump($post_data_json);die;
      $this->send_url('http://192.168.1.162:8081/wms-admin/onlineApi/onlineOverDetail',$post_data_json);
    }
    /**
     * 发送订单数据
     * @return [type] [description]
     */
    public function send_ordersOp()
    {
      //增加供货商id和type，type，10：全部，20：预售，30：非预售
      $type = isset($_GET['type'])?$_GET['type']:10;
      //$supplierCode = isset($_GET['type'])?$_GET['type']:000;//000处理是否有问题，supplierCode为数组
      //根据供货商获取发货人id
      $time = time();
      $time_end = date("Y-m-d",time());
      $time_end .= " 20:00:02";        
      $online_sn = strtotime($time_end);
      $post_data = array();
      $post_data['onlineSn'] = $online_sn;//导表结束时间戳+供货商编号（全部默认0000）
      $post_data['tenantId'] = 45;
      $post_data['salerCode'] = 'WZXD';
      $post_data['salerName'] = '物资小店';
      $post_data['overTime'] = $time;
      //1.获取需要分拣的仓库id
      $storage_list = Model('storage')->getStorageList(array('is_picked' => 1,'times' => 2),'storage_id');
      $storage_ids = '';
      $count = count($storage_list);
      foreach ($storage_list as $k => $v) {
        if ($k < $count-1) {
          $storage_ids .= $v['storage_id'].',';
        }else{
          $storage_ids .= $v['storage_id'];
        }
      }
      //2.获取需要分拣的发货人id
      $daddress_list = Model('daddress')->getAddressList(array('storage_id' => array('in', $storage_ids)),'address_id,seller_name,wms_id');
      // var_dump($daddress_list);die;
      $address_list = array();
      $address_ids = '';
      $count = count($daddress_list);
      foreach ($daddress_list as $key => $value) {
        if (in_array($value['address_id'],$address_list)) {
          continue;
        }elseif ($key < $count-1) {
          $address_ids .= $value['address_id'].',';
          $address_list[] = $value['address_id'];
        }else{
          $address_ids .= $value['address_id'];
        }
      }
      $post_data['orderList'] = $this->export_order2($address_ids,$type);
      // // //发送请求数据
      //print_r($post_data);die;
      //var_dump(json_encode($post_data));
      $post_data_json = json_encode($post_data,320);
      var_dump($post_data_json);die;
      $result = $this->send_url('http://wms.zo718.cn:8081/wms-admin/onlineApi/onlineOrderDetail',$post_data_json);
      var_dump($result);die;

      //die;
      //不回执处理
    }

    /**
     * 导出配货单
     *
     */
    private function export_order($address_id,$type)
    {
       $model_goods = Model('goods');
        $model_order = Model('order');
        $condition = array();
        $condition['order.order_state'] = 20;

        //发货人姓名
        if($address_id>0){
            $condition['order_goods.deliverer_id'] = $address_id;
        }
        //当天时间11:30,前一天时间,当天时间11：30时间戳
        //导出截止时间
        $time_end = date("Y-m-d",time());
        $time_end .= " 20:00:00";     
        $time_end_str = strtotime($time_end);
        $time_start_str = strtotime('-9 hours',$time_end_str);
        $condition['order.payment_time'] = array('between', array($time_start_str, $time_end_str));
        $data = $model_order->getOrderGoodsExportList($condition,20000);
        $ordergoods_arr=array();

        foreach($data as $kk=>$vv){
            
            if($vv['is_zorder']==0 ){
                unset($data[$kk]);
                continue;
            }
             foreach ($vv['extend_order_goods'] as $key => $value) { 
                $model_refund_return = Model('refund_return');
                $refund_list = $model_refund_return->getRefundReturnList(array('order_id' => $vv['order_id']));
                if(!empty( $refund_list)&&is_array( $refund_list)){
                    foreach ($refund_list as $key1 => $value1) {
                       if($value1['goods_id']==0){
                            if($value1['seller_state']<3){
                                 unset($data[$kk]);
                            }  
                       }else{
                          if($value1['goods_id']==$value['goods_id']&&$value1['seller_state']<3){
                                 unset($data[$kk]['extend_order_goods'][$key]);
                            }  
                       }
                    }
                }

                //排除预售商品
                if(strstr($value['goods_name'],'【团购')){
                  unset($data[$kk]['extend_order_goods'][$key]);
                  continue;
                }
                
                if($address_id>0){
                    if($value['order_goods_deliverer_id']!=$address_id){
                        unset($data[$kk]['extend_order_goods'][$key]);                       
                    }
                }   
             }
        }
        foreach ($data as $k2 => $v2) {
            foreach ($v2['extend_order_goods'] as $k22 => $v22) {
                $ordergoods_arr[]=$v22;
            }
        }

        foreach ($ordergoods_arr as $k => $v) {
              $data_arr[$v['goods_id']][] = $v;
          }
          $num_goodsnum=0;
          $num_costprice=0;
          foreach ($data_arr as $ke => $va) {
             $sumall=0;
              $goods_cost_price_all=0;
            foreach ($va as $ke1 => $va1) {
                $sumall=$sumall+$va1['goods_num'];
                $goods_cost_price_all=$goods_cost_price_all+$va1['goods_cost_price']*$va1['goods_num'];
            }
             $data_array[$ke]['goods_id']=$va[0]['goods_id'];
             $data_array[$ke]['goods_name']=$va[0]['goods_name'];
             $data_array[$ke]['sumall']=$sumall;
             $data_array[$ke]['goods_cost_price_all']= $goods_cost_price_all;
             $num_costprice=$num_costprice+$goods_cost_price_all;
             $num_goodsnum=$num_goodsnum+$sumall;
          }
          $count=count($data_array);
          $data_array=array_values($data_array);
          $data_excel['data_array']=$data_array;
          $data_excel['time1']=$end_unixtime_pay;
          if($_GET['daddress_id']>0){
            $data_excel['deliverer_id']=$_GET['daddress_id'];
              $data_excel['name']='';
          }else{
            $data_excel['deliverer_id']=0;
             $data_excel['name']=$name;
          }
          $data_tmp=$data_excel['data_array'];
          $order_data = [];
        foreach ($data_tmp as $k => $v) {
            $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsInfo(array('goods_id'=>$v['goods_id']));
               $order_data[] =  array(
                'goodsName' => $v['goods_name'],
                'goodsCode' => $goods_detail['goods_serial'],
                'goodsCount' => $v['sumall'],
            );
           
        }
        return $order_data;
    }

    /**
     * 导出订单
     *
     */
    private function export_order2($daddress_id,$type)
    {    
        $model_order = Model('order');
        $condition['order.order_state'] = 20;
        if (is_string($daddress_id)) {
          $condition['order_goods.deliverer_id'] = array('in',$daddress_id);
        }else{
          return;
        }
        $time_end = date("Y-m-d",time());
        $time_end .= " 20:00:00";   
        $time_end_str = strtotime($time_end);
        $time_start_str = strtotime('-9 hours',$time_end_str);
        $condition['order.payment_time'] = array('between', array($time_start_str, $time_end_str));
        $data = $model_order->getOrderGoodsExportList($condition,'20000');
        $ordergoods_arr=array();
        $orders_wms = array();
        foreach($data as $kk=>$vv){
            $address_info = unserialize($data[$kk]['reciver_info']);
            $orders_wms[$kk] = array();
            $orders_wms[$kk]['orderSn'] = $data[$kk]['order_sn'];
            $orders_wms[$kk]['orderId'] = $data[$kk]['order_id'];
            $orders_wms[$kk]['orderTime'] = $data[$kk]['add_time'];
            $orders_wms[$kk]['orderPurchaser'] = $data[$kk]['reciver_name'];
            $orders_wms[$kk]['orderPhone'] = $address_info['phone'];
            $orders_wms[$kk]['orderAddress'] = $address_info['address'];
            $ziti_address_info = Model('ziti_address')->getAddressInfo(array('address_id' => $data[$kk]['reciver_ziti_id']),'seller_name');
            $orders_wms[$kk]['zitiName'] = $ziti_address_info['seller_name'];
            $orders_wms[$kk]['detailAddress'] = $data[$kk]['mall_info'];
            $orders_wms[$kk]['totalAmount'] = $data[$kk]['order_amount'];
            $orders_wms[$kk]['orderComments'] = $data[$kk]['order_message'];
            $orders_wms[$kk]['goodsList'] = array();
            if($vv['is_zorder']==0 ){
                unset($data[$kk]);
                unset($orders_wms[$kk]);
                continue;
            }
           foreach ($vv['extend_order_goods'] as $key => $value) {
              $orders_wms[$kk]['goodsList'][$key]['goodsName']=$value['goods_name'];
              $orders_wms[$kk]['goodsList'][$key]['goodsCode']=$value['goods_serial'];
              $orders_wms[$kk]['goodsList'][$key]['supplierId']=$value['order_goods_deliverer_id'];
              //获取对应的wms_id 发货人名称
              $daddress_info = Model('daddress')->getAddressInfo(array('address_id' => $value['order_goods_deliverer_id']),'wms_id,seller_name');
              $orders_wms[$kk]['goodsList'][$key]['supplierCode']=$daddress_info['wms_id'];
              $orders_wms[$kk]['goodsList'][$key]['supplierName']=$daddress_info['seller_name'];
              $orders_wms[$kk]['goodsList'][$key]['goodsCount']=$value['goods_num'];
              $orders_wms[$kk]['goodsList'][$key]['goodsPrice']=number_format($value['goods_pay_price']/$value['goods_num'],2);
              $orders_wms[$kk]['goodsList'][$key]['goodsMoney']=$value['goods_pay_price'];
              $model_refund_return = Model('refund_return');
              $refund_list = $model_refund_return->getRefundReturnList(array('order_id' => $vv['order_id']));
              if(!empty( $refund_list)&&is_array( $refund_list)){
                  foreach ($refund_list as $key1 => $value1) {
                     if($value1['goods_id']==0){
                          if($value1['seller_state']<3){
                               unset($data[$kk]);
                               unset($orders_wms[$kk]);
                          }  
                     }else{
                        if($value1['goods_id']==$value['goods_id']&&$value1['seller_state']<3){
                               unset($data[$kk]['extend_order_goods'][$key]);
                               unset($orders_wms[$kk]['goodsList'][$key]);
                          }  
                     }
                  }
              }
              if(strstr($value['goods_name'],'【团购')){
                  unset($data[$kk]['extend_order_goods'][$key]);
                  unset($orders_wms[$kk]['goodsList'][$key]);
                  continue;
              }
              if (!in_array($value['order_goods_deliverer_id'],explode(',',$daddress_id))) {
                unset($data[$kk]['extend_order_goods'][$key]);
                unset($orders_wms[$kk]['goodsList'][$key]);
              }
              if($_GET['delivery_type_id']>0){
                  $daddress_id=$_GET['delivery_type_id'];
                   if(!in_array($value['deliverer_id'], $daddress_ids)){
                           unset($data[$kk]['extend_order_goods'][$key]);
                           unset($orders_wms[$kk]['goodsList'][$key]);                      
                  }
              }    
           }
           $orders_wms[$kk]['goodsList'] = array_values($orders_wms[$kk]['goodsList']);
           if (empty($orders_wms[$kk]['goodsList'])) {
             unset($orders_wms[$kk]);
           }
        }
        unset($data);
        $orders_wms = array_values($orders_wms);
        return $orders_wms;
    }


    //发送数据
    private function send_url($url,$data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          //CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$data,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        // echo $response;
        curl_close($curl);
        // echo $response;
        $data = date('Y-m-d H:i:s',time());
        file_put_contents('wms_result.log', $data."\r\n".$response."\r\n", FILE_APPEND | LOCK_EX);
    }
}
