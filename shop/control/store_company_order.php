<?php
/**
 * 卖家实物订单管理
 *
 **/
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/IOFactory.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/Reader/Excel2007.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/Reader/IReader.php'; //引入文件

defined('In718Shop') or exit('Access Invalid!');
class store_company_orderControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct();
        Language::read('member_store_index');
    }

	/**
	 * 订单列表
	 *
	 */
	public function indexOp() {
        $member_id = $_SESSION['member_id'];
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($member_id,'company_id');
        $company_id = $member_info['company_id'];
        $model_ziti = Model('ziti_address');
        $condition_ziti = array();
        $ziti_list = $model_ziti->getAddressList($condition_ziti);
        Tpl::output('ziti_list',$ziti_list);
        if ($company_id == 0) {
            $order_list = 0;
            self::profile_menu('list',$_GET['state_type']);
            Tpl::showpage('store_company_order.index');
            die;
        }
        $model_order = Model('order');
        $condition = array();
        $condition['company_id'] = $company_id;
        $condition['order.store_id'] = $_SESSION['store_id'];
        if ($_GET['order_sn'] != '') {
            $condition['order.order_sn'] = $_GET['order_sn'];
        }
        if ($_GET['buyer_name'] != '') {
            $condition['order.buyer_name'] = $_GET['buyer_name'];
        }

        //发货人姓名 新增 11.1

        if($_GET['senderusername']!=''){
            $sql="SELECT * from `718shop_order_goods` where kuajing_info like '%".$_GET['senderusername']."%'";
            $kuajing_info=Model()->query($sql);
            $order_id=array();
            for($i=0;$i<count($kuajing_info);$i++){
                $order_id[$i]=$kuajing_info[$i]['order_id'];
            }

            $condition['order.order_id']=array('in',$order_id);
        }

	    if ($_GET['consignee_name'] != '') {
        $condition['order_common.reciver_name']=$_GET['consignee_name'];
        }

        if ($_GET['ziti_id'] != '') {
        $condition['order_common.reciver_ziti_id']=$_GET['ziti_id'];
        }

        if ($_GET['is_mode'] != '') {
            $condition['order.is_mode'] = $_GET['is_mode'];
        }
        $allow_state_array = array('state_new','state_pay','state_send','state_success','state_cancel');
        if (in_array($_GET['state_type'],$allow_state_array)) {
            $condition['order.order_state'] = str_replace($allow_state_array,
                    array(ORDER_STATE_NEW,ORDER_STATE_PAY,ORDER_STATE_SEND,ORDER_STATE_SUCCESS,ORDER_STATE_CANCEL), $_GET['state_type']);
        } else {
            $_GET['state_type'] = 'store_order';
        }
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['order.add_time'] = array('between',array($start_unixtime,$end_unixtime));
        }

        //提货时间
        $if_start_date_ziti = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$_GET['ziti_query_start_date']);
        $if_end_date_ziti = preg_match('/^20\d{2}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',$_GET['ziti_query_end_date']);
        $ziti_start_unixtime = $if_start_date_ziti ? strtotime($_GET['ziti_query_start_date']) : null;
        $ziti_end_unixtime = $if_end_date_ziti ? strtotime($_GET['ziti_query_end_date']): null;
        if ($ziti_start_unixtime || $ziti_end_unixtime) {
            $condition['order_common.ziti_ladder_time'] = array('between',array($ziti_start_unixtime,$ziti_end_unixtime));
        }

        if ($_GET['skip_off'] == 1) {
            $condition['order.order_state'] = array('neq',ORDER_STATE_CANCEL);
        }

        $order_list = $model_order->getOrderList2($consignee_name,$condition, 20, '*', '','', array('order_goods','order_common','member'));
	    //print_r($order_list);
		//break;
        //页面中显示那些操作
       foreach ($order_list as $key => $order_info) {
        
             //显示收货
            $order_info['if_receive'] = $model_order->getOrderOperateState('receive',$order_info);
        	//显示取消订单
        	$order_info['if_cancel'] = $model_order->getOrderOperateState('store_cancel',$order_info);

        	//显示调整运费
        	$order_info['if_modify_price'] = $model_order->getOrderOperateState('modify_price',$order_info);
			
		    //显示修改价格
        	$order_info['if_spay_price'] = $model_order->getOrderOperateState('spay_price',$order_info);

        	//显示发货
        	$order_info['if_send'] = $model_order->getOrderOperateState('send',$order_info);

        	//显示锁定中
        	$order_info['if_lock'] = $model_order->getOrderOperateState('lock',$order_info);

        	//显示物流跟踪
        	$order_info['if_deliver'] = $model_order->getOrderOperateState('deliver',$order_info);

        	foreach ($order_info['extend_order_goods'] as $value) {
        	    $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
        	    $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
                //edit0420
        	     $value['goods_type_cn'] = goodsTypeName($value['goods_type']);
        	    $value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
        	        $order_info['goods_list'][] = $value;
        	}

        	if (empty($order_info['zengpin_list'])) {
        	    $order_info['goods_count'] = count($order_info['goods_list']);
        	} else {
        	    $order_info['goods_count'] = count($order_info['goods_list']) + 1;
        	}
			
        	$order_list[$key] = $order_info;
            //剔除订单列表已付款的总单保留分单和集市总单又是分单的所有状态
            $arraystates = array(20,30,40);
            if($order_info['is_zorder']==0 && in_array($order_info['order_state'], $arraystates)){
                 unset($order_list[$key]);
            }

        }

        Tpl::output('order_list',$order_list);
        Tpl::output('show_page',$model_order->showpage());
        self::profile_menu('list',$_GET['state_type']);

        Tpl::showpage('store_company_order.index');
	}

	/**
	 * 用户中心右边，小导航
	 *
	 * @param string	$menu_type	导航类型
	 * @param string 	$menu_key	当前导航的menu_key
	 * @return
     */
    private function profile_menu($menu_type='',$menu_key='') {
        Language::read('member_layout');
        switch ($menu_type) {
        	case 'list':
            $menu_array = array(
            array('menu_key'=>'store_order',		'menu_name'=>Language::get('nc_member_path_all_order'),	'menu_url'=>'index.php?act=store_company_order'),
            array('menu_key'=>'state_new',			'menu_name'=>Language::get('nc_member_path_wait_pay'),	'menu_url'=>'index.php?act=store_company_order&op=index&state_type=state_new'),
            array('menu_key'=>'state_pay',	        'menu_name'=>Language::get('nc_member_path_wait_send'),	'menu_url'=>'index.php?act=store_company_order&op=index&state_type=state_pay'),
            array('menu_key'=>'state_send',		    'menu_name'=>"待取货",	    'menu_url'=>'index.php?act=store_company_order&op=index&state_type=state_send'),
            array('menu_key'=>'state_success',		'menu_name'=>Language::get('nc_member_path_finished'),	'menu_url'=>'index.php?act=store_company_order&op=index&state_type=state_success'),
            array('menu_key'=>'state_cancel',		'menu_name'=>Language::get('nc_member_path_canceled'),	'menu_url'=>'index.php?act=store_company_order&op=index&state_type=state_cancel'),
            );
            break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }
	
	public function exportOp(){
		Tpl::showpage('store_company_order_export');
	}
	
	/**
     * 导出子订单
     *
     */
    public function export_order_subOp()
    {
        $model_order = Model('order');
        $condition = array();
        if ($_GET['order_sn']) {
            $condition['order.order_sn'] = $_GET['order_sn'];
        }
        if ($_GET['store_name']) {
            $condition['order.store_name'] = $_GET['store_name'];
        }
        if (in_array($_GET['order_state'], array('0', '10', '20', '30', '40'))) {
            $condition['order.order_state'] = $_GET['order_state'];
        }
        if ($_GET['payment_code']) {
            $condition['order.payment_code'] = $_GET['payment_code'];
        }
        if ($_GET['buyer_name']) {
            $condition['order.buyer_name'] = $_GET['buyer_name'];
        }

        //模式
        if ($_GET['is_mode'] != '') {
            $condition['order.is_mode'] = $_GET['is_mode'];
        }

        if ($_GET['order_type'] != '') {
            $condition['order.order_type'] = $_GET['order_type'];
        }

        //支付方式
        if ($_GET['pay_code'] != '') {
            $condition['order.payment_code'] = $_GET['pay_code'];
        }

        //订单状态
        if ($_GET['order_state'] != '') {
            $condition['order.order_state'] = $_GET['order_state'];
        }

        //已关闭订单
        if ($_GET['skipoff2'] == 1) {
            $condition['order.order_state'] = array('neq', 0);
        }

        $condition['order.store_id'] = $_SESSION['store_id'];

        if ($_GET['goods_name'] != '') {
            $goods_name = $_GET['goods_name'];
            $condition['order_goods.goods_name'] = array('like', '%' . $goods_name . '%');
        }
        if ($_GET['goods_serial'] != '') {
            $goods_serial = $_GET['goods_serial'];
        }
        if ($_GET['consignee_name'] != '') {
            $condition['order_common.reciver_name'] = $_GET['consignee_name'];
        }

        //发货人姓名 新增
        if ($_GET['senderusername'] != '') {
            $model_daddress = Model('daddress');
            $address_list = $model_daddress->getAddressInfo(array('seller_name' => $_GET['senderusername']));

            $condition['order_goods.deliverer_id'] = $address_list['address_id'];
        }

        //下单时间
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date2']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date2']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_date2']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_date2']) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['order.add_time'] = array('time', array($start_unixtime, $end_unixtime));
        }

        //发货时间
        $if_start_time_fahuo = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date2_fahuo']);
        $if_end_time_fahuo = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date2_fahuo']);
        $start_unixtime_fahuo = $if_start_time_fahuo ? strtotime($_GET['query_start_date2_fahuo']) : null;
        $end_unixtime_fahuo = $if_end_time_fahuo ? strtotime($_GET['query_end_date2_fahuo']) : null;
        if ($start_unixtime_fahuo || $end_unixtime_fahuo) {
            $condition['order_common.shipping_time'] = array('time', array($start_unixtime_fahuo, $end_unixtime_fahuo));
        }

        $if_start_time_pay = $_GET['query_start_date_pay2'];
        $if_end_time_pay = $_GET['query_end_date_pay2'];
        $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_date_pay2']) : null;
        $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_date_pay2']) : null;

        //订单完成时间  xinzeng
        $if_start_time_finish = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date_finish2']);
        $if_end_time_finish = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date_finish2']);
        $start_unixtime_finish = $if_start_time_finish ? strtotime($_GET['query_start_date_finish2']) : null;
        $end_unixtime_finish = $if_end_time_finish ? strtotime($_GET['query_end_date_finish2']) : null;
        if ($start_unixtime_finish || $end_unixtime_finish) {
            $condition['order.finnshed_time'] = array('time', array($start_unixtime_finish, $end_unixtime_finish));
        }

        if ($start_unixtime_pay || $end_unixtime_pay) {
            $condition['order.payment_time'] = array('between', array($start_unixtime_pay, $end_unixtime_pay));
        }
		//$condition['order.order_state'] = array('neq',10);
		//$condition['order.is_zorder'] = array('neq',0);
		$member_id = $_SESSION['member_id'];
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($member_id,'company_id');
        $company_id = $member_info['company_id'];
        $condition['order.company_id'] = $company_id;

        $data = $model_order->getOrderGoodsExportList($condition,'20000',$goods_serial);
		
		foreach($data as $kk=>$vv){
			if($vv['is_zorder']==0 && in_array($vv['order_state'],array('20','30','40'))){
				unset($data[$kk]);
			}
		}
        $this->excel_order_sub($data);
    }

    private function excel_order_sub($data_tmp)
    {
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV','AW','AX','AY','AZ','BA','BB','BC','BD','BE','BF');
        $tableheader = array('订单号','总单号', '商品名称', '规格型号', '商品净重', '商品一级分类', '商品分类', '商品数量', '发货人', '收货人姓名', '收货人地址', '', '', '收货地址', '收货人电话', '子订单号', '店铺', '买家', '订单来源', '下单时间', '	支付时间', '	完成时间	', '商品货号	', '商品单价',  '单价税金', '商品总价', '总税金', '运费', '预存款支付金额', '充值卡支付金额', '优惠券优惠', '实际支付金额', '订单总额', '支付方式	', '发货人姓名', '身份证号', '发货时间', '买家留言', '发货备注	', '商品模式	', '交易流水号', '订单状态', '退款金额','退款完成时间','商家处理状态','平台确认','商家意见','管理员意见','退款原因', '备注', '运单号', '促销信息', '代金券','分享人','分享公司','佣金比例');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
            $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
        }

        $model_order_log = Model('order_log');
        foreach ($data_tmp as $key => $order_info) {
            for ($ii = 0; $ii < count($order_info['extend_order_goods']); $ii++) {
                $reciver_info = unserialize($order_info['reciver_info']);
                $address = $reciver_info['area'];
                $street = $reciver_info['street'];
                $arr_str = explode(" ", preg_replace('#\s+#', ' ', trim($address)));
                if (!empty($arr_str)) {
                    $sheng = $arr_str[0] . '省';
                    $shi = $arr_str[1];
                    $qu = $arr_str[2];
                    $jie = $street;
                } else {
                    $sheng = ' ';
                    $shi = ' ';
                    $qu = ' ';
                    $jie = ' ';
                }
                 $model_class = Model('goods_class');
                  $goods_class1 = $model_class->getGoodsClassInfoById($order_info['extend_order_goods'][$ii]['gc_id']);//第一级商品分类
                  $goods_class2 = $model_class->getGoodsClassInfoById($goods_class1['gc_parent_id']);
                  $goods_class3 = $model_class->getGoodsClassInfoById($goods_class2['gc_parent_id']);
                   $goods_classname=$goods_class3['gc_name'];
                if ($ii == 0) {
                    if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
                        $yhqzf = $order_info['extend_order_goods'][$ii]['voucher_price'];
                    } else {
                        $yhqzf = $order_info['voucher_price'];
                    }
                    if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
                        $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'] - $order_info['extend_order_goods'][$ii]['voucher_price'], 2);
                    } else {
                        $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'], 2);
                    }
                } else {
                    if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
                        $yhqzf = $order_info['extend_order_goods'][$ii]['voucher_price'];
                    } else {
                        $yhqzf = 0.00;
                    }
                    if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
                        $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'] - $order_info['extend_order_goods'][$ii]['voucher_price'], 2);
                    } else {
                        $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'], 2);
                    }
                }

                if ($order_info['is_mode'] == 0) {
                    $is_mode = '一般贸易';
                } elseif ($order_info['is_mode'] == 1) {
                    $is_mode = '备货模式';
                } elseif ($order_info['is_mode'] == 2) {
                    $is_mode = '集货模式';
                }

                $model_refund_return = Model('refund_return');
                //部分退款与全部退款 
                $goodsid = $model_refund_return->getRefundReturnList(array('order_id' => $order_info['order_id']));
                if ($order_info['refund_state'] == '1') {
                   
                    foreach ($goodsid as $key => $vv) {
                        if ($order_info['extend_order_goods'][$ii]['goods_id'] == $vv['goods_id']) {
                             $state = '部分退款';
                            $refund_amount = $vv['refund_amount'];
                        }else{
                            $state = '';
							$refund_amount = '';
						}
                    }
                } else if ($order_info['refund_state'] == '2') {
                    $state = '已关闭';
                    $refund_amount = $order_info['refund_amount'];
                } else {
                    $state = strip_tags(orderState($order_info));
                    $refund_amount = '0.00';
                }
              foreach ($goodsid as $key => $vv) {
                 if($order_info['extend_order_goods'][$ii]['goods_id'] == $vv['goods_id']){
                     //备注
                $result = $model_refund_return->getRefundReturnList(array('order_id'=>$order_info['order_id']));
                //退款时间
                 if($result[0]['admin_time']>0){
                        $refund_time=date('Y-m-d H:i:s',$result[0]['admin_time']) ;
                    }else{
                         $refund_time='无';
                    }
                    if($result[0]['seller_state']=='1'){
                        $seller_state = '待审核';
                    }else if($result[0]['seller_state']=='2'){
                        $seller_state= '同意';
                    }else if($result[0]['seller_state']=='3'){
                        $seller_state = '不同意';
                    }else{
                        $seller_state = '';
                    }
                    if($result[0]['seller_state']=='2'){
                        if($result[0]['refund_state']=='1'){
                            $admin_state = '处理中';
                        }else if($result[0]['refund_state']=='2'){
                            $admin_state = '待管理员处理';
                        }else if($result[0]['refund_state']=='3'){
                            $admin_state ='已完成';
                        }else{
                            $admin_state ='无';
                        }
                    }else{
                        $admin_state ='无';
                    }
                    $seller_message = $result[0]['seller_message'];
                    $admin_message= $result[0]['admin_message'];
                     $buyer_message =$result[0]['reason_info'];
                if ($result) {
                    if ($result[0]['refund_type'] == 1) {
                        if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
                            $beizhu = '退款中'; //退款中
                        } else if ($result[0]['refund_state'] == 3) {
                            if ($result[0]['seller_state'] == 2) {
                                $beizhu = '退款完成'; //退款完成
                            } else if ($result[0]['seller_state'] == 3) {
                                $beizhu = '退款失败'; //退款失败
                            }
                        } else {
                            $beizhu = '';
                        }
                    } elseif ($result[0]['refund_type'] == 2) {
                        if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
                            $beizhu = '退款退货中';//退款退货中
                        } else if ($result[0]['refund_state'] == 3) {
                            $beizhu = '退款退货完成'; //退款退货完成
                        } else {
                            $beizhu = ' ';
                        }
                    }
                } else {
                    $beizhu = '';
                }
                    break;
                 }else if($vv['goods_id']==0){
                         //备注
                    $result = $model_refund_return->getRefundReturnList(array('order_id'=>$order_info['order_id']));
                    //退款时间
                     if($result[0]['admin_time']>0){
                            $refund_time=date('Y-m-d H:i:s',$result[0]['admin_time']) ;
                        }else{
                             $refund_time='无';
                        }
                        if($result[0]['seller_state']=='1'){
                            $seller_state = '待审核';
                        }else if($result[0]['seller_state']=='2'){
                            $seller_state= '同意';
                        }else if($result[0]['seller_state']=='3'){
                            $seller_state = '不同意';
                        }else{
                            $seller_state = '';
                        }
                        if($result[0]['seller_state']=='2'){
                            if($result[0]['refund_state']=='1'){
                                $admin_state = '处理中';
                            }else if($result[0]['refund_state']=='2'){
                                $admin_state = '待管理员处理';
                            }else if($result[0]['refund_state']=='3'){
                                $admin_state ='已完成';
                            }else{
                                $admin_state ='无';
                            }
                        }else{
                            $admin_state ='无';
                        }
                        $seller_message = $result[0]['seller_message'];
                        $admin_message= $result[0]['admin_message'];
                         $buyer_message =$result[0]['reason_info'];
                    if ($result) {
                        if ($result[0]['refund_type'] == 1) {
                            if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
                                $beizhu = '退款中'; //退款中
                            } else if ($result[0]['refund_state'] == 3) {
                                if ($result[0]['seller_state'] == 2) {
                                    $beizhu = '退款完成'; //退款完成
                                } else if ($result[0]['seller_state'] == 3) {
                                    $beizhu = '退款失败'; //退款失败
                                }
                            } else {
                                $beizhu = '';
                            }
                        } elseif ($result[0]['refund_type'] == 2) {
                            if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
                                $beizhu = '退款退货中';//退款退货中
                            } else if ($result[0]['refund_state'] == 3) {
                                $beizhu = '退款退货完成'; //退款退货完成
                            } else {
                                $beizhu = ' ';
                            }
                        }
                    } else {
                        $beizhu = '';
                    }

                 }else{
                     $refund_time='';
                    $seller_state = '';
                    $admin_state ='';
                    $seller_message = '';
                    $admin_message='';
                    $buyer_message ='';
                       $beizhu = ' ';
                 }
              }
               

                if ($order_info['order_type'] == 0) {
                    $order_type = '无活动';
                } elseif ($order_info['order_type'] == 1) {
                    $order_type = '阶梯价';
                } elseif ($order_info['order_type'] == 2) {
                    $order_type = '团购';
                } elseif ($order_info['order_type'] == 3) {
                    $order_type = '新人专享';
                } elseif ($order_info['order_type'] == 4) {
                    $order_type = '限时秒杀';
                } elseif ($order_info['order_type'] == 5) {
                    $order_type = '即买即送';
                }

                $voucher = unserialize($order_info['voucher_code']);
                if (!empty($voucher)) {
                    foreach ($voucher as $voucherk => $voucherv) {
                        if (!empty($voucherv['voucher_code'])) {
                            $voucher_code = $voucherv['voucher_code'];
                            $voucher_name = Model('voucher')->getVoucherInfo(array('voucher_code'=>$voucher_code),'voucher_title');
                            $vou = $voucher_name['voucher_title'];
                        } else {
                            $vou = ' ';
                        }
                    }
                } else {
                    $vou = ' ';
                }

                $order_data[] = [
                    'order_sn'=>$ii == 0 ? $order_info['order_sn'] : ' ',
                    'z_order_sn'=>$ii == 0 ? $order_info['z_order_sn'] : ' ',
                    'goods_name'=>$order_info['extend_order_goods'][$ii]['goods_name'],
                    'goods_spec'=>unserialize($order_info['extend_order_goods'][$ii]['goods_spec']) ? array_values(unserialize($order_info['extend_order_goods'][$ii]['goods_spec']))[0] : ' ',
                    'goods_weight'=>$order_info['extend_order_goods'][$ii]['goods_weight'] ? $order_info['extend_order_goods'][$ii]['goods_weight'] . 'kg' : ' ',
                    'goods_class1'=> $goods_classname,
                    'goods_class'=>Model('goods_class')->getGoodsClassInfoById($order_info['extend_order_goods'][$ii]['gc_id'])['gc_name'] ? Model('goods_class')->getGoodsClassInfoById($order_info['extend_order_goods'][$ii]['gc_id'])['gc_name'] : ' ',
                    'goods_num'=>$order_info['extend_order_goods'][$ii]['goods_num'],
                    'storage'=>$order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']?Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']))['seller_name']:Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['deliverer_id']))['seller_name'],
                    'reciver_name'=>$order_info['reciver_name'],
                    'reciver_address1'=>$sheng, 'reciver_address2'=>$shi, 'reciver_address3'=>$qu, 'recive_address'=>$jie,//省，市，区，街
                    'reciver_phone'=>$reciver_info['phone'],
                    'order_sub_id'=>$order_info['order_sn'] . $order_info['extend_order_goods'][$ii]['goods_id'],
                    'store'=>$ii == 0 ? $order_info['store_name'] : ' ',
                    'buyer'=>$ii == 0 ? $order_info['buyer_name'] : ' ',
                    'order_from'=>'微信小程序',
                    'add_time'=>date('Y-m-d H:i:s', $order_info['add_time']),
                    'pay_time'=>$order_info['payment_time'] != 0 ? date('Y-m-d H:i:s', $order_info['payment_time']) : ' ',
                    'complete_time'=>$order_info['finnshed_time'] != 0 ? date('Y-m-d H:i:s', $order_info['finnshed_time']) : ' ',
                    'goods_serial'=>$order_info['extend_order_goods'][$ii]['order_goods_serial']? $order_info['extend_order_goods'][$ii]['order_goods_serial']: $order_info['extend_order_goods'][$ii]['goods_serial'],
                    'goods_price'=>$order_info['extend_order_goods'][$ii]['goods_price'],
                    //'goods_costprice'=>$order_info['extend_order_goods'][$ii]['goods_cost_price'],
                    //'goods_costall'=>number_format($order_info['extend_order_goods'][$ii]['goods_cost_price'] * $order_info['extend_order_goods'][$ii]['goods_num'],2),
                    'goods_tax'=>'0.00',//税金暂定0
                    'goods_priceall'=>number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'], 2),
                    'goods_taxall'=>'0.00',//总税金暂定0
                    'deliver_fee'=>$order_info['shipping_fee'],
                    'pd_amount'=>$order_info['pd_amount'],
                    'rcb_amount'=>$order_info['rcb_amount'],
                    'voucher_price'=>$yhqzf,
                    'pay_amount'=>$sjzf,
                    'order_amount'=>$ii == 0 ? $order_info['order_amount'] : ' ',
                    'pay_type'=>orderPaymentName($order_info['payment_code']),
                    'deliver_name'=>' ',//发货人姓名暂定空
                    'id_card'=>str_replace(" ", "1", $reciver_info['id_card']),
                    'deliver_time'=>$order_info['shipping_time'] != 0 ? date('Y-m-d H:i:s', $order_info['shipping_time']) : ' ',
                    'order_message'=>$order_info['order_message'] != '' ? $order_info['order_message'] : ' ',
                    'deliver_explain'=>$order_info['deliver_explain'] != '' ? $order_info['deliver_explain'] : ' ',
                    'is_mode'=>$is_mode,
                    'pay_sn'=>$ii == 0 ? explode(' ', $model_order_log->where(array('order_id' => $order_info['order_id'], 'log_msg' => array('like', '%支付平台交易号%')))->select()[0]['log_msg'])[4] : ' ',
                    'order_state'=>$state,
                    'refund_amount'=>$refund_amount,
                    'refund_time'=>$refund_time,
                    'seller_state'=>$seller_state,
                    'admin_state'=>$admin_state,
                    'seller_message'=>$seller_message,
                    'admin_message'=>$admin_message,
                    'buyer_message'=>$buyer_message,
                    'beizhu'=>$beizhu,
                    'waybill'=>$ii == 0 ? $order_info['shipping_code'] : ' ',
                    //'order_type'=>goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
					'order_type'=>$order_info['add_time']<1618931572?goodsTypeName($order_info['order_type']):goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
                    'voucher'=>$vou,
                    'share_name' => $order_info['share_name'],
                    'company_name' => $order_info['company_name'],
                    'commis_rate'=>$order_info['extend_order_goods'][$ii]['commis_rate'].'%',
                ];
            }
            unset($data_tmp[$key]);
        }
        //填充表格信息
        for ($i = 2; $i <= count($order_data) + 1; $i++) {
            $j = 0;
            foreach ($order_data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i","$value",PHPExcel_Cell_DataType::TYPE_STRING);
                $j++;
            }
        }
        //创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        $filename = '订单-' . date('Y-m-d-H', time()) . '.xls';
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename=' . $filename);
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
        die;
    }
}
