<?php
/**
 * 商家中心订单导出
 *
 *
 *
 **/


defined('In718Shop') or exit('Access Invalid!');
class store_exportControl extends BaseSellerControl {
    const EXPORT_SIZE = 1000;
    public function __construct() {
        parent::__construct();
        Language::read('member_store_index');
    }


    /**
     * 导出订单
     *
     */
    public function export_orderOp_disable(){
        /*
        $lang   = Language::getLangContent();

        $model_order = Model('order');
        $condition  = array();
        if($_GET['order_sn']) {
            $condition['order_sn'] = $_GET['order_sn'];
        }
        if($_GET['store_name']) {
            $condition['store_name'] = $_GET['store_name'];
        }
        if(in_array($_GET['order_state'],array('0','10','20','30','40'))){
            $condition['order_state'] = $_GET['order_state'];
        }
        if($_GET['payment_code']) {
            $condition['payment_code'] = $_GET['payment_code'];
        }
        if($_GET['buyer_name']) {
            $condition['buyer_name'] = $_GET['buyer_name'];
        }
        if ($_GET['is_mode'] != '') {
            $condition['is_mode'] = $_GET['is_mode'];
        }
        $condition['store_id'] = $_SESSION['store_id'];

        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_date']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('time',array($start_unixtime,$end_unixtime));
        }

        if (!is_numeric($_GET['curpage'])){
            $count = $model_order->getOrderCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE ){   //显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=order&op=index');
                Tpl::showpage('export.excel');
            }else{  //如果数量小，直接下载
                $data = $model_order->getOrderList($condition,'','*','order_id desc',self::EXPORT_SIZE);
                $this->createExcel($data);
            }
        }else{  //下载
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_order->getOrderList($condition,'','*','order_id desc',"{$limit1},{$limit2}");
            $this->createExcel($data);
        }
        */
    }


    /**
     * 导出订单
     *
     */
    public function export_orderOp(){
        $lang   = Language::getLangContent();
        $model_order = Model('order');
        $condition  = array();
        if($_GET['order_sn']) {
            $condition['order.order_sn'] = $_GET['order_sn'];
        }
        if($_GET['store_name']) {
            $condition['order.store_name'] = $_GET['store_name'];
        }
        if(in_array($_GET['order_state'],array('0','10','20','30','40'))){
            $condition['order.order_state'] = $_GET['order_state'];
        }
        if($_GET['payment_code']) {
            $condition['order.payment_code'] = $_GET['payment_code'];
        }
        if($_GET['buyer_name']) {
            $condition['order.buyer_name'] = $_GET['buyer_name'];
        }

        if ($_GET['consignee_name'] != '') {
            $condition['order_common.reciver_name']=$_GET['consignee_name'];
        }
        //发货人姓名 新增
        if ($_GET['senderusername']!=''){
           $model_daddress = Model('daddress');
            $address_list = $model_daddress->getAddressInfo(array('seller_name'=>$_GET['senderusername']));
             
           $condition['order_common.daddress_id']= $address_list['address_id'];
        }

        // if ($_GET['senderusername']!=''){
        //     $sql="SELECT * from `718shop_order_goods` where kuajing_info like '%".$_GET['senderusername']."%'";
        //     $kuajing_info=Model()->query($sql);
        //     $order_id=array();
        //     for($i=0;$i<count($kuajing_info);$i++){
        //         $order_id[$i]=$kuajing_info[$i]['order_id'];
        //     }

        //     $condition['order.order_id']=array('in',$order_id);
        // }

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

        //退款状态
        //if ($_GET['refund_state'] != '') {
        //    $condition['order.refund_state'] = $_GET['refund_state'];
        //}

        //订单状态
        if ($_GET['order_state'] != '') {
            $condition['order.order_state'] = $_GET['order_state'];
        }

        //已关闭订单
        if ($_GET['skip_off'] == 1) {
            $condition['order.order_state'] = array('neq',0);
        }

        $condition['order.store_id'] = $_SESSION['store_id'];

        //下单时间
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_date']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['order.add_time'] = array('time',array($start_unixtime,$end_unixtime));
        }

        //发货时间
        $if_start_time_fahuo = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date_fahuo']);
        $if_end_time_fahuo = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date_fahuo']);
        $start_unixtime_fahuo = $if_start_time_fahuo ? strtotime($_GET['query_start_date_fahuo']) : null;
        $end_unixtime_fahuo = $if_end_time_fahuo ? strtotime($_GET['query_end_date_fahuo']): null;
        if ($start_unixtime_fahuo || $end_unixtime_fahuo) {
            $condition['order_common.shipping_time'] = array('time',array($start_unixtime_fahuo,$end_unixtime_fahuo));
        }

        //支付时间  xinzeng
        $if_start_time_pay = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date_pay']);
        $if_end_time_pay = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date_pay']);
        $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_date_pay']) : null;
        $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_date_pay']): null;
        if ($start_unixtime_pay || $end_unixtime_pay) {
            $condition['order.payment_time'] = array('time',array($start_unixtime_pay,$end_unixtime_pay));
        }

        //订单完成时间  xinzeng
        $if_start_time_finish = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date_finish']);
        $if_end_time_finish = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date_finish']);
        $start_unixtime_finish = $if_start_time_finish ? strtotime($_GET['query_start_date_finish']) : null;
        $end_unixtime_finish = $if_end_time_finish ? strtotime($_GET['query_end_date_finish']): null;
        if ($start_unixtime_finish || $end_unixtime_finish) {
            $condition['order.finnshed_time'] = array('time',array($start_unixtime_finish,$end_unixtime_finish));
        }


