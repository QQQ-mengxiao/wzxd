<?php

/**
 * 11点仓库报表导出
 * 新增，20220408，mx
 **/
require_once BASE_ROOT_PATH . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

defined('In718Shop') or exit('Access Invalid!');

class store_export_warehouse_excel11 extends BaseSellerControl
{

    /**
     *
     */
    public function indexOp()
    {
        echo "红红火火恍恍惚惚";
    }
    // 1、循环生成execl到指定临时文件夹
    // 2、使用zip压缩文件
    // 3、下载zip文件


    /**
     * 批量导出仓库报表
     */
    public function batchExportWarehouse11Op($start_time_pay, $end_time_pay, $shipperCategory, $zitiAddress, $type)
    {
        // 发货人类别名称
        if ($shipperCategory > 0) {
            $daddress_list = Model('peisong')->where(array('id' => $shipperCategory))->find();
            $categoryName = $daddress_list['p_name'];
        } else {
            $categoryName = "11点截单";
        }

        // 自提点名称
        if ($zitiAddress > 0) {
            $zitiInfo = Model('ziti_address')->where(array('address_id' => $zitiAddress))->field('seller_name')->find();
            $zitiName = $zitiInfo['seller_name'];
            // 单个表格，直接导出
            $this->exportSingleExcel($start_time_pay, $end_time_pay, $shipperCategory, $zitiAddress, $zitiName, $categoryName, $type);
        } else {
            // 查询所有自提点
            $zitiInfo = Model('ziti_address')->field('address_id,seller_name')->select();
            
            // 循环生成每个自提点的excel
            foreach ($zitiInfo as $k => $v) {
                $zitiAddress = $v['address_id'];
                $zitiName = $v['seller_name'];
                // 生成本地表格
                $this->createLocalExcel($start_time_pay, $end_time_pay, $shipperCategory, $zitiAddress, $zitiName, $categoryName, $type);
            }
            $this->exportZip();
        }
    }

    private function createLocalExcel($start_time_pay, $end_time_pay, $shipperCategory, $zitiAddress, $zitiName, $categoryName, $type)
    {
        $model_order = Model('order');
        // 处理数据
        // 支付开始时间
        $payStartTime = $start_time_pay ? strtotime($start_time_pay) : null;
        // 支付结束时间
        $payEndTime = $end_time_pay ? strtotime($end_time_pay) : null;
        //20点截单支付开始时间
        // 方法1
//        $payStartTime20 = $payStartTime? strtotime(date("Y-m-d",$payStartTime))+20*60*60 : null;
        // 方法2
        // $Y = date("Y",$payStartTime);
        // $m = date("m",$payStartTime);
        // $d = date("d",$payStartTime);
        // $payStartTime20 = $payStartTime? mktime(20,0,0,$m,$d,$Y) : null;
        //此处更改为6点
        $Y = date("Y",$payEndTime);
        $m = date("m",$payEndTime);
        $d = date("d",$payEndTime);
        $payStartTime20 = $payEndTime? mktime(6,0,0,$m,$d,$Y) : null;
        //团购支付开始时间，提前15天
        $payStartTimeYushou = $payStartTime? strtotime('-15 days',$payStartTime) : null;

        //11点截单1天1配类别对应的发货人
        $shippers11 = $this->getShippersByCategoryId(12);
        //20点截单类别对应的发货人
        $shippers20 = $this->getShippersByCategoryId(17);


        //查询11点截单 1天1配 11点-11点
        $condition1 = $this->createCondition($payStartTime, $payEndTime, $shippers11, $zitiAddress, $type, 0);
        $data1 = $model_order->getWarehouseOrderGoodsExportList($condition1);

        //查询11点截单 1天2配 20点-11点
        $condition2 = $this->createCondition($payStartTime20, $payEndTime, $shippers20, $zitiAddress, $type, 0);
        $data2 = $model_order->getWarehouseOrderGoodsExportList($condition2);

        // 团购
        $condition3 = $this->createCondition($payStartTimeYushou, $payEndTime, null, $zitiAddress, $type, 1);
        $data3 = $model_order->getWarehouseOrderGoodsExportList($condition3);

        // 合并数组
        $data = array_merge((array)$data1, (array)$data2, (array)$data3);

        // 按支付时间排序
        array_multisort(array_column($data, 'payment_time'), SORT_ASC, $data);

        if(count($data) > 0) {
            // 生成excel数据
            $dataArray = $this->createExcelDataBySqlData($data);
            // 生成表格
            $this->createWarehouseExcel($dataArray, $zitiName, $categoryName);
        }
    }

