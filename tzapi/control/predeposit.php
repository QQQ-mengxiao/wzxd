<?php
/**
 * 佣金+提现
 * */


defined('In718Shop') or exit('Access Invalid!');

class predepositControl extends BaseControl {
	public function __construct(){
		Language::read('member_member_predeposit');
	}

	public function get_balanceOp()
	{
		$model_deposit = Model('deposit');
		$deposit_info = $model_deposit->where(array('id'=>1))->field('deposit')->find();
		$deposit = $deposit_info['deposit'];//获取保证金
		$tz_id = intval($_GET['tz_id']);
		$address_id = intval($_GET['address_id']);
		$model_ziti_balance = Model('ziti_balance');
    	$ziti_balance_info = $model_ziti_balance->getZitiBalanceInfo(array('address_id'=>$address_id),'available_predeposit,gl_id');
    	if (empty($ziti_balance_info)) {
    		$ziti_balance_info['available_predeposit'] = '0.00';
    	}else{
    		if ($ziti_balance_info['gl_id'] != $tz_id) {
    			echo $this->returnMsg(101, '用户信息异常', array());
				die; 
    		}
    	}
    	echo $this->returnMsg(100, '查询成功', array('available_predeposit'=>$ziti_balance_info['available_predeposit'],'deposit'=>$deposit));
		die;  
	}

	/**
	 * 充值添加
	 */
	public function recharge_addOp(){
		if (!chksubmit()){
		    //信息输出
		    self::profile_menu('recharge_add','recharge_add');
		    Tpl::showpage('member_pd.add');
		    exit();
		}
		$pdr_amount = abs(floatval($_POST['pdr_amount']));
		if ($pdr_amount <= 0) {
		    showMessage(Language::get('predeposit_recharge_add_pricemin_error'),'','html','error');
		}
        $model_pdr = Model('predeposit');
        $data = array();
        $data['pdr_sn'] = $pay_sn = $model_pdr->makeSn();
        $data['pdr_member_id'] = $_SESSION['member_id'];
        $data['pdr_member_name'] = $_SESSION['member_name'];
        $data['pdr_amount'] = $pdr_amount;
        $data['pdr_add_time'] = TIMESTAMP;
        $insert = $model_pdr->addPdRecharge($data);
        if ($insert) {
            //转向到商城支付页面
            redirect('index.php?act=buy&op=pd_pay&pay_sn='.$pay_sn);
        }
	}

	/**
	 * 佣金列表
	 */
    public function indexOp(){
    	$tz_id = intval($_GET['tz_id']);
    	$address_id = intval($_GET['address_id']);
    	//增加自提点查询
    	$member_id  = $this->getNumberIdByID($tz_id);
        $condition = array();
        $condition['pdr_member_id']	= $member_id;
        $condition['address_id']	= $address_id;
        $model_pd = Model('predeposit');
        $recharge_list = $model_pd->getPdRechargeList($condition,10,'pdr_trade_sn AS order_sn,FROM_UNIXTIME(pdr_add_time) AS pdr_add_time,pdr_amount,pdr_payment_state','pdr_id desc');
        $total_page = $model_pd->gettotalpage();//总页数
		$total_num = $model_pd->gettotalnum();//总条数
		echo $this->returnMsg(100, '查询成功', array('recharge_list'=>$recharge_list,'total_page'=>$total_page,'total_num'=>$total_num));
		die;       
    }

    /**
     * 查看充值详细
     *
     */
    public function recharge_showOp(){
        $pdr_id = intval($_GET["id"]);
        if ($pdr_id <= 0){
            showDialog(Language::get('predeposit_parameter_error'),'','error');
        }

        $model_pd = Model('predeposit');
        $condition = array();
        $condition['pdr_member_id'] = $_SESSION['member_id'];
        $condition['pdr_id'] = $pdr_id;
        $condition['pdr_payment_state'] = 1;
        $info = $model_pd->getPdRechargeInfo($condition);
        if (!$info){
            showDialog(Language::get('predeposit_record_error'),'','error');
        }
        Tpl::output('info',$info);
        self::profile_menu('rechargeinfo','rechargeinfo');
        Tpl::showpage('member_pd.info');
    }

	/**
	 * 删除充值记录
	 *
	 */
	public function recharge_delOp(){
		$pdr_id = intval($_GET["id"]);
		if ($pdr_id <= 0){
		    showDialog(Language::get('predeposit_parameter_error'),'','error');
		}

		$model_pd = Model('predeposit');
		$condition = array();
		$condition['pdr_member_id'] = $_SESSION['member_id'];
		$condition['pdr_id'] = $pdr_id;
		$condition['pdr_payment_state'] = 0;
		$result = $model_pd->delPdRecharge($condition);
		if ($result){
			showDialog(Language::get('nc_common_del_succ'),'reload','succ','CUR_DIALOG.close()');
		}else {
			showDialog(Language::get('nc_common_del_fail'),'','error');
		}
	}
	
