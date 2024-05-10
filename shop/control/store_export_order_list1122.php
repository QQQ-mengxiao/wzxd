<?php
/**
 * 商家中心订单导出
 *
 *
 *
 **/
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/IOFactory.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/Reader/Excel2007.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/Reader/IReader.php'; //引入文件
defined('In718Shop') or exit('Access Invalid!');

class store_export_order_listControl extends BaseSellerControl
{
    const EXPORT_SIZE = 10000;
      private $gc_arr;//分类数组
    private $choose_gcid;//选择的分类ID
    public function __construct()
    {
        parent::__construct();
        Language::read('member_store_index');
         /**
         * 处理商品分类
         */
        $this->choose_gcid = ($t = intval($_REQUEST['choose_gcid']))>0?$t:0;
        $gccache_arr = Model('goods_class')->getGoodsclassCache($this->choose_gcid,3);
        $this->gc_arr = $gccache_arr['showclass'];
        Tpl::output('gc_json',json_encode($gccache_arr['showclass']));
        Tpl::output('gc_choose_json',json_encode($gccache_arr['choose_gcid']));
    }

    /**
     * 导出订单
     *
     */
    public function export_orderOp()
    {
        $model_order = Model('order');
        $condition = array();
        if (in_array($_GET['order_state'], array('0', '10', '20', '30', '40'))) {
            $condition['order.order_state'] = $_GET['order_state'];
        }

        //订单状态
        // if ($_GET['order_state'] != '') {
        //     $condition['order.order_state'] = $_GET['order_state'];
        // }
        // type=1,待发货；type=2,退款
        if($_GET['type']>0){
            $type = $_GET['type'];
            // if ($_GET['type'] == 1) {
            //     $condition['order.order_state'] = 20;
            //     $condition['order.refund_state'] = 0;
            //     $condition['order.lock_state'] = 0;
            // }
            // elseif ($_GET['type'] == 2) {
            //     // $condition['order.refund_state'] = array(1,2);
            //     //$condition['order.lock_state'] = 1;
            // }
        }
        //发货人姓名
        if($_GET['daddress_id']>0){
            $condition['order_goods.deliverer_id'] = $_GET['daddress_id'];
        }

        //配送方式
        if($_GET['delivery_type_id']>0){
            $daddress_list = Model('peisong')->where(array('id' => $_GET['delivery_type_id']))->field('deliever_id')->find();
            $daddress_ids = $daddress_list['deliever_id'];
            $condition['order_goods.deliverer_id'] = array('in',$daddress_ids);
        }
        // if($_GET['delivery_type_id']>0){
        //     $daddress_list = Model('peisong_deliever')->where(array('pei_id' => $_GET['delivery_type_id']))->field('deliever_id')->select();
        //     $daddress_ids = array();
        //     foreach ($daddress_list as $key => $value) {
        //         $daddress_ids[] = $value['deliever_id'];
        //     }
        //     $condition['order_goods.deliverer_id'] = array('in',$daddress_ids);
        // }

        //自提地址
        if($_GET['address_id']>0){
            $condition['order_common.reciver_ziti_id'] = $_GET['address_id'];
            $ziti_info = Model('ziti_address')->where(array('address_id'=>$_GET['address_id']))->field('seller_name')->find();
            $ziti_name = $ziti_info['seller_name'];
        }else{
            $ziti_name = '全部';
        }

        $if_start_time_pay = $_GET['query_start_date_pay2'];
        $if_end_time_pay = $_GET['query_end_date_pay2'];
        $start_unixtime_pay_1 = $if_start_time_pay ? strtotime($_GET['query_start_date_pay2']) : null;
        $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_date_pay2']) : null;
        $start_unixtime_pay = strtotime('-10 days',$end_unixtime_pay);
        if ($start_unixtime_pay || $end_unixtime_pay) {
            $condition['order.payment_time'] = array('between', array($start_unixtime_pay, $end_unixtime_pay));
        }

        $data = $model_order->getOrderGoodsExportList($condition,'20000',$goods_serial);
        
        foreach($data as $kk=>$vv){
            if($vv['is_zorder']==0 && in_array($vv['order_state'],array('20','30','40'))){
                unset($data[$kk]);
            }
        }
        $data = array_values($data);
        $sum = 0;
        $limit = array();
        foreach ($data as $key => $value) {
            $sum += $data[$key]['order_goods_count'];
            if ($sum > 1000) {
                $limit[] = $key - 1;
                $sum = $data[$key]['order_goods_count'];
            }
        }
        // if (count($limit) > 0) {
        //     array_push($limit, count($data) - 1);
        //     if ($_GET['curpage'] == 1) {
        //         $data = array_slice($data, 0, $limit[$_GET['curpage'] - 1] + 1);//echo '<pre>';
        //         $this->excel_order_sub($data);
        //     } elseif ($_GET['curpage'] > 1) {
        //         $data = array_slice($data, $limit[$_GET['curpage'] - 2] + 1, $limit[$_GET['curpage'] - 1] - $limit[$_GET['curpage'] - 2]);
        //         $this->excel_order_sub($data);
        //     }
        //     foreach ($limit as $k => $v) {
        //         $l1 = $k == 0 ? 1 : $limit[$k - 1] + 1;
        //         $l2 = $v;
        //         $array[$k + 1] = $l1 . ' ~ ' . $l2;
        //     }
        //     Tpl::output('list', $array);
        //     Tpl::output('murl', 'index.php?act=order&op=index');
        //     Tpl::showpage('store_export.excel');
        // } else {
            $this->excel_order_sub($data,$type,$ziti_name,$sum,$start_unixtime_pay_1,$end_unixtime_pay);
        // }
    }

