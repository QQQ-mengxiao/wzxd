<?php
/**
 * 云仓
 */
defined('In718Shop') or exit('Access Invalid!');

class cwModel extends Model
{

    public function __construct()
    {
        parent::__construct('cw');
    }

    /**
     * 同步库存接口
     */
    public function cwPlatGoodsSyn($tenantId, $goodsList)
    {
        $data = array();
        $data['tenantId'] = $tenantId;
        $data['goodsList'] = $goodsList;
        $url = 'http://10.10.11.61:8083/cloud-admin/cwApi/cwPlatGoodsSyn';
        $res = $this->Post_curls($url, json_encode($data,320));
        $res = json_decode($res, true);
		//$date = date("y-m");
            //$dateday = date("y-m-d");
            //$path = '/data/default/wzxd/logsmx/' . $date . '/';
            //if (!is_dir($path)) {
            //mkdir($path, 0777, true);
            //}
            //$filename = $path . $dateday . ".txt";
            //if (file_exists($filename)) {
            //$content = file_get_contents($filename);
            //$content = $content . "\r\n------------------------\r\n" .json_encode($res,320);
            //file_put_contents($filename, $content);
            //} else {
            //file_put_contents($filename, json_encode($res,320));
           //}
//        echo '<pre>';var_dump($res);die;
        return $res;
    }
	
    /**
     * 已支付订单同步接口
     */
    public function cwOrderSubmit($orderList)
    {
        $url = "http://10.10.11.61:8083/cloud-admin/cwApi/cwOrderSubmit";
		$res = $this->Post_curls($url, json_encode($orderList,320));

//        $date = date("y-m");
//        $dateday = date("y-m-d");
//        $path = '/data/default/wzxd/logsmx/' . $date . '/';
//        if (!is_dir($path)) {
//            mkdir($path, 0777, true);
//        }
//        $filename = $path . $dateday . ".txt";
//        if (file_exists($filename)) {
//            $content = file_get_contents($filename);
//            $content = $content . "\r\n------------------------\r\n" .json_encode($orderList,320);
//            file_put_contents($filename, $content);
//        } else {
//            file_put_contents($filename, json_encode($orderList,320));
//        }
//
//        $date = date("y-m");
//        $dateday = date("y-m-d");
//        $path = '/data/default/wzxd/logsmx/' . $date . '/';
//        if (!is_dir($path)) {
//        mkdir($path, 0777, true);
//        }
//        $filename = $path . $dateday . ".txt";
//        if (file_exists($filename)) {
//        $content = file_get_contents($filename);
//        $content = $content . "\r\n------------------------\r\n" .json_encode($res,320);
//        file_put_contents($filename, $content);
//        } else {
//        file_put_contents($filename, json_encode($res,320));
//        }

		return json_decode($res, true);
    }

    /**
     * 完成订单接口（确认收货的订单）
     */
    public function cwOrderComplete($data)
    {
        $url = 'http://10.10.11.61:8083/cloud-admin/cwApi/cwOrderComplete';
        $res = $this->Post_curls($url, json_encode($data,320));
        //var_dump( $res);die;

		//$date = date("y-m");
            //$dateday = date("y-m-d");
            //$path = '../logsmx/' . $date . '/';
            //if (!is_dir($path)) {
            //mkdir($path, 0777, true);
            //}
            //$filename = $path . $dateday . ".txt";
            //if (file_exists($filename)) {
            //$content = file_get_contents($filename);
            //$content = $content . "\r\n------------------------\r\n" .$res;
            //file_put_contents($filename, $content);
            //} else {
            //file_put_contents($filename, $res);
            //}
        //$res = json_decode($res, true);
        //print_r($res);
        //{"msg":"请勿重复提交订单","code":1,"data":null}
        //成功code=0
    }

    /**
     * 退款接口
     */
    public function cwOrderCancle($data)
    {
        $url = 'http://10.10.11.61:8083/cloud-admin/cwApi/cwOrderCancle';
        $res = $this->Post_curls($url, json_encode($data,320));
        //print_r($res);
        //{"msg":"请勿重复提交","code":1,"data":null}
    }
	
