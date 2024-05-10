<?php
/**
 * 购买流程
 **/

header("Content-Type: text/html;charset=utf-8");
defined('In718Shop') or exit('Access Invalid!');
class buyControl extends BaseBuyControl {

    public function __construct() {
        parent::__construct();
        Language::read('home_cart_index');
        if (!$_SESSION['member_id']){
            redirect('index.php?act=login&ref_url='.urlencode(request_uri()));
        }
        //验证该会员是否禁止购买
        if(!$_SESSION['is_buy']){
            showMessage(Language::get('cart_buy_noallow'),'','html','error');
        }
        Tpl::output('hidden_rtoolbar_cart', 1);
    }

    /**
     * 实物商品 购物车、直接购买第一步:选择收获地址和配送方式
     */
    public function buy_step1Op() {

        //虚拟商品购买分流
        $this->_buy_branch($_POST);
        //得到购买数据
        $logic_buy = Logic('buy');
        $result = $logic_buy->buyStep1($_POST['cart_id'], $_POST['ifcart'], $_SESSION['member_id'], $_SESSION['store_id']);//var_dump($result);

        if(!$result['state']) {
            showMessage($result['msg'], '', 'html', 'error');
        } else {
            $result = $result['data'];
        }
        $cart_id=$_POST['cart_id'];
        //var_dump($cart_id);
        $ifcart=$_POST['ifcart'];
        Tpl::output('cart_id',$cart_id);
        Tpl::output('ifcart',$ifcart);
        //商品金额计算(分别对每个商品/优惠套装小计、每个店铺小计)
        Tpl::output('store_cart_list', $result['store_cart_list']);
        Tpl::output('store_goods_total', $result['store_goods_total']);

        $goods_id_arr = '';
        if(is_array($result['store_cart_list'][$result['store_id']] )){
        foreach ($result['store_cart_list'][$result['store_id']] as $value) {
            $goods_id_arr .= $value['goods_id'] . ',';
        }
    }
            //所有购买商品的id集合
            Tpl::output('goods_id_arr', $goods_id_arr);

		//商品金额计算(分别对每个商品/优惠套装小计、每个店铺小计)//xinzeng
        Tpl::output('store_agc_total', $result['store_agc_total']);

        //商品税金计算
         Tpl::output('store_goods_tax_total', $result['store_goods_tax_total']);

         //商品模式区分
         Tpl::output('is_mode', $result['store_is_mode']);

        //取得店铺优惠 - 满即送(赠品列表，店铺满送规则列表)
        Tpl::output('store_premiums_list', $result['store_premiums_list']);
        Tpl::output('store_mansong_rule_list', $result['store_mansong_rule_list']);

        //返回店铺可用的代金券
        $gc_list=rkcache('gc_list1',true);
        Tpl::output('gc_list', $gc_list);
        Tpl::output('store_voucher_list', $result['store_voucher_list']);

        //返回需要计算运费的店铺ID数组 和 不需要计算运费(满免运费活动的)店铺ID及描述
        Tpl::output('need_calc_sid_list', $result['need_calc_sid_list']);
        Tpl::output('cancel_calc_sid_list', $result['cancel_calc_sid_list']);

        //将商品ID、数量、售卖区域、运费序列化，加密，输出到模板，选择地区AJAX计算运费时作为参数使用
        Tpl::output('freight_hash', $result['freight_list']);

        //输出用户默认收货地址 gai
        // Tpl::output('address_info', $result['address_info']);


        //输出有货到付款时，在线支付和货到付款及每种支付下商品数量和详细列表
        Tpl::output('pay_goods_list', $result['pay_goods_list']);
        Tpl::output('ifshow_offpay', $result['ifshow_offpay']);
        Tpl::output('deny_edit_payment', $result['deny_edit_payment']);

        //不提供增值税发票时抛出true(模板使用)
        Tpl::output('vat_deny', $result['vat_deny']);

        //增值税发票哈希值(php验证使用)
        Tpl::output('vat_hash', $result['vat_hash']);

        //输出默认使用的发票信息
        Tpl::output('inv_info', $result['inv_info']);

        // Tpl::output('result',$result);
        if($result['store_is_mode']==0){
        //显示预存款、支付密码、充值卡
           Tpl::output('available_pd_amount', $result['available_predeposit']);
        //显示一卡通支付
           if(!empty($result['cardInfo'])){
               Tpl::output('available_card_amount',$result['cardInfo']['Balance']);
               Tpl::output('cardopdate',$result['cardInfo']['cardopdate']);
               Tpl::output('card_status',$result['cardInfo']['Status']);
           }
           Tpl::output('member_paypwd', $result['member_paypwd']);

        //充值卡只支持店铺2
            if($result['store_id']==2){
                Tpl::output('available_rcb_amount', $result['available_rc_balance']);
            }
        }
        //删除购物车无效商品
        $logic_buy->delCart($_POST['ifcart'], $_SESSION['member_id'], $_POST['invalid_cart']);

        //标识购买流程执行步骤
        Tpl::output('buy_step','step2');

        Tpl::output('ifcart', $_POST['ifcart']);

        //店铺信息
        $store_list = Model('store')->getStoreMemberIDList(array_keys($result['store_cart_list']));
        Tpl::output('store_list',$store_list);


        //地址详细列表  //xinzeng
		 $model_addr = Model('address');
	    //如果传入ID，先删除再查询
	    if (!empty($_GET['id']) && intval($_GET['id']) > 0) {
            $model_addr->delAddress(array('address_id'=>intval($_GET['id']),'member_id'=>$_SESSION['member_id']));
	    }
	    $condition = array();
	    $condition['member_id'] = $_SESSION['member_id'];
	    if (!C('delivery_isuse')) {
	        $condition['dlyp_id'] = 0;
	        $order = 'dlyp_id asc,address_id desc';
	    }
	    $list = $model_addr->getAddressList($condition,$order);
	    Tpl::output('address_list',$list);

        // Tpl::output('result',$result);
        Tpl::showpage('buy_step1');

        }