        if (!is_numeric($_GET['curpage'])){
            $count = $model_order->getOrderCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE ){   //显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=order&op=index');
                Tpl::showpage('export.excel');
            }else{  //如果数量小，直接下载
                $data = $model_order->getOrderList3($consignee_name,$condition,'','*','order_id desc',self::EXPORT_SIZE,array('order_goods','order_common','member','goods_kuajing_d'));
                $this->createExcel($data);
            }
        }else{  //下载
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_order->getOrderList3($consignee_name,$condition,'','*','order_id desc',"{$limit1},{$limit2}",array('order_goods','order_common','member','goods_kuajing_d'));
            $this->createExcel($data);
        }
    }


    /**
     * 导出子订单
     *
     */
    public function export_order_subOp(){
        $lang   = Language::getLangContent();

        $model_order = Model('order');
        $condition  = array();
        if($_GET['order_sn']) {
            $condition['order.order_sn'] = $_GET['order_sn'];
        }
        if($_GET['store_name']) {
            $condition['order.store_name'] = $_GET['store_name'];
        }
        if(in_array($_GET['order_state'],array('0','10','20','30','40'))){
            $condition['order.order_state'] = $_GET['order_state'];
        }
        if($_GET['payment_code']) {
            $condition['order.payment_code'] = $_GET['payment_code'];
        }
        if($_GET['buyer_name']) {
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

        //退款状态
        //if ($_GET['refund_state'] != '') {
        //    $condition['order.refund_state'] = $_GET['refund_state'];
        //}

        //订单状态
        if ($_GET['order_state'] != '') {
            $condition['order.order_state'] = $_GET['order_state'];
        }

        //已关闭订单
        if ($_GET['skipoff2'] == 1) {
            $condition['order.order_state'] = array('neq',0);
        }

        $condition['order.store_id'] = $_SESSION['store_id'];

        if ($_GET['goods_name'] != '') {
            $goods_name = $_GET['goods_name'];
        }
        if ($_GET['goods_serial'] != '') {
            $goods_serial = $_GET['goods_serial'];
        }
        if ($_GET['consignee_name'] != '') {
            $condition['order_common.reciver_name']=$_GET['consignee_name'];
        }


        //发货人姓名 新增
        if ($_GET['senderusername']!=''){
           $model_daddress = Model('daddress');
            $address_list = $model_daddress->getAddressInfo(array('seller_name'=>$_GET['senderusername']));
             
           $condition['order_common.daddress_id']= $address_list['address_id'];
        }

        // if($_GET['senderusername']!=''){
        //     $sql="SELECT * from `718shop_order_goods` where kuajing_info like '%".$_GET['senderusername']."%'";
        //     $kuajing_info=Model()->query($sql);
        //     $order_id=array();
        //     for($i=0;$i<count($kuajing_info);$i++){
        //         $order_id[$i]=$kuajing_info[$i]['order_id'];
        //     }

        //     $condition['order.order_id']=array('in',$order_id);
        // }


        //下单时间
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date2']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date2']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_date2']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_date2']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['order.add_time'] = array('time',array($start_unixtime,$end_unixtime));
        }

        //发货时间
        $if_start_time_fahuo = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date2_fahuo']);
        $if_end_time_fahuo = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date2_fahuo']);
        $start_unixtime_fahuo = $if_start_time_fahuo ? strtotime($_GET['query_start_date2_fahuo']) : null;
        $end_unixtime_fahuo = $if_end_time_fahuo ? strtotime($_GET['query_end_date2_fahuo']): null;
        if ($start_unixtime_fahuo || $end_unixtime_fahuo) {
            $condition['order_common.shipping_time'] = array('time',array($start_unixtime_fahuo,$end_unixtime_fahuo));
        }

        //支付时间  xinzeng
        //$if_start_time_pay = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date_pay2']);
        //$if_end_time_pay = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date_pay2']);

        $if_start_time_pay = $_GET['query_start_date_pay2'];
        $if_end_time_pay = $_GET['query_end_date_pay2'];
        $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_date_pay2']) : null;
        $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_date_pay2']): null;

        //$start_unixtime_pay = strtotime($_GET['query_start_date_pay2']);
        //$end_unixtime_pay = strtotime($_GET['query_end_date_pay2']);

        //订单完成时间  xinzeng
        $if_start_time_finish = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date_finish2']);
        $if_end_time_finish = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date_finish2']);
        $start_unixtime_finish = $if_start_time_finish ? strtotime($_GET['query_start_date_finish2']) : null;
        $end_unixtime_finish = $if_end_time_finish ? strtotime($_GET['query_end_date_finish2']): null;
        if ($start_unixtime_finish || $end_unixtime_finish) {
            $condition['order.finnshed_time'] = array('time',array($start_unixtime_finish,$end_unixtime_finish));
        }

        if ($start_unixtime_pay || $end_unixtime_pay) {
            $condition['order.payment_time'] = array('between',array($start_unixtime_pay,$end_unixtime_pay));
            //$condition['order.payment_time'] = array('time',array($start_unixtime_pay,$end_unixtime_pay));
        }
        if (!is_numeric($_GET['curpage'])){
            $count = $model_order->getOrderCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE ){   //显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=order&op=index');
                Tpl::showpage('export.excel');
            }else{  //如果数量小，直接下载
                $data = $model_order->getOrderList3($consignee_name,$condition,'','*','order_id desc',self::EXPORT_SIZE,array('order_goods','order_common','member','goods_kuajing_d'));

                $this->createExcel_sub($data,$goods_name,$goods_serial);
            }
        }else{  //下载
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_order->getOrderList3($consignee_name,$condition,'','*','order_id desc',"{$limit1},{$limit2}",array('order_goods','order_common','member','goods_kuajing_d'));

            $this->createExcel_sub($data,$goods_name,$goods_serial);
        }
    }

    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel_disable($data = array()){
        /*
        Language::read('export');
        import('libraries.excel');
        $excel_obj = new Excel();
        $excel_data = array();
        $model_common = Model('order_common');
        //设置样式
        $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
        //header
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'店铺');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'买家');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'买家ID');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'买家Email');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'下单时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单总额');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'运费');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'支付方式');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单状态');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'店铺ID');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'收货人姓名');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'发货时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'支付方式');
        //$excel_data[0][] = array('styleid'=>'s_title','data'=>'支付单号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品模式');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'交易流水号');

        //data
        foreach ((array)$data as $k=>$v){
            $tmp = array();
            $tmp[] = array('data'=>$v['order_sn']);
            $tmp[] = array('data'=>$v['store_name']);
            $tmp[] = array('data'=>$v['buyer_name']);
            $tmp[] = array('data'=>$v['buyer_id']);
            $tmp[] = array('data'=>$v['buyer_email']);
            $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['add_time']));
            $tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['order_amount']));
            $tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['shipping_fee']));
            $tmp[] = array('data'=>orderPaymentName($v['payment_code']));
            $tmp[] = array('data'=>orderState($v));
            $tmp[] = array('data'=>$v['store_id']);
            $tmp[] = array('data'=>$model_common->getfby_order_id($v['order_id'],'reciver_name'));
            //发货时间
            $shipping_time = $model_common->getfby_order_id($v['order_id'],'shipping_time');
            if($shipping_time != 0) {
                $tmp[] = array('data'=>date('Y-m-d H:i:s',$shipping_time));
            } else {
                $tmp[] = array('data'=>'');
            }

            $tmp[] = array('data'=>$v['payment_code']);
            //$tmp[] = array('data'=>$v['pay_sn']);
            //商品模式
            if($v['is_mode']==0){
                $mode = '一般贸易';
            }else if($v['is_mode']==2){
                $mode = '集货模式';
            }else if($v['is_mode']==1){
                $mode = '备货模式';
            }
            $tmp[] = array('data'=>$mode);

            //交易流水号
            $model_order_log = Model('order_log');
            $logdata = $model_order_log->where(array('order_id'=>$v['order_id'],'log_msg'=>array('like','%支付平台交易号%')))->select();
            $tradeNo = explode(' ',$logdata[0]['log_msg']);
            $tmp[] = array('data'=>$tradeNo[4]);

            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset('订单',CHARSET));
        $excel_obj->generateXML($excel_obj->charset('订单',CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
        */
    }


    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array()){
        // print_r($data);
        // break;
        Language::read('export');
        import('libraries.excel');
        $excel_obj = new Excel();
        $excel_data = array();
        $model_common = Model('order_common');
        //设置样式
        $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
        //header
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'支付时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品名称');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品数量');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品单价');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品总价');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单总税金');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'运费');
//        $excel_data[0][] = array('styleid'=>'s_title','data'=>'预存款支付金额');
//        $excel_data[0][] = array('styleid'=>'s_title','data'=>'充值卡支付金额');
//        $excel_data[0][] = array('styleid'=>'s_title','data'=>'优惠券优惠');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'实际支付金额');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'支付方式');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'买家留言');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'卖家备注');
//        $excel_data[0][] = array('styleid'=>'s_title','data'=>'发票');//xinzeng
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'退款金额');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'交易流水号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'发货人姓名');//新增
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'收货人姓名');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'收货人电话');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'收货人地址');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'取货时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'身份证号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'子订单号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'店铺');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'买家');
//        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单来源');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'下单时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品货号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'发货时间');
//        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品模式');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单状态');
        $excel_data[0][] = array('styleid' => 's_title', 'data' => '备注');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'运单号');//运单号
