<?php
defined('In718Shop') or exit('Access Invalid!');
    include 'HttpClient.class.php';
    define('USER', 'zhiyk@zih718.com');  //*必填*：飞鹅云后台注册账号
    define('UKEY', 'm9sg3ybD7UF6CwAm');  //*必填*: 飞鹅云后台注册账号后生成的UKEY 【备注：这不是填打印机的KEY】
    

    // //以下参数不需要修改
    define('IP','api.feieyun.cn');      //接口IP或域名
    define('PORT',80);            //接口IP端口
    define('PATH','/Api/Open/');    //接口路径
class OrderControl extends BaseControl{
    
    
    /**
     购买第一步
     */
    public function buy_step1Op(){
        $ifcart =$_GET['ifcart'];
        $cart_id = explode(',',$_GET['cart_id']);
        $member_id = $_GET['member_id'];
        $extra = array();
        if(empty($cart_id)){
            $message='wrong_argument';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>'');
             echo json_encode($res,320);die;
        }
        $buy_logic = Logic('buy');
        $result = $buy_logic->buyStep1($cart_id, $ifcart,  $member_id,$extra);
        // var_dump($result);die;
        // var_dump($result['data']['need_calc_sid_list']);die;
        $store_cart_list=$result['data']['store_cart_list'];
        // $buy1_logic = model('buy_1','logic');
        // list($store_goodscommon_total)=$buy1_logic->calcCartList1($store_cart_list);
        // $store_voucher_list=$buy1_logic->getStoreAvailableVoucherList2($store_goodscommon_total, $member_id);
        $data=array();
        //收货地址
        $address_model=model('address');
        $address_list = $address_model->getDefaultAddressInfo(array('member_id'=>$member_id));
            $return = preg_replace('#\s+#', ' ',trim($address_list['area_info']));
            $arr_str=explode(" ",$return);
            $address_list['area_info']=implode(" ", $arr_str);
        //默认发票
            // $order = 'is_default desc,invoice_id asc'
            // $condition = array();
            // $condition['member_id']= $member_id;
            // $invoice_info = db('invoice')->where($condition)->order($order)->find();
        foreach ($result['data']['store_cart_list'] as $key => $value) {
           $data['store_cart_list']=$value;
        } 
        $store_id=$data['store_cart_list'][0]['store_id'];
         //的阶梯价规则
        // $store_id = $_GET['store_id'];
        $model_mansong = Model('p_ladder');
        $model_mansong_rule = Model('p_ladder_rule');
        $condition = array();
        $condition['store_id'] =intval($store_id);
        $condition['is_default'] =1;
        $ladder_info = $model_mansong->getMansongInfo($condition);
        // var_dump($ladder_info);die;     
        // $mansong_id = intval($ladder_info['p_ladder_id']);
        // $ladder_info = $model_mansong_rule->getMansongRuleListByID($mansong_id);
         $deliver_time= $ladder_info['deliver_time'];
        $deliver_time=explode(',',$deliver_time);
         $data['ladder_info']=$deliver_time ;
          //slkedit0111
       $member_info = Model('member')->getMemberInfo(array('member_id'=>$member_id));
           $model_daddress = Model('ziti_address');
           $address_id=$member_info['ziti_id'];
           $condition = array();
           $condition['address_id'] =  $address_id;
           $address_info = $model_daddress->getAddressInfo($condition);
           $data['freight']=0;
           $data['address_info']= $address_info;
            //默认自提点电话
            $where = array();
            $where['ziti_id'] = $address_id;
            $where['member_id'] = $member_id;
            $address_xinxi = Model('address')->getAddressList($where);
            // var_dump( $address_xinxi);die;
           $data['ziti_phone']=$address_xinxi[0]['mob_phone'];
        //slkedit0111
        foreach ($data['store_cart_list'] as $key => $value) {
                $data['store_cart_list'][$key]['goods_image'] = cthumb($value['goods_image'], '', $value['store_id']);
        }
        foreach ($result['data']['store_goods_total'] as $key => $value) {
            $data['store_goods_total']=$value;
        }
        foreach ($result['data']['cancel_calc_sid_list'] as $key => $value) {
           $data['cancel_calc_sid_list']=$value;
        } 
        //取得店铺优惠 - 满即送(赠品列表，店铺满送规则列表)
         foreach ($result['data']['store_mansong_rule_list'] as $key => $value) {
           $data['mansong_rule_list']=$value;
        }             
         $data['mansong_rule_list']= $data['mansong_rule_list'][1];
        $gc_list=rkcache('gc_list1',true);
          foreach($gc_list as $v =>$gc){
            if($data['mansong_rule_list']['mansong_gc_id']==$v) {
                            $gcname= $gc['gc_name'];
             }
         }
        $mansong=array();
        $mansong['mansong_name']=$data['mansong_rule_list']['mansong_name'];
        $mansong['mansong_id']=$data['mansong_rule_list']['mansong_id'];
        $mansong['price']=$data['mansong_rule_list']['price'];//满多少
        $mansong['discount']=$data['mansong_rule_list']['discount'];//减得
        $mansong['desc']=$data['mansong_rule_list']['desc'];//描述 
        $mansong['ruler']="满减(".$gcname.")-满".$data['mansong_rule_list']['price']."减".$data['mansong_rule_list']['discount'];
        $data['mansong_rule_list']= $mansong;
             $payment_code = 'wxpay_jsapi';
        $condition = array();
        $condition['payment_code'] = $payment_code;
        $payment_info = Model()->table('mb_payment')->where($condition)->find();
        // var_dump($payment_info);die;
        $payment_info = unserialize($payment_info['payment_config']);
        $data['appid']=$payment_info['appId'];//小程序id
       if ($result) {
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
             echo json_encode($res,320);
        } else {
             $message=$result['msg'];
            $res = array('code'=>'200' , 'message'=>$message,'data'=>$result);
             echo json_encode($res,320);
        }
       
    }
   
     /**
     购买第2步生成订单
     */
    public function buy_step2Op(){
        $member_model=Model('member');
        $ifcart =$_GET['ifcart'];
        $cart_id = explode(',',$_GET['cart_id']);
        // echo'333';die;
        $member_id = $_GET['member_id'];
        $store_id=$_GET['store_id'];
         //slkedit0111
            $name= $_GET['name'];//收货人姓名
            $phone=$_GET['phone'];//新加的要传的字段电话
			//mx商品活动状态
			$is_group_ladder = $_GET['is_group_ladder'];
            $model_daddress = Model('ziti_address');
           $condition = array();
           $condition['address_id'] = $_GET['address_id'];//这个id为自提地址表的id
           $address_list = $model_daddress->getAddressInfo($condition);
           // var_dump($address_list);die;
           //根据自提地址信息增加用户的地址表
           $address_model=Model('address');
             $add = array(
            'member_id' => $member_id,
            'true_name' => $name,
            'area_id' => $address_list['area_id'],
            'city_id' => $address_list['city_id'],
            'area_info'=>  $address_list['area_info'],
            'address' =>  $address_list['seller_name'],//团购自提点名称
            'tel_phone' => '',
            'mob_phone' => $phone,
            'is_default' => 0,
            'ziti_id'   => $_GET['address_id'],
            );
            $insert_id =  $address_model->addAddress($add);
            $address_id= $insert_id;//选择自提后根据传来的自提地址id储存到个人地址表
            // var_dump(   $add);die;
            $buy_city_id= $address_list['city_id'];
            $address_info=$address_list['area_info'];
           //slkedit0111
        // $address_id=$_GET['address_id'];
        // $buy_city_id=$_GET['city_id'];
        // $address_info=$_GET['address_info'];
        $vat_hash='7FbPIVxFrb8puXO5UK9VDSdTJUzdeSjAMB7';//发票？
        $voucher=array('1'=>'0.00');
        $voucher=array($store_id=>$voucher);
        // var_dump($voucher);die;
        $pay_message=array($store_id=>$_GET['pay_message']);
        $member_info =$member_model->getMemberInfo(array('member_id'=>$member_id));
        $member_name= $member_info['member_name'];
        $member_email=$member_info['member_email'];
        $open_id=$member_info['member_wxopenid'];
        $rule_id=$_GET['rule_id'];
        $time = $_GET['time'];
        $today = strtotime(date("Y-m-d"),time());
        $time =$today+ $time*3600;
        $_POST=array();
        $_POST['ziti_ladder_time']=$time;
        $_POST['rule_id']=$rule_id;
        $_POST['voucher']=$voucher;
        $_POST['ifcart']=$ifcart;
        $_POST['vat_hash']=$vat_hash;
        $_POST['cart_id']=$cart_id;
        $_POST['member_id']=$member_id;
        $_POST['store_id']=$store_id;
        $_POST['address_id']=$address_id;
        $_POST['address_info']=$address_info;
        $_POST['buy_city_id']=$buy_city_id;
        $_POST['allow_offpay']=0;
        $_POST['pay_message']=$pay_message;//买家留言
        $_POST['allow_offpay_batch']='';
        $_POST['offpay_hash']='';
        $_POST['invoice_id']=0;
        $_POST['offpay_hash_batch']='';
        // $_POST['address_detail']=$address_detail;
        $_POST['order_from']=2;
        $_POST['pay_name']='online';
		//mx商品活动状态
		$_POST['order_type'] = $is_group_ladder;
        $buy_logic =Logic('buy');
        $result = $buy_logic->buyStep2($_POST, $member_id,$member_name, $member_email,$ifpintuan);
		
		/**
		$date = date("y-m");
        $dateday = date("y-m-d");
        $path = '../logs/' . $date . '/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $filename = $path . $dateday . ".txt";
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            $content = $content . "\r\n------------------------\r\n" . json_encode($result);
            file_put_contents($filename, $content);
        } else {
            file_put_contents($filename, json_encode($result));
        }
		**/
		
        // echo json_encode($result,320);
       if ($result['state']) {
            $order_no=$result['data']['pay_sn'];
            $order_list=$result['data']['order_list'];
            foreach ($order_list as $key => $value) {
                $pay_price=$value['order_amount'];
            }
            $this->wxPay($order_no, $open_id, $pay_price);
        } else {
            $message=$result['msg'];
            $res = array('code'=>'200' , 'message'=>$message,'data'=>$result);
             echo json_encode($res,320);
        }
    }
     /**
     * 代付款支付
     * @param $member_id
     * @param $order_id
     */
     public function payOp(){
        $order_id = $_GET['order_id'];
        $model_order= Model('order');
        $condition['order_id'] = $order_id;
        $order_info = $model_order->getOrderInfo($condition);
        $order_no=$order_info['order_sn'];
        $member_id = $order_info['buyer_id'];
        $member_model=Model('member');
        $member_info =$member_model->getMemberInfo(array('member_id'=>$member_id));
        $open_id=$member_info['member_wxopenid'];
        $pay_price= $order_info['order_amount'];
         $this->wxPay($order_no, $open_id, $pay_price);
    }
     /**
     * 构建微信支付
     * @param $order_no
     * @param $open_id
     * @param $pay_price
     */
    private function wxPay($order_no, $open_id, $pay_price)
    { 
        $payment_code = 'wxpay_jsapi';
        $condition = array();
        $condition['payment_code'] = $payment_code;
        $payment_info = Model()->table('mb_payment')->where($condition)->find();
        // var_dump($payment_info);die;
        $payment_info = unserialize($payment_info['payment_config']);
        $APPID=$payment_info['appId'];//小程序id
        $MCHID= $payment_info['partnerId'];//商户号
        $KEY=$payment_info['apiKey'];//商户号支付秘钥
         // 当前时间
        $time = time();
        // 生成随机字符串
        $nonceStr = md5($time . $openid);
        // API参数
        // var_dump();die;
        $params = [
            'appid' => $APPID,
            'attach' => '小程序测试',//附加数据，在查询API和支付通知中原样返回，可作为自定义参数使用。
            'body' => $order_no,//商品信息
            'mch_id' => $MCHID,
            'nonce_str' => $nonceStr,
            'notify_url' => BASE_SITE_URL.'/api/notify_url.php',  // 异步通知地址
            'openid' => $open_id,
            'out_trade_no' => $order_no,
            'spbill_create_ip' => $_SERVER['SERVER_ADDR'],
            'total_fee' => $pay_price * 100, // 价格:单位分
            'trade_type' => 'JSAPI',
        ];
        // var_dump($params);die;
        // 生成签名
        $params['sign'] = $this->makeSign($params,$KEY);
        // var_dump($params['sign']);die;
        // 请求API
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        // var_dump($this->toXml($params));die;
        $result = $this->postXmlCurl($this->toXml($params), $url);
        $prepay = $this->fromXml($result);
         // echo json_encode($prepay,320);die;
        // 请求失败
        if ($prepay['return_code'] === 'FAIL') {
            $message=$prepay['return_msg'];
            $res = array('code'=>'200' , 'message'=>$message,'data'=>$prepay);
             echo json_encode($res,320);die;
        }
        // 生成 nonce_str 供前端使用
        $paySign = $this->makePaySign($params['nonce_str'], $prepay['prepay_id'], $time,$APPID,$KEY);
        $result=array(
            'prepay_id' => $prepay['prepay_id'],
            'nonceStr' => $nonceStr,
            'timeStamp' => (string)$time,
            'paySign' => $paySign
        );
            $message='SUCCESS';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$result);
             echo json_encode($res,320);
    }
    /**
     * 选择不同地区时，异步处理并返回每个店铺总运费以及本地区是否能使用货到付款
     * 如果店铺统一设置了满免运费规则，则售卖区域无效
     * 如果店铺未设置满免规则，且使用售卖区域，按售卖区域计算，如果其中有商品使用相同的售卖区域，则两种商品数量相加后再应用该售卖区域计算（即作为一种商品算运费）
     * 如果未找到售卖区域，按免运费处理
     * 如果没有使用售卖区域，商品运费按快递价格计算，运费不随购买数量增加
     */
     private function change_addr($daddress_info) {
        // var_dump($daddress_info);die;
        $logic_buy = Logic('buy');
           // exit(json_encode($_POST['is_groupbuy']));
        $data = $logic_buy->changeAddr($daddress_info['freight_hash'], $daddress_info['city_id'], $daddress_info['area_id'], $daddress_info['member_id'],$daddress_info['is_groupbuy']);
        if(!empty($data)) {
           $message='SUCCESS';
           foreach ($data['content'] as $key => $value) {
               $yunfei=$value;//运费
           }
       }
            return $yunfei;
    }
     /**
     * 支付成功异步通知
     * @param \app\task\model\Order $OrderModel
     * @throws BaseException
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function notifyOp()
    {
//        $xml = <<<EOF
// <xml><appid><![CDATA[wx62f4cad175ad0f90]]></appid>
// <attach><![CDATA[test]]></attach>
// <bank_type><![CDATA[ICBC_DEBIT]]></bank_type>
// <cash_fee><![CDATA[1]]></cash_fee>
// <fee_type><![CDATA[CNY]]></fee_type>
// <is_subscribe><![CDATA[N]]></is_subscribe>
// <mch_id><![CDATA[1499579162]]></mch_id>
// <nonce_str><![CDATA[963b42d0a71f2d160b3831321808ab79]]></nonce_str>
// <openid><![CDATA[o9coS0eYE8pigBkvSrLfdv49b8k4]]></openid>
// <out_trade_no><![CDATA[4000000003672901]]></out_trade_no>
// <result_code><![CDATA[SUCCESS]]></result_code>
// <return_code><![CDATA[SUCCESS]]></return_code>
// <sign><![CDATA[E252025255D59FE900DAFA4562C4EF5C]]></sign>
// <time_end><![CDATA[20180624122501]]></time_end>
// <total_fee>1</total_fee>
// <trade_type><![CDATA[JSAPI]]></trade_type>
// <transaction_id><![CDATA[5000000002801601]]></transaction_id>
// </xml>
// EOF;
        if (!$xml = file_get_contents('php://input')) {
             $this->returnCode(false, 'Not found DATA');
        }
        // 将服务器返回的XML数据转化为数组
        $data = $this->fromXml($xml);
        // var_dump($data);die;
        // 订单信息
         $model_order = Model('order');
        $order_info = $model_order->getOrderInfo(array('order_sn'=> $data['out_trade_no']));
       if(empty($order_info)){    
            $this->returnCode(true, '订单不存在');
            }
            // $result = $this->_update_order($data['out_trade_no'], $data['transaction_id']);
            // var_dump($result);die;
        // 保存微信服务器返回的签名sign
        // $dataSign = $data['sign'];
        // // sign不参与签名算法
        // unset($data['sign']);
        // // 生成签名
        // $sign = $this->makeSign($data,$KEY);
        // 判断签名是否正确  判断支付状态
        if (($data['return_code'] == 'SUCCESS')&& ($data['result_code'] == 'SUCCESS')) {
             $result = $this->_update_order($data['out_trade_no'], $data['trade_no']);
            if($result['state']) {
               $this->returnCode(true, 'OK');
            }
        }
        // 返回微信服务器状态
        $this->returnCode(false, '签名失败');
    }
    /**
     * 构建云打印
     */
    public function yundayinOp() {
        $model_order = Model('order');
        $condition = array();
        $condition['order_id'] = intval($_GET['order_id']);
        
        //$condition['order_sn'] = $out_trade_no;
        $order_info = $model_order->getFpOrderInfo($condition,array('order_common','order_goods','member'));

        if (empty($order_info)) {
            $order_info = false;
        }else{
           //查询匹配的打印机信息，若自提地址和活动类型一样，取ID靠前的一个打印机信息
            $address_id = $order_info['extend_order_common']['reciver_ziti_id'] ;
            $order_type = $order_info['order_type'] ;

            $sql="SELECT * FROM `718shop_yundayin` where address_id = $address_id AND FIND_IN_SET($order_type,order_type) limit 1";  
            $yundayin =Model()->query($sql);
            //print_r($yundayin);
                    
            //设置打印机接口参数
             $SN = $yundayin[0]['dayin_sn'];      //*必填*：打印机编号，必须要在管理后台里添加打印机或调用API接口添加之后，才能调用API
            //echo $SN;die;
            //调用小票机打印订单接口
            $result = $this->_printorder($SN,$order_info);
            $logdata['order_id'] =  intval($_GET['order_id']);
            $logdata['dayin_id'] = $yundayin[0]['dayin_id'];
            $logdata['address_id'] = $address_id;
            
            //判断接口是否成功，不成功重复请求
            if($result['code'] == 1){
                $result['arr'] = json_decode($result['json'] ,true);
                //正确例子
                if($result['arr']['ret'] == 0){
                    //更新订单发送状态
                    $condition1['order_id'] = intval($_GET['order_id']);
                    $updata['dayin_state'] = 1;
                    $model_order->where($condition1)->update($updata);
                   
                     //添加订单日志
                    $this->_insert_dayinlog($result['arr'], $result['json'], $logdata);
                }else{
                    //添加订单日志
                    $this->_insert_dayinlog($result['arr'], $result['json'], $logdata);
                    $result1 = $this->_printorder($SN,$order_info);
                    if($result1['code'] == 1){
                        $result1['arr'] = json_decode($result1['json'] ,true);
                        //正确例子
                        if($result1['arr']['ret'] == 0){
                            //更新订单发送状态
                            $condition2['order_id'] = intval($_GET['order_id']);
                            $updata1['dayin_state'] = 1;
                            $model_order->where($condition2)->update($updata1);

                            //添加订单日志
                            $this->_insert_dayinlog($result1['arr'], $result1['json'], $logdata);
                        }else{
                            //两次请求都失败，插入到异常打印单表
                            $this->_insert_errordayin($result1['arr'], $result1['json'], $logdata);
                            $this->_insert_dayinlog($result1['arr'], $result1['json'], $logdata);
                        }
                    }else{
                        $result1['arr']['ret'] = 'fail';
                        $result1['arr']['msg'] = 'fail';
                        //两次请求都失败，插入到异常打印单表
                        $this->_insert_errordayin($result1['arr'], $result1['json'], $logdata);
                        $this->_insert_dayinlog($result1['arr'], $result1['json'], $logdata);
                    }
                }
            }else{
                $result['arr']['ret'] = 'fail';
                $result['arr']['msg'] = 'fail';
                $this->_insert_dayinlog($result['arr'], $result['json'], $logdata);
                $result2 = $this->_printorder($SN,$order_info);

                if($result2['code'] == 1){
                    $result2['arr'] = json_decode($result2['json'] ,true);
                    //正确例子
                    if($result2['arr']['ret'] == 0){
                        //更新订单发送状态
                        $condition3['order_id'] = intval($_GET['order_id']);
                        $updata2['dayin_state'] = 1;

                        $model_order->where($condition3)->update($updata2);

                        //添加订单日志
                        $this->_insert_dayinlog($result2['arr'], $result2['json'], $logdata);
                    }else{
                        //两次请求都失败，插入到异常打印单表
                        $this->_insert_errordayin($result2['arr'], $result2['json'], $logdata);
                        $this->_insert_dayinlog($result2['arr'], $result2['json'], $logdata);
                    }
                }else{
                    $result2['arr']['ret'] = 'fail';
                    $result2['arr']['msg'] = 'fail';
                    //两次请求都失败，插入到异常打印单表
                    $this->_insert_errordayin($result2['arr'], $result2['json'], $logdata);
                    $this->_insert_dayinlog($result2['arr'], $result2['json'], $logdata);
                }
            }
        }
       
        //return $result;
    }
    /*********************************小票机打印订单接口*********************
    //***接口返回值说明***
    //正确例子：{"msg":"ok","ret":0,"data":"123456789_20160823165104_1853029628","serverExecutedTime":6}
    //错误例子：{"msg":"错误信息.","ret":非零错误码,"data":null,"serverExecutedTime":5}
    */
    private function _printorder($SN, $order_info ,$times = 1 ) {
        //标签说明：
        //单标签:
        //"<BR>"为换行,"<CUT>"为切刀指令(主动切纸,仅限切刀打印机使用才有效果)
        //"<LOGO>"为打印LOGO指令(前提是预先在机器内置LOGO图片),"<PLUGIN>"为钱箱或者外置音响指令
        //成对标签：
        //"<CB></CB>"为居中放大一倍,"<B></B>"为放大一倍,"<C></C>"为居中,<L></L>字体变高一倍
        //<W></W>字体变宽一倍,"<QR></QR>"为二维码,"<BOLD></BOLD>"为字体加粗,"<RIGHT></RIGHT>"为右对齐

        //拼凑订单内容时可参考如下格式
        //根据打印纸张的宽度，自行调整内容的格式，可参考下面的样例格式
        $content = '<CB>物资小店测试打印页</CB><BR>';
        $content .= '名称　　　　　 单价  数量 金额<BR>';
        $content .= '--------------------------------<BR>';
        $content .= '饭　　　　　 　10.0   10  100.0<BR>';
        $content .= '炒饭　　　　　 10.0   10  100.0<BR>';
        $content .= '蛋炒饭　　　　 10.0   10  100.0<BR>';
        $content .= '鸡蛋炒饭　　　 10.0   10  100.0<BR>';
        $content .= '西红柿炒饭　　 10.0   10  100.0<BR>';
        $content .= '西红柿蛋炒饭　 10.0   10  100.0<BR>';
        $content .= '西红柿鸡蛋炒饭 10.0   10  100.0<BR>';
        $content .= '--------------------------------<BR>';
        $content .= '备注：加辣<BR>';
        $content .= '合计：xx.0元<BR>';
        $content .= '送货地点：广州市南沙区xx路xx号<BR>';
        $content .= '联系电话：13888888888888<BR>';
        $content .= '订餐时间：2014-08-08 08:08:08<BR>';
        $content .= '<QR>http://www.feieyun.com</QR>';//把二维码字符串用标签套上即可自动生成二维码

        //$SN = $sn;
        $content = $content;
        $times = 1;

        //打开注释可测试
        $result = $this->printMsg($SN,$content,1);
        return $result;
    }
    /**
   * [打印订单接口 Open_printMsg]
   * @param  [string] $sn      [打印机编号sn]
   * @param  [string] $content [打印内容]
   * @param  [string] $times   [打印联数]
   * @return [string]          [接口返回值]
   */
  function printMsg($sn,$content,$times){
    $time = time();         //请求时间
    $msgInfo = array(
      'user'=>USER,
      'stime'=>$time,
      'sig'=>$this->signature($time),
      'apiname'=>'Open_printMsg',
      'sn'=>$sn,
      'content'=>$content,
      'times'=>$times//打印次数
    );
    // print_r($msgInfo);die;
    $client = new HttpClient(IP,PORT);
    if(!$client->post(PATH,$msgInfo)){
        $result['code'] = 0;
        $result['json'] = '[]'; 
    }else{
      //服务器返回的JSON字符串，建议要当做日志记录起来
        
       $result['json'] = $client->getContent();
       $result['code'] = 1; 
       
    }
    return $result;
  }
  /**
   * [signature 生成签名]
   * @param  [string] $time [当前UNIX时间戳，10位，精确到秒]
   * @return [string]       [接口返回值]
   */
  function signature($time){
    return sha1(USER.UKEY.$time);//公共参数，请求公钥
  }

    /**
     * 添加打印订单日志
     */
    private function _insert_dayinlog($arr, $json, $logdata) {
       $data['code'] = $arr['ret'];
       $data['order_id'] = $logdata['order_id'];
       $data['dayin_id'] = $logdata['dayin_id'];
       $data['address_id'] = $logdata['address_id'];
       $data['message'] = $arr['msg'];
       $data['json'] = $json;
       $data['send_time'] = time();

       Model()->table('dayin_log')->insert($data);
        
    }
    /**
     * 插入异常订单打印表
     */
    private function _insert_errordayin($arr, $json, $logdata) {
       $data['code'] = $arr['ret'];
       $data['order_id'] = $logdata['order_id'];
       $data['dayin_id'] = $logdata['dayin_id'];
       $data['address_id'] = $logdata['address_id'];
       $data['message'] = $arr['msg'];
       $data['json'] = $json;
       $data['send_time'] = time();

       Model()->table('error_dayin')->insert($data);
        
    }
     
    /**
     * 更新订单状态
     */
    private function _update_order($out_trade_no, $trade_no) {
        $model_order = Model('order');
        $logic_payment = Logic('payment');
            $paymentCode = 'online';
            $result = $logic_payment->getRealOrderInfo($out_trade_no);
            if (intval($result['data']['api_pay_state'])) {
                return array('state'=>true);
            }
            $order_list = $result['data']['order_list'];
            $result = $logic_payment->updateRealOrder($out_trade_no, $paymentCode, $order_list, $trade_no);

        return $result;
    }
    /**
     * 将xml转为array
     * @param $xml
     * @return mixed
     */
    private function fromXml($xml)
    {
        // 禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
    /**
     * 生成paySign
     * @param $nonceStr
     * @param $prepay_id
     * @param $timeStamp
     * @return string
     */
    private function makePaySign($nonceStr, $prepay_id, $timeStamp,$appid,$KEY)
    {
        $data = [
            'appId' => $appid,
            'nonceStr' => $nonceStr,
            'package' => 'prepay_id=' . $prepay_id,
            'signType' => 'MD5',
            'timeStamp' => $timeStamp,
        ];

        //签名步骤一：按字典序排序参数
        ksort($data);

        $string = $this->toUrlParams($data);

        //签名步骤二：在string后加入KEY
        $string = $string . '&key=' . $KEY;

        //签名步骤三：MD5加密
        $string = md5($string);

        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);

        return $result;
    }
    /**
     * 生成签名
     * @param $values
     * @return string 本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    private function makeSign($values,$KEY)
    {
        //签名步骤一：按字典序排序参数
        ksort($values);
        $string = $this->toUrlParams($values);
        //签名步骤二：在string后加入KEY
        $string = $string . '&key=' .$KEY;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }    
        /**
     * 以post方式提交xml到对应的接口url
     * 
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws WxPayException
     */
    private static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {       
       $ch = curl_init();
        // 设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);//严格校验
        // 设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        // 运行curl
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    /**
     * 格式化参数格式化成url参数
     * @param $values
     * @return string
     */
    private function toUrlParams($values)
    {
        $buff = '';
        foreach ($values as $k => $v) {
            if ($k != 'sign' && $v != '' && !is_array($v)) {
                $buff .= $k . '=' . $v . '&';
            }
        }
        return trim($buff, '&');
    }
/**
     * 返回状态给微信服务器
     * @param bool $is_success
     * @param string $msg
     */
    private function returnCode($is_success = true, $msg = null)
    {
        $xml_post = $this->toXml([
            'return_code' => $is_success ? $msg ?: 'SUCCESS' : 'FAIL',
            'return_msg' => $is_success ? 'OK' : $msg,
        ]);
        die($xml_post);
    }
    /**
     * 输出xml字符
     * @param $values
     * @return bool|string
     */
    private function toXml($values)
    {
        if (!is_array($values)
            || count($values) <= 0
        ) {
            return false;
        }

        $xml = "<xml>";
        foreach ($values as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
}