    /**
     * 生成订单
     *
     */
    public function buy_step2Op() {
        //exit(json_encode($_GET['address']));
        $logic_buy = logic('buy');

        $result = $logic_buy->buyStep2($_POST, $_SESSION['member_id'], $_SESSION['member_name'], $_SESSION['member_email']);//var_dump($result);
        if(!$result['state']) {
            showMessage($result['msg'], 'index.php?act=cart', 'html', 'error');
        }
        //转向到商城支付页面
        redirect('index.php?act=buy&op=pay&pay_sn='.$result['data']['pay_sn']);
    }

    /**
     * 下单时支付页面
     */
    public function payOp() {
        $pay_sn	= $_GET['pay_sn'];
        if (!preg_match('/^\d{16,18}$/',$pay_sn)){
            showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=member_order','html','error');
        }
        //查询支付单信息
        $model_order= Model('order');
        $pay_info = $model_order->getOrderPayInfo(array('pay_sn'=>$pay_sn,'buyer_id'=>$_SESSION['member_id']),true);
        if(empty($pay_info)){
            showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=member_order','html','error');
        }
        Tpl::output('pay_info',$pay_info);

        //取子订单列表
        $condition = array();
        $condition['pay_sn'] = $pay_sn;
        $condition['order_state'] = array('in',array(ORDER_STATE_NEW,ORDER_STATE_PAY));
        $order_list = $model_order->getOrderList($condition,'','order_id,order_state,payment_code,order_amount,rcb_amount,pd_amount,card_amount,order_sn','','',array(),true);
        if (empty($order_list)) {
            showMessage('未找到需要支付的订单','index.php?act=member_order','html','error');
        }

        //重新计算在线支付金额
        $pay_amount_online = 0;
        $pay_amount_offline = 0;
        //订单总支付金额(不包含货到付款)
        $pay_amount = 0;

        foreach ($order_list as $key => $order_info) {

            $payed_amount = floatval($order_info['rcb_amount'])+floatval($order_info['pd_amount'])+floatval($order_info['card_amount']);
            //计算相关支付金额
            if ($order_info['payment_code'] != 'offline') {
                if ($order_info['order_state'] == ORDER_STATE_NEW) {
                    $pay_amount_online += ncPriceFormat(floatval($order_info['order_amount'])-$payed_amount);
                }
                $pay_amount += floatval($order_info['order_amount']);
        } else {
            $pay_amount_offline += floatval($order_info['order_amount']);
        }

            //显示支付方式与支付结果
            if ($order_info['payment_code'] == 'offline') {
                $order_list[$key]['payment_state'] = '货到付款';
            } else {
                $order_list[$key]['payment_state'] = '在线支付';
                if ($payed_amount > 0) {
                    $payed_tips = '';
                    if (floatval($order_info['rcb_amount']) > 0) {
                        $payed_tips = '充值卡已支付：￥'.$order_info['rcb_amount'];
                    }
                    if (floatval($order_info['pd_amount']) > 0) {
                        $payed_tips .= ' 预存款已支付：￥'.$order_info['pd_amount'];
                    }
                    if(floatval($order_info['card_amount'])>0){
                        $payed_tips.='一卡通已支付：￥'.$order_info['card_amount'];
                    }
                    $order_list[$key]['order_amount'] .= " ( {$payed_tips} )";
                }
            }
        }
        Tpl::output('order_list',$order_list);

        //如果线上线下支付金额都为0，转到支付成功页
        if (empty($pay_amount_online) && empty($pay_amount_offline)) {
            redirect('index.php?act=buy&op=pay_ok&pay_sn='.$pay_sn.'&pay_amount='.ncPriceFormat($pay_amount));
        }

        //输出订单描述
        if (empty($pay_amount_online)) {
            $order_remind = '下单成功，我们会尽快为您发货，请保持电话畅通！';
        } elseif (empty($pay_amount_offline)) {
            $order_remind = '请您及时付款，以便订单尽快处理！';
        } else {
            $order_remind = '部分商品需要在线支付，请尽快付款！';
        }
        Tpl::output('order_remind',$order_remind);
        Tpl::output('pay_amount_online',ncPriceFormat($pay_amount_online));
        Tpl::output('pd_amount',ncPriceFormat($pd_amount));

        //显示支付接口列表
        if ($pay_amount_online > 0) {
            $model_payment = Model('payment');
            $condition = array();
            $payment_list = $model_payment->getPaymentOpenList($condition);
            if (!empty($payment_list)) {
                unset($payment_list['predeposit']);
                unset($payment_list['offline']);
            }
            if (empty($payment_list)) {
                showMessage('暂未找到合适的支付方式','index.php?act=member_order','html','error');
            }
            Tpl::output('payment_list',$payment_list);
        }

        //标识 购买流程执行第几步
        Tpl::output('buy_step','step3');
        Tpl::showpage('buy_step2');
    }

