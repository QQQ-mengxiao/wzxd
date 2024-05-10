<?php
/**
 * 发货
 *
 *
 *
 ***/


defined('In718Shop') or exit('Access Invalid!');
//电商ID
defined('EBusinessID') or define('EBusinessID', 1274426);
//电商加密私钥，快递鸟提供，注意保管，不要泄漏
defined('AppKey') or define('AppKey', 'cc5b260c-c19e-4eb5-b2b6-e9fa3fedb33e');
//请求url
defined('ReqURL') or define('ReqURL', 'http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx');
include_once template('phpqrcode/phpqrcode');

class store_deliverControl extends BaseSellerControl {
	public function __construct() {
		parent::__construct();
		Language::read('member_store_index,deliver');
	}

	/**
	 * 发货列表
	 *
	 */
	public function indexOp() {
	    $model_order = Model('order');
		if (!in_array($_GET['state'],array('deliverno','delivering','delivered'))) $_GET['state'] = 'deliverno';
		$order_state = str_replace(array('deliverno','delivering','delivered'),
		        array(ORDER_STATE_PAY,ORDER_STATE_SEND,ORDER_STATE_SUCCESS),$_GET['state']);
		$condition = array();
		$condition['store_id'] = $_SESSION['store_id'];
		$condition['order_state'] = $order_state;
		if ($_GET['buyer_name'] != '') {
		    $condition['buyer_name'] = $_GET['buyer_name'];
		}
         //发货人姓名 新增

        if ($_GET['senderusername']!=''){
            $sql="SELECT * from `718shop_order_goods` where kuajing_info like '%".$_GET['senderusername']."%'";
            $kuajing_info=Model()->query($sql);
            $order_id=array();
            for($i=0;$i<count($kuajing_info);$i++){
                $order_id[$i]=$kuajing_info[$i]['order_id'];
            }

            $condition['order_id']=array('in',$order_id);
        }

        if ($_GET['order_sn'] != '') {
		    $condition['order_sn'] = $_GET['order_sn'];
		}
		if ($_GET['logisticsNo']!=''){
        	$con=array();
        	$con['store_id']=$_SESSION['store_id'];
        	$sql="SELECT * from `718shop_order_common` where store_id=".$_SESSION['store_id']." and waybill_info like '%".$_GET['logisticsNo']."%'";
			$waybill_info = Model()->query($sql);
        	$order_id=array();
			for ($i = 0; $i < count($waybill_info); $i++) {
                $order_id[$i]=$waybill_info[$i]['order_id'];
				}
        	$condition['order_id']=array('in',$order_id);
			}


		if ($_GET['is_mode'] != '') {
            $condition['is_mode'] = $_GET['is_mode'];
        }
		$if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
		$if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
		$start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
		$end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']): null;
		if ($start_unixtime || $end_unixtime) {
		    $condition['add_time'] = array('time',array($start_unixtime,$end_unixtime));
		}
        $pay_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['pay_start_date']);
        $pay_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['pay_end_date']);
        $start_paytime = $pay_start_date ? strtotime($_GET['pay_start_date']) : null;
        $end_paytime = $pay_end_date ? strtotime($_GET['pay_end_date']): null;
        if ($start_paytime || $end_paytime) {
            $condition['add_time'] = array('time',array($start_paytime,$end_paytime));
        }
		$order_list = $model_order->getOrderList($condition,5,'*','order_id desc','',array('order_goods','order_common','member'));
		$number = $model_order->getOrderList($condition,'','*','order_id desc','',array('order_goods','order_common','member'));
		$num = count($number);
		$order_list_id = array();
		if(is_array($number)){
			foreach($number as $order_all_info){

					$order_list_id[] = $order_all_info['order_id'];

			}
		}

		$order_all_id = implode(",",$order_list_id);

		foreach ($order_list as $key => $order_info) {
		    foreach ($order_info['extend_order_goods'] as $value) {
		        $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
		        $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
		        $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
		        $value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
		        if ($value['goods_type'] == 5) {
		            $order_info['zengpin_list'][] = $value;
		        } else {
		            $order_info['goods_list'][] = $value;
		        }
		    }

		    if (empty($order_info['zengpin_list'])) {
		        $order_info['goods_count'] = count($order_info['goods_list']);
		    } else {
		        $order_info['goods_count'] = count($order_info['goods_list']) + 1;
		    }
		    $order_list[$key] = $order_info;
		}
        Tpl::output('order_all_id',$order_all_id);
        Tpl::output('num',$num);
		Tpl::output('order_list',$order_list);
		Tpl::output('show_page',$model_order->showpage());
		self::profile_menu('deliver',$_GET['state']);
		Tpl::showpage('store_order.deliver');
	}

	/**
	 * 发货
	 */
	public function sendOp(){
        $order_id = intval($_GET['order_id']);
		if ($order_id <= 0){
			showMessage(Language::get('wrong_argument'),'','html','error');
		}

		$model_order = Model('order');
		$condition = array();
		$condition['order_id'] = $order_id;
		$condition['store_id'] = $_SESSION['store_id'];
		$order_info = $model_order->getOrderInfo($condition,array('order_common','order_goods'));
		$if_allow_send = intval($order_info['lock_state']) || !in_array($order_info['order_state'],array(ORDER_STATE_PAY,ORDER_STATE_SEND,ORDER_STATE_SUCCESS));
		if ($if_allow_send) {
		    showMessage(Language::get('wrong_argument'),'','html','error');
		}

		if (chksubmit()){
		    $logic_order = Logic('order');
		    $_POST['reciver_info'] = $this->_get_reciver_info();
		    $result = $logic_order->changeOrderSend($order_info, 'seller', $_SESSION['member_name'], $_POST);
			if (!$result['state']) {
			    showMessage($result['msg'],'','html','error');
			} else {
			    showDialog($result['msg'],$_POST['ref_url'],'succ');
			}
		}

        Tpl::output('order_info',$order_info);
		//取发货地址
		$model_daddress = Model('daddress');
		if ($order_info['extend_order_common']['daddress_id'] > 0 ){
			$daddress_info = $model_daddress->getAddressInfo(array('address_id'=>$order_info['extend_order_common']['daddress_id']));
		}else{
		    //取默认地址
			$daddress_info = $model_daddress->getAddressList(array('store_id'=>$_SESSION['store_id']),'*','is_default desc',1);
			$daddress_info = $daddress_info[0];

            //写入发货地址编号
            $this->_edit_order_daddress($daddress_info['address_id'], $order_id);
		}
		Tpl::output('daddress_info',$daddress_info);

		$express_list  = rkcache('express',true);

		//如果是自提订单，只保留自提快递公司
		if (isset($order_info['extend_order_common']['reciver_info']['dlyp'])) {
		    foreach ($express_list as $k => $v) {
		        if ($v['e_zt_state'] == '0') unset($express_list[$k]);
		    }
		    $my_express_list = array_keys($express_list);
		} else {
		    //快递公司
		    $my_express_list = Model()->table('store_extend')->getfby_store_id($_SESSION['store_id'],'express');
		    if (!empty($my_express_list)){
		        $my_express_list = explode(',',$my_express_list);
		    }
		}

		Tpl::output('my_express_list',$my_express_list);
		Tpl::output('express_list',$express_list);
		Tpl::showpage('store_deliver.send');
	}

    /**
     * 设置商品属性
     */
    public function edit_goods_kuajingOp()
    {
        if (chksubmit()) {
            $str_ids = $_COOKIE['str_id'];
            $order_id_array = explode(',', $str_ids);
            array_pop($order_id_array);
            $goods_shipper_id = $_POST['goods_shipper_id'];
            $sql = 'SELECT * FROM `718shop_shipper_kuajing_d` where shipper_id=' . $goods_shipper_id . ' LIMIT 1';
            $shipperArr = Model()->query($sql);
            $where = array('order_id' => array('in', $order_id_array), 'store_id' => $_SESSION['store_id']);
            $order_goods_list = Model('order')->getOrderGoodsList($where);
            foreach ($order_goods_list as $value) {
                $kuajing_info = unserialize($value['kuajing_info']);
                $kuajing_info['senderusername'] = $shipperArr[0]['shipper_name'];
                $kuajing_info['senderuseraddress'] = $shipperArr[0]['shipper_address'];
                $kuajing_info['senderusertelephone'] = $shipperArr[0]['shipper_phone'];
            $update = array('kuajing_info' => serialize($kuajing_info));
                $return = Model('order')->editOrderGoods($update, array('order_id'=>$value['order_id'],'goods_id' => $value['goods_id']));
            }
            setcookie('str_id','',time() - 3600);
            if ($return) {
                $this->recordSellerLog('设置发货人，订单编号：' . substr($str_ids, 0, -1));
                showDialog(L('nc_common_op_succ'), 'reload', 'succ');
            } else {
                showDialog(L('nc_common_op_fail'), 'reload');
            }
        }
        $model_shipper = Model('shipper_kuajing_d');
        $kuajing_shipper = $model_shipper->where(array('store_id' => $_SESSION ['store_id']))->select();
        Tpl::output('kuajing_shipper', $kuajing_shipper);
        Tpl::showpage('store_order_deliver.edit_goods_kuajing', 'null_layout');
    }

	/**
	 * 编辑收货地址
	 * @return boolean
	 */
	public function buyer_address_editOp() {
	    $order_id = intval($_GET['order_id']);
	    if ($order_id <= 0) return false;
	    $model_order = Model('order');
		$condition = array();
		$condition['order_id'] = $order_id;
		$condition['store_id'] = $_SESSION['store_id'];
		$order_common_info = $model_order->getOrderCommonInfo($condition);
        if (!$order_common_info) return false;
        $order_common_info['reciver_info'] = @unserialize($order_common_info['reciver_info']);

		Tpl::output('address_info',$order_common_info);

		Tpl::showpage('store_deliver.buyer_address.edit','null_layout');
	}

    /**
     * 收货地址保存
     */
    public function buyer_address_saveOp() {
        $model_order = Model('order');
        $data = array();
        $data['reciver_name'] = $_POST['reciver_name'];
        $data['reciver_info'] = $this->_get_reciver_info();
        
        $condition = array();
        $condition['order_id'] = intval($_POST['order_id']);
        $condition['store_id'] = $_SESSION['store_id'];
        $result = $model_order->editOrderCommon($data, $condition);
        if($result) {
            echo 'true';
        } else {
            echo 'flase';
        }
    }

    /**
     * 组合reciver_info
     */
    private function _get_reciver_info() {
        $reciver_info = array(
            'address' => $_POST['reciver_area'] . ' ' . $_POST['reciver_street'],
            'phone' => trim($_POST['reciver_mob_phone'] . ',' . $_POST['reciver_tel_phone'],','),
            'area' => $_POST['reciver_area'],
            'street' => $_POST['reciver_street'],
            'mob_phone' => $_POST['reciver_mob_phone'],
            'tel_phone' => $_POST['reciver_tel_phone'],
            'id_card' => $_POST['reciver_id_card']
        );

        return serialize($reciver_info);
    }

	/**
	 * 选择发货地址
	 * @return boolean
	 */
	public function send_address_selectOp() {
	    Language::read('deliver');
	    $address_list = Model('daddress')->getAddressList(array('store_id'=>$_SESSION['store_id']));
	    Tpl::output('address_list',$address_list);
	    Tpl::output('order_id', $_GET['order_id']);
	    Tpl::showpage('store_deliver.daddress.select','null_layout');
	}

    /**
     * 保存发货地址修改
     */
    public function send_address_saveOp() {
        $result = $this->_edit_order_daddress($_POST['daddress_id'], $_POST['order_id']);
        if($result) {
            echo 'true';
        } else {
            echo 'flase';
        }
    }

    /**
     * 修改发货地址
     */
    private function _edit_order_daddress($daddress_id, $order_id) {
        $model_order = Model('order');
        $data = array();
        $data['daddress_id'] = intval($daddress_id);
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['store_id'] = $_SESSION['store_id'];
        return $model_order->editOrderCommon($data, $condition);
    }

    /**
	 * 物流跟踪
	 */
	public function search_deliverOp(){
		Language::read('member_member_index');
		$lang	= Language::getLangContent();

		$order_sn	= $_GET['order_sn'];
		if (!is_numeric($order_sn)) showMessage(Language::get('wrong_argument'),'','html','error');
		$model_order	= Model('order');
		$condition['order_sn'] = $order_sn;
		$condition['store_id'] = $_SESSION['store_id'];
		$order_info = $model_order->getOrderInfo($condition,array('order_common','order_goods'));
		if (empty($order_info) || $order_info['shipping_code'] == '') {
		    showMessage('未找到信息','','html','error');
		}
		$order_info['state_info'] = orderState($order_info);
		Tpl::output('order_info',$order_info);
		//卖家发货信息
		$daddress_info = Model('daddress')->getAddressInfo(array('address_id'=>$order_info['extend_order_common']['daddress_id']));
		Tpl::output('daddress_info',$daddress_info);

		//取得配送公司代码
		$express = rkcache('express',true);
		Tpl::output('e_code',$express[$order_info['extend_order_common']['shipping_express_id']]['e_code']);
		Tpl::output('e_name',$express[$order_info['extend_order_common']['shipping_express_id']]['e_name']);
		Tpl::output('e_url',$express[$order_info['extend_order_common']['shipping_express_id']]['e_url']);
		Tpl::output('shipping_code',$order_info['shipping_code']);

		self::profile_menu('search','search');
		Tpl::showpage('store_deliver.detail');
	}

	/**
	 * 延迟收货
	 */
	public function delay_receiveOp(){
	    $order_id = intval($_GET['order_id']);
	    $model_trade = Model('trade');
	    $model_order = Model('order');
	    $condition = array();
	    $condition['order_id'] = $order_id;
	    $condition['store_id'] = $_SESSION['store_id'];
	    $condition['lock_state'] = 0;
	    $order_info = $model_order->getOrderInfo($condition);

        //MX获取订单商品类型进行判断
        if($order_info['is_mode'] == 0) {
	    //取目前系统最晚收货时间
	    $delay_time = $order_info['delay_time'] + ORDER_AUTO_RECEIVE_DAY * 3600 * 24;
        }elseif ($order_info['is_mode'] == 2){
            //取目前系统最晚收货时间（集货模式）
            $delay_time = $order_info['delay_time'] + JIHUO_ORDER_AUTO_RECEIVE_DAY * 3600 * 24;
        }
	    if (chksubmit()) {
	        $delay_date = intval($_POST['delay_date']);
	        if (!in_array($delay_date,array(5,10,15))) {
	            showDialog(Language::get('wrong_argument'),'','error',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
	        }
	        $update = $model_order->editOrder(array('delay_time'=>array('exp','delay_time+'.$delay_date*3600*24)),$condition);
	        if ($update) {
	            //新的最晚收货时间
	            $dalay_date = date('Y-m-d H:i:s',$delay_time+$delay_date*3600*24);
	            showDialog("成功将最晚收货期限延迟到了".$dalay_date.'&emsp;','','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();',4);
	        } else {
	            showDialog('延迟失败','','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
	        }
        } else {
            $order_info['delay_time'] = $delay_time;
            Tpl::output('order_info',$order_info);
            Tpl::showpage('store_deliver.delay_receive','null_layout');
            exit();
        }
	}

	/**
	 * 从第三方取快递信息
	 *
	 */
	public function get_expressOp(){
        $requestData= array(
            'OrderCode' => $_GET['OrderCode'],
            'ShipperCode' => $_GET['ShipperCode'],
            'LogisticCode' => $_GET['LogisticCode'],
        );
        $requestData = json_encode($requestData,true);

        $datas = array(
            'EBusinessID' => EBusinessID,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, AppKey);
        $result=$this->sendPost(ReqURL, $datas);

        $result=json_decode($result,true);
        if(!$result['Traces']) exit(json_encode(false));
        $output = array();
        if (is_array($result['Traces'])){
            foreach ($result['Traces'] as $k=>$v) {
                if ($v['AcceptTime'] == '') continue;
                $output[]= $v['AcceptTime'].'&nbsp;&nbsp;'.$v['AcceptStation'];
            }
        }
        if (empty($output)) exit(json_encode(false));
        $output['result']=$result;

        echo json_encode($output);
    }

    /**
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if(empty($url_info['port']))
        {
            $url_info['port']=80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }

    /**
     * 电商Sign签名生成
     * @param data 内容
     * @param appkey Appkey
     * @return DataSign签名
     */
    function encrypt($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }
	/**
	 * 从第三方取快递信息
	 *
	 */
	public function get_express1Op(){
        $url = 'http://www.kuaidi100.com/query?type='.$_GET['e_code'].'&postid='.$_GET['shipping_code'].'&id=1&valicode=&temp='.random(4).'&sessionid=&tmp='.random(4);
        import('function.ftp');
        $content = dfsockopen($url);
        $content = json_decode($content,true);
        if ($content['status'] != 200) exit(json_encode(false));
        $content['data'] = array_reverse($content['data']);
        $output = '';
        if (is_array($content['data'])){
            foreach ($content['data'] as $k=>$v) {
                if ($v['time'] == '') continue;
                $output .= '<li>'.$v['time'].'&nbsp;&nbsp;'.$v['context'].'</li>';
            }
        }
        if ($output == '') exit(json_encode(false));
        if (strtoupper(CHARSET) == 'GBK'){
            $output = Language::getUTF8($output);//网站GBK使用编码时,转换为UTF-8,防止json输出汉字问题
        }
        echo json_encode($output);
	}

    /**
     * 运单打印
     */
    public function waybill_printOp() {
        $order_id = intval($_GET['order_id']);
        if($order_id <= 0) {
            showMessage(L('param_error'));
        }

        $model_order = Model('order');
        $model_store_waybill = Model('store_waybill');
        $model_waybill = Model('waybill');

        $order_info = $model_order->getOrderInfo(array('order_id' => intval($_GET['order_id'])), array('order_common'));

        $store_waybill_list = $model_store_waybill->getStoreWaybillList(array('store_id' => $order_info['store_id']), 'is_default desc');

        $store_waybill_info = $this->_getCurrentWaybill($store_waybill_list, $_GET['store_waybill_id']);
        if(empty($store_waybill_info)) {
            showMessage('请首先绑定打印模板', urlShop('store_waybill', 'waybill_manage'), '', 'error');
        }

        $waybill_info = $model_waybill->getWaybillInfo(array('waybill_id' => $store_waybill_info['waybill_id']));
        if(empty($waybill_info)) {
            showMessage('请首先绑定打印模板', urlShop('store_waybill', 'waybill_manage'), '', 'error');
        }

        //根据订单内容获取打印数据
        $print_info = $model_waybill->getPrintInfoByOrderInfo($order_info);

        //整理打印模板
        $store_waybill_data = unserialize($store_waybill_info['store_waybill_data']);
        foreach ($waybill_info['waybill_data'] as $key => $value) {
            $waybill_info['waybill_data'][$key]['show'] = $store_waybill_data[$key]['show'];
            $waybill_info['waybill_data'][$key]['content'] = $print_info[$key];
        }

        //使用商家自定义的偏移尺寸
        $waybill_info['waybill_pixel_top'] = $store_waybill_info['waybill_pixel_top'];
        $waybill_info['waybill_pixel_left'] = $store_waybill_info['waybill_pixel_left'];

        Tpl::output('waybill_info', $waybill_info);
        Tpl::output('store_waybill_list', $store_waybill_list);
        Tpl::showpage('waybill.print', 'null_layout');
    }

    /**
     * 获取当前打印模板
     */
    private function _getCurrentWaybill($store_waybill_list, $store_waybill_id) {
        if(empty($store_waybill_list)) {
            return false;
        }

        $store_waybill_id = intval($store_waybill_id);

        $store_waybill_info = null;

        //如果指定模板使用指定的模板，未指定使用默认模板
        if($store_waybill_id > 0) {
            foreach ($store_waybill_list as $key => $value) {
                if($store_waybill_id == $value['store_waybill_id']) {
                    $store_waybill_info = $store_waybill_list[$key];
                    break;
                }
            }
        }

        if(empty($store_waybill_info)) {
            $store_waybill_info = $store_waybill_list[0];
        }

        return $store_waybill_info;
    }

	/**
	 * 用户中心右边，小导航
	 *
	 * @param string	$menu_type	导航类型
	 * @param string 	$menu_key	当前导航的menu_key
	 * @return
	 */
	private function profile_menu($menu_type,$menu_key='') {
		Language::read('member_layout');
		$menu_array		= array();
		switch ($menu_type) {
			case 'deliver':
				$menu_array = array(
				array('menu_key'=>'deliverno',			'menu_name'=>Language::get('nc_member_path_deliverno'),	'menu_url'=>'index.php?act=store_deliver&op=index&state=deliverno'),
				array('menu_key'=>'delivering',			'menu_name'=>'已发货',	'menu_url'=>'index.php?act=store_deliver&op=index&state=delivering'),
				array('menu_key'=>'delivered',		'menu_name'=>Language::get('nc_member_path_delivered'),	'menu_url'=>'index.php?act=store_deliver&op=index&state=delivered'),
				);
				break;
			case 'search':
				$menu_array = array(
				1=>array('menu_key'=>'nodeliver',			'menu_name'=>Language::get('nc_member_path_deliverno'),	'menu_url'=>'index.php?act=store_deliver&op=index&state=nodeliver'),
				2=>array('menu_key'=>'delivering',			'menu_name'=>Language::get('nc_member_path_delivering'),	'menu_url'=>'index.php?act=store_deliver&op=index&state=delivering'),
				3=>array('menu_key'=>'delivered',		'menu_name'=>Language::get('nc_member_path_delivered'),	'menu_url'=>'index.php?act=store_deliver&op=index&state=delivered'),
				4=>array('menu_key'=>'search',		'menu_name'=>Language::get('nc_member_path_deliver_info'),	'menu_url'=>'###'),
				);
				break;
		}
		Tpl::output('member_menu',$menu_array);
		Tpl::output('menu_key',$menu_key);
	}

public function EorderPushOp() {


$dkj = Logic('kuajing');
$result = $dkj->EorderPush();

print_r($result);
}

/*
	*生成电子口岸订单报文zip（批量东站）
	*
	*
	**/
	public function DorderPush1Op(){
		$result = array();
		$filename = array();
		$id_array = explode(",", $_GET['order_id']);
		$time = date("YmdHis", time());
		$zip = new ZipArchive();
		$dirname = $time . '.zip';
		$zip->open($dirname, ZipArchive::CREATE);
		if (is_array($id_array)) {
			foreach ($id_array as $key => $value) {
				$dkj = Logic('kuajing');
                $payment_code = Model()->table('order')->getfby_order_id($value,'payment_code');
               // if($payment_code=='alipay'){
                   // $result[$key] = $dkj->DorderJM78_ali($value);
              //  }else {
				//$result[$key] = $dkj->DorderJM78($value);
				$result[$key] = $dkj->DorderJM78_new($value);
               // }
				$appTime = date("YmdHis", time());
				$sql = 'SELECT order_sn FROM `718shop_order` where order_id=' . $value . ' LIMIT 1';
				$itemNoArr = Model()->query($sql);
				$orderNo = $itemNoArr[0]['order_sn'];
				$filename[$key] = $appTime . "_" . $orderNo . ".xml";
				$zip->addFromString($filename[$key], $result[$key]);
			}
		}
		$zip->close();
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header('Content-disposition: attachment; filename=' . basename($dirname)); //文件名
		header("Content-Type: application/zip"); //zip格式的
		header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
		header('Content-Length: ' . filesize($dirname)); //告诉浏览器，文件大小
		@readfile($dirname);

		unlink($dirname);


	}
	/*
	*生成电子口岸订单报文zip（批量机场）
	*
	*
	**/
	public function DorderPush3Op(){
		$result = array();
		$filename = array();
		$id_array = explode(",", $_GET['order_id']);
		$time = date("YmdHis", time());
		$zip = new ZipArchive();
		$dirname = $time . '.zip';
		$zip->open($dirname, ZipArchive::CREATE);
		if (is_array($id_array)) {
			foreach ($id_array as $key => $value) {
				$dkj = Logic('kuajing');
                $payment_code = Model()->table('order')->getfby_order_id($value,'payment_code');
               // if($payment_code=='alipay'){
                   // $result[$key] = $dkj->DorderJM78_ali($value);
              //  }else {
				//$result[$key] = $dkj->DorderJM78($value);
				$result[$key] = $dkj->DorderJM78_new1($value);
               // }
				$appTime = date("YmdHis", time());
				$sql = 'SELECT order_sn FROM `718shop_order` where order_id=' . $value . ' LIMIT 1';
				$itemNoArr = Model()->query($sql);
				$orderNo = $itemNoArr[0]['order_sn'];
				$filename[$key] = $appTime . "_" . $orderNo . ".xml";
				$zip->addFromString($filename[$key], $result[$key]);
			}
		}
		$zip->close();
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header('Content-disposition: attachment; filename=' . basename($dirname)); //文件名
		header("Content-Type: application/zip"); //zip格式的
		header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
		header('Content-Length: ' . filesize($dirname)); //告诉浏览器，文件大小
		@readfile($dirname);

		unlink($dirname);


	}
	/*
	*生成电子口岸清单报文zip(东站批量)
	*
	*
	**/
	public function DlistPush1Op(){
		$result = array();
		$filename = array();
		$id_array = explode(",", $_GET['order_id']);
		$appTime = date("YmdHis", time());
		$zip = new ZipArchive();
		$dirname = 'QD' . $appTime . '.zip';
		$zip->open($dirname, ZipArchive::CREATE);
		if (is_array($id_array)) {
			foreach ($id_array as $key => $value) {
				$dkj = Logic('kuajing');
                $payment_code = Model()->table('order')->getfby_order_id($value,'payment_code');
              //  if($payment_code=='alipay'){
              //      $result[$key] = $dkj->DlistJM78_ali($value);
              //  }else {
				//$result[$key] = $dkj->DlistJM78($value);
				$result[$key] = $dkj->DlistJM78_new($value);
              //  }
				$sql = 'SELECT order_sn FROM `718shop_order` where order_id=' . $value . ' LIMIT 1';
				$itemNoArr = Model()->query($sql);
				$orderNo = $itemNoArr[0]['order_sn'];
				$filename[$key] = "QD" . $appTime . "_" . $orderNo . ".xml";
				$zip->addFromString($filename[$key], $result[$key]);
			}
		}
		$zip->close();
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header('Content-disposition: attachment; filename=' . basename($dirname)); //文件名
		header("Content-Type: application/zip"); //zip格式的
		header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
		header('Content-Length: ' . filesize($dirname)); //告诉浏览器，文件大小
		@readfile($dirname);

		unlink($dirname);
	}
	/*
	*生成电子口岸清单报文zip(机场批量)
	*
	*
	**/
	public function DlistPush3Op(){
		$result = array();
		$filename = array();
		$id_array = explode(",", $_GET['order_id']);
		$appTime = date("YmdHis", time());
		$zip = new ZipArchive();
		$dirname = 'QD' . $appTime . '.zip';
		$zip->open($dirname, ZipArchive::CREATE);
		if (is_array($id_array)) {
			foreach ($id_array as $key => $value) {
				$dkj = Logic('kuajing');
                $payment_code = Model()->table('order')->getfby_order_id($value,'payment_code');
              //  if($payment_code=='alipay'){
              //      $result[$key] = $dkj->DlistJM78_ali($value);
              //  }else {
				//$result[$key] = $dkj->DlistJM78($value);
				$result[$key] = $dkj->DlistJM78_new1($value);
              //  }
				$sql = 'SELECT order_sn FROM `718shop_order` where order_id=' . $value . ' LIMIT 1';
				$itemNoArr = Model()->query($sql);
				$orderNo = $itemNoArr[0]['order_sn'];
				$filename[$key] = "QD" . $appTime . "_" . $orderNo . ".xml";
				$zip->addFromString($filename[$key], $result[$key]);
			}
		}
		$zip->close();
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header('Content-disposition: attachment; filename=' . basename($dirname)); //文件名
		header("Content-Type: application/zip"); //zip格式的
		header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
		header('Content-Length: ' . filesize($dirname)); //告诉浏览器，文件大小
		@readfile($dirname);

		unlink($dirname);
	}
	/*
*生成电子口岸订单报文东站
*
*
**/
public function DorderPushOp() {


$dkj = Logic('kuajing');
		$order_id = $_GET['order_id'];
		$payment_code = Model()->table('order')->getfby_order_id($order_id,'payment_code');
		//if($payment_code=='alipay'){
         //   $result = $dkj->DorderJM78_ali($order_id);
       // }else {
		//$result = $dkj->DorderJM78($order_id);
		$result = $dkj->DorderJM78_new($order_id);
       // }
$appTime = date("YmdHis",time());
		$sql = 'SELECT order_sn FROM `718shop_order` where order_id='.$order_id.' LIMIT 1';
	$itemNoArr = Model()->query($sql);
	$orderNo    = $itemNoArr[0]['order_sn'];

$filename = $appTime."_".$orderNo.".xml";
//在浏览器下载生成的xml
header("Content-Type: application/octet-stream");
header('Content-Disposition: attachment; filename="' .  $filename . '"');
print_r($result);

}
/*
*生成电子口岸订单报文机场
*
*
**/
public function DorderPush2Op() {


$dkj = Logic('kuajing');
		$order_id = $_GET['order_id'];
		$payment_code = Model()->table('order')->getfby_order_id($order_id,'payment_code');
		//if($payment_code=='alipay'){
         //   $result = $dkj->DorderJM78_ali($order_id);
       // }else {
		//$result = $dkj->DorderJM78($order_id);
		$result = $dkj->DorderJM78_new1($order_id);
       // }
$appTime = date("YmdHis",time());
		$sql = 'SELECT order_sn FROM `718shop_order` where order_id='.$order_id.' LIMIT 1';
	$itemNoArr = Model()->query($sql);
	$orderNo    = $itemNoArr[0]['order_sn'];

$filename = $appTime."_".$orderNo.".xml";
//在浏览器下载生成的xml
header("Content-Type: application/octet-stream");
header('Content-Disposition: attachment; filename="' .  $filename . '"');
print_r($result);

}


/*
*生成电子口岸清单报文（东站）
*
*
**/
public function DlistPushOp() {


$dkj = Logic('kuajing');
		$order_id = $_GET['order_id'];
    $payment_code = Model()->table('order')->getfby_order_id($order_id,'payment_code');
   // if($payment_code=='alipay'){
    //    $result = $dkj->DlistJM78_ali($order_id);
   // }else {
		//$result = $dkj->DlistJM78($order_id);
		$result = $dkj->DlistJM78_new($order_id);
   // }
$appTime = date("YmdHis",time());
		$sql = 'SELECT order_sn FROM `718shop_order` where order_id='.$order_id.' LIMIT 1';
	$itemNoArr = Model()->query($sql);
	$orderNo    = $itemNoArr[0]['order_sn'];

$filename = "QD".$appTime."_".$orderNo.".xml";
//在浏览器下载生成的xml
header("Content-Type: application/octet-stream");
header('Content-Disposition: attachment; filename="' .  $filename . '"');
print_r($result);

}

/*
*生成电子口岸清单报文（机场）
*
*
**/
public function DlistPush2Op() {


$dkj = Logic('kuajing');
		$order_id = $_GET['order_id'];
    $payment_code = Model()->table('order')->getfby_order_id($order_id,'payment_code');
   // if($payment_code=='alipay'){
    //    $result = $dkj->DlistJM78_ali($order_id);
   // }else {
		//$result = $dkj->DlistJM78($order_id);
		$result = $dkj->DlistJM78_new1($order_id);
   // }
$appTime = date("YmdHis",time());
		$sql = 'SELECT order_sn FROM `718shop_order` where order_id='.$order_id.' LIMIT 1';
	$itemNoArr = Model()->query($sql);
	$orderNo    = $itemNoArr[0]['order_sn'];

$filename = "QD".$appTime."_".$orderNo.".xml";
//在浏览器下载生成的xml
header("Content-Type: application/octet-stream");
header('Content-Disposition: attachment; filename="' .  $filename . '"');
print_r($result);

}

/*
*获取申通热敏单号
*
*
**/
public function StoNumGetOp() {


$dkj = Logic('kuajing');
$result = $dkj->sto_number_get();
// print_r($result);die;			
$xml = simplexml_load_string($result);
$Result = $xml->Result;//在做数据比较时，注意要先强制转换

//$CheckData = (string) $Result->CheckData;
//$orderNo
$Remark1      = iconv("UTF-8", "GBK//IGNORE", "备注    ：").iconv("UTF-8", "GBK//IGNORE", (string) $Result->Remark);
$Status      = iconv("UTF-8", "GBK//IGNORE", "推送状态：").iconv("UTF-8", "GBK//IGNORE", (string) $Result->Status);
$Remark      = iconv("UTF-8", "GBK//IGNORE", "开始单号   ：").iconv("UTF-8", "GBK//IGNORE", (string) $Result->StartNumber);
$logisticsNo = iconv("UTF-8", "GBK//IGNORE", "结束单号：").iconv("UTF-8", "GBK//IGNORE", (string) $Result->EndNumber);
$Remark2      = iconv("UTF-8", "GBK//IGNORE", "最后sucess号    ：").iconv("UTF-8", "GBK//IGNORE", (string) $Result->SuccessSn);

print_r($Status);
echo "<br>";
print_r($Remark);
echo "<br>";
print_r($logisticsNo);
echo "<br>";
print_r($Remark1);
echo "<br>";
print_r($Remark2);

// print_r($result);
}


/*
*获取申通运单推送
*
*
**/
public function STOPushOp() {

        $data = array();
        $data['reciver_totalLogisticsNo'] = $_POST['reciver_totalLogisticsNo'];
        $data['reciver_jcbOrderTime'] = $_POST['reciver_jcbOrderTime'];
        $data['reciver_jcbOrderPort'] = $_POST['reciver_jcbOrderPort'];
        $data['reciver_jcbOrderPortInsp'] = $_POST['reciver_jcbOrderPortInsp'];
        

$dkj = Logic('kuajing');
//$result = $dkj->sto_baowen_JM(); //原始报文
$result = $dkj->sto_baowen_Push($data);
//return $result;
//print_r($result);

$xml = simplexml_load_string($result);
$Result = $xml->Result;//在做数据比较时，注意要先强制转换

//$CheckData = (string) $Result->CheckData;
//$orderNo

$Status      = iconv("UTF-8", "GBK//IGNORE", "推送状态：").iconv("UTF-8", "GBK//IGNORE", (string) $Result->Status);
$Remark      = iconv("UTF-8", "GBK//IGNORE", "备注    ：").iconv("UTF-8", "GBK//IGNORE", (string) $Result->Remark);
$logisticsNo = iconv("UTF-8", "GBK//IGNORE", "申通单号：").iconv("UTF-8", "GBK//IGNORE", (string) $Result->logisticsNo);

print_r($Status);
echo "<br>";
print_r($Remark);
echo "<br>";
print_r($logisticsNo);

}
/*
	 * 获取圆通运单推送
	 *
	 */
	public function YTOPushOp()
    {

        $data = array();
        $data['reciver_totalLogisticsNo'] = $_POST['reciver_totalLogisticsNo'];
        $data['reciver_jcbOrderTime'] = $_POST['reciver_jcbOrderTime'];
        $data['reciver_jcbOrderPort'] = $_POST['reciver_jcbOrderPort'];
        $data['reciver_jcbOrderPortInsp'] = $_POST['reciver_jcbOrderPortInsp'];


        $dkj = Logic('kuajing');
        $result = $dkj->yto_baowen_Push($data);

        $xml = simplexml_load_string($result);
        $header = $xml->{'Header'};
        $responseOrder = $xml->{'ResponseOrder'};

        $Status = "推送状态：" . $header->success;
        $Remark = "备注    ：" . $responseOrder->ErrMsg;
        $logisticsNo = "订单号：" . $responseOrder->OrderID;

        print_r($Status);
        echo "<br>";
        print_r($logisticsNo);
        echo "<br>";
        print_r($Remark);
    }				
    public function yto_trackingInfoOp()
    {
        $dkj = Logic('kuajing');
        $result = $dkj->yto_trackingInfo();
        return $result;
	}


	/**
     *获取st运单号(sdddw)
     * @param  string $order_sn [订单号]
     * @return  string 
     */
	
	public function WaybillNoOp(){
			// var_dump($res2);die;
		// $order_info = order_info($order_sn);

		//热敏单号获取(VIP0009)

		//请求的方法名
		$code = 'vip0009';
		//方法签名(申通总部提供)
		$data_digest = 'fd5a95446d2c1c394359afbcfe9e7a0d';
		//客户密码
		$cuspwd = 'Sto331155.';
		//客户名称
		$cusname = 'VIP易境通';
		//网点名称
		$cusite = '北京顺义林河公司';
		//个数
		$len  = 1;
		// $len = '&len='.$len;
        //测试
		//请求的方法名
		// $code = 'vip0009';
		// //方法签名(申通总部提供)
		// $data_digest = 'ec30c4dd6d04325b72688384753c2952';
		// //客户密码
		// $cuspwd = 'limx_1234';
		// //客户名称
		// $cusname = '东商	';
		// //网点名称
		// $cusite = '上海陈行公司';
		// //个数
		// $len  = 1;

		// //测试链接
		// $url = 'http://222.72.44.130:3111/sto_vipFacade/PreviewInterfaceAction.action';
 		$url = "http://vip.sto.cn/PreviewInterfaceAction.action";//zhengshi

 		// $sa = new sha();
		$post = array('code'=>$code,'data_digest'=>$data_digest,'cuspwd'=>$cuspwd,'cusname'=>$cusname,'cusite'=>$cusite,'len'=>$len);
		$res =$this->Post_curls($url, $post);
	 
		//更新数据表中运单号的值
		$res1 = json_decode($res,true);
		$res2 = $res1['data'];
		// var_dump($res2);die;
		$model_order = Model('order');
		$data = array();
		$condition = array();
		$condition['order_id'] = intval($_GET['order_id']);
		$order_common_info = $model_order->getOrderCommonInfo($condition);
		$order_common_info_un = unserialize($order_common_info['waybill_info']);
		$reciver_info = array(
			'transTool' =>$order_common_info_un['transTool'],
				'transType' => $order_common_info_un['transType'],
				'voyageNo' => $order_common_info_un['voyageNo'],
				'totalLogisticsNo' => $order_common_info_un['totalLogisticsNo'],
				'logisticsNo' => $res2,
				'jcbOrderTime' => $order_common_info_un['jcbOrderTime'],
				'jcbOrderPort' => $order_common_info_un['jcbOrderPort'],
				'jcbOrderPortInsp' => $order_common_info_un['jcbOrderPortInsp'],
				'shortaddress' => $shortaddress,
				'packagecentercode' => $packagecentercode,
				
		);
		$data['waybill_info'] = serialize($reciver_info);
		$result = $model_order->editOrderCommon($data, $condition);
		if($result) {
			if(!empty($res2)){
				echo 'true';
				echo "<br>";
				echo $res2;
			}else{
				echo 'false';
				echo "<br>";
				echo $reason;
			}
		} else {
			echo 'false';
		}
	
		
   	 // return $res2;
	}
	/**
     * POST请求http接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $post [请求的参数]
     * @return  string
     */
    public function Post_curls($url, $post)
    {
       
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $res = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $res; // 返回数据，json格式
 
    }

   
	
	/**
     *获取三段码相关信息存储到ordercommon表中的waybill_info']['shortaddress'];
     * @param  string $order_sn [订单号]
     * @return  string 
     */
	
	public function sanduanmaOp(){
			

		//请求的方法名三段码
		$code = 'vip0007';
		//方法签名(申通总部提供)
		// $data_digest = 'ec30c4dd6d04325b72688384753c2952';
		$data_digest ='b2a2a03a33f3e9ebeb40e36e376112a9';//zhegnshi
		//客户密码
		$cuspwd = 'Sto@2018';
		// $cuspwd ='limx_1234';
		//客户名称
		// $cusname = '东商';
		// //网点名称
		// $cusite = '上海陈行公司';
		// //个数
		
		// //测试链接
		// $url = 'http://222.72.44.130:3111/sto_vipFacade/PreviewInterfaceAction.action';
		$url ='http://vip.sto.cn/PreviewInterfaceAction.action';
        // $shopId=$_SESSION['store_id'];
        // $storeInfo=$this->getStoreInfo($shopId);
		$model_order = Model('order');
		$model_country = Model('kuajing_country');

		$condition = array();
        $order_id = $_GET['order_id'];
		$condition['order_id'] = $order_id;

		$order_list = $model_order->getOrderList($condition, 5, '*', 'order_id desc', '', array('order_goods', 'order_common', 'member', 'goods_kuajing_d'));
	//订单基本信息

	 //网点编码（必填，string64）
	//$siteCode = "000057";  
	$siteCode = "100512"; 
	//网点名称（非必填，string64）  
	$sendsite = "河南省市场部六部";    
	//客户名称
	$sendcus ="中陆进出口贸易";
	//交易订单号（必填，string64）
	$orderno = $order_list[$order_id]['order_sn'];
	//寄件日期（必填系统当前日期）
	$senddate = date("Y-m-d");
	//运单号（必填，string64）    
	$billno = $order_list[$order_id]['extend_order_common']['waybill_info']['logisticsNo'];
	//寄件人
	$model_order = Model('order');
     $goods_list = $model_order->getOrderGoodsList(array('order_id'=>$order_id));
	 foreach ($goods_list as $value) {
	$goodsname = $value['goods_name'];
    } 
	// $waybillNo =5530091783101;
	 // echo '订单号:'.$tradeNo.'运单号:'.$waybillNo;die;
	//l录入时间（必填，string64 yyyy-MM-dd HHmmss） 
	$inputdate = date("Y-m-d"); 
	$inputperson	=  "中陆进出口贸易";
	$inputsite      =   "河南省市场部六部";
    $lasteditdate   = "";
    $lasteditperson = "";
    $lasteditsite   = "";
    $remark         =   "";
    $weight         ="";
    $productcode    ="";
    $bigchar   ="";
	//合作伙伴编码（必填，string64  申通提供）  
	
      // var_dump($goods_list);
    // foreach ($goods_list as $value) {
    // 	$value['kuajing_info'] = @unserialize($value['kuajing_info']);
    // $itemQuantity = $value['goods_num'];
    $daddress_id=$order_list[$order_id]['extend_order_common']['daddress_id'];
	$model_daddress=Model('daddress');
	//发件人名字
    $sendperson = $model_daddress->getfby_address_id($daddress_id, 'seller_name');
	//发件人信息
	$address =$model_daddress->getfby_address_id($daddress_id, 'address') ;
	// 发件人电话
	$sendtel = $model_daddress->getfby_address_id($daddress_id, 'telphone') ;
     $area_info = $model_daddress->getfby_address_id($daddress_id, 'area_info') ;
     // var_dump($area_info);die;
		$return = preg_replace('#\s+#', ' ',trim($area_info));
		$str_reverse=explode(" ",$return);
		if(!empty($str_reverse)){
			// $str_reverse=array_reverse($arr_str);
	//发件人省份（必填，string50）
	$sendprovince = $str_reverse[0];   
	 //发件人城市（必填，string50）
	$sendcity = $str_reverse[1];  
	//发件人地区（必填，string50） 
	$sendarea = $str_reverse[2];
    }
	//发件人地址（必填，string200）
	$sendaddress=$sendprovince.$sendcity.$senderarea.$address;  
	$daddress_id=$order_list[$order_id]['extend_order_common']['daddress_id'];
	$model_daddress=Model('daddress');
	//发件人邮编（必填，string20）  
	$senderPostcode = $model_daddress->getfby_address_id($daddress_id, 'seller_zipcode');
	// $senderPostcode = 111111;
   $receivecus =  "";
   $sendpcode    ="";
   $sendccode="";
   $sendacode="";
   $receivepcode="";
   $receiveccode="";
   $receiveacode="";
	//收件人信息
     //收件人地址
		$consigneeAddress = $order_list[$order_id]['extend_order_common']['reciver_info']['address'];
		// var_dump($consigneeAddress);die;
		//物流订单号
		$return = preg_replace('#\s+#', ' ',trim($consigneeAddress));
        $arr_str=explode(" ",$return);
		if(!empty($arr_str)){
	//收件人省份（必填，string50）
	$receiveprovince = $arr_str[0];    
	 //收件人城市（必填，string50）
	$receivecity = $arr_str[1];   
	//收件人地区（必填，string50）
	$receivearea = $arr_str[2];
    }
	//收件人地址（必填，string200）
	$receiveaddress=$consigneeAddress;
	//$receiverAddress = $order_info['address'];    
	//收件人手机号（必填，string20）
	$receivetel =  substr($order_list[$order_id]['extend_order_common']['reciver_info']['phone'],0,11);
	//收件人姓名（必填，string50）   
	$receiveperson =  $order_list[$order_id]['extend_order_common']['reciver_name'];  
	//创建拓展属性的数组值
	
	$otherInfo = compact("appType","appStatus","billNo","freight","insuredFee","goodsInfo","packNo","currency","note");
	$items = compact("otherInfo");
	//创建整体传值数组
 	 $array_way1 = compact("billno","senddate","sendsite","sendcus","sendperson","sendtel","receivecus","receiveperson","receivetel","goodsname","inputdate","inputperson","inputsite","lasteditdate","lasteditperson","lasteditsite","remark","receiveprovince","receivecity","receivearea","receiveaddress","sendprovince","sendcity","sendarea","sendaddress","weight","productcode","sendpcode","sendccode","sendacode","receivepcode","receiveccode","receiveacode","bigchar","orderno");
 	 // print_r($array_way1);die;
 	foreach ( $array_way1 as $key => $v ) { 
 		if( $key!=="items"){
 		$array_way1[$key] = @urlencode ( $v ); 
 		}
   	else{
   		$array_way1[$key] = $v;
  	 }
	 }

	$logisticsInfo = json_encode($array_way1);
	
	$logisticsInfo = urldecode($logisticsInfo);	
	$logisticsInfo ="[$logisticsInfo]";
		$post = array('code'=>$code,'data_digest'=>$data_digest,'cuspwd'=>$cuspwd,'data'=>$logisticsInfo);
		$res =$this->Post_curls($url, $post);
	 
		//更新数据表中运单号的值
		$res1 = json_decode($res,true);
		
		// var_dump($res1);die;
		if($res1['success'] == ture) {
			$res2 = $res1['data'][0]['bigchar'];
		// 	if(!empty($res2)){
				echo '获取成功';
				echo "<br>";
				echo $res2;
		// var_dump($data['bigchar']);die;
		// var_dump($res2);die;
				// $res2='761 X03 000';
		$model_order = Model('order');
		$data = array();
		$condition = array();
		$condition['order_id'] = intval($_GET['order_id']);
		$order_common_info = $model_order->getOrderCommonInfo($condition);
		$order_common_info_un = unserialize($order_common_info['waybill_info']);
		// var_dump($order_common_info_un);die;
		$reciver_info = array(
			'transTool' =>$order_common_info_un['transTool'],
				'transType' => $order_common_info_un['transType'],
				'voyageNo' => $order_common_info_un['voyageNo'],
				'totalLogisticsNo' => $order_common_info_un['totalLogisticsNo'],
				'logisticsNo' => $order_common_info_un['logisticsNo'],
				'jcbOrderTime' => $order_common_info_un['jcbOrderTime'],
				'jcbOrderPort' => $order_common_info_un['jcbOrderPort'],
				'jcbOrderPortInsp' => $order_common_info_un['jcbOrderPortInsp'],
				'shortaddress' => $res2,
				'packagecentercode' => $packagecentercode,
				
		);
		$data['waybill_info'] = serialize($reciver_info);
		$result = $model_order->editOrderCommon($data, $condition);
		
		// 	}else{
		// 		echo 'false';
		// 		echo "<br>";
		// 		echo $reason;
		// 	}
		} else {
			$res2 =$res1['data'];
			echo '获取失败';
			echo "<br>";
			print_r($res2) ;
		}
	
		
   	 // return $res2;
	}

	/*
	 * 获取圆通电子运单号
	 * 运单号需要存到数据库
	 */
	public function YTOLogisticsNoOp(){
		$dkj = Logic('kuajing');
		$result = $dkj->yto_logistics_No();
		$mail_no=substr($this->getNeedBetween($result,'<mailNo>','</mailNo>'),6);
		$shortaddress=substr($this->getNeedBetween($result,'<shortaddress>','</shortaddress>'),12);
		$packagecentercode=substr($this->getNeedBetween($result,'<packagecentercode>','</packagecentercode>'),17);
		$reason=substr($this->getNeedBetween($result,'<reason>','</reason>'),6);
		$model_order = Model('order');
		$data = array();
		$condition = array();
		$condition['order_id'] = intval($_GET['order_id']);
		$order_common_info = $model_order->getOrderCommonInfo($condition);
		$order_common_info_un = unserialize($order_common_info['waybill_info']);
		$reciver_info = array(
				'transTool' =>$order_common_info_un['transTool'],
				'transType' => $order_common_info_un['transType'],
				'voyageNo' => $order_common_info_un['voyageNo'],
				'totalLogisticsNo' => $order_common_info_un['totalLogisticsNo'],
				'logisticsNo' => $mail_no,
				'jcbOrderTime' => $order_common_info_un['jcbOrderTime'],
				'jcbOrderPort' => $order_common_info_un['jcbOrderPort'],
				'jcbOrderPortInsp' => $order_common_info_un['jcbOrderPortInsp'],
				'shortaddress' => $shortaddress,
				'packagecentercode' => $packagecentercode
		);
		$data['waybill_info'] = serialize($reciver_info);
		$result = $model_order->editOrderCommon($data, $condition);
		if($result) {
			if(!empty($mail_no)){
				echo 'true';
				echo "<br>";
				echo $mail_no;
			}else{
				echo 'false';
				echo "<br>";
				echo $reason;
			}
		} else {
			echo 'false';
		}
	}
	function getNeedBetween($kw1,$mark1,$mark2){
		$kw=$kw1;
		$kw='123'.$kw.'123';
		$st =stripos($kw,$mark1);
		$ed =stripos($kw,$mark2);
		if(($st==false||$ed==false)||$st>=$ed)
			return 0;
		$kw=substr($kw,($st+2),($ed-$st-2));
		return $kw;
	}

    //xinzeng11.13
    //圆通运单推送测试
	public function testyd_parameter_editOp(){
		Language::read('member_member_index');
        $lang   = Language::getLangContent();
        $model_order = Model('order');
		$order_id = intval($_GET['order_id']);
	   // if ($order_id <= 0) return false;
		$condition = array();
		$condition['order_id'] = $order_id;
		$condition['store_id'] = $_SESSION['store_id'];
		$order_common_info = $model_order->getOrderCommonInfo($condition);
        if (!$order_common_info) return false;
        $order_common_info['waybill_info'] = @unserialize($order_common_info['waybill_info']);
        $order_info = $model_order->getOrderInfo($condition,array('order_common','order_goods'));
        // $BillID=$address_info['waybill_info']['logisticsNo'];
        // $OrderID=$order_info['order_sn'];
		Tpl::output('address_info',$order_common_info);
		Tpl::output('order_info',$order_info);

		Tpl::showpage('store_deliver.testyd_parameter.send','null_layout');

	}

	public function testyd_parameter_sendOp(){

        $order_id = intval($_GET['order_id']);
        $model_order = Model('order');

        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['store_id'] = $_SESSION['store_id'];
        $order_common_info = $model_order->getOrderCommonInfo($condition);
        if (!$order_common_info) return false;
        $order_common_info['waybill_info'] = @unserialize($order_common_info['waybill_info']);
        $order_info = $model_order->getOrderInfo($condition,array('order_common','order_goods'));
        $BillID=$order_common_info['waybill_info']['logisticsNo'];
        $OrderID=$order_info['order_sn'];

        $data = array();
        $data['store_id']= $_SESSION['store_id'];
//        $data['order_id'] = $order_id;
	    $data['BillID'] =  $BillID;//上下游运单号写一样的***********************
	    $data['OrderID'] = $BillID;//上下游运单号写一样的***********************
	    $data['DepName'] = $_POST['DepName'];
	    if(in_array($_POST['StatusCode'],array('P767','P760','P765','P769','P762','P764','P771','P773'))){
            $data['StatusCode'] = $_POST['StatusCode'];
        }
        switch ( $data['StatusCode'] ) {
    case  'P767' :
        $data['StatusDesc'] ='海外派件中';
        break;
    case  'P760' :
        $data['StatusDesc'] ='海外收入' ;
        break;
    case  'P765' :
        $data['StatusDesc'] ='海外清关完成' ;
        break;
    case  'P769' :
        $data['StatusDesc'] ='海外签收' ;
        break;
    case  'P762' :
        $data['StatusDesc'] ='海外发运' ;
        break;
    case  'P764' :
        $data['StatusDesc'] ='海外清关中' ;
        break;
    case  'P771' :
        $data['StatusDesc'] ='航班已起飞' ;
        break;
    case  'P773' :
        $data['StatusDesc'] ='航班抵达保税区' ;
        break;
}

        if(in_array($_POST['FacilityType'],array('网点','中转中心','分拨中心'))){
        	$data['FacilityType'] = $_POST['FacilityType'];
        }
        $data['Contacter']=$_POST['Contacter'];
        $data['ContactInfo']=$_POST['ContactInfo'];

        $dkj = Logic('kuajing');
        $result = $dkj->yto_trackingInfo($data);
//        var_dump($result);
        $errcode = substr($this->getNeedBetween($result, '<errcode>', '</errcode>'), 7);//echo '<br/>';echo $errcode;
        $errmsg = substr($this->getNeedBetween($result, '<errmsg>', '</errmsg>'), 6);//echo '<br/>';echo $errmsg;
//        die;
//        return $result;
        echo $OrderID.'<br/>'.$errcode.'<br/>'.$errmsg.'<br/>';
    }
    // xinzeng end

	/**
	 * 编辑运单推送报文
	 * @return boolean
	 */
	public function STO_parameter_editOp() {
	    $order_id = intval($_GET['order_id']);
	    if ($order_id <= 0) return false;
	    $model_order = Model('order');
		$condition = array();
		$condition['order_id'] = $order_id;
		$condition['store_id'] = $_SESSION['store_id'];
		$order_common_info = $model_order->getOrderCommonInfo($condition);
        if (!$order_common_info) return false;
        $order_common_info['waybill_info'] = @unserialize($order_common_info['waybill_info']);
		Tpl::output('address_info',$order_common_info);

		//PC::debug($order_common_info);

		//抛出运输方式变量
        $model_trans_type = Model('kuajing_trans_type');
        $kuajing_trans_type= $model_trans_type->select();
        Tpl::output('kuajing_trans_type', $kuajing_trans_type);

        $str_order_id = $_POST['str_order_id'];
        $arr_order_id = explode(',',$str_order_id);

        //抛出运输工具变量
        $model_trans_tool = Model('kuajing_trans_tool');
        $kuajing_trans_tool= $model_trans_tool->select();
        Tpl::output('kuajing_trans_tool', $kuajing_trans_tool);
        Tpl::output('arr_order_id',$arr_order_id);
        Tpl::output('str_order_id',$str_order_id);

		Tpl::showpage('store_deliver.sto_parameter.edit','null_layout');
	}

/**
     * 收货地址保存
     */
    public function STO_parameter_saveOp() {
        $model_order = Model('order');
        $data = array();
        //$data['reciver_name'] = $_POST['reciver_name'];
        $data['waybill_info'] = $this->_get_waybill_info();
        $condition = array();
        $condition['order_id'] = intval($_POST['order_id']);
        $condition['store_id'] = $_SESSION['store_id'];
        $result = $model_order->editOrderCommon($data, $condition);
        if($result) {
            echo 'true';
        } else {
            echo 'flase';
        }
    }

    /*
     *跨境运单信息---总单号、航班航次的保存
     */
    public function kuajing_info_saveOp(){
    	$model=Model('order');
        $str_order_id=$_POST['str_order_id'];
        $arr_order_id=explode(',',$str_order_id);
        $param=array();
        $con=array();
        $con['store_id']=$_SESSION['store_id'];
        foreach($arr_order_id as $order_id){
            $con['order_id']=$order_id;
            $waybill_info = $model->table('order_common')->where($con)->limit(1)->find();
            $waybill_info=unserialize($waybill_info['waybill_info']);
            $waybill_info['transTool'] = $waybill_info['transTool'];
            $waybill_info['transType'] = $waybill_info['transType'];
            $waybill_info['voyageNo']=$_POST['reciver_voyageNo'];
            $waybill_info['totalLogisticsNo']=$_POST['reciver_totalLogisticsNo'];
            $waybill_info['logisticsNo'] = $waybill_info['logisticsNo'];
            $waybill_info['jcbOrderTime'] = $_POST['reciver_jcbOrderTime'];
            $waybill_info['jcbOrderPort'] = $waybill_info['jcbOrderPort'];
            $waybill_info['jcbOrderPortInsp'] = $waybill_info['jcbOrderPortInsp'];
            $waybill_info['shortaddress'] = $waybill_info['shortaddress'];
            $waybill_info['packagecentercode'] = $waybill_info['packagecentercode'];
            $param['waybill_info']=serialize($waybill_info);
            $result=$model->editOrderCommon($param,$con);
            if($result)  echo 'true';
            else { echo 'flase';}
        }
    }

 /**
     * 组合reciver_info
     */
    private function _get_waybill_info() {
        $reciver_info = array(
            'transTool' => $_POST['reciver_transTool'],
            'transType' => $_POST['reciver_transType'],
            'voyageNo' => $_POST['reciver_voyageNo'],
            'totalLogisticsNo' => $_POST['reciver_totalLogisticsNo'],
            'logisticsNo' => $_POST['reciver_logisticsNo'],
            'jcbOrderTime' => $_POST['reciver_jcbOrderTime'],
            'jcbOrderPort' => $_POST['reciver_jcbOrderPort'],
            'jcbOrderPortInsp' => $_POST['reciver_jcbOrderPortInsp']
        );
        return serialize($reciver_info);
    }
    //申通
    //打印面单
    public function STOPrintOp(){
        $order_list = array();
        $kuajing_info = array();
        $goods_all_number = array();
        $goods_all_quantity = array();
        $qrcode = array();
        $store_id = $_SESSION['store_id'];
        $id_array = explode(",", $_GET['order_id']);
        array_pop($id_array);//数组最后一个元素是空，删掉最后一个元素再进行循环
        if (is_array($id_array)) {
            foreach ($id_array as $key => $value) {
                $order_id	= intval($value);
                if ($order_id <= 0){
                    showMessage(Language::get('wrong_argument'),'','html','error');
                }
                $order_model = Model('order');
                $condition['order_id'] = $order_id;
                $condition['store_id'] = $store_id;
                $order_info = $order_model->getOrderInfo($condition,array('order_common','order_goods'));
                if (empty($order_info)){
                    showMessage(Language::get('member_printorder_ordererror'),'','html','error');
                }

                QRcode::png($order_info['extend_order_common']['waybill_info']['shortaddress'], $order_id.'.png', 'L', '3', 2);
                $filesize = @getimagesize($order_id.'.png');
                if($filesize){
                    $qrcode[] = $order_id.'.png';
                }else{
                    $qrcode[]= 0;
                }

                $kuajing_info = $order_info['extend_order_goods'][0]['kuajing_info'];
                $shipper_info[0] = unserialize($kuajing_info);
                $shipping[] = $shipper_info[0];

                $order_list[] = $order_info;

                $condition = array();
                $condition['order_id'] = $order_id;
                $condition['store_id'] = $store_id;
                $goods_all_num = 0;
                $goods_total_quantity = 0;
                if (!empty($order_info['extend_order_goods'])){
                    $i = 1;
                    foreach ($order_info['extend_order_goods'] as $k => $v){
                        $goods_id = $v['goods_id'];
                        $sql = 'SELECT goods_kuajingD_id FROM `718shop_goods` where goods_id=' . $goods_id . ' LIMIT 1';
                        $goods_kuajingD_idArr = Model()->query($sql);
                        $goods_kuajingD_id = $goods_kuajingD_idArr[0]['goods_kuajingD_id'];
                        $sql = 'SELECT * FROM `718shop_goods_kuajing_d` where id=' . $goods_kuajingD_id . ' LIMIT 1';
                        $kuajingArr = Model()->query($sql);
                        $v['goods_name'] = str_cut($v['goods_name'],100);
                        $goods_all_num += $v['goods_num'];
                        $goods_total_quantity += $v['goods_num']* $kuajingArr[0]['gross_weight'];
                        $i++;
                    }
                    $goods_all_number[] = $goods_all_num;
                    $goods_all_quantity[] = $goods_total_quantity;
                }

            }

        }
         $numbers = count($order_list);
        Tpl::output('numbers',$numbers);
        Tpl::output('order_list',$order_list);
//        Tpl::output('shippingArr',$shippingArr);
        Tpl::output('shippingArr', $shipping);
        Tpl::output('qrcode',$qrcode);
        Tpl::output('goods_all_number',$goods_all_number);
        Tpl::output('goods_all_quantity',$goods_all_quantity);
        Tpl::showpage('store_sto.print',"null_layout");
    }
   //
   //
   //
    

    //圆通面单打印    
	public function YTOPrintOp(){
        $order_list = array();
        $kuajing_info = array();
        $goods_all_number = array();
        $goods_all_quantity = array();
        $qrcode = array();
        $store_id = $_SESSION['store_id'];
        $id_array = explode(",", $_GET['order_id']);
        array_pop($id_array);//数组最后一个元素是空，删掉最后一个元素再进行循环
        if (is_array($id_array)) {
            foreach ($id_array as $key => $value) {
                $order_id	= intval($value);
                if ($order_id <= 0){
                    showMessage(Language::get('wrong_argument'),'','html','error');
                }
                $order_model = Model('order');
                $condition['order_id'] = $order_id;
                $condition['store_id'] = $store_id;
                $order_info = $order_model->getOrderInfo($condition,array('order_common','order_goods'));
                if (empty($order_info)){
                    showMessage(Language::get('member_printorder_ordererror'),'','html','error');
                }

                QRcode::png($order_info['extend_order_common']['waybill_info']['shortaddress'], $order_id.'.png', 'L', '3', 2);
                $filesize = @getimagesize($order_id.'.png');
                if($filesize){
                    $qrcode[] = $order_id.'.png';
                }else{
                    $qrcode[]= 0;
                }

                $kuajing_info = $order_info['extend_order_goods'][0]['kuajing_info'];
                $shipper_info[0] = unserialize($kuajing_info);
                $shipping[] = $shipper_info[0];

                $order_list[] = $order_info;

                $condition = array();
                $condition['order_id'] = $order_id;
                $condition['store_id'] = $store_id;
                $goods_all_num = 0;
                $goods_total_quantity = 0;
                if (!empty($order_info['extend_order_goods'])){
                    $i = 1;
                    foreach ($order_info['extend_order_goods'] as $k => $v){
                        $goods_id = $v['goods_id'];
                        $sql = 'SELECT goods_kuajingD_id FROM `718shop_goods` where goods_id=' . $goods_id . ' LIMIT 1';
                        $goods_kuajingD_idArr = Model()->query($sql);
                        $goods_kuajingD_id = $goods_kuajingD_idArr[0]['goods_kuajingD_id'];
                        $sql = 'SELECT * FROM `718shop_goods_kuajing_d` where id=' . $goods_kuajingD_id . ' LIMIT 1';
                        $kuajingArr = Model()->query($sql);
                        $v['goods_name'] = str_cut($v['goods_name'],100);
                        $goods_all_num += $v['goods_num'];
                        $goods_total_quantity += $v['goods_num']* $kuajingArr[0]['gross_weight'];
                        $i++;
                    }
                    $goods_all_number[] = $goods_all_num;
                    $goods_all_quantity[] = $goods_total_quantity;
                }

            }

        }

        $numbers = count($order_list);
        Tpl::output('numbers',$numbers);
        Tpl::output('order_list',$order_list);
//        Tpl::output('shippingArr',$shippingArr);
        Tpl::output('shippingArr', $shipping);
        Tpl::output('qrcode',$qrcode);
        Tpl::output('goods_all_number',$goods_all_number);
        Tpl::output('goods_all_quantity',$goods_all_quantity);
        Tpl::showpage('store_yto.print',"null_layout");

	}
	
	public function deliver_explainsaveOp(){
        $deliver_explain = $_GET['deliver_explain'];
        $order_id = $_GET['order_id'];
        $model_order_common = Model('order');
        $result = $model_order_common->editOrderCommon(array('deliver_explain'=>$deliver_explain),array('order_id'=>$order_id));
        $data = array();
        $data['result'] = $result;
        exit(json_encode($data));
    }
    /*
*测试获取申通运单推送的内容
*
*
**/
public function STOPushtestOp() {

$dkj = Logic('kuajing');
//$result = $dkj->sto_baowen_JM(); //原始报文
$result = $dkj->sto_baowen_JM($data);
// //return $result;
// var_dump(expression)($result);die;
$declareDate = date("YmdHis",time());
		$sql = 'SELECT order_sn FROM `718shop_order` where order_id='.$order_id.' LIMIT 1';
	$itemNoArr = Model()->query($sql);
	$orderNo    = $itemNoArr[0]['order_sn'];

$filename = "QD".$declareDate."_".$orderNo.".xml";
//在浏览器下载生成的xml
header("Content-Type: application/octet-stream");
header('Content-Disposition: attachment; filename="' .  $filename . '"');
print_r($result);
}
/**
	 * 编辑运单推送报文
	 * @return boolean
	 */
	public function STO_parameter_edit2Op() {
	    $order_id = intval($_GET['order_id']);
	    $goods_id = intval($_GET['goods_id']);
	    // var_dump($goods_id);die;
	    if ($order_id <= 0) return false;
	    $model_order = Model('order');
		$condition = array();
		$condition['order_id'] = $order_id;
		$condition['goods_id'] = $goods_id;
		 // $where = array('order_id' => array('in', $order_id_array), 'store_id' => $_SESSION['store_id']);
		 // var_dump($where);
		  $order_goods_list = Model('order')->getOrderGoodsList($condition);
            foreach ($order_goods_list as $value) {
                $kuajing_info = unserialize($value['kuajing_info']);
                $goods_info=$value;
            }
            // var_dump($kuajing_info);die;
		// $order_common_info = $model_order->getOrderCommonInfo($condition);
  //       if (!$order_common_info) return false;
  //       $order_common_info['waybill_info'] = @unserialize($order_common_info['waybill_info']);
		Tpl::output('address_info',$kuajing_info);
		Tpl::output('goods_info',$goods_info);

		//PC::debug($order_common_info);

		//抛出运输方式变量
        $model_trans_type = Model('kuajing_trans_type');
        $kuajing_trans_type= $model_trans_type->select();
        Tpl::output('kuajing_trans_type', $kuajing_trans_type);

        $str_order_id = $_POST['str_order_id'];
        $arr_order_id = explode(',',$str_order_id);

        //抛出运输工具变量
        $model_trans_tool = Model('kuajing_trans_tool');
        $kuajing_trans_tool= $model_trans_tool->select();
        Tpl::output('kuajing_trans_tool', $kuajing_trans_tool);
        Tpl::output('arr_order_id',$arr_order_id);
        Tpl::output('str_order_id',$str_order_id);

		Tpl::showpage('store_deliver.kuajing_goods.edit','null_layout');
	}

/**
     * 收货地址保存
     */
    public function STO_parameter_save2Op() {
        $model_order = Model('order');
        $data = array();
        // echo $_POST['reciver_totalLogisticsNo'];die;
        //var_dump($_POST['reciver_totalLogisticsNo']);die;
        //$data['reciver_name'] = $_POST['reciver_name'];
        // $data['waybill_info'] = $this->_get_kuajinggoods_info();
        //var_dump($data);die;

        $condition = array();
        $condition['rec_id'] = intval($_POST['rec_id']);
        // $condition['goods_id'] = $_GET['goods_id'];
        $order_goods_list = Model('order')->getOrderGoodsList($condition);
        // var_dump($order_goods_list);die;
            foreach ($order_goods_list as $value) {
                $kuajing_info = unserialize($value['kuajing_info']);
                 $kuajing_info['specifications'] = $_POST['reciver_totalLogisticsNo'];
                $kuajing_info['weight'] = $_POST['reciver_voyageNo'];
                $kuajing_info['net_weight'] = $_POST['reciver_logisticsNo'];
            $date = array('kuajing_info' => serialize($kuajing_info));
             // $condition['goods_id'] = $value['goods_id'];
             $result = $model_order->editOrderGoods($date, $condition);
            }
       
        if($result) {
            echo 'true';
        } else {
            echo 'flase';
        }
        // Tpl::showpage('store_deliver.kuajing_goods.edit','null_layout');
    }


}
