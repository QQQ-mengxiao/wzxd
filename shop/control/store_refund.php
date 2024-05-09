<?php
/**
 * 卖家退款
 *
 *
 **/


defined('In718Shop') or exit('Access Invalid!');

class store_refundControl extends BaseSellerControl {
	public function __construct() {
		parent::__construct();
		$model_refund = Model('refund_return');
		$model_refund->getRefundStateArray();
        Language::read('member_store_index');
	}
	/**
	 * 退款记录列表页
	 *
	 */
	public function indexOp() {
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['store_id'] = $_SESSION['store_id'];

		$keyword_type = array('order_sn','refund_sn','buyer_name');
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
		if (trim($_GET['admin_time_from']) != '' || trim($_GET['admin_time_to']) != '') {
			$admin_time_from = strtotime(trim($_GET['admin_time_from']));
			$admin_time_to = strtotime(trim($_GET['admin_time_to']));
			if ($admin_time_from !== false || $admin_time_to !== false) {
				$condition['admin_time'] = array('time',array($admin_time_from,$admin_time_to));
			}
		}
		$seller_state = intval($_GET['state']);
		if ($seller_state > 0) {
		    $condition['seller_state'] = $seller_state;
		}
		$order_lock = intval($_GET['lock']);
		if ($order_lock != 1) {
		    $order_lock = 2;
		}
		$_GET['lock'] = $order_lock;
		$condition['order_lock'] = $order_lock;

		$refund_list = $model_refund->getRefundList($condition,10);
		if($refund_list){
			foreach($refund_list as $key=>$value){
				$refund_list[$key]['order_add_time'] = Model()->table('order')->getfby_order_id($value['order_id'],'add_time');
			}
		}
		Tpl::output('refund_list',$refund_list);
		Tpl::output('show_page',$model_refund->showpage());
		self::profile_menu('refund',$order_lock);
		Tpl::showpage('store_refund');
	}
	/**
	 * 退款审核页
	 *
	 */
	public function editOp() {
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['store_id'] = $_SESSION['store_id'];
		$condition['refund_id'] = intval($_GET['refund_id']);
		$refund_list = $model_refund->getRefundList($condition);
		$refund = $refund_list[0];
		$model_order = Model('order');
		$order_in = $model_order->table('order')->getfby_order_id($refund['order_id'],'	jin_time');
		$order_type = $model_order->table('order')->getfby_order_id($refund['order_id'],'		type');
		$jin_time = date("Y-m-d H:i:s",$order_in);
		Tpl::output('jin_time',$jin_time);
		Tpl::output('order_type',$order_type);
		if (chksubmit()) {
			$reload = 'index.php?act=store_refund&lock=1';
			if ($refund['order_lock'] == '2') {
			    $reload = 'index.php?act=store_refund&lock=2';
			}
			if ($refund['seller_state'] != '1') {//检查状态,防止页面刷新不及时造成数据错误
				showDialog(Language::get('wrong_argument'),$reload,'error');
			}
			$order_id = $refund['order_id'];
			$refund_array = array();
			$refund_array['seller_time'] = time();
			$refund_array['seller_state'] = $_POST['seller_state'];//卖家处理状态:1为待审核,2为同意,3为不同意
			$refund_array['seller_message'] = $_POST['seller_message'];
			if ($refund_array['seller_state'] == '3') {
			    $refund_array['refund_state'] = '3';//状态:1为处理中,2为待管理员处理,3为已完成
			     $buyer_info = Model('member')->getMemberInfo(array('member_id' => $refund['buyer_id']),'member_wxopenid');
                $refund['buyer_message']='拒绝退款'.$_POST['seller_message'];
                $output = Model('wxsend')->sendMessage($buyer_info['member_wxopenid'],$refund);
			} else {
			    $refund_array['seller_state'] = '2';
			    $refund_array['refund_state'] = '2';
			}
			$state = $model_refund->editRefundReturn($condition, $refund_array);
			if ($state) {
    			if ($refund_array['seller_state'] == '3' && $refund['order_lock'] == '2') {
    			    $model_refund->editOrderUnlock($order_id);//订单解锁
    			}
    			$this->recordSellerLog('退款处理，退款编号：'.$refund['refund_sn']);

    			// 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $refund['buyer_id'];
                $param['param'] = array(
                    'refund_url'=> urlShop('member_refund', 'view', array('refund_id' => $refund['refund_id'])),
                    'refund_sn' => $refund['refund_sn']
                );
                QueueClient::push('sendMemberMsg', $param);

				showDialog(Language::get('nc_common_save_succ'),$reload,'succ');
			} else {
				showDialog(Language::get('nc_common_save_fail'),$reload,'error');
			}
		}
		Tpl::output('refund',$refund);
		$info['buyer'] = array();
	    if(!empty($refund['pic_info'])) {
	        $info = unserialize($refund['pic_info']);
	    }
		Tpl::output('pic_list',$info['buyer']);
		$model_member = Model('member');
		$member = $model_member->getMemberInfoByID($refund['buyer_id']);
		Tpl::output('member',$member);
		$condition = array();
		$condition['order_id'] = $refund['order_id'];
		$model_refund->getRightOrderList($condition, $refund['order_goods_id']);
		Tpl::showpage('store_refund_edit');
	}
	/**
	 * 退款记录查看页
	 *
	 */
	public function viewOp() {
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['store_id'] = $_SESSION['store_id'];
		$condition['refund_id'] = intval($_GET['refund_id']);
		$refund_list = $model_refund->getRefundList($condition);
		$refund = $refund_list[0];
		$model_order = Model('order');
		$order_in = $model_order->table('order')->getfby_order_id($refund['order_id'],'	jin_time');
		$order_type = $model_order->table('order')->getfby_order_id($refund['order_id'],'		type');
		$jin_time = date("Y-m-d H:i:s",$order_in);
		Tpl::output('jin_time',$jin_time);
		Tpl::output('order_type',$order_type);
		Tpl::output('refund',$refund);
		$info['buyer'] = array();
	    if(!empty($refund['pic_info'])) {
	        $info = unserialize($refund['pic_info']);
	    }
		Tpl::output('pic_list',$info['buyer']);
		$model_member = Model('member');
		$member = $model_member->getMemberInfoByID($refund['buyer_id']);
		Tpl::output('member',$member);
		$condition = array();
		$condition['order_id'] = $refund['order_id'];
		$model_refund->getRightOrderList($condition, $refund['order_goods_id']);
		Tpl::showpage('store_refund_view');
	}
	/**
	 * 用户中心右边，小导航
	 *
	 * @param string	$menu_type	导航类型
	 * @param string 	$menu_key	当前导航的menu_key
	 * @return
	 */
	private function profile_menu($menu_type,$menu_key='') {
		$menu_array = array();
		switch ($menu_type) {
			case 'refund':
				$menu_array = array(
					array('menu_key'=>'2','menu_name'=>'售前退款',	'menu_url'=>'index.php?act=store_refund&lock=2'),
					array('menu_key'=>'1','menu_name'=>'售后退款','menu_url'=>'index.php?act=store_refund&lock=1')
				);
				break;
		}
		Tpl::output('member_menu',$menu_array);
		Tpl::output('menu_key',$menu_key);
	}
    public function export_refundOp(){
        $lang   = Language::getLangContent();
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];

