<?php
/**
 * 团长佣金
 ** */

defined('In718Shop') or exit('Access Invalid!');
class brokerageControl extends SystemControl{
	const EXPORT_SIZE = 1000;
	public function __construct(){
		parent::__construct();
		Language::read('predeposit');
	}

	/**
	 * 提现列表
	 */
	public function pd_cash_listOp(){
	    $condition = array();
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['stime']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['etime']);
        $start_unixtime = $if_start_date ? strtotime($_GET['stime']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['etime']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['pdc_add_time'] = array('time',array($start_unixtime,$end_unixtime));
        }
        $if_start_pdate = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['sptime']);
        $if_end_pdate = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['eptime']);
        $spayment_time = $if_start_pdate ? strtotime($_GET['sptime']) : null;
        $epayment_time = $if_end_pdate ? strtotime($_GET['eptime']): null;
        if ($spayment_time || $epayment_time) {
            $condition['pdc_payment_time'] = array('time',array($spayment_time,$epayment_time));
        }
        if (!empty($_GET['mname'])){
            $condition['pdc_member_name'] = $_GET['mname'];
        }
        if (!empty($_GET['pdc_bank_user'])){
        	$condition['pdc_bank_user'] = $_GET['pdc_bank_user'];
        }
		if ($_GET['paystate_search'] != ''){
			$condition['pdc_payment_state'] = $_GET['paystate_search'];
		}
		$condition['pdc_bank_name'] = array('eq','wx_change');//只显示佣金部分提现
		$model_pd = Model('predeposit');
		$cash_list = $model_pd->getPdCashList($condition,20,'*','pdc_payment_state asc,pdc_add_time desc');
        foreach ($cash_list as $k => $v) {
            $cash_list[$k]['payment_code'] = Model('order')->getfby_order_sn($v['order_sn'], 'payment_code');
            $cash_list[$k]['refund_amount'] = Model('refund_return')->getRefundReturnInfo(array('refund_id' => $v['refund_id']), 'refund_amount')['refund_amount'];
        }
		Tpl::output('list',$cash_list);
		Tpl::output('show_page',$model_pd->showpage());
		Tpl::showpage('brokerage_cash.list');
	}

	//保证金
	public function depositOp()
	{
		$model_deposit = Model('deposit');
		$deposit_info = $model_deposit->where(array('id'=>1))->field('deposit')->find();
		/**
		 * 保存
		 */
		if (chksubmit()){
			$obj_validate = new Validate();
			$obj_validate->validateparam = array(
			    array("input"=>$_POST["deposit"], "require"=>"true", "message"=>'保证金不能为空')
			);
			$error = $obj_validate->validate();
			if ($error != ''){
				showMessage($error);
			}else {
				$insert_array = array();
				$insert_array['deposit'] = $_POST['deposit'];
				if (empty($deposit_info)) {
					$result = $model_deposit->insert($insert_array);
				}else{
					$result = $model_deposit->where(array('id'=>1))->update($insert_array);
				}
				if ($result){
					$url = array(
					array(
					'url'=>'index.php?act=brokerage&op=pd_cash_list',
					'msg'=>'佣金提现管理',
					),
					);
					showMessage('保证金添加成功',$url);
				}else {
					showMessage('保证金添加失败',$url);
				}
			}
		}
		if(!empty($deposit_info) && is_array($deposit_info)){
			$deposit = $deposit_info['deposit'];
			Tpl::output('deposit',$deposit);
		}
		Tpl::showpage('deposit.add');
	}

	/**
	 * 删除提现记录
	 */
	public function pd_cash_delOp(){
		$pdc_id = intval($_GET["id"]);
		if ($pdc_id <= 0){
			showMessage(Language::get('admin_predeposit_parameter_error'),'index.php?act=predeposit&op=pd_cash_list','','error');
		}
		$model_pd = Model('predeposit');
		$condition = array();
		$condition['pdc_id'] = $pdc_id;
		$condition['pdc_payment_state'] = 0;
		$info = $model_pd->getPdCashInfo($condition);
		if (!$info) {
		    showMessage(Language::get('admin_predeposit_parameter_error'),'index.php?act=predeposit&op=pd_cash_list','','error');
		}
		try {
		    $result = $model_pd->delPdCash($condition);
		    if (!$result) {
		        throw new Exception(Language::get('admin_predeposit_cash_del_fail'));
		    }
		    //退还冻结的预存款
		    $model_member = Model('member');
		    $member_info = $model_member->getMemberInfo(array('member_id'=>$info['pdc_member_id']));
		    //扣除冻结的预存款
		    $admininfo = $this->getAdminInfo();
		    $data = array();
		    $data['member_id'] = $member_info['member_id'];
		    $data['member_name'] = $member_info['member_name'];
		    $data['amount'] = $info['pdc_amount'];
		    $data['order_sn'] = $info['pdc_sn'];
		    $data['admin_name'] = $admininfo['name'];
		    $model_pd->changePd('cash_del',$data);
		    $model_pd->commit();
			showMessage(Language::get('admin_predeposit_cash_del_success'),'index.php?act=predeposit&op=pd_cash_list');

		} catch (Exception $e) {
		    $model_pd->commit();
		    showMessage($e->getMessage(),'index.php?act=predeposit&op=pd_cash_list','html','error');
		}
	}

	/**
	 * 更改提现为支付状态
	 */
	public function pd_cash_payOp(){
	    $id = intval($_GET['id']);
	    die;
	    if ($id <= 0){
	        showMessage(Language::get('admin_predeposit_parameter_error'),'index.php?act=predeposit&op=pd_cash_list','','error');
	    }
	    $model_pd = Model('predeposit');
	    $condition = array();
	    $condition['pdc_id'] = $id;
	    $condition['pdc_payment_state'] = 0;
	    $info = $model_pd->getPdCashInfo($condition);//是否二维数组
	    if (!is_array($info) || count($info)<0){
	        showMessage(Language::get('admin_predeposit_record_error'),'index.php?act=brokerage&op=pd_cash_list','','error');
	    }
	    //查询用户信息
	    $model_member = Model('member');
	    $member_info = $model_member->getMemberInfo(array('member_id'=>$info['pdc_member_id']));
	    
	    //微信转账
	    $this->wxtransfers($info,$member_info['groupbuy_leader_id']);//退款单信息和对应团长id

        $update = array();
        $admininfo = $this->getAdminInfo();
        $update['pdc_payment_state'] = 1;
        $update['pdc_payment_admin'] = $admininfo['name'];
        $update['pdc_payment_time'] = TIMESTAMP;
        $log_msg = L('admin_predeposit_cash_edit_state').','.L('admin_predeposit_cs_sn').':'.$info['pdc_sn'];

        try {
            $model_pd->beginTransaction();
            $result = $model_pd->editPdCash($update,$condition);
            if (!$result) {
                throw new Exception(Language::get('admin_predeposit_cash_edit_fail'));
            }
            //扣除冻结的预存款
            $data = array();
            $data['member_id'] = $member_info['member_id'];
            $data['member_name'] = $member_info['member_name'];
            $data['amount'] = $info['pdc_amount'];
            $data['order_sn'] = $info['pdc_sn'];
            $data['admin_name'] = $admininfo['name'];
            $model_pd->changePd('cash_pay',$data);
            $model_pd->commit();
            $this->log($log_msg,1);
            showMessage(Language::get('admin_predeposit_cash_edit_success'),'index.php?act=brokerage&op=pd_cash_list');
        } catch (Exception $e) {
            $model_pd->rollback();
            $this->log($log_msg,0);
            showMessage($e->getMessage(),'index.php?act=brokerage&op=pd_cash_list','html','error');
        }
	}

	/**
	 * 查看提现信息
	 */
	public function pd_cash_viewOp(){
	    $id = intval($_GET['id']);
	    if ($id <= 0){
	        showMessage(Language::get('admin_predeposit_parameter_error'),'index.php?act=predeposit&op=pd_cash_list','','error');
	    }
	    $model_pd = Model('predeposit');
	    $condition = array();
	    $condition['pdc_id'] = $id;
	    $info = $model_pd->getPdCashInfo($condition);
	    if (!is_array($info) || count($info)<0){
	        showMessage(Language::get('admin_predeposit_record_error'),'index.php?act=predeposit&op=pd_cash_list','','error');
	    }

	    //jinp0827 读取refund_return表中的“平台审核”
	    //$model_refund = Model('refund_return');
		//$condition_1 = array();
		//$condition_1['refund_id'] = $info['refund_id'];
		//$refund_list = $model_refund->getRefundList($condition_1);
		//$refund = $refund_list[0];
		//Tpl::output('refund',$refund);
		//新增支付方式
		$payment=array();
        $order_sn=$info['order_sn'];
        $code = Model()->query("SELECT payment_code  FROM `718shop_order` where order_sn=\"$order_sn\" ");
        $payment['payment_code']=$code[0]['payment_code'];
        $payment_code=$payment['payment_code'];
        $name = Model()->query("SELECT payment_name  FROM `718shop_payment` where payment_code=\"$payment_code\" ");
        $payment_name=$name[0]['payment_name'];
        if ($payment_name) {
        	$payment['payment_name']=$payment_name;
        }else{
        	$payment['payment_name']=$payment['payment_code'];
        }

        
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['refund_id'] = $info['refund_id'];
//		$refund_list = $model_refund->getRefundList($condition);
		$refund = $model_refund->getRefundReturnInfo($condition);
//		$refund = $refund_list[0];
		Tpl::output('refund',$refund);
        Tpl::output('payment',$payment);


	    Tpl::output('info',$info);
	    Tpl::showpage('brokerage_cash.view');
	}
	
	//取得会员信息
	public function checkmemberOp(){
		$name = trim($_GET['name']);
		if (!$name){
			echo ''; die;
		}
		/**
		 * 转码
		 */
		if(strtoupper(CHARSET) == 'GBK'){
			$name = Language::getGBK($name);
		}
		$obj_member = Model('member');
		$member_info = $obj_member->getMemberInfo(array('member_name'=>$name));
		if (is_array($member_info) && count($member_info)>0){
			if(strtoupper(CHARSET) == 'GBK'){
				$member_info['member_name'] = Language::getUTF8($member_info['member_name']);
			}
			echo json_encode(array('id'=>$member_info['member_id'],'name'=>$member_info['member_name'],'available_predeposit'=>$member_info['available_predeposit'],'freeze_predeposit'=>$member_info['freeze_predeposit']));
		}else {
			echo ''; die;
		}
	}
	/**
	 * 微信转账
	 * @param  [type] $info  退款单信息
	 * @param  [type] $ld_id 团长id
	 * @return [type]        [description]
	 */
	private function wxtransfers($info,$ld_id)
	{

	    $ld_info = Model()->table('groupbuy_leader')->where(array('groupbuy_leader_id'=>$ld_id))->field('wx_openid')->find();
	    $wx_openId = $ld_info['wx_openid'];
	    $model_payment = Model('mb_payment');
        $payment_info = $model_payment->getMbPaymentInfo(array('payment_id' => 2));
        $wxpay = $payment_info['payment_config'];
        define('WXPAY_APPID', 'wx074067c8956a62ea');//团长端appid
        define('WXPAY_MCHID', $wxpay['partnerId']);
        define('WXPAY_KEY', $wxpay['apiKey']);
        $total_fee = $info['pdc_amount']*100;//本次微信提款金额(单位为分)
        $api_file = BASE_PATH.DS.'api'.DS.'refund'.DS.'wxpay'.DS.'WxPay.Api.php';
        include $api_file;
        $input = new WxTransfers();
        $input->SetOut_trade_no($info['pdc_sn']);//微信订单号
        $input->SetTotal_fee($total_fee);
        $input->SetDesc('线上小店团长测试');
		$input->SetCheck_name('NO_CHECK');
		$input->SetOpenid($wx_openId);//用户openid
        $wxpay_result = WxPayApi::transfers($input);
        if(empty($wxpay_result) || $wxpay_result['return_code'] != 'SUCCESS' || !empty($wxpay_result['err_code'])) {//请求结果
            showMessage("微信转账失败,".$wxpay_result['err_code_des'],'index.php?act=brokerage&op=pd_cash_list');
        }
        return true;
	}

	//导出佣金明细
	// public function export_excellOp()
	// {
	// 	ini_set('memory_limit', '-1');
 //        set_time_limit(0);
	// 	$model_brokerage = Model('brokerage');
	// 	$condition = array();
	// 	$if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['stime']);
 //        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['etime']);
 //        $start_unixtime = $if_start_date ? strtotime($_GET['stime']) : null;
 //        $end_unixtime = $if_end_date ? strtotime($_GET['etime']): null;
 //        if ($start_unixtime || $end_unixtime) {
 //            $condition['pdr_add_time'] = array('time',array($start_unixtime,$end_unixtime));
 //        }

 //        $pay_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['pay_stime']);
 //        $pay_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['pay_etime']);
 //        $pay_start_unixtime = $pay_start_date ? strtotime($_GET['pay_stime']) : null;
 //        $pay_end_unixtime = $pay_end_date ? strtotime($_GET['pay_etime']): null;
 //        if ($pay_start_unixtime || $pay_end_unixtime) {
 //            $condition['pay_time'] = array('time',array($start_unixtime,$end_unixtime));
 //        }
 //        if (!empty($_GET['nickname'])){
 //            $condition['nickname'] = $_GET['nickname'];
 //        }
 //        if (!empty($_GET['tz_id'])){
 //        	$condition['tz_id'] = $_GET['tz_id'];
 //        }
 //        // echo "<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>111";
 //        // var_dump($condition);die;
	// 	$data = $model_brokerage->field('*')->where($condition)->select();
	// 	$total_amount = $model_brokerage->where($condition)->sum('commission');
	// 	require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel.php';
	// 	$objPHPExcel = new PHPExcel();
	// 	$objPHPExcel->setActiveSheetIndex(0)
 //           ->setCellValue( 'A1', '团长结算订单明细' )
 //           ->setCellValue( 'A2', '团长名称' )
 //           ->setCellValue( 'B2', '团长id' )
 //           ->setCellValue( 'C2', '订单号')
 //           ->setCellValue( 'D2', '商品名称')
 //           ->setCellValue( 'E2', '商品数量')
 //           ->setCellValue( 'F2', '自提地址')
 //           ->setCellValue( 'G2', '子订单号')
 //           ->setCellValue( 'H2', '支付时间')
 //           ->setCellValue( 'I2', '商品单价')
 //           ->setCellValue( 'J2', '商品成本')
 //           ->setCellValue( 'K2', '商品总成本')
 //           ->setCellValue( 'L2', '商品总价')
 //           ->setCellValue( 'M2', '优惠券优惠金额')
 //           ->setCellValue( 'N2', '实际支付金额')
 //           ->setCellValue( 'O2', '订单总额')
 //           ->setCellValue( 'P2', '支付方式')
 //           ->setCellValue( 'Q2', '发货备注')
 //           ->setCellValue( 'R2', '订单状态')
 //           ->setCellValue( 'S2', '促销信息')
 //           ->setCellValue( 'T2', '代金券')
 //           ->setCellValue( 'U2', '商品一级分类')
 //           ->setCellValue( 'V2', '佣金比例')
 //           ->setCellValue( 'W2', '佣金');
 //        $objActSheet =$objPHPExcel->getActiveSheet();//取当前活动的表
 //        //设置表格样式
 //        $objActSheet->mergeCells('A1:W1');
 //        $objActSheet->getStyle('A1')->getFont()->setSize(20);
 //        $objActSheet->getStyle('A2:W3')->getFont()->setSize(11);
	// 	$objActSheet->getStyle('A1:W2')->getFont()->setBold(true);
	// 	$objActSheet->getStyle('A1:W2')->getFont()->setName('宋体');
 //        //给当前活动的表设置名称
	// 	$objActSheet->setTitle('团长佣金结算明细');
	// 	//数据填充
	// 	$baseRow=3;
	// 	foreach($data as $r => $dataRow){
 //      		$row= $baseRow +$r;    //$row是循环操作行的行号
 //      		$objActSheet->insertNewRowBefore($row,1);  //在操作行的号前加一空行，这空行的行号就变成了当前的行号
 //       		//对应的列都附上数据和编号
 //       		$objActSheet->setCellValue( 'B'.$row,$dataRow['tz_name']);
 //      		$objActSheet->setCellValue( 'A'.$row,$dataRow['tz_id']);
 //      		$objActSheet->setCellValue( 'C'.$row,' '.$dataRow['order_sn']);
 //      		$objActSheet->setCellValue( 'D'.$row,$dataRow['goods_name']);
 //      		$objActSheet->setCellValue( 'E'.$row,$dataRow['goods_sum']);
 //      		$objActSheet->setCellValue( 'F'.$row,$dataRow['ziti_name']);
 //      		$objActSheet->setCellValue( 'G'.$row,' '.$dataRow['sub_order_sn']);
 //      		$objActSheet->setCellValue( 'H'.$row,date('Y-m-d Y-m-d H:i:s',$dataRow['pay_time']));
 //      		$objActSheet->setCellValue( 'I'.$row,$dataRow['goods_price']);
 //      		$objActSheet->setCellValue( 'J'.$row,$dataRow['cost_price']);
 //      		$objActSheet->setCellValue( 'K'.$row,$dataRow['total_cost']);
 //      		$objActSheet->setCellValue( 'L'.$row,$dataRow['total_price']);
 //      		$objActSheet->setCellValue( 'M'.$row,$dataRow['discount_amount']);
 //      		$objActSheet->setCellValue( 'N'.$row,$dataRow['amount_paid']);
 //      		$objActSheet->setCellValue( 'O'.$row,$dataRow['order_amount']);
 //      		$objActSheet->setCellValue( 'P'.$row,$dataRow['pay_type_name']);
 //      		$objActSheet->setCellValue( 'Q'.$row,$dataRow['shipping_note']);
 //      		$objActSheet->setCellValue( 'R'.$row,$dataRow['order_state']);
 //      		$objActSheet->setCellValue( 'S'.$row,$dataRow['promotion_info']);
 //      		$objActSheet->setCellValue( 'T'.$row,$dataRow['voucher_name']);
 //      		$objActSheet->setCellValue( 'U'.$row,$dataRow['cate_name']);
 //      		$objActSheet->setCellValue( 'V'.$row,$dataRow['commission_rate'].'%');
 //      		$objActSheet->setCellValue( 'W'.$row,$dataRow['commission']);
	// 	}
	// 	//合并统计
	// 	$lastRow = $row+1;
	// 	$objActSheet->mergeCells('A'.$lastRow.':V'.$lastRow);
	// 	$objActSheet->setCellValue( 'A'.$lastRow,'应付佣金总计');
	// 	$objActSheet->setCellValue( 'W'.$lastRow,$total_amount);
	// 	$objActSheet->getStyle('A'.$lastRow)->getFont()->setName('宋体');
	// 	$objActSheet->getStyle('A1:W'.$lastRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	// 	//下载文件
	// 	header('Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	// 	header('Content-Disposition:attachment;filename="团长结算订单明细.xlsx"');//设置文件名
	// 	header('Cache-Control:max-age=0');
	// 	$objWriter =PHPExcel_IOFactory:: createWriter($objPHPExcel, 'Excel2007');
	// 	$objWriter->save( 'php://output');
	// 	exit;
	// }
	public function export_excellOp()
	{
		ini_set('memory_limit', '-1');
        set_time_limit(0);
		$condition = array();
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['stime']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['etime']);
        $start_unixtime = $if_start_date ? strtotime($_GET['stime']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['etime']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['pdr_add_time'] = array('time',array($start_unixtime,$end_unixtime));
        }

        $pay_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['pay_stime']);
        $pay_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['pay_etime']);
        $pay_start_unixtime = $pay_start_date ? strtotime($_GET['pay_stime']) : null;
        $pay_end_unixtime = $pay_end_date ? strtotime($_GET['pay_etime']): null;
        if ($pay_start_unixtime || $pay_end_unixtime) {
            $condition['pay_time'] = array('between',$pay_start_unixtime.','.$pay_end_unixtime);
        }
        if (!empty($_GET['nickname'])){
            $condition['nickname'] = $_GET['nickname'];
        }
        if (!empty($_GET['tz_id'])){
        	$condition['tz_id'] = $_GET['tz_id'];
        }
		// if ($_GET['paystate_search'] != ''){
		// 	$condition['pdc_payment_state'] = $_GET['paystate_search'];
		// }
		// echo "<br/><br/><br/><br/><br/><br/><br/><br/>";
		// var_dump($condition);
		$model_brokerage = Model('brokerage');
		$data = $model_brokerage->field('*')->where($condition)->select();
		// var_dump($data);die;
		$total_amount = $model_brokerage->where($condition)->sum('commission');
		require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0)
           ->setCellValue( 'A1', '团长结算订单明细' )
           ->setCellValue( 'A2', '团长名称' )
           ->setCellValue( 'B2', '团长id' )
           ->setCellValue( 'C2', '订单号')
           ->setCellValue( 'D2', '商品名称')
           ->setCellValue( 'E2', '商品数量')
           ->setCellValue( 'F2', '自提地址')
           ->setCellValue( 'G2', '子订单号')
           ->setCellValue( 'H2', '支付时间')
           ->setCellValue( 'I2', '商品单价')
           ->setCellValue( 'J2', '商品成本')
           ->setCellValue( 'K2', '商品总成本')
           ->setCellValue( 'L2', '商品总价')
           ->setCellValue( 'M2', '优惠券优惠金额')
           ->setCellValue( 'N2', '实际支付金额')
           ->setCellValue( 'O2', '订单总额')
           ->setCellValue( 'P2', '支付方式')
           ->setCellValue( 'Q2', '发货备注')
           ->setCellValue( 'R2', '订单状态')
           ->setCellValue( 'S2', '促销信息')
           ->setCellValue( 'T2', '代金券')
           ->setCellValue( 'U2', '商品一级分类')
           ->setCellValue( 'V2', '佣金比例')
           ->setCellValue( 'W2', '佣金');
        $objActSheet =$objPHPExcel->getActiveSheet();//取当前活动的表
        //设置表格样式
        $objActSheet->mergeCells('A1:W1');
        $objActSheet->getStyle('A1')->getFont()->setSize(20);
        $objActSheet->getStyle('A2:W3')->getFont()->setSize(11);
		$objActSheet->getStyle('A1:W2')->getFont()->setBold(true);
		$objActSheet->getStyle('A1:W2')->getFont()->setName('宋体');
        //给当前活动的表设置名称
		$objActSheet->setTitle('团长佣金结算明细');
		//数据填充
		$baseRow=3;
		foreach($data as $r => $dataRow){
      		$row= $baseRow +$r;    //$row是循环操作行的行号
      		$objActSheet->insertNewRowBefore($row,1);  //在操作行的号前加一空行，这空行的行号就变成了当前的行号
       		//对应的列都附上数据和编号
       		$objActSheet->setCellValue( 'B'.$row,$dataRow['tz_name']);
      		$objActSheet->setCellValue( 'A'.$row,$dataRow['tz_id']);
      		$objActSheet->setCellValue( 'C'.$row,' '.$dataRow['order_sn']);
      		$objActSheet->setCellValue( 'D'.$row,$dataRow['goods_name']);
      		$objActSheet->setCellValue( 'E'.$row,$dataRow['goods_sum']);
      		$objActSheet->setCellValue( 'F'.$row,$dataRow['ziti_name']);
      		$objActSheet->setCellValue( 'G'.$row,' '.$dataRow['sub_order_sn']);
      		$objActSheet->setCellValue( 'H'.$row,date('Y-m-d H:i:s',$dataRow['pay_time']));
      		$objActSheet->setCellValue( 'I'.$row,$dataRow['goods_price']);
      		$objActSheet->setCellValue( 'J'.$row,$dataRow['cost_price']);
      		$objActSheet->setCellValue( 'K'.$row,$dataRow['total_cost']);
      		$objActSheet->setCellValue( 'L'.$row,$dataRow['total_price']);
      		$objActSheet->setCellValue( 'M'.$row,$dataRow['discount_amount']);
      		$objActSheet->setCellValue( 'N'.$row,$dataRow['amount_paid']);
      		$objActSheet->setCellValue( 'O'.$row,$dataRow['order_amount']);
      		$objActSheet->setCellValue( 'P'.$row,$dataRow['pay_type_name']);
      		$objActSheet->setCellValue( 'Q'.$row,$dataRow['shipping_note']);
      		$objActSheet->setCellValue( 'R'.$row,$dataRow['order_state']);
      		$objActSheet->setCellValue( 'S'.$row,$dataRow['promotion_info']);
      		$objActSheet->setCellValue( 'T'.$row,$dataRow['voucher_name']);
      		$objActSheet->setCellValue( 'U'.$row,$dataRow['cate_name']);
      		$objActSheet->setCellValue( 'V'.$row,$dataRow['commission_rate'].'%');
      		$objActSheet->setCellValue( 'W'.$row,$dataRow['commission']);
		}
		//合并统计
		$lastRow = $row+1;
		$objActSheet->mergeCells('A'.$lastRow.':V'.$lastRow);
		$objActSheet->setCellValue( 'A'.$lastRow,'应付佣金总计');
		$objActSheet->setCellValue( 'W'.$lastRow,$total_amount);
		$objActSheet->getStyle('A'.$lastRow)->getFont()->setName('宋体');
		$objActSheet->getStyle('A1:W'.$lastRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		//下载文件
		header('Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition:attachment;filename="团长结算订单明细.xlsx"');//设置文件名
		header('Cache-Control:max-age=0');
		$objWriter =PHPExcel_IOFactory:: createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save( 'php://output');
		exit;
	}
	
	
}