    /**
     * 预存款充值下单时支付页面
     */
    public function pd_payOp() {
        $pay_sn	= $_GET['pay_sn'];
        if (!preg_match('/^\d{16,18}$/',$pay_sn)){
            showMessage(Language::get('para_error'),'index.php?act=predeposit','html','error');
        }

        //查询支付单信息
        $model_order= Model('predeposit');
        $pd_info = $model_order->getPdRechargeInfo(array('pdr_sn'=>$pay_sn,'pdr_member_id'=>$_SESSION['member_id']));
        if(empty($pd_info)){
            showMessage(Language::get('para_error'),'','html','error');
        }
        if (intval($pd_info['pdr_payment_state'])) {
            showMessage('您的订单已经支付，请勿重复支付','index.php?act=predeposit','html','error');
        }
        Tpl::output('pdr_info',$pd_info);

        //显示支付接口列表
		$model_payment = Model('payment');
        $condition = array();
        $condition['payment_code'] = array('not in',array('offline','predeposit'));
        $condition['payment_state'] = 1;
        $payment_list = $model_payment->getPaymentList($condition);
        if (empty($payment_list)) {
            showMessage('暂未找到合适的支付方式','index.php?act=predeposit&op=index','html','error');
        }
        Tpl::output('payment_list',$payment_list);

        //标识 购买流程执行第几步
        Tpl::output('buy_step','step3');
        Tpl::showpage('predeposit_pay');
    }

