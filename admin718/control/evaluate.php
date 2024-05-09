<?php
/**
 * 商品评价
 *
 ** */

defined('In718Shop') or exit('Access Invalid!');
class evaluateControl extends SystemControl{
    const EXPORT_SIZE = 1000;
	public function __construct() {
		parent::__construct();
		Language::read('evaluate');
	}

	public function indexOp() {
		$this->evalgoods_listOp();
	}

	/**
	 * 商品来自买家的评价列表
	 */
	public function evalgoods_listOp() {
		$model_evaluate_goods = Model('evaluate_goods');

		$condition = array();
		//商品名称
//		if (!empty($_GET['goods_name'])) {
//			$condition['geval_goodsname'] = array('like', '%'.$_GET['goods_name'].'%');
//		}
        if (!empty($_GET['from_name'])) {
            $condition['geval_frommembername'] = array('like', '%'.$_GET['from_name'].'%');
        }
        //lxs
		$str=$_GET['goods_name'];
		if(!empty($str)){
            $str = Model('search')->decorateSearch_pre($str);
		    $condition['geval_goodsname'] = array('like','%'.$str.'%');
        }
        //lxs
		//店铺名称
		if (!empty($_GET['store_name'])) {
			$condition['geval_storename'] = array('like', '%'.$_GET['store_name'].'%');
		}
        if($_GET['is_photo']){
			$condition['geval_image'] = array('like', '%http%');
        }
        if($_GET['geval_scores']){
            $condition['geval_scores'] = $_GET['geval_scores'];
        }
        if($_GET['is_voucher'] || $_GET['is_voucher']=='0'){
            $condition['is_voucher'] = $_GET['is_voucher'];
        }
        $condition['geval_addtime'] = array('time', array(strtotime($_GET['stime']), strtotime($_GET['etime'])));
		$evalgoods_list	= $model_evaluate_goods->getEvaluateGoodsList($condition, 10);
        /*echo "<pre>";
        var_dump($evalgoods_list);
        echo "</pre>";
        //die;*/
		Tpl::output('show_page',$model_evaluate_goods->showpage());
		Tpl::output('evalgoods_list',$evalgoods_list);
		Tpl::showpage('evalgoods.index');
	}

	/**
	 * 删除商品评价
	 */
	public function evalgoods_delOp() {
		$geval_id = intval($_POST['geval_id']);
		if ($geval_id <= 0) {
			showMessage(Language::get('param_error'),'','','error');
		}

		$model_evaluate_goods = Model('evaluate_goods');

		$result = $model_evaluate_goods->delEvaluateGoods(array('geval_id'=>$geval_id));

		if ($result) {
            $this->log('删除商品评价，评价编号'.$geval_id);
			showMessage(Language::get('nc_common_del_succ'),'','','error');
		} else {
			showMessage(Language::get('nc_common_del_fail'),'','','error');
		}
	}

	/**
	 * 店铺动态评价列表
	 */
	public function evalstore_listOp() {
        $model_evaluate_store = Model('evaluate_store');

		$condition = array();
		//评价人
		if (!empty($_GET['from_name'])) {
			$condition['seval_membername'] = array('like', '%'.$_GET['from_name'].'%');
		}
		//店铺名称
		if (!empty($_GET['store_name'])) {
			$condition['seval_storename'] = array('like', '%'.$_GET['store_name'].'%');
		}
        $condition['seval_addtime_gt'] = array('time', array(strtotime($_GET['stime']), strtotime($_GET['etime'])));

		$evalstore_list	= $model_evaluate_store->getEvaluateStoreList($condition, 10);
		Tpl::output('show_page',$model_evaluate_store->showpage());
		Tpl::output('evalstore_list',$evalstore_list);
		Tpl::showpage('evalstore.index');
	}

