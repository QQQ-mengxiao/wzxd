<?php
defined('In718Shop') or exit('Access Invalid!');
    
class OrderControl extends BaseControl{
    
   
    /**
     *购买第一步
     */
    public function buy_step1Op()
    {
        //用户id
        $member_id = $_GET['member_id'];
        if(!$member_id){
            die(json_encode(array('code' => '200', 'message' => '请先登录', 'data' => []), 320));
        }
        // sleep(50);
        //购物车标记，1购物车购买，0直接购买
        $ifcart = $_GET['ifcart'];
        //购物车购买为"购物车ID|数量"，多个数据由逗号隔开；直接购买为"商品ID|数量"
        $cart_id = explode(',', $_GET['cart_id']);

        $buy_logic = Logic('buy');
        $result = $buy_logic->buyStep1($cart_id, $ifcart, $member_id);

        // //详细地址
        // $address_info = Model('address')->getDefaultAddressInfo(array('member_id' => $member_id));
        // $address_info['id'] = $address_info['address_id'];
        // $address_info['address_id'] = $address_info['ziti_id'];
        // // $result['data']['address_info'] = $address_info;
        // if($address_info){
        //     $result['data']['address_info'] = $address_info;
        // }
        // if($_GET['tihuo_id']){
        //     $address_info = Model()->table('ziti_address')->field('*')->where(array('address_id'=>$_GET['tihuo_id']))->find();//print_r($address_info);die;
        //     $result['data']['address_info'] = $address_info;
        // }

        // //自提地址
        // $condition = array();
        // $condition['ziti_id'] = $_GET['tihuo_id']?$_GET['tihuo_id']:$address_info['ziti_id'];
        // $condition['member_id'] = $member_id;
        // $address_xinxi = Model('address')->getAddressList($condition);
        // $result['data']['ziti_phone'] = $address_xinxi[0]['mob_phone'];
        // $result['data']['name'] = $address_xinxi[0]['true_name'];
        // $result['data']['mall_info'] = $address_xinxi[0]['mall_info'];

         $by_post = $_GET['by_post'];
        if($by_post == 2){
            /*邮寄地址*/
            $address_info = Model("address_you")->table("address_you")->where(array('member_id'=>$member_id,'member_id'=>$member_id))->order('is_default desc,address_id desc')->find();
            if($address_info){
              $return = preg_replace('#\s+#', ' ',trim($address_info['area_info']));
              $arr_str=explode(",",$return);
              $address_info['area_info']=implode(" ", $arr_str);
            }
            $result['data']['address_info'] = $address_info;
            
        }else{
        //   sleep(50);
          /*自提地址 by_post*/
          //详细地址
          $address_info = Model('address')->getDefaultAddressInfo(array('member_id' => $member_id));
          $address_info['id'] = $address_info['address_id'];
          $address_info['address_id'] = $address_info['ziti_id'];
          // $result['data']['address_info'] = $address_info;
          if($address_info){
              $result['data']['address_info'] = $address_info;
          }
          if($_GET['tihuo_id']){
            $address_info = Model()->table('ziti_address')->field('*')->where(array('address_id'=>$_GET['tihuo_id']))->find();//print_r($address_info);die;
            $result['data']['address_info'] = $address_info;
          }
          //自提地址
          $condition = array();
          $condition['ziti_id'] = $_GET['tihuo_id']?$_GET['tihuo_id']:$address_info['ziti_id'];
          $condition['member_id'] = $member_id;
          $address_xinxi = Model('address')->getAddressList($condition);
          $result['data']['ziti_phone'] = $address_xinxi[0]['mob_phone'];
          $result['data']['name'] = $address_xinxi[0]['true_name'];
          $result['data']['mall_info'] = $address_xinxi[0]['mall_info'];
        }

        //重量0
        $result['data']['freight'] = 0;
        $payment_code = 'wxpay_jsapi';
        $condition1 = array();
        $condition1['payment_code'] = $payment_code;
        $payment_info = Model()->table('mb_payment')->where($condition1)->find();
        $payment_info = unserialize($payment_info['payment_config']);
        $result['data']['appid']=$payment_info['appId'];
        // sleep(50);
        if ($result['state']) {
            $message = 'sucess';
            $res = array('code' => '100', 'message' => $message, 'data' => $result['data']);
            die(json_encode($res, 320));
        } else {
            $message = $result['msg'];
            $res = array('code' => '200', 'message' => $message, 'data' => $result);
            die(json_encode($res, 320));
        }
    }
    /**
     *购买第2步生成订单
     */
    public function buy_step2Op()
    {
        $member_model = Model('member');
        $ifcart = $_GET['ifcart'];
        $cart_id = explode(',', $_GET['cart_id']);
        $member_id = $_GET['member_id'];
        $store_id = $_GET['store_id'];
        $name = $_GET['name']; //收货人姓名
        $phone = $_GET['phone']; //电话
        // sleep(50);
        if (strlen($phone) != 11 || empty($name)) {
            $res = array('code' => '400', 'message' => '手机号不能为空', 'data' => $phone);
            die(json_encode($res, 320));
        }
        //mx商品活动状态
        $is_group_ladder = $_GET['is_group_ladder'];
        $model_daddress = Model('ziti_address');
        $condition = array();
        $by_post = $_GET['by_post'];
        if($by_post == 2){
          $address_id = $_GET['address_id']>0?$_GET['address_id']:0;
          $address_list = Model("address_you")->table("address_you")->where(array('address_id'=>$address_id))->find();
          if($address_list){
            $return = preg_replace('#\s+#', ' ',trim($address_list['area_info']));
            $arr_str=explode(",",$return);
            $address_list['area_info']=implode(" ", $arr_str);
          }

        }else{
          $condition['address_id'] = $_GET['address_id']>0?$_GET['address_id']:3; //这个id为自提地址表的id
          //新增正常营业筛选
          $condition['state'] = 1;
          $address_list = $model_daddress->getAddressInfo($condition);
          if(empty($address_list)){
            $message = '该自提地址歇业中，请选择其他地址购物！';
            $res = array('code' => '20003', 'message' => $message, 'data' => '');
            die(json_encode($res, 320));
          }
          
          //根据自提地址信息增加用户的地址表
          $address_model = Model('address');
          $add = array(
              'member_id' => $member_id,
              'true_name' => $name,
              'area_id' => $address_list['area_id'],
              'city_id' => $address_list['city_id'],
              'area_info' =>  $address_list['area_info'],
              'ziti_name' =>  $address_list['seller_name'], //团购自提点名称
              'address' =>  $address_list['address'],
              'tel_phone' => '',
              'mob_phone' => $phone,
              'is_default' => 0,
              'ziti_id'   => $_GET['address_id'],
              'mall_info' => $_GET['mall_info'],
          );

          $address_model->addAddress($add); //每次购买都要插入一条新的地址,选择自提后根据传来的自提地址id储存到个人地址表
         
        }
        $member_info = $member_model->getMemberInfo(array('member_id' => $member_id));
        $data = array();
        // sleep(50);
        //分享人id和分享公司id
        //share_id和company_id默认设置为0
        if (!empty($member_info['share_id'])) {
            $data['share_id'] = $member_info['share_id'];
            $share_member_info = $member_model->getMemberInfo(array('member_id' => $member_info['share_id']), 'company_id');
            $data['company_id'] = $share_member_info['company_id'];
        } else {
            $data['share_id'] = 0;
            $data['company_id'] = 0;
        }
        $data['voucher_t_id'] = intval($_GET['voucher_id']);
        $data['ifcart'] = $ifcart;
        $data['cart_id'] = $cart_id;
        $data['member_id'] = $member_id;
        $data['store_id'] = $store_id;
        if($by_post == 2){
          $data['mob_phone'] = $address_list['mob_phone'];
          $data['mall_info'] = $_GET['mall_info'];
          $data['reciver_info'] = serialize(array('phone'=>$address_list['mob_phone'],'mob_phone'=>$address_list['mob_phone'],'tel_phone'=>$address_list['mob_phone'],'address'=>$address_list['area_info'].$address_list['address'],'area'=>$address_list['area_info'],'street'=>$address_list['address'],'id_card'=>$address_list['id_card']));
          $data['ziti_id'] = 0;
          $data['true_name'] = $address_list['true_name'];
          $data['buy_city_id'] = $address_list['city_id'];
          $data['reciver_province_id'] = $address_list['prov_id'];
          $data['by_post'] = 2;
          $data['address_you_id'] = $address_id;
        }else{
           $data['by_post'] = 1;
            $data['mob_phone'] = $add['mob_phone'];
            $data['mall_info'] = $add['mall_info'];
            $data['reciver_info'] = serialize(array('phone'=>$add['mob_phone'],'mob_phone'=>$add['mob_phone'],'tel_phone'=>$add['mob_phone'],'address'=>$add['area_info'].$add['address'].$add['ziti_name'],'area'=>$add['area_info'],'street'=>$add['address'],'id_card'=>0));
            $data['ziti_id'] = $add['ziti_id'];
            $data['true_name'] = $add['true_name'];
            // $data['address_info'] = $address_info;
            $data['buy_city_id'] = $add['city_id'];
            $data['reciver_province_id'] = Model()->table('area')->getfby_area_id($add['city_id'],'area_parent_id');
        }
        
        $data['pay_message'] = $_GET['pay_message']; //买家留言
        $data['order_from'] = 2;
        $data['pay_name'] = 'online';
        $data['order_type'] = $is_group_ladder;

        $buy_logic = Logic('buy');
        $result = $buy_logic->buyStep2($data, $member_id);

        if ($result['state']) {
            $message = 'sucess';
            $order_list = $result['data']['order_list'];
            $res = array('code' => '10001', 'message' => $message, 'data' => $order_list);
            echo json_encode($res, 320);
            exit();
        } else {
            $message = $result['msg'];
            $res = array('code' => '200', 'message' => $message, 'data' => $result);
            echo json_encode($res, 320);
        }
    }
    /**
     *购买第2步生成订单
     */
    public function buy_step2_0622Op()
    {
        $member_model = Model('member');
        // sleep(50);
        $ifcart = $_GET['ifcart'];
        $cart_id = explode(',', $_GET['cart_id']);
        $member_id = $_GET['member_id'];
        $store_id = $_GET['store_id'];
        $name = $_GET['name']; //收货人姓名
        $phone = $_GET['phone']; //电话
        if (strlen($phone) != 11 || empty($name)) {
            $res = array('code' => '400', 'message' => '手机号不能为空', 'data' => $phone);
            die(json_encode($res, 320));
        }
        //mx商品活动状态
        $is_group_ladder = $_GET['is_group_ladder'];
        $model_daddress = Model('ziti_address');
        $condition = array();
        $condition['address_id'] = $_GET['address_id']>0?$_GET['address_id']:3; //这个id为自提地址表的id
        //新增正常营业筛选
        $condition['state'] = 1;
        $address_list = $model_daddress->getAddressInfo($condition);
        if(empty($address_list)){
          $message = '该自提地址歇业中，请选择其他地址购物！';
          $res = array('code' => '20003', 'message' => $message, 'data' => '');
          die(json_encode($res, 320));
        }
        
        //根据自提地址信息增加用户的地址表
        $address_model = Model('address');
        $add = array(
            'member_id' => $member_id,
            'true_name' => $name,
            'area_id' => $address_list['area_id'],
            'city_id' => $address_list['city_id'],
            'area_info' =>  $address_list['area_info'],
            'ziti_name' =>  $address_list['seller_name'], //团购自提点名称
            'address' =>  $address_list['address'],
            'tel_phone' => '',
            'mob_phone' => $phone,
            'is_default' => 0,
            'ziti_id'   => $_GET['address_id'],
            'mall_info' => $_GET['mall_info'],
        );

        $address_model->addAddress($add); //每次购买都要插入一条新的地址,选择自提后根据传来的自提地址id储存到个人地址表
        $member_info = $member_model->getMemberInfo(array('member_id' => $member_id));
        $data = array();
        //分享人id和分享公司id
        //share_id和company_id默认设置为0
        // sleep(50);
        if (!empty($member_info['share_id'])) {
            $data['share_id'] = $member_info['share_id'];
            $share_member_info = $member_model->getMemberInfo(array('member_id' => $member_info['share_id']), 'company_id');
            $data['company_id'] = $share_member_info['company_id'];
        } else {
            $data['share_id'] = 0;
            $data['company_id'] = 0;
        }
        $data['voucher_t_id'] = intval($_GET['voucher_id']);
        $data['ifcart'] = $ifcart;
        $data['cart_id'] = $cart_id;
        $data['member_id'] = $member_id;
        $data['store_id'] = $store_id;
        $data['mob_phone'] = $add['mob_phone'];
        $data['mall_info'] = $add['mall_info'];
        $data['reciver_info'] = serialize(array('phone'=>$add['mob_phone'],'mob_phone'=>$add['mob_phone'],'tel_phone'=>$add['mob_phone'],'address'=>$add['area_info'].$add['address'].$add['ziti_name'],'area'=>$add['area_info'],'street'=>$add['address'],'id_card'=>0));
        $data['ziti_id'] = $add['ziti_id'];
        $data['true_name'] = $add['true_name'];
        // $data['address_info'] = $address_info;
        $data['buy_city_id'] = $add['city_id'];
        $data['reciver_province_id'] = Model()->table('area')->getfby_area_id($add['city_id'],'area_parent_id');
        $data['pay_message'] = $_GET['pay_message']; //买家留言
        $data['order_from'] = 2;
        $data['pay_name'] = 'online';
        $data['order_type'] = $is_group_ladder;

        $buy_logic = Logic('buy');
        $result = $buy_logic->buyStep2($data, $member_id);

        if ($result['state']) {
            $message = 'sucess';
            $order_list = $result['data']['order_list'];
            $res = array('code' => '10001', 'message' => $message, 'data' => $order_list);
            echo json_encode($res, 320);
            exit();
        } else {
            $message = $result['msg'];
            $res = array('code' => '200', 'message' => $message, 'data' => $result);
            echo json_encode($res, 320);
        }
    }
    public function paymentOp(){
            $member_id=$_GET['member_id'];
            $order_id=$_GET['order_id'];
            $model_order= Model('order');
            $member_model=Model('member');
            $condition['order_id'] = $order_id;
            $order_info = $model_order->getOrderInfo($condition);
             $order_list=array();
             $order_list[$order_id]=$order_info;
             // var_dump($order_list);die;
            $buyer_info =$member_model->getMemberInfo(array('member_id'=>$member_id));
            $open_id=$buyer_info['member_wxopenid'];
            if($_GET['card_pay']==1){
                   //edit修改豫卡通支付位置
                $cardno=Model()->table('member_card')->where(array('member_id'=>$member_id))->limit(1)->find();
                $buyer_info['cardno']=$cardno['cardno'];
                $card_info=Model('card')->getMemberCardInfo($buyer_info['cardno']);
                $buyer_info['available_card_balance']=ncPriceFormat($card_info['Balance']);
                 $buy1_logic =Logic('buy_1');

                 $result=$buy1_logic->cardPay($order_list,'',$buyer_info);
                  // var_dump($result);die;
                 if($result){
                     $message='cardpaysucess';
                     $res = array('code'=>'10001','message'=>$message,'data'=>'');
                     echo json_encode($res,320);exit();
                 }else{
                     $message='fail';
                     $res = array('code'=>'2001','message'=>$message,'data'=>'');
                     echo json_encode($res,320);
                 }
            }elseif ($_GET['card_pay'] == 2) {
                  $buy1_logic =Logic('buy_1');
                  $result=$buy1_logic->offline_balancePay($order_list,$member_id);
                  if($result){
                    $message='offline_balancepaysucess';
                    $res = array('code'=>'10001','message'=>$message,'data'=>'');
                     echo json_encode($res,320);exit();
                   }
                   else{
                      $message='fail';
                      $res = array('code'=>'2001','message'=>$message,'data'=>'');
                      echo json_encode($res,320);exit;
                   }
            }elseif ($_GET['card_pay']==3){
                //集团餐卡支付
                $member_uid_model = Model('member_uid');
                $uid = $member_uid_model->getUid($member_id);
                //1. 集团餐卡余额查询
                $data = array('uid' => $uid);
                //测试地址
                //$url = "http://171.15.132.170:8083/api/smallprogram/center/selectCardInfo";
                //正式地址
                //$url = "https://xls.zhonghaokeji.net/api/smallprogram/center/selectCardInfo";
                $url = "https://xls.zitcloud.cn/api/smallprogram/center/selectCardInfo";
                $ji_response = $this->selectJiBalance($url,$data);
                //print_r($ji_response);die;
                if (!empty($ji_response) && $ji_response['code'] == 0) {
                    //余额
                    $buyer_info['available_card_balance'] = ncPriceFormat($ji_response['balance']);
                    $buyer_info['cardNo'] = $ji_response['cardNo'];
                }
                $buy1_logic =Logic('buy_1');
                $result=$buy1_logic->jicardPay($order_list,'',$buyer_info);
                 if($result){
                     $message='cardpaysucess';
                     $res = array('code'=>'10001','message'=>$message,'data'=>'');
                     echo json_encode($res,320);exit();
                 }else{
                     $message='fail';
                     $res = array('code'=>'2001','message'=>$message,'data'=>'');
                     echo json_encode($res,320);
                 }
            }elseif ($_GET['card_pay']==4){
                  $cardno=Model()->table('member_card')->where(array('member_id'=>$member_id))->limit(1)->find();
                $buyer_info['gonghao']=$cardno['gonghao'];
                $card_info=Model('card')->getBalancebygh($buyer_info['gonghao']);
                $buyer_info['available_exchange_balance']=ncPriceFormat($card_info['Balance16']);
                 // var_dump($buyer_info);die;
                 $buy1_logic =Logic('buy_1');
                 $result=$buy1_logic->exchangePay($order_list,'',$buyer_info);
                  // var_dump($result);die;
                 if($result){
                     $message='cardpaysucess';
                     $res = array('code'=>'10001','message'=>$message,'data'=>'');
                     echo json_encode($res,320);exit();
                 }else{
                     $message='fail';
                     $res = array('code'=>'2001','message'=>$message,'data'=>'');
                     echo json_encode($res,320);
                 }
            }else{
                $order_no=$order_info['pay_sn'];
                foreach ($order_list as $key => $value) {
                    $pay_price=$value['order_amount'];
                }
                // var_dump($order_list);die;
                $this->wxPay($order_no, $open_id, $pay_price);
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
            $res = array('code'=>'20110' , 'message'=>$message,'data'=>$prepay);
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
            $result = $this->_update_order($data['out_trade_no'], $data['transaction_id']);

            if($result['state']) {
                $this->returnCode(true, 'OK');
            }            
            
        }
        // 返回微信服务器状态
        $this->returnCode(false, '签名失败');
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
     * 更新订单状态
     */
    public function update_order1Op() {

        $out_trade_no = $_GET['out_trade_no'];
        $trade_no = $_GET['trade_no'];
        $model_yundayin = Model('yundayin');
        $model_yundayin->_yundayin11($out_trade_no);
        //$model_yundayin->_yundayin11($out_trade_no);
        die;
        $model_order = Model('order');
        $logic_payment = Logic('payment');
            $paymentCode = 'online';
            
            $result = $logic_payment->getRealOrderInfo($out_trade_no);
            //print_r($result);
            // if (intval($result['data']['api_pay_state'])) {
            //     return array('state'=>true);
            // }
            $order_list = $result['data']['order_list'];
            // print_r( $order_list);die;
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
         private function deliver_time($time,$store_id){
         $model_mansong = Model('p_ladder');
        $model_mansong_rule = Model('p_ladder_rule');
        $condition = array();
        $condition['store_id'] =intval($store_id);
        $condition['is_default'] =1;
        $ladder_info = $model_mansong->getMansongInfo($condition);
        // var_dump($ladder_info);die;     
        $mansong_id = intval($ladder_info['p_ladder_id']);
        $ladder_rule = $model_mansong_rule->getMansongRuleListByID($mansong_id);
        // var_dump($ladder_info);die;
        // echo date( "h:i ");
        foreach ($ladder_rule as $key => $value) {
         $time_dian[]=$value['time'];
        }
        // var_dump($time_dian);die;
        $a=date('H',time())+date('s',time())/60;
        // $a=2.5;
        $chazhi=$time-$a;
        // if($chazhi<2){
        //     $data=0;
        //    return  $data;
        // }
         if($a>11.5){
           $data=0;
           return  $data;exit();
        }
        $max=max( $time_dian);
        // var_dump($max);die;
        if($max>= $chazhi){
           $count=count($time_dian);
          for ($i=0; $i <$count ; $i++) {
             $arr2[]=$chazhi-$time_dian[$i];
          }
          for ($i=0; $i <$count ; $i++) {
            if ($arr2[$i]<=0) {
                 $time=$time_dian[$i];
                 break;
            }
          }
        }else{
          $time=$max;
        }
       foreach ($ladder_rule as $key => $value) {
         if( $time==$value['time']){
          $data=$value['discount'];
         }
       }
        return $data;
    }
    
    public function paymentTypeOp()
    {
        $member_id = $_GET['member_id'];
        // $member_info = Model()->table('member')->where(array('member_id'=>$member_id))->find();
        $member_info = Model("member")->getMemberInfoByID($member_id);
        //var_dump($member_info);

        //线下余额查询
        $member_uid_model = Model('member_uid');
        $member_uid_log_model = Model('member_uid_log');
        $balance_info = array();
        $uid = $member_uid_model->getUid($member_id);
        //var_dump($uid);die;
        if ($uid) {
            //集团餐卡余额查询
            $data = array('uid' => $uid);
            //$data = array('uid' => '713');
            //测试地址
            //$url = "http://171.15.132.170:8083/api/smallprogram/center/selectCardInfo";
            //正式地址
            //$url = "https://xls.zhonghaokeji.net/api/smallprogram/center/selectCardInfo";
            $url = "https://xls.zitcloud.cn/api/smallprogram/center/selectCardInfo";
            $ji_response = $this->selectJiBalance($url,$data);
            if (!$ji_response || $ji_response['code'] != 0) {
                //定义返回的数据
                $balance_info['ji_balance'] = 0;
                $balance_info['ji_open'] = 0;
            } else {
                //如果member表没存cardNo
                if($member_info['cardNo'] != $ji_response['cardNo']){
                   Model()->table('member')->where(array('member_id'=>$member_id))->update(array('cardNo'=>$ji_response['cardNo']));
                }
                //定义返回的数据
                $balance_info['ji_balance'] = ncPriceFormat($ji_response['balance']);
                $balance_info['ji_open'] = 1;
            }
            
            //会员卡余额
            $response = $member_uid_model->selectBalance($uid);
            if (!$response || $response['code'] != 0) {
                $log_data = array('member_id' => $member_id, 'uid' => $uid, 'action' => 2, 'log_time' => time(), 'content' => '查询失败：' . $response['msg'], 'result' => 3);
                //定义返回的数据
                $balance_info['offline_balance'] = 0;
                $balance_info['is_open'] = 0;
            } else {
                $log_data = array('member_id' => $member_id, 'uid' => $uid, 'action' => 2, 'log_time' => time(), 'content' => '查询成功，余额' . $response['balance'], 'result' => 2);
                //定义返回的数据
                $balance_info['offline_balance'] = ncPriceFormat($response['balance']);
                $balance_info['is_open'] = 1;
            }
        }
        $member_uid_log_model->addLog($log_data);
        $result['balance_info'] = $balance_info;
		// var_dump($result);die;
        //一卡通信息
        $cardno = Model()->table('member_card')->where(array('member_id' => $member_id))->limit(1)->find();
        // var_dump($cardno);die;
        if (!empty($cardno)) {
            $card_info = Model('card')->getMemberCardInfo($cardno['cardno']);
            // var_dump($card_info);die;
            // if (!empty($card_info['Balance'])) {
                $result['card_amount'] = ncPriceFormat($card_info['Balance']);
            // }
            $Balance_info = Model('card')->getBalancebygh($cardno['gonghao']);
            if (!empty($Balance_info)) {
                $result['new_amount'] = ncPriceFormat($Balance_info['Balance16']);
            }
        }
        //var_dump($result);die;
        $result['member_paypwd'] = $member_info['member_paypwd'] ? true : false;
        if ($result) {
            $message = 'sucess';
            $res = array('code' => '100', 'message' => $message, 'data' => $result);
            echo json_encode($res, 320);
        } else {
            $message = $result['msg'];
            $res = array('code' => '200', 'message' => $message, 'data' => $result);
            echo json_encode($res, 320);
        }
    }
    private function selectJiBalance($url,$data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        //判断返回数据是否json格式
        if (is_null(json_decode($response))){
            return false;
        }
        return json_decode($response,true);
    }
}
