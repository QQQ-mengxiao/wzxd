<?php
/**
 * 商家中心订单销售额导出
 *
 *
 *
 **/
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/IOFactory.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/Reader/Excel2007.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/Reader/IReader.php'; //引入文件
defined('In718Shop') or exit('Access Invalid!');

class store_export_typeControl extends BaseSellerControl
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
        // echo'22222222222222';die;
        $model_order = Model('order');
        $condition = array();
        $sale_data=  array();
        //订单状态
        $condition['is_zorder'] =array('neq',0);
        $condition['store_id'] = $_SESSION['store_id'];
        // 支付时间  xinzeng
        # $if_start_time_pay = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date_pay2']);
        # $if_end_time_pay = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date_pay2']);
        // var_dump(  $if_start_time_pay);die;
        $start_unixtime_pay = strtotime($_GET['query_start_date_pay2']);
        $end_unixtime_pay = strtotime($_GET['query_end_date_pay2']);
        if ($start_unixtime_pay || $end_unixtime_pay) {
            $condition['payment_time'] = array('between', array($start_unixtime_pay, $end_unixtime_pay));
        }
       
       // var_dump($condition);die;
         ini_set('max_execution_time', '0');
        $field = 'SUM(order_amount) as orderamount ';
        // var_dump($condition);die;
        $result = $model_order->getOrdersum($condition, '', $field);
        $amount=$result[0]['orderamount'];
        $count = $model_order->getOrderCount($condition); 
        // var_dump($count);die;
            $data = $model_order->getOrderList($condition, '', '*', 'order_id desc','', array('order_goods'));
            // var_dump($data);die;
            $count_xinren=0;
            $count_miaosha=0;
            $cuont_jimai=0;
            $cuont_other=0;
            $amount_xinren=0;
            $amount_miaosha=0;
            $amount_jimai=0;
            $amount_other=0;
           foreach ($data  as $key => $value) {
            $typearray=array();
               foreach ($value['extend_order_goods'] as $k => $v) {
                 $typearray[]=$v['goods_type'];
                 if($v['goods_type']==3){
                     // $count_xinren=$count_xinren+1;
                       $amount_xinren=$amount_xinren+$v['goods_pay_price'];
                   }else if($v['goods_type']==4){
                    // $count_miaosha=$count_miaosha+1;
                             $amount_miaosha=$amount_miaosha+$v['goods_pay_price'];
                   }else if($v['goods_type']==5){
                      // $cuont_jimai=$cuont_jimai+1;
                               $amount_jimai=$amount_jimai+$v['goods_pay_price'];
                   }else{
                    // $cuont_other=$cuont_other+1;
                     $amount_other=$amount_other+$v['goods_pay_price'];
                   }
               }
               // var_dump($typearray);die;
               if(in_array(3, $typearray)){
                        $count_xinren=$count_xinren+1;
               }
               if(in_array(4, $typearray)){
                          $count_miaosha=$count_miaosha+1;
               }
                if(in_array(5, $typearray)){
                           $cuont_jimai=$cuont_jimai+1;
                } 
                if(in_array(0, $typearray)){
                 $cuont_other=$cuont_other+1;
               }
                if(in_array(1, $typearray)){
                 $cuont_other=$cuont_other+1;
               }
           }
           // var_dump($cuont_jimai);die;
           $sale_data[0]['payment_time_start']= $start_unixtime_pay;
           $sale_data[0]['payment_time_end']= $end_unixtime_pay;
           $sale_data[0]['count_xinren']= $count_xinren;
           $sale_data[0]['count_miaosha']= $count_miaosha;
           $sale_data[0]['cuont_jimai']= $cuont_jimai;
           $sale_data[0]['cuont_other']= $cuont_other;
           $sale_data[0]['amount_xinren']= $amount_xinren;
           $sale_data[0]['amount_miaosha']= $amount_miaosha;
           $sale_data[0]['amount_jimai']= $amount_jimai;
           $sale_data[0]['amount_other']= $amount_other;
            $sale_data[0]['sum']= $count;
           $sale_data[0]['amount']= $amount;
           // var_dump($sale_data);die;
            $this->excel_order(array_values($sale_data));
        
    }

    private function excel_order($data_tmp)
    {
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K','L');
        $tableheader = array('支付开始时间', '支付截止时间','新人专享订单数', '新人专享销售额', '限时秒杀订单数', '限时秒杀销售额','即买即送订单数', '即买即送销售额','其他订单数', '其他销售额','合计订单数', '合计销售额');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
            $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
        }
        $order_data = [];
        // var_dump($data_tmp);die;
        foreach ($data_tmp as $k => $v) {
            $order_data[$k] = array(
                date('Y-m-d H:i:s', $v['payment_time_start']),
                date('Y-m-d H:i:s', $v['payment_time_end']),
                $v['count_xinren'],
                $v['amount_xinren'],
                $v['count_miaosha'],
                 $v['amount_miaosha'],
                $v['cuont_jimai'],
                $v['amount_jimai'],
                $v['cuont_other'],
                $v['amount_other'],
                 $v['sum'],
                $v['amount'],
            );
        }
        // var_dump($order_data);die;
        //填充表格信息
       for ($i = 2; $i <= count($order_data) + 1; $i++) {
            $j = 0;
            foreach ($order_data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i","$value",PHPExcel_Cell_DataType::TYPE_STRING);
                $j++;
            }
        }
        // var_dump($order_data);die;
        //创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        $filename = '销售额-' . date('Y-m-d-H', time()) . '.xls';
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
     * 生成导出预存款充值excel
     *
     * @param array $data
     */
    private function createExcel($data = array()){
        Language::read('export');
        import('libraries.excel');
        $excel_obj = new Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
        //header
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'支付开始时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'支付截止时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'新人专享订单数(单)');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'新人专享销售额(元)');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'限时秒杀订单数(单)');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'限时秒杀销售额(元)');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'即买即送订单数(单)');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'即买即送销售额(元)');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'其他订单数(单)');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'其他销售额(元)');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'合计订单数(单)');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'合计销售额(元)');
        foreach ((array)$data as $k=>$v){
            $tmp = array();
            $tmp[] = array('data'=>date('Y-m-d H:i:s', $v['payment_time_start']));
            $tmp[] = array('data'=>date('Y-m-d H:i:s', $v['payment_time_end']));
            $tmp[] = array('data'=>$v['count_xinren']);
            $tmp[] = array('data'=>$v['amount_xinren']);
            $tmp[] = array('data'=>$v['count_miaosha']);
            $tmp[] = array('data'=>$v['amount_miaosha']);
            $tmp[] = array('data'=>$v['cuont_jimai']);
            $tmp[] = array('data'=>$v['amount_jimai']);
            $tmp[] = array('data'=>$v['cuont_other']);
            $tmp[] = array('data'=>$v['amount_other']);
            $tmp[] = array('data'=>$v['sum']);
            $tmp[] = array('data'=>$v['amount']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(L('exp_yc_yckcz'),CHARSET));
        $excel_obj->generateXML($excel_obj->charset(L('exp_yc_yckcz'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
    }
    /**
     * 订单列表
     *
     */
    public function indexOp()
    {

        Tpl::showpage('store_export_type.index');
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
