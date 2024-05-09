<?php
/**
 * 商家中心订单导出
 *
 *
 *
 **/

//require_once 'E:\phpstudy\WWW\fp\PHPExcel\Classes\PHPExcel.php'; //引入文件
require_once '/home/wwwroot/default/wzxd/PHPExcel/Classes/PHPExcel.php'; //引入文件
require_once '/home/wwwroot/default/wzxd/PHPExcel/Classes/PHPExcel/IOFactory.php'; //引入文件
require_once '/home/wwwroot/default/wzxd/PHPExcel/Classes/PHPExcel/Reader/Excel2007.php'; //引入文件
require_once '/home/wwwroot/default/wzxd/PHPExcel/Classes/PHPExcel/Reader/IReader.php'; //引入文件
defined('In718Shop') or exit('Access Invalid!');

class store_exportControl extends BaseSellerControl
{
    const EXPORT_SIZE = 1000;

    public function __construct()
    {
        parent::__construct();
        Language::read('member_store_index');
    }

    /**
     * 导出订单
     *
     */
    public function export_orderOp()
    {
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

        if ($_GET['consignee_name'] != '') {
            $condition['order_common.reciver_name'] = $_GET['consignee_name'];
        }
        //发货人姓名 新增
        if ($_GET['senderusername'] != '') {
            $model_daddress = Model('daddress');
            $address_list = $model_daddress->getAddressInfo(array('seller_name' => $_GET['senderusername']));

            $condition['order_common.daddress_id'] = $address_list['address_id'];
        }
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
        if ($_GET['skip_off'] == 1) {
            $condition['order.order_state'] = array('neq', 0);
        }
        $condition['order.store_id'] = $_SESSION['store_id'];
        //下单时间
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_date']) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['order.add_time'] = array('time', array($start_unixtime, $end_unixtime));
        }
        //发货时间
        $if_start_time_fahuo = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date_fahuo']);
        $if_end_time_fahuo = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date_fahuo']);
        $start_unixtime_fahuo = $if_start_time_fahuo ? strtotime($_GET['query_start_date_fahuo']) : null;
        $end_unixtime_fahuo = $if_end_time_fahuo ? strtotime($_GET['query_end_date_fahuo']) : null;
        if ($start_unixtime_fahuo || $end_unixtime_fahuo) {
            $condition['order_common.shipping_time'] = array('time', array($start_unixtime_fahuo, $end_unixtime_fahuo));
        }
        //支付时间  xinzeng
        $if_start_time_pay = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date_pay']);
        $if_end_time_pay = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date_pay']);
        $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_date_pay']) : null;
        $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_date_pay']) : null;
        if ($start_unixtime_pay || $end_unixtime_pay) {
            $condition['order.payment_time'] = array('time', array($start_unixtime_pay, $end_unixtime_pay));
        }
        //订单完成时间  xinzeng
        $if_start_time_finish = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date_finish']);
        $if_end_time_finish = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date_finish']);
        $start_unixtime_finish = $if_start_time_finish ? strtotime($_GET['query_start_date_finish']) : null;
        $end_unixtime_finish = $if_end_time_finish ? strtotime($_GET['query_end_date_finish']) : null;
        if ($start_unixtime_finish || $end_unixtime_finish) {
            $condition['order.finnshed_time'] = array('time', array($start_unixtime_finish, $end_unixtime_finish));
        }

