<?php
/**
 * 退款管理
 **/

defined('In718Shop') or exit('Access Invalid!');
class signingControl extends SystemControl{
	const EXPORT_SIZE = 1000;
	public function __construct(){
		parent::__construct();
		$model_signing = Model('signing');
	}


	/**
	 * 所有记录
	 */
	public function signing_allOp() {
		$model_signing = Model('signing');
		$condition = array();

		$keyword_type = array('buyer_name','goods_name');
        $str=$_GET['key'];
        // var_dump($str);die;
        if(trim($str) != '' && in_array($_GET['type'],$keyword_type)) {
            $type=$_GET['type'];
            if($type=='buyer_name'){
	             $model_member= Model('member');
	        	 $array=$model_member->where(array('member_name'=>$str))->select();
	        	 $condition['user_id']=$array[0]['member_id'];
            }else{
	            $str = Model('search')->decorateSearch_pre($str);
	            $condition[$type] = array('like', '%'.$str.'%');
            }
        }

		if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != '') {
			$add_time_from = strtotime(trim($_GET['add_time_from']));
			$add_time_to = strtotime(trim($_GET['add_time_to']));
			if ($add_time_from !== false || $add_time_to !== false) {
				$condition['purchase_time'] = array('time',array($add_time_from,$add_time_to));
			}
		}
		$signing_list = $model_signing->getSigningList($condition,10);
        foreach ($signing_list as $k => $v) {
        	$model_address = Model('daddress');
            $address=$model_address->getby_address_id($v['address_id']);
            $signing_list[$k]['area_info']=$address['area_info'];
            $signing_list[$k]['telphone']=$address['telphone'];
            $signing_list[$k]['seller_name']=$address['seller_name'];
            $model_member = Model('member');
            $member=$model_member->getby_member_id($v['user_id']);
            $signing_list[$k]['member_name']=$member['member_name'];
            $model_store = Model('store');
            $store=$model_store->getby_store_id($v['store_id']);
            $signing_list[$k]['store_name']=$store['store_name'];
        }
        // var_dump($signing_list);die;
		Tpl::output('signing_list',$signing_list);
		Tpl::output('show_page',$model_signing->showpage());
		Tpl::showpage('signing_all.list');
	}

	/**
	 * 退款记录查看页
	 *
	 */
	public function viewOp() {
		$model_signing = Model('signing');
		$condition = array();
		$condition['signing_id'] = intval($_GET['signing_id']);
		$signing_list = $model_signing->getsigningList($condition);
		$signing = $signing_list[0];
		Tpl::output('signing',$signing);
		$info['buyer'] = array();
	    if(!empty($signing['pic_info'])) {
	        $info = unserialize($signing['pic_info']);
	    }
		Tpl::output('pic_list',$info['buyer']);
		Tpl::showpage('signing.view');
	}


	

	/**
	 * 导出
	 *
	 */
	public function export_step1Op(){
		$lang	= Language::getLangContent();
		$model_signing = Model('signing');
		$condition	= array();
		$keyword_type = array('buyer_name','goods_name');
        $str=$_GET['key'];
        if(trim($str) != '' && in_array($_GET['type'],$keyword_type)) {
            $type=$_GET['type'];
            if($type=='buyer_name'){
	             $model_member= Model('member');
	        	 $array=$model_member->where(array('member_name'=>$str))->select();
	        	 $condition['user_id']=$array[0]['member_id'];
            }else{
	            $str = Model('search')->decorateSearch_pre($str);
	            $condition[$type] = array('like', '%'.$str.'%');
            }
        }
        if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != '') {
            $add_time_from = strtotime(trim($_GET['add_time_from']));
            $add_time_to = strtotime(trim($_GET['add_time_to']));
            if ($add_time_from !== false || $add_time_to !== false) {
                $condition['purchase_time'] = array('time',array($add_time_from,$add_time_to));
            }
        }
		if (!is_numeric($_GET['curpage'])){
			$count = $model_signing->getSigningCount($condition);  //获取退款的数量
			$array = array();
			$data = $model_signing->getSigningList($condition,'','*',$count);
			$this->createExcel($data);
		}else{	//下载
			$limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
			$limit2 = self::EXPORT_SIZE;
			$data = $model_signing->getSigningList($condition,'','*','id desc',"{$limit1},{$limit2}");
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
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'编号');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'签约人');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'店铺名称');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'采购商品');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'申请时间');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'采购单位');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'采购数量');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'意向价格');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'供应商');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'供应商电话');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'供应商地址');
		//data
		 foreach ($data as $k => $v) {
        	$model_address = Model('daddress');
            $address=$model_address->getby_address_id($v['address_id']);
            $data[$k]['area_info']=$address['area_info'];
            $data[$k]['telphone']=$address['telphone'];
            $data[$k]['seller_name']=$address['seller_name'];
            $model_member = Model('member');
            $member=$model_member->getby_member_id($v['user_id']);
            $data[$k]['member_name']=$member['member_name'];
            $model_store = Model('store');
            $store=$model_store->getby_store_id($v['store_id']);
            $data[$k]['store_name']=$store['store_name'];
        }
      
		foreach ((array)$data as $k=>$v){
			$tmp = array();
			$tmp[] = array('data'=>$v['id']);
			$tmp[] = array('data'=>$v['member_name']);
			$tmp[] = array('data'=>$v['store_name']);
			$tmp[] = array('data'=>$v['goods_name']);
			$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['purchase_time']));
			$tmp[] = array('data'=>$v['purchase_unit']);
			$tmp[] = array('data'=>$v['purchase_quantity']);
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['purchase_price']));
			$tmp[] = array('data'=>$v['seller_name']);
			$tmp[] = array('data'=>$v['telphone']);
			$tmp[] = array('data'=>$v['area_info']);
			$excel_data[] = $tmp;
		}
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset('签约信息',CHARSET));
		$excel_obj->generateXML($excel_obj->charset('签约信息',CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
	}
}
