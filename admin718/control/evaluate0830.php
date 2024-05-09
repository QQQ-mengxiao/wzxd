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
		if (!empty($_GET['goods_name'])) {
			$condition['geval_goodsname'] = array('like', '%'.$_GET['goods_name'].'%');
		}
		//店铺名称
		if (!empty($_GET['store_name'])) {
			$condition['geval_storename'] = array('like', '%'.$_GET['store_name'].'%');
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

            $excel_data[] = $tmp;
        }

        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(L('exp_od_order'),CHARSET));
        $excel_obj->generateXML($excel_obj->charset(L('exp_od_order'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
    }
}