	/**
	 * 支付成功页面
	 */
	public function pay_okOp() {
	    //下面有引用到function.php这个文件里面的output_error函数
        require_once(BASE_ROOT_PATH.'/mobile/framework/function/function.php');
        $pay_sn = $_GET['pay_sn'];
        if (!preg_match('/^\d{16,17}$/',$pay_sn)){
            showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=member_order','html','error');
        }

        //查询支付单信息
        $model_order= Model('order');
        $pay_info = $model_order->getOrderPayInfo(array('pay_sn'=>$pay_sn,'buyer_id'=>$_SESSION['member_id']));//var_dump($pay_info);
        if(empty($pay_info)){
            showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=member_order','html','error');
        }
        //从这里开始往中间表里插入数据
        if($pay_info) {
            //根据支付pay_sn去查询order_commom表里面的reciver_info的手机号码
            //查询订单表order和order_common的表信息
             $field = 'order_id,buyer_id,order_sn';
            $order_info = $model_order->getOrderInfo(array('pay_sn' => $pay_sn, 'buyer_id' => $_SESSION['member_id']));
            // $member_mobile = $order_info['extend_order_common']['reciver_info']['mob_phone'];
            // $member_mobile ='17513258661';
            // var_dump($member_mobile);die;
            //查询订单表order和order_common的表信息
            $ordercommon_info = $model_order->getOrderInfo(array('pay_sn' => $pay_sn, 'buyer_id' => $_SESSION['member_id']), array('order_common'), $field);
            $member_mobile = $ordercommon_info['extend_order_common']['reciver_info']['phone'];
            // var_dump($member_mobile);
            //查询商品公共表order_goods表里的数据
            $info = $model_order->getOrderAndOrderGoodsList(array('order_goods.order_id' => $order_info['order_id']));
            foreach ($info as $k => $value) {
                //这里是连接171.15.132.162服务器的数据库语句
                // $connectionInfo = array("Database" => "sysdb", "UID" => "sa", "PWD" => "hnZZzl(!@#)jksp@2018", "CharacterSet"=>"utf-8");
                // $conn = sqlsrv_connect("39.98.82.154", $connectionInfo);
                $host = "39.98.82.154";//20190130修改
        $username = "sa";//"lg&zosc";
        $userpwd = "hnZZzl(!@#)jksp@2018";
        $conn=new PDO("dblib:host=$host;dbname=sysdb;charset=utf8",$username,$userpwd);
               // if ($conn) {
               //     echo "success";
               // } else {
               //     echo "error";
               // }
                //这里开始要往171.15.132.160数据库里插入数据
                //单号 不可为空
                $PFSaleNo = $order_info['order_sn'];
                //可为空
                $BillNo = '777';
                //送货地址 可为空
                $DeliverAddr1 = $ordercommon_info['extend_order_common']['reciver_info']['address'];
                // var_dump($DeliverAddr1);die;
                //截取适合字段类型的字符
                //$DeliverAddr=substr($DeliverAddr1,12);
                //组织编码//门店的唯一标识 不可为空
                if (strpos($DeliverAddr1, '郑欧体验中心店') !== false) {
                    $OrgCode = '01001';
                } else if (strpos($DeliverAddr1, '许昌禹州加盟一店') !== false) {
                    $OrgCode = '01002';
                } else if (strpos($DeliverAddr1, '天之星顺河路加盟一店') !== false) {
                    $OrgCode = '01016';
                } else if (strpos($DeliverAddr1, '周口淮阳加盟一店') !== false) {
                    $OrgCode = '01022';
                } else if (strpos($DeliverAddr1, '福建泉州加盟一店') !== false) {
                    $OrgCode = '14001';
                } else if (strpos($DeliverAddr1, '河南中陆贸易进出口有限公司') !== false) {
                    $OrgCode = 'C001';
                } else if (strpos($DeliverAddr1, '天之星总仓') !== false) {
                    $OrgCode = 'C01005';
                } else if (strpos($DeliverAddr1, '东腾商行总仓') !== false) {
                    $OrgCode = 'C14001';
                }
                // exit(json_encode($OrgCode));
                //根据获取到的门店编码连接数据库查询门店名称
                $query = "select * from subshop where OrgCode='$OrgCode'";
                $stmt=$conn->query($query);
            // $stmt = sqlsrv_query($conn, $query);//exit(json_encode($stmt));
                if ($stmt) {
                   $row = $stmt->fetch();
                        $DeliverAddr = $row['OrgName'];
                    
                }
                // $DeliverAddr='测试地址';
                //用户编码 可为空
                $CustomerCode = $order_info['buyer_id'];
                //用户姓名 可为空
                $CustomerName = $order_info['buyer_name'];
                //有限期 可为空
                $ValidDate = '';
                //批发日期 可为空
                $PFSaleDate = '';
                //付款日期 可为空
                $PayDate = $order_info['payment_time'];
                //出库地址  0  仓库 1  柜台（门店） 不可为空
//                if (strpos($DeliverAddr, '郑欧体验中心') !== false) {
//                    $Place = '1';
//                } else if (strpos($DeliverAddr, '许昌禹州加盟一店') !== false) {
//                    $Place = '1';
//                } else if (strpos($DeliverAddr, '天之星顺河路加盟一店') !== false) {
//                    $Place = '1';
//                } else if (strpos($DeliverAddr, '周口淮阳加盟一店') !== false) {
//                    $Place = '1';
//                } else if (strpos($DeliverAddr, '福建泉州加盟一店') !== false) {
//                    $Place = '1';
//                } else if (strpos($DeliverAddr, '河南中陆贸易进出口有限公司') !== false) {
//                    $Place = '1';
//                } else if (strpos($DeliverAddr, '天之星总仓') !== false) {
//                    $Place = '0';
//                } else if (strpos($DeliverAddr, '东腾商行总仓') !== false) {
//                    $Place = '0';
//                }
                if ($OrgCode == array('in', '01001,01002,01016,01022,14001,C001')) {
                    $Place = '1';
                } else if ($OrgCode == array('in', 'C14001,C01005')) {
                    $Place = '0';
                }
                    //商品编码 不可为空//这里录入商品条码
                    //根据goods_id获取goods表里面的barcode
                    $goods_id = $value['goods_id'];//var_dump($goods_id);
                    $condition = array();
                    $condition['goods_id'] = $goods_id;
                    // $field = 'barcode';
                    $field = 'goods_serial';
                    //这里输出的是数组形式的，要转换为字符串形式
                    $barcode_array = Model('goods')->getGoodsInfo($condition, $field);
                    $PluCode = implode($barcode_array);
                    //批发价 可为空
                    $PfPrice = $value['goods_pay_price'];
                    //数量 可为空
                    $Counts = $value['goods_num'];
                    //赠送数量 不可为空
                    $ZpCount = 0;
                    //批发金额 不可为空
                    $PFTotal = $value['order_amount'];
                    //下单日期 可为空
                    $LrDate = date('Y-m-d', $order_info['add_time']);
                    //录入时间 可为空
                    $LrTime = date('H:i:s', $order_info['add_time']);
                    //用户编码 可为空
                    $UserCode = '';
                    //用户姓名 可为空
                    $UserName = '';
                    //送货人姓名 可为空
                    $SendorName = '';
                    //电话 可为空
                    $Phone = $ordercommon_info['extend_order_common']['reciver_info']['phone'];
                    //备注 可为空
                    $Remark = '';
                    //地址信息 可为空
                    //$AddInfo1 = $order_info['extend_order_common']['reciver_info']['address'];
                    //截取适合字段类型的字符
                    //$AddInfo=substr($AddInfo1,8);
                    $AddInfo = $ordercommon_info['extend_order_common']['reciver_info']['area'];
                    //手机验证码
                    $VerificationCode = '1234';
                    //可以把插入语句跟发送验证码语句写在一块，这样的话插入之后就发送了验证码，这个插入语句最好写在支付成功之后
                    //这里是插入语句//如果非门店下单的话，那个对应的OrgCode是空值，但它是非空约束，所以就算生成了语句也插不进去
                    $query = "insert into PFSaleMid VALUES ($PFSaleNo,'$BillNo',$OrgCode,$CustomerCode,'$CustomerName','$ValidDate','$PFSaleDate','$PayDate','$Place','$PluCode',$PfPrice,$Counts,$ZpCount,$PFTotal,'$LrDate','$LrTime','$UserCode','$UserName','$DeliverAddr','$SendorName','$Phone','$Remark','$AddInfo','$VerificationCode')";
                    // echo $query;
                    // echo $conn;
                    // $stmt = sqlsrv_query($conn, $query);
                    $stmt=$conn->query($query);
                    // var_dump($stmt);die;
                    //$row = sqlsrv_fetch_array($stmt);//var_dump($row);
                    //}
                    //只有当往中间表里面插入数据了，才要发送提货验证码
                    if ($stmt) {
                        try {
                            $pickup_code = rand(100000, 999999);
                            $tpl_info = Model('mail_templates')->getTplInfo(array('code' => 'send_pickup_code'));
                            $param = array();
                            $param['send_time'] = date('Y-m-d H:i', TIMESTAMP);
                            $param['pickup_code'] = $pickup_code;
                            $param['deliver_addr'] = $DeliverAddr;
                            $param['site_name'] = C('site_name');
                            $message = ncReplaceText($tpl_info['content'], $param);//var_dump($message);
                            $sms = new Sms();
                            // var_dump($member_mobile);
                            $result = $sms->send($member_mobile,$message);
                            // var_dump($result);
                            if ($result) {
                                $update_data = array();
                                $update_data['auth_code'] = $pickup_code;
                                $update_data['send_acode_time'] = TIMESTAMP;
                                $update_data['send_acode_times'] = array('exp', 'send_acode_times+1');
                                //member_common表里面统计的是这个会员总共收到了多少条短信，不涉及到短信的具体相关信息
                                $update = Model('member')->editMemberCommon($update_data, array('member_id' => $_SESSION['member_id']));//var_dump($update);
                                if ($update) {
                                    $update_ordercommon['dlyo_pickup_code'] = $pickup_code;
                                    $update_ordercommon['is_ziti'] = '1';
                                    // 更新订单扩展表数据
                                    $renew = Model('order')->editOrderCommon($update_ordercommon, array('order_id' => $order_info['order_id']));
                                    //更新了order_common表里面的提货码之后要同步更新171服务器上的中间表里面的提货码
                                    if ($renew) {
                                        $query = "update PFSaleMid set VerificationCode = $pickup_code where PFSaleNo='$PFSaleNo'";//echo $query;
                                        // $stmt = sqlsrv_query($conn, $query);
                                        $stmt=$conn->query($query);
                                        //$row = sqlsrv_fetch_array($stmt);//var_dump($row);
                                    }
                                    //同步在数据库sms_log表里面插入一条记录
                                    if ($renew) {
                                        $log_array['log_phone'] = $member_mobile;
                                        $log_array['log_captcha'] = $pickup_code;
                                        $log_array['log_ip'] = getIp();
                                        $log_array['log_msg'] = $message;
                                        $log_array['log_type'] = '7';
                                        $log_array['add_time'] = time();
                                        Model('sms_log')->addSms($log_array);
                                    }
                                }
                                if (!$update) {
                                    output_error('系统发生错误');
                                }
                            } else {
                                output_error('验证码发送失败');
                            }
                        } catch (Exception $e) {
                            output_error($e->getMessage());
                        }
                    }
                }
            }

        Tpl::output('pay_info',$pay_info);
        Tpl::output('buy_step','step4');
        Tpl::showpage('buy_step3');
	}


