<?php
/**
 * 商家中心订单导出
 *
 *
 *
 **/
defined('In718Shop') or exit('Access Invalid!');

class send_wmsControl extends BaseControl
{
    public function __construct()
    {      
    }
    public function indexOp()
    {
      //合并数据
      $goods_json = $this->merge_goods();
      $order_json = $this->merge_orders();
      $data = date('Y-m-d H:i:s',time());
      file_put_contents('wms_result.log', $data."start"."\r\n", FILE_APPEND | LOCK_EX);
      // //发送数据
      $result = $this->send_url('http://wms.zo718.cn:8081/wms-admin/onlineApi/onlineOrderDetail',$order_json);
      $this->send_url('http://wms.zo718.cn:8081/wms-admin/onlineApi/onlineOverDetail',$goods_json);
      die;
    }
    public function sendOrdersOp()
    {
      //合并数据
      $order_json = $this->merge_orders();
      $this->send_url('http://wms.zo718.cn:8081/wms-admin/onlineApi/onlineOrderDetail',$order_json);
    }
    public function sendGoodsOp()
    {
      //合并数据
      $goods_json = $this->merge_goods();
      $this->send_url('http://wms.zo718.cn:8081/wms-admin/onlineApi/onlineOverDetail',$goods_json);
    }
    public function getOrdersOp()
    {
      //合并数据
      //$goods_json = $this->merge_goods();
      $order_json = $this->merge_orders();
      var_dump($order_json);die;
    }
    public function getGoodsOp()
    {
      //合并数据
      $goods_json = $this->merge_goods();
      //$order_json = $this->merge_orders();
      // //发送数据
      var_dump($goods_json);die;
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

    //合并商品数据
    private function merge_goods()
    {
      $time_end = date("Y-m-d",time());
      $time_end .= " 11:00:00";    
      $online_sn = strtotime($time_end);
      $online_sn_1 = $online_sn+1;
      $str1 = file_get_contents("goods/".$online_sn.".goods");
      $str2 = file_get_contents("goods/".$online_sn_1.".goods");
      $arr1 = json_decode($str1,true);
      if (!$arr1) {
        file_put_contents('wms_result.log', "goods-00-error"."\r\n", FILE_APPEND | LOCK_EX);
        die;
      }
      $arr2 = json_decode($str2,true);
      if (!$arr2) {
        file_put_contents('wms_result.log', "goods-01-error"."\r\n", FILE_APPEND | LOCK_EX);
        die;
      }
      $arr1['overList'] = array_merge($arr1['overList'],$arr2['overList']);
      unset($arr2);
      return json_encode($arr1,320);
    }

    //合并订单数据
    private function merge_orders()
    {
      $time_end = date("Y-m-d",time());
      $time_end .= " 11:00:00";    
      $online_sn = strtotime($time_end);
      $online_sn_1 = $online_sn+1;
      $str1 = file_get_contents("orders/".$online_sn.".order");
      $str2 = file_get_contents("orders/".$online_sn_1.".order");
      $arr1 = json_decode($str1,true);
      if (!$arr1) {
        file_put_contents('wms_result.log', "orders-00-error"."\r\n", FILE_APPEND | LOCK_EX);
        die;
      }
      $arr2 = json_decode($str2,true);
      if (!$arr2) {
        file_put_contents('wms_result.log', "orders-01-error"."\r\n", FILE_APPEND | LOCK_EX);
        die;
      }
      $arr1['orderList'] = array_merge($arr1['orderList'],$arr2['orderList']);
      unset($arr2);
      return json_encode($arr1,320);
    }

    public function test()
    {
      $post_data_json = '';
      var_dump($post_data_json);die;
      $this->send_url('http://192.168.1.162:8081/wms-admin/onlineApi/onlineOverDetail',$post_data_json);
    }
}