	/**
	 * 删除店铺评价
	 */
	public function evalstore_delOp() {
		$seval_id = intval($_POST['seval_id']);
		if ($seval_id <= 0) {
			showMessage(Language::get('param_error'),'','','error');
		}

		$model_evaluate_store = Model('evaluate_store');

		$result = $model_evaluate_store->delEvaluateStore(array('seval_id'=>$seval_id));

		if ($result) {
            $this->log('删除店铺评价，评价编号'.$geval_id);
			showMessage(Language::get('nc_common_del_succ'),'','','error');
		} else {
			showMessage(Language::get('nc_common_del_fail'),'','','error');
		}
	}
    public function export_step1Op(){
        $lang	= Language::getLangContent();
        $model_order = Model('evaluate_goods');
        $condition	= array();
        if (!empty($_GET['goods_name'])) {
            $condition['geval_goodsname'] = array('like', '%'.$_GET['goods_name'].'%');
        }
        //评价人名称
        if (!empty($_GET['from_name'])) {
            $condition['geval_frommembername'] = array('like', '%'.$_GET['from_name'].'%');
        }
        //是否发劵
        if (!empty($_GET['is_voucher'])) {
            $condition['is_voucher'] = $_GET['is_voucher'];
        }
        //是否带图
        if($_GET['is_photo']){
            $condition['geval_image'] = array('like', '%http%');
        }
        //店铺名称
        if (!empty($_GET['store_name'])) {
            $condition['geval_storename'] = array('like', '%'.$_GET['store_name'].'%');
        }
        $condition['geval_addtime'] = array('time', array(strtotime($_GET['stime']), strtotime($_GET['etime'])));
        if (!is_numeric($_GET['curpage'])){
            $array = array();
            $count = $model_order->getEvaluateCount($condition);  //获取退款的数量
            /*if ($count > self::EXPORT_SIZE ){	//显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=evaluate&op=evalstore_list');
                Tpl::showpage('export.excel');
            }else{*/	//如果数量小，直接下载
                //var_dump($condition);die;
                $data = $model_order->getEvaluateGoodsList1($condition,'geval_id desc','*',$count);
                $this->createExcel($data);
            //}
        }else{	//下载
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_order->getEvaluateGoodsList1($condition,'geval_id desc','*',self::EXPORT_SIZE);
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
        
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品名称');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品评分');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'评价描述');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'晒单图片');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'订单号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'评价人');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'店铺名称');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'是否发劵');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'发劵时间');
        //data
        foreach ((array)$data as $k=>$v){
            $tmp = array();
            $tmp[] = array('data'=>$v['geval_goodsname']);
            $tmp[] = array('data'=>$v['geval_scores']);
            $tmp[] = array('data'=>$v['geval_content']);
            if(!empty($v['geval_image']))
            {
                $tmp[] = array('data'=>'有');
            }else{
                $tmp[] = array('data'=>'无');
            }
            $tmp[] = array('data'=>$v['geval_orderno']);
            $tmp[] = array('data'=>$v['geval_frommembername']);
            $tmp[] = array('data'=>$v['geval_storename']);
            //是否发劵
            if($v['is_voucher'] == 1){
                $tmp[] = array('data'=>'已发');
            }else{
                $tmp[] = array('data'=>'未发');
            }
            //发劵时间
            if(!empty($v['voucher_time'])){
                $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['voucher_time']));
            }else{
                $tmp[] = array('data'=>'');
            }
            $excel_data[] = $tmp;
        }

        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        // $excel_obj->addWorksheet($excel_obj->charset(L('exp_od_order'),CHARSET));
        // $excel_obj->generateXML($excel_obj->charset(L('exp_od_order'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
        $excel_obj->addWorksheet($excel_obj->charset('好评发劵信息表',CHARSET));
        $excel_obj->generateXML($excel_obj->charset('好评发劵信息表',CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
    }

    
    /**
     * ajax发券
     */
    public function ajaxVoucherOp() {

        if (chksubmit()) {

            $geval_id = $_POST['geval_id'];

			if(!$geval_id){
            	showDialog('参数错误', 'reload');
			}
            $geval_id_array = array_unique(explode(',', $geval_id));
            $voucher_t_id = $_POST['voucher_t_id'];

            $order_sn_array = array_column(Model()->table('evaluate_goods')->where(['geval_id'=>['in',$geval_id_array],'is_voucher'=>0])->field('geval_orderno')->select(),'geval_orderno');

            //判断券是否超过总数
            $voucher_t_total = Model()->table('voucher_template')->getfby_voucher_t_id($voucher_t_id,'voucher_t_total');
            if($voucher_t_total<count($geval_id_array)){
                $voucher_t_title = Model()->table('voucher_template')->getfby_voucher_t_id($voucher_t_id,'voucher_t_title');
                showDialog('代金券：'.$voucher_t_title.'剩余'.$voucher_t_total.'张，可发放数量不足', 'reload');
            }
            $member_id_array = array_column(Model()->table('evaluate_goods')->where(['geval_id'=>['in',$geval_id_array],'is_voucher'=>0])->field('geval_frommemberid')->select(),'geval_frommemberid');

            //发放代金券
            $result = Model('voucher')->batch_sendVoucherAdmin($voucher_t_id,$member_id_array,1);

			if($result){
                //更改评论的发券状态
                $this->change_voucher($order_sn_array);
            	showDialog('代金券发放成功', 'reload', 'succ');
			}else{
				showDialog('代金券发放失败', 'reload');
			}
        }

        $condition['voucher_t_start_date'] = array('elt',TIMESTAMP);
        $condition['voucher_t_end_date'] = array('egt',TIMESTAMP);
        $condition['voucher_t_state'] = 1;
        $voucher_list = Model()->table('voucher_template')->where($condition)->field('voucher_t_id,voucher_t_title')->select();

        Tpl::output('geval_id', $_GET['geval_id']);
        Tpl::output('voucher_list', $voucher_list);
        Tpl::showpage('evalgoods.voucher', 'null_layout');
    }

    private function change_voucher($order_sn_array){
        $data['is_voucher']  =  1;
        $data['voucher_time']  =  time();
        Model()->table('evaluate_goods')->where(['geval_orderno'=>['in',$order_sn_array],'is_voucher'=>0])->update(['is_voucher'=>1]);
    }
}
