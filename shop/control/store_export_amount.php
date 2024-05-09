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

class store_export_amountControl extends BaseSellerControl
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
        $model_order = Model('order');
        $condition = array();
        $sale_data=  array();
        //订单状态
        $condition['order_state'] =array('neq',0);
        $condition['is_zorder'] =array('neq',0);
        $condition['store_id'] = $_SESSION['store_id'];
        //支付时间  xinzeng
        $if_start_time_pay = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date_pay']);
        $if_end_time_pay = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date_pay']);
        $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_date_pay']) : null;
        $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_date_pay'])+86400 : null;
        if ($start_unixtime_pay || $end_unixtime_pay) {
            $condition['payment_time'] = array('between', array($start_unixtime_pay, $end_unixtime_pay));
        }
         $count1 = $model_order->getOrderCount($condition); 
         // var_dump($count1 );die;
        // var_dump($end_unixtime_pay);die;
        $condition['payment_code'] = array('neq','online');
        //表格数组

        ini_set('max_execution_time', '0');
        $field = 'SUM(order_amount) as orderamount ';
        // var_dump($condition);die;
        $result = $model_order->getOrdersum($condition, '', $field);
        $count2 = $model_order->getOrderCount($condition,'','sum(order_id) as order_id'); 
        // var_dump( $count2);die;
        $condition['payment_code'] ='online';
        $result2 = $model_order->getOrdersum($condition, '', $field);
        $count3 = $model_order->getOrderCount($condition); 
        $sum= $result[0]['orderamount']+$result2[0]['orderamount'];
        $sale_data[0]['payment_time_start']=$start_unixtime_pay; 
        $sale_data[0]['payment_time_end']=$end_unixtime_pay;
        $sale_data[0]['count1']=$count1;
        $sale_data[0]['sum']=$sum;
        $sale_data[0]['sum1']=$result[0]['orderamount'];
        if($count3==0){
         $sale_data[0]['sum2']=0;
        }else{
          $sale_data[0]['sum2']=$result2[0]['orderamount'];  
        } 
        $sale_data[0]['count2']=$count2;
        $sale_data[0]['count3']=$count3;
        $sale_data[0]['zhanbi1']=number_format($result[0]['orderamount']/$sum*100, 2);
        if( $sale_data[0]['zhanbi1']==0){
        $sale_data[0]['zhanbi2']=0;
        }else{
         $sale_data[0]['zhanbi2']=100-$sale_data[0]['zhanbi1'];
        } 

        // var_dump( $data);die;

        $count = count($sale_data);//echo $count;die;
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
                $this->excel_order(array_values($sale_data));
            }
        } else {  //下载
            $limit1 = ($_GET['curpage'] - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_order->getOrderList3('', $condition, '', '*', 'order_id desc', "{$limit1},{$limit2}", array('order_goods', 'order_common', 'member', 'goods_kuajing_d'));
            $this->excel_order(array_values($sale_data));
        }
    }

    private function excel_order($data_tmp)
    {
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K');
        $tableheader = array('支付开始时间', '支付截止时间', '订单数','销售额','一卡通订单数','一卡通销售额','一卡通占比','微信订单数','微信销售额', '微信占比');
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
            $order_data[$k] = array(
                date('Y-m-d H:i:s', $v['payment_time_start']),
                date('Y-m-d H:i:s', $v['payment_time_end']),
                $v['count1'],
                $v['sum'],
                $v['count2'],
                $v['sum1'],
                $v['zhanbi1'].'%',
                $v['count3'],
                $v['sum2'],
                $v['zhanbi2'].'%',
            );
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
     * 订单列表
     *
     */
    public function indexOp()
    {

        Tpl::showpage('store_export_amount.index');
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
