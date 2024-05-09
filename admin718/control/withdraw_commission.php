<?php
/**
 * 佣金提现管理
 ** */

defined('In718Shop') or exit('Access Invalid!');
class withdraw_commissionControl extends SystemControl{
	const EXPORT_SIZE = 1000;
	public function __construct(){
		parent::__construct();
		Language::read('predeposit');
	}

	/**
	 * 佣金提现列表
	 */
	public function fx_cash_listOp(){
        $condition = array();
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['fxc_add_time'] = array('time',array($start_unixtime,$end_unixtime));
        }
        if (!empty($_GET['mname'])){
        	$condition['fxc_member_name'] = $_GET['mname'];
        }
		if ($_GET['paystate_search'] != ''){
			$condition['fxc_payment_state'] = $_GET['paystate_search'];
		}
		//var_dump($condition);die;
		$model_pd = Model('withdraw_commission');
		$recharge_list = $model_pd->getPdRechargeList($condition,20,'*','fxc_add_time desc');
		//var_dump($recharge_list);die;
		//信息输出
		Tpl::output('list',$recharge_list);
		Tpl::output('show_page',$model_pd->showpage());
		Tpl::showpage('fx_cash_list');
	}

	/**
	 * 导出佣金提现记录
	 *
	 */
	public function export_cash_step1Op(){
	    $condition = array();
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['stime']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['etime']);
        $start_unixtime = $if_start_date ? strtotime($_GET['stime']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['etime']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['pdc_add_time'] = array('time',array($start_unixtime,$end_unixtime));
        }
        if (!empty($_GET['mname'])){
            $condition['fxc_member_name'] = $_GET['mname'];
        }
		$model_pd = Model('withdraw_commission');
		$model_order = Model('order');
		$model_refund_return = Model('refund_return');

		if (!is_numeric($_GET['curpage'])){
			$count = $model_pd->getPdCashCount($condition);
			$array = array();
			if ($count > self::EXPORT_SIZE ){	//显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}

				Tpl::output('list',$array);
				Tpl::output('murl','index.php?act=withdraw_commission&op=fx_cash_list');
				Tpl::showpage('export.excel');
			}else{	//如果数量小，直接下载
				$data = $model_pd->getPdCashList($condition,'','*','fxc_sn desc',self::EXPORT_SIZE);
				$con=array();
				for($i=0;$i<$count;$i++){
				    $con['fxc_sn']=$data[$i]['fxc_sn'];
                    $order_info=$model_order->table('order')->where($con)->find();
                    $refund_id=$data[$i]['refund_id'];
                    $refund_info=$model_refund_return->getRefundReturnInfo(array('refund_id'=>$refund_id));
                    $data[$i]['goods_id'] = $refund_info['goods_id'];
                    $data[$i]['order_goods_id'] = $refund_info['order_goods_id'];
                }
				$cashpaystate = array(1=>'审核中',2=>'审核通过',3=>'已驳回');
				foreach ($data as $k=>$v) {
					$data[$k]['fxc_payment_state'] = $cashpaystate[$v['fxc_payment_state']];
				}
				$fx_cash_way = array(1=>'账户余额',2=>'支付宝',3=>'网银');
				foreach ($data as $k=>$v) {
					$data[$k]['fx_cash_way'] = $fx_cash_way[$v['fx_cash_way']];
				}
				Tpl::output('data',$data);
				Tpl::output('order',$order_info);
				// Tpl::showpage('order_info');
				$this->createCashExcel($data);
			}
		}else{	//下载
			$limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
			$limit2 = self::EXPORT_SIZE;
			$data = $model_pd->getPdCashList($condition,'','*','fxc_sn desc',"{$limit1},{$limit2}");
			$cashpaystate = array(1=>'审核中',2=>'审核通过',3=>'审核通过');
			foreach ($data as $k=>$v) {
				$data[$k]['fxc_payment_state'] = $cashpaystate[$v['fxc_payment_state']];
			}
			$fx_cash_way = array(1=>'账户余额',2=>'支付宝',3=>'网银');
			foreach ($data as $k=>$v) {
				$data[$k]['fx_cash_way'] = $fx_cash_way[$v['fx_cash_way']];
				}
			$this->createCashExcel($data);
		}
	}

	/**
	 * 生成导出预存款提现excel
	 *
	 * @param array $data
	 */
	private function createCashExcel($data = array()){
		Language::read('export');
		import('libraries.excel');
		$excel_obj = new Excel();
		$excel_data = array();
		//设置样式
		$excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
		//header
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_no'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_memberid'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_member'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_money'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_ctime'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_withdraw_way'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_pays_account'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_account_tel'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_com_bank_user'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_com_bank_name'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_com_bank_address'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_com_state'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_com_member'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_com_time'));
		foreach ((array)$data as $k=>$v){
			$tmp = array();
			// $tmp[] = array('data'=>'NC'.$v['id']);181116注释
			$tmp[] = array('data'=>'NC'.$v['fxc_sn']);//181116新增提现编号的完整显示
			$tmp[] = array('data'=>$v['fxc_member_id']);
			$tmp[] = array('data'=>$v['fxc_member_name']);
			$tmp[] = array('data'=>$v['fxc_amount']);
			$v['fxc_add_time']?$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['fxc_add_time'])):$tmp[]=array('data'=>'');
			$tmp[] = array('data'=>$v['fx_cash_way']);
			$tmp[] = array('data'=>$v['fxc_bank_no']);
			//$tmp[] = array('data'=>$v['fxc_bank_name']);
			$tmp[] = array('data'=>$v['mamber_mobile']);
			$tmp[] = array('data'=>$v['fxc_bank_user']);
			$tmp[] = array('data'=>$v['fxc_bank_name']);
			$tmp[] = array('data'=>$v['fx_cash_address']);
			$tmp[] = array('data'=>$v['fxc_payment_state']);
			$tmp[] = array('data'=>$v['fxc_payment_admin']);
			$v['fxc_payment_time']?$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['fxc_payment_time'])):$tmp[]=array('data'=>'');
			$excel_data[] = $tmp;
		}
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset(L('exp_tx_title'),CHARSET));
		$excel_obj->generateXML($excel_obj->charset(L('exp_tx_title'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
	}

		/**
	 * 查看佣金提现信息
	 */
	public function fx_cash_viewOp(){
	    $id = intval($_GET['id']);
	    if ($id <= 0){
	        showMessage(Language::get('admin_predeposit_parameter_error'),'index.php?act=withdraw_commission&op=fx_cash_list','','error');
	    }
	    $model_pd = Model('withdraw_commission');
	    $condition = array();
	    $condition['id'] = $id;
	    $info = $model_pd->getWdCashInfo($condition);
	    if (!is_array($info) || count($info)<0){
	        showMessage(Language::get('admin_predeposit_record_error'),'index.php?act=withdraw_commission&op=fx_cash_list','','error');
	    }
	    //var_dump($info);die;
		$condition = array();
	    Tpl::output('info',$info);
	    Tpl::showpage('fx_cash_view');
	}


		/**
	 * 操作页(更改成收到款)
	 */

	public function fx_cash_editOp(){
		$id = intval($_GET['id']);
		if ($id <= 0){
			showMessage(Language::get('admin_predeposit_parameter_error'),'index.php?act=withdraw_commission&op=fx_cash_list','','error');
		}
		$model_com = Model('withdraw_commission');
		$condition = array();
		$condition['id'] = $id;
		$info = $model_com->getcomInfo($condition);
		if (empty($info)){
			showMessage(Language::get('admin_predeposit_record_error'),'index.php?act=withdraw_commission&op=fx_cash_list','','error');
		}
		Tpl::output('info',$info);
		Tpl::showpage('fx_cash_edit');

	}
	/**
	 * 更改提现为审核通过状态
	 */
	public function fx_cash_payOp(){
	    $id = intval($_GET['id']);
	    $fxc_amount = $_GET['fxc_amount'];
	    //var_dump($fxc_amount);die;
	    if ($id <= 0){
	        showMessage(Language::get('admin_predeposit_parameter_error'),'index.php?act=withdraw_commission&op=fx_cash_list','','error');
	    }
	    $model_com = Model('withdraw_commission');
	    $condition = array();
	    $condition['id'] = $id;
	    $condition['fxc_payment_state'] = 1;
	    $condition['fxc_amount'] = $fxc_amount;
	    $info = $model_com->getcomInfo($condition);
	    //var_dump($condition);die;
	    if (!is_array($info) || count($info)<0){
	        showMessage(Language::get('admin_predeposit_record_error'),'index.php?act=withdraw_commission&op=fx_cash_list','','error');
	    }

	    //查询用户信息
	    $model_member = Model('withdraw_commission');
	    // var_dump($info);die;
	    $member_info = $model_member->getWdCashInfo(array('id'=>$info['id']));
  		//var_dump($member_info);die;
        $update = array();
        $admininfo = $this->getAdminInfo();
        $update['fxc_payment_state'] = 2;
        $update['fxc_payment_admin'] = $admininfo['name'];
        $update['fxc_payment_time'] = TIMESTAMP;
        $log_msg = L('admin_predeposit_cash_edit_state').','.L('admin_predeposit_cs_sn').':'.$info['fxc_sn'];
        try {
            $result = $model_com->editcomCash($update,$condition);
            //var_dump($result);die;
            if (!$result) {
                throw new Exception(Language::get('admin_predeposit_cash_edit_fail'));
            }
            //扣除提现金额
		    $data = array();
		    $data['fx_cash_way'] = $member_info['fx_cash_way'];
		    $data['fxc_member_id'] = $member_info['fxc_member_id'];
		    $data['fxc_amount'] =$member_info['fxc_amount'];
		    $model_com->changePd('withdraw_commission',$data);
            showMessage(Language::get('admin_predeposit_cash_edit_success'),'index.php?act=withdraw_commission&op=fx_cash_list');
        } catch (Exception $e) {
            $model_com->rollback();
            $this->log($log_msg,0);
            showMessage($e->getMessage(),'index.php?act=withdraw_commission&op=fx_cash_list','html','error');
        }
	}

		/**
	 * 驳回提现申请
	 */
	public function fx_refuse_cash_payOp(){
	    $id = intval($_GET['id']);
	    if ($id <= 0){
	        showMessage(Language::get('admin_predeposit_parameter_error'),'index.php?act=withdraw_commission&op=fx_cash_list','','error');
	    }
	    $model_com = Model('withdraw_commission');
	    $condition = array();
	    $condition['id'] = $id;
	    $condition['fxc_payment_state'] = 1;
	    $info = $model_com->getcomInfo($condition);
	    // var_dump($condition);die;
	    if (!is_array($info) || count($info)<0){
	        showMessage(Language::get('admin_predeposit_record_error'),'index.php?act=withdraw_commission&op=fx_cash_list','','error');
	    }

	    //查询用户信息
	    $model_member = Model('withdraw_commission');
	    // var_dump($info);die;
	    $member_info = $model_member->getWdCashInfo(array('fxc_member_id'=>$info['fxc_member_id']));
 // var_dump($member_info);die;
        $update = array();
        $admininfo = $this->getAdminInfo();
        $update['fxc_payment_state'] = 3;
        $update['fxc_payment_admin'] = $admininfo['name'];
        $update['fxc_payment_time'] = TIMESTAMP;
        $log_msg = L('admin_predeposit_cash_edit_state').','.L('admin_predeposit_cs_sn').':'.$info['fxc_sn'];
        try {
            $result = $model_com->editcomCash($update,$condition);
            if (!$result) {
                throw new Exception(Language::get('admin_predeposit_cash_edit_fail'));
            }
		    //$model_com->changePd('withdraw_commission',$data);
            showMessage(Language::get('admin_predeposit_cash_edit_success'),'index.php?act=withdraw_commission&op=fx_cash_list');
        } catch (Exception $e) {
            $model_com->rollback();
            $this->log($log_msg,0);
            showMessage($e->getMessage(),'index.php?act=withdraw_commission&op=fx_cash_list','html','error');
        }
	}
}
