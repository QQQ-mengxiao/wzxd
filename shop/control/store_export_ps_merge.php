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

class store_export_ps_mergeControl extends BaseSellerControl
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

    public function export_data_mergeplOp(){
        $model_order = Model('order');
        $condition = array();
        $condition['order.order_state'] = 20;

        //发货人姓名
        if($_GET['daddress_id'] > 0){
            $condition['order_goods.deliverer_id'] = $_GET['daddress_id'];
            $daddress_id = $_GET['daddress_id'];
            $name='';
        }
         
        //配送方式
        if($_GET['delivery_type_id'] > 0){
            $daddress_list = Model('peisong')->where(array('id' => $_GET['delivery_type_id']))->find();
            $daddress_ids = explode(',', $daddress_list['deliever_id']);
            $daddress_ids = array_values($daddress_ids);
            $count = count($daddress_ids);
            $date = date('Y-m-dhis', time());
            $dir = BASE_ROOT_PATH . '/excel/goods2/' . $date . '/';

            if (!is_dir ($dir)){
                mkdir($dir, 0777, true); //创建文件夹
            }
            for ($i = 0; $i < $count; $i ++) {
                $condition['order_goods.deliverer_id'] = $daddress_ids[$i];
                $daddress_id = $daddress_ids[$i];
                $name = $daddress_list['p_name'];
                $if_start_time_pay = $_GET['query_start_date_pay2'];
                $if_end_time_pay = $_GET['query_end_date_pay2'];
                $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_date_pay2']) : null;
                $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_date_pay2']) : null;
                if ($start_unixtime_pay || $end_unixtime_pay) {
                    $condition['order.payment_time'] = array('between', array($start_unixtime_pay, $end_unixtime_pay));
                }
                
                $excel = new PHPExcel();
                $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R');
                $order_goods_data_list = $model_order->getOrderGoodsExportList($condition,'20000'); 

                $goods_data_list = $order_goods_data_list; 
                $ordergoods_arr = array();
                
                foreach($goods_data_list as $kk => $vv){
                    if($vv['is_zorder'] == 0 ){
                        unset($goods_data_list[$kk]);
                        continue;
                    }
                    foreach ($vv['extend_order_goods'] as $key => $value) {
                        $model_refund_return = Model('refund_return');
                        $refund_list = $model_refund_return->getRefundReturnList(array('order_id' => $vv['order_id']));
                        if(!empty($refund_list) && is_array($refund_list)){
                            foreach ($refund_list as $key1 => $value1) {
                                if($value1['goods_id'] == 0){
                                    if($value1['seller_state'] < 3){
                                        unset($goods_data_list[$kk]);
                                    }  
                                }else{
                                    if($value1['goods_id'] == $value['goods_id'] && $value1['seller_state'] < 3){
                                        unset($goods_data_list[$kk]['extend_order_goods'][$key]);
                                    }  
                                }
                            }
                        }
                        if($daddress_id > 0){
                            if($value['order_goods_deliverer_id'] != $daddress_ids[$i]){
                                unset($goods_data_list[$kk]['extend_order_goods'][$key]);
                            }
                        } 
                    }
                    $goods_data_list[$kk]['extend_order_goods'] = array_values($goods_data_list[$kk]['extend_order_goods']);
                }
                foreach ($goods_data_list as $k2 => $v2) {
                    foreach ($v2['extend_order_goods'] as $k22 => $v22) {
                        $ordergoods_arr[] = $v22;
                    }
                }
                $data_arr = array();
                foreach ($ordergoods_arr as $k => $v) {
                    $data_arr[$v['goods_id']][] = $v;
                }

                $num_goodsnum = 0;
                $num_costprice = 0;
                $data_excel = array();
                $data_array = array();

                foreach ($data_arr as $ke => $va) {
                    $sumall = 0;
                    $goods_cost_price_all = 0;
                    foreach ($va as $ke1 => $va1) {
                        $sumall = $sumall + $va1['goods_num'];
                        $goods_cost_price_all = $goods_cost_price_all + $va1['goods_cost_price'] * $va1['goods_num'];
                    }
                    $model_goods = Model('goods');
                    $goods_info = $model_goods->getGoodsInfoByID($va[0]['goods_id']);
                    $model_class = Model('goods_class');
                    $goods_class1 = $model_class->getGoodsClassInfoById($goods_info['gc_id_1']);
                    $goods_class2 = $model_class->getGoodsClassInfoById($goods_info['gc_id_2']);
                    $data_array[$ke]['gc_name_1'] = $goods_class1['gc_name'];
                    $data_array[$ke]['goods_id'] = $va[0]['goods_id'];
                    $data_array[$ke]['goods_name'] = $va[0]['goods_name'];
                    $data_array[$ke]['sumall'] = $sumall;
                    $data_array[$ke]['goods_cost_price_all'] = $goods_cost_price_all;
                    $num_costprice = $num_costprice + $goods_cost_price_all;
                    $num_goodsnum = $num_goodsnum + $sumall;
                } 
                $count1 = count($data_array);
                $data_array = array_values($data_array);
                $data_array[$count1]['goods_name'] = '总计';
                $data_array[$count1]['deliverer_name'] = '';
                $data_array[$count1]['goods_id'] = '';
                $data_array[$count1]['gc_name_1'] = '';
                $data_array[$count1]['sumall'] = $num_goodsnum;
                $data_array[$count1]['goods_cost_price_all'] = $num_costprice;
                $data_excel['data_array'] = $data_array;
                $data_excel['time1'] = $end_unixtime_pay;
                
                $data_excel['deliverer_id'] = $daddress_id;
                $data_excel['name'] = $i;
        
                //表格数组
                $model_order = Model('order');
                ini_set('max_execution_time', '0');
                if($daddress_id > 0){
                    $model_daddress = Model('daddress');
                    $address = $model_daddress->getAddressInfo(array('address_id' => $daddress_id));
                    $name = $address['seller_name'];
                }
                $excel->setActiveSheetIndex(0);
                $excel->getActiveSheet()->setTitle("配货订单表");
                $order_goods_data = $this->excel_order_mergepl($excel, $data_excel, $dir);
                //填充表格信息
                for ($iii = 3; $iii <= count($order_goods_data) + 2; $iii++) {
                    $j = 0;
                    foreach ($order_goods_data[$iii - 3] as $key => $value) {
                        $excel->getActiveSheet()->setCellValue("$letter[$j]$iii", "$value");
                        $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$iii", "$value", PHPExcel_Cell_DataType::TYPE_STRING);
                        $j++;
                    }
                }
                $excel->getActiveSheet()->setCellValue('A'.($iii-1), "");
                
                $order_data_list = $order_goods_data_list;//订单数据
                foreach($order_data_list as $kk=>$vv){
                    if($vv['is_zorder'] == 0){
                        unset($order_data_list[$kk]);
                        continue;
                    }
                    foreach ($vv['extend_order_goods'] as $key => $value) { 
                        $model_refund_return = Model('refund_return');
                        $refund_list = $model_refund_return->getRefundReturnList(array('order_id' => $vv['order_id']));
                        if(!empty($refund_list) && is_array( $refund_list)){
                            foreach ($refund_list as $key1 => $value1) {
                                if($value1['goods_id'] == 0){
                                    if($value1['seller_state'] < 3){
                                        unset($order_data_list[$kk]);
                                    }  
                                }else{
                                    if($value1['goods_id'] == $value['goods_id'] && $value1['seller_state'] < 3){
                                        unset($order_data_list[$kk]['extend_order_goods'][$key]);
                                    }  
                                }
                            }
                        }  
                        if($daddress_id > 0){
                            if($value['order_goods_deliverer_id'] != $daddress_ids[$i]){
                                unset($order_data_list[$kk]['extend_order_goods'][$key]);  
                            }
                        }  
                    }
                    $order_data_list[$kk]['extend_order_goods'] = array_values($order_data_list[$kk]['extend_order_goods']);
                }
                $order_data_list = array_values($order_data_list);
                $sum = 0;
                $limit = array();
                foreach ($order_data_list as $key => $value) {
                    $sum += $order_data_list[$key]['order_goods_count'];
                    if ($sum > 1000) {
                        $limit[] = $key - 1;
                        $sum = $order_data_list[$key]['order_goods_count'];
                    }
                }
                if($daddress_id > 0){
                    $model_daddress = Model('daddress');
                    $address = $model_daddress->getAddressInfo(array('address_id' => $daddress_id));
                    $name = '点' . $address['seller_name'];
                }
                $time = date('Y-m-d-H', $end_unixtime_pay);
                $name = $time . $name . '配货订单+商品表.xls';
                $name = iconv('utf-8', 'gb2312', $name);
                $filename = $time . $i . '.xls';
                $data_list[] = $dir . $name;
                $names[] = $name;
                $excel->createSheet(1);
                $excel->setActiveSheetIndex(1);
                $excel->getActiveSheet()->setTitle("配货商品表");
                $excel_order_data = $this->excel_order_sub_mergepl($excel, $order_data_list, $end_unixtime_pay, $daddress_id, $sum, $i, $dir);
                for ($ii = 2; $ii <= count($excel_order_data) + 1; $ii++) {
                    $j = 0;
                    foreach ($excel_order_data[$ii - 2] as $key => $value) {
                        $w = 'A' . $ii;
                        $z = 'S' . $ii;
                        $bubiao=array('5', '6');
                        if(!in_array($daddress_id, $bubiao)){
                            $excel->getActiveSheet()->getStyle($w . ':' . $z)->getFont()->setColor(new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) ); 
                        }
                        $excel->getActiveSheet()->setCellValue("$letter[$j]$ii", "$value");
                        $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$ii", "$value", PHPExcel_Cell_DataType::TYPE_STRING);
                        $j++;
                    }
                }
                $excel->setActiveSheetIndex(0);
                $write = new PHPExcel_Writer_Excel5($excel);
                $write->save($dir.$name);
            }
            //打包
            $zipname= $dir.'excel.zip';
            $files= $data_list;
            $zip = new ZipArchive();
            $res = $zip->open($zipname, ZipArchive::CREATE );
            if ($res== TRUE) {
                foreach ($files as $k => $v) {
                    //这里直接用原文件的名字进行打包，也可以直接命名，需要注意如果文件名字一样会导致后面文件覆盖前面的文件，所以建议重新命名
                    $value = explode("/", $v);
                    $end = end($value);
                    $a=$zip->addFile($v, $end);
                    $zip->renameName($end, $names[$k]);
                }
               //关闭文件
                $zip->close();
            }
            //这里是下载zip文件
            $dir= substr($dir, 0, -1);
            set_time_limit(0);
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header('Content-disposition: attachment; filename='.date('Y-m-d',time()).'.zip'); //文件名
            header("Content-Type: application/zip"); //zip格式的
            header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
            header('Content-Length: ' . filesize($zipname)); //告诉浏览器，文件大小
            @readfile($zipname);
            //删除临时文件
            $a=unlink($zipname);
      
        }else{
            showDialog('请选择配送方式');
        }
    }

    private function excel_order_sub_mergepl($excel,$data_tmp,$time=0,$address_id=0, $sum,$name,$dir){   
        $type = 1;
        if($address_id > 0){
            $model_daddress = Model('daddress');
            $address = $model_daddress->getAddressInfo(array('address_id' => $address_id));
        }
        
        $time = date('Y-m-d-H', $time);
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R');
        $tableheader = array('订单号', '收货人', '发货人', '商品数量', '商品名称', '商品货号', '收货人电话', '自提点','详细地址', '买家','支付时间','送货时间','买家留言','发货备注','订单状态','备注(退款信息)','促销信息','商品总成本');
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
        $x = $sum;
        $z = 'S' . $x;
        $w = 'A1';
        $excel->getActiveSheet()->getStyle($w . ':' . $z)->applyFromArray($styleThinBlackBorderOutline);
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
                    $mall_info = Model('order')->getOrderCommonInfo(array('order_id'=>$order_info['order_id']));
                    if($order_info['by_post'] == 2){
                        $jie = $reciver_info['address'];
                    }else{
                        $jie = $mall_info['mall_info'];
                    }
                } else {
                    $sheng = ' ';
                    $shi = ' ';
                    $qu = ' ';
                    $jie = ' ';
                }
                $ziti_name = Model('ziti_address')->getAddressInfo(array('address_id' => $order_info['reciver_ziti_id']));
                if (!empty($ziti_name)) {
                    $ziti_name = $ziti_name['seller_name'];
                } else {
                    $ziti_name = ' ';
                }
                $model_class = Model('goods_class');
                $goods_class1 = $model_class->getGoodsClassInfoById($order_info['extend_order_goods'][$ii]['gc_id']);//第一级商品分类
                $goods_class2 = $model_class->getGoodsClassInfoById($goods_class1['gc_parent_id']);
                $goods_class3 = $model_class->getGoodsClassInfoById($goods_class2['gc_parent_id']);
                $goods_classname = $goods_class3['gc_name'];
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
                if(!empty($goodsid) && is_array($goodsid)){
                    foreach ($goodsid as $key => $vv) {
                        if($order_info['extend_order_goods'][$ii]['goods_id'] == $vv['goods_id']){
                            //备注
                            $result = $model_refund_return->getRefundReturnList(array('order_id' => $order_info['order_id']));
                            //退款时间
                            if($result[0]['admin_time'] > 0){
                                $refund_time = date('Y-m-d H:i:s',$result[0]['admin_time']) ;
                            }else{
                                $refund_time = '无';
                            }
                            if($result[0]['seller_state'] == '1'){
                                $seller_state = '待审核';
                            }else if($result[0]['seller_state'] == '2'){
                                $seller_state = '同意';
                            }else if($result[0]['seller_state'] == '3'){
                                $seller_state = '不同意';
                            }else{
                                $seller_state = '';
                            }
                            if($result[0]['seller_state'] == '2'){
                                if($result[0]['refund_state'] == '1'){
                                    $admin_state = '处理中';
                                }else if($result[0]['refund_state'] == '2'){
                                    $admin_state = '待管理员处理';
                                }else if($result[0]['refund_state'] == '3'){
                                    $admin_state = '已完成';
                                }else{
                                    $admin_state = '无';
                                }
                            }else{
                                $admin_state = '无';
                            }
                            $seller_message = $result[0]['seller_message'];
                            $admin_message = $result[0]['admin_message'];
                            $buyer_message = $result[0]['reason_info'];
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
                        }else if($vv['goods_id'] == 0){
                            //备注
                            $result = $model_refund_return->getRefundReturnList(array('order_id' => $order_info['order_id']));
                            //退款时间
                            if($result[0]['admin_time'] > 0){
                                $refund_time = date('Y-m-d H:i:s', $result[0]['admin_time']) ;
                            }else{
                                $refund_time = '无';
                            }
                            if($result[0]['seller_state'] == '1'){
                                $seller_state = '待审核';
                            }else if($result[0]['seller_state'] == '2'){
                                $seller_state = '同意';
                            }else if($result[0]['seller_state'] == '3'){
                                $seller_state = '不同意';
                            }else{
                                $seller_state = '';
                            }
                            if($result[0]['seller_state'] == '2'){
                                if($result[0]['refund_state'] == '1'){
                                    $admin_state = '处理中';
                                }else if($result[0]['refund_state'] == '2'){
                                    $admin_state = '待管理员处理';
                                }else if($result[0]['refund_state'] == '3'){
                                    $admin_state = '已完成';
                                }else{
                                    $admin_state = '无';
                                }
                            }else{
                                $admin_state = '无';
                            }
                            $seller_message = $result[0]['seller_message'];
                            $admin_message = $result[0]['admin_message'];
                            $buyer_message = $result[0]['reason_info'];
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
                            $refund_time = '';
                            $seller_state = '';
                            $admin_state = '';
                            $seller_message = '';
                            $admin_message = '';
                            $buyer_message = '';
                            $beizhu = ' ';
                        }
                    }
                }else{
                    $refund_time = '';
                    $seller_state = '';
                    $admin_state = '';
                    $seller_message = '';
                    $admin_message = '';
                    $buyer_message = '';
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
                            $voucher_name = Model('voucher')->getVoucherInfo(array('voucher_code' => $voucher_code), 'voucher_title');
                            $vou = $voucher_name['voucher_title'];
                        } else {
                            $vou = ' ';
                        }
                    }
                } else {
                    $vou = ' ';
                }
                $order_data[] = [
                    'order_sn' => $ii == 0 ? $order_info['order_sn'] : ' ',
                    'reciver_name' => $order_info['reciver_name'],
                    'storage' => $order_info['extend_order_goods'][$ii]['order_goods_deliverer_id'] ? Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']))['seller_name'] : Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['deliverer_id']))['seller_name'],
                    'goods_num' => $order_info['extend_order_goods'][$ii]['goods_num'],
                    'goods_name' => $order_info['extend_order_goods'][$ii]['goods_name'],
                    'goods_serial' => $order_info['extend_order_goods'][$ii]['goods_serial'],
                    'reciver_phone' => $reciver_info['phone'],
                    'ziti_name' => $ziti_name,//自提点
                    'recive_address' => $jie,//省，市，区，街
                    'buyer' => $ii == 0 ? $order_info['buyer_name'] : ' ',
                    'pay_time' => $order_info['payment_time'] != 0 ? date('Y-m-d H:i:s', $order_info['payment_time']) : ' ',
                    'ziti_time' => $order_info['ziti_ladder_time'] != 0 ? date('Y-m-d H:i:s', $order_info['ziti_ladder_time']) : ' ',
                    'order_message' => $order_info['order_message'] != '' ? $order_info['order_message'] : ' ',
                    'deliver_explain' => $order_info['deliver_explain'] != '' ? $order_info['deliver_explain'] : ' ',
                    'order_state' => $state,
                    'beizhu' => $beizhu,
                    'order_type' => $order_info['add_time'] < 1618931572 ? goodsTypeName($order_info['order_type']) : goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
                    'goods_costall' => number_format($order_info['extend_order_goods'][$ii]['goods_cost_price'] * $order_info['extend_order_goods'][$ii]['goods_num'], 2),
                ];
            }
            unset($data_tmp[$key]);
        }
        return $order_data;
    }

    
    private function excel_order_mergepl($excel, $data_excel, $dir){  
        $data_tmp = $data_excel['data_array'];
        $model_daddress = Model('daddress');
        if($data_excel['deliverer_id'] > 0){
            $address = $model_daddress->getAddressInfo(array('address_id' => $data_excel['deliverer_id']));
            $name_biao = $data_excel['name'];
            $name = $address['seller_name'];
            $address_info = $address['address'];
            $tel = $address['telphone'];
        }else{
            $name = $data_excel['name'];
            $address_info = '';
            $tel = '';
        }
        
        $time = date('Y-m-d-H', $data_excel['time1']);
        $letter = array('A', 'B', 'C', 'D','E' , 'F','G');
        // 设置行高度
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);      //第一行是否加粗
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);         //第一行字体大小
        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20); //设置默认行高
        $excel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);    //第一行行高
        // 设置水平居中
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置单元格宽度
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(90);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $excel->getActiveSheet()->mergeCells('A1:G1');
        $a = count($data_tmp) + 3;
        $b = 'A' . $a;
        $c = 'G' . $a;
        $d = $a + 1;
        $e = 'A' . $d;
        $f = 'G' . $d;
        $g = $a + 2;
        $h = 'A' . $g;
        $i = 'G' . $g;
        $excel->getActiveSheet()->mergeCells($b . ':' . $c);
        $excel->getActiveSheet()->mergeCells($e . ':' . $f);
        $excel->getActiveSheet()->mergeCells($h . ':' . $i);           
        $excel->getActiveSheet()
                ->setCellValue('A1',  $time . '点' . $name . '配货清单')
                ->setCellValue('A2', '供应商')
                ->setCellValue('B2', '商品名称')
                ->setCellValue('C2', '商品数量')
                ->setCellValue('D2', '商品条码')
                ->setCellValue('E2', '商品货号')
                ->setCellValue('F2', '一级分类')
                ->setCellValue('G2', '商品总成本价')
                ->setCellValue($b, '供货商：' . $name . '             交接人：                     送货时间：                           签收人：')
                ->setCellValue($e, '供货商地址：' . $address_info . '                联系电话：' . $tel)
                ->setCellValue($h, '配送地址：郑州市管城区航海路物资集团仓库        联系人：罗经理17513319366');
        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'allborders' => array( //设置全部边框
                    'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                ),

            ),
        );  
        $x = $a + 2;
        $z = 'G' . $x;
        $w = 'A1';

        $excel->getActiveSheet()->getStyle($w.':'.$z)->applyFromArray($styleThinBlackBorderOutline);
        //表格数组
        $order_data = [];
        foreach ($data_tmp as $k => $v) {
            $model_goods = Model('goods');
            $goods_detail = $model_goods->getGoodsInfo(array('goods_id' => $v['goods_id']));
            $order_data[] =  array(
                $name,
                $v['goods_name'],
                $v['sumall'],
                $goods_detail['goods_barcode'],
                $goods_detail['goods_serial'],
                $v['gc_name_1'],
                $v['goods_cost_price_all'],
            );
           
        }
        return $order_data;
    }






    private function param_data_handling($data){
        $param['class_type'] = $data['class_type'];
        $param['daddress_id'] = $data['daddress_id'];
        $param['delivery_type_id'] = $data['delivery_type_id'];

        $param['start_unixtime_pay'] = $_GET['query_start_date_pay2'] ? strtotime($_GET['query_start_date_pay2']) : null;
        $param['end_unixtime_pay'] = $_GET['query_end_date_pay2'] ? strtotime($_GET['query_end_date_pay2']) : null;

        $param['query_start_date_pay2'] = $data['query_start_date_pay2'];
        $param['query_end_date_pay2'] = $data['query_end_date_pay2'];

        $param['curpage'] = $data['curpage'];
        return $param;
    }

    private function create_excel_order_data($param){
        $model_goods = Model('goods');
        $model_order = Model('order');
        $model_goods_class = Model('goods_class');
        $model_refund_return = Model('refund_return');
        $model_daddress = Model('daddress');
        $condition = array();
        $condition['order.order_state'] = 20;
        
        if($param['class_type']>0){
            //果蔬
            $gc_parent_id1 = 1697;
            $goods_class1 = $model_goods_class->getChildClassByFirstId_vouchertype($gc_parent_id1);
            $class1 = array_keys($goods_class1['class']);

            $gc_parent_id2 = 1653 ;
            $goods_class2 = $model_goods_class->getChildClassByFirstId_vouchertype($gc_parent_id2);
            $class2 = array_keys($goods_class2['class']);

            $gc_parent_id3 = 1699 ;
            $goods_class3 = $model_goods_class->getChildClassByFirstId_vouchertype($gc_parent_id3);
            $class3 = array_keys($goods_class3['class']);

            $guoshu_class = array_merge($class1,$class2);
            $guoshudong_class = array_merge($guoshu_class,$class3);

            if($param['class_type'] == 1){
                $condition['order_goods.gc_id'] = array('in',$guoshu_class);
                $name_more = '-果蔬';
            }else if($param['class_type']==2){
                $condition['order_goods.gc_id'] = array('not in',$guoshu_class);
                $name_more = '-预包装';
            }else if($param['class_type']==3){
                $condition['order_goods.gc_id'] = array('in',$guoshudong_class);
                $name_more ='-果蔬+冻品';
            }else if($param['class_type'] == 4){
                $condition['order_goods.gc_id'] = array('not in',$guoshudong_class);
                $name_more = '-预包装';      
            }
        }else{
             $name_more = '';
        }

        //发货人姓名
        if($param['daddress_id']>0){
            $condition['order_goods.deliverer_id'] = $param['daddress_id'];
        }
         //配送方式
        if($param['delivery_type_id']>0){
            $is_gharr= array(28,37);
            if(in_array($param['delivery_type_id'],$is_gharr)){
                  $is_gh=1;
            }
            $daddress_list = Model('peisong')->where(array('id' => $param['delivery_type_id']))->find();
            $daddress_ids = explode(',', $daddress_list['deliever_id']);
            $daddress_ids = array_values($daddress_ids);
            $condition['order_goods.deliverer_id'] = array('in',$daddress_ids);
            $name = $daddress_list['p_name'];
        }

        if ($param['start_unixtime_pay'] || $param['end_unixtime_pay']) {
            $condition['order.payment_time'] = array('between', array($param['start_unixtime_pay'], $param['end_unixtime_pay']));
        }
       
        $data = $model_order->getOrderGoodsExportList($condition,'20000'); 
        
        $ordergoods_arr = array();
        foreach($data as $kk => $vv){
            if($vv['is_zorder'] == 0 ){
                unset($data[$kk]);
                continue;
            }
            foreach ($vv['extend_order_goods'] as $key => $value) { 
                $refund_list = $model_refund_return->getRefundReturnList(array('order_id' => $vv['order_id']));
                if(!empty( $refund_list)&&is_array( $refund_list)){
                    foreach ($refund_list as $key1 => $value1) {
                        if($value1['goods_id'] == 0){
                            if($value1['seller_state'] < 3){
                                unset($data[$kk]);
                            }  
                        }else{
                            if($value1['goods_id'] == $value['goods_id'] && $value1['seller_state'] < 3){
                                unset($data[$kk]['extend_order_goods'][$key]);
                            }  
                        }
                    }
                }
                if($param['class_type'] > 0){
                    if($param['class_type'] == 1){
                        if(!in_array($value['order_goods_gc_id'], $guoshu_class)){
                            unset($data[$kk]['extend_order_goods'][$key]);
                        }  
                    }else if($param['class_type'] == 2){
                        if(in_array($value['order_goods_gc_id'], $guoshu_class)){
                            unset($data[$kk]['extend_order_goods'][$key]);
                        } 
                    }else if($param['class_type'] == 3){
                        if(!in_array($value['order_goods_gc_id'], $guoshudong_class)){
                            unset($data[$kk]['extend_order_goods'][$key]);
                        } 
                    }else if($param['class_type'] == 4){
                        if(in_array($value['order_goods_gc_id'], $guoshudong_class)){
                            unset($data[$kk]['extend_order_goods'][$key]);
                        } 
                    } 
                } 
                if($param['daddress_id'] > 0){
                    if($value['order_goods_deliverer_id']!=$param['daddress_id']){
                        unset($data[$kk]['extend_order_goods'][$key]);  
                    }
                }
                if($param['delivery_type_id'] > 0){
                    if(!in_array($value['order_goods_deliverer_id'], $daddress_ids)){
                        unset($data[$kk]['extend_order_goods'][$key]);
                    }
                }    
             }
        }

        foreach ($data as $k2 => $v2) {
            foreach ($v2['extend_order_goods'] as $k22 => $v22) {
                $ordergoods_arr[] = $v22;
            }
        }

        foreach ($ordergoods_arr as $k => $v) {
            $data_arr[$v['goods_id']][] = $v;
        }

        $num_goodsnum = 0;
        $num_costprice = 0;

        $num_payprice = 0;
        $data_excel = array();

        if(is_array($data_arr) && !empty($data_arr)){
            foreach ($data_arr as $ke => $va) {
                $sumall = 0;
                $goods_cost_price_all = 0;
                $goods_pay_price_all = 0;

                foreach ($va as $ke1 => $va1) {
                    $sumall = $sumall + $va1['goods_num'];
                    $goods_cost_price_all = $goods_cost_price_all + $va1['goods_cost_price'] * $va1['goods_num'];
                    $goods_pay_price_all = $goods_pay_price_all + $va1['goods_pay_price'];
                }
                $address = $model_daddress->getAddressInfo(array('address_id' => $va[0]['deliverer_id']));

                $data_array[$ke]['deliverer_name'] = $address['seller_name'];
                $data_array[$ke]['goods_id'] = $va[0]['goods_id'];
                $data_array[$ke]['goods_name'] = $va[0]['goods_name'];

                $goods_info = $model_goods->getGoodsInfoByID($va[0]['goods_id']);
                $goods_class1 = $model_goods_class->getGoodsClassInfoById($goods_info['gc_id_1']);
                $goods_class2 = $model_goods_class->getGoodsClassInfoById($goods_info['gc_id_2']);

                $data_array[$ke]['gc_name_1'] = $goods_class1['gc_name'];
                $data_array[$ke]['sumall'] = $sumall;
                $data_array[$ke]['goods_cost_price_all'] = $goods_cost_price_all;

                if($va[0]['is_cw'] == 1){
                    $data_array[$ke]['is_cw'] = '是';
                }else{
                    $data_array[$ke]['is_cw'] = '否';
                }

                $data_array[$ke]['goods_pay_price_all'] = $goods_pay_price_all;

                $num_payprice = $num_payprice+$goods_pay_price_all;
                $num_costprice = $num_costprice+$goods_cost_price_all;
                $num_goodsnum = $num_goodsnum+$sumall;

            }

            foreach ($data_array as $aa => $ab) {
                $goods_deliver_arr[$ab['deliverer_name']][] = $ab;
            }

            foreach ($goods_deliver_arr as $keya => $valuea) {
                foreach ($valuea as $keyb => $valueb) {
                    $final_array[] = $valueb;
                }
            }

            $data_array = array_values($final_array); 
            $count = count($data_array);

            $data_array = array_values($data_array);
            $data_array[$count]['goods_name'] = '总计';
            $data_array[$count]['deliverer_name'] = '';
            $data_array[$count]['goods_id'] = '';
            $data_array[$count]['gc_name_1'] = '';
            $data_array[$count]['sumall'] = $num_goodsnum;
            $data_array[$count]['goods_cost_price_all'] = $num_costprice;
            $data_array[$count]['goods_pay_price_all'] = $num_payprice;
            $data_array[$count]['is_cw'] = '';
            $data_excel['data_array'] = $data_array;
            $data_excel['time1'] = $param['end_unixtime_pay'];

            if($param['daddress_id'] > 0){
                $data_excel['deliverer_id'] = $param['daddress_id'];
                $data_excel['name'] = '';
            }else{
                $data_excel['deliverer_id'] = 0;
                $data_excel['name'] = $name.$name_more;
            }
        }else{
            showDialog('无数据');
        }

        ini_set('max_execution_time', '0');
        $count = count($data);
        
        return array($data_excel, $is_gh);
    }

    private function excel_order_data_filling($excel, $excel_order_data, $is_gh){
        if($is_gh == 1){
            $this->excel_order_data_guohao($excel, $excel_order_data);
        }else{
            $this->excel_order_data($excel, $excel_order_data);
        }
    }

    private function excel_order_data_guohao($excel, $excel_order_data){
        $data_tmp = $excel_order_data['data_array'];
        $model_daddress = Model('daddress');
        if($excel_order_data['deliverer_id'] > 0){
            $address = $model_daddress->getAddressInfo(array('address_id' => $excel_order_data['deliverer_id']));
            $name = $address['seller_name'];
            $address_info = $address['address'];
            $tel = $address['telphone'];
        }else{
             $name=$excel_order_data['name'];
             $address_info='';
             $tel='';
        }
        
        $time=date('Y-m-d-H',$excel_order_data['time1']);
        $letter = array('A', 'B', 'C', 'D','E' , 'F','G' ,'H','I');
        
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);      //第一行是否加粗
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);         //第一行字体大小
        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20); //设置默认行高
        $excel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);    //第一行行高
        // 设置水平居中
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置单元格宽度
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(90);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $excel->getActiveSheet()->mergeCells('A1:I1');
        
        $a = count($data_tmp) + 3;
        $b = 'A'.$a;
        $c = 'I'.$a;
        $d = $a+1;
        $e = 'A'.$d;
        $f = 'I'.$d;
        $g = $a+2;
        $h = 'A'.$g;
        $i = 'I'.$g;
        
        $excel->getActiveSheet()->mergeCells($b.':'.$c);
        $excel->getActiveSheet()->mergeCells($e.':'.$f);
        $excel->getActiveSheet()->mergeCells($h.':'.$i);      
        $excel->getActiveSheet()
                ->setCellValue('A1', $time.'点'.$name.'配货清单')
                ->setCellValue('A2', '供应商')
                ->setCellValue('B2', '商品名称')
                ->setCellValue('C2', '商品数量')
                ->setCellValue('D2', '商品条码')
                ->setCellValue('E2', '商品货号')
                ->setCellValue('F2', '一级分类')
                ->setCellValue('G2', '商品总成本价')
                ->setCellValue('H2', '实际支付金额')
                ->setCellValue('I2', '是否云仓商品')
                ->setCellValue($b, '供货商：'.$name.'             交接人：                     送货时间：                           签收人：')
                ->setCellValue($e, '供货商地址：'.$address_info.'                联系电话：'.$tel)
                ->setCellValue($h, '配送地址：郑州市管城区航海路物资集团仓库        联系人：田经理13592636294');
        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'allborders' => array( //设置全部边框
                    'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                ),
            ),
        );

        $x = $a+2;
        $z = 'I'.$x;
        $w = 'A1';

        $excel->getActiveSheet()->getStyle($w.':'.$z)->applyFromArray($styleThinBlackBorderOutline);
        //表格数组
        $order_data = [];

        foreach ($data_tmp as $k => $v) {
            $model_goods = Model('goods');
            $goods_detail = $model_goods->getGoodsInfo(array('goods_id' => $v['goods_id']));
            $order_data[] =  array(
                $v['deliverer_name'],
                $v['goods_name'],
                $v['sumall'],
                $goods_detail['goods_barcode'],
                $goods_detail['goods_serial'],
                $v['gc_name_1'],
                $v['goods_cost_price_all'],
                $v['goods_pay_price_all'],
                $v['is_cw'],
            );
        }
        //填充表格信息
        for ($i = 3; $i <= count($order_data) + 2; $i++) {
            $j = 0;
            foreach ($order_data[$i - 3] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i","$value",PHPExcel_Cell_DataType::TYPE_STRING);
                $j++;
            }
        }
        // echo '<pre>';print_r($order_data);die;
        // return $excel;
    }
    
    private function excel_order_data($excel, $excel_order_data){
        $data_tmp = $excel_order_data['data_array'];
        $model_daddress = Model('daddress');

        if($excel_order_data['deliverer_id'] > 0){
            $address = $model_daddress->getAddressInfo(array('address_id' => $excel_order_data['deliverer_id']));
            $name = $address['seller_name'];
            $address_info = $address['address'];
            $tel = $address['telphone'];
        }else{
             $name = $excel_order_data['name'];
             $address_info = '';
             $tel = '';
        }
        
        $time = date('Y-m-d-H',$excel_order_data['time1']);

        $letter = array('A', 'B', 'C', 'D','E' , 'F','G' );
        // 设置行高度
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);      //第一行是否加粗
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);         //第一行字体大小
        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20); //设置默认行高
        $excel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);    //第一行行高
        // 设置水平居中
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //设置单元格宽度
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(90);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $excel->getActiveSheet()->mergeCells('A1:G1');
        
        $a = count($data_tmp) + 3;
        $b = 'A'.$a;
        $c = 'G'.$a;
        $d = $a+1;
        $e = 'A'.$d;
        $f = 'G'.$d;
        $g = $a+2;
        $h = 'A'.$g;
        $i = 'G'.$g;
        $excel->getActiveSheet()->mergeCells($b.':'.$c);
        $excel->getActiveSheet()->mergeCells($e.':'.$f);
        $excel->getActiveSheet()->mergeCells($h.':'.$i);           
        $excel->getActiveSheet()
                ->setCellValue('A1', $time.'点'.$name.'配货清单')
                ->setCellValue('A2', '供应商')
                ->setCellValue('B2', '商品名称')
                ->setCellValue('C2', '商品数量')
                ->setCellValue('D2', '商品条码')
                ->setCellValue('E2', '商品货号')
                ->setCellValue('F2', '一级分类')
                ->setCellValue('G2', '商品总成本价')
                ->setCellValue($b, '供货商：'.$name.'             交接人：                     送货时间：                           签收人：')
                ->setCellValue($e, '供货商地址：'.$address_info.'                联系电话：'.$tel)
                ->setCellValue($h, '配送地址：郑州市管城区航海路物资集团仓库        联系人：田经理13592636294');
        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'allborders' => array( //设置全部边框
                    'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                ),

            ),
        );  
        $x = $a + 2;
        $z = 'G'.$x;
        $w = 'A1';
        $excel->getActiveSheet()->getStyle($w.':'.$z)->applyFromArray($styleThinBlackBorderOutline);
        //表格数组
        $order_data = [];
        foreach ($data_tmp as $k => $v) {
            $model_goods = Model('goods');
            $goods_detail = $model_goods->getGoodsInfo(array('goods_id' => $v['goods_id']));
            $order_data[] = array(
                $v['deliverer_name'],
                $v['goods_name'],
                $v['sumall'],
                $goods_detail['goods_barcode'],
                $goods_detail['goods_serial'],
                $v['gc_name_1'],
                $v['goods_cost_price_all'],
            );
        }
        //填充表格信息
        for ($i = 3; $i <= count($order_data) + 2; $i++) {
            $j = 0;
            foreach ($order_data[$i - 3] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i","$value",PHPExcel_Cell_DataType::TYPE_STRING);
                $j++;
            }
        }
    }

    private function create_excel_order_goods_data($param){
        $model_order = Model('order');
        $condition = array();
        $condition['order.order_state'] = 20;

        //发货人姓名
        if($param['daddress_id']>0){
            $condition['order_goods.deliverer_id'] = $param['daddress_id'];
            $daddress_id = $param['daddress_id'];
            $name = '';
        }
        
        //配送方式
        if($param['delivery_type_id'] > 0){
            $daddress_list = Model('peisong')->where(array('id' => $param['delivery_type_id']))->find();
            $daddress_ids = explode(',', $daddress_list['deliever_id']);
            $daddress_ids = array_values($daddress_ids);
            $condition['order_goods.deliverer_id'] = array('in',$daddress_ids);
            $daddress_id = 0;
            $name = $daddress_list['p_name'];
        }
        
        if ($param['start_unixtime_pay'] || $param['end_unixtime_pay']) {
            $condition['order.payment_time'] = array('between', array($param['start_unixtime_pay'], $param['end_unixtime_pay']));
        }
        
        $data = $model_order->getOrderGoodsExportList($condition,'20000'); 
        
        $ordergoods_arr = array();
        foreach($data as $kk => $vv){
            if($vv['is_zorder'] == 0 ){
                unset($data[$kk]);
                continue;
            }
            foreach ($vv['extend_order_goods'] as $key => $value) { 
                $model_refund_return = Model('refund_return');
                $refund_list = $model_refund_return->getRefundReturnList(array('order_id' => $vv['order_id']));
                if(!empty($refund_list) && is_array($refund_list)){
                    foreach ($refund_list as $key1 => $value1) {
                       if($value1['goods_id'] == 0){
                            if($value1['seller_state'] < 3){
                                 unset($data[$kk]);
                            }  
                        }else{
                            if($value1['goods_id'] == $value['goods_id'] && $value1['seller_state'] < 3){
                                unset($data[$kk]['extend_order_goods'][$key]);
                            }  
                        }
                    }
                }  
                if($param['daddress_id'] > 0){
                    if($value['order_goods_deliverer_id'] != $param['daddress_id']){
                        unset($data[$kk]['extend_order_goods'][$key]);
                    }
                }
                if($param['delivery_type_id'] > 0){
                    if(!in_array($value['order_goods_deliverer_id'], $daddress_ids)){
                        unset($data[$kk]['extend_order_goods'][$key]);
                        
                    }
                }    
             }
            $data[$kk]['extend_order_goods'] = array_values($data[$kk]['extend_order_goods']);
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
        return array($data, $sum, $name);
    }

    private function excel_order_goods_data_filling($excel, $excel_order_goods_data, $param, $sum, $name){
        $type = 1;
        if ($param['address_id'] > 0) {
            $model_daddress = Model('daddress');
            $address = $model_daddress->getAddressInfo(array('address_id' => $param['address_id']));
            $name = $address['seller_name'];
        }
        $time = date('Y-m-d-H', $param['end_unixtime_pay']);
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S');
        $tableheader = array('订单号', '收货人', '发货人', '商品数量', '商品名称', '商品货号', '收货人电话', '自提点', '详细地址', '买家', '支付时间', '送货时间', '买家留言', '发货备注', '订单状态', '备注(退款信息)', '促销信息', '商品总成本');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
            $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
        }
        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'allborders' => array( //设置全部边框
                    'style' => \PHPExcel_Style_Border::BORDER_THIN, //粗的是thick
                ),

            ),
        );

        $x = $sum;
        $z = 'S' . $x;
        $w = 'A1';

        $excel->getActiveSheet()->getStyle($w . ':' . $z)->applyFromArray($styleThinBlackBorderOutline);
        $model_order_log = Model('order_log');
        foreach ($excel_order_goods_data as $key => $order_info) {
            for ($ii = 0; $ii < count($order_info['extend_order_goods']); $ii++) {
                $reciver_info = unserialize($order_info['reciver_info']);
                $address = $reciver_info['area'];
                $street = $reciver_info['street'];
                $arr_str = explode(" ", preg_replace('#\s+#', ' ', trim($address)));
                if (!empty($arr_str)) {
                    $sheng = $arr_str[0] . '省';
                    $shi = $arr_str[1];
                    $qu = $arr_str[2];
                    $mall_info = Model('order')->getOrderCommonInfo(array('order_id' => $order_info['order_id']));
                    if ($order_info['by_post'] == 2) {
                        $jie = $reciver_info['address'];
                    } else {
                        $jie = $mall_info['mall_info'];
                    }
                } else {
                    $sheng = ' ';
                    $shi = ' ';
                    $qu = ' ';
                    $jie = ' ';
                }
                $ziti_name = Model('ziti_address')->getAddressInfo(array('address_id' => $order_info['reciver_ziti_id']));
                if (!empty($ziti_name)) {
                    $ziti_name = $ziti_name['seller_name'];
                } else {
                    $ziti_name = ' ';
                }
                $model_class = Model('goods_class');
                $goods_class1 = $model_class->getGoodsClassInfoById($order_info['extend_order_goods'][$ii]['gc_id']); //第一级商品分类
                $goods_class2 = $model_class->getGoodsClassInfoById($goods_class1['gc_parent_id']);
                $goods_class3 = $model_class->getGoodsClassInfoById($goods_class2['gc_parent_id']);
                $goods_classname = $goods_class3['gc_name'];
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
                        } else {
                            $state = strip_tags(orderState($order_info));
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
                if (!empty($goodsid) && is_array($goodsid)) {
                    foreach ($goodsid as $key => $vv) {
                        if ($order_info['extend_order_goods'][$ii]['goods_id'] == $vv['goods_id']) {
                            //备注
                            $result = $model_refund_return->getRefundReturnList(array('order_id' => $order_info['order_id']));
                            //退款时间
                            if ($result[0]['admin_time'] > 0) {
                                $refund_time = date('Y-m-d H:i:s', $result[0]['admin_time']);
                            } else {
                                $refund_time = '无';
                            }
                            if ($result[0]['seller_state'] == '1') {
                                $seller_state = '待审核';
                            } else if ($result[0]['seller_state'] == '2') {
                                $seller_state = '同意';
                            } else if ($result[0]['seller_state'] == '3') {
                                $seller_state = '不同意';
                            } else {
                                $seller_state = '';
                            }
                            if ($result[0]['seller_state'] == '2') {
                                if ($result[0]['refund_state'] == '1') {
                                    $admin_state = '处理中';
                                } else if ($result[0]['refund_state'] == '2') {
                                    $admin_state = '待管理员处理';
                                } else if ($result[0]['refund_state'] == '3') {
                                    $admin_state = '已完成';
                                } else {
                                    $admin_state = '无';
                                }
                            } else {
                                $admin_state = '无';
                            }
                            $seller_message = $result[0]['seller_message'];
                            $admin_message = $result[0]['admin_message'];
                            $buyer_message = $result[0]['reason_info'];
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
                                        $beizhu = '退款退货中'; //退款退货中
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
                        } else if ($vv['goods_id'] == 0) {
                            //备注
                            $result = $model_refund_return->getRefundReturnList(array('order_id' => $order_info['order_id']));
                            //退款时间
                            if ($result[0]['admin_time'] > 0) {
                                $refund_time = date('Y-m-d H:i:s', $result[0]['admin_time']);
                            } else {
                                $refund_time = '无';
                            }
                            if ($result[0]['seller_state'] == '1') {
                                $seller_state = '待审核';
                            } else if ($result[0]['seller_state'] == '2') {
                                $seller_state = '同意';
                            } else if ($result[0]['seller_state'] == '3') {
                                $seller_state = '不同意';
                            } else {
                                $seller_state = '';
                            }
                            if ($result[0]['seller_state'] == '2') {
                                if ($result[0]['refund_state'] == '1') {
                                    $admin_state = '处理中';
                                } else if ($result[0]['refund_state'] == '2') {
                                    $admin_state = '待管理员处理';
                                } else if ($result[0]['refund_state'] == '3') {
                                    $admin_state = '已完成';
                                } else {
                                    $admin_state = '无';
                                }
                            } else {
                                $admin_state = '无';
                            }
                            $seller_message = $result[0]['seller_message'];
                            $admin_message = $result[0]['admin_message'];
                            $buyer_message = $result[0]['reason_info'];
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
                                        $beizhu = '退款退货中'; //退款退货中
                                    } else if ($result[0]['refund_state'] == 3) {
                                        $beizhu = '退款退货完成'; //退款退货完成
                                    } else {
                                        $beizhu = ' ';
                                    }
                                }
                            } else {
                                $beizhu = '';
                            }

                        } else {
                            $refund_time = '';
                            $seller_state = '';
                            $admin_state = '';
                            $seller_message = '';
                            $admin_message = '';
                            $buyer_message = '';
                            $beizhu = ' ';
                        }
                    }
                } else {
                    $refund_time = '';
                    $seller_state = '';
                    $admin_state = '';
                    $seller_message = '';
                    $admin_message = '';
                    $buyer_message = '';
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
                            $voucher_name = Model('voucher')->getVoucherInfo(array('voucher_code' => $voucher_code), 'voucher_title');
                            $vou = $voucher_name['voucher_title'];
                        } else {
                            $vou = ' ';
                        }
                    }
                } else {
                    $vou = ' ';
                }
                $order_data[] = [
                    'order_sn' => $ii == 0 ? $order_info['order_sn'] : ' ',
                    'reciver_name' => $order_info['reciver_name'],
                    'storage' => $order_info['extend_order_goods'][$ii]['order_goods_deliverer_id'] ? Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['order_goods_deliverer_id']))['seller_name'] : Model('daddress')->getAddressInfo(array('address_id' => $order_info['extend_order_goods'][$ii]['deliverer_id']))['seller_name'],
                    'goods_num' => $order_info['extend_order_goods'][$ii]['goods_num'],
                    'goods_name' => $order_info['extend_order_goods'][$ii]['goods_name'],
                    'goods_serial' => $order_info['extend_order_goods'][$ii]['goods_serial'],
                    'reciver_phone' => $reciver_info['phone'],
                    'ziti_name' => $ziti_name, //自提点
                    'recive_address' => $jie, //省，市，区，街
                    'buyer' => $ii == 0 ? $order_info['buyer_name'] : ' ',
                    'pay_time' => $order_info['payment_time'] != 0 ? date('Y-m-d H:i:s', $order_info['payment_time']) : ' ',
                    'ziti_time' => $order_info['ziti_ladder_time'] != 0 ? date('Y-m-d H:i:s', $order_info['ziti_ladder_time']) : ' ',
                    'order_message' => $order_info['order_message'] != '' ? $order_info['order_message'] : ' ',
                    'deliver_explain' => $order_info['deliver_explain'] != '' ? $order_info['deliver_explain'] : ' ',
                    'order_state' => $state,
                    'beizhu' => $beizhu,
                    'order_type' => $order_info['add_time'] < 1618931572 ? goodsTypeName($order_info['order_type']) : goodsTypeName($order_info['extend_order_goods'][$ii]['goods_type']),
                    'goods_costall' => number_format($order_info['extend_order_goods'][$ii]['goods_cost_price'] * $order_info['extend_order_goods'][$ii]['goods_num'], 2),
                ];
            }
            unset($data_tmp[$key]);
        }
        //填充表格信息
        for ($i = 2; $i <= count($order_data) + 1; $i++) {
            $j = 0;
            foreach ($order_data[$i - 2] as $key => $value) {
                $w = 'A' . $i;
                $z = 'S' . $i;
                $bubiao = array('5', '6');
                if (!in_array($param['address_id'], $bubiao)) {
                    $excel->getActiveSheet()->getStyle($w . ':' . $z)->getFont()->setColor(new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_RED));
                }
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i", "$value", PHPExcel_Cell_DataType::TYPE_STRING);
                $j++;
            }
        }
        return $name;
    }

    private function download($excel, $param){
        $write = new PHPExcel_Writer_Excel5($excel);
        // echo '<pre>';print_r($param);die; 
        $filename = $param['time'] . '点' . $param['name'] . '配货清单+商品表.xls';
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename=' . $filename);
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
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