	/**
	 * 加载买家收货地址
	 *
	 */
	public function load_addrOp() {
	    $model_addr = Model('address');
	    //如果传入ID，先删除再查询
	    if (!empty($_GET['id']) && intval($_GET['id']) > 0) {
            $model_addr->delAddress(array('address_id'=>intval($_GET['id']),'member_id'=>$_SESSION['member_id']));
	    }
	    $condition = array();
	    $condition['member_id'] = $_SESSION['member_id'];
	    if (!C('delivery_isuse')) {
	        $condition['dlyp_id'] = 0;
	        $order = 'dlyp_id asc,address_id desc'; 
	    }
	    $list = $model_addr->getAddressList($condition,$order);//var_dump($list);
	    Tpl::output('address_list',$list);
	    Tpl::showpage('buy_address.load','null_layout');
	}

    /**
     * 选择不同地区时，异步处理并返回每个店铺总运费以及本地区是否能使用货到付款
     * 如果店铺统一设置了满免运费规则，则售卖区域无效
     * 如果店铺未设置满免规则，且使用售卖区域，按售卖区域计算，如果其中有商品使用相同的售卖区域，则两种商品数量相加后再应用该售卖区域计算（即作为一种商品算运费）
     * 如果未找到售卖区域，按免运费处理
     * 如果没有使用售卖区域，商品运费按快递价格计算，运费不随购买数量增加
     */
    public function change_addrOp() {
        $logic_buy = Logic('buy');

        $data = $logic_buy->changeAddr($_POST['freight_hash'], $_POST['city_id'], $_POST['area_id'], $_SESSION['member_id']);
        if(!empty($data)) {
            exit(json_encode($data));
        } else {
            exit();
        }
    }

 //检查地区是否支持配送
   public function checkAreaOp()
    {
        $goods_model = Model('goods');
        $transport_model = Model('transport');
        $goods_id_arr = explode(',', $_POST['goods_id_arr']);//count($goods_ids)获取数组长度
        array_pop($goods_id_arr);
        $data = '';
        $city_id = $_POST['city_id'];
        //循环判断city_id是否在各个商品的售卖区域中
        foreach ($goods_id_arr as $value) {//value是goods_id
            $transport_id = $goods_model->getfby_goods_id($value, 'transport_id');
            if($transport_id==0)exit(json_encode($data));
            $transportList = $transport_model->getExtendList(array('transport_id' => $transport_id));
//            $data['transportList'] = $transportList;
            $area_id_array = array();
            foreach ($transportList as $v) {
                $area_ids = explode(',', $v['area_id']);
                array_pop($area_ids);
                array_shift($area_ids);
                $area_id_array=array_merge($area_id_array,$area_ids);
//                if (in_array($city_id, $area_ids)) {
//                }
//                $data['area_ids'] = $area_ids;
//                $data['city_id'] = $city_id;
//                exit(json_encode($data));
            }
//            $data['area_id_array'] = $area_id_array;
            if(in_array($city_id,$area_id_array)){
                continue;
            }else{
                $data['goods_name'] = $goods_model->getfby_goods_id($value, 'goods_name');;
                $data['state'] = 'false';
                exit(json_encode($data));
            }
        }
        exit(json_encode($data));
    }