        $keyword_type = array('order_sn','refund_sn','buyer_name');
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
        if (trim($_GET['admin_time_from']) != '' || trim($_GET['admin_time_to']) != '') {
			$admin_time_from = strtotime(trim($_GET['admin_time_from']));
			$admin_time_to = strtotime(trim($_GET['admin_time_to']));
			if ($admin_time_from !== false || $admin_time_to !== false) {
				$condition['admin_time'] = array('time',array($admin_time_from,$admin_time_to));
			}
		}
        $seller_state = intval($_GET['state']);
        if ($seller_state > 0) {
            $condition['seller_state'] = $seller_state;
        }
        $order_lock = intval($_GET['lock']);
        if ($order_lock != 1) {
            $order_lock = 2;
        }
        $_GET['lock'] = $order_lock;
        $condition['order_lock'] = $order_lock;

        if (!is_numeric($_GET['curpage'])){
            $count = $model_refund->getRefundReturn($condition);
            $array = array();
           /* echo "<pre>";
            var_dump($count);
            echo "</pre>";
            die;*/

           /* if ($count > self::EXPORT_SIZE ){   //显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=store_return&op=index');
                Tpl::showpage('export.excel');
            }else{ */ //如果数量小，直接下载
                $data = $model_refund->getRefundList($condition,$count);
                $this->createExcel($data);
          //  }
        }else{  //下载
            $count = $model_refund->getRefundReturn($condition);
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_refund->getRefundList($condition,$count);
            $this->createExcel($data);
        }
    }
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
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单编号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'退款编号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'退款金额');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'支付方式');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'买家会员名');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'申请时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'退款完成时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商家处理状态');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'平台确认');//xinzeng
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商家意见');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'管理员意见');

        //data
       /* echo "<pre>";
        var_dump((array)$data);
        echo "</pre>";
        die;*/
        foreach ((array)$data as $k=>$v){
            $tmp = array();
            $tmp[] = array('data'=>$v['goods_name']);
            $tmp[] = array('data'=>$v['order_sn']);
            $tmp[] = array('data'=>$v['refund_sn']);
            $tmp[] = array('data'=>$v['refund_amount']);
             $payment_code= Model('order')->getfby_order_id($v['order_id'], 'payment_code');
            if($payment_code=='zihpay'){
                $tmp[] = array('data'=>'zihpay');
            }else if($payment_code=='online'){
            	 $tmp[] = array('data'=>'wxpay');
            }else{
                 $tmp[] = array('data'=>'小店余额');
            }
            $tmp[] = array('data'=>$v['buyer_name']);
             $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['add_time']));
			// $tmp[] = array('data'=>date('Y-m-d H:i:s',$add_time));
			if($v['admin_time']>0){
             $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['admin_time']));
			}else{
                $tmp[] = array('data'=>'无');
			}
            if($v['seller_state']=='1'){
                $tmp[] = array('data'=>'待审核');
            }else if($v['seller_state']=='2'){
                $tmp[] = array('data'=>'同意');
            }else if($v['seller_state']=='3'){
                $tmp[] = array('data'=>'不同意');
            }else{
                $tmp[] = array('data'=>'');
            }
            if($v['seller_state']=='2'){
                if($v['refund_state']=='1'){
                    $tmp[] = array('data'=>'处理中');
                }else if($v['refund_state']=='2'){
                    $tmp[] = array('data'=>'待管理员处理');
                }else if($v['refund_state']=='3'){
                    $tmp[] = array('data'=>'已完成');
                }else{
                    $tmp[] = array('data'=>'无');
                }
            }else{
                $tmp[] = array('data'=>'无');
            }
            $tmp[] = array('data'=>$v['seller_message']);
            $tmp[] = array('data'=>$v['admin_message']);


            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset('订单',CHARSET));
        $excel_obj->generateXML($excel_obj->charset('订单',CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
    }
}
