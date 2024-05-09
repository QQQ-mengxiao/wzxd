<?php
/**
 * 商家中心销售数据导出
 *
 **/
require_once BASE_ROOT_PATH.'/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

defined('In718Shop') or exit('Access Invalid!');

class store_export_salesdata extends BaseSellerControl {

    /**
     *
     */
    public function indexOp() {
        echo "哈哈";
    }
    
    
    /**
     * 用户中心右边，小导航
     */
    private function profile_menu($menu_type = '', $menu_key = '') {
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


    /**
     * 销售数据导出
     */
    public function exportSalesDataOp($start_time_pay, $end_time_pay, $zitiAddress) {

        // 处理数据
        // 支付开始时间
        $payStartTime = $start_time_pay ? strtotime($start_time_pay) : null;
        // 支付结束时间
        $payEndTime = $end_time_pay ? strtotime($end_time_pay) : null;
        // 选择的时间只有年月日，支付结束时间，延迟1天
        $payEndTime1 = $payStartTime? strtotime('1 day',$payEndTime) : null;
        
        // 创建查询条件
        $condition = $this->createCondition($payStartTime, $payEndTime, $zitiAddress);

        // 查询数据
        $data = $this->getSaleDataExportList($condition);

        // 生成excel数据
        $dataArray = $this->createExcelDataBySqlData($data);

        // 生成表格
        $this->createSalesDataExcel($dataArray, $zitiName);
    }

    
    /**
     * 创建查询条件
     */
    private function createCondition($payStartTime, $payEndTime, $zitiAddress) {

        $condition = "";

        if ($payStartTime && $payEndTime) {
            $condition .= "AND so.payment_time BETWEEN $payStartTime AND $payEndTime ";
        } else {
            exit("请选择支付时间");
        }

        //自提地址
        if($zitiAddress > 0){
            $condition .= "AND order_common.reciver_ziti_id=$zitiAddress ";
        }

        return $condition;
    }

    
    /**
     * 生成excel数据
     */
    private function createExcelDataBySqlData($data) {

        // 处理数据
        $dataArray = array();
        // 列标题
        $tableheader = array('自提点','日期','订单量', '销售额（元）', '成本', '利润', '利润率', '社区服务销售额（元）', '总优惠券', '陆港优惠券');
        array_push($dataArray, $tableheader);
        
        $totalNum = 0;
        $totalAmount = 0;
        $totalCost = 0;
        $totalProfit = 0;
        $totalProfitRate = 0;
        $totalServiceAmount = 0;
        $totalVoucher = 0;
        $totalLgVoucher = 0;
        // var_dump($data );die;
        foreach ($data as $key => $value) {
            $tempArray = array();

            // 自提点
            $zitiName = $value['ziti_name'];
             if(empty($value['ziti_name']) ){
                    $zitiName = '邮寄订单';
                }
            // 自提点去重
            if($key>0){
               
                if($data[$key]['ziti_name'] == $data[$key-1]['ziti_name']){
                    $zitiName = '';
                }
            }
            $tempArray[] = $zitiName;
            
            // 日期，订单量，销售额，成本
            array_push($tempArray, $value['everyday'],$value['count'],$value['amount'],$value['cost']);
            // 利润（销售额-成本）
            $profit = $value['amount']-$value['cost'];
            $tempArray[] = $profit;
            // 利润率
            $profitRate = round($profit/$value['amount']*100,2).'%';
            
            $tempArray[] = $profitRate;
            // 社区服务销售额，总优惠券，陆港优惠券
            array_push($tempArray, $value['service'],$value['voucher'],$value['lgVoucher']);

            //
            array_push($dataArray, $tempArray);
            
            // 总计
            $totalNum += $value['count'];
            $totalAmount += $value['amount'];
            $totalCost += $value['cost'];
            $totalProfit += $profit;
            $totalServiceAmount += $value['service'];
            $totalVoucher += $value['voucher'];
            $totalLgVoucher += $value['lgVoucher'];
        }
        // 合计
        $totalProfitRate = round($totalProfit/$totalAmount*100,2).'%';
        $total = array('全部','合计',$totalNum, $totalAmount, $totalCost, $totalProfit, $totalProfitRate, $totalServiceAmount, $totalVoucher, $totalLgVoucher);
        array_push($dataArray, $total);
        
        return $dataArray;
    }

    
    /**
     * 创建销售数据表格
     */
    private function createSalesDataExcel($data, $zitiName) {

        try {
            //code...
            $spreadsheet = new Spreadsheet();
            $workSheet = $spreadsheet->getActiveSheet();

            // 样式
            $this->defineSalesDataExcelStyles($workSheet, $data);

            //表头内容
            $workSheet->setCellValue('A1', '线上物资小店销售日报表');

            // 表数据
            $workSheet->fromArray($data, NULL, 'A2');

            // 文件名
            $filename = date('m.d', time()).'销售报表'.'.xlsx';
            // redirect output to client browser
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename='.$filename);
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
            
        } catch (Throwable $th) {
            //throw $th;
            echo "Captured Throwable: " . $th->getMessage() . PHP_EOL;
        }
    }

    
    /**
     * 设置报表样式
     */
    private function defineSalesDataExcelStyles($workSheet, $data) {

        // 表头合并单元格
        $workSheet->mergeCells('A1:J1');
        // 设置表头高度
        $workSheet->getRowDimension('1')->setRowHeight(40);
        // 表头字体大小、加粗、垂直居中
        $headerStyleArray = [
            'font' => [
                'bold' => true,
                'size' => 18,
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $workSheet->getStyle('A1')->applyFromArray($headerStyleArray);

        // 设置标题高度
        $workSheet->getRowDimension('2')->setRowHeight(30);
        
        // 标题自动换行
        $workSheet->getStyle('A2:J2')->getAlignment()->setWrapText(true);

        // 总行数
        $rowCount = count($data)+1;

        // 设置所有框线
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    // 'color' => ['argb' => 'FFFF0000'],
                ],
            ],
        ];
        $workSheet->getStyle('A1:J'.$rowCount)->applyFromArray($styleArray);

        // 水平居中
        $workSheet->getStyle('A1:J'.$rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        // 垂直居中
        $workSheet->getStyle('A1:J'.$rowCount)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // 设置列宽
        $workSheet->getDefaultColumnDimension()->setWidth(10);
        $workSheet->getColumnDimension('A')->setWidth(18);
        $workSheet->getColumnDimension('B')->setWidth(12);

        // 修改字体颜色
        $workSheet->getStyle("A$rowCount:J$rowCount")->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
    }

    
    /**
     * add,2022.03.17,zyf
     * 销售额信息
     */
    public function getSaleDataExportList($condition) {
        $columns = "ziti.seller_name AS ziti_name,FROM_UNIXTIME(so.payment_time,'%Y-%m-%d') AS everyday,COUNT(DISTINCT CASE WHEN order_goods.deliverer_id !=90 THEN so.order_id ELSE NULL END) AS count,SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0)) AS amount,SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_cost_price*order_goods.goods_num,0)) AS cost,SUM(IF (order_goods.deliverer_id=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0)) AS service,SUM(IF (order_goods.deliverer_id !=90,order_goods.voucher_price,0)) AS voucher,SUM(IF (voucher_template.voucher_t_is_lg=1,order_goods.voucher_price,0)) AS lgVoucher ";

        $tables = " 718shop_order so INNER JOIN 718shop_order_common order_common ON so.order_id=order_common.order_id LEFT JOIN 718shop_order_goods order_goods ON so.order_id=order_goods.order_id LEFT JOIN 718shop_voucher voucher ON order_common.voucher_id=voucher.voucher_id LEFT JOIN 718shop_voucher_template voucher_template ON voucher.voucher_t_id=voucher_template.voucher_t_id LEFT JOIN 718shop_ziti_address ziti ON order_common.reciver_ziti_id=ziti.address_id LEFT JOIN 718shop_refund_return refund ON order_goods.rec_id=refund.order_goods_id AND refund.seller_state=2 AND refund.refund_state=3 ";

        $preCondition = "WHERE NOT (so.is_zorder=0 AND so.order_state=20) ";

        $endCondition = "AND so.order_state IN (20,30,40) AND NOT (IFNULL(refund.seller_state,0)=2 AND IFNULL(refund.refund_state,0)=3 AND IFNULL(refund.refund_amount,0)=order_goods.goods_pay_price) GROUP BY seller_name,everyday ORDER BY ziti.address_id,everyday ASC ";

        $sql = "SELECT ".$columns."FROM ".$tables.$preCondition.$condition.$endCondition;

//print_r($sql);die;
        $list = Model()->query($sql);

        return $list;
    }
}
