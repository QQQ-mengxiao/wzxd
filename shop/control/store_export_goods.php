<?php
/**
 * 商家中心订单导出
 *
 *
 *
 **/

require_once BASE_ROOT_PATH.'/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/IOFactory.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/Reader/Excel2007.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/Reader/IReader.php'; //引入文件
defined('In718Shop') or exit('Access Invalid!');

class store_export_goodsControl extends BaseSellerControl
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
       $model_goods = Model('goods');
        $model_order = Model('order');
        $condition = array();
         $where = array();
          $condition2=array();
       if($this->choose_gcid > 0){
            $gc_depth = $this->gc_arr[$this->choose_gcid]['depth'];
            $where['gc_id_'.$gc_depth] = $this->choose_gcid;
        }
        //lxs
        $str=$_GET['search_gname'];
        if(trim($str)) {
            $str = Model('search')->decorateSearch_pre($str);
            $where['goods_name'] = array('like', '%' . trim($str) . '%');
        }
         //关联发货人
        if($_GET['daddress_id']>0){
            $where['deliverer_id'] = $_GET['daddress_id'];
        }
        // var_dump('2222222222');die;
        //支付时间  xinzeng
        $start_unixtime_pay = strtotime($_GET['query_start_date_pay2']);
        $end_unixtime_pay = strtotime($_GET['query_end_date_pay2']);
        if ($start_unixtime_pay || $end_unixtime_pay) {
            $payment_time = array('between', array($start_unixtime_pay, $end_unixtime_pay));
              $order_list = $model_order->getOrderList( array('payment_time'=>$payment_time));
          foreach ($order_list as $k => $v) {
         }
         $arr2 = array_reduce($order_list, create_function('$result, $v', '$result[] = $v["order_id"];return $result;'));
              $condition2['order_id'] = array('in',$arr2);
        }
        // var_dump($arr2);die;
        // var_dump($where);die;

         
     
         if($_GET['address_id']) {
             $order_common= Model('order_common');
             $array=$order_common->where(array('reciver_ziti_id'=>$_GET['address_id']))->select();
                 foreach ($array as $k => $v) {
                 }
         $arr3 = array_reduce($array, create_function('$result, $v', '$result[] = $v["order_id"];return $result;'));
              $condition2['order_id'] = array('in',$arr3);
        }
         if($arr2&&$arr3){
            $condition2['order_id'] = array('in',array_intersect($arr2,$arr3));//收货人和地址同时存在筛选相同的order_id
            $result=array_intersect($arr2,$arr3);
        }
        // var_dump($condition2);die;
         if(!empty($where)){
                $goods_id = $model_goods->getGoodsList($where);
                foreach ($goods_id as $key => $value) {
                    $goods_id_array[]=$value['goods_id'];
                }
               $condition['goods_id'] = array('in',$goods_id_array);
               $order_goods = $model_order->getOrderGoodsList( $condition);
              foreach ($order_goods as $key => $value) {
                  $order_goodsid[]=$value['order_id'];
              }
              if($order_goodsid){
                if($result){
                    $order_goodsid=array_unique( $order_goodsid);
                    $condition2['order_id'] = array('in',array_intersect($result,$order_goodsid));
                }else{
                    if($arr2){
                       $order_goodsid=array_unique( $order_goodsid);
                    $condition2['order_id'] = array('in',array_intersect($arr2,$order_goodsid));
                    }else if($arr3){
                       $order_goodsid=array_unique( $order_goodsid);
                    $condition2['order_id'] = array('in',array_intersect($arr3,$order_goodsid));
                    }else{
                        $condition2['order_id'] = array('in',$order_goodsid);
                    }
                  
                }
              }
          }
         
          $data = $model_order->getOrderGoodsList($condition2);
           // var_dump($condition2);die;
        //表格数组
        $model_order = Model('order');
        ini_set('max_execution_time', '0');
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
                Tpl::showpage('store_export_goods.excel');
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
        $tableheader = array( '商品名称', '商品数量','子订单号', '支付时间', '商品单价', '商品成本价','商品总成本价','商品总价', '优惠券优惠', '实际支付金额', '优惠券');
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
            $model_order = Model('order');
             $data = $model_order->getOrderList(array('order_id'=>$v['order_id']),'','*','order_id desc','',array('order_common'));
             // var_dump($data );die;
             $data=$data[$v['order_id']];
             $zongjia=$v['goods_num']*$v['goods_price'];
             $zongchengben=$v['goods_num']*$v['goods_cost_price'];
             if(!empty($data['extend_order_common']['voucher_code'])){
                 $voucher_code=$data['extend_order_common']['voucher_code'];
                    foreach ($voucher_code as $key => $value) {
                       $p=$value['voucher_code'];
                    }
                  $model_voucher= Model('voucher');
                 $voucher=Model()->query("SELECT voucher_title FROM 718shop_voucher where voucher_code=\"$p\" ");
                 $voucher_name=$voucher[0]['voucher_title'];
             }else{
                 $voucher_name='';
             }
             if($data['payment_time']>0){
               $payment_time=date('Y-m-d H:i:s', $data['payment_time']);
               $order_data[] =  array(
                $v['goods_name'],
                $v['goods_num'],
                $data['order_sn'],
                $payment_time,
                 $v['goods_price'],
                 $v['goods_cost_price'],
                 $zongchengben,
                $zongjia,
                $v['voucher_price'],
                $v['goods_pay_price'],
                $voucher_name,
            );
             }
           
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
     * 订单列表
     *
     */
    public function indexOp()
    {
         //关联发货人
        $daddress_list = Model('daddress')->getAddressList(array('store_id' => $_SESSION['store_id']),'address_id,seller_name');
        Tpl::output('daddress_list', $daddress_list);
       
         //显示自提地址列表(搜索)
         $condition2 = array();
         $model_daddress = Model('ziti_address');
         $address_list = $model_daddress->getAddressList($condition2);
         $pei_list =Model()->table('peisong')->where(array('id'=>array('gt',0)))->field('id,p_name')->select();
         Tpl::output('pei_list',$pei_list); 
         // var_dump($address_list);die;
         Tpl::output('address_list',$address_list); 
         Tpl::showpage('store_export_goods.index');
    }

    public function export_order_tuanOp(){
        // echo '<pre>';print_r($_GET);
        $condition = "WHERE 1";
        //支付时间  xinzeng
        $if_start_time_pay = strtotime($_GET['query_start_date_pay2_tuan']);
        $if_end_time_pay = strtotime($_GET['query_end_date_pay2_tuan']);
        $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_date_pay2_tuan']) : null;
        $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_date_pay2_tuan']) : null;
        if ($start_unixtime_pay || $end_unixtime_pay) {
            $condition .= " AND order1.payment_time BETWEEN $start_unixtime_pay AND $end_unixtime_pay ";
            // $condition['order.payment_time'] = array('between', array($start_unixtime_pay, $end_unixtime_pay));
        }
        //发货时间  xinzeng
        $if_start_time_deliver = strtotime($_GET['query_start_date_deliver_tuan']);
        $if_end_time_deliver = strtotime($_GET['query_end_date_deliver_tuan']);
        $start_unixtime_deliver = $if_start_time_deliver ? strtotime($_GET['query_start_date_deliver_tuan']) : null;
        $end_unixtime_deliver = $if_end_time_deliver ? strtotime($_GET['query_end_date_deliver_tuan']) : null;
        if ($start_unixtime_deliver || $end_unixtime_deliver) {
            $condition .= " AND order1.ruku_time BETWEEN $start_unixtime_deliver AND $end_unixtime_deliver ";
            // $condition['order.payment_time'] = array('between', array($start_unixtime_deliver, $end_unixtime_deliver));
        }

        $address_id = 0;
        if($_GET['address_id']){
            $condition .= "AND order_common.reciver_ziti_id = ".$_GET['address_id']." ";
            $address_id = 1;
        }

        $data = Model('order')->getTuanOrderList($condition);
        
        $this->excel_order_tuan($data,$_GET['query_start_date_pay2_tuan'],$_GET['query_end_date_pay2_tuan'],$address_id);
    }

    private function excel_order_tuan($data,$start_time,$end_time,$address_id){
        //创建表内容
        $arrayData = array(array('order_sn'=>'订单号','goods_name'=>'商品名称','goods_num'=>'商品数量','seller_name'=>'发货人','reciver_name'=>'收货人姓名','ziti_address'=>'收货人地址','mobile'=>'收货人电话','order_sn_sub'=>'子订单号','buyer_name'=>'买家','payment_time'=>'支付时间','finnshed_time'=>'完成时间','goods_serial'=>'商品货号','goods_price'=>'商品单价','goods_cost_price'=>'商品成本','all_goods_cost_price'=>'商品总成本','all_goods_price'=>'商品总价','voucher_price'=>'优惠券优惠金额','goods_pay_price'=>'实际支付金额','order_amount'=>'订单总额','payment_code'=>'支付方式','ruku_time'=>'发货时间','order_state'=>'订单状态','goods_type'=>'促销信息','voucher_title'=>'代金券','gc1_name'=>'商品一级分类','commis_rate'=>'佣金比例','commission'=>'佣金'));

        foreach ($data as $key => $value) {
            $value['order_sn_sub'] = $value['order_sn'].$value['goods_id'];//子订单号

            //--------订单状态--------
            if($value['order_state']==0){
                $state = '已取消';
            }elseif($value['order_state']==10){
                $state = '待付款';
            }elseif($value['order_state']==20){
                $state = '待发货';
            }elseif($value['order_state']==30){
                $state = '待收货';
            }elseif($value['order_state']==40){
                $state = '交易完成';
            }else{
                $state = '未知状态';
            }
            // 全部退款
            if($value['order_refund_state']==2){
                $state = '已关闭';
            }elseif($value['order_refund_state']==1){
                if($value['seller_state'] ==2 and $value['refund_state'] ==3){
                    $state = '部分退款';
                }
            }
            $value['order_state'] = $state;
            //--------订单状态--------

            // //--------退款状态备注--------
            // $value['seller_refund_state'] = '';
            // if(in_array($value['seller_state'],[1,2]) and in_array($value['refund_state'],[1,2])){
            //     $value['seller_refund_state'] = '退款中';
            // }
            // if($value['seller_state'] ==2 and $value['refund_state'] ==3){
            //     $value['seller_refund_state'] = '退款完成';
            // }
            // if($value['seller_state'] ==3 and $value['refund_state'] ==3){
            //     $value['seller_refund_state'] = '退款失败';
            // }
            // //--------退款状态备注--------

            // //--------商家处理状态--------
            // if($value['seller_state']==1){
            //     $seller_state = '待审核';
            // }elseif($value['seller_state']==2){
            //     $seller_state = '同意';
            // }elseif($value['seller_state']==3){
            //     $seller_state = '不同意';
            // }else{
            //     $seller_state = '';
            // }
            // $value['seller_state'] = $seller_state;
            // //--------商家处理状态--------

            // //--------平台确认--------
            // if($value['refund_state']==1){
            //     $refund_state = '处理中';
            // }elseif($value['refund_state']==2){
            //     $refund_state = '待管理员处理';
            // }elseif($value['refund_state']==3){
            //     $refund_state = '已完成';
            // }else{
            //     $refund_state = '';
            // }
            // $value['refund_state'] = $refund_state;
            // //--------平台确认--------

            //--------促销信息--------
            if($value['goods_type'] == 0){
                $goods_type = '普通商品';
            }elseif($value['goods_type'] == 1){
                $goods_type = '阶梯价';
            }elseif($value['goods_type'] == 2){
                $goods_type = '团购';
            }elseif($value['goods_type'] == 3){
                $goods_type = '新人专享';
            }elseif($value['goods_type'] == 4){
                $goods_type = '限时秒杀';
            }elseif($value['goods_type'] == 5){
                $goods_type = '周边商家';
            }else{
                $goods_type = '无活动';
            }
            $value['goods_type'] = $goods_type;
            //--------促销信息--------
            
            $value['commission'] = number_format($value['goods_pay_price'] * $value['commis_rate'] / 100,2);//佣金
            //日期处理
            // $value['add_time'] = $value['add_time']?date("Y-m-d H:i:s",$value['add_time']):'';
            $value['payment_time'] = $value['payment_time']?date("Y-m-d H:i:s",$value['payment_time']):'';
            $value['finnshed_time'] = $value['finnshed_time']?date("Y-m-d H:i:s",$value['finnshed_time']):'';
            $value['ruku_time'] = $value['ruku_time']?date("Y-m-d H:i:s",$value['ruku_time']):'';
            //数据格式处理
            $value['order_sn'] = "".$value['order_sn']." ";
            $value['goods_serial'] = "".$value['goods_serial']." ";
            $value['mobile'] = "".$value['mobile']." ";
            
            //订单号、总单号去重
            if($key>0){
                if($data[$key]['order_sn'] == $data[$key-1]['order_sn']){
                    $value['order_sn'] = '';
                }
                // if($data[$key]['z_order_sn'] == $data[$key-1]['z_order_sn'] && $data[$key]['order_sn'] == $data[$key-1]['order_sn']){
                //     $value['z_order_sn'] = '';
                // }
            }

            array_push($arrayData, $value);
        }
        
        try {
            $spreadsheet = new Spreadsheet();
            $workSheet = $spreadsheet->getActiveSheet();
            if($address_id>0){
                $workSheet->setCellValue('A1', $start_time.'~'.$end_time.$arrayData[1]['ziti_address']."团长佣金费用结算明细");
                $filename = $start_time.'~'.$end_time.$arrayData[1]['ziti_address']."团长佣金费用结算明细";
            }else{
                $workSheet->setCellValue('A1', $start_time.'~'.$end_time."团长佣金费用结算明细");
                $filename = $start_time.'~'.$end_time."团长佣金费用结算明细";
            }
            $workSheet->getStyle('A1')->getFont()->setBold(true);
            $styleArray = [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ];
            $workSheet->getStyle('A1')->applyFromArray($styleArray);
            $workSheet->mergeCells('A1:AA1');
            foreach($arrayData as $key=>$value){
                $workSheet->setCellValue('A'. ($key+2), $value['order_sn']);
                $workSheet->setCellValue('B'. ($key+2), $value['goods_name']);
                $workSheet->setCellValue('C'. ($key+2), $value['goods_num']);
                $workSheet->setCellValue('D'. ($key+2), $value['seller_name']);
                $workSheet->setCellValue('E'. ($key+2), $value['reciver_name']);
                $workSheet->setCellValue('F'. ($key+2), $value['ziti_address']);
                $workSheet->setCellValue('G'. ($key+2), $value['mobile']);
                $workSheet->setCellValue('H'. ($key+2), $value['order_sn_sub']);
                $workSheet->setCellValue('I'. ($key+2), $value['buyer_name']);
                $workSheet->setCellValue('J'. ($key+2), $value['payment_time']);
                $workSheet->setCellValue('K'. ($key+2), $value['finnshed_time']);
                $workSheet->setCellValue('L'. ($key+2), $value['goods_serial']);
                $workSheet->setCellValue('M'. ($key+2), $value['goods_price']);
                $workSheet->setCellValue('N'. ($key+2), $value['goods_cost_price']);
                $workSheet->setCellValue('O'. ($key+2), $value['all_goods_cost_price']);
                $workSheet->setCellValue('P'. ($key+2), $value['all_goods_price']);
                $workSheet->setCellValue('Q'. ($key+2), $value['voucher_price']);
                $workSheet->setCellValue('R'. ($key+2), $value['goods_pay_price']);
                $workSheet->setCellValue('S'. ($key+2), $value['order_amount']);
                $workSheet->setCellValue('T'. ($key+2), $value['payment_code']);
                $workSheet->setCellValue('U'. ($key+2), $value['ruku_time']);
                $workSheet->setCellValue('V'. ($key+2), $value['order_state']);
                $workSheet->setCellValue('W'. ($key+2), $value['goods_type']);
                $workSheet->setCellValue('X'. ($key+2), $value['voucher_title']);
                $workSheet->setCellValue('Y'. ($key+2), $value['gc1_name']);
                $workSheet->setCellValue('Z'. ($key+2), $value['commis_rate']);
                $workSheet->setCellValue('AA'. ($key+2), $value['commission']);
            }
    
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
            header('Cache-Control: max-age=0');
        
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            
        } catch (Throwable $th) {
            //throw $th;
            echo "Captured Throwable: " . $th->getMessage() . PHP_EOL;
        }
    }

    public function export_order_faOp(){
        $condition = " WHERE 1";
        $if_start_time_pay = strtotime($_GET['query_start_date_pay2_fa']);
        $if_end_time_pay = strtotime($_GET['query_end_date_pay2_fa']);
        $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_date_pay2_fa']) : null;
        $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_date_pay2_fa']) : null;
        if ($start_unixtime_pay || $end_unixtime_pay) {
            $condition .= " AND order1.payment_time BETWEEN $start_unixtime_pay AND $end_unixtime_pay ";
        }

        if ($_GET['peisong'] >0) {//配送方式
            $deliever_ids = Model()->table('peisong')->getfby_id($_GET['peisong'],'deliever_id');
            $condition .= " AND order_goods.deliverer_id in (".$deliever_ids.") ";
        }elseif ($_GET['daddress_id'] >0) {//发货人
            $condition .= " AND order_goods.deliverer_id = ".$_GET['daddress_id'];
        }

        if($_GET['address_id']){
            $condition .= " AND order_common.reciver_ziti_id = ".$_GET['address_id']." ";
        }

        $data = Model('order')->getFaOrderList($condition);

        $this->excel_order_fa($data,$_GET['query_start_date_pay2_fa'],$_GET['query_end_date_pay2_fa']);
    }

    private function excel_order_fa($data,$start_time,$end_time){
        //创建表内容
        $arrayData = array(array('order_sn'=>'订单号','goods_name'=>'商品名称','goods_num'=>'商品数量','seller_name'=>'发货人','order_sn_sub'=>'子订单号','payment_time'=>'支付时间','goods_price'=>'商品单价','goods_cost_price'=>'商品成本','all_goods_cost_price'=>'商品总成本','all_goods_price'=>'商品总价','voucher_price'=>'优惠券优惠金额','goods_pay_price'=>'实际支付金额','payment_code'=>'支付方式','order_state'=>'订单状态','goods_type'=>'促销信息','voucher_title'=>'代金券'));
        $filename = $start_time.'~'.$end_time."供货商结算明细";

        foreach ($data as $key => $value) {
            $value['order_sn_sub'] = $value['order_sn'].$value['goods_id'];//子订单号

            //--------订单状态--------
            if($value['order_state']==0){
                $state = '已取消';
            }elseif($value['order_state']==10){
                $state = '待付款';
            }elseif($value['order_state']==20){
                $state = '待发货';
            }elseif($value['order_state']==30){
                $state = '待收货';
            }elseif($value['order_state']==40){
                $state = '交易完成';
            }else{
                $state = '未知状态';
            }
            // 全部退款
            if($value['order_refund_state']==2){
                $state = '已关闭';
            }elseif($value['order_refund_state']==1){
                if($value['seller_state'] ==2 and $value['refund_state'] ==3){
                    $state = '部分退款';
                }
            }
            $value['order_state'] = $state;
            //--------订单状态--------

            //--------促销信息--------
            if($value['goods_type'] == 0){
                $goods_type = '普通商品';
            }elseif($value['goods_type'] == 1){
                $goods_type = '阶梯价';
            }elseif($value['goods_type'] == 2){
                $goods_type = '团购';
            }elseif($value['goods_type'] == 3){
                $goods_type = '新人专享';
            }elseif($value['goods_type'] == 4){
                $goods_type = '限时秒杀';
            }elseif($value['goods_type'] == 5){
                $goods_type = '周边商家';
            }else{
                $goods_type = '无活动';
            }
            $value['goods_type'] = $goods_type;
            //--------促销信息--------
            
            //日期处理
            $value['payment_time'] = $value['payment_time']?date("Y-m-d H:i:s",$value['payment_time']):'';
            //数据格式处理
            $value['order_sn'] = "".$value['order_sn']." ";
            
            //订单号、总单号去重
            if($key>0){
                if($data[$key]['order_sn'] == $data[$key-1]['order_sn']){
                    $value['order_sn'] = '';
                }
            }
            array_push($arrayData, $value);
        }
        
        try {
            $spreadsheet = new Spreadsheet();
            $workSheet = $spreadsheet->getActiveSheet();
            foreach($arrayData as $key=>$value){
                $workSheet->setCellValue('A'. ($key+1), $value['order_sn']);
                $workSheet->setCellValue('B'. ($key+1), $value['goods_name']);
                $workSheet->setCellValue('C'. ($key+1), $value['goods_num']);
                $workSheet->setCellValue('D'. ($key+1), $value['seller_name']);
                $workSheet->setCellValue('E'. ($key+1), $value['order_sn_sub']);
                $workSheet->setCellValue('F'. ($key+1), $value['payment_time']);
                $workSheet->setCellValue('G'. ($key+1), $value['goods_price']);
                $workSheet->setCellValue('H'. ($key+1), $value['goods_cost_price']);
                $workSheet->setCellValue('I'. ($key+1), $value['all_goods_cost_price']);
                $workSheet->setCellValue('J'. ($key+1), $value['all_goods_price']);
                $workSheet->setCellValue('K'. ($key+1), $value['voucher_price']);
                $workSheet->setCellValue('L'. ($key+1), $value['goods_pay_price']);
                $workSheet->setCellValue('M'. ($key+1), $value['payment_code']);
                $workSheet->setCellValue('N'. ($key+1), $value['order_state']);
                $workSheet->setCellValue('O'. ($key+1), $value['goods_type']);
                $workSheet->setCellValue('P'. ($key+1), $value['voucher_title']);
            }
    
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
            header('Cache-Control: max-age=0');
        
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            
        } catch (Throwable $th) {
            //throw $th;
            echo "Captured Throwable: " . $th->getMessage() . PHP_EOL;
        }
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