//        $excel_data[0][] = array('styleid'=>'s_title','data'=>'代金券');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单类型');
//echo '<pre>';var_dump($data);die;
        //data
        foreach ((array)$data as $k=>$v){
            $tmp = array();

            //订单号
            $tmp[] = array('data'=>$v['order_sn']);

            //支付时间
            if($v['payment_time'] != 0) {
                $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['payment_time']));//xinzeng
            } else {
                $tmp[] = array('data'=>'');
            }

            //商品名称
            $goodsname = str_replace(" ","",$v['extend_order_goods']['0']['goods_name']);
            $tmp[] = array('data'=>$goodsname);

            //商品数量
            $goodsnum = str_replace(" ","",$v['extend_order_goods']['0']['goods_num']);
            $tmp[] = array('data'=>$goodsnum);

            //商品单价
            $goodsprice = str_replace(" ","",$v['extend_order_goods']['0']['goods_price']);
            $tmp[] = array('data'=>$goodsprice);

            //商品总价
            $tmp[] = array('data'=>number_format($goodsprice*$goodsnum,2));

            //订单总税金
            $tmp[] = array('data'=>$v['store_tax_total']);

            //运费
            $tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['shipping_fee']));

            //预存款
//            $tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['pd_amount']));

            // 充值卡
//            $tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['rcb_amount']));

            //优惠券
            $voucher_tmp_num = $model_common->getfby_order_id($v['order_id'],'voucher_price');
            if ($voucher_tmp_num != ''){$voucher_tmp = '-'.number_format($voucher_tmp_num,2);}
            else {$voucher_tmp = '0.00';}
//            $tmp[] = array('data'=>$voucher_tmp);

            //实际支付金额
            $tmp[] = array('data'=>$v['order_amount']);

            //支付方式
            $tmp[] = array('data'=>orderPaymentName($v['payment_code']));

            //买家留言
            $order_message = $model_common->getfby_order_id($v['order_id'],'order_message');
            if($order_message != '') {
                $tmp[] = array('data'=>$order_message);
            } else {
                $tmp[] = array('data'=>'');
            }
            //卖家备注
            $order_message = $model_common->getfby_order_id($v['order_id'],'deliver_explain');
            if($order_message != '') {
                $tmp[] = array('data'=>$order_message);
            } else {
                $tmp[] = array('data'=>'');
            }

            //发票
            $tneirong = str_replace(" ","",$v['extend_order_common']['invoice_info']['内容']);
            $ttaitou = str_replace(" ","",$v['extend_order_common']['invoice_info']['抬头']);
            $tleixing = str_replace(" ","",$v['extend_order_common']['invoice_info']['类型']);
//            $tmp[] = array('data'=>"类型：$tleixing 抬头：$ttaitou 内容：$tneirong");

            //退款金额
            $tmp[] = array('data'=>$v['refund_amount']);

            //交易流水号
            $model_order_log = Model('order_log');
            $logdata = $model_order_log->where(array('order_id'=>$v['order_id'],'log_msg'=>array('like','%支付平台交易号%')))->select();
            $tradeNo = explode(' ',$logdata[0]['log_msg']);
            $tmp[] = array('data'=>$tradeNo[4]);

            //发货人姓名 新增

            // $senderusername = str_replace(" ","",$v['extend_order_goods']['kuajing_info']['senderusername']);
            $daddress_id=$v['extend_order_common']['daddress_id'];
            $model_daddress = Model('daddress');
            $address_list = $model_daddress->getAddressInfo(array('address_id'=>$daddress_id));
            $tmp[] = array('data'=>$address_list['seller_name']);
            // $tmp[] = array('data'=>$senderusername);



            //收款人姓名
            $reciver_name_tmp = $model_common->getfby_order_id($v['order_id'],'reciver_name');
            $tmp[] = array('data'=>$reciver_name_tmp);

            //收款人电话
            $phone = str_replace(" ","",$v['extend_order_common']['reciver_info']['phone']);
            $tmp[] = array('data'=>$phone);

            //收款人地址
            $address = str_replace(" ","",$v['extend_order_common']['reciver_info']['address']);
            $tmp[] = array('data'=>$address);

            //取货时间
            if($v['order_type']==1){
                $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['ziti_ladder_time']));
            }else{
                $tmp[] = array('data'=>'');
            }

            //身份证号
            $id_card = str_replace(" ","",$v['extend_order_common']['reciver_info']['id_card']);
            $tmp[] = array('data'=>$id_card);

            //子订单号
            $goodsid = str_replace(" ","",$v['extend_order_goods'][0]['goods_id']);
            $ordersn = $v['order_sn'];
            $sordersn = $ordersn.$goodsid;
            $tmp[]=array('data'=>$sordersn);

            //店铺
            $tmp[] = array('data'=>$v['store_name']);

            //买家
            $tmp[] = array('data'=>$v['buyer_name']);

            //订单来源
//            switch ($v['order_from'])
//            {
//                case 1:
//                    $tmp[] = array('data'=>'PC');
//                    break;
//                case 2:
//                    $tmp[] = array('data'=>'WAP');
//                    break;
//                case 3:
//                    $tmp[] = array('data'=>'ANDROID');
//                    break;
//                case 4:
//                    $tmp[] = array('data'=>'IOS');
//                    break;
//                default:
//                    $tmp[] = array('data'=>'');
//            }

            //下单时间
            $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['add_time']));

            //商品货号
            $serial = Model()->query("SELECT goods_serial FROM `718shop_goods` where goods_id=\"$goodsid\" LIMIT 10");
            $tmp[] = array('data'=>$serial[0]['goods_serial']);

            //发货时间
            $shipping_time = $model_common->getfby_order_id($v['order_id'],'shipping_time');
            if($shipping_time != 0) {
                $tmp[] = array('data'=>date('Y-m-d H:i:s',$shipping_time));
            } else {
                $tmp[] = array('data'=>'');
            }

            //商品模式
            if($v['is_mode']==0){
                $mode = '一般贸易';
            }else if($v['is_mode']==2){
                $mode = '集货模式';
            }else if($v['is_mode']==1){
                $mode = '备货模式';
            }
//            $tmp[] = array('data'=>$mode);

            //订单状态
            if($v['refund_state']=='1'){
                $tmp[] = array('data'=>'部分退款');
            }
            else if($v['refund_state']=='2'){
                $tmp[] = array('data'=>'已关闭');
            }else{
                $tmp[] = array('data'=>orderState($v));
            }



            //备注
            $order_id = $v['order_id'];
            $a = array('order_id' => $order_id);
            $model_refund_return = Model('refund_return');
            $result = $model_refund_return->getRefundReturnList($a);//
            if($result) {
                if ($result[0]['refund_type'] == 1) {
                    if ($result[0]['refund_state'] == 1||$result[0]['refund_state'] ==2) {
                        $tmp[] = array('data' => '退款中'); //退款中

                    } else if ($result[0]['refund_state'] == 3) {
                        if ($result[0]['seller_state']==2) {
                            $tmp[] = array('data' => '退款完成'); //退款完成
                        }else if ($result[0]['seller_state']==3) {
                            $tmp[] = array('data' => '退款失败'); //退款失败
                        }

                    } else {
                        $tmp[] = array('data' => '');
                    }
                } elseif ($result[0]['refund_type'] == 2) {
                    if ($result[0]['refund_state'] == 1||$result[0]['refund_state'] ==2) {
                        $tmp[] = array('data' => '退款退货中');//退款退货中

                    } else if ($result[0]['refund_state'] ==3) {
                        $tmp[] = array('data' => '退款退货完成'); //退款退货完成

                    } else {
                        $tmp[] = array('data' => '');
                    }
                }
            }else{
                $tmp[] = array('data' => '');

            }

            //运单号
            $waybill_info = unserialize($v['extend_order_common']['waybill_info']);
            if($v['is_mode']==0){
                $tmp[] = array('data'=>$v['shipping_code']);
            }else{
                if($waybill_info){
                    $tmp[] = array('data'=>$waybill_info['logisticsNo']);
                }else{
                    $tmp[] = array('data'=>'');
                }
            }
            //代金券名称