	/**
	 * 申请提现
	 */
	public function pd_cash_addOp(){
		$obj_validate = new Validate();
		$pdc_amount = abs(floatval($_GET['pdc_amount']));
		$tz_id = intval($_GET['tz_id']);
		$address_id = intval($_GET['address_id']);
		$leader_info = Model()->table('groupbuy_leader')->field('wx_openid')->where(array('groupbuy_leader_id'=>$tz_id))->find();
		$model_member = Model('member');
		$member_info = $model_member->getMemberInfo(array('groupbuy_leader_id' => $tz_id));
		$member_id = $member_info['member_id'];
		if ($pdc_amount < 0.01){
			echo $this->returnMsg(101, '请输入正确金额',array());
			die;
		}

		$model_ziti_balance = Model('ziti_balance');
    	$ziti_balance_info = $model_ziti_balance->getZitiBalanceInfo(array('address_id'=>$address_id),'available_predeposit,gl_id');
    	if (empty($ziti_balance_info)) {
    		$ziti_balance_info['available_predeposit'] = '0.00';
    	}else{
    		if ($ziti_balance_info['gl_id'] != $tz_id) {
    			echo $this->returnMsg(101, '用户信息异常', array());
				die; 
    		}
    	}

		$model_deposit = Model('deposit');
		$deposit_info = $model_deposit->where(array('id'=>1))->field('deposit')->find();
		$deposit = $deposit_info['deposit'];//获取保证金
		if (floatval($ziti_balance_info['available_predeposit']-$deposit) < $pdc_amount){
			echo $this->returnMsg(102, '余额不足', array());
			die;
		}
		$model_pd = Model('predeposit');
		try {
		    $model_pd->beginTransaction();
		    $pdc_sn = $model_pd->makeSn();
			$data = array();
			$data['pdc_sn'] = $pdc_sn;
			$data['pdc_member_id'] = $member_id;
			$data['pdc_member_name'] = $member_info['member_name'];
			$data['pdc_bank_name'] = 'wx_change';
			$data['pdc_bank_no'] = $leader_info['wx_openid'];
			$data['pdc_amount'] = $pdc_amount;
			$data['pdc_add_time'] = TIMESTAMP;
			$data['pdc_payment_state'] = 0;
			$data['address_id'] = $address_id;
			$insert = $model_pd->addPdCash($data);
			if (!$insert) {
			    echo $this->returnMsg(103, '系统繁忙',array());
			    die;
			}
			//冻结可用预存款
			$data = array();
			$data['member_id'] = $member_info['member_id'];
			$data['member_name'] = $member_info['member_name'];
			$data['amount'] = $pdc_amount;
			$data['order_sn'] = $pdc_sn;
			$data['address_id'] = $address_id;
			$data['gl_id'] = $tz_id;
			$model_pd->changePd('cash_apply',$data);
			$model_pd->commit();
		} catch (Exception $e) {
		    $model_pd->rollback();
		    echo $this->returnMsg(103, '系统繁忙', array());
			die;
		}
		echo $this->returnMsg(100, '请求成功', array('pdc_sn'=>$pdc_sn,'pdc_amount'=>$pdc_amount));
		die;
	}

	/**
	 * 提现列表
	 */
	public function pd_cash_listOp(){
		$tz_id = intval($_GET['tz_id']);
		$address_id = intval($_GET['address_id']);
		$member_id  = $this->getNumberIdByID($tz_id);
		$condition = array();
		$condition['pdc_member_id'] = $member_id;
		$condition['address_id'] = $address_id;
		$model_pd = Model('predeposit');
		$cash_list = $model_pd->getPdCashList($condition,10,'pdc_sn,pdc_amount,FROM_UNIXTIME(pdc_add_time) AS pdc_add_time,pdc_payment_state','pdc_id desc');
		$total_page = $model_pd->gettotalpage();//总页数
		$total_num = $model_pd->gettotalnum();//总条数
		echo $this->returnMsg(100, '查询成功', array('cash_list'=>$cash_list,'total_page'=>$total_page,'total_num'=>$total_num));
	}

	/**
	 * 提现记录详细
	 */
	public function pd_cash_infoOp(){
		$pdc_id = intval($_GET["id"]);
		if ($pdc_id <= 0){
			showMessage(Language::get('predeposit_parameter_error'),'index.php?act=predeposit&op=pd_cash_list','html','error');
		}
		$model_pd = Model('predeposit');
		$condition = array();
		$condition['pdc_member_id'] = $_SESSION['member_id'];
		$condition['pdc_id'] = $pdc_id;
		$info = $model_pd->getPdCashInfo($condition);
		if (empty($info)){
			showMessage(Language::get('predeposit_record_error'),'index.php?act=predeposit&op=pd_cash_list','html','error');
		}

		self::profile_menu('cashinfo','cashinfo');
		Tpl::output('info',$info);
		Tpl::showpage('member_pd_cash.info');
	}

	private function getNumberIdByID($tz_id)
	{
		$model_member = Model('member');
		$member_info = $model_member->getMemberInfo(array('groupbuy_leader_id' => $tz_id));
		return $member_info['member_id'];
	}

	private function getAddressId($tz_id)
	{
		$model_ziti_address = Model('ziti_address');
		$condition = array();
		$condition['gl_id'] = $tz_id;
		$condition['is_current'] = 1;
		$ziti_address_info = $model_ziti_address->field('address_id')->where($condition)->find();
		return $ziti_address_info['address_id'];
	}

}