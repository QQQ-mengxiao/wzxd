<?php

/**
 * 商家中心订单导出
 *
 *
 *
 **/

require_once BASE_ROOT_PATH.'/shop/control/store_export_salesdata.php';

require_once BASE_ROOT_PATH . '/PHPExcel/Classes/PHPExcel.php'; //引入文件
require_once BASE_ROOT_PATH . '/PHPExcel/Classes/PHPExcel/IOFactory.php'; //引入文件
require_once BASE_ROOT_PATH . '/PHPExcel/Classes/PHPExcel/Reader/Excel2007.php'; //引入文件
require_once BASE_ROOT_PATH . '/PHPExcel/Classes/PHPExcel/Reader/IReader.php'; //引入文件
defined('In718Shop') or exit('Access Invalid!');

class store_export_member_zitiControl extends BaseSellerControl
{

    /**
     * 导出订单
     *
     */
    public function indexOp()
    {
        //当前登录账号
        $seller_id = $_SESSION['seller_id'];
        if($_SESSION['seller_name'] == 'shop02'){
            $address_sql="SELECT *  FROM `718shop_ziti_address` WHERE `store_id` = 4";
            $address_list =Model()->query($address_sql);

            $a =array();
            foreach ($address_list as $key => $value) {
                $a[] = $value['address_id'];
            }
            $seller_group['ziti_limits'] = implode(',', $a);
            
        }else{
            $seller = Model('seller')->table('seller')->where(array('seller_id'=>$seller_id))->find();
            $seller_group = Model('seller_group')->table('seller_group')->where(array('group_id'=>$seller['seller_group_id']))->find();
            //print_r($seller_group );
            //登录账号自提地址权限
            $ziti_limits = explode(',', $seller_group['ziti_limits']);
        }
        //显示自提地址列表(搜索)
        $condition2 = array();
        $model_daddress = Model('ziti_address');
        $address_sql="SELECT *  FROM `718shop_ziti_address` WHERE `address_id` IN  (".$seller_group['ziti_limits'].")";
        $address_list =Model()->query($address_sql);
        Tpl::output('address_list',$address_list);
        /*$model_daddress = Model('ziti_address');
        $address_list = $model_daddress->getAddressList(array());
        Tpl::output('address_list', $address_list);*/
        Tpl::showpage('store_export_member_ziti.excel');
    }

    public function exportOp()
    {
        $condition = array();
        //当前登录账号
        $seller_id = $_SESSION['seller_id'];
        if($_SESSION['seller_name'] == 'shop02'){
            $address_sql="SELECT *  FROM `718shop_ziti_address` WHERE `store_id` = 4";
            $address_list =Model()->query($address_sql);

            $a =array();
            foreach ($address_list as $key => $value) {
                $a[] = $value['address_id'];
            }
            $seller_group['ziti_limits'] = implode(',', $a);
            
        }else{
            $seller = Model('seller')->table('seller')->where(array('seller_id'=>$seller_id))->find();
            $seller_group = Model('seller_group')->table('seller_group')->where(array('group_id'=>$seller['seller_group_id']))->find();
        }
        $condition['member.ziti_id']  = array('in',$seller_group['ziti_limits']);

        //注册时间
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_start_date_add']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $_GET['query_end_date_add']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_date_add']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_date_add']) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['member.member_time'] = array('between', array($start_unixtime, $end_unixtime));
        }

        $member_list = Model()->table('member,ziti_address')->join('inner')->on('member.ziti_id=ziti_address.address_id')->where($condition)->field('ziti_address.seller_name,count(member.member_id) as member_count')->group('ziti_id')->select();
        //  echo '<pre>';print_r($member_list);die;
        $this->excel_export($member_list);
    }

    private function excel_export($data_tmp)
    {
        $excel = new PHPExcel();
        $letter = array('A', 'B');
        $tableheader = array('自提点', '人数');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
            $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
        }
        $member_data = [];
        foreach ($data_tmp as $item) {
            $member_data[] = [
                $item['seller_name'],
                $item['member_count']
            ];
        }

        //填充表格信息
        for ($i = 2; $i <= count($member_data) + 1; $i++) {
            $j = 0;
            foreach ($member_data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i", "$value", PHPExcel_Cell_DataType::TYPE_STRING);
                $j++;
            }
        }
        //创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        $filename = '自提点人数信息' . date('Y-m-d-H', time()) . '.xls';
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
     * 销售报表导出
     */
    public function export_zitiOp()
    {
        //支付时间
        $if_start_time = $_GET['query_start_date_pay'];
        $if_end_time = $_GET['query_end_date_pay'];
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_date_pay']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_date_pay']) : null;
        $stime = date('Ymd', $start_unixtime);
        $ltime = ceil(($end_unixtime - $start_unixtime) / 86400);//echo $ltime;die;//echo $start_unixtime;echo '---';echo $end_unixtime;die;

        //当前登录账号
        $seller_id = $_SESSION['seller_id'];
        if($_SESSION['seller_name'] == 'shop02'){
            $address_sql="SELECT *  FROM `718shop_ziti_address` WHERE `store_id` = 4";
            $address_list =Model()->query($address_sql);

            $a =array();
            foreach ($address_list as $key => $value) {
                $a[] = $value['address_id'];
            }
            $seller_group['ziti_limits'] = implode(',', $a);
            
        }else{
            $seller = Model('seller')->table('seller')->where(array('seller_id'=>$seller_id))->find();
            $seller_group = Model('seller_group')->table('seller_group')->where(array('group_id'=>$seller['seller_group_id']))->find();
        }
        //根据自提地址权限搜索数据
        $con = " AND order_common.reciver_ziti_id in (" . $seller_group['ziti_limits'] .")";
        //登录账号自提地址权限
        $ziti_limits = explode(',', $seller_group['ziti_limits']);
        $zi_condition['address_id']  = array('in',$seller_group['ziti_limits']);
        $zi_condition['state'] = array('gt',0);
        $address_list = Model('ziti_address')->table('ziti_address')->where($zi_condition)->select();