        //表格数组
        $model_order = Model('order');
ini_set('max_execution_time', '0');
        $data = $model_order->getOrderList3('', $condition, '', '*', 'order_id desc', '20000', array('order_goods', 'order_common', 'member', 'goods_kuajing_d'));
		foreach($data as $kk=>$vv){
			if($vv['is_zorder']==0 && in_array($vv['order_state'],[20,30,40])){
				unset($data[$kk]);
			}
		}
        $count = count($data);//echo $count;die;
        if (!$_GET['curpage']) {
            if ($count > self::EXPORT_SIZE) {
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                Tpl::output('list', $array);
                Tpl::output('murl', 'index.php?act=order&op=index');
                Tpl::showpage('store_export.excel');
            } else {
                //直接下载
                $this->excel_order(array_values($data));
            }
        } else {  //下载
            $limit1 = ($_GET['curpage'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_order->getOrderList3('', $condition, '', '*', 'order_id desc', "{$limit1},{$limit2}", array('order_goods', 'order_common', 'member', 'goods_kuajing_d'));
            $this->excel_order(array_values($data));
        }
    }

    private function excel_order($data_tmp)
    {
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD');
        $tableheader = array('订单号', '支付时间', '商品名称', '商品数量', '商品单价', '商品总价', '订单总税金', '运费', '实际支付金额', '支付方式', '买家留言', '卖家备注', '退款金额', '交易流水号', '发货人姓名', '收货人姓名', '收货人电话', '收货人地址', '取货时间', '身份证号', '子订单号', '店铺', '买家', '下单时间', '商品货号', '发货时间', '订单状态', '备注', '运单号', '订单类型');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
            $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
        }
        //表格数组
        $model_common = Model('order_common');
        $model_order_log = Model('order_log');
        $model_daddress = Model('daddress');
        $order_data = [];
        foreach ($data_tmp as $k => $v) {
            //备注
            $order_id = $v['order_id'];
            $a = array('order_id' => $order_id);
            $model_refund_return = Model('refund_return');
            $result = $model_refund_return->getRefundReturnList($a);//
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
                        $beizhu = '';
                    }
                }
            } else {
                $beizhu = '';
            }
            $order_data[$k] = array(
                $v['order_sn'],
                $v['payment_time'] ? date('Y-m-d H:i:s', $v['payment_time']) : ' ',
                $v['extend_order_goods'][0]['goods_name'],
                $v['extend_order_goods'][0]['goods_num'],
                $v['extend_order_goods'][0]['goods_price'],
                number_format($v['extend_order_goods'][0]['goods_num'] * $v['extend_order_goods'][0]['goods_price'], 2),
                $v['store_tax_total'],
                $v['shipping_fee'],
                $v['order_amount'],
                orderPaymentName($v['payment_code']),
                $model_common->getfby_order_id($v['order_id'], 'order_message'),
                $model_common->getfby_order_id($v['order_id'], 'deliver_explain'),
                $v['refund_amount'],
                explode(' ', $model_order_log->where(array('order_id' => $v['order_id'], 'log_msg' => array('like', '%支付平台交易号%')))->select()[0]['log_msg'])[4],
                $model_daddress->getAddressInfo(array('address_id' => $v['extend_order_common']['daddress_id']))['seller_name'],
                $model_common->getfby_order_id($v['order_id'], 'reciver_name'),
                str_replace(" ", "", $v['extend_order_common']['reciver_info']['phone']),
                str_replace(" ", "", $v['extend_order_common']['reciver_info']['address']),
                $v['order_type'] == 1 ? date('Y-m-d H:i:s', $v['ziti_ladder_time']) : '',
                str_replace(" ", "", $v['extend_order_common']['reciver_info']['id_card']),
                $v['order_sn'] . str_replace(" ", "", $v['extend_order_goods'][0]['goods_id']),
                $v['store_name'],
                $v['buyer_name'],
                date('Y-m-d H:i:s', $v['add_time']),
                Model()->table('goods')->getfby_goods_id(str_replace(" ", "", $v['extend_order_goods'][0]['goods_id']), 'goods_serial'), $model_common->getfby_order_id($v['order_id'], 'shipping_time') != 0 ? date('Y-m-d H:i:s', $model_common->getfby_order_id($v['order_id'], 'shipping_time')) : '',
                $v['refund_state'] == 1 ? '部分退款' : $v['refund_state'] == '2' ? '已关闭' : strip_tags(orderState($v)),
                $beizhu,
                $v['is_mode'] == 0 ? $v['shipping_code'] : unserialize($v['extend_order_common']['waybill_info']) ? unserialize($v['extend_order_common']['waybill_info'])['logisticsNo'] : '',
                $v['order_type'] = 0 ? '无活动' : $v['order_type'] == 1 ? '阶梯价' : $v['order_type'] == 2 ? '团购' : $v['order_type'] == 3 ? '新人专享' : $v['order_type'] == 4 ? '限时秒杀' : $v['order_type'] == 5 ? '即买即送' : '');
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
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV','AW');
        $tableheader = array('订单号','总单号', '商品名称', '规格型号', '商品净重', '商品分类', '商品数量', '发货人', '收货人姓名', '收货人地址', '', '', '收货地址', '收货人电话', '子订单号', '店铺', '买家', '订单来源', '下单时间', '	支付时间', '	完成时间	', '商品货号	', '商品单价', '	商品成本', '商品总成本', '单价税金', '商品总价', '总税金', '运费', '预存款支付金额', '充值卡支付金额', '优惠券优惠', '实际支付金额', '订单总额', '支付方式	', '发货人姓名', '身份证号', '发货时间', '买家留言', '发货备注	', '商品模式	', '交易流水号', '订单状态', '退款金额','退款完成时间', '备注', '运单号', '促销信息', '代金券');
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
                if ($order_info['refund_state'] == '1') {
                    $goodsid = $model_refund_return->getRefundReturnList(array('order_id' => $order_info['order_id']));
                    $state = '部分退款';
                    foreach ($goodsid as $key => $vv) {
                        if ($order_info['extend_order_goods'][$ii]['goods_id'] == $vv['goods_id']) {
                            $refund_amount = $vv['refund_amount'];
                        }
                    }
                } else if ($order_info['refund_state'] == '2') {
                    $state = '已关闭';
                    $refund_amount = $order_info['refund_amount'];
                } else {
                    $state = strip_tags(orderState($order_info));
                    $refund_amount = '0.00';
                }

                //备注
                $result = $model_refund_return->getRefundReturnList(array('order_id'=>$order_info['order_id']));
                //退款时间
                 if($result[0]['admin_time']>0){
                        $refund_time=date('Y-m-d H:i:s',$result[0]['admin_time']) ;
                    }else{
                         $refund_time='无';
                    }
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
                    'goods_costprice'=>$order_info['extend_order_goods'][$ii]['goods_cost_price'],
                    'goods_costall'=>number_format($order_info['extend_order_goods'][$ii]['goods_cost_price'] * $order_info['extend_order_goods'][$ii]['goods_num'],2),
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
                    'beizhu'=>$beizhu,
                    'waybill'=>$ii == 0 ? $order_info['shipping_code'] : ' ',
                    //'order_type'=>goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
					'order_type'=>$order_info['add_time']<1618931572?goodsTypeName($order_info['order_type']):goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
                    'voucher'=>$vou,
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
        $filename = '子订单-' . date('Y-m-d-H', time()) . '.xls';
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