     /**
      * 添加新的收货地址
      *
      */
    public function add_addrOp(){
         $model_addr = Model('address');
     	if (chksubmit()){
            $count = $model_addr->getAddressCount(array('member_id'=>$_SESSION['member_id']));
            if ($count >= 50) {
                exit(json_encode(array('state'=>false,'msg'=>'最多允许添加50个有效地址')));
            }
     		//验证表单信息
     		$obj_validate = new Validate();
               if($_POST["area_id"]>45055){
                $_POST["address"]='门店自提';
                $obj_validate->validateparam = array(
                array("input"=>$_POST["true_name"],"require"=>"true","message"=>Language::get('cart_step1_input_receiver')),
                array("input"=>$_POST["area_id"],"require"=>"true","validator"=>"Number","message"=>Language::get('cart_step1_choose_area')),
                array("input"=>$_POST["address"],"require"=>"false","message"=>Language::get('cart_step1_input_address'))
            );

            }else{
     		$obj_validate->validateparam = array(
     			array("input"=>$_POST["true_name"],"require"=>"true","message"=>Language::get('cart_step1_input_receiver')),
     			array("input"=>$_POST["area_id"],"require"=>"true","validator"=>"Number","message"=>Language::get('cart_step1_choose_area')),
     			array("input"=>$_POST["address"],"require"=>"true","message"=>Language::get('cart_step1_input_address'))
     		);
        }
     		$error = $obj_validate->validate();
			if ($error != ''){
				$error = strtoupper(CHARSET) == 'GBK' ? Language::getUTF8($error) : $error;
				exit(json_encode(array('state'=>false,'msg'=>$error)));
			}
			$data = array();
			$data['member_id'] = $_SESSION['member_id'];
			$data['true_name'] = $_POST['true_name'];
			$data['area_id'] = intval($_POST['area_id']);
			$data['city_id'] = intval($_POST['city_id']);
			$data['area_info'] = $_POST['area_info'];
			$data['address'] = $_POST['address'];
			$data['tel_phone'] = $_POST['tel_phone'];
			$data['mob_phone'] = $_POST['mob_phone'];
            $data['id_card'] = $_POST['id_card'];
            //var_dump($data);
            //Tpl::output('area_id',$data['area_id']);

            //转码
            $data = strtoupper(CHARSET) == 'GBK' ? Language::getGBK($data) : $data;
            if(!empty($_POST['address_id1']) && intval($_POST['address_id1']) > 0){
                $result = $model_addr->editAddress($data,array('address_id'=>$_POST['address_id1'],'member_id'=>$_SESSION['member_id']));
				if($result){
                    $insert_id = $_POST['address_id1'];
                }
            }else {
			$insert_id = $model_addr->addAddress($data);
            }
			if ($insert_id){
				exit(json_encode(array('state'=>true,'addr_id'=>$insert_id)));
			}else {
				exit(json_encode(array('state'=>false,'msg'=>Language::get('cart_step1_addaddress_fail','UTF-8'))));
			}
     	} else {

            if (!empty($_GET['id']) && intval($_GET['id']) > 0) {
                $addrinfo = $model_addr->getAddressInfo(array('address_id'=>$_GET['id'],'member_id'=>$_SESSION['member_id']));//var_dump($addrinfo);
            }

            Tpl::output('addrinfo',$addrinfo);
     		Tpl::showpage('buy_address.add','null_layout');

     	}
     }