    /**
     * 销售端已收货订单接口
     */
    public function cwOrderOver($data)
    {
        // $url = 'http://yuncangm.zhonghaokeji.cn:8083/cloud-admin/cwApi/cwOrderOver';
        $url = 'http://10.10.11.61:8083/cloud-admin/cwApi/cwOrderOver';
        $res = $this->Post_curls($url, json_encode($data));
        $res = json_decode($res, true);
        return $res;
    }
	/**
 * 退货
 */
    public function cwOrderRefund($data){
        $url = 'http://10.10.11.61:8083/cloud-admin/cwApi/cwOrderRefund';
        $res = $this->Post_curls($url, json_encode($data,320));
        $res = json_decode($res, true);
        return $res;
//        print_r($res);die;
        //Array([msg] => 退货成功[code] => 0[data] =>)
        //Array([msg] => 订单已经全部退货![code] => 1[data] =>)
    }

    /**
     * 退款
     */
    public function cwOrderBack($data){
        $url = 'http://10.10.11.61:8083/cloud-admin/cwApi/cwOrderBack';
        $res = $this->Post_curls($url, json_encode($data,320));
        $res = json_decode($res, true);

        return $res;
        //Array([msg] => 退货成功[code] => 0[data] =>)
        //Array([msg] => 订单已经全部退货![code] => 1[data] =>)
    }

    /**
     * 完成
     */
    public function cwOrderCompleteNew($data){
        $url = 'http://10.10.11.61:8083/cloud-admin/cwApi/cwOrderCompleteNew';
        $res = $this->Post_curls($url, json_encode($data,320));//echo json_encode($data,320);echo '<br>';echo $res;

        $res = json_decode($res, true);
        return $res;
        //Array([msg] => 退货成功[code] => 0[data] =>)
        //Array([msg] => 订单已经全部退货![code] => 1[data] =>)
    }
	
	
	/**
     * 异常收货
     */
    public function cwAbnormalOrderOver($data){
        $url = 'http://10.10.11.61:8083/cloud-admin/cwApi/cwAbnormalOrderOver';
        $res = $this->Post_curls($url, json_encode($data,320));//var_dump($data);die;

        $res = json_decode($res, true);
        return $res;
        //Array([msg] => 退货成功[code] => 0[data] =>)
        //Array([msg] => 订单已经全部退货![code] => 1[data] =>)
    }

    /***
     * 发货同步
     * {"msg": "同步成功","code": 0,"data": null}
     */
	public function synchoronousShipment($data){
        $companyName = $this->url_encode($data['companyName']);
        $deliverExplain = $this->url_encode($data['deliverExplain']);

        $url = 'http://10.10.11.61:8081/api/shopExpress/synchoronousShipment?companyCode='.$data['companyCode'].'&companyName='.$companyName.'&deliverExplain='.$deliverExplain.'&orderSn='.$data['orderSn'].'&shippingCode='.$data['shippingCode'];

        return $this->Get_curls($url);
    }


    /**
     * POST请求http接口返回内容
     * @param string $url [请求的URL地址]
     * @param string $post [请求的参数]
     * @return  string
     */
    public function Post_curls($url, $post)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: text/plain",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
		curl_close($curl);
        return $response; // 返回数据，json格式
    }

    public function Get_curls($url){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [],
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
        }

    private function url_encode($str){
        $result = preg_match("/[\x7f-\xff]/", $str);
        if($result){
            return urlencode($str);
        }
        return $str;
    }
	
	public function cw_logAdd($insert){
		return $this->table('cw_log')->insert($insert);
	}

	public function cw_logGet($order_id){
        return $this->table('cw_log')->where(array('order_id'=>$order_id))->find();
    }

	public function cw_logUpdate($order_id,$update){
        $this->table('cw_log')->where(array('order_id'=>$order_id))->update($update);
    }
}