//            $voucher_code= Model()->query("SELECT voucher_code  FROM `718shop_order_common` where order_id=\"$order_id\" ");
//            // var_dump( $voucher_code);die;
//            if (!empty($voucher_code[0]['voucher_code'])) {
//            $voucher=unserialize($voucher_code[0]['voucher_code']);
//            foreach ($voucher as $key1 => $value1) {
//
//                if (!empty($value1['voucher_code'])) {
//                    $p=$value1['voucher_code'];
//
//                 $voucher_name=Model()->query("SELECT voucher_title FROM 718shop_voucher where voucher_code=\"$p\" ");
//                   $tmp[]=array('data'=>$voucher_name[0]['voucher_title']);
//
//            } else {
//                    $tmp[]=array('data'=>'');
//                }
//          }
//             } else {
//                $tmp[]=array('data'=>'');
//            }
            if($v['order_type']==0){
                $order_type = '无活动';
            }else if($v['order_type']==1){
                $order_type = '阶梯价';
            }else if($v['order_type']==2){
                $order_type = '团购';
            }else if($v['order_type']==3){
                $order_type = '新人专享';
            }else if($v['order_type']==4){
                $order_type = '限时秒杀';
            }else if($v['order_type']==5){
                $order_type = '即买即送';
            }
            $tmp[] = array('data'=>$order_type);

            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset('订单',CHARSET));
        $excel_obj->generateXML($excel_obj->charset('订单',CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
    }


    /**
     * 生成excel子订单
     *
     * @param array $data
     */
    private function createExcel_sub($data = array(),$goods_name,$goods_serial){
        Language::read('export');
        import('libraries.excel');
        $excel_obj = new Excel();
        $excel_data = array();
        $model_common = Model('order_common');
        $model_order_goods = Model('order_goods');
        $model_goods = Model('goods');
        //设置样式
        $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
        //header
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品名称');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'规格型号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品净重');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品分类');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品数量');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'发货人姓名');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'收货人姓名');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'收货人地址');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'收货地址');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'收货人电话');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'子订单号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'店铺');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'买家');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单来源');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'下单时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'支付时间');//xinzeng
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'完成时间');//xinzeng
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品货号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品单价');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品成本');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'单价税金');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品总价');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'总税金');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'运费');
        //预存款支付金额
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'预存款支付金额');
        //充值卡支付金额
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'充值卡支付金额');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'优惠券优惠');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'实际支付金额');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'支付方式');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'发货人姓名');//新增
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'身份证号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'发货时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'买家留言');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'发货备注');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品模式');
        //交易流水号
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'交易流水号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单状态');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'退款金额');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'备注');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'运单号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'促销信息');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'代金券');

        //$model_goods = Model('goods');
        if ($goods_serial != '') {
            $goods_id_arr = Model()->query("SELECT goods_id FROM `718shop_goods` where goods_serial=\"$goods_serial\" LIMIT 10");
            $goods_id = $goods_id_arr[0]['goods_id'];
        }
        //$goods_id = $model_goods->getfby_goods_serial($goods_serial,'goods_id');
        // print_r($goods_id);
        //break;



        //data
        foreach ((array)$data as $k=>$v){
            // $condition  = array();
            // $condition['order_id'] = $v['order_id'];
            // $condition['goods_name'] = array('like','%'.$goods_name.'%');

            //特别修正
            // if ($goods_serial != '') {
            //     $condition['goods_id'] = $goods_id;
            // }
            $cond=array();
            $cond['order_sn']=$v['order_sn'];
            $goodsid=Model('refund_return')->table('refund_return')->where($cond)->select();
            // $sub_data = $model_order_goods->where($condition)->order('goods_id asc')->select();//获取子订单
            //$sub_data = $model_order_goods->where(array('order_id'=>$v['order_id'],'goods_name'=>array('like','%'.$goods_name.'%'),'goods_id'=>$goods_id))->select();
            //print_r($sub_data);
            //break;
                         foreach ($v['extend_order_goods'] as $goodsall) {
                $p[]=$goodsall['goods_num']*$goodsall['goods_price'];
            }
            $all_price=array_sum($p);
            $count=count($v['extend_order_goods'])-1;
            foreach ($v['extend_order_goods'] as $keya =>$order) {
                    $voucher_price=$v['extend_order_common']['voucher_price'];
                    $goods_price=$order['goods_num']*$order['goods_price'];
                    $rate=sprintf("%.3f",$goods_price/$all_price);
                if($keya==$count){
                     if($keya==0){
                        $v['extend_order_goods'][$keya]['voucher_price']= $voucher_price;
                     }else{
                       $v['extend_order_goods'][$keya]['voucher_price']= $voucher_price-$all_voucher_price;
                     }
                 }else{
                     $v['extend_order_goods'][$keya]['voucher_price']= $voucher_price*$rate;
                 }
                 $all_voucher_price+= $v['extend_order_goods'][$keya]['voucher_price'];
            }
            $ii = 0;
           foreach ((array)$v['extend_order_goods'] as $k1=>$v1){
                $ii++;
                $tmp = array();
                if($ii==1){
                    $tmp[] = array('data'=>$v['order_sn']);
                }
                else {
                    $tmp[] = array('data'=>'');
                }
                //$tmp[] = array('data'=>$v1['goods_name']);
                //商品长度过长，临时修正
                $goid = $v1['goods_id'];
                $goods_name_tmp = Model()->query("SELECT goods_name FROM `718shop_goods` where goods_id=\"$goid\" LIMIT 10");
                // $tmp[] = array('data'=>$goods_name_tmp[0]['goods_name']);
                $tmp[] = array('data'=>$v1['goods_name']);
                //规格型号
                $kuajing_id1 = Model()->query("SELECT goods_kuajingD_id FROM `718shop_goods` where goods_id=\"$goid\" LIMIT 10");
                $kuajing_id = $kuajing_id1[0]['goods_kuajingD_id'];
                $kujing_guige1= Model()->query("SELECT specification FROM `718shop_goods_kuajing_d` where id=\"$kuajing_id\" LIMIT 10");
                $kujing_guige = $kujing_guige1[0]['specification'];
                $tmp[] = array('data'=>$kujing_guige);
                //商品净重
                 $goods_weight = Model()->query("SELECT goods_weight FROM `718shop_goods` where goods_id=\"$goid\" LIMIT 10");
                $tmp[] = array('data'=>$goods_weight[0]['goods_weight']);
                //商品分类
                $gcid=$v1['gc_id'];
                $gc_name= Model()->query("SELECT gc_name FROM `718shop_goods_class` where gc_id=\"$gcid\" LIMIT 10");
                $tmp[]=array('data'=>$gc_name[0]['gc_name']);
                //商品数量
                $tmp[] = array('data'=>$v1['goods_num']);
                //发货人姓名
                $daddress_id=$v['extend_order_common']['daddress_id'];
                $model_daddress = Model('daddress');
                $address_list = $model_daddress->getAddressInfo(array('address_id'=>$daddress_id));
                $tmp[] = array('data'=>$address_list['seller_name']);
                //收货人姓名
                $reciver_name_tmp = $model_common->getfby_order_id($v['order_id'],'reciver_name');
                $tmp[] = array('data'=>$reciver_name_tmp);

                //地址和电话
                // $address = $v['extend_order_common']['reciver_info']['address'];
                $address = $v['extend_order_common']['reciver_info']['area'];
                $street  = $v['extend_order_common']['reciver_info']['street'];
                // print_r($address);die;
                //str_replace(" ","1",$v['extend_order_common']['reciver_info']['address']);
                $return = preg_replace('#\s+#', ' ',trim($address));
                $arr_str=explode(" ",$return);
                // var_dump($arr_str);die;
                if(!empty($arr_str)){
                    //收货人所在省
                    $tmp[] = array('data'=>$arr_str[0].'省');
                    //收货人所在市
                    $tmp[] = array('data'=>$arr_str[1]);
                    //收货人所在区
                    $tmp[] = array('data'=>$arr_str[2]);
                    $tmp[] = array('data'=>$street);
                }else{
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                }


                //电话
                $tmp[] = array('data'=>$v['extend_order_common']['reciver_info']['phone']);

                $tmp[] = array('data'=>$v['order_sn'].$v1['goods_id']);

                if($ii==1){
                    $tmp[] = array('data'=>$v['store_name']);
                    $tmp[] = array('data'=>$v['buyer_name']);
                    //订单来源
                    switch ($v['order_from'])
                    {
                        case 1:
                            $tmp[] = array('data'=>'PC');
                            break;
                        case 2:
                            $tmp[] = array('data'=>'WAP');
                            break;
                        case 3:
                            $tmp[] = array('data'=>'ANDROID');
                            break;
                        case 4:
                            $tmp[] = array('data'=>'IOS');
                            break;
                        default:
                            $tmp[] = array('data'=>'');
                    }

                    //下单时间
                    $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['add_time']));
                    //支付时间
                    if($v['payment_time'] != 0) {
                        $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['payment_time']));//xinzeng
                    } else {
                        $tmp[] = array('data'=>'');
                    }

                    //完成时间
                    if($v['finnshed_time'] != 0) {
                        $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['finnshed_time']));//xinzeng
                    } else {
                        $tmp[] = array('data'=>'');
                    }

                } else {
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                }
                //商品货号
                $goid = $v1['goods_id'];
                $serial = Model()->query("SELECT goods_serial FROM `718shop_goods` where goods_id=\"$goid\" LIMIT 10");
                $tmp[] = array('data'=>$serial[0]['goods_serial']);

                //商品单价
                $tmp[] = array('data'=>$v1['goods_price']);
                //成本价
                $goods_common_id = Model()->table('goods')->getfby_goods_id($v1['goods_id'],'goods_commonid');
                $goods_costprice = Model()->table('goods_common')->getfby_goods_commonid($goods_common_id,'goods_costprice');
                $tmp[] = array('data'=>$goods_costprice);
                //单价税金
                $tax_rate=unserialize($v1['kuajing_info']);
                $tax_rate1=$tax_rate['goods_tax_rate'];
                $tmp[]=array('data'=>round($tax_rate1*$v1['goods_price'],2));
                //商品总价
                $tmp[] = array('data'=>number_format($v1['goods_price']*$v1['goods_num'],2));
                //总税金
                $tmp[]=array('data'=>round($tax_rate1*$v1['goods_price']*$v1['goods_num'],2));

                if($ii==1){
                    //运费
                    $tmp[] = array('data'=>$v['shipping_fee']);

                    //充值卡支付金额
                    $tmp[] = array('data'=>$v['pd_amount']);
                    //充值卡支付金额
                    $tmp[] = array('data'=>$v['rcb_amount']);
                    //优惠券
                                   //优惠券
             $tmp[] = array('data'=>$v1['voucher_price']);

                    //实际支付金额
                    $tmp[] = array('data'=>$v['order_amount']);
                    //支付方式
                    $tmp[] = array('data'=>orderPaymentName($v['payment_code']));

                    //$tmp[] = array('data'=>);
                    //$tmp[] = array('data'=>);
                    // $tmp[] = array('data'=>);

                    //发货人姓名

                    $senderusername = str_replace(" "," ",$v['extend_order_goods']['kuajing_info']['senderusername']);
                    $tmp[] = array('data'=>$senderusername);
                    // var_dump($senderusername);

                    //$address = $v['extend_order_common']['reciver_info']['address'];
//            $tmp[] = array('data'=>$address);
                    //身份证号
                    $id_card = str_replace(" ","1",$v['extend_order_common']['reciver_info']['id_card']);
                    //$address = $v['extend_order_common']['reciver_info']['address'];
                    $tmp[] = array('data'=>$id_card);

                    $shipping_time = $model_common->getfby_order_id($v['order_id'],'shipping_time');
                    if($shipping_time != 0) {
                        $tmp[] = array('data'=>date('Y-m-d H:i:s',$shipping_time));
                    } else {
                        $tmp[] = array('data'=>'');
                    }
                    //订单留言
                    $order_message = $model_common->getfby_order_id($v['order_id'],'order_message');
                    if($order_message != '') {
                        $tmp[] = array('data'=>$order_message);
                    } else {
                        $tmp[] = array('data'=>'');
                    }
                    //发货留言
                    $deliver_explain = $model_common->getfby_order_id($v['order_id'],'deliver_explain');
                    if($deliver_explain != '') {
                        $tmp[] = array('data'=>$deliver_explain);
                    } else {
                        $tmp[] = array('data'=>'');
                    }

                    //商品模式
                    if($v['is_mode']==0){
                        $mode = '一般贸易';
                    }else if($v['is_mode']==2){
                        $mode = '集货模式';
                    }else if($v['is_mode']==1){
                        $mode = '备货模式';
                    }
                    $tmp[] = array('data'=>$mode);
                } else {
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                //优惠券
             $tmp[] = array('data'=>$v1['voucher_price']);
                    $tmp[] = array('data'=>'');
                                       $tmp[] = array('data'=>orderPaymentName($v['payment_code']));
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                    // $tmp[] = array('data'=>'');
                    // $tmp[] = array('data'=>'');
                    // $tmp[] = array('data'=>'');
                    // $tmp[] = array('data'=>'');
                    // $tmp[] = array('data'=>'');
                    // $tmp[] = array('data'=>'');
                    // $tmp[] = array('data'=>'');

                }
                if($ii==1) {
                    //交易流水号
                    $model_order_log = Model('order_log');
                    $logdata = $model_order_log->where(array('order_id' => $v['order_id'], 'log_msg' => array('like', '%支付平台交易号%')))->select();
                    $tradeNo = explode(' ', $logdata[0]['log_msg']);
                    $tmp[] = array('data' => $tradeNo[4]);
                }else{
                    $tmp[] = array('data'=>'');
                    $tmp[] = array('data'=>'');
                }
                    //部分退款与全部退款
                    if($v['refund_state']=='1'){
                        $tmp[] = array('data'=>'部分退款');
                        foreach ($goodsid as $key =>$vv)
                        {
                            if($v1['goods_id']==$vv['goods_id'])
                            {
                                $tmp[] = array('data'=>$vv['refund_amount']);
                            }
                        }
                    }
                    else if($v['refund_state']=='2'){
                        $tmp[] = array('data'=>'已关闭');
                        $tmp[] = array('data'=>$v['refund_amount']);
                    }else{
                        $tmp[] = array('data'=>orderState($v));
                        $tmp[] = array('data'=>'0.00');
                    }



                    //备注
                    $order_id = $v['order_id'];
                    $a = array('order_id' => $order_id);
                    $model_refund_return = Model('refund_return');
                    $result = $model_refund_return->getRefundReturnList($a);//
                    if($result) {
                        if ($result[0]['refund_type'] == 1) {
                            if ($result[0]['refund_state'] == 1||$result[0]['refund_state'] ==2) {
                                $tmp[] = array('data' => '退款中'); //退款中

                            } else if ($result[0]['refund_state'] == 3) {
                                if ($result[0]['seller_state']==2) {
                                    $tmp[] = array('data' => '退款完成'); //退款完成
                                }else if ($result[0]['seller_state']==3) {
                                    $tmp[] = array('data' => '退款失败'); //退款失败
                                }

                            } else {
                                $tmp[] = array('data' => '');
                            }
                        } elseif ($result[0]['refund_type'] == 2) {
                            if ($result[0]['refund_state'] == 1||$result[0]['refund_state'] ==2) {
                                $tmp[] = array('data' => '退款退货中');//退款退货中

                            } else if ($result[0]['refund_state'] ==3) {
                                $tmp[] = array('data' => '退款退货完成'); //退款退货完成

                            } else {
                                $tmp[] = array('data' => '');
                            }
                        }
                    }else{
                        $tmp[] = array('data' => '');

                    }
                    if($ii=1){
                    //运单号
                    $waybill_info = unserialize($v['extend_order_common']['waybill_info']);
                    if($v['is_mode']==0){
                        $tmp[] = array('data'=>$v['shipping_code']);
                    }else{
                        if($waybill_info){
                            $tmp[] = array('data'=>$waybill_info['logisticsNo']);
                        }else{
                            $tmp[] = array('data'=>'');
                        }
                    }
                }
                //促销信息，满送等