    public function addressOp(){
         
         $address=$_REQUEST['address'];
          // exit(json_encode($address));
         // $address='郑欧体验中心店';
        $return = preg_replace('#\s+#', ' ',trim($address));
        $arr_str=explode(" ",$return);
        if(!empty($arr_str)){
            $str_reverse=array_reverse($arr_str);
            //发货人所在省
            $prov =$str_reverse[0];
            //发货人所在市
            $scity = trim($str_reverse[1]);
        }
        // exit(json_encode($address));
         // exit(json_encode($scity));
        // if (strpos($address, '郑欧体验中心') !== false) {
        //     $OrgCode = '01001';
        // } else if (strpos($address, '许昌禹州加盟一店') !== false) {
        //     $OrgCode = '01002';
        // } else if (strpos($address, '天之星顺河路加盟一店') !== false) {
        //     $OrgCode = '01016';
        // } else if (strpos($address, '周口淮阳加盟一店') !== false) {
        //     $OrgCode = '01022';
        // } else if (strpos($address, '福建泉州加盟一店') !== false) {
        //     $OrgCode = '14001';
        // } else if (strpos($address, '河南中陆贸易进出口有限公司') !== false) {
        //     $OrgCode = 'C001';
        // } else if (strpos($address, '天之星总仓') !== false) {
        //     $OrgCode = 'C01005';
        // } else if (strpos($address, '东腾商行总仓') !== false) {
        //     $OrgCode = 'C14001';
        // }else{
        //     $OrgCode = '0';
        // }
        // exit(json_encode($OrgCode));
        //从这里开始查询库存
        //连接171.15.132.162服务器的数据库语句
        // $connectionInfo = array("Database" => "sysdb", "UID" => "sa", "PWD" => "hnZZzl(!@#)jksp@2018", "CharacterSet"=>"utf-8");
        // $conn = sqlsrv_connect("39.98.82.154", $connectionInfo);

        $host = "39.98.82.154";//20190130修改
        $username = "sa";//"lg&zosc";
        $userpwd = "hnZZzl(!@#)jksp@2018";
        $conn=new PDO("dblib:host=$host;dbname=sysdb;charset=utf8",$username,$userpwd);
       //  if ($conn) {
       //     exit(json_encode("success"));
       // } else {
          // exit(json_encode($scity));
       // }
         // $scity='郑欧体验中心店';
        $query = "select * from SubShop where OrgName='$scity'";//exit(json_encode($query));
            // $stmt = sqlsrv_query($conn, $query);//exit(json_encode($stmt));
         $stmt=$conn->query($query);
            if ($stmt) {
               $row = $stmt->fetch();
                // exit(json_encode($row));
                    $OrgCode = $row['OrgCode'];
                
            // }else{
            //    $OrgCode = '0'; 
            }
        // exit(json_encode($OrgCode));
        //判断数据库是否连接成功
       // if ($conn) {
       //     exit(json_encode("success"));
       // } else {
       //    exit(json_encode("error"));
       // }
        $cart_id1=$_GET['cart_id'];
        //这里可以输出加入购物车的商品id和商品数量
        //exit(json_encode($cart_id1));
        if (is_array($cart_id1)) {
            foreach ($cart_id1 as $value) {
                if (preg_match_all('/^(\d{1,10})\|(\d{1,6})$/', $value, $match)) {
                    if (intval($match[2][0]) > 0) {
                        $num=$match[2][0];
                    }
                }
            }
        }
        //这里可以输出商品要购买的个数
        // exit(json_encode($num));
        $logic_buy = Logic('buy');
        $result = $logic_buy->buyStep1($_GET['cart_id'], $_GET['ifcart'], $_SESSION['member_id'], $_SESSION['store_id']);
        //这里可以根据是否为购物车商品等来输出购物商品发票地址等所有的相关信息
        // exit(json_encode($result));
        if(!$result['state']) {
            showMessage($result['msg'], '', 'html', 'error');
        } else {
            $result = $result['data'];
        }

        $goods_id_arr = '';
                 $a=array();
         $i=0;
        foreach ($result['store_cart_list'][$result['store_id']] as $value) {
             // exit(json_encode($OrgCode));
             if($OrgCode){
                 // exit(json_encode($OrgCode));
            $goods_id_arr .= $value['goods_id'] . ',';//exit(json_encode($goods_id_arr));
            //根据goods_id获取goods表里面的barcode
            $goods_id = $value['goods_id'];
            //exit(json_encode($goods_id));
            $condition = array();
            $condition['goods_id'] = $goods_id;
            // $field = 'barcode';
            $field = 'goods_serial';
            //这里输出的是数组形式的，要转换为字符串形式
            $barcode_array = Model('goods')->getGoodsInfo($condition, $field);
            $barcode = implode($barcode_array);//exit(json_encode($barcode));
            //然后根据barcode联查171服务器上goods表里面的plucode，
            $query = "select * from Goods where Barcode='$barcode'";
            // exit(json_encode($query));
             $stmt=$conn->query($query);
            // $stmt = sqlsrv_query($conn, $query);//exit(json_encode($stmt));
            if ($stmt) {
                // while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
             $row = $stmt->fetch();
                    $plucode = $row['PluCode'];
                
            }
            //再然后再根据plucode联查goodsOrg表里面的gcount
            //这里加了个判断，如果$OrgCode=0,则说明是非线下自提，则默认为有库存，因为在添加购物车之前已经做过库存判断了

            if(empty($barcode_array)){
                $info=0;
                $goodsname=$value['goods_name'];
            }else{
                $query = "select * from GoodsOrg where OrgCode='$OrgCode' AND PluCode='$plucode'";
                // $stmt = sqlsrv_query($conn, $query);
                $stmt=$conn->query($query);
                if ($stmt) {
             $row = $stmt->fetch() ;
                        $gcount = $row['GCount'];
                    
                }
                //根据库存剩余和要购买的商品数量判断库存
                //如果$info的值为1的话说明有货，若$info的值为0则说明库存不足
                if ($gcount >= $num) {
                    $info = 1;
                } else {
                    $info = 0;
                    // $goodsname=$model->table('goods')->getfby_goods_id($goods_id,'goods_name');
                    $goodsname=$value['goods_name'];
                }
            }
            $a[$i]['info']= $info;
            $a[$i]['OrgCode'] = $OrgCode;
            $a[$i]['goodsname'] = $goodsname;
            $i++;
             }else{
                $info = 1;
            $a[$i]['info']= $info;
            $a[$i]['OrgCode'] = $OrgCode;
            $a[$i]['goodsname'] = $goodsname;
            $i++;
               }
            
             // exit( json_encode($a));
        } 
        echo json_encode($a);
    }