/**
     * 导出子订单-物资小店专用
     */
    public function export_order_sub_wzxdOp()
    {
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
            $sql = "SELECT * from `718shop_order_goods` where kuajing_info like '%" . $_GET['senderusername'] . "%'";
            $kuajing_info = Model()->query($sql);
            $order_id = array();
            for ($i = 0; $i < count($kuajing_info); $i++) {
                $order_id[$i] = $kuajing_info[$i]['order_id'];
            }
            $condition['order.order_id'] = array('in', $order_id);
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
        $model_order = Model('order');
        $data = $model_order->getOrderGoodsExportList($condition,'20000',$goods_serial);
        $this->excel_order_wzxd(array_values($data));
        die;
        $count = count($data);
        if (!is_numeric($_GET['curpage'])) {
            $array = array();
            if ($count > self::EXPORT_SIZE) {   //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                Tpl::output('list', $array);
                Tpl::output('murl', 'index.php?act=order&op=index');
                Tpl::showpage('store_export.excel');
            } else {  //如果数量小，直接下载
                $data = $model_order->getOrderGoodsExportList($condition,'20000',$goods_serial);
                $this->excel_order_wzxd(array_values($data));
            }
        } else {  //下载
            $limit1 = ($_GET['curpage'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_order->getOrderGoodsExportList($condition,'20000',$goods_serial);
            $this->excel_order_wzxd(array_values($data));
        }
    }

    private function excel_order_wzxd($data_tmp)
    {
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
        $tableheader = array('订单号', '商品名称', '商品数量', '规格型号', '下单时间', '收货人姓名', '收货人电话', '订单状态');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
            $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
        }

        foreach ($data_tmp as $key => $order_info) {
            for ($ii = 0; $ii < count($order_info['extend_order_goods']); $ii++) {
                $reciver_info = unserialize($order_info['reciver_info']);

                $model_refund_return = Model('refund_return');
                //部分退款与全部退款
                if ($order_info['refund_state'] == '1') {
                    $goodsid = $model_refund_return->getRefundReturnList(array('order_id' => $order_info['order_id']));
                    $state = '部分退款';
                    foreach ($goodsid as $key => $vv) {
                        if ($order_info['extend_order_goods'][$ii]['goods_id'] == $vv['goods_id']) {
                        }
                    }
                } else if ($order_info['refund_state'] == '2') {
                    $state = '已关闭';
                } else {
                    $state = strip_tags(orderState($order_info));
                }

                $order_data[] = [
                    'order_sn'=>$ii == 0 ? $order_info['order_sn'] : ' ',
                    'goods_name'=>$order_info['extend_order_goods'][$ii]['goods_name'],
                    'goods_num'=>$order_info['extend_order_goods'][$ii]['goods_num'],
                    'goods_spec'=>unserialize($order_info['extend_order_goods'][$ii]['goods_spec']) ? array_values(unserialize($order_info['extend_order_goods'][$ii]['goods_spec']))[0] : ' ',
                    'add_time'=>date('Y-m-d H:i:s', $order_info['add_time']),
                    'reciver_name'=>$order_info['reciver_name'],
                    'reciver_phone'=>$reciver_info['phone'],
                    'order_state'=>$state,
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
        $filename = '子订单（小店）-' . date('Y-m-d-H', time()) . '.xls';
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
    /**
     * 订单列表
     *
     */
    public function indexOp()
    {
        $model_order = Model('order');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        if ($_GET['order_sn'] != '') {
            $condition['order_sn'] = $_GET['order_sn'];
        }
        if ($_GET['buyer_name'] != '') {
            $condition['buyer_name'] = $_GET['buyer_name'];
        }
        if ($_GET['is_mode'] != '') {
            $condition['is_mode'] = $_GET['is_mode'];
        }
        $allow_state_array = array('state_new', 'state_pay', 'state_send', 'state_success', 'state_cancel');
        if (in_array($_GET['state_type'], $allow_state_array)) {
            $condition['order_state'] = str_replace($allow_state_array,
                array(ORDER_STATE_NEW, ORDER_STATE_PAY, ORDER_STATE_SEND, ORDER_STATE_SUCCESS, ORDER_STATE_CANCEL), $_GET['state_type']);
        } else {
            $_GET['state_type'] = 'store_order';
        }
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('time', array($start_unixtime, $end_unixtime));
        }

        if ($start_unixtime || $end_unixtime) {
            $condition['payment_time'] = array('time', array($start_unixtime, $end_unixtime));//xinzeng
        }

        if ($_GET['skip_off'] == 1) {
            $condition['order_state'] = array('neq', ORDER_STATE_CANCEL);
        }

        $order_list = $model_order->getOrderList($condition, 20, '*', 'order_id desc', '', array('order_goods', 'order_common', 'member'));

        //页面中显示那些操作
        foreach ($order_list as $key => $order_info) {
            //显示取消订单
            $order_info['if_cancel'] = $model_order->getOrderOperateState('store_cancel', $order_info);
            //显示调整运费
            $order_info['if_modify_price'] = $model_order->getOrderOperateState('modify_price', $order_info);
            //显示修改价格
            $order_info['if_spay_price'] = $model_order->getOrderOperateState('spay_price', $order_info);
            //显示发货
            $order_info['if_send'] = $model_order->getOrderOperateState('send', $order_info);
            //显示锁定中
            $order_info['if_lock'] = $model_order->getOrderOperateState('lock', $order_info);
            //显示物流跟踪
            $order_info['if_deliver'] = $model_order->getOrderOperateState('deliver', $order_info);
            foreach ($order_info['extend_order_goods'] as $value) {
                $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
                $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
                $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
                $value['goods_url'] = urlShop('goods', 'index', array('goods_id' => $value['goods_id']));
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
        Tpl::output('order_list', $order_list);
        Tpl::output('show_page', $model_order->showpage());
        self::profile_menu('list', $_GET['state_type']);
        Tpl::showpage('store_export.index');
    }

    /**
     * 卖家订单详情
     */
    public function show_orderOp()
    {
        Language::read('member_member_index');
        $order_id = intval($_GET['order_id']);
        if ($order_id <= 0) {
            showMessage(Language::get('wrong_argument'), '', 'html', 'error');
        }
        $model_order = Model('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['store_id'] = $_SESSION['store_id'];
        $order_info = $model_order->getOrderInfo($condition, array('order_common', 'order_goods', 'member'));
        if (empty($order_info)) {
            showMessage(Language::get('store_order_none_exist'), '', 'html', 'error');
        }

        $model_refund_return = Model('refund_return');
        $order_list = array();
        $order_list[$order_id] = $order_info;
        $order_list = $model_refund_return->getGoodsRefundList($order_list, 1);//订单商品的退款退货显示
        $order_info = $order_list[$order_id];
        $refund_all = $order_info['refund_list'][0];
        if (!empty($refund_all) && $refund_all['seller_state'] < 3) {//订单全部退款商家审核状态:1为待审核,2为同意,3为不同意
            Tpl::output('refund_all', $refund_all);
        }

        //显示锁定中
        $order_info['if_lock'] = $model_order->getOrderOperateState('lock', $order_info);
        //显示调整运费
        $order_info['if_modify_price'] = $model_order->getOrderOperateState('modify_price', $order_info);
        //显示调整价格
        $order_info['if_spay_price'] = $model_order->getOrderOperateState('spay_price', $order_info);
        //显示取消订单
        $order_info['if_cancel'] = $model_order->getOrderOperateState('buyer_cancel', $order_info);
        //显示发货
        $order_info['if_send'] = $model_order->getOrderOperateState('send', $order_info);
        //显示物流跟踪
        $order_info['if_deliver'] = $model_order->getOrderOperateState('deliver', $order_info);
        //显示系统自动取消订单日期
        if ($order_info['order_state'] == ORDER_STATE_NEW) {
            $order_info['order_cancel_day'] = $order_info['add_time'] + ORDER_AUTO_CANCEL_DAY + 3 * 24 * 3600;
        }
        //显示快递信息
        if ($order_info['shipping_code'] != '') {
            $express = rkcache('express', true);
            $order_info['express_info']['e_code'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_code'];
            $order_info['express_info']['e_name'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_name'];
            $order_info['express_info']['e_url'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_url'];
        }
        //显示系统自动收获时间
        if ($order_info['order_state'] == ORDER_STATE_SEND) {
            $order_info['order_confirm_day'] = $order_info['delay_time'] + ORDER_AUTO_RECEIVE_DAY + 15 * 24 * 3600;
        }
        //如果订单已取消，取得取消原因、时间，操作人
        if ($order_info['order_state'] == ORDER_STATE_CANCEL) {
            $order_info['close_info'] = $model_order->getOrderLogInfo(array('order_id' => $order_info['order_id']), 'log_id desc');
        }

        foreach ($order_info['extend_order_goods'] as $value) {
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
            $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
            $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
            $value['goods_url'] = urlShop('goods', 'index', array('goods_id' => $value['goods_id']));
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

        Tpl::output('order_info', $order_info);

        //发货信息
        if (!empty($order_info['extend_order_common']['daddress_id'])) {
            $daddress_info = Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_common']['daddress_id']));
            Tpl::output('daddress_info', $daddress_info);
        }
        Tpl::showpage('store_order.show');
    }

    /**
     * 卖家订单状态操作
     *
     */
    public function change_stateOp()
    {
        $state_type = $_GET['state_type'];
        $order_id = intval($_GET['order_id']);

        $model_order = Model('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['store_id'] = $_SESSION['store_id'];
        $order_info = $model_order->getOrderInfo($condition);

        if ($_GET['state_type'] == 'order_cancel') {
            $result = $this->_order_cancel($order_info, $_POST);
        } elseif ($_GET['state_type'] == 'modify_price') {
            $result = $this->_order_ship_price($order_info, $_POST);
        } elseif ($_GET['state_type'] == 'spay_price') {
            $result = $this->_order_spay_price($order_info, $_POST);
        }
        if (!$result['state']) {
            showDialog($result['msg'], '', 'error', empty($_GET['inajax']) ? '' : 'CUR_DIALOG.close();');
        } else {
            showDialog($result['msg'], 'reload', 'succ', empty($_GET['inajax']) ? '' : 'CUR_DIALOG.close();');
        }
    }

    /**
     * 取消订单
     * @param unknown $order_info
     */
    private function _order_cancel($order_info, $post)
    {
        $model_order = Model('order');
        $logic_order = Logic('order');

        if (!chksubmit()) {
            Tpl::output('order_info', $order_info);
            Tpl::output('order_id', $order_info['order_id']);
            Tpl::showpage('store_order.cancel', 'null_layout');
            exit();
        } else {
            $if_allow = $model_order->getOrderOperateState('store_cancel', $order_info);
            if (!$if_allow) {
                return callback(false, '无权操作');
            }
            $msg = $post['state_info1'] != '' ? $post['state_info1'] : $post['state_info'];
            return $logic_order->changeOrderStateCancel($order_info, 'seller', $_SESSION['member_name'], $msg);
        }
    }

    /**
     * 修改运费
     * @param unknown $order_info
     */
    private function _order_ship_price($order_info, $post)
    {
        $model_order = Model('order');
        $logic_order = Logic('order');
        if (!chksubmit()) {
            Tpl::output('order_info', $order_info);
            Tpl::output('order_id', $order_info['order_id']);
            Tpl::showpage('store_order.edit_price', 'null_layout');
            exit();
        } else {
            $if_allow = $model_order->getOrderOperateState('modify_price', $order_info);
            if (!$if_allow) {
                return callback(false, '无权操作');
            }
            return $logic_order->changeOrderShipPrice($order_info, 'seller', $_SESSION['member_name'], $post['shipping_fee']);
        }

    }

    /**
     * 修改商品价格
     * @param unknown $order_info
     */
    private function _order_spay_price($order_info, $post)
    {
        $model_order = Model('order');
        $logic_order = Logic('order');
        if (!chksubmit()) {
            Tpl::output('order_info', $order_info);
            Tpl::output('order_id', $order_info['order_id']);
            Tpl::showpage('store_order.edit_spay_price', 'null_layout');
            exit();
        } else {
            $if_allow = $model_order->getOrderOperateState('spay_price', $order_info);
            if (!$if_allow) {
                return callback(false, '无权操作');
            }
            return $logic_order->changeOrderSpayPrice($order_info, 'seller', $_SESSION['member_name'], $post['goods_amount']);
        }
    }

	
    /**
     * 导出会员信息
     * 工号	姓名	会员账号	会员ID	手机号码	注册时间	最后登录	积分	经验值	消费总额	豫卡通余额	消费频次高的品类
     */
    public function memberInfo_exportOp()
    {
        $model_card = Model('card');
		$member_card_list = $model_card->getMemberCardList();
		//echo '<pre>';var_dump($member_card_list);die;
        $field = 'member.member_id,member.member_name,member.member_mobile,member.member_time,member.member_old_login_time,member.member_points,member.member_exppoints,member_card.cardno';
        $member_list = Model()->table('member,member_card')->join('left')->on('member.member_id=member_card.member_id')->field($field)->where(array('member.member_id' => array('gt', 0)))->order('member.member_id asc')->limit('20000')->select();
//            $model_member->getMemberList(array('member_id' => array('gt', 0)), $field, '', 'member_id asc', '20000');
        if (is_array($member_list)) {
            foreach ($member_list as $k => $v) { 
				foreach($member_card_list as $key=>$value) {
					if($v['cardno']==$value['Sno'] && $v['cardno']!=''){//echo $value['Name'].'<br>';
						$member_list[$k]['name'] = $value['Name'];
						$member_list[$k]['yue'] = number_format($value['Balance'],2);
						$member_list[$k]['member_gonghao'] = $value['PersonalID'];
						break;
					}
				} 
				
                //$member_card_info = $model_card->getMemberCardInfo($v['cardno']);
                //$member_list[$k]['name'] = $member_card_info['name'];
                //$member_list[$k]['yue'] = $member_card_info['balance'];
                //$member_list[$k]['member_gonghao'] = $member_card_info['personalID'];
                $order_list = Model()->table('order,order_goods')->join('left')->on('order.order_id=order_goods.order_id')->field('order.order_amount,order_goods.gc_id')->group('order.order_id')->where(array('order.buyer_id' => $v['member_id'], 'order.order_state' => array('gt', 19)))->select();
                $member_list[$k]['order_amount_all'] = array_sum(array_column($order_list, 'order_amount'));
                if (is_array($order_list)) {
                    $gc_id_arr = '';
                    foreach ($order_list as $item) {
                        $gc_id_arr .= $item['gc_id'] . ',';
                    }
                    $gc_id_arr = explode(',', $gc_id_arr);
                    array_pop($gc_id_arr);
                    $gc_id_arr = array_count_values($gc_id_arr);
                    arsort($gc_id_arr);
                    $more_value = key($gc_id_arr);
                    $goods_class = Model()->table('goods_class')->getfby_gc_id($more_value,'gc_name');
                } else {
                    $goods_class = '';
                }
                $member_list[$k]['gc_name'] = $goods_class;
            }
        }//die;echo '<pre>';var_dump($member_list);die;
        $this->excel_memberInfo($member_list);
    }

    private function excel_memberInfo($data_tmp){
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H','I','J','K','L');
        $tableheader = array('工号', '姓名', '会员账号', '会员ID', '手机号码', '注册时间', '最后登录', '积分','经验值','消费总额','豫卡通余额','消费频次高的品类');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
            $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
        }
        $member_data = [];
        foreach ($data_tmp as $item){
            $member_data[] = [
                $item['member_gonghao'],
                $item['name'],
                $item['member_name'],
                $item['member_id'],
                $item['member_mobile'],
                date('Y-m-d H:i:s', $item['member_time']),
                date('Y-m-d H:i:s', $item['member_old_login_time']),
                $item['member_points'],
                $item['member_exppoints'],
                $item['order_amount_all'],
                $item['yue'],
                $item['gc_name']
            ];
        }

        //填充表格信息
        for ($i = 2; $i <= count($member_data) + 1; $i++) {
            $j = 0;
            foreach ($member_data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i","$value",PHPExcel_Cell_DataType::TYPE_STRING);
                $j++;
            }
        }
        //创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        $filename = '会员信息' . date('Y-m-d-H', time()) . '.xls';
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



    /**
     * 用户中心右边，小导航
     */
    private function profile_menu($menu_type = '', $menu_key = '')
    {
        Language::read('member_layout');
        switch ($menu_type) {
            case 'list':
                $menu_array = array(
                    array('menu_key' => 'store_order', 'menu_name' => Language::get('nc_member_path_all_order'), 'menu_url' => 'index.php?act=store_order'),
                    array('menu_key' => 'state_new', 'menu_name' => Language::get('nc_member_path_wait_pay'), 'menu_url' => 'index.php?act=store_order&op=index&state_type=state_new'),
                    array('menu_key' => 'state_pay', 'menu_name' => Language::get('nc_member_path_wait_send'), 'menu_url' => 'index.php?act=store_order&op=store_order&state_type=state_pay'),
                    array('menu_key' => 'state_send', 'menu_name' => Language::get('nc_member_path_sent'), 'menu_url' => 'index.php?act=store_order&op=index&state_type=state_send'),
                    array('menu_key' => 'state_success', 'menu_name' => Language::get('nc_member_path_finished'), 'menu_url' => 'index.php?act=store_order&op=index&state_type=state_success'),
                    array('menu_key' => 'state_cancel', 'menu_name' => Language::get('nc_member_path_canceled'), 'menu_url' => 'index.php?act=store_order&op=index&state_type=state_cancel'),
                );
                break;
        }
        Tpl::output('member_menu', $menu_array);
        Tpl::output('menu_key', $menu_key);
    }
}