//           $promotion_info=Model()->query("SELECT promotion_info  FROM `718shop_order_common` WHERE order_id=\"$order_id\"");
                // if($promotion_info){
                $order_type = $v['order_type'];
                switch ($order_type){
                    case 0:
                        $promotion_info= '无活动';
                        break;
                    case 1:
                        $promotion_info= '阶梯价';
                        break;
                    case 2:
                        $promotion_info= '团购';
                        break;
                    case 3:
                        $promotion_info= '新人专享';
                        break;
                    case 4:
                        $promotion_info= '限时秒杀';
                        break;
                    case 5:
                        $promotion_info= '即买即送';
                        break;
                }
                $tmp[]=array('data'=>$promotion_info);

                if($ii=1){
                    //代金券名称
                    $voucher_code= Model()->query("SELECT voucher_code  FROM `718shop_order_common` where order_id=\"$order_id\" ");
                    // var_dump( $voucher_code);die;
                    if (!empty($voucher_code[0]['voucher_code'])) {
                        $voucher=unserialize($voucher_code[0]['voucher_code']);
                        foreach ($voucher as $key1 => $value1) {

                            if (!empty($value1['voucher_code'])) {
                                $p=$value1['voucher_code'];

                                $voucher_name=Model()->query("SELECT voucher_title FROM 718shop_voucher where voucher_code=\"$p\" ");
                                $tmp[]=array('data'=>$voucher_name[0]['voucher_title']);

                            } else {
                                $tmp[]=array('data'=>'');
                            }
                        }
                    }else{
                        $tmp[] = array('data'=>'');
                    }
                }
                $excel_data[] = $tmp;
            }
        }

        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset('子订单',CHARSET));
        $excel_obj->generateXML($excel_obj->charset('子订单',CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
    }

    /**
     * 导出子订单-物资小店专用
     */
    public function export_order_sub_wzxdOp(){
        $model_order = Model('order');
        $condition  = array();
        if($_GET['order_sn']) {
            $condition['order.order_sn'] = $_GET['order_sn'];
        }
        if($_GET['store_name']) {
            $condition['order.store_name'] = $_GET['store_name'];
        }
        if(in_array($_GET['order_state'],array('0','10','20','30','40'))){
            $condition['order.order_state'] = $_GET['order_state'];
        }
        if($_GET['payment_code']) {
            $condition['order.payment_code'] = $_GET['payment_code'];
        }
        if($_GET['buyer_name']) {
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
            $condition['order.order_state'] = array('neq',0);
        }

        $condition['order.store_id'] = $_SESSION['store_id'];

        if ($_GET['goods_name'] != '') {
            $goods_name = $_GET['goods_name'];
        }
        if ($_GET['goods_serial'] != '') {
            $goods_serial = $_GET['goods_serial'];
        }
        if ($_GET['consignee_name'] != '') {
            $condition['order_common.reciver_name']=$_GET['consignee_name'];
        }

        //发货人姓名 新增
        if($_GET['senderusername']!=''){
            $sql="SELECT * from `718shop_order_goods` where kuajing_info like '%".$_GET['senderusername']."%'";
            $kuajing_info=Model()->query($sql);
            $order_id=array();
            for($i=0;$i<count($kuajing_info);$i++){
                $order_id[$i]=$kuajing_info[$i]['order_id'];
            }
            $condition['order.order_id']=array('in',$order_id);
        }

        //下单时间
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date2']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date2']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_date2']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_date2']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['order.add_time'] = array('time',array($start_unixtime,$end_unixtime));
        }

        //发货时间
        $if_start_time_fahuo = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date2_fahuo']);
        $if_end_time_fahuo = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date2_fahuo']);
        $start_unixtime_fahuo = $if_start_time_fahuo ? strtotime($_GET['query_start_date2_fahuo']) : null;
        $end_unixtime_fahuo = $if_end_time_fahuo ? strtotime($_GET['query_end_date2_fahuo']): null;
        if ($start_unixtime_fahuo || $end_unixtime_fahuo) {
            $condition['order_common.shipping_time'] = array('time',array($start_unixtime_fahuo,$end_unixtime_fahuo));
        }

        $if_start_time_pay = $_GET['query_start_date_pay2'];
        $if_end_time_pay = $_GET['query_end_date_pay2'];
        $start_unixtime_pay = $if_start_time_pay ? strtotime($_GET['query_start_date_pay2']) : null;
        $end_unixtime_pay = $if_end_time_pay ? strtotime($_GET['query_end_date_pay2']): null;

        //订单完成时间  xinzeng
        $if_start_time_finish = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date_finish2']);
        $if_end_time_finish = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date_finish2']);
        $start_unixtime_finish = $if_start_time_finish ? strtotime($_GET['query_start_date_finish2']) : null;
        $end_unixtime_finish = $if_end_time_finish ? strtotime($_GET['query_end_date_finish2']): null;
        if ($start_unixtime_finish || $end_unixtime_finish) {
            $condition['order.finnshed_time'] = array('time',array($start_unixtime_finish,$end_unixtime_finish));
        }

        if ($start_unixtime_pay || $end_unixtime_pay) {
            $condition['order.payment_time'] = array('between',array($start_unixtime_pay,$end_unixtime_pay));
        }
        if (!is_numeric($_GET['curpage'])){
            $count = $model_order->getOrderCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE ){   //显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=order&op=index');
                Tpl::showpage('export.excel');
            }else{  //如果数量小，直接下载
                $data = $model_order->getOrderList3('',$condition,'','*','order_id desc',self::EXPORT_SIZE,array('order_goods','order_common','member','goods_kuajing_d'));

                $this->createExcel_sub_wzxd($data,$goods_name,$goods_serial);
            }
        }else{  //下载
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_order->getOrderList3('',$condition,'','*','order_id desc',"{$limit1},{$limit2}",array('order_goods','order_common','member','goods_kuajing_d'));

            $this->createExcel_sub_wzxd($data,$goods_name,$goods_serial);
        }
    }

    /**
     * 生成excel子订单
     *
     * @param array $data
     */
    private function createExcel_sub_wzxd($data = array(),$goods_name,$goods_serial){
        Language::read('export');
        import('libraries.excel');
        $excel_obj = new Excel();
        $excel_data = array();
        $model_common = Model('order_common');
        $model_order_goods = Model('order_goods');
        $model_goods = Model('goods');
        //设置样式
        $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
        //header
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品名称');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品数量');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'规格型号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'下单时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'收货人姓名');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'收货人电话');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单状态');

        if ($goods_serial != '') {
            $goods_id_arr = Model()->query("SELECT goods_id FROM `718shop_goods` where goods_serial=\"$goods_serial\" LIMIT 10");
            $goods_id = $goods_id_arr[0]['goods_id'];
        }
        //data
        foreach ((array)$data as $k=>$v){
            $condition  = array();
            $condition['order_id'] = $v['order_id'];
            $condition['goods_name'] = array('like','%'.$goods_name.'%');

            //特别修正
            if ($goods_serial != '') {
                $condition['goods_id'] = $goods_id;
            }
            $cond=array();
            $cond['order_sn']=$v['order_sn'];
            $goodsid=Model('refund_return')->table('refund_return')->where($cond)->select();
            $sub_data = $model_order_goods->where($condition)->order('goods_id asc')->select();//获取子订单
            $ii = 0;
            foreach ((array)$sub_data as $k1=>$v1){
                $ii++;
                $tmp = array();
                if($ii==1){
                    $tmp[] = array('data'=>$v['order_sn']);
                }
                else {
                    $tmp[] = array('data'=>'');
                }
                //商品长度过长，临时修正
                $goid = $v1['goods_id'];
                $tmp[] = array('data'=>$v1['goods_name']);

                //商品数量
                $tmp[] = array('data'=>$v1['goods_num']);

                //规格型号
                $kuajing_id1 = Model()->query("SELECT goods_kuajingD_id FROM `718shop_goods` where goods_id=\"$goid\" LIMIT 10");
                $kuajing_id = $kuajing_id1[0]['goods_kuajingD_id'];
                $kujing_guige1= Model()->query("SELECT specification FROM `718shop_goods_kuajing_d` where id=\"$kuajing_id\" LIMIT 10");
                $kujing_guige = $kujing_guige1[0]['specification'];
                $tmp[] = array('data'=>$kujing_guige);

                //下单时间
                $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['add_time']));

                //收货人姓名
                $reciver_name_tmp = $model_common->getfby_order_id($v['order_id'],'reciver_name');
                $tmp[] = array('data'=>$reciver_name_tmp);

                //电话
                $tmp[] = array('data'=>$v['extend_order_common']['reciver_info']['phone']);

                //部分退款与全部退款
                if($v['refund_state']=='1'){
                    $tmp[] = array('data'=>'部分退款');
                }else if($v['refund_state']=='2'){
                    $tmp[] = array('data'=>'已关闭');
                }else{
                    $tmp[] = array('data'=>orderState($v));
                }

                $excel_data[] = $tmp;
            }
        }

        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset('子订单',CHARSET));
        $excel_obj->generateXML($excel_obj->charset('子订单',CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
    }



    /**
     * 订单列表
     *
     */
    public function indexOp() {
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
        $allow_state_array = array('state_new','state_pay','state_send','state_success','state_cancel');
        if (in_array($_GET['state_type'],$allow_state_array)) {
            $condition['order_state'] = str_replace($allow_state_array,
                array(ORDER_STATE_NEW,ORDER_STATE_PAY,ORDER_STATE_SEND,ORDER_STATE_SUCCESS,ORDER_STATE_CANCEL), $_GET['state_type']);
        } else {
            $_GET['state_type'] = 'store_order';
        }
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('time',array($start_unixtime,$end_unixtime));
        }

        if ($start_unixtime || $end_unixtime) {
            $condition['payment_time'] = array('time',array($start_unixtime,$end_unixtime));//xinzeng
        }

        if ($_GET['skip_off'] == 1) {
            $condition['order_state'] = array('neq',ORDER_STATE_CANCEL);
        }

        $order_list = $model_order->getOrderList($condition, 20, '*', 'order_id desc','', array('order_goods','order_common','member'));

        //页面中显示那些操作
        foreach ($order_list as $key => $order_info) {

            //显示取消订单
            $order_info['if_cancel'] = $model_order->getOrderOperateState('store_cancel',$order_info);

            //显示调整运费
            $order_info['if_modify_price'] = $model_order->getOrderOperateState('modify_price',$order_info);

            //显示修改价格
            $order_info['if_spay_price'] = $model_order->getOrderOperateState('spay_price',$order_info);

            //显示发货
            $order_info['if_send'] = $model_order->getOrderOperateState('send',$order_info);

            //显示锁定中
            $order_info['if_lock'] = $model_order->getOrderOperateState('lock',$order_info);

            //显示物流跟踪
            $order_info['if_deliver'] = $model_order->getOrderOperateState('deliver',$order_info);

            foreach ($order_info['extend_order_goods'] as $value) {
                $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
                $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
                $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
                $value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
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

        Tpl::output('order_list',$order_list);
        Tpl::output('show_page',$model_order->showpage());
        self::profile_menu('list',$_GET['state_type']);

        Tpl::showpage('store_export.index');
    }

    /**
     * 卖家订单详情
     *
     */
    public function show_orderOp() {
        Language::read('member_member_index');
        $order_id = intval($_GET['order_id']);
        if ($order_id <= 0) {
            showMessage(Language::get('wrong_argument'),'','html','error');
        }
        $model_order = Model('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['store_id'] = $_SESSION['store_id'];
        $order_info = $model_order->getOrderInfo($condition,array('order_common','order_goods','member'));
        if (empty($order_info)) {
            showMessage(Language::get('store_order_none_exist'),'','html','error');
        }

        $model_refund_return = Model('refund_return');
        $order_list = array();
        $order_list[$order_id] = $order_info;
        $order_list = $model_refund_return->getGoodsRefundList($order_list,1);//订单商品的退款退货显示
        $order_info = $order_list[$order_id];
        $refund_all = $order_info['refund_list'][0];
        if (!empty($refund_all) && $refund_all['seller_state'] < 3) {//订单全部退款商家审核状态:1为待审核,2为同意,3为不同意
            Tpl::output('refund_all',$refund_all);
        }

        //显示锁定中
        $order_info['if_lock'] = $model_order->getOrderOperateState('lock',$order_info);

        //显示调整运费
        $order_info['if_modify_price'] = $model_order->getOrderOperateState('modify_price',$order_info);

        //显示调整价格
        $order_info['if_spay_price'] = $model_order->getOrderOperateState('spay_price',$order_info);

        //显示取消订单
        $order_info['if_cancel'] = $model_order->getOrderOperateState('buyer_cancel',$order_info);

        //显示发货
        $order_info['if_send'] = $model_order->getOrderOperateState('send',$order_info);

        //显示物流跟踪
        $order_info['if_deliver'] = $model_order->getOrderOperateState('deliver',$order_info);

        //显示系统自动取消订单日期
        if ($order_info['order_state'] == ORDER_STATE_NEW) {
            //$order_info['order_cancel_day'] = $order_info['add_time'] + ORDER_AUTO_CANCEL_DAY * 24 * 3600;
            // by
            $order_info['order_cancel_day'] = $order_info['add_time'] + ORDER_AUTO_CANCEL_DAY + 3 * 24 * 3600;
        }

        //显示快递信息
        if ($order_info['shipping_code'] != '') {
            $express = rkcache('express',true);
            $order_info['express_info']['e_code'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_code'];
            $order_info['express_info']['e_name'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_name'];
            $order_info['express_info']['e_url'] = $express[$order_info['extend_order_common']['shipping_express_id']]['e_url'];
        }

        //显示系统自动收获时间
        if ($order_info['order_state'] == ORDER_STATE_SEND) {
            //$order_info['order_confirm_day'] = $order_info['delay_time'] + ORDER_AUTO_RECEIVE_DAY * 24 * 3600;
            //by
            $order_info['order_confirm_day'] = $order_info['delay_time'] + ORDER_AUTO_RECEIVE_DAY + 15 * 24 * 3600;
        }

        //如果订单已取消，取得取消原因、时间，操作人
        if ($order_info['order_state'] == ORDER_STATE_CANCEL) {
            $order_info['close_info'] = $model_order->getOrderLogInfo(array('order_id'=>$order_info['order_id']),'log_id desc');
        }

        foreach ($order_info['extend_order_goods'] as $value) {
            $value['image_60_url'] = cthumb($value['goods_image'], 60, $value['store_id']);
            $value['image_240_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
            $value['goods_type_cn'] = orderGoodsType($value['goods_type']);
            $value['goods_url'] = urlShop('goods','index',array('goods_id'=>$value['goods_id']));
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

        Tpl::output('order_info',$order_info);

        //发货信息
        if (!empty($order_info['extend_order_common']['daddress_id'])) {
            $daddress_info = Model('daddress')->getAddressInfo(array('address_id'=>$order_info['extend_order_common']['daddress_id']));
            Tpl::output('daddress_info',$daddress_info);
        }

        Tpl::showpage('store_order.show');
    }

    /**
     * 卖家订单状态操作
     *
     */
    public function change_stateOp() {
        $state_type	= $_GET['state_type'];
        $order_id	= intval($_GET['order_id']);

        $model_order = Model('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['store_id'] = $_SESSION['store_id'];
        $order_info	= $model_order->getOrderInfo($condition);

        if ($_GET['state_type'] == 'order_cancel') {
            $result = $this->_order_cancel($order_info,$_POST);
        } elseif ($_GET['state_type'] == 'modify_price') {
            $result = $this->_order_ship_price($order_info,$_POST);
        } elseif ($_GET['state_type'] == 'spay_price') {
            $result = $this->_order_spay_price($order_info,$_POST);
        }
        if (!$result['state']) {
            showDialog($result['msg'],'','error',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
        } else {
            showDialog($result['msg'],'reload','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
        }
    }

    /**
     * 取消订单
     * @param unknown $order_info
     */
    private function _order_cancel($order_info, $post) {
        $model_order = Model('order');
        $logic_order = Logic('order');

        if(!chksubmit()) {
            Tpl::output('order_info',$order_info);
            Tpl::output('order_id',$order_info['order_id']);
            Tpl::showpage('store_order.cancel','null_layout');
            exit();
        } else {
            $if_allow = $model_order->getOrderOperateState('store_cancel',$order_info);
            if (!$if_allow) {
                return callback(false,'无权操作');
            }
            $msg = $post['state_info1'] != '' ? $post['state_info1'] : $post['state_info'];
            return $logic_order->changeOrderStateCancel($order_info,'seller',$_SESSION['member_name'], $msg);
        }
    }

    /**
     * 修改运费
     * @param unknown $order_info
     */
    private function _order_ship_price($order_info, $post) {
        $model_order = Model('order');
        $logic_order = Logic('order');
        if(!chksubmit()) {
            Tpl::output('order_info',$order_info);
            Tpl::output('order_id',$order_info['order_id']);
            Tpl::showpage('store_order.edit_price','null_layout');
            exit();
        } else {
            $if_allow = $model_order->getOrderOperateState('modify_price',$order_info);
            if (!$if_allow) {
                return callback(false,'无权操作');
            }
            return $logic_order->changeOrderShipPrice($order_info,'seller',$_SESSION['member_name'],$post['shipping_fee']);
        }

    }
    /**
     * 修改商品价格
     * @param unknown $order_info
     */
    private function _order_spay_price($order_info, $post) {
        $model_order = Model('order');
        $logic_order = Logic('order');
        if(!chksubmit()) {
            Tpl::output('order_info',$order_info);
            Tpl::output('order_id',$order_info['order_id']);
            Tpl::showpage('store_order.edit_spay_price','null_layout');
            exit();
        } else {
            $if_allow = $model_order->getOrderOperateState('spay_price',$order_info);
            if (!$if_allow) {
                return callback(false,'无权操作');
            }
            return $logic_order->changeOrderSpayPrice($order_info,'seller',$_SESSION['member_name'],$post['goods_amount']);
        }
    }


    /**
     * 用户中心右边，小导航
     *
     * @param string	$menu_type	导航类型
     * @param string 	$menu_key	当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_type='',$menu_key='') {
        Language::read('member_layout');
        switch ($menu_type) {
            case 'list':
                $menu_array = array(
                    array('menu_key'=>'store_order',		'menu_name'=>Language::get('nc_member_path_all_order'),	'menu_url'=>'index.php?act=store_order'),
                    array('menu_key'=>'state_new',			'menu_name'=>Language::get('nc_member_path_wait_pay'),	'menu_url'=>'index.php?act=store_order&op=index&state_type=state_new'),
                    array('menu_key'=>'state_pay',	        'menu_name'=>Language::get('nc_member_path_wait_send'),	'menu_url'=>'index.php?act=store_order&op=store_order&state_type=state_pay'),
                    array('menu_key'=>'state_send',		    'menu_name'=>Language::get('nc_member_path_sent'),	    'menu_url'=>'index.php?act=store_order&op=index&state_type=state_send'),
                    array('menu_key'=>'state_success',		'menu_name'=>Language::get('nc_member_path_finished'),	'menu_url'=>'index.php?act=store_order&op=index&state_type=state_success'),
                    array('menu_key'=>'state_cancel',		'menu_name'=>Language::get('nc_member_path_canceled'),	'menu_url'=>'index.php?act=store_order&op=index&state_type=state_cancel'),
                );
                break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }
}
