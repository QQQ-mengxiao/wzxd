<?php
/**
 * YTO运单打印
 ***/


defined('In718Shop') or exit('Access Invalid!');
include_once template('phpqrcode/phpqrcode');

class store_yto_printControl extends BaseSellerControl {
	public function __construct() {
		parent::__construct();
		Language::read('member_printorder');
	}

	/**
	 * 查看订单
	 */
	public function indexOp() {
		$order_id	= intval($_GET['order_id']);
		$store_id = $_SESSION['store_id'];
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
		Tpl::output('order_info',$order_info);

		$sql = 'SELECT * FROM 718shop_daddress where store_id='.$store_id.'';
		$shippingArr[0] = Model()->query($sql);
		Tpl::output('shippingArr',$shippingArr[0]);

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
		}
		QRcode::png($order_info['extend_order_common']['waybill_info']['shortaddress'], 'shortaddress.png', 'L', '3', 2);
		$shortaddress =  'shortaddress.png';
		Tpl::output('shortaddress',$shortaddress);
		Tpl::output('goods_all_num',$goods_all_num);
		Tpl::output('goods_total_quantity',$goods_total_quantity);
		Tpl::showpage('store_yto.print',"null_layout");
	}
}