	/**
	 * 加载买家发票列表，最多显示10条
	 *
	 */
	public function load_invOp() {
        $logic_buy = Logic('buy');

	    $condition = array();
	    if ($logic_buy->buyDecrypt($_GET['vat_hash'], $_SESSION['member_id']) == 'allow_vat') {
	    } else {
	        Tpl::output('vat_deny',true);
	        $condition['inv_state'] = 1;
	    }
	    $condition['member_id'] = $_SESSION['member_id'];

	    $model_inv = Model('invoice');
	    //如果传入ID，先删除再查询
	    if (intval($_GET['del_id']) > 0) {
            $model_inv->delInv(array('inv_id'=>intval($_GET['del_id']),'member_id'=>$_SESSION['member_id']));
	    }
	    $list = $model_inv->getInvList($condition,10);
	    if (!empty($list)) {
	        foreach ($list as $key => $value) {
	           if ($value['inv_state'] == 1) {
	               $list[$key]['content'] = '普通发票'.' '.$value['inv_title'].' '.$value['inv_content'];
	           } else {
	               $list[$key]['content'] = '增值税发票'.' '.$value['inv_company'].' '.$value['inv_code'].' '.$value['inv_reg_addr'];
	           }
	        }
	    }
	    Tpl::output('inv_list',$list);
	    Tpl::showpage('buy_invoice.load','null_layout');
	}

     /**
      * 新增发票信息
      *
      */
    public function add_invOp(){
        $model_inv = Model('invoice');
     	if (chksubmit()){
     		//如果是增值税发票验证表单信息
     		if ($_POST['invoice_type'] == 2) {
     		    if (empty($_POST['inv_company']) || empty($_POST['inv_code']) || empty($_POST['inv_reg_addr'])) {
     		        exit(json_encode(array('state'=>false,'msg'=>Language::get('nc_common_save_fail','UTF-8'))));
     		    }
     		}
			$data = array();
            if ($_POST['invoice_type'] == 1) {
                $data['inv_state'] = 1;
                $data['inv_title'] = $_POST['inv_title_select'] == 'person' ? '个人' : $_POST['inv_title'];
                $data['inv_content'] = $_POST['inv_content'];
            } else {
                $data['inv_state'] = 2;
    			$data['inv_company'] = $_POST['inv_company'];
    			$data['inv_code'] = $_POST['inv_code'];
    			$data['inv_reg_addr'] = $_POST['inv_reg_addr'];
    			$data['inv_reg_phone'] = $_POST['inv_reg_phone'];
    			$data['inv_reg_bname'] = $_POST['inv_reg_bname'];
    			$data['inv_reg_baccount'] = $_POST['inv_reg_baccount'];
    			$data['inv_rec_name'] = $_POST['inv_rec_name'];
    			$data['inv_rec_mobphone'] = $_POST['inv_rec_mobphone'];
    			$data['inv_rec_province'] = $_POST['area_info'];
    			$data['inv_goto_addr'] = $_POST['inv_goto_addr'];
            }
            $data['member_id'] = $_SESSION['member_id'];
	     	//转码
            $data = strtoupper(CHARSET) == 'GBK' ? Language::getGBK($data) : $data;
			$insert_id = $model_inv->addInv($data);
			if ($insert_id) {
				exit(json_encode(array('state'=>'success','id'=>$insert_id)));
			} else {
				exit(json_encode(array('state'=>'fail','msg'=>Language::get('nc_common_save_fail','UTF-8'))));
			}
     	} else {
     		Tpl::showpage('buy_address.add','null_layout');
     	}
     } 

    /**
     * AJAX验证支付密码
     */
    public function check_pd_pwdOp(){
        if (empty($_GET['password'])) exit('0');
        $buyer_info	= Model('member')->getMemberInfoByID($_SESSION['member_id'],'member_paypwd');
		if($buyer_info['member_paypwd'] === md5($_GET['password'])){
            //添加日志
            Model('card_balance_log')->addcard_balance_log($_SESSION['member_id'],'zihpay');
        }
        echo ($buyer_info['member_paypwd'] != '' && $buyer_info['member_paypwd'] === md5($_GET['password'])) ? '1' : '0';
    }

    /*
     * 验证一卡通密码   //不要了
    */
    // public function check_card_pwdOp(){
    //     $pwd=$_GET['pwd'];
    //     if(!empty($pwd)){
    //         $member_id=$_SESSION['member_id'];
    //         $cardno=Model()->table('member_card')->where(array('member_id'=>$member_id))->find();
    //         $card_info=Model('card')->getMemberCardInfo($cardno['cardno']);
    //         echo ($card_info['Password']!=''&&$card_info['Password']===$pwd)?'1':'0';
    //     }
    //     else{
    //         exit('0');
    //     }
    // }
    
    /**
     * F码验证
     */
    public function check_fcodeOp() {
        $result = logic('buy')->checkFcode($_GET['goods_commonid'], $_GET['fcode']);
        echo $result['state'] ? '1' : '0';
        exit;
    }

    /**
     * 得到所购买的id和数量
     *
     */
    private function _parseItems($cart_id) {
        //存放所购商品ID和数量组成的键值对
        $buy_items = array();
        if (is_array($cart_id)) {
            foreach ($cart_id as $value) {
                if (preg_match_all('/^(\d{1,10})\|(\d{1,6})$/', $value, $match)) {
                    $buy_items[$match[1][0]] = $match[2][0];
                }
            }
        }
        return $buy_items;
    }

    /**
     * 购买分流
     */
    private function _buy_branch($post) {
        if (!$post['ifcart']) {
            //取得购买商品信息
            $buy_items = $this->_parseItems($post['cart_id']);
            $goods_id = key($buy_items);
            $quantity = current($buy_items);

            $goods_info = Model('goods')->getGoodsOnlineInfoAndPromotionById($goods_id);
            if ($goods_info['is_virtual']) {
                redirect('index.php?act=buy_virtual&op=buy_step1&goods_id='.$goods_id.'&quantity='.$quantity);
            }
        }
    }


}