    private function excel_order_sub($data_tmp,$type,$ziti_name,$sum,$start_time,$end_time)
    {
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q');
        $tableheader = array('订单号', '收货人', '发货人', '商品数量', '商品名称', '收货人电话', '详细地址', '买家','支付时间','完成时间','送货时间','买家留言','发货备注','订单状态','备注(退款信息)','促销信息','商品总成本');
        // $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV','AW','AX','AY','AZ','BA','BB','BC','BD','BE','BF','BG','BH');
        // $tableheader = array('订单号','总单号', '商品名称', '规格型号', '商品净重', '商品一级分类', '商品分类', '商品数量', '发货人', '收货人姓名', '收货人地址', '', '', '收货地址', '收货人电话', '子订单号', '店铺', '买家', '订单来源', '下单时间', ' 支付时间', '    完成时间    ','送货时间', '商品货号 ','商品条码', '商品单价', ' 商品成本', '商品总成本', '单价税金', '商品总价', '总税金', '运费', '预存款支付金额', '充值卡支付金额', '优惠券优惠', '实际支付金额', '订单总额', '支付方式 ', '发货人姓名', '身份证号', '发货时间', '买家留言', '发货备注   ', '商品模式    ', '交易流水号', '订单状态', '退款金额','退款完成时间','商家处理状态','平台确认','商家意见','管理员意见','退款原因', '备注', '运单号', '促销信息', '代金券','分享人','分享公司','佣金比例');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
            $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
        }
           $styleThinBlackBorderOutline = array(
        'borders' => array(
            'allborders' => array( //设置全部边框
                'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
            ),

        ),
    );
         $x=$sum+1;
         // var_dump($sum);die;
        $z='Q'.$x;
        $w='A1';
        // var_dump($z);die;
    $excel->getActiveSheet()->getStyle($w.':'.$z)->applyFromArray($styleThinBlackBorderOutline);
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
                            $state = strip_tags(orderState($order_info));;
                            $refund_amount = '0.00';
                        }
                    }
                } else if ($order_info['refund_state'] == '2') {
                    $state = '已关闭';
                    $refund_amount = $order_info['refund_amount'];
                } else {
                    $state = strip_tags(orderState($order_info));
                    $refund_amount = '0.00';
                }
                if(!empty($goodsid)&&is_array($goodsid)){
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
          }else{
                     $refund_time='';
                    $seller_state = '';
                    $admin_state ='';
                    $seller_message = '';
                    $admin_message='';
                    $buyer_message ='';
                       $beizhu = ' ';
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
                //时间格式处理
               $time_1 = date('n.j',$end_time);
               //var_dump($time_1);die;
                //截至时间、开始时间、当前时间
                // if (strstr($order_info['extend_order_goods'][$ii]['goods_name'],'【预售') && !strstr($order_info['extend_order_goods'][$ii]['goods_name'],'【预售'.$time_1)) {
                //     continue;
                // }elseif ($order_type == '阶梯价' && date('m-d', time()) != date('m-d', $order_info['ziti_ladder_time'])) {
                //     continue;
                // }elseif ($start_time>$order_info['payment_time']) {
                //     continue;//判断可优化
                // }
                if ($order_info['payment_time'] < $start_time) {
                    if(!strstr($order_info['extend_order_goods'][$ii]['goods_name'],'【预售'.$time_1)){
                        if ($order_type != "阶梯价" || date('m-d', $end_time) != date('m-d', $order_info['ziti_ladder_time'])) {
                            continue;
                        }
                    }
                }else{
                    if(strstr($order_info['extend_order_goods'][$ii]['goods_name'],'【预售') && !strstr($order_info['extend_order_goods'][$ii]['goods_name'],'【预售'.$time_1)){
                        continue;
                    }elseif($order_type == '阶梯价' && !empty($order_info['ziti_ladder_time']) && date('m-d', $end_time) != date('m-d', $order_info['ziti_ladder_time'])) {
                            continue;
                    }
                }
                if ($state == '待发货') {
                    $order_data_temp = [
                    'order_sn'=>$ii == 0 ? $order_info['order_sn'] : ' ',
                    'reciver_name'=>$order_info['reciver_name'],
                    'storage'=>$order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']?Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']))['seller_name']:Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['deliverer_id']))['seller_name'],
                    'goods_num'=>$order_info['extend_order_goods'][$ii]['goods_num'],
                    'goods_name'=>$order_info['extend_order_goods'][$ii]['goods_name'],
                    'reciver_phone'=>$reciver_info['phone'],
                    'recive_address'=>$jie,//省，市，区，街
                    'buyer'=>$ii == 0 ? $order_info['buyer_name'] : ' ',
                    'pay_time'=>$order_info['payment_time'] != 0 ? date('Y-m-d H:i:s', $order_info['payment_time']) : ' ',
                    'complete_time'=>$order_info['finnshed_time'] != 0 ? date('Y-m-d H:i:s', $order_info['finnshed_time']) : ' ',
                    'ziti_time'=>$order_info['ziti_ladder_time'] != 0 ? date('Y-m-d H:i:s', $order_info['ziti_ladder_time']) : ' ',
                    'order_message'=>$order_info['order_message'] != '' ? $order_info['order_message'] : ' ',
                    'deliver_explain'=>$order_info['deliver_explain'] != '' ? $order_info['deliver_explain'] : ' ',
                    'order_state'=>$state,
                    'beizhu'=>$beizhu,
                    'order_type'=>$order_info['add_time']<1618931572?goodsTypeName($order_info['order_type']):goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
                    //'buyer_message'=>$buyer_message,
                    'goods_costall'=>number_format($order_info['extend_order_goods'][$ii]['goods_cost_price'] * $order_info['extend_order_goods'][$ii]['goods_num'],2),
                    ];
                    $order_data_0[] = $order_data_temp;
                    $order_data_1[] = $order_data_temp;
                }elseif (strstr($state,'退款') || strstr($state,'已关闭')) {
                    $order_data_temp = [
                    'order_sn'=>$ii == 0 ? $order_info['order_sn'] : ' ',
                    'reciver_name'=>$order_info['reciver_name'],
                    'storage'=>$order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']?Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']))['seller_name']:Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['deliverer_id']))['seller_name'],
                    'goods_num'=>$order_info['extend_order_goods'][$ii]['goods_num'],
                    'goods_name'=>$order_info['extend_order_goods'][$ii]['goods_name'],
                    'reciver_phone'=>$reciver_info['phone'],
                    'recive_address'=>$jie,//省，市，区，街
                    'buyer'=>$ii == 0 ? $order_info['buyer_name'] : ' ',
                    'pay_time'=>$order_info['payment_time'] != 0 ? date('Y-m-d H:i:s', $order_info['payment_time']) : ' ',
                    'complete_time'=>$order_info['finnshed_time'] != 0 ? date('Y-m-d H:i:s', $order_info['finnshed_time']) : ' ',
                    'ziti_time'=>$order_info['ziti_ladder_time'] != 0 ? date('Y-m-d H:i:s', $order_info['ziti_ladder_time']) : ' ',
                    'order_message'=>$order_info['order_message'] != '' ? $order_info['order_message'] : ' ',
                    'deliver_explain'=>$order_info['deliver_explain'] != '' ? $order_info['deliver_explain'] : ' ',
                    'order_state'=>$state,
                    'beizhu'=>$beizhu,
                    'order_type'=>$order_info['add_time']<1618931572?goodsTypeName($order_info['order_type']):goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
                    //'buyer_message'=>$buyer_message,
                    'goods_costall'=>number_format($order_info['extend_order_goods'][$ii]['goods_cost_price'] * $order_info['extend_order_goods'][$ii]['goods_num'],2),
                    ];
                    $order_data_0[] = $order_data_temp;
                    $order_data_2[] = $order_data_temp;
                }else{
                    $order_data_0[] = [
                    'order_sn'=>$ii == 0 ? $order_info['order_sn'] : ' ',
                    'reciver_name'=>$order_info['reciver_name'],
                    'storage'=>$order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']?Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']))['seller_name']:Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['deliverer_id']))['seller_name'],
                    'goods_num'=>$order_info['extend_order_goods'][$ii]['goods_num'],
                    'goods_name'=>$order_info['extend_order_goods'][$ii]['goods_name'],
                    'reciver_phone'=>$reciver_info['phone'],
                    'recive_address'=>$jie,//省，市，区，街
                    'buyer'=>$ii == 0 ? $order_info['buyer_name'] : ' ',
                    'pay_time'=>$order_info['payment_time'] != 0 ? date('Y-m-d H:i:s', $order_info['payment_time']) : ' ',
                    'complete_time'=>$order_info['finnshed_time'] != 0 ? date('Y-m-d H:i:s', $order_info['finnshed_time']) : ' ',
                    'ziti_time'=>$order_info['ziti_ladder_time'] != 0 ? date('Y-m-d H:i:s', $order_info['ziti_ladder_time']) : ' ',
                    'order_message'=>$order_info['order_message'] != '' ? $order_info['order_message'] : ' ',
                    'deliver_explain'=>$order_info['deliver_explain'] != '' ? $order_info['deliver_explain'] : ' ',
                    'order_state'=>$state,
                    'beizhu'=>$beizhu,
                    'order_type'=>$order_info['add_time']<1618931572?goodsTypeName($order_info['order_type']):goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
                    //'buyer_message'=>$buyer_message,
                    'goods_costall'=>number_format($order_info['extend_order_goods'][$ii]['goods_cost_price'] * $order_info['extend_order_goods'][$ii]['goods_num'],2),
                    ];
                }
                // $order_data[] = [
                //     'order_sn'=>$ii == 0 ? $order_info['order_sn'] : ' ',
                //     'reciver_name'=>$order_info['reciver_name'],
                //     'storage'=>$order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']?Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']))['seller_name']:Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['deliverer_id']))['seller_name'],
                //     'goods_num'=>$order_info['extend_order_goods'][$ii]['goods_num'],
                //     'goods_name'=>$order_info['extend_order_goods'][$ii]['goods_name'],
                //     'reciver_phone'=>$reciver_info['phone'],
                //     'recive_address'=>$jie,//省，市，区，街
                //     'buyer'=>$ii == 0 ? $order_info['buyer_name'] : ' ',
                //     'pay_time'=>$order_info['payment_time'] != 0 ? date('Y-m-d H:i:s', $order_info['payment_time']) : ' ',
                //     'complete_time'=>$order_info['finnshed_time'] != 0 ? date('Y-m-d H:i:s', $order_info['finnshed_time']) : ' ',
                //     'ziti_time'=>$order_info['ziti_ladder_time'] != 0 ? date('Y-m-d H:i:s', $order_info['ziti_ladder_time']) : ' ',
                //     'order_message'=>$order_info['order_message'] != '' ? $order_info['order_message'] : ' ',
                //     'deliver_explain'=>$order_info['deliver_explain'] != '' ? $order_info['deliver_explain'] : ' ',
                //     'order_state'=>$state,
                //     'beizhu'=>$beizhu,
                //     'order_type'=>$order_info['add_time']<1618931572?goodsTypeName($order_info['order_type']):goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
                //     //'buyer_message'=>$buyer_message,
                // ];
                // $order_data[] = [
                //     'order_sn'=>$ii == 0 ? $order_info['order_sn'] : ' ',
                //     'z_order_sn'=>$ii == 0 ? $order_info['z_order_sn'] : ' ',
                //     'goods_name'=>$order_info['extend_order_goods'][$ii]['goods_name'],
                //     'goods_spec'=>unserialize($order_info['extend_order_goods'][$ii]['goods_spec']) ? array_values(unserialize($order_info['extend_order_goods'][$ii]['goods_spec']))[0] : ' ',
                //     'goods_weight'=>$order_info['extend_order_goods'][$ii]['goods_weight'] ? $order_info['extend_order_goods'][$ii]['goods_weight'] . 'kg' : ' ',
                //     'goods_class1'=> $goods_classname,
                //     'goods_class'=>Model('goods_class')->getGoodsClassInfoById($order_info['extend_order_goods'][$ii]['gc_id'])['gc_name'] ? Model('goods_class')->getGoodsClassInfoById($order_info['extend_order_goods'][$ii]['gc_id'])['gc_name'] : ' ',
                //     'goods_num'=>$order_info['extend_order_goods'][$ii]['goods_num'],
                //     'storage'=>$order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']?Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']))['seller_name']:Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['deliverer_id']))['seller_name'],
                //     'reciver_name'=>$order_info['reciver_name'],
                //     'reciver_address1'=>$sheng, 'reciver_address2'=>$shi, 'reciver_address3'=>$qu, 'recive_address'=>$jie,//省，市，区，街
                //     'reciver_phone'=>$reciver_info['phone'],
                //     'order_sub_id'=>$order_info['order_sn'] . $order_info['extend_order_goods'][$ii]['goods_id'],
                //     'store'=>$ii == 0 ? $order_info['store_name'] : ' ',
                //     'buyer'=>$ii == 0 ? $order_info['buyer_name'] : ' ',
                //     'order_from'=>'微信小程序',
                //     'add_time'=>date('Y-m-d H:i:s', $order_info['add_time']),
                //     'pay_time'=>$order_info['payment_time'] != 0 ? date('Y-m-d H:i:s', $order_info['payment_time']) : ' ',
                //     'complete_time'=>$order_info['finnshed_time'] != 0 ? date('Y-m-d H:i:s', $order_info['finnshed_time']) : ' ',
                //     'ziti_time'=>$order_info['ziti_ladder_time'] != 0 ? date('Y-m-d H:i:s', $order_info['ziti_ladder_time']) : ' ',
                //     'goods_serial'=>$order_info['extend_order_goods'][$ii]['order_goods_serial']? $order_info['extend_order_goods'][$ii]['order_goods_serial']: $order_info['extend_order_goods'][$ii]['goods_serial'],
                //     'goods_barcode'=>$order_info['extend_order_goods'][$ii]['order_goods_barcode']? $order_info['extend_order_goods'][$ii]['order_goods_barcode']: $order_info['extend_order_goods'][$ii]['goods_barcode'],
                //     'goods_price'=>$order_info['extend_order_goods'][$ii]['goods_price'],
                //     'goods_costprice'=>$order_info['extend_order_goods'][$ii]['goods_cost_price'],
                //     'goods_costall'=>number_format($order_info['extend_order_goods'][$ii]['goods_cost_price'] * $order_info['extend_order_goods'][$ii]['goods_num'],2),
                //     'goods_tax'=>'0.00',//税金暂定0
                //     'goods_priceall'=>number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'], 2),
                //     'goods_taxall'=>'0.00',//总税金暂定0
                //     'deliver_fee'=>$order_info['shipping_fee'],
                //     'pd_amount'=>$order_info['pd_amount'],
                //     'rcb_amount'=>$order_info['rcb_amount'],
                //     'voucher_price'=>$yhqzf,
                //     'pay_amount'=>$sjzf,
                //     'order_amount'=>$ii == 0 ? $order_info['order_amount'] : ' ',
                //     'pay_type'=>orderPaymentName($order_info['payment_code']),
                //     'deliver_name'=>' ',//发货人姓名暂定空
                //     'id_card'=>str_replace(" ", "1", $reciver_info['id_card']),
                //     'deliver_time'=>$order_info['shipping_time'] != 0 ? date('Y-m-d H:i:s', $order_info['shipping_time']) : ' ',
                //     'order_message'=>$order_info['order_message'] != '' ? $order_info['order_message'] : ' ',
                //     'deliver_explain'=>$order_info['deliver_explain'] != '' ? $order_info['deliver_explain'] : ' ',
                //     'is_mode'=>$is_mode,
                //     'pay_sn'=>$ii == 0 ? explode(' ', $model_order_log->where(array('order_id' => $order_info['order_id'], 'log_msg' => array('like', '%支付平台交易号%')))->select()[0]['log_msg'])[4] : ' ',
                //     'order_state'=>$state,
                //     'refund_amount'=>$refund_amount,
                //     'refund_time'=>$refund_time,
                //     'seller_state'=>$seller_state,
                //     'admin_state'=>$admin_state,
                //     'seller_message'=>$seller_message,
                //     'admin_message'=>$admin_message,
                //     'buyer_message'=>$buyer_message,
                //     'beizhu'=>$beizhu,
                //     'waybill'=>$ii == 0 ? $order_info['shipping_code'] : ' ',
                //     //'order_type'=>goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
                //     'order_type'=>$order_info['add_time']<1618931572?goodsTypeName($order_info['order_type']):goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
                //     'voucher'=>$vou,
                //     'share_name' => $order_info['share_name'],
                //     'company_name' => $order_info['company_name'],
                //     'commis_rate'=>$order_info['extend_order_goods'][$ii]['commis_rate'].'%',
                // ];
            }
            unset($data_tmp[$key]);
        }
        if ($type == 1) {
            $order_data = $order_data_1;
            $extend_name = '待发货订单';
        }elseif ($type == 2) {
            $order_data = $order_data_2;
            $extend_name = '退款订单';
        }else{
            $order_data = $order_data_0;
            $extend_name = '订单';
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
        $filename = date('m.d', time()).$ziti_name.$extend_name.'.xls';
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
    // private function excel_order_sub($data_tmp)
    // {
    //     $excel = new PHPExcel();
    //     //$letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV','AW','AX','AY','AZ','BA','BB','BC','BD','BE','BF');
    //     //$tableheader = array('订单号','总单号', '商品名称', '规格型号', '商品净重', '商品一级分类', '商品分类', '商品数量', '发货人', '收货人姓名', '收货人地址', '', '', '收货地址', '收货人电话', '子订单号', '店铺', '买家', '订单来源', '下单时间', '   支付时间', '    完成时间    ', '商品货号    ', '商品单价', '    商品成本', '商品总成本', '单价税金', '商品总价', '总税金', '运费', '预存款支付金额', '充值卡支付金额', '优惠券优惠', '实际支付金额', '订单总额', '支付方式 ', '发货人姓名', '身份证号', '发货时间', '买家留言', '发货备注   ', '商品模式    ', '交易流水号', '订单状态', '退款金额','退款完成时间','商家处理状态','平台确认','商家意见','管理员意见','退款原因', '备注', '运单号', '促销信息', '代金券','分享人','分享公司','佣金比例');
    //     $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV','AW','AX','AY','AZ','BA','BB','BC','BD','BE','BF','BG','BH');
    //     $tableheader = array('订单号','总单号', '商品名称', '规格型号', '商品净重', '商品一级分类', '商品分类', '商品数量', '发货人', '收货人姓名', '收货人地址', '', '', '收货地址', '收货人电话', '子订单号', '店铺', '买家', '订单来源', '下单时间', ' 支付时间', '    完成时间    ','送货时间', '商品货号 ','商品条码', '商品单价', ' 商品成本', '商品总成本', '单价税金', '商品总价', '总税金', '运费', '预存款支付金额', '充值卡支付金额', '优惠券优惠', '实际支付金额', '订单总额', '支付方式 ', '发货人姓名', '身份证号', '发货时间', '买家留言', '发货备注   ', '商品模式    ', '交易流水号', '订单状态', '退款金额','退款完成时间','商家处理状态','平台确认','商家意见','管理员意见','退款原因', '备注', '运单号', '促销信息', '代金券','分享人','分享公司','佣金比例');
    //     for ($i = 0; $i < count($tableheader); $i++) {
    //         $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
    //         $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
    //     }

    //     $model_order_log = Model('order_log');
    //     foreach ($data_tmp as $key => $order_info) {
    //         for ($ii = 0; $ii < count($order_info['extend_order_goods']); $ii++) {
    //             $reciver_info = unserialize($order_info['reciver_info']);
    //             $address = $reciver_info['area'];
    //             $street = $reciver_info['street'];
    //             $arr_str = explode(" ", preg_replace('#\s+#', ' ', trim($address)));
    //             if (!empty($arr_str)) {
    //                 $sheng = $arr_str[0] . '省';
    //                 $shi = $arr_str[1];
    //                 $qu = $arr_str[2];
    //                 $jie = $street;
    //             } else {
    //                 $sheng = ' ';
    //                 $shi = ' ';
    //                 $qu = ' ';
    //                 $jie = ' ';
    //             }
    //              $model_class = Model('goods_class');
    //               $goods_class1 = $model_class->getGoodsClassInfoById($order_info['extend_order_goods'][$ii]['gc_id']);//第一级商品分类
    //               $goods_class2 = $model_class->getGoodsClassInfoById($goods_class1['gc_parent_id']);
    //               $goods_class3 = $model_class->getGoodsClassInfoById($goods_class2['gc_parent_id']);
    //                $goods_classname=$goods_class3['gc_name'];
    //             if ($ii == 0) {
    //                 if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
    //                     $yhqzf = $order_info['extend_order_goods'][$ii]['voucher_price'];
    //                 } else {
    //                     $yhqzf = $order_info['voucher_price'];
    //                 }
    //                 if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
    //                     $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'] - $order_info['extend_order_goods'][$ii]['voucher_price'], 2);
    //                 } else {
    //                     $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'], 2);
    //                 }
    //             } else {
    //                 if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
    //                     $yhqzf = $order_info['extend_order_goods'][$ii]['voucher_price'];
    //                 } else {
    //                     $yhqzf = 0.00;
    //                 }
    //                 if ($order_info['extend_order_goods'][$ii]['voucher_price'] > 0) {
    //                     $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'] - $order_info['extend_order_goods'][$ii]['voucher_price'], 2);
    //                 } else {
    //                     $sjzf = number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'], 2);
    //                 }
    //             }

    //             if ($order_info['is_mode'] == 0) {
    //                 $is_mode = '一般贸易';
    //             } elseif ($order_info['is_mode'] == 1) {
    //                 $is_mode = '备货模式';
    //             } elseif ($order_info['is_mode'] == 2) {
    //                 $is_mode = '集货模式';
    //             }

    //             $model_refund_return = Model('refund_return');
    //             //部分退款与全部退款 
    //             $goodsid = $model_refund_return->getRefundReturnList(array('order_id' => $order_info['order_id']));
    //             if ($order_info['refund_state'] == '1') {
                   
    //                 foreach ($goodsid as $key => $vv) {
    //                     if ($order_info['extend_order_goods'][$ii]['goods_id'] == $vv['goods_id']) {
    //                          $state = '部分退款';
    //                         $refund_amount = $vv['refund_amount'];
    //                     }else{
    //                         $state = strip_tags(orderState($order_info));;
    //                         $refund_amount = '0.00';
    //                     }
    //                 }
    //             } else if ($order_info['refund_state'] == '2') {
    //                 $state = '已关闭';
    //                 $refund_amount = $order_info['refund_amount'];
    //             } else {
    //                 $state = strip_tags(orderState($order_info));
    //                 $refund_amount = '0.00';
    //             }
    //             if(!empty($goodsid)&&is_array($goodsid)){
    //                 foreach ($goodsid as $key => $vv) {
    //              if($order_info['extend_order_goods'][$ii]['goods_id'] == $vv['goods_id']){
    //                  //备注
    //             $result = $model_refund_return->getRefundReturnList(array('order_id'=>$order_info['order_id']));
    //             //退款时间
    //              if($result[0]['admin_time']>0){
    //                     $refund_time=date('Y-m-d H:i:s',$result[0]['admin_time']) ;
    //                 }else{
    //                      $refund_time='无';
    //                 }
    //                 if($result[0]['seller_state']=='1'){
    //                     $seller_state = '待审核';
    //                 }else if($result[0]['seller_state']=='2'){
    //                     $seller_state= '同意';
    //                 }else if($result[0]['seller_state']=='3'){
    //                     $seller_state = '不同意';
    //                 }else{
    //                     $seller_state = '';
    //                 }
    //                 if($result[0]['seller_state']=='2'){
    //                     if($result[0]['refund_state']=='1'){
    //                         $admin_state = '处理中';
    //                     }else if($result[0]['refund_state']=='2'){
    //                         $admin_state = '待管理员处理';
    //                     }else if($result[0]['refund_state']=='3'){
    //                         $admin_state ='已完成';
    //                     }else{
    //                         $admin_state ='无';
    //                     }
    //                 }else{
    //                     $admin_state ='无';
    //                 }
    //                 $seller_message = $result[0]['seller_message'];
    //                 $admin_message= $result[0]['admin_message'];
    //                  $buyer_message =$result[0]['reason_info'];
    //             if ($result) {
    //                 if ($result[0]['refund_type'] == 1) {
    //                     if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
    //                         $beizhu = '退款中'; //退款中
    //                     } else if ($result[0]['refund_state'] == 3) {
    //                         if ($result[0]['seller_state'] == 2) {
    //                             $beizhu = '退款完成'; //退款完成
    //                         } else if ($result[0]['seller_state'] == 3) {
    //                             $beizhu = '退款失败'; //退款失败
    //                         }
    //                     } else {
    //                         $beizhu = '';
    //                     }
    //                 } elseif ($result[0]['refund_type'] == 2) {
    //                     if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
    //                         $beizhu = '退款退货中';//退款退货中
    //                     } else if ($result[0]['refund_state'] == 3) {
    //                         $beizhu = '退款退货完成'; //退款退货完成
    //                     } else {
    //                         $beizhu = ' ';
    //                     }
    //                 }
    //             } else {
    //                 $beizhu = '';
    //             }
    //                 break;
    //              }else if($vv['goods_id']==0){
    //                      //备注
    //                 $result = $model_refund_return->getRefundReturnList(array('order_id'=>$order_info['order_id']));
    //                 //退款时间
    //                  if($result[0]['admin_time']>0){
    //                         $refund_time=date('Y-m-d H:i:s',$result[0]['admin_time']) ;
    //                     }else{
    //                          $refund_time='无';
    //                     }
    //                     if($result[0]['seller_state']=='1'){
    //                         $seller_state = '待审核';
    //                     }else if($result[0]['seller_state']=='2'){
    //                         $seller_state= '同意';
    //                     }else if($result[0]['seller_state']=='3'){
    //                         $seller_state = '不同意';
    //                     }else{
    //                         $seller_state = '';
    //                     }
    //                     if($result[0]['seller_state']=='2'){
    //                         if($result[0]['refund_state']=='1'){
    //                             $admin_state = '处理中';
    //                         }else if($result[0]['refund_state']=='2'){
    //                             $admin_state = '待管理员处理';
    //                         }else if($result[0]['refund_state']=='3'){
    //                             $admin_state ='已完成';
    //                         }else{
    //                             $admin_state ='无';
    //                         }
    //                     }else{
    //                         $admin_state ='无';
    //                     }
    //                     $seller_message = $result[0]['seller_message'];
    //                     $admin_message= $result[0]['admin_message'];
    //                      $buyer_message =$result[0]['reason_info'];
    //                 if ($result) {
    //                     if ($result[0]['refund_type'] == 1) {
    //                         if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
    //                             $beizhu = '退款中'; //退款中
    //                         } else if ($result[0]['refund_state'] == 3) {
    //                             if ($result[0]['seller_state'] == 2) {
    //                                 $beizhu = '退款完成'; //退款完成
    //                             } else if ($result[0]['seller_state'] == 3) {
    //                                 $beizhu = '退款失败'; //退款失败
    //                             }
    //                         } else {
    //                             $beizhu = '';
    //                         }
    //                     } elseif ($result[0]['refund_type'] == 2) {
    //                         if ($result[0]['refund_state'] == 1 || $result[0]['refund_state'] == 2) {
    //                             $beizhu = '退款退货中';//退款退货中
    //                         } else if ($result[0]['refund_state'] == 3) {
    //                             $beizhu = '退款退货完成'; //退款退货完成
    //                         } else {
    //                             $beizhu = ' ';
    //                         }
    //                     }
    //                 } else {
    //                     $beizhu = '';
    //                 }

    //              }else{
    //                  $refund_time='';
    //                 $seller_state = '';
    //                 $admin_state ='';
    //                 $seller_message = '';
    //                 $admin_message='';
    //                 $buyer_message ='';
    //                    $beizhu = ' ';
    //              }
    //           }
    //       }else{
    //                  $refund_time='';
    //                 $seller_state = '';
    //                 $admin_state ='';
    //                 $seller_message = '';
    //                 $admin_message='';
    //                 $buyer_message ='';
    //                    $beizhu = ' ';
    //       }
              
               

    //             if ($order_info['order_type'] == 0) {
    //                 $order_type = '无活动';
    //             } elseif ($order_info['order_type'] == 1) {
    //                 $order_type = '阶梯价';
    //             } elseif ($order_info['order_type'] == 2) {
    //                 $order_type = '团购';
    //             } elseif ($order_info['order_type'] == 3) {
    //                 $order_type = '新人专享';
    //             } elseif ($order_info['order_type'] == 4) {
    //                 $order_type = '限时秒杀';
    //             } elseif ($order_info['order_type'] == 5) {
    //                 $order_type = '即买即送';
    //             }

    //             $voucher = unserialize($order_info['voucher_code']);
    //             if (!empty($voucher)) {
    //                 foreach ($voucher as $voucherk => $voucherv) {
    //                     if (!empty($voucherv['voucher_code'])) {
    //                         $voucher_code = $voucherv['voucher_code'];
    //                         $voucher_name = Model('voucher')->getVoucherInfo(array('voucher_code'=>$voucher_code),'voucher_title');
    //                         $vou = $voucher_name['voucher_title'];
    //                     } else {
    //                         $vou = ' ';
    //                     }
    //                 }
    //             } else {
    //                 $vou = ' ';
    //             }

    //             $order_data[] = [
    //                 'order_sn'=>$ii == 0 ? $order_info['order_sn'] : ' ',
    //                 'z_order_sn'=>$ii == 0 ? $order_info['z_order_sn'] : ' ',
    //                 'goods_name'=>$order_info['extend_order_goods'][$ii]['goods_name'],
    //                 'goods_spec'=>unserialize($order_info['extend_order_goods'][$ii]['goods_spec']) ? array_values(unserialize($order_info['extend_order_goods'][$ii]['goods_spec']))[0] : ' ',
    //                 'goods_weight'=>$order_info['extend_order_goods'][$ii]['goods_weight'] ? $order_info['extend_order_goods'][$ii]['goods_weight'] . 'kg' : ' ',
    //                 'goods_class1'=> $goods_classname,
    //                 'goods_class'=>Model('goods_class')->getGoodsClassInfoById($order_info['extend_order_goods'][$ii]['gc_id'])['gc_name'] ? Model('goods_class')->getGoodsClassInfoById($order_info['extend_order_goods'][$ii]['gc_id'])['gc_name'] : ' ',
    //                 'goods_num'=>$order_info['extend_order_goods'][$ii]['goods_num'],
    //                 'storage'=>$order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']?Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']))['seller_name']:Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['deliverer_id']))['seller_name'],
    //                 'reciver_name'=>$order_info['reciver_name'],
    //                 'reciver_address1'=>$sheng, 'reciver_address2'=>$shi, 'reciver_address3'=>$qu, 'recive_address'=>$jie,//省，市，区，街
    //                 'reciver_phone'=>$reciver_info['phone'],
    //                 'order_sub_id'=>$order_info['order_sn'] . $order_info['extend_order_goods'][$ii]['goods_id'],
    //                 'store'=>$ii == 0 ? $order_info['store_name'] : ' ',
    //                 'buyer'=>$ii == 0 ? $order_info['buyer_name'] : ' ',
    //                 'order_from'=>'微信小程序',
    //                 'add_time'=>date('Y-m-d H:i:s', $order_info['add_time']),
    //                 'pay_time'=>$order_info['payment_time'] != 0 ? date('Y-m-d H:i:s', $order_info['payment_time']) : ' ',
    //                 'complete_time'=>$order_info['finnshed_time'] != 0 ? date('Y-m-d H:i:s', $order_info['finnshed_time']) : ' ',
    //                 'ziti_time'=>$order_info['ziti_ladder_time'] != 0 ? date('Y-m-d H:i:s', $order_info['ziti_ladder_time']) : ' ',
    //                 'goods_serial'=>$order_info['extend_order_goods'][$ii]['order_goods_serial']? $order_info['extend_order_goods'][$ii]['order_goods_serial']: $order_info['extend_order_goods'][$ii]['goods_serial'],
    //                 'goods_barcode'=>$order_info['extend_order_goods'][$ii]['order_goods_barcode']? $order_info['extend_order_goods'][$ii]['order_goods_barcode']: $order_info['extend_order_goods'][$ii]['goods_barcode'],
    //                 'goods_price'=>$order_info['extend_order_goods'][$ii]['goods_price'],
    //                 'goods_costprice'=>$order_info['extend_order_goods'][$ii]['goods_cost_price'],
    //                 'goods_costall'=>number_format($order_info['extend_order_goods'][$ii]['goods_cost_price'] * $order_info['extend_order_goods'][$ii]['goods_num'],2),
    //                 'goods_tax'=>'0.00',//税金暂定0
    //                 'goods_priceall'=>number_format($order_info['extend_order_goods'][$ii]['goods_price'] * $order_info['extend_order_goods'][$ii]['goods_num'], 2),
    //                 'goods_taxall'=>'0.00',//总税金暂定0
    //                 'deliver_fee'=>$order_info['shipping_fee'],
    //                 'pd_amount'=>$order_info['pd_amount'],
    //                 'rcb_amount'=>$order_info['rcb_amount'],
    //                 'voucher_price'=>$yhqzf,
    //                 'pay_amount'=>$sjzf,
    //                 'order_amount'=>$ii == 0 ? $order_info['order_amount'] : ' ',
    //                 'pay_type'=>orderPaymentName($order_info['payment_code']),
    //                 'deliver_name'=>' ',//发货人姓名暂定空
    //                 'id_card'=>str_replace(" ", "1", $reciver_info['id_card']),
    //                 'deliver_time'=>$order_info['shipping_time'] != 0 ? date('Y-m-d H:i:s', $order_info['shipping_time']) : ' ',
    //                 'order_message'=>$order_info['order_message'] != '' ? $order_info['order_message'] : ' ',
    //                 'deliver_explain'=>$order_info['deliver_explain'] != '' ? $order_info['deliver_explain'] : ' ',
    //                 'is_mode'=>$is_mode,
    //                 'pay_sn'=>$ii == 0 ? explode(' ', $model_order_log->where(array('order_id' => $order_info['order_id'], 'log_msg' => array('like', '%支付平台交易号%')))->select()[0]['log_msg'])[4] : ' ',
    //                 'order_state'=>$state,
    //                 'refund_amount'=>$refund_amount,
    //                 'refund_time'=>$refund_time,
    //                 'seller_state'=>$seller_state,
    //                 'admin_state'=>$admin_state,
    //                 'seller_message'=>$seller_message,
    //                 'admin_message'=>$admin_message,
    //                 'buyer_message'=>$buyer_message,
    //                 'beizhu'=>$beizhu,
    //                 'waybill'=>$ii == 0 ? $order_info['shipping_code'] : ' ',
    //                 //'order_type'=>goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
    //                 'order_type'=>$order_info['add_time']<1618931572?goodsTypeName($order_info['order_type']):goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
    //                 'voucher'=>$vou,
    //                 'share_name' => $order_info['share_name'],
    //                 'company_name' => $order_info['company_name'],
    //                 'commis_rate'=>$order_info['extend_order_goods'][$ii]['commis_rate'].'%',
    //             ];
    //         }
    //         unset($data_tmp[$key]);
    //     }
    //     //填充表格信息
    //     for ($i = 2; $i <= count($order_data) + 1; $i++) {
    //         $j = 0;
    //         foreach ($order_data[$i - 2] as $key => $value) {
    //             $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
    //             $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i","$value",PHPExcel_Cell_DataType::TYPE_STRING);
    //             $j++;
    //         }
    //     }
    //     //创建Excel输入对象
    //     $write = new PHPExcel_Writer_Excel5($excel);
    //     $filename = '子订单-' . date('Y-m-d-H', time()) . '.xls';
    //     header("Pragma: public");
    //     header("Expires: 0");
    //     header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
    //     header("Content-Type:application/force-download");
    //     header("Content-Type:application/vnd.ms-execl");
    //     header("Content-Type:application/octet-stream");
    //     header("Content-Type:application/download");;
    //     header('Content-Disposition:attachment;filename=' . $filename);
    //     header("Content-Transfer-Encoding:binary");
    //     $write->save('php://output');
    //     die;
    // }


    /**
     * 
     *
     */
    public function indexOp()
    {
        //配送方式
        $delivery_type_list = Model('peisong')->where()->field('id,p_name')->select();
        Tpl::output('delivery_type_list', $delivery_type_list);
         //关联发货人
        $daddress_list = Model('daddress')->getAddressList(array('store_id' => $_SESSION['store_id']),'address_id,seller_name');
        Tpl::output('daddress_list', $daddress_list);
       
         //显示自提地址列表(搜索)
         $condition2 = array();
         $model_daddress = Model('ziti_address');
         $address_list = $model_daddress->getAddressList($condition2);
         // var_dump($address_list);die;
         Tpl::output('address_list',$address_list); 
         Tpl::showpage('store_export_order.list');
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