//        echo '<pre>';print_r($address_list);die;
        //$address_list = Model()->query("select * from 718shop_ziti_address ORDER BY FIELD(seller_name,'河南物资集团','陆港园区') desc");
        //echo '<pre>';print_r($address_list);die;
        //自提点ID
        if($_GET['address_id']){
            $condition = " AND order_common.reciver_ziti_id=" . $_GET['address_id'];
            $seller_name = Model()->table('ziti_address')->getfby_address_id($_GET['address_id'], 'seller_name') ;
            $a = $_GET['address_id'];
        }else{
            $condition = $con;
            $seller_name = "合计";
            $a = '';

        }
        /*$condition = $_GET['address_id'] ? "and oc.reciver_ziti_id=" . $_GET['address_id'] : "";
        $seller_name = $_GET['address_id'] ? Model()->table('ziti_address')->getfby_address_id($_GET['address_id'], 'seller_name') : "合计";*/
//        $sql = "SELECT a.* FROM (SELECT FROM_UNIXTIME(so.payment_time,'%Y-%m-%d') AS days,COUNT(DISTINCT CASE WHEN order_goods.deliverer_id !=90 THEN so.order_id ELSE NULL END) AS count,SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0)) AS amount,SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_cost_price*order_goods.goods_num,0)) AS cost,(SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0))-SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_cost_price*order_goods.goods_num,0))) AS profit,concat(format((SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0))-SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_cost_price*order_goods.goods_num,0)))/SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0))*100,2),'%') AS profit_rate,SUM(IF (order_goods.deliverer_id=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0)) AS sqamount,SUM(IF (order_goods.deliverer_id !=90,order_goods.voucher_price,0)) AS voucher,ziti.seller_name AS seller_name FROM 718shop_order so INNER JOIN 718shop_order_common order_common ON so.order_id=order_common.order_id LEFT JOIN 718shop_order_goods order_goods ON so.order_id=order_goods.order_id LEFT JOIN 718shop_voucher voucher ON order_common.voucher_id=voucher.voucher_id LEFT JOIN 718shop_voucher_template voucher_template ON voucher.voucher_t_id=voucher_template.voucher_t_id LEFT JOIN 718shop_ziti_address ziti ON order_common.reciver_ziti_id=ziti.address_id LEFT JOIN 718shop_refund_return refund ON order_goods.rec_id=refund.order_goods_id AND refund.seller_state=2 AND refund.refund_state=3 WHERE NOT (so.is_zorder=0 AND so.order_state=20) AND so.payment_time BETWEEN ".$start_unixtime." AND ".$end_unixtime.$condition." AND so.order_state IN (20,30,40) AND NOT (IFNULL(refund.seller_state,0)=2 AND IFNULL(refund.refund_state,0)=3 AND IFNULL(refund.refund_amount,0)=order_goods.goods_pay_price) GROUP BY seller_name,days ORDER BY ziti.address_id,days ASC) a ";
        $sql = "SELECT a.* FROM (SELECT a.*FROM (SELECT day_list.DAY AS days,a.count,a.amount,a.cost,a.profit,a.profit_rate,a.sqamount,a.voucher,a.seller_name FROM (SELECT FROM_UNIXTIME(so.payment_time,'%Y-%m-%d') AS days,COUNT(DISTINCT so.order_id) AS count,SUM(order_goods.goods_pay_price-IFNULL(refund.refund_amount,0)) AS amount,SUM(order_goods.goods_cost_price*order_goods.goods_num) AS cost,(SUM(order_goods.goods_pay_price-IFNULL(refund.refund_amount,0))-SUM(order_goods.goods_cost_price*order_goods.goods_num)) AS profit,concat(format((SUM(order_goods.goods_pay_price-IFNULL(refund.refund_amount,0))-SUM(order_goods.goods_cost_price*order_goods.goods_num))/SUM(order_goods.goods_pay_price-IFNULL(refund.refund_amount,0))*100,2),'%') AS profit_rate,SUM(IF (order_goods.deliverer_id=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0)) AS sqamount,SUM(order_goods.voucher_price) AS voucher,'".$seller_name."' AS seller_name FROM 718shop_order so INNER JOIN 718shop_order_common order_common ON so.order_id=order_common.order_id LEFT JOIN 718shop_order_goods order_goods ON so.order_id=order_goods.order_id LEFT JOIN 718shop_voucher voucher ON order_common.voucher_id=voucher.voucher_id LEFT JOIN 718shop_voucher_template voucher_template ON voucher.voucher_t_id=voucher_template.voucher_t_id LEFT JOIN 718shop_refund_return refund ON order_goods.rec_id=refund.order_goods_id AND refund.seller_state=2 AND refund.refund_state=3 WHERE NOT (so.is_zorder=0 AND so.order_state=20) AND so.payment_time BETWEEN ".$start_unixtime." AND ".$end_unixtime.$condition." AND so.order_state IN (20,25,30,40) AND NOT (IFNULL(refund.seller_state,0)=2 AND IFNULL(refund.refund_state,0)=3 AND IFNULL(refund.refund_amount,0)=order_goods.goods_pay_price) GROUP BY days) a RIGHT JOIN (SELECT @date :=DATE_ADD(@date,INTERVAL 1 DAY) DAY FROM (SELECT @date :=DATE_ADD(".$stime.",INTERVAL-1 DAY) FROM 718shop_order) days LIMIT ".$ltime.") day_list ON day_list.DAY=a.days) a ORDER BY a.days ASC) a";
        if (!$a) {
//            $sql = "SELECT a.* FROM (SELECT a.*FROM (SELECT day_list.DAY AS days,a.count,a.amount,a.cost,a.profit,a.profit_rate,a.sqamount,a.voucher,a.seller_name FROM (SELECT FROM_UNIXTIME(so.payment_time,'%Y-%m-%d') AS days,COUNT(DISTINCT CASE WHEN order_goods.deliverer_id !=90 THEN so.order_id ELSE NULL END) AS count,SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0)) AS amount,SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_cost_price*order_goods.goods_num,0)) AS cost,(SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0))-SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_cost_price*order_goods.goods_num,0))) AS profit,concat(format((SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0))-SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_cost_price*order_goods.goods_num,0)))/SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0))*100,2),'%') AS profit_rate,SUM(IF (order_goods.deliverer_id=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0)) AS sqamount,SUM(IF (order_goods.deliverer_id !=90,order_goods.voucher_price,0)) AS voucher,'".$seller_name."' AS seller_name FROM 718shop_order so INNER JOIN 718shop_order_common order_common ON so.order_id=order_common.order_id LEFT JOIN 718shop_order_goods order_goods ON so.order_id=order_goods.order_id LEFT JOIN 718shop_voucher voucher ON order_common.voucher_id=voucher.voucher_id LEFT JOIN 718shop_voucher_template voucher_template ON voucher.voucher_t_id=voucher_template.voucher_t_id LEFT JOIN 718shop_ziti_address ziti ON order_common.reciver_ziti_id=ziti.address_id LEFT JOIN 718shop_refund_return refund ON order_goods.rec_id=refund.order_goods_id AND refund.seller_state=2 AND refund.refund_state=3 WHERE NOT (so.is_zorder=0 AND so.order_state=20) AND so.payment_time BETWEEN ".$start_unixtime." AND ".$end_unixtime.$condition." AND so.order_state IN (20,30,40) AND NOT (IFNULL(refund.seller_state,0)=2 AND IFNULL(refund.refund_state,0)=3 AND IFNULL(refund.refund_amount,0)=order_goods.goods_pay_price) GROUP BY days) a RIGHT JOIN (SELECT @date :=DATE_ADD(@date,INTERVAL 1 DAY) DAY FROM (SELECT @date :=DATE_ADD(".$stime.",INTERVAL-1 DAY) FROM 718shop_order) days LIMIT ".$ltime.") day_list ON day_list.DAY=a.days) a ORDER BY a.days ASC) a";
            foreach ($address_list as $k => $v) {
//                $sql .= " UNION ALL SELECT a.* FROM (SELECT FROM_UNIXTIME(so.payment_time,'%Y-%m-%d') AS days,COUNT(DISTINCT CASE WHEN order_goods.deliverer_id !=90 THEN so.order_id ELSE NULL END) AS count,SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0)) AS amount,SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_cost_price*order_goods.goods_num,0)) AS cost,(SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0))-SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_cost_price*order_goods.goods_num,0))) AS profit,concat(format((SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0))-SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_cost_price*order_goods.goods_num,0)))/SUM(IF (order_goods.deliverer_id !=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0))*100,2),'%') AS profit_rate,SUM(IF (order_goods.deliverer_id=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0)) AS sqamount,SUM(IF (order_goods.deliverer_id !=90,order_goods.voucher_price,0)) AS voucher,ziti.seller_name AS seller_name FROM 718shop_order so INNER JOIN 718shop_order_common order_common ON so.order_id=order_common.order_id LEFT JOIN 718shop_order_goods order_goods ON so.order_id=order_goods.order_id LEFT JOIN 718shop_voucher voucher ON order_common.voucher_id=voucher.voucher_id LEFT JOIN 718shop_voucher_template voucher_template ON voucher.voucher_t_id=voucher_template.voucher_t_id LEFT JOIN 718shop_ziti_address ziti ON order_common.reciver_ziti_id=ziti.address_id LEFT JOIN 718shop_refund_return refund ON order_goods.rec_id=refund.order_goods_id AND refund.seller_state=2 AND refund.refund_state=3 WHERE NOT (so.is_zorder=0 AND so.order_state=20) AND so.payment_time BETWEEN ".$start_unixtime." AND ".$end_unixtime." AND order_common.reciver_ziti_id=" . $v['address_id'] ." AND so.order_state IN (20,30,40) AND NOT (IFNULL(refund.seller_state,0)=2 AND IFNULL(refund.refund_state,0)=3 AND IFNULL(refund.refund_amount,0)=order_goods.goods_pay_price) GROUP BY seller_name,days ORDER BY ziti.address_id,days ASC) a ";
                $sql .= " UNION ALL SELECT a.* FROM (SELECT a.*FROM (SELECT day_list.DAY AS days,a.count,a.amount,a.cost,a.profit,a.profit_rate,a.sqamount,a.voucher,'".$v['seller_name']."' AS seller_name FROM (SELECT FROM_UNIXTIME(so.payment_time,'%Y-%m-%d') AS days,COUNT(DISTINCT so.order_id) AS count,SUM(order_goods.goods_pay_price-IFNULL(refund.refund_amount,0)) AS amount,SUM(order_goods.goods_cost_price*order_goods.goods_num) AS cost,(SUM(order_goods.goods_pay_price-IFNULL(refund.refund_amount,0))-SUM(order_goods.goods_cost_price*order_goods.goods_num)) AS profit,concat(format((SUM(order_goods.goods_pay_price-IFNULL(refund.refund_amount,0))-SUM(order_goods.goods_cost_price*order_goods.goods_num))/SUM(order_goods.goods_pay_price-IFNULL(refund.refund_amount,0))*100,2),'%') AS profit_rate,SUM(IF (order_goods.deliverer_id=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0)) AS sqamount,SUM(order_goods.voucher_price) AS voucher,'".$v['seller_name']."' AS seller_name FROM 718shop_order so INNER JOIN 718shop_order_common order_common ON so.order_id=order_common.order_id LEFT JOIN 718shop_order_goods order_goods ON so.order_id=order_goods.order_id LEFT JOIN 718shop_voucher voucher ON order_common.voucher_id=voucher.voucher_id LEFT JOIN 718shop_voucher_template voucher_template ON voucher.voucher_t_id=voucher_template.voucher_t_id LEFT JOIN 718shop_refund_return refund ON order_goods.rec_id=refund.order_goods_id AND refund.seller_state=2 AND refund.refund_state=3 WHERE NOT (so.is_zorder=0 AND so.order_state=20) AND so.payment_time BETWEEN ".$start_unixtime." AND ".$end_unixtime." AND order_common.reciver_ziti_id=" . $v['address_id'] ." AND so.order_state IN (20,25,30,40) AND NOT (IFNULL(refund.seller_state,0)=2 AND IFNULL(refund.refund_state,0)=3 AND IFNULL(refund.refund_amount,0)=order_goods.goods_pay_price) GROUP BY days) a RIGHT JOIN (SELECT @date :=DATE_ADD(@date,INTERVAL 1 DAY) DAY FROM (SELECT @date :=DATE_ADD(".$stime.",INTERVAL-1 DAY) FROM 718shop_order) days LIMIT ".$ltime.") day_list ON day_list.DAY=a.days) a ORDER BY a.days ASC)a ";
            }
			$sql .= " UNION ALL SELECT a.*FROM (SELECT a.*FROM (SELECT day_list.DAY AS days,a.count,a.amount,a.cost,a.profit,a.profit_rate,a.sqamount,a.voucher,'邮寄订单' AS seller_name FROM (SELECT FROM_UNIXTIME(so.payment_time,'%Y-%m-%d') AS days,COUNT(so.order_id) AS count,SUM(order_goods.goods_pay_price-IFNULL(refund.refund_amount,0)) AS amount,SUM(order_goods.goods_cost_price*order_goods.goods_num) AS cost,(SUM(order_goods.goods_pay_price-IFNULL(refund.refund_amount,0))-SUM(order_goods.goods_cost_price*order_goods.goods_num)) AS profit,concat(format((SUM(order_goods.goods_pay_price-IFNULL(refund.refund_amount,0))-SUM(order_goods.goods_cost_price*order_goods.goods_num))/SUM(order_goods.goods_pay_price-IFNULL(refund.refund_amount,0))*100,2),'%') AS profit_rate,SUM(IF (order_goods.deliverer_id=90,order_goods.goods_pay_price-IFNULL(refund.refund_amount,0),0)) AS sqamount,SUM(order_goods.voucher_price) AS voucher,'邮寄订单' AS seller_name FROM 718shop_order so INNER JOIN 718shop_order_common order_common ON so.order_id=order_common.order_id LEFT JOIN 718shop_order_goods order_goods ON so.order_id=order_goods.order_id LEFT JOIN 718shop_voucher voucher ON order_common.voucher_id=voucher.voucher_id LEFT JOIN 718shop_voucher_template voucher_template ON voucher.voucher_t_id=voucher_template.voucher_t_id LEFT JOIN 718shop_refund_return refund ON order_goods.rec_id=refund.order_goods_id AND refund.seller_state=2 AND refund.refund_state=3 WHERE NOT (so.is_zorder=0 AND so.order_state=20) AND so.payment_time BETWEEN ".$start_unixtime." AND ".$end_unixtime." AND so.address_you_id> 0 AND so.order_state IN (20,25,30,40) AND NOT (IFNULL(refund.seller_state,0)=2 AND IFNULL(refund.refund_state,0)=3 AND IFNULL(refund.refund_amount,0)=order_goods.goods_pay_price) GROUP BY days) a RIGHT JOIN (SELECT @date :=DATE_ADD(@date,INTERVAL 1 DAY) DAY FROM (SELECT @date :=DATE_ADD(".$stime.",INTERVAL-1 DAY) FROM 718shop_order) days LIMIT ".$ltime.") day_list ON day_list.DAY=a.days) a ORDER BY a.days ASC)a ";
        }
        $saleList = Model()->query($sql);
    //    echo '<pre>';
    //    print_r($sql);die;
        if ($seller_name == '合计') {
            $this->excel_export_ziti_all($saleList, $ltime);
        } else { //七列
            $this->excel_export_ziti($saleList, $seller_name);
        }
        die;
    }

    private function excel_export_ziti($saleList, $seller_name)
    {
        $excel = new PHPExcel();
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN, //细边框
                ),
            ),
        );
        $styleArrayFont = array(
            'font'  => array(
                'color' => array('rgb' => 'FF0000')
            )
        );
        $excel->getActiveSheet()->getStyle('A1:H' . (4 + count($saleList)))->applyFromArray($styleArray);
        $excel->getActiveSheet()->getRowDimension('3')->setRowHeight(30);
        $excel->getActiveSheet()->getStyle('A2:H3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FDC995');
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(13);
        $excel->getActiveSheet()->setCellValue('A1', '线上物资小店销售日报表');
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->mergeCells('A1:H1');
        $excel->getActiveSheet()->setCellValue('A2', '日期');
        $excel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('A2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->mergeCells('A2:A3');
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(9);
        $excel->getActiveSheet()->setCellValue('B2', $seller_name);
        $excel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->mergeCells('B2:H2');
        $excel->getActiveSheet()->setCellValue('B3', '订单量');
        $excel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('B3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $excel->getActiveSheet()->setCellValue('C3', '销售额（元）');
        $excel->getActiveSheet()->getStyle('C3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('C3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $excel->getActiveSheet()->setCellValue('D3', '成本');
        $excel->getActiveSheet()->getStyle('D3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('D3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $excel->getActiveSheet()->setCellValue('E3', '利润');
        $excel->getActiveSheet()->getStyle('E3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('E3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $excel->getActiveSheet()->setCellValue('F3', '利润率');
        $excel->getActiveSheet()->getStyle('F3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('F3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $excel->getActiveSheet()->setCellValue('G3', '社区服务销售额（元）');
        $excel->getActiveSheet()->getStyle('G3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('G3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->getStyle('G3:G3')->getAlignment()->setWrapText(TRUE);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
        $excel->getActiveSheet()->setCellValue('H3', '优惠券备注');
        $excel->getActiveSheet()->getStyle('H3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('H3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $B = 0;
        $C = 0;
        $D = 0;
        $E = 0;
        $F = 0;
        $G = 0;
        $H = 0;
        foreach ($saleList as $key => $value) {
            $excel->getActiveSheet()->setCellValue('A' . ($key + 4), $value['days']);
            $excel->getActiveSheet()->setCellValue('A' . ($key + 5), '合计');
            $excel->getActiveSheet()->getStyle('A' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('A' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('A' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('A' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->setCellValue('B' . ($key + 4), $value['count']);
            $B += $value['count'];
            $excel->getActiveSheet()->setCellValue('B' . ($key + 5), $B);
            $excel->getActiveSheet()->getStyle('B' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('B' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('B' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('B' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->setCellValue('C' . ($key + 4), $value['amount']);
            $C += $value['amount'];
            $excel->getActiveSheet()->setCellValue('C' . ($key + 5), $C);
            $excel->getActiveSheet()->getStyle('C' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('C' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('C' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('C' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->setCellValue('D' . ($key + 4), $value['cost']);
            $D += $value['cost'];
            $excel->getActiveSheet()->setCellValue('D' . ($key + 5), $D);
            $excel->getActiveSheet()->getStyle('D' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('D' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('D' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('D' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->setCellValue('E' . ($key + 4), $value['profit']);
            $E += $value['profit'];
            $excel->getActiveSheet()->setCellValue('E' . ($key + 5), $E);
            $excel->getActiveSheet()->getStyle('E' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('E' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('E' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('E' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->setCellValue('F' . ($key + 4), $value['profit_rate']);
            $F += $value['profit_rate'];
            $excel->getActiveSheet()->getStyle('F' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('F' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('F' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('F' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->setCellValue('G' . ($key + 4), $value['sqamount']);
            $G += $value['sqamount'];
            $excel->getActiveSheet()->setCellValue('G' . ($key + 5), $G);
            $excel->getActiveSheet()->getStyle('G' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('G' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('G' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('G' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->setCellValue('H' . ($key + 4), $value['remark']);
            $H += $value['remark'];
            $excel->getActiveSheet()->setCellValue('H' . ($key + 5), $H);
            $excel->getActiveSheet()->getStyle('H' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('H' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('H' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('H' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            if ($key == count($saleList) - 1) {
                $excel->getActiveSheet()->getStyle('A' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->getStyle('B' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->getStyle('C' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->getStyle('D' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->getStyle('E' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->getStyle('F' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->getStyle('G' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->getStyle('H' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->setCellValue('F' . ($key + 5), $F . '%');
            }
        }

        $write = new PHPExcel_Writer_Excel5($excel);
        $filename = '销售报表-' . date('Y-m-d-H', time()) . '.xls';
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

    private function excel_export_ziti_all($saleList, $ltime)
    {
        $saleList = array_chunk($saleList, $ltime);
        $excel = new PHPExcel();
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN, //细边框
                ),
            ),
        );
        $styleArrayFont = array(
            'font'  => array(
                'color' => array('rgb' => 'FF0000')
            )
        );
        $excel->getActiveSheet()->getStyle('A1:' . $this->num2Letter(7 * count($saleList) + 1) . (4 + $ltime))->applyFromArray($styleArray);
        $excel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
        $excel->getActiveSheet()->getRowDimension('3')->setRowHeight(30);
        $excel->getActiveSheet()->getStyle('A2:H3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FDC995');
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(13);
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $excel->getActiveSheet()->setCellValue('A1', '线上物资小店销售日报表');
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->mergeCells('A1:H1');
        $excel->getActiveSheet()->mergeCells('I1:' . $this->num2Letter(64) . '1');
        $excel->getActiveSheet()->setCellValue('A2', '日期');
        $excel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('A2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->mergeCells('A2:A3');
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(9);
        $excel->getActiveSheet()->setCellValue('B2', '当天合计销售');
        $excel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->mergeCells('B2:H2');
        $excel->getActiveSheet()->setCellValue('B3', '订单量');
        $excel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('B3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $excel->getActiveSheet()->setCellValue('C3', '销售额（元）');
        $excel->getActiveSheet()->getStyle('C3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('C3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $excel->getActiveSheet()->setCellValue('D3', '成本');
        $excel->getActiveSheet()->getStyle('D3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('D3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $excel->getActiveSheet()->setCellValue('E3', '利润');
        $excel->getActiveSheet()->getStyle('E3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('E3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $excel->getActiveSheet()->setCellValue('F3', '利润率');
        $excel->getActiveSheet()->getStyle('F3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('F3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
        $excel->getActiveSheet()->setCellValue('G3', '社区服务销售额（元）');
        $excel->getActiveSheet()->getStyle('G3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('G3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->getStyle('G3:G3')->getAlignment()->setWrapText(TRUE);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
        $excel->getActiveSheet()->setCellValue('H3', '优惠券备注');
        $excel->getActiveSheet()->getStyle('H3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle('H3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $B = 0;
        $C = 0;
        $D = 0;
        $E = 0;
        $F = 0;
        $G = 0;
        $H = 0;
        foreach ($saleList[0] as $key => $value) {
            $excel->getActiveSheet()->setCellValue('A' . ($key + 4), $value['days']);
            $excel->getActiveSheet()->setCellValue('A' . ($key + 5), '合计');
            $excel->getActiveSheet()->getStyle('A' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('A' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('A' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('A' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->setCellValue('B' . ($key + 4), $value['count']);
            $B += $value['count'];
            $excel->getActiveSheet()->setCellValue('B' . ($key + 5), $B);
            $excel->getActiveSheet()->getStyle('B' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('B' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('B' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('B' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->setCellValue('C' . ($key + 4), $value['amount']);
            $C += $value['amount'];
            $excel->getActiveSheet()->setCellValue('C' . ($key + 5), $C);
            $excel->getActiveSheet()->getStyle('C' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('C' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('C' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('C' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->setCellValue('D' . ($key + 4), $value['cost']);
            $D += $value['cost'];
            $excel->getActiveSheet()->setCellValue('D' . ($key + 5), $D);
            $excel->getActiveSheet()->getStyle('D' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('D' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('D' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('D' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->setCellValue('E' . ($key + 4), $value['profit']);
            $E += $value['profit'];
            $excel->getActiveSheet()->setCellValue('E' . ($key + 5), $E);
            $excel->getActiveSheet()->getStyle('E' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('E' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('E' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('E' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->setCellValue('F' . ($key + 4), $value['profit_rate']);
            $F += $value['profit_rate'];
            $excel->getActiveSheet()->getStyle('F' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('F' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('F' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('F' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->setCellValue('G' . ($key + 4), $value['sqamount']);
            $G += $value['sqamount'];
            $excel->getActiveSheet()->setCellValue('G' . ($key + 5), $G);
            $excel->getActiveSheet()->getStyle('G' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('G' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('G' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('G' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->setCellValue('H' . ($key + 4), $value['remark']);
            $H += $value['remark'];
            $excel->getActiveSheet()->setCellValue('H' . ($key + 5), $H);
            $excel->getActiveSheet()->getStyle('H' . ($key + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('H' . ($key + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle('H' . ($key + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle('H' . ($key + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            if ($key == count($saleList[0]) - 1) {
                $excel->getActiveSheet()->getStyle('A' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->getStyle('B' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->getStyle('C' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->getStyle('D' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->getStyle('E' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->getStyle('F' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->getStyle('G' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->getStyle('H' . ($key + 5))->applyFromArray($styleArrayFont);
                $excel->getActiveSheet()->setCellValue('F' . ($key + 5), $F . '%');
            }
        }

        //非合计部分
        foreach ($saleList as $k => $v) {
            if ($k == 0) {
                continue;
            }
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 9) . '2:' . $this->num2Letter($k * 7 + 8) . '3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFDA');
            $excel->getActiveSheet()->getColumnDimension($this->num2Letter(($k - 1) * 7 + 9))->setWidth(9);
            $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 9) . '2', $v[0]['seller_name']);
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 9) . '2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->mergeCells($this->num2Letter(($k - 1) * 7 + 9) . '2:' . $this->num2Letter($k * 7 + 8) . '2');
            $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 9) . '3', '订单量');
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 9) . '3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 9) . '3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getColumnDimension($this->num2Letter(($k - 1) * 7 + 10))->setWidth(15);
            $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 10) . '3', '销售额（元）');
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 10) . '3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 10) . '3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getColumnDimension($this->num2Letter(($k - 1) * 7 + 11))->setWidth(10);
            $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 11) . '3', '成本');
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 11) . '3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 11) . '3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getColumnDimension($this->num2Letter(($k - 1) * 7 + 12))->setWidth(10);
            $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 12) . '3', '利润');
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 12) . '3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 12) . '3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getColumnDimension($this->num2Letter(($k - 1) * 7 + 13))->setWidth(10);
            $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 13) . '3', '利润率');
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 13) . '3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 13) . '3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getColumnDimension($this->num2Letter(($k - 1) * 7 + 14))->setWidth(12);
            $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 14) . '3', '社区服务销售额（元）');
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 14) . '3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 14) . '3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 14) . '3' . ':' . $this->num2Letter(($k - 1) * 7 + 14) . '3')->getAlignment()->setWrapText(TRUE);
            $excel->getActiveSheet()->getColumnDimension($this->num2Letter(($k - 1) * 7 + 15))->setWidth(12);
            $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 15) . '3', '优惠券备注');
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 15) . '3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 15) . '3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);


            $BB = 0;
            $CC = 0;
            $DD = 0;
            $EE = 0;
            $FF = 0;
            $GG = 0;
            $HH = 0;
            foreach ($v as $kk => $vv) {
                $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 9) . ($kk + 4), $vv['count']);
                $BB += $vv['count'];
                $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 9) . ($kk + 5), $BB);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 9) . ($kk + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 9) . ($kk + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 9) . ($kk + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 9) . ($kk + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 10) . ($kk + 4), $vv['amount']);
                $CC += $vv['amount'];
                $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 10) . ($kk + 5), $CC);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 10) . ($kk + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 10) . ($kk + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 10) . ($kk + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 10) . ($kk + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 11) . ($kk + 4), $vv['cost']);
                $DD += $vv['cost'];
                $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 11) . ($kk + 5), $DD);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 11) . ($kk + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 11) . ($kk + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 11) . ($kk + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 11) . ($kk + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 12) . ($kk + 4), $vv['profit']);
                $EE += $vv['profit'];
                $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 12) . ($kk + 5), $EE);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 12) . ($kk + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 12) . ($kk + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 12) . ($kk + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 12) . ($kk + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 13) . ($kk + 4), $vv['profit_rate']);
                $FF += $vv['profit_rate'];
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 13) . ($kk + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 13) . ($kk + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 13) . ($kk + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 13) . ($kk + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 14)  . ($kk + 4), $vv['sqamount']);
                $GG += $vv['sqamount'];
                $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 14)  . ($kk + 5), $GG);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 14) . ($kk + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 14)  . ($kk + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 14)  . ($kk + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 14)  . ($kk + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 15) . ($kk + 4), $vv['remark']);
                $HH += $vv['remark'];
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 15) . ($kk + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 15) . ($kk + 4))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 15) . ($kk + 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 15) . ($kk + 5))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                if ($kk == count($v) - 1) {
                    $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 9) . ($kk + 5))->applyFromArray($styleArrayFont);
                    $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 10) . ($kk + 5))->applyFromArray($styleArrayFont);
                    $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 11) . ($kk + 5))->applyFromArray($styleArrayFont);
                    $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 12) . ($kk + 5))->applyFromArray($styleArrayFont);
                    $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 13) . ($kk + 5))->applyFromArray($styleArrayFont);
                    $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 14) . ($kk + 5))->applyFromArray($styleArrayFont);
                    $excel->getActiveSheet()->getStyle($this->num2Letter(($k - 1) * 7 + 15) . ($kk + 5))->applyFromArray($styleArrayFont);
                    $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 13) . ($kk + 5), $FF . '%');
                    $excel->getActiveSheet()->setCellValue($this->num2Letter(($k - 1) * 7 + 15) . ($kk + 5), $HH);
                }
            }
        }

        $write = new PHPExcel_Writer_Excel5($excel);
        $filename = '销售报表-' . date('Y-m-d-H', time()) . '.xls';
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

    function num2Letter($num)
    {
        $num = intval($num);
        if ($num <= 0)
            return false;
        $letterArr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $letter = '';
        do {
            $key = ($num - 1) % 26;
            $letter = $letterArr[$key] . $letter;
            $num = floor(($num - $key) / 26);
        } while ($num > 0);
        return $letter;
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

    
    /**
     * 销售数据导出
     */
    public function exportSalesDataOp() {
        // 获取条件参数
        // 支付开始时间
        $start_time_pay = $_GET['query_start_date_pay2'];
        // 支付结束时间
        $end_time_pay = $_GET['query_end_date_pay2'];
        // 自提点
        $zitiAddress = $_GET['address_id'];
        
        try {
            $salesdata = new store_export_salesdata();
            $salesdata -> exportSalesDataOp($start_time_pay, $end_time_pay, $zitiAddress);
        } catch (Throwable $th) {
            //throw $th;
            echo "Captured Throwable: " . $th->getMessage() . PHP_EOL;
        }
    }
}