    /**
     * 导出zip压缩文件
     */
    private function exportZip() {
        $date = date('Ymd', time());
        $path = BASE_ROOT_PATH . '/download/tmp/batchWarehouseExcelTmp/';
        $zipname = '各自提点仓库报表(11点截单).zip';
        $zip = new ZipArchive();
        $res = $zip->open($path.$zipname, ZipArchive::CREATE);
        if ($res == TRUE) {
            $this->addFileToZip($path, $zip);
            $zip->close();

            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length: " . filesize($path.$zipname));
            header("Content-Disposition: attachment; filename=$date$zipname");
            @readfile($path.$zipname);
            @unlink($path.$zipname);
            $this->removeExcelTmp();
            exit;
        }
    }

    function addFileToZip($path, $zip) {
        $handler = opendir($path); //打开当前文件夹由$path指定。   
        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") { //文件夹文件名字为'.'和‘..'，不要对他们进行操作      
                if (is_dir($path . "/" . $filename)) { // 如果读取的某个对象是文件夹，则递归         
                    $this->addFileToZip($path . "/" . $filename, $zip);
                } else { //将文件加入zip对象         
                    $zip->addFile($path.$filename, $filename);
                }
            }
        }
        @closedir($path);
    }

    /**
     * 根据发货人类别id，获取并返回发货人id
     */
    private function getShippersByCategoryId($id) {
        if ($id > 0) {
            $daddress_list = Model('peisong')->where(array('id' => $id))->find();
            $daddress_ids = $daddress_list['deliever_id'];
            return $daddress_ids;
        }
    }

    /**
     * 创建查询条件。isYushou：团购为1，非团购为0
     */
    private function createCondition($payStartTime, $payEndTime, $shippers, $zitiAddress, $type, $isYushou)
    {

        $condition = "";

        if ($payStartTime && $payEndTime) {
            $condition .= "AND order1.payment_time BETWEEN $payStartTime AND $payEndTime ";
        } else {
            exit("请选择支付时间");
        }

        //发货人类别
        if ($shippers) {
            $condition .= "AND order_goods.deliverer_id IN ($shippers) ";
        }

        //自提地址
        if ($zitiAddress > 0) {
            $condition .= "AND order_common.reciver_ziti_id=$zitiAddress ";
        }

        //订单类型：type=1,待发货；type=2,退款
        if ($type == 1) {
            $condition .= "AND order1.order_state = 20 AND (refund.seller_state is null OR refund.seller_state=3) ";
        } elseif ($type == 2) {
            $condition .= "AND refund.seller_state IN (1,2) ";
        }

        // 非团购
        if ($isYushou == 0) {
            $condition .= "AND order_goods.goods_name NOT REGEXP '团购' ";
        } elseif ($isYushou == 1) {
            if ($payEndTime) {
                //时间格式处理
                $payEndTime += 86400;
                $time = date('n.j', $payEndTime);
                $condition .= "AND order_goods.goods_name REGEXP '团购$time' ";
            } else {
                $condition .= "AND order_goods.goods_name REGEXP '团购' ";
            }
        }

        return $condition;
    }


    /**
     * 生成excel数据
     */
    private function createExcelDataBySqlData($data)
    {
// 处理数据
        $dataArray = array();
        // 列标题
        $tableheader = array('序号','订单号', '收货人', '发货人', '商品数量', '商品名称', '商品货号', '收货人电话', '自提点', '详细地址', '买家','支付时间','自提时间','买家留言','发货备注','订单状态','备注(退款信息)','促销信息','商品总成本');
        array_push($dataArray, $tableheader);


        // 序号，中盛开头为A，中陆为B
        $zhongshengNum = 0; // 中盛中心仓id6
        $zhongluNum = 0; // 仓库排序B专用id 26
        $otherNum = 0; // 其他
        // 获取中盛排序A类别对应的发货人，并转成数组
        $zhongshengShippersStr = $this->getShippersByCategoryId(6);
        $zhongshengShippersArr = explode(',', $zhongshengShippersStr);
        // 获取中陆排序B类别对应的发货人，并转成数组
        $zhongluShippersStr = $this->getShippersByCategoryId(26);
        $zhongluShippersArr = explode(',', $zhongluShippersStr);

        // order_sn,reciver_name,address_id,seller_name,goods_num,goods_name,goods_serial,mobile,ziti_name,detail_address,buyer_name,
        // payment_time,ziti_ladder_time,order_message,deliver_explain,
        // order_state,goods_type,goods_cost_price,order_refund_state,seller_state,refund_state
        foreach ($data as $key => $value) {
            $tempArray = array();

            //订单号
            $orderSn = $value['order_sn'];
            //订单号去重
            if($key>0){
                if($data[$key]['order_sn'] == $data[$key-1]['order_sn']){
                    $orderSn = '';
                }
            }

            // 序号
            $num = "";
            if(!empty($orderSn)) {
                $orderSn = "'".$orderSn;
                // 排序，中盛开头为A，中陆为B

                if(in_array($value['address_id'], $zhongshengShippersArr)){
                    ++$zhongshengNum;
                    $num = 'A'.$zhongshengNum;
                }
                elseif(in_array($value['address_id'], $zhongluShippersArr)){
                    ++$zhongluNum;
                    $num = 'B'.$zhongluNum;
                }
                else {
                    ++$otherNum;
                    $num = $otherNum;
                }
            }
            $tempArray[] = $num;


            $tempArray[] = $orderSn;
            //
            array_push($tempArray, $value['reciver_name'],$value['seller_name'],$value['goods_num'],$value['goods_name'],$value['goods_serial'],"'".$value['mobile'],$value['ziti_name'],$value['detail_address'],"'".$value['buyer_name']);
            //支付时间
            $tempArray[] = $value['payment_time']?date("Y-m-d H:i:s",$value['payment_time']):'';
            //自提时间
            $tempArray[] = $value['ziti_ladder_time']?date("Y-m-d H:i:s",$value['ziti_ladder_time']):'';
            //买家留言，发货备注
            array_push($tempArray, $value['order_message'],$value['deliver_explain']);
            //订单状态
            $state = '';
            switch ($value['order_state']) {
                case 0:
                    $state = '已取消';
                    break;
                case 10:
                    $state = '待付款';
                    break;
                case 20:
                    $state = '待发货';
                    break;
                case 30:
                    $state = '待收货';
                    break;
                case 40:
                    $state = '交易完成';
                    break;
                default:
                    $state = '未知状态';
            }
            if($value['order_refund_state']==2){
                $state = '已关闭';
            }elseif($value['order_refund_state']==1){
                if($value['seller_state'] ==2 and $value['refund_state'] ==3){
                    $state = '部分退款';
                }
            }
            $tempArray[] = $state;
            //备注（退款信息）
            $refundState = '';
            if(in_array($value['seller_state'],[1,2]) and in_array($value['refund_state'],[1,2])){
                $refundState = '退款中';
            }
            elseif($value['seller_state'] ==2 and $value['refund_state'] ==3){
                $refundState = '退款完成';
            }
            elseif($value['seller_state'] ==3 and $value['refund_state'] ==3){
                $refundState = '退款失败';
            }
            $tempArray[] = $refundState;
            //促销信息
            $goodsType = '';
            switch ($value['goods_type']) {
                case 0:
                    $goodsType = '普通商品';
                    break;
                case 1:
                    $goodsType = '阶梯价';
                    break;
                case 2:
                    $goodsType = '团购';
                    break;
                case 3:
                    $goodsType = '新人专享';
                    break;
                case 4:
                    $goodsType = '限时秒杀';
                    break;
                case 5:
                    $goodsType = '周边商家';
                    break;
                default:
                    $goodsType = '无活动';
            }
            // array_push($tempArray, $goodsType);
            $tempArray[] = $goodsType;
            //商品总成本
            $tempArray[] = $value['goods_cost_price']*$value['goods_num'];

            //
            array_push($dataArray, $tempArray);
        }
        return $dataArray;
    }


    /**
     * 创建表格
     */
    private function createWarehouseExcel($data, $zitiName, $categoryName)
    {

        try {
            //code...
            $spreadsheet = new Spreadsheet();
            $workSheet = $spreadsheet->getActiveSheet();

            // 样式
            $this->defineWarehouseExcelStyles($workSheet, $data);

            //表头内容
            $workSheet->setCellValue('A1', date('m-d', time()) . '，'.$categoryName.'，自提点：' . $zitiName);

            // 表数据
            $workSheet->fromArray($data, NULL, 'A2');

            header("Content-type: text/html; charset=utf-8");
            // 文件名
            $filename = date('m.d', time()) . $zitiName . '待发货订单(11点截单)' . '.xlsx';
            $name = iconv("utf-8", "gb2312", $filename);
            // redirect output to client browser
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // header('Content-Disposition: attachment;filename='.$filename);
            // header('Cache-Control: max-age=0');

            $path = BASE_ROOT_PATH . '/download/tmp/batchWarehouseExcelTmp/';
            if (!is_dir($path)) {
                mkdir(iconv("UTF-8", "GBK", $path), 0777, true);
            }

            $writer = new Xlsx($spreadsheet);
            // $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            // $writer->save('php://output');
            $writer->save($path . $name);
        } catch (Throwable $th) {
            //throw $th;
            echo "Captured Throwable: " . $th->getMessage() . PHP_EOL;
        }
    }



    /**
     * 设置报表样式
     */
    private function defineWarehouseExcelStyles($workSheet, $data)
    {

        // 表头合并单元格
        $workSheet->mergeCells('A1:S1');
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


        // 总行数
        $rowCount = count($data) + 1;

        // 设置所有框线
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    // 'color' => ['argb' => 'FFFF0000'],
                ],
            ],
        ];
        $workSheet->getStyle('A1:S' . $rowCount)->applyFromArray($styleArray);

        // 序号左对齐
        $workSheet->getStyle('A3:A' . $rowCount)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // 设置列宽
        $workSheet->getColumnDimension('B')->setWidth(18);
        $workSheet->getColumnDimension('D')->setWidth(12);
        $workSheet->getColumnDimension('F')->setWidth(20);
        $workSheet->getColumnDimension('G')->setWidth(12);
        $workSheet->getColumnDimension('H')->setWidth(13);
        $workSheet->getColumnDimension('I')->setWidth(16);
        $workSheet->getColumnDimension('J')->setWidth(10);
        $workSheet->getColumnDimension('K')->setWidth(10);
        $workSheet->getColumnDimension('L')->setWidth(18);
        $workSheet->getColumnDimension('M')->setWidth(18);
        $workSheet->getColumnDimension('S')->setWidth(10);

        // 数量大于1时，字体加大加粗
        $conditional1 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional1->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $conditional1->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_GREATERTHAN);
        $conditional1->addCondition('1');
        $conditional1->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE);
        $conditional1->getStyle()->getFont()->setBold(true);
        $conditional1->getStyle()->getFont()->setSize(14);

        $conditionalStyles = $workSheet->getStyle('E3:E' . $rowCount)->getConditionalStyles();
        $conditionalStyles[] = $conditional1;

        $workSheet->getStyle('E3:E' . $rowCount)->setConditionalStyles($conditionalStyles);

        // 默认不标红0，标红1
        $isRed = 0;
        foreach ($data as $key => $value) {
            if ($key > 0) {
                // 只在序号值不为空时，修改是否标红
                if ($value[0]) {
                    // 包含A
                    $pos = strpos($value[0], 'A');
                    // 中心仓为0，其他为1
                    if ($pos !== false) {
                        $isRed = 0;
                    } else {
                        $isRed = 1;
                    }
                }

                // 修改字体颜色
                if ($isRed == 1) {
                    $rowNum = $key + 2;
                    $workSheet->getStyle("A$rowNum:S$rowNum")->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
                }
            }
        }
    }


    /**
     * add,20220408,zyf
     * 仓库报表订单信息
     */
    public function getWarehouseOrderGoodsExportList($condition)
    {
        $columns = "order1.order_sn,order_common.reciver_name,daddress.address_id,daddress.seller_name,order_goods.goods_num,order_goods.goods_name,order_goods.goods_serial,SUBSTRING(order_common.reciver_info,INSTR(order_common.reciver_info,'s:11:\"')+6,11) AS mobile,ziti_address.seller_name AS ziti_name,order_common.mall_info AS detail_address,order1.buyer_name,order1.payment_time,order_common.ziti_ladder_time,order_common.order_message,order_common.deliver_explain,order1.order_state,order_goods.goods_type,order_goods.goods_cost_price,order1.refund_state AS order_refund_state,refund.seller_state,refund.refund_state ";

        $tables = "718shop_order order1 INNER JOIN 718shop_order_common order_common ON order1.order_id = order_common.order_id INNER JOIN 718shop_order_goods order_goods ON order1.order_id = order_goods.order_id LEFT JOIN 718shop_daddress daddress ON order_goods.deliverer_id = daddress.address_id LEFT JOIN 718shop_ziti_address ziti_address ON order_common.reciver_ziti_id = ziti_address.address_id LEFT JOIN 718shop_refund_return refund ON order1.order_id = refund.order_id AND (refund.goods_id=0 OR order_goods.goods_id = refund.goods_id) ";

        $preCondition = "WHERE NOT (order1.is_zorder=0 AND order1.order_state=20) ";

        $endCondition = "ORDER BY order1.order_id ASC LIMIT 20000";

        $sql = "SELECT " . $columns . "FROM " . $tables . $preCondition . $condition . $endCondition;

        $list = Model()->query($sql);

        return $list;
    }




    /**
     * 导出单个表格
     */
    private function exportSingleExcel($start_time_pay, $end_time_pay, $shipperCategory, $zitiAddress, $zitiName, $categoryName, $type)
    {
        $model_order = Model('order');
        // 处理数据
        // 支付开始时间
        $payStartTime = $start_time_pay ? strtotime($start_time_pay) : null;
        // 支付结束时间
        $payEndTime = $end_time_pay ? strtotime($end_time_pay) : null;
        //20点截单支付开始时间
        // 方法1
//        $payStartTime20 = $payStartTime? strtotime(date("Y-m-d",$payStartTime))+20*60*60 : null;
        // 方法2
        // $Y = date("Y",$payStartTime);
        // $m = date("m",$payStartTime);
        // $d = date("d",$payStartTime);
        // $payStartTime20 = $payStartTime? mktime(20,0,0,$m,$d,$Y) : null;
        //更改为当天6点
        $Y = date("Y",$payEndTime);
        $m = date("m",$payEndTime);
        $d = date("d",$payEndTime);
        $payStartTime20 = $payEndTime? mktime(6,0,0,$m,$d,$Y) : null;
        //团购支付开始时间，提前15天
        $payStartTimeYushou = $payStartTime? strtotime('-15 days',$payStartTime) : null;

        //11点截单1天1配类别对应的发货人
        $shippers11 = $this->getShippersByCategoryId(12);
        //20点截单类别对应的发货人
        $shippers20 = $this->getShippersByCategoryId(17);


        //查询11点截单 1天1配 11点-11点
        $condition1 = $this->createCondition($payStartTime, $payEndTime, $shippers11, $zitiAddress, $type, 0);
        $data1 = $model_order->getWarehouseOrderGoodsExportList($condition1);

        //查询11点截单 1天2配 20点-11点
        $condition2 = $this->createCondition($payStartTime20, $payEndTime, $shippers20, $zitiAddress, $type, 0);
        $data2 = $model_order->getWarehouseOrderGoodsExportList($condition2);

        // 团购
        $condition3 = $this->createCondition($payStartTimeYushou, $payEndTime, null, $zitiAddress, $type, 1);
        $data3 = $model_order->getWarehouseOrderGoodsExportList($condition3);

        // 合并数组
        $data = array_merge((array)$data1, (array)$data2, (array)$data3);

        // 按支付时间排序
        array_multisort(array_column($data, 'payment_time'), SORT_ASC, $data);

        // 生成excel数据
        $dataArray = $this->createExcelDataBySqlData($data);
        // 导出表格
        $this->exportWarehouseExcel($dataArray, $zitiName, $categoryName);
    }

    /**
     * 生成并导出单个表格
     */
    private function exportWarehouseExcel($data, $zitiName, $categoryName)
    {

        try {
            //code...
            $spreadsheet = new Spreadsheet();
            $workSheet = $spreadsheet->getActiveSheet();

            // 样式
            $this->defineWarehouseExcelStyles($workSheet, $data);

            //表头内容
            $workSheet->setCellValue('A1', date('m-d', time()).'，11点截单，自提点：'.$zitiName);

            // 表数据
            $workSheet->fromArray($data, NULL, 'A2');

            // 文件名
            $filename = date('m.d', time()).$zitiName.'待发货订单(11点截单)'.'.xlsx';
            // redirect output to client browser
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename='.$filename);
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');

            // $str .= '╔over：'.(memory_get_usage()/1024/1024)."MB\r\n";
            // $str .= '╚time(s)：'.time()."\r\n";
            // $str .= '脚本内存峰值：'.(memory_get_peak_usage()/1024/1024)."MB\r\n";
            // $str .= '物理峰值：'.(memory_get_peak_usage(true)/1024/1024)."MB\r\n";
            // echo "导出后".$str;
        } catch (Throwable $th) {
            //throw $th;
            echo "Captured Throwable: " . $th->getMessage() . PHP_EOL;
        }
    }


    
    
    /**
     * 删除BASE_ROOT_PATH/download/tmp/batchWarehouseExcelTmp/下所有文件
     */
    private function removeExcelTmp() {
        $path = BASE_ROOT_PATH . '/download/tmp/batchWarehouseExcelTmp/';
        $res = $this->removeDir($path,false);
    }

    /**
     * 注意：使用此方法请谨慎
     * 删除文件夹及文件夹下文件
     */
    private function removeDir($path, $delDir = true) {
        if (is_dir($path)) {
          $handle = opendir($path);
          if ($handle) {
            while (false !== ( $item = @readdir($handle) )) {
              if ($item != "." && $item != "..") {
                is_dir("$path/$item") ?  $this->removeDir("$path/$item", $delDir) : unlink("$path/$item");
              }
            }
            closedir($handle);
            if ($delDir) {
                return rmdir($path);
            }
          }
        }
        clearstatcache();
    }
}
