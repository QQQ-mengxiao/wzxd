<?php
/**
 * 退货管理
 **/

defined('In718Shop') or exit('Access Invalid!');
class returnControl extends SystemControl{
	const EXPORT_SIZE = 1000;
	public function __construct(){
		parent::__construct();
		$model_refund = Model('refund_return');
		$model_refund->getRefundStateArray();
	}

	/**
	 * 待处理列表
	 */
	public function return_manageOp() {
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['refund_state'] = '2';//状态:1为处理中,2为待管理员处理,3为已完成

		$keyword_type = array('order_sn','refund_sn','store_name','buyer_name','goods_name');
		if (trim($_GET['key']) != '' && in_array($_GET['type'],$keyword_type)) {
			$type = $_GET['type'];
			$condition[$type] = array('like','%'.$_GET['key'].'%');
		}
		if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != '') {
			$add_time_from = strtotime(trim($_GET['add_time_from']));
			$add_time_to = strtotime(trim($_GET['add_time_to']));
			if ($add_time_from !== false || $add_time_to !== false) {
				$condition['add_time'] = array('time',array($add_time_from,$add_time_to));
			}
		}
		$return_list = $model_refund->getReturnList($condition,10);

		Tpl::output('return_list',$return_list);
		Tpl::output('show_page',$model_refund->showpage());
		Tpl::showpage('return_manage.list');
	}

	/**
	 * 所有记录
	 */
	public function return_allOp() {
		$model_refund = Model('refund_return');
		$condition = array();

		$keyword_type = array('order_sn','refund_sn','store_name','buyer_name','goods_name');
		if (trim($_GET['key']) != '' && in_array($_GET['type'],$keyword_type)) {
			$type = $_GET['type'];
			$condition[$type] = array('like','%'.$_GET['key'].'%');
		}
		if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != '') {
			$add_time_from = strtotime(trim($_GET['add_time_from']));
			$add_time_to = strtotime(trim($_GET['add_time_to']));
			if ($add_time_from !== false || $add_time_to !== false) {
				$condition['add_time'] = array('time',array($add_time_from,$add_time_to));
			}
		}
		$return_list = $model_refund->getReturnList($condition,10);
		Tpl::output('return_list',$return_list);
		Tpl::output('show_page',$model_refund->showpage());
		Tpl::showpage('return_all.list');
	}

	/**
	 * 退货处理页
	 *
	 */
	public function editOp() {
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['refund_id'] = intval($_GET['return_id']);

		//jinp 0801 --S--
		$model_pd = Model('predeposit');
		$model_member = Model('member');

		$model_order = Model('order');

		//$member_info = $model_member->getMemberInfoByID($_GET['refund_id']);
		//jinp 0801 --E--

		$return_list = $model_refund->getReturnList($condition);
		$return = $return_list[0];
        $refund_id=$return['refund_id'];
		//获取一卡通卡号
        $member_id=$return['buyer_id'];
        $cardno=Model()->table('member_card')->where(array('member_id'=>$member_id))->find();
        $return['cardno']=$cardno['cardno'];
        //获取管理员信息
        $admin=$this->getAdminInfo();
        $con['admin_id']=$admin['id'];
        $admin_info=Model('admin')->infoAdmin($con);
        $return['admin_name']=$admin_info['admin_name'];
		if (chksubmit()) {
			if ($return['refund_state'] != '2') {//检查状态,防止页面刷新不及时造成数据错误
				showMessage(Language::get('nc_common_save_fail'));
			}
			$order_id = $return['order_id'];
			$refund_array = array();
			$refund_array['admin_time'] = time();
			$refund_array['refund_state'] = '3';//状态:1为处理中,2为待管理员处理,3为已完成
			$refund_array['admin_message'] = $_POST['admin_message'];
//			$state = $model_refund->editOrderRefund($return);
			 $state = $model_refund->editOrderRefund_jp($return);
			if ($state) {
			    $model_refund->editRefundReturn($condition, $refund_array);

			     //jinp 0808 --S--
			    $member_info = $model_member->getMemberInfoByID($return['buyer_id']);

//			    $order_info = $model_order->getOrderInfo(array('order_id'=> $return['order_id']));
			    
			    
//			    $pdc_sn = $model_pd->makeSn();
//    			$data = array();
//    			$data['pdc_sn'] = $pdc_sn;
//	    		$data['pdc_member_id'] = $member_info['member_id'];
//	    		$data['pdc_member_name'] = $member_info['member_name'];
//	    		$data['pdc_amount'] = $return['refund_amount'] ;
//	    		$data['pdc_bank_name'] = "支付宝/微信";
//	    		$data['pdc_bank_no'] = $return['order_sn'];
//	    		$data['pdc_bank_user'] = $member_info['member_name'];
//	    		$data['pdc_add_time'] = TIMESTAMP;
//	    		$data['pdc_payment_state'] = 0;
//	    		$data['order_sn'] = $return['order_sn'];
//	    		$data['pay_sn'] = $order_info['pay_sn'];
//                $data['refund_id'] = $refund_id;
//
//                $insert = $model_pd->addPdCash($data);

	    		//jinp 0808 --E--
//            }
//            if($state){
//			    $this->log('退货确认，退货编号'.$return['refund_sn']);

			    // 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $return['buyer_id'];
                $param['param'] = array(
                    'refund_url' => urlShop('return_id', 'view', array('return_id' => $return['refund_id'])),
                    'refund_sn' => $return['refund_sn']
                );
                QueueClient::push('sendMemberMsg', $param);
                $this->log('退货确认，退货编号'.$return['refund_sn']);

				showMessage(Language::get('nc_common_save_succ'),'index.php?act=return&op=return_manage');
			} else {
				showMessage(Language::get('nc_common_save_fail'));
			}
		}
		Tpl::output('return',$return);
		Tpl::output('member_info',$member_info);
		$info['buyer'] = array();
	    if(!empty($return['pic_info'])) {
	        $info = unserialize($return['pic_info']);
	    }
		Tpl::output('pic_list',$info['buyer']);
		Tpl::showpage('return.edit');
	}

	/**
	 * 退货记录查看页
	 *
	 */
	public function viewOp() {
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['refund_id'] = intval($_GET['return_id']);
		$return_list = $model_refund->getReturnList($condition);
		$return = $return_list[0];
		Tpl::output('return',$return);
		$info['buyer'] = array();
	    if(!empty($return['pic_info'])) {
	        $info = unserialize($return['pic_info']);
	    }
		Tpl::output('pic_list',$info['buyer']);
		Tpl::showpage('return.view');
	}

	/**
	 * 导出
	 *
	 */
	public function export_step1Op(){
		$lang	= Language::getLangContent();
		$model_order = Model('refund_return');
		$condition	= array();
		$condition['refund_type']='2';//1为退款，2为退货
        $condition = array();

        $keyword_type = array('order_sn','refund_sn','store_name','buyer_name','goods_name');
        if (trim($_GET['key']) != '' && in_array($_GET['type'],$keyword_type)) {
            $type = $_GET['type'];
            $condition[$type] = array('like','%'.$_GET['key'].'%');
        }
        if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != '') {
            $add_time_from = strtotime(trim($_GET['add_time_from']));
            $add_time_to = strtotime(trim($_GET['add_time_to']));
            if ($add_time_from !== false || $add_time_to !== false) {
                $condition['add_time'] = array('time',array($add_time_from,$add_time_to));
            }
        }
		if (!is_numeric($_GET['curpage'])){
			$count = $model_order->getReturnCount($condition);  //获取退款的数量
			$array = array();
			if ($count > self::EXPORT_SIZE ){	//显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}
				Tpl::output('list',$array);
				Tpl::output('murl','index.php?act=refurn&op=refund_all');
				Tpl::showpage('export.excel');
			}else{	//如果数量小，直接下载
				$data = $model_order->getReturnList($condition,'','*','order_sn desc',self::EXPORT_SIZE);
				$this->createExcel($data);
			}
		}else{	//下载
			$limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
			$limit2 = self::EXPORT_SIZE;
			$data = $model_order->getReturnList($condition,'','*','order_sn desc',"{$limit1},{$limit2}");
			$this->createExcel($data);
		}
	}

	/**
	 * 生成excel
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
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_ordersn'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_refundsn'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_store_name'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('return_goods_name'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_buyer'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_add_time'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('refund_order_refund'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'退货数量');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'商家审核');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'平台确认');
		//data
		foreach ((array)$data as $k=>$v){
			$tmp = array();
			$tmp[] = array('data'=>'NC'.$v['order_sn']);
			$tmp[] = array('data'=>'NC'.$v['refund_sn']);
			$tmp[] = array('data'=>$v['store_name']);
			$tmp[] = array('data'=>$v['goods_name']);
			$tmp[] = array('data'=>$v['buyer_name']);
			$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['add_time']));
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['refund_amount']));
			$tmp[] = array('data'=>$v['goods_num']);
			//商家审核状态
			if($v['seller_state']=='1')
			{
				$tmp[] = array('data'=>L('refund_state_confirm'));
			}
			if($v['seller_state']=='2')
			{
				$tmp[] = array('data'=>L('refund_state_yes'));
			}
			if ($v['seller_state']=='3')
			{
				$tmp[] = array('data'=>L('refund_state_no'));
			}
			//平台确认
			if($v['seller_state']=='2')
			{
				if($v['refund_state']=='1')
				{
					$tmp[] = array('data'=>'处理中');
				}
				if ($v['refund_state']=='2')
				{
					$tmp[] = array('data'=>'待处理');
				}
				if ($v['refund_state']=='3')
				{
					$tmp[] = array('data'=>'已完成');
				}
			}
			else
			{
				$tmp[] = array('data'=>'无');
			}
			//$tmp[] = array('data'=>$v['refund_state']);
			$excel_data[] = $tmp;
		}
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset(L('refund_add'),CHARSET));
		$excel_obj->generateXML($excel_obj->charset(L('refund_add'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
	}
}
