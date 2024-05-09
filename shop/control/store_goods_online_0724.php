<?php
/**
 * 商品管理
 *
 **/
// require_once '/home/wwwroot/default/wzxd/PHPExcel/Classes/PHPExcel.php'; //引入文件
// require_once '/home/wwwroot/default/wzxd/PHPExcel/Classes/PHPExcel/IOFactory.php'; //引入文件
// require_once '/home/wwwroot/default/wzxd/PHPExcel/Classes/PHPExcel/Reader/Excel2007.php'; //引入文件
// require_once '/home/wwwroot/default/wzxd/PHPExcel/Classes/PHPExcel/Reader/IReader.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/IOFactory.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/Reader/Excel2007.php'; //引入文件
require_once BASE_ROOT_PATH.'/PHPExcel/Classes/PHPExcel/Reader/IReader.php'; //引入文件

defined('In718Shop') or exit ('Access Invalid!');
class store_goods_onlineControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct ();
        Language::read ('member_store_goods_index');
    }
    public function indexOp() {
        $this->goods_listOp();
    }

    /**
     * 出售中的商品列表
     */
    public function goods_listOp() {
        $model_goods = Model('goods');
        $model_p_ladder = Model('p_ladder');

        $where = array();
        $where['store_id'] = $_SESSION['store_id'];
        if (intval($_GET['stc_id']) > 0) {
            $where['goods_stcids'] = array('like', '%,' . intval($_GET['stc_id']) . ',%');
        }
         //价格区间
        if ($_GET['d_price']!=''  || $_GET['h_price']!='') {
            $d_price = intval($_GET['d_price']);
            $h_price = intval($_GET['h_price']);
            $where['goods_price'] = array('between',array($d_price,$h_price));
        }
        //关联发货人
        if($_GET['daddress_id']>0){
            $where['deliverer_id'] = $_GET['daddress_id'];
        }
        //lxs
       $a=$_GET['keyword'];
       $str=trim($a);
       if($str!=''){
           $str = Model('search')->decorateSearch_pre($str);//调用model里的search.model的搜索代码
           switch ($_GET['search_type']){
               case 0:
                   $where['goods_name']=array('like','%'.$str.'%');
                   break;
               case 1:
                   $where['goods_serial']=array('like','%'.$str.'%');
                   break;
               case 2:
                   $where['goods_commonid']=intval($a);
                   break;
           }
       }
        // if (trim($_GET['keyword']) != '') {
        //     switch ($_GET['search_type']) {
        //         case 0:
        //             $where['goods_name'] = array('like', '%' . trim($_GET['keyword']) . '%');
        //             break;
        //         case 1:
        //             $where['goods_serial'] = array('like', '%' . trim($_GET['keyword']) . '%');
        //             break;
        //         case 2:
        //             $where['goods_commonid'] = intval($_GET['keyword']);
        //             break;
        //     }
        // }
        $goods_list = $model_goods->getGoodsCommonOnlineList($where);
       if(is_array($goods_list)){
           foreach($goods_list as $k=>$v){
               $goods_list[$k]['is_cw'] = Model()->table('goods')->getfby_goods_commonid($v['goods_commonid'],'is_cw');
                $p_ladder_list = $model_goods->getGoodsList(array('goods_commonid'=>$v['goods_commonid']),'p_ladder_id');
                $p_ladder_name = $model_p_ladder->getMansongList(array('p_ladder_id'=>array('in',array_column($p_ladder_list,'p_ladder_id'))),'','','p_name');
                $goods_list[$k]['p_ladder_list'] = array_column($p_ladder_name,'p_name');
           }
       }
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::output('goods_list', $goods_list);

        // 计算库存
        $storage_array = $model_goods->calculateStorage($goods_list);
        Tpl::output('storage_array', $storage_array);

        // 商品分类
        $store_goods_class = Model('store_goods_class')->getClassTree(array('store_id' => $_SESSION['store_id'], 'stc_state' => '1'));
        Tpl::output('store_goods_class', $store_goods_class);

        //关联发货人
        $daddress_list = Model('daddress')->getAddressList(array('store_id' => $_SESSION['store_id']),'address_id,seller_name');
        Tpl::output('daddress_list', $daddress_list);

        $this->profile_menu('goods_list', 'goods_list');
        Tpl::showpage('store_goods_list.online');
    }
    /**
     * 库存为0 
     */
    public function goods_list1Op() {
        $model_goods = Model('goods');

        $where = array();
        $where['store_id'] = $_SESSION['store_id'];
        if (intval($_GET['stc_id']) > 0) {
            $where['goods_stcids'] = array('like', '%,' . intval($_GET['stc_id']) . ',%');
        }
         //价格区间
        if ($_GET['d_price']!=''  || $_GET['h_price']!='') {
            $d_price = intval($_GET['d_price']);
            $h_price = intval($_GET['h_price']);
            $where['goods_price'] = array('between',array($d_price,$h_price));
        }
        //lxs
       $a=$_GET['keyword'];
       $str=trim($a);
       if($str!=''){
           $str = Model('search')->decorateSearch_pre($str);//调用model里的search.model的搜索代码
           switch ($_GET['search_type']){
               case 0:
                   $where['goods_name']=array('like','%'.$str.'%');
                   break;
               case 1:
                   $where['goods_serial']=array('like','%'.$str.'%');
                   break;
               case 2:
                   $where['goods_commonid']=intval($a);
                   break;
           }
       }
        // if (trim($_GET['keyword']) != '') {
        //     switch ($_GET['search_type']) {
        //         case 0:
        //             $where['goods_name'] = array('like', '%' . trim($_GET['keyword']) . '%');
        //             break;
        //         case 1:
        //             $where['goods_serial'] = array('like', '%' . trim($_GET['keyword']) . '%');
        //             break;
        //         case 2:
        //             $where['goods_commonid'] = intval($_GET['keyword']);
        //             break;
        //     }
        // }
        $goods_list = $model_goods->getGoodsCommonOnlineList3($where);
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::output('goods_list', $goods_list);

        // 计算库存
        $storage_array = $model_goods->calculateStorage($goods_list);
        Tpl::output('storage_array', $storage_array);

        // 商品分类
        $store_goods_class = Model('store_goods_class')->getClassTree(array('store_id' => $_SESSION['store_id'], 'stc_state' => '1'));
        Tpl::output('store_goods_class', $store_goods_class);

        $this->profile_menu('goods_list', 'goods_list');
        Tpl::showpage('store_goods_list.online');
    }
     public function export1Op() {
        //搜索条件
        $model_goods = Model('goods');
        $where = array();
        $where['store_id'] = $_SESSION['store_id'];
        if (intval($_GET['stc_id']) > 0) {
            $where['goods_stcids'] = array('like', '%,' . intval($_GET['stc_id']) . ',%');
        }
         //价格区间
        if ($_GET['d_price']!=''  || $_GET['h_price']!='') {
            $d_price = intval($_GET['d_price']);
            $h_price = intval($_GET['h_price']);
            $where['goods_price'] = array('between',array($d_price,$h_price));
        }
        //lxs
       $a=$_GET['keyword'];
       $str=trim($a);
       if($str!=''){
           $str = Model('search')->decorateSearch_pre($str);//调用model里的search.model的搜索代码
           switch ($_GET['search_type']){
               case 0:
                   $where['goods_name']=array('like','%'.$str.'%');
                   break;
               case 1:
                   $where['goods_serial']=array('like','%'.$str.'%');
                   break;
               case 2:
                   $where['goods_commonid']=intval($a);
                   break;
           }
       }
        // $goods_list = $model_goods->getGoodsCommonOnlineList($where);
        if (!is_numeric($_GET['curpage'])){
            // echo'666';die;
            // var_dump($where);echo'br/';
            // $array = array();
            // $count = $model_complain->getGoodsCommonOnlineList($where);  
            // var_dump($count);die;
                $data = $model_goods->getGoodsCommonOnlineList($where,'*',0);
                // var_dump($data);die;
                $this->createExcel($data);
            //}
        }else{  //下载
            // echo'66677';die;
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_goods->getGoodsCommonOnlineList($where,'*',0);
            $this->createExcel($data);
        }
    }
     public function exportOp() {
        //搜索条件
        $model_goods = Model('goods');
        $where = array();
        $where['store_id'] = $_SESSION['store_id'];
        if (intval($_GET['stc_id']) > 0) {
            $where['goods_stcids'] = array('like', '%,' . intval($_GET['stc_id']) . ',%');
        }
         //价格区间
        if ($_GET['d_price']!=''  || $_GET['h_price']!='') {
            $d_price = intval($_GET['d_price']);
            $h_price = intval($_GET['h_price']);
            $where['goods_price'] = array('between',array($d_price,$h_price));
        }
        //lxs
       $a=$_GET['keyword'];
       $str=trim($a);
       if($str!=''){
           $str = Model('search')->decorateSearch_pre($str);//调用model里的search.model的搜索代码
           switch ($_GET['search_type']){
               case 0:
                   $where['goods_name']=array('like','%'.$str.'%');
                   break;
               case 1:
                   $where['goods_serial']=array('like','%'.$str.'%');
                   break;
               case 2:
                   $where['goods_commonid']=intval($a);
                   break;
           }
       }
        $where['goods_state'] = 1;
        $where['goods_verify'] = 1;
        $goods_list = $model_goods->getGoodsAllList($where,'goods_commonid','','','20000');
        $goods_list_all = array();
        if($goods_list){
            foreach ($goods_list as $key=>$value){
                $goods_spec_list = $model_goods->getGoodsList(array('goods_commonid'=>$value['goods_commonid']),'gc_id_1,gc_id_2,gc_id_3,goods_name,goods_jingle,goods_price,goods_costprice,goods_weight,goods_presalenum,goods_serial,goods_storage,goods_storage_alarm,goods_spec,goods_freight,goods_packages,deliverer_id');
                if($goods_spec_list){
                    foreach ($goods_spec_list as $k=>$v){
                        $goods_list_all[] = $v;
                    }
                }
            }
        }
//        echo '<pre>';var_dump($goods_list_all);die;
        $this->createExcel($goods_list_all);
        if (!is_numeric($_GET['curpage'])){
            // echo'666';die;
            // var_dump($where);echo'br/';
            // $array = array();
            // $count = $model_complain->getGoodsCommonOnlineList($where);  
            // var_dump($count);die;
//            $data = $model_goods->getGoodsCommonOnlineList($where,'*',0);
                // var_dump($data);die;
            $this->createExcel($goods_list);
            //}
        }else{  //下载
            // echo'66677';die;
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_goods->getGoodsCommonOnlineList($where,'*',0);
            $this->createExcel($data);
        }
    }
     /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel1($data = array()){
        Language::read('export');
        import('libraries.excel');
        $excel_obj = new Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
        //header

        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品分类');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品名称');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品卖点');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品价格');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品净重');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品件数');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'预设销量');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商品库存');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'库存预警值');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商家货号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'运费');
        //data
        // var_dump($data);die;
        foreach ((array)$data as $k=>$v){
            // var_dump($v);die;        
            $tmp = array();
           $goods_class = Model('goods_class')->getGoodsClassLineForTag($v['gc_id']);
            $tmp[] = array('data'=>$goods_class['gc_tag_name']);
            $tmp[] = array('data'=>$v['goods_name']);
            $tmp[] = array('data'=>$v['goods_jingle']);
            $tmp[] = array('data'=>$v['goods_price']);
            $tmp[] = array('data'=>$v['goods_weight']);
            $tmp[] = array('data'=>$v['goods_packages']);
            $tmp[] = array('data'=>$v['goods_presalenum']);
            $where = array('goods_commonid' => $v['goods_commonid'], 'store_id' => $_SESSION['store_id']);
            $goods_storage =Model('goods')->getGoodsSum($where, 'goods_storage');
            $tmp[] = array('data'=> $goods_storage);
            $tmp[] = array('data'=>$v['goods_storage_alarm']);
            // var_dump($v);die;
            $tmp[] = array('data'=>$v['goods_serial']);
            $tmp[] = array('data'=>$v['goods_freight']);
        
            $excel_data[] = $tmp;
        }

        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(L('exp_od_order'),CHARSET));
        $excel_obj->generateXML($excel_obj->charset('商品信息',CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
    }
     /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array()){
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT');
        $tableheader = array('商品分类1','商品分类2','商品分类3', '商品名称','商品规格', '商品卖点', '商品价格','成本价', '商品净重', '商品件数', '预设销量', '商品库存', '库存预警值', '商家货号', '运费','发货人');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
            $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
        }

         foreach ((array)$data as $k=>$v){
            $goods_class1 = Model('goods_class')->getfby_gc_id($v['gc_id_1'],'gc_name');
            $goods_class2 = Model('goods_class')->getfby_gc_id($v['gc_id_2'],'gc_name');
            $goods_class3 = Model('goods_class')->getfby_gc_id($v['gc_id_3'],'gc_name');
            $goods_spec = unserialize($v['goods_spec']);
            if($goods_spec){
                $goods_spec_info = array_values($goods_spec)[0];
            }else{
                $goods_spec_info = '';
            }
            $deliverer_name = Model('daddress')->getfby_address_id($v['deliverer_id'],'seller_name');
            $order_data[] = [
                $goods_class1,$goods_class2,$goods_class3,$v['goods_name'],$goods_spec_info,$v['goods_jingle'],$v['goods_price'],$v['goods_costprice'],$v['goods_weight'],$v['goods_packages'],$v['goods_presalenum'],intval($v['goods_storage']), $v['goods_storage_alarm'], $v['goods_serial'], $v['goods_freight'],$deliverer_name
                ];
        }

        //填充表格信息
        for ($i = 2; $i <= count($order_data) + 1; $i++) {
            $j = 0;
            foreach ($order_data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $excel->getActiveSheet()->setCellValueExplicit("$letter[$j]$i","$value",PHPExcel_Cell_DataType::TYPE_STRING);
                $j++;
            }
        }
        //创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        $filename = '出售中商品-' . date('Y-m-d-H', time()) . '.xls';
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
        //data
        // var_dump($data);die;
       
    }
    /**
     * 编辑商品页面
     */
    public function edit_goodsOp() {
        $common_id = $_GET['commonid'];
        if ($common_id <= 0) {
            showMessage(L('wrong_argument'), '', 'html', 'error');
        }
        $model_goods = Model('goods');
        $goodscommon_info = $model_goods->getGoodeCommonInfoByID($common_id);
        // var_dump($goodscommon_info);die;
        if (empty($goodscommon_info) || $goodscommon_info['store_id'] != $_SESSION['store_id'] || $goodscommon_info['goods_lock'] == 1) {
            showMessage(L('wrong_argument'), '', 'html', 'error');
        }

        $where = array('goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id']);
        //$where['is_deleted'] = 0;
        $goodscommon_info['g_storage'] = $model_goods->getGoodsSum($where, 'goods_storage');
        $goodscommon_info['ladder'] = $model_goods->getfby_goods_commonid($common_id, 'p_ladder_id');
        $goodscommon_info['is_cw'] = $model_goods->getfby_goods_commonid($common_id, 'is_cw');
        $goodscommon_info['goods_barcode'] = $model_goods->getfby_goods_commonid($common_id, 'goods_barcode');
        $goodscommon_info['is_jin'] = $model_goods->getfby_goods_commonid($common_id, 'is_jin');
        $goodscommon_info['xiao_gui'] = $model_goods->getfby_goods_commonid($common_id, 'xiao_gui');
        $goodscommon_info['danwei_id'] = $model_goods->getfby_goods_commonid($common_id, 'danwei_id');
        $goodscommon_info['wu_gui'] = $model_goods->getfby_goods_commonid($common_id, 'wu_gui');
        $goodscommon_info['spec_name'] = unserialize($goodscommon_info['spec_name']);
        if ($goodscommon_info['mobile_body'] != '') {
            $goodscommon_info['mb_body'] = unserialize($goodscommon_info['mobile_body']);

	    if (is_array($goodscommon_info['mb_body'])) {
                $mobile_body = '[';
                foreach ($goodscommon_info['mb_body'] as $val ) {
                    $mobile_body .= '{"type":"' . $val['type'] . '","value":"' . $val['value'] . '"},';
                }
                $mobile_body = rtrim($mobile_body, ',') . ']';
            }
            $goodscommon_info['mobile_body'] = $mobile_body;
        }
        Tpl::output('goods', $goodscommon_info);

        //获取goods_kuajing_d数据
        //$model_goods_kuajing_d = Model('goods_kuajing_d');
        $kuajing_id = $model_goods->getfby_goods_commonid($common_id,'goods_kuajingD_id');
        if ($kuajing_id > 0) { 
            $goods_kuajing_info = $model_goods->getGoodeKuajingInfo(array('id'=>$kuajing_id));
        Tpl::output('goods_kuajingD', $goods_kuajing_info);

        }
        else {

            Tpl::output('goods_kuajingD', array('id' => $kuajing_id));
        }

        if (intval($_GET['class_id']) > 0) {
            $goodscommon_info['gc_id'] = intval($_GET['class_id']);
        }
        $goods_class = Model('goods_class')->getGoodsClassLineForTag($goodscommon_info['gc_id']);
        Tpl::output('goods_class', $goods_class);

        $model_type = Model('type');
        // 获取类型相关数据
        $typeinfo = $model_type->getAttr($goods_class['type_id'], $_SESSION['store_id'], $goodscommon_info['gc_id']);
        list($spec_json, $spec_list, $attr_list, $brand_list) = $typeinfo;
        Tpl::output('spec_json', $spec_json);
        Tpl::output('sign_i', count($spec_list));
        Tpl::output('spec_list', $spec_list);
        Tpl::output('attr_list', $attr_list);
        Tpl::output('brand_list', $brand_list);

        // 取得商品规格的输入值 jinp170608
        $goods_array = $model_goods->getGoodsList($where, 'goods_id,goods_marketprice,goods_costprice,goods_price,goods_weight,goods_all_weight,goods_packages,goods_app_price,goods_storage,goods_serial,goods_barcode,goods_storage_alarm,goods_spec,is_mode,goods_hs,goods_tax,goods_shipper_id,p_ladder_id,xiao_gui,danwei_id,wu_gui,is_deleted');
        $sp_value = array();
        if (is_array($goods_array) && !empty($goods_array)) {

            // 取得已选择了哪些商品的属性
            $attr_checked_l = $model_type->typeRelatedList ( 'goods_attr_index', array (
                    'goods_id' => intval ( $goods_array[0]['goods_id'] )
            ), 'attr_value_id' );
            if (is_array ( $attr_checked_l ) && ! empty ( $attr_checked_l )) {
                $attr_checked = array ();
                foreach ( $attr_checked_l as $val ) {
                    $attr_checked [] = $val ['attr_value_id'];
                }
            }
            Tpl::output ( 'attr_checked', $attr_checked );

            $spec_checked = array();
            foreach ( $goods_array as $k => $v ) {
                $a = unserialize($v['goods_spec']);
                if (!empty($a)) {
					if($v['is_deleted']==0){
						foreach ($a as $key => $val){
							$spec_checked[$key]['id'] = $key;
							$spec_checked[$key]['name'] = $val;
						}
					}
                    $matchs = array_keys($a);
                    sort($matchs);
                    $id = str_replace ( ',', '', implode ( ',', $matchs ) );
                    $sp_value ['i_' . $id . '|xiao_gui'] = $v['xiao_gui'];
                    $sp_value ['i_' . $id . '|wu_gui'] = $v['wu_gui'];
                    $sp_value ['i_' . $id . '|ladder1'] = $v['danwei_id'];
                    $sp_value_ladder ['i_' . $id . '|ladder1'] = $v['danwei_id'];
                    $sp_value ['i_' . $id . '|marketprice'] = $v['goods_marketprice'];
                    $sp_value ['i_' . $id . '|price'] = $v['goods_price'];
                    //jinp170608
                    $sp_value ['i_' . $id . '|weight'] = $v['goods_weight'];
                    $sp_value ['i_' . $id . '|all_weight'] = $v['goods_all_weight'];
                    $sp_value ['i_' . $id . '|packages'] = $v['goods_packages'];
					$sp_value ['i_' . $id . '|costprice'] = $v['goods_costprice'];
                    $sp_value ['i_' . $id . '|id'] = $v['goods_id'];
                    $sp_value ['i_' . $id . '|stock'] = $v['goods_storage'];
                    $sp_value ['i_' . $id . '|alarm'] = $v['goods_storage_alarm'];
                    $sp_value ['i_' . $id . '|sku'] = $v['goods_serial'];
                    $sp_value ['i_' . $id . '|barcode'] = $v['goods_barcode'];
                    $sp_value ['i_' . $id . '|ladder'] = $v['p_ladder_id'];
                    $sp_value_ladder ['i_' . $id . '|ladder'] = $v['p_ladder_id'];
                }
            }
            Tpl::output('spec_checked', $spec_checked);
        }
        Tpl::output ( 'sp_value', $sp_value );
        if(is_array($sp_value_ladder)) {
            foreach ($sp_value_ladder as $ks => $vs) {
                $spec_value[$ks]['spec'] = $ks;
                $spec_value[$ks]['spec_value'] = $vs;
            }
            $spec_value = array_values($spec_value);
            Tpl::output('sp_value_ladder', json_encode($spec_value));
        }

        // 实例化店铺商品分类模型
        $store_goods_class = Model('store_goods_class')->getClassTree(array('store_id' => $_SESSION ['store_id'], 'stc_state' => '1'));
        Tpl::output('store_goods_class', $store_goods_class);
        //处理商品所属分类
        $store_goods_class_tmp = array();
        if (!empty($store_goods_class)){
            foreach ($store_goods_class as $k=>$v) {
                $store_goods_class_tmp[$v['stc_id']] = $v;
                if (is_array($v['child'])) {
                    foreach ($v['child'] as $son_k=>$son_v){
                        $store_goods_class_tmp[$son_v['stc_id']] = $son_v;
                    }
                }
            }
        }
        $goodscommon_info['goods_stcids'] = trim($goodscommon_info['goods_stcids'], ',');
        $goods_stcids = empty($goodscommon_info['goods_stcids'])?array():explode(',', $goodscommon_info['goods_stcids']);
        $goods_stcids_tmp = $goods_stcids_new = array();
        if (!empty($goods_stcids)){
            foreach ($goods_stcids as $k=>$v){
                $stc_parent_id = $store_goods_class_tmp[$v]['stc_parent_id'];
                //分类进行分组，构造为array('1'=>array(5,6,8));
                if ($stc_parent_id > 0){//如果为二级分类，则分组到父级分类下
                    $goods_stcids_tmp[$stc_parent_id][] = $v;
                } elseif (empty($goods_stcids_tmp[$v])) {//如果为一级分类而且分组不存在，则建立一个空分组数组
                    $goods_stcids_tmp[$v] = array();
                }
            }
            foreach ($goods_stcids_tmp as $k=>$v){
                if (!empty($v) && count($v) > 0){
                    $goods_stcids_new = array_merge($goods_stcids_new,$v);
                } else {
                    $goods_stcids_new[] = $k;
                }
            }
        }
        Tpl::output('store_class_goods', $goods_stcids_new);

        // 是否能使用编辑器
        if(checkPlatformStore()){ // 平台店铺可以使用编辑器
            $editor_multimedia = true;
        } else {    // 三方店铺需要
            $editor_multimedia = false;
            if ($this->store_grade['sg_function'] == 'editor_multimedia') {
                $editor_multimedia = true;
            }
        }
        Tpl::output ( 'editor_multimedia', $editor_multimedia );

        // 小时分钟显示
        $hour_array = array('00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23');
        Tpl::output('hour_array', $hour_array);
        $minute_array = array('05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55');
        Tpl::output('minute_array', $minute_array);

        // 关联版式
        $plate_list = Model('store_plate')->getStorePlateList(array('store_id' => $_SESSION['store_id']), 'plate_id,plate_name,plate_position');
        $plate_list = array_under_reset($plate_list, 'plate_position', 2);
        Tpl::output('plate_list', $plate_list);

        // F码
        if ($goodscommon_info['is_fcode'] == 1) {
            $fcode_array = Model('goods_fcode')->getGoodsFCodeList(array('goods_commonid' => $goodscommon_info['goods_commonid']));
            Tpl::output('fcode_array', $fcode_array);
        }
        $menu_promotion = array(
            'lock' => $goodscommon_info['goods_lock'] == 1 ? true : false,
            'gift' => $model_goods->checkGoodsIfAllowGift($goodscommon_info),
            'combo' => $model_goods->checkGoodsIfAllowCombo($goodscommon_info)
        );
        $this->profile_menu('edit_detail','edit_detail', $menu_promotion);
        Tpl::output('edit_goods_sign', true);

        //抛出国家变量
        $model_country = Model('kuajing_country');
        $kuajing_country= $model_country->select();
        Tpl::output('kuajing_country', $kuajing_country);

        //关联发货人变量
        $deliverer=Model('daddress')->where(array('store_id'=>$_SESSION ['store_id']))->select();
        // var_dump($deliverer);die;
        Tpl::output('deliverer', $deliverer);

        //抛出本店铺的发货人变量
        $model_shipper = Model('shipper_kuajing_d');
        $kuajing_shipper= $model_shipper->where(array('store_id'=>$_SESSION ['store_id']))->select();
        Tpl::output('kuajing_shipper', $kuajing_shipper);

        //抛出阶梯折扣信息
        $ladder_list = Model('p_ladder')->getMansongList(['store_id'=>$_SESSION['store_id']],'','','p_ladder_id,p_name');
        Tpl::output('ladder_list', $ladder_list);

        Tpl::showpage('store_goods_add.step2');
    }

 /**
     * 备货参数页面
     */
    public function edit_goodsOp1() {
        $common_id = $_GET['commonid'];
        if ($common_id <= 0) {
            showMessage(L('wrong_argument'), '', 'html', 'error');
        }
        $model_goods = Model('goods');
        $goodscommon_info = $model_goods->getGoodeCommonInfoByID($common_id);
        if (empty($goodscommon_info) || $goodscommon_info['store_id'] != $_SESSION['store_id'] || $goodscommon_info['goods_lock'] == 1) {
            showMessage(L('wrong_argument'), '', 'html', 'error');
        }

        $where = array('goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id']);
        $goodscommon_info['g_storage'] = $model_goods->getGoodsSum($where, 'goods_storage');
        $goodscommon_info['spec_name'] = unserialize($goodscommon_info['spec_name']);
        if ($goodscommon_info['mobile_body'] != '') {
            $goodscommon_info['mb_body'] = unserialize($goodscommon_info['mobile_body']);

	    if (is_array($goodscommon_info['mb_body'])) {
                $mobile_body = '[';
                foreach ($goodscommon_info['mb_body'] as $val ) {
                    $mobile_body .= '{"type":"' . $val['type'] . '","value":"' . $val['value'] . '"},';
                }
                $mobile_body = rtrim($mobile_body, ',') . ']';
            }
            $goodscommon_info['mobile_body'] = $mobile_body;
        }
        Tpl::output('goods', $goodscommon_info);

        if (intval($_GET['class_id']) > 0) {
            $goodscommon_info['gc_id'] = intval($_GET['class_id']);
        }
        $goods_class = Model('goods_class')->getGoodsClassLineForTag($goodscommon_info['gc_id']);
        Tpl::output('goods_class', $goods_class);

        $model_type = Model('type');
        // 获取类型相关数据
        $typeinfo = $model_type->getAttr($goods_class['type_id'], $_SESSION['store_id'], $goodscommon_info['gc_id']);
        list($spec_json, $spec_list, $attr_list, $brand_list) = $typeinfo;
        Tpl::output('spec_json', $spec_json);
        Tpl::output('sign_i', count($spec_list));
        Tpl::output('spec_list', $spec_list);
        Tpl::output('attr_list', $attr_list);
        Tpl::output('brand_list', $brand_list);

        // 取得商品规格的输入值
        $goods_array = $model_goods->getGoodsList($where, 'goods_id,goods_marketprice,goods_price,goods_app_price,goods_storage,goods_serial,goods_barcode,goods_storage_alarm,goods_spec');
        $sp_value = array();
        if (is_array($goods_array) && !empty($goods_array)) {

            // 取得已选择了哪些商品的属性
            $attr_checked_l = $model_type->typeRelatedList ( 'goods_attr_index', array (
                    'goods_id' => intval ( $goods_array[0]['goods_id'] )
            ), 'attr_value_id' );
            if (is_array ( $attr_checked_l ) && ! empty ( $attr_checked_l )) {
                $attr_checked = array ();
                foreach ( $attr_checked_l as $val ) {
                    $attr_checked [] = $val ['attr_value_id'];
                }
            }
            Tpl::output ( 'attr_checked', $attr_checked );

            $spec_checked = array();
            foreach ( $goods_array as $k => $v ) {
                $a = unserialize($v['goods_spec']);
                if (!empty($a)) {
                    foreach ($a as $key => $val){
                        $spec_checked[$key]['id'] = $key;
                        $spec_checked[$key]['name'] = $val;
                    }
                    $matchs = array_keys($a);
                    sort($matchs);
                    $id = str_replace ( ',', '', implode ( ',', $matchs ) );
                    $sp_value ['i_' . $id . '|marketprice'] = $v['goods_marketprice'];
                    $sp_value ['i_' . $id . '|price'] = $v['goods_price'];
					$sp_value ['i_' . $id . '|app_price'] = $v['goods_app_price'];
                    $sp_value ['i_' . $id . '|id'] = $v['goods_id'];
                    $sp_value ['i_' . $id . '|stock'] = $v['goods_storage'];
                    $sp_value ['i_' . $id . '|alarm'] = $v['goods_storage_alarm'];
                    $sp_value ['i_' . $id . '|sku'] = $v['goods_serial'];
                    $sp_value ['i_' . $id . '|barcode'] = $v['goods_barcode'];
                }
            }
            Tpl::output('spec_checked', $spec_checked);
        }
        Tpl::output ( 'sp_value', $sp_value );

        // 实例化店铺商品分类模型
        $store_goods_class = Model('store_goods_class')->getClassTree(array('store_id' => $_SESSION ['store_id'], 'stc_state' => '1'));
        Tpl::output('store_goods_class', $store_goods_class);
        //处理商品所属分类
        $store_goods_class_tmp = array();
        if (!empty($store_goods_class)){
            foreach ($store_goods_class as $k=>$v) {
                $store_goods_class_tmp[$v['stc_id']] = $v;
                if (is_array($v['child'])) {
                    foreach ($v['child'] as $son_k=>$son_v){
                        $store_goods_class_tmp[$son_v['stc_id']] = $son_v;
                    }
                }
            }
        }
        $goodscommon_info['goods_stcids'] = trim($goodscommon_info['goods_stcids'], ',');
        $goods_stcids = empty($goodscommon_info['goods_stcids'])?array():explode(',', $goodscommon_info['goods_stcids']);
        $goods_stcids_tmp = $goods_stcids_new = array();
        if (!empty($goods_stcids)){
            foreach ($goods_stcids as $k=>$v){
                $stc_parent_id = $store_goods_class_tmp[$v]['stc_parent_id'];
                //分类进行分组，构造为array('1'=>array(5,6,8));
                if ($stc_parent_id > 0){//如果为二级分类，则分组到父级分类下
                    $goods_stcids_tmp[$stc_parent_id][] = $v;
                } elseif (empty($goods_stcids_tmp[$v])) {//如果为一级分类而且分组不存在，则建立一个空分组数组
                    $goods_stcids_tmp[$v] = array();
                }
            }
            foreach ($goods_stcids_tmp as $k=>$v){
                if (!empty($v) && count($v) > 0){
                    $goods_stcids_new = array_merge($goods_stcids_new,$v);
                } else {
                    $goods_stcids_new[] = $k;
                }
            }
        }
        Tpl::output('store_class_goods', $goods_stcids_new);

        // 是否能使用编辑器
        if(checkPlatformStore()){ // 平台店铺可以使用编辑器
            $editor_multimedia = true;
        } else {    // 三方店铺需要
            $editor_multimedia = false;
            if ($this->store_grade['sg_function'] == 'editor_multimedia') {
                $editor_multimedia = true;
            }
        }
        Tpl::output ( 'editor_multimedia', $editor_multimedia );

        // 小时分钟显示
        $hour_array = array('00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23');
        Tpl::output('hour_array', $hour_array);
        $minute_array = array('05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55');
        Tpl::output('minute_array', $minute_array);

        // 关联版式
        $plate_list = Model('store_plate')->getStorePlateList(array('store_id' => $_SESSION['store_id']), 'plate_id,plate_name,plate_position');
        $plate_list = array_under_reset($plate_list, 'plate_position', 2);
        Tpl::output('plate_list', $plate_list);

        // F码
        if ($goodscommon_info['is_fcode'] == 1) {
            $fcode_array = Model('goods_fcode')->getGoodsFCodeList(array('goods_commonid' => $goodscommon_info['goods_commonid']));
            Tpl::output('fcode_array', $fcode_array);
        }
        $menu_promotion = array(
            'lock' => $goodscommon_info['goods_lock'] == 1 ? true : false,
            'gift' => $model_goods->checkGoodsIfAllowGift($goodscommon_info),
            'combo' => $model_goods->checkGoodsIfAllowCombo($goodscommon_info)
        );
        $this->profile_menu('edit_detail','edit_detail', $menu_promotion);
        Tpl::output('edit_goods_sign', true);
        Tpl::showpage('store_goods_add.step2');
    }

    /**
     * 编辑商品保存
     */
    public function edit_save_goodsOp() {

        $common_id = intval ( $_POST ['commonid'] );
        if (!chksubmit() || $common_id <= 0) {
            showDialog(L('store_goods_index_goods_edit_fail'), urlShop('store_goods_online', 'index'));
        }
        if(!$_POST ['spec']){//此时没有规格，查看该commonid对应的是否为多规格，如果为多规格则返回，提示不能保存
//            $order_spec_info = Model()->table('goods_common')->getfby_goods_commonid($common_id,'spec_value');
            $order_spec_info = Model('goods')->getGoodsList(array('goods_commonid'=>$common_id,'is_deleted'=>0));
//            $order_spec = unserialize($order_spec_info);
            if($order_spec_info && count($order_spec_info)>0){
                showDialog('多规格商品编辑应至少保留一个规格，请重新编辑！', 'reload');
            }//else编辑提交没有规格，且编辑商品为无规格商品
        }else{//此时有规格，查看该commonid对应的规格

        }
//        echo '<pre>';var_dump($order_spec);die;
        // 验证表单
        $obj_validate = new Validate ();
        $obj_validate->validateparam = array (
            array (
                "input" => $_POST["g_name"],
                "require" => "true",
                "message" => L('store_goods_index_goods_name_null')
            ),
            array (
                "input" => $_POST["g_price"],
                "require" => "true",
                "validator" => "Double",
                "message" => L('store_goods_index_goods_price_null')
            ),
			array (
                "input" => $_POST["g_app_price"],
                "require" => "true",
                "validator" => "Double",
                "message" => L('store_goods_index_goods_price_null')
            )
        );
        $error = $obj_validate->validate ();
        if ($error != '') {
            showDialog(L('error') . $error, urlShop('store_goods_online', 'index'));
        }

        $gc_id = intval($_POST['cate_id']);

        // 验证商品分类是否存在且商品分类是否为最后一级
        $data = Model('goods_class')->getGoodsClassForCacheModel();
        if (!isset($data[$gc_id]) || isset($data[$gc_id]['child']) || isset($data[$gc_id]['childchild'])) {
            showDialog(L('store_goods_index_again_choose_category1'));
        }

        // 三方店铺验证是否绑定了该分类
        if (!checkPlatformStore()) {
            //商品分类提供批量显示所有分类插件
            $model_bind_class = Model('store_bind_class');
            $goods_class = Model('goods_class')->getGoodsClassForCacheModel();
            $where['store_id'] = $_SESSION['store_id'];
            $class_2 = $goods_class[$gc_id]['gc_parent_id'];
            $class_1 = $goods_class[$class_2]['gc_parent_id'];
            $where['class_1'] =  $class_1;
            $where['class_2'] =  $class_2;
            $where['class_3'] =  $gc_id;
            $bind_info = $model_bind_class->getStoreBindClassInfo($where);
            if (empty($bind_info))
            {
                $where['class_3'] =  0;
                $bind_info = $model_bind_class->getStoreBindClassInfo($where);
                if (empty($bind_info))
                {
                    $where['class_2'] =  0;
                    $where['class_3'] =  0;
                    $bind_info = $model_bind_class->getStoreBindClassInfo($where);
                    if (empty($bind_info))
                    {
                        $where['class_1'] =  0;
                        $where['class_2'] =  0;
                        $where['class_3'] =  0;
                        $bind_info = $model_bind_class->getStoreBindClassInfo($where);
                        if (empty($bind_info))
                        {
                            showDialog(L('store_goods_index_again_choose_category2'));
                        }
                    }

                }

            }
        }
        // 分类信息
        $goods_class = Model('goods_class')->getGoodsClassLineForTag(intval($_POST['cate_id']));

        $model_goods = Model ( 'goods' );

        $update_common = array();
        $update_common['goods_name']         = $_POST['g_name'];
        //额外送积分
        if(!empty($_POST['points_send'])){
         $update_common['points_send']         = $_POST['points_send'];
          }else{
                 $update_common['points_send']         = '0';
            } 
        $update_common['deliverer_id']       = $_POST['deliverer_id']; 
        $update_common['goods_jingle']       = $_POST['g_jingle'];
        $update_common['gc_id']              = $gc_id;
        $update_common['gc_id_1']            = intval($goods_class['gc_id_1']);
        $update_common['gc_id_2']            = intval($goods_class['gc_id_2']);
        $update_common['gc_id_3']            = intval($goods_class['gc_id_3']);
        $update_common['gc_name']            = $_POST['cate_name'];
        $update_common['brand_id']           = $_POST['b_id'];
        $update_common['brand_name']         = $_POST['b_name'];
        $update_common['type_id']            = intval($_POST['type_id']);
        $update_common['goods_image']        = $_POST['image_path'];
        $update_common['goods_price']        = floatval($_POST['g_price']);
        $update_common['is_vip_price']        = floatval($_POST['is_vip_price']);
		$update_common['goods_app_price']    = floatval($_POST['g_app_price']);
        $update_common['goods_marketprice']  = floatval($_POST['g_marketprice']);
        $update_common['goods_costprice']    = floatval($_POST['g_costprice']);
        $update_common['goods_discount']     = floatval($_POST['g_discount']);
        //佣金
        $update_common['commis_rate']    = $_POST['commis_rate'];
        $update_common['goods_presalenum']     = intval($_POST['g_presalenum']);
        $update_common['goods_serial']       = $_POST['g_serial'];
        $update_common['goods_storage_alarm']= intval($_POST['g_alarm']);
        $update_common['goods_attr']         = serialize($_POST['attr']);
        $update_common['goods_body']         = $_POST['g_body'];
        // 序列化保存手机端商品描述数据
        if ($_POST['m_body'] != '') {
            $_POST['m_body'] = str_replace('&quot;', '"', $_POST['m_body']);
            $_POST['m_body'] = json_decode($_POST['m_body'], true);
            if (!empty($_POST['m_body'])) {
                $_POST['m_body'] = serialize($_POST['m_body']);
            } else {
                $_POST['m_body'] = '';
            }
        }
        $update_common['mobile_body']        = $_POST['m_body'];
        $update_common['goods_commend']      = intval($_POST['g_commend']);
        $update_common['goods_state']        = ($this->store_info['store_state'] != 1) ? 0 : intval($_POST['g_state']);            // 店铺关闭时，商品下架
        $update_common['goods_selltime']     = strtotime($_POST['starttime']) + intval($_POST['starttime_H'])*3600 + intval($_POST['starttime_i'])*60;
        $update_common['goods_verify']       = (C('goods_verify') == 1) ? 10 : 1;
        $update_common['spec_name']          = is_array($_POST['spec']) ? serialize($_POST['sp_name']) : serialize(null);
        $update_common['spec_value']         = is_array($_POST['spec']) ? serialize($_POST['sp_val']) : serialize(null);
        $update_common['goods_vat']          = intval($_POST['g_vat']);
        $update_common['areaid_1']           = intval($_POST['province_id']);
        $update_common['areaid_2']           = intval($_POST['city_id']);
        $update_common['transport_id']       = ($_POST['freight'] == '0') ? '0' : intval($_POST['transport_id']); // 售卖区域
        $update_common['transport_title']    = $_POST['transport_title'];
        $update_common['goods_freight']      = floatval($_POST['g_freight']);
        //查询店铺商品分类
        $goods_stcids_arr = array();
        if (!empty($_POST['sgcate_id'])){
            $sgcate_id_arr = array();
            foreach ($_POST['sgcate_id'] as $k=>$v){
                $sgcate_id_arr[] = intval($v);
            }
            $sgcate_id_arr = array_unique($sgcate_id_arr);
            $store_goods_class = Model('store_goods_class')->getStoreGoodsClassList(array('store_id' => $_SESSION['store_id'], 'stc_id' => array('in', $sgcate_id_arr), 'stc_state' => '1'));
            if (!empty($store_goods_class)){
                foreach ($store_goods_class as $k=>$v){
                    if ($v['stc_id'] > 0){
                        $goods_stcids_arr[] = $v['stc_id'];
                    }
                    if ($v['stc_parent_id'] > 0){
                        $goods_stcids_arr[] = $v['stc_parent_id'];
                    }
                }
                $goods_stcids_arr = array_unique($goods_stcids_arr);
                sort($goods_stcids_arr);
            }
        }
        if (empty($goods_stcids_arr)){
            $update_common['goods_stcids'] = '';
        } else {
            $update_common['goods_stcids'] = ','.implode(',',$goods_stcids_arr).',';
        }
        $update_common['plateid_top']        = intval($_POST['plate_top']) > 0 ? intval($_POST['plate_top']) : '';
        $update_common['plateid_bottom']     = intval($_POST['plate_bottom']) > 0 ? intval($_POST['plate_bottom']) : '';
        $update_common['is_virtual']         = intval($_POST['is_gv']);
        $update_common['virtual_indate']     = $_POST['g_vindate'] != '' ? (strtotime($_POST['g_vindate']) + 24*60*60 -1) : 0;  // 当天的最后一秒结束
        $update_common['virtual_limit']      = intval($_POST['g_vlimit']) > 10 || intval($_POST['g_vlimit']) < 0 ? 10 : intval($_POST['g_vlimit']);
        $update_common['virtual_invalid_refund'] = intval($_POST['g_vinvalidrefund']);
        $update_common['is_fcode']           = intval($_POST['is_fc']);
        $update_common['is_appoint']         = intval($_POST['is_appoint']);     // 只有库存为零的商品可以预约
        $update_common['appoint_satedate']   = $update_common['is_appoint'] == 1 ? strtotime($_POST['g_saledate']) : '';   // 预约商品的销售时间
        $update_common['is_presell']         = $update_common['goods_state'] == 1 ? intval($_POST['is_presell']) : 0;     // 只有出售中的商品可以预售
        $update_common['presell_deliverdate']= $update_common['is_presell'] == 1? strtotime($_POST['g_deliverdate']) : ''; // 预售商品的发货时间
        $update_common['is_own_shop']        = in_array($_SESSION['store_id'], model('store')->getOwnShopIds()) ? 1 : 0;
        $update_common['is_mode']            = $_POST['is_mode'];
        if($update_common['is_mode'] == 0){$update_common['goods_shipper_id'] = 0;}
        else {$update_common['goods_shipper_id']   = $_POST['goods_shipper_id'];}
        
        $update_common['goods_hs']           = $_POST['goods_hs'];
        $model_hs = Model('ctax_hs');
            $xiaofei_rate = $model_hs->getfby_hs($_POST['goods_hs'],'xiaofei_rate');
            $zengzhi_rate = $model_hs->getfby_hs($_POST['goods_hs'],'zengzhi_rate');  
            //综合税率
            $update_common['goods_tax_rate'] = $model_hs->getfby_hs($_POST['goods_hs'],'tax_rate');         
            $tax_all = array();
            $tax_all = $model_hs->getgoodsTax($update_common['goods_price'],$xiaofei_rate,$zengzhi_rate);
            $update_common['goods_tax'] =  $tax_all['tax'];
            // jinp170608 商品净重/毛重、件数
            $update_common['goods_weight']        = floatval($_POST['g_weight']);
            $update_common['goods_all_weight']    = floatval($_POST['g_all_weight']);
            $update_common['goods_packages']        = intval($_POST['g_packages']);
            //跨境参数
            $kuajing_array = array();
            $kuajing_array['is_mode']           = intval($_POST['is_mode']);
            //$kuajing_array['goods_id']        = '';
            $kuajing_array['country_origin']    = $_POST['source_country'];
            $kuajing_array['country_trade']     = $_POST['trade_country'];
            $kuajing_array['hs']                = $_POST['goods_hs'];
            $kuajing_array['weight_unit']       = $_POST['net_weight_unit'];
            $kuajing_array['net_weight']        = $_POST['net_weight'];
            $kuajing_array['gross_weight']      = $_POST['gross_weight'];
            $kuajing_array['record_no_guan']    = $_POST['record_no_guan'];
            $kuajing_array['record_no_jian']    = $_POST['record_no_jian'];
            $kuajing_array['unit_guan']         = $_POST['unit_guan'];
            $kuajing_array['unit_jian']         = $_POST['unit_jian'];
            $kuajing_array['unit_name']         = $_POST['unit_name'];
            $kuajing_array['unit1']         = $_POST['unit1'];
            $kuajing_array['unit2']         = $_POST['unit2'];
            $kuajing_array['qty1']         = $_POST['qty1'];
            $kuajing_array['qty2']         = $_POST['qty2'];
            $kuajing_array['tax']               = $tax_all['tax'];
            $kuajing_array['vat']               = $tax_all['vat'];
            $kuajing_array['consumption_tax']   = $tax_all['consumption_tax'];

            $kuajing_array['specification']     = $_POST['guige'];

            $kuajing_id = $_POST['kuajingDid'];
            
            //$kuajing_id = $model_goods->getfby_goods_commonid($common_id,'goods_kuajingD_id');
            //$kuajing_id = 3;
            //如果是跨境产品，保存到跨境参数
            if($kuajing_array['is_mode'] == 2) {
                if ($kuajing_id == 0) {
                    $kuajing_id = $model_goods->addGoodsKuajingD($kuajing_array);
                } else if ($kuajing_id > 0) {
                    $resultKuajingID = $model_goods->editGoodsKuajingById($kuajing_array,$kuajing_id);
                }
            } else {
                $kuajing_id = 0;
            }

        
        


        // 开始事务
        Model()->beginTransaction();
        $model_gift = Model('goods_gift');
        // 清除原有规格数据
//        $model_type = Model('type');
//        $model_type->delGoodsAttr(array('goods_commonid' => $common_id));
            // 生成商品二维码
            require_once(BASE_RESOURCE_PATH.DS.'phpqrcode'.DS.'index.php');
            $PhpQRCode = new PhpQRCode();
            $PhpQRCode->set('pngTempDir',BASE_UPLOAD_PATH.DS.ATTACH_STORE.DS.$_SESSION['store_id'].DS);
                    
        // 更新商品规格
        $goodsid_array = array();
        $colorid_array = array();
        if (is_array ( $_POST ['spec'] )) {
            $this->multi_array_sort($_POST ['spec'],'color');
            $goods_info = $model_goods->getGoodsInfo(array('goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id'],'color_id'=>0),'goods_id');
            if(count($goods_info)==1){//一个无规格商品
                $model_goods->editGoods(array('color_id' =>array_values($_POST ['spec'])[0]['color']), array('goods_id' => $goods_info['goods_id'], 'goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id']));
            }
            foreach ($_POST['spec'] as $value) {
                    $goods_info = $model_goods->getGoodsInfo(array('color_id' => $value['color'], 'goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id']), 'goods_id');
                if (!empty($goods_info)) {
                    $goods_id = $goods_info['goods_id'];
                    $update = array ();
                     $update['goods_commonid']    = $common_id;
                    $update['goods_kuajingD_id'] = $kuajing_id;    
                    $update['goods_name']        = $update_common['goods_name'] . ' ' . implode(' ', $value['sp_value']);
                    //额外送积分
                    $update['points_send']        = $update_common['points_send'];
                    $update['goods_jingle']      = $update_common['goods_jingle'];
                     $update['deliverer_id']        = $update_common['deliverer_id'];
                    $update['is_cw']       = intval($_POST['is_cw']);
                    $update['store_id']          = $_SESSION['store_id'];
                    $update['store_name']        = $_SESSION['store_name'];
                    $update['gc_id']             = $update_common['gc_id'];
                    $update['gc_id_1']           = $update_common['gc_id_1'];
                    $update['gc_id_2']           = $update_common['gc_id_2'];
                    $update['gc_id_3']           = $update_common['gc_id_3'];
                    $update['brand_id']          = $update_common['brand_id'];
                    //佣金
                    $update['commis_rate']       = $update_common['commis_rate'];
                    $update['goods_price']       = $value['price'];
					          $update['goods_app_price']       = $value['app_price'];
                    $update['goods_marketprice'] = $value['marketprice'] == 0 ? $update_common['goods_marketprice'] : $value['marketprice'];
                    $update['goods_costprice'] = $value['costprice'] == 0 ? $update_common['goods_costprice'] : $value['costprice'];
                    $update['goods_presalenum']  = $update_common['goods_presalenum'];
                    $update['goods_serial']      = $value['sku'];
                    $update['goods_barcode']      = $value['barcode'];
                    $update['goods_storage_alarm']= intval($value['alarm']);
                    $update['goods_spec']        = serialize($value['sp_value']);
                    $update['goods_storage']     = $value['stock'];
                    $update['goods_state']       = $update_common['goods_state'];
                    $update['goods_verify']      = $update_common['goods_verify'];
                    $update['goods_edittime']    = TIMESTAMP;
                    $update['areaid_1']          = $update_common['areaid_1'];
                    $update['areaid_2']          = $update_common['areaid_2'];
                    $update['color_id']          = intval($value['color']);
                    $update['transport_id']      = $update_common['transport_id'];
                    $update['goods_freight']     = $update_common['goods_freight'];
                    $update['goods_vat']         = $update_common['goods_vat'];
                    $update['goods_commend']     = $update_common['goods_commend'];
                    $update['goods_stcids']      = $update_common['goods_stcids'];
                    $update['is_virtual']        = $update_common['is_virtual'];
                    $update['virtual_indate']    = $update_common['virtual_indate'];
                    $update['virtual_limit']     = $update_common['virtual_limit'];
                    $update['virtual_invalid_refund'] = $update_common['virtual_invalid_refund'];
                    $update['is_fcode']          = $update_common['is_fcode'];
                    $update['is_appoint']        = $update_common['is_appoint'];
                    $update['is_presell']        = $update_common['is_presell'];
                    $update['is_mode']           = $update_common['is_mode'];

                    //商品阶梯价格
                    $p_ladder_id = $value['ladder'];
                    $update['p_ladder_id'] = $p_ladder_id;
                     //销售规格、最小单位、误差、进销存设置
                     $update['xiao_gui']          = intval($value['xiao_gui']);
                     $update['wu_gui']          = intval($value['wu_gui']);
                     $update['danwei_id']          = intval($value['ladder1']);
                     $update['is_jin']          = intval($_POST['is_jin']);
					 
					$goods_group_ladder = $goods_info['is_group_ladder'];
					//if($goods_group_ladder == 0){
					if($p_ladder_id >0){
						if($goods_group_ladder <= 1){
							$update['is_group_ladder'] = 1;
						}
					}else{
						if($goods_group_ladder <= 1){
							$update['is_group_ladder'] = 0;
						}
					}
					//}

                    if($update['is_mode'] == 0) {$update['goods_shipper_id'] = 0;}
                    else {$update['goods_shipper_id']  = $update_common['goods_shipper_id'];}
                    
                    $update['goods_hs']          = $update_common['goods_hs'];
                    //$update['goods_tax']         = $update_common['goods_tax'];
                    $model_hs = Model('ctax_hs');
                    $xiaofei_rate = $model_hs->getfby_hs($update_common['goods_hs'],'xiaofei_rate');
                    $zengzhi_rate = $model_hs->getfby_hs($update_common['goods_hs'],'zengzhi_rate');
                    //综合税率
                    if($update['is_mode'] == 0) {$update['goods_tax_rate'] = 0;}
                    else {$update['goods_tax_rate'] = $model_hs->getfby_hs($update_common['goods_hs'],'tax_rate');}
                    $tax_all = $model_hs->getgoodsTax($value['price'],$xiaofei_rate,$zengzhi_rate);
                    $update['goods_tax'] = $tax_all['tax'];
                    // jinp170608商品净重、毛重、件数
                    $update['goods_weight']       = $value['weight'];
                    $update['goods_all_weight']       = $value['all_weight'];
                    $update['goods_packages']       = $value['packages'];
                    // 虚拟商品不能有赠品
                    if ($update_common['is_virtual'] == 1) {
                        $update['have_gift']    = 0;
                        $model_gift->delGoodsGift(array('goods_id' => $goods_id));
                    }
                    $update['is_own_shop']       = $update_common['is_own_shop'];
                    //多规格删除商品改为未删除
                    $update['is_deleted'] = 0;
                    $model_goods->editGoodsById($update, $goods_id);
		    // 生成商品二维码
                        //$PhpQRCode->set('date',WAP_SITE_URL . '/tmpl/product_detail.html?goods_id='.$goods_id);
                        $PhpQRCode->set('date',WAP_SITE_URL . '/goods/goodsDetail.shtml?goodsId='.$goods_id);
                        $PhpQRCode->set('pngTempName', $goods_id . '.png');
                        $PhpQRCode->init();
                } else {
                    $insert = array();
                    $insert['goods_commonid']    = $common_id;
                    $insert['goods_kuajingD_id'] = $kuajing_id;
                    $insert['goods_name']        = $update_common['goods_name'] . ' ' . implode(' ', $value['sp_value']);

                    $insert['goods_jingle']      = $update_common['goods_jingle'];
                    $insert['store_id']          = $_SESSION['store_id'];
                    $insert['store_name']        = $_SESSION['store_name'];
                    //额外送积分
                     $insert['deliverer_id']        = $update_common['deliverer_id'];
                    $insert['is_cw']       = intval($_POST['is_cw']);
                    $insert['points_send']        = $update_common['points_send'];
                    $insert['gc_id']             = $update_common['gc_id'];
                    $insert['gc_id_1']           = $update_common['gc_id_1'];
                    $insert['gc_id_2']           = $update_common['gc_id_2'];
                    $insert['gc_id_3']           = $update_common['gc_id_3'];
                    $insert['brand_id']          = $update_common['brand_id'];
                    //佣金
                    $insert['commis_rate']       = $update_common['commis_rate'];
                    $insert['goods_price']       = $value['price'];
					$insert['goods_app_price']   = $value['app_price'];
                    $insert['goods_promotion_price']=$value['price'];
                    $insert['goods_marketprice'] = $value['marketprice'] == 0 ? $update_common['goods_marketprice'] : $value['marketprice'];
                    $insert['goods_costprice'] = $value['costprice'] == 0 ? $update_common['goods_costprice'] : $value['costprice'];
                    $insert['goods_presalenum']  = $update_common['goods_presalenum'];
                    $insert['goods_serial']      = $value['sku'];
                    $insert['goods_barcode']      = $value['barcode'];
                    $insert['goods_storage_alarm']= intval($value['alarm']);
                    $insert['goods_spec']        = serialize($value['sp_value']);
                    $insert['goods_storage']     = $value['stock'];
                    $insert['goods_image']       = $update_common['goods_image'];
                    $insert['goods_state']       = $update_common['goods_state'];
                    $insert['goods_verify']      = $update_common['goods_verify'];
                    $insert['goods_addtime']     = TIMESTAMP;
                    $insert['goods_edittime']    = TIMESTAMP;
                    $insert['areaid_1']          = $update_common['areaid_1'];
                    $insert['areaid_2']          = $update_common['areaid_2'];
                    $insert['color_id']          = intval($value['color']);
                    $insert['transport_id']      = $update_common['transport_id'];
                    $insert['goods_freight']     = $update_common['goods_freight'];
                    $insert['goods_vat']         = $update_common['goods_vat'];
                    $insert['goods_commend']     = $update_common['goods_commend'];
                    $insert['goods_stcids']      = $update_common['goods_stcids'];
                    $insert['is_virtual']        = $update_common['is_virtual'];
                    $insert['virtual_indate']    = $update_common['virtual_indate'];
                    $insert['virtual_limit']     = $update_common['virtual_limit'];
                    $insert['virtual_invalid_refund'] = $update_common['virtual_invalid_refund'];
                    $insert['is_fcode']          = $update_common['is_fcode'];
                    $insert['is_appoint']        = $update_common['is_appoint'];
                    $insert['is_presell']        = $update_common['is_presell'];
                    $insert['is_own_shop']       = $update_common['is_own_shop'];
                    $insert['is_mode']          = $update_common['is_mode'];

                    //商品阶梯价格
                    $p_ladder_id = $value['ladder'];
                    $insert['p_ladder_id'] = intval($p_ladder_id);
                     //销售规格、最小单位、误差、进销存设置
                    $insert['xiao_gui']          = intval($value['xiao_gui']);
                    $insert['wu_gui']          = intval($value['wu_gui']);
                    $insert['danwei_id']          = intval($value['ladder1']);
                    $insert['is_jin']          = intval($_POST['is_jin']);
					if($p_ladder_id >0){
						$insert['is_group_ladder'] = 1;
					}else{
						$insert['is_group_ladder'] = 0;
					}

                    if($insert['is_mode'] == 0) {$insert['goods_shipper_id'] = 0;}
                    else {$insert['goods_shipper_id']  = $update_common['goods_shipper_id'];}
                    
                    $insert['goods_hs']          = $update_common['goods_hs'];
                    //$insert['goods_tax']          = $update_common['goods_tax'];
                     $model_hs = Model('ctax_hs');
                    $xiaofei_rate = $model_hs->getfby_hs($update_common['goods_hs'],'xiaofei_rate');
                    $zengzhi_rate = $model_hs->getfby_hs($update_common['goods_hs'],'xiaofei_rate');
                    //综合税率
                    if($insert['is_mode'] == 0) {$insert['goods_tax_rate'] = 0;}
                    else {$insert['goods_tax_rate'] = $model_hs->getfby_hs($update_common['goods_hs'],'tax_rate');}
                    $tax_all = $model_hs->getgoodsTax($value['price'],$xiaofei_rate,$zengzhi_rate);
                    $insert['goods_tax'] = $tax_all['tax'];
                    // jinp170608 商品净重/毛重、件数
                    $insert['goods_weight']       = $value['weight'];
//                    $insert['goods_all_weight']       = $value['all_weight'];
                    $insert['goods_packages']       = $value['packages'];

                    $goods_id = $model_goods->addGoods($insert);
                        // 生成商品二维码
                        //$PhpQRCode->set('date',WAP_SITE_URL . '/tmpl/product_detail.html?goods_id='.$goods_id);
                        $PhpQRCode->set('date',WAP_SITE_URL . '/goods/goodsDetail.shtml?goodsId='.$goods_id);
                        $PhpQRCode->set('pngTempName', $goods_id . '.png');
                        $PhpQRCode->init();
                }
                $goodsid_array[] = intval($goods_id);
                $colorid_array[] = intval($value['color']);
//                $model_type->addGoodsType($goods_id, $common_id, array('cate_id' => $_POST['cate_id'], 'type_id' => $_POST['type_id'], 'attr' => $_POST['attr']));
				Model('spec')->editSpecValue(array('sp_value_name'=>$value['sp_value'][$value['color']]),array('sp_value_id'=>intval($value['color']),'store_id'=>$_SESSION['store_id'],'gc_id'=>$update_common['gc_id']));
            }
        } else {
            $goods_info = $model_goods->getGoodsInfo(array('goods_spec' => serialize(null), 'goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id']), 'goods_id');
            if (!empty($goods_info)) {
                $goods_id = $goods_info['goods_id'];

                $update = array ();
                $update['goods_commonid']    = $common_id;
                // var_dump($goods_info['is_group_ladder']);die;
                $update['goods_kuajingD_id'] = $kuajing_id;
                $update['goods_name']        = $update_common['goods_name'];
                // 、、额外送积分
                $update['deliverer_id']        = $update_common['deliverer_id'];
                $update['points_send']        = $update_common['points_send'];
                $update['goods_jingle']      = $update_common['goods_jingle'];
                $update['store_id']          = $_SESSION['store_id'];
                $update['store_name']        = $_SESSION['store_name'];
                $update['gc_id']             = $update_common['gc_id'];
                $update['gc_id_1']           = $update_common['gc_id_1'];
                $update['gc_id_2']           = $update_common['gc_id_2'];
                $update['gc_id_3']           = $update_common['gc_id_3'];
                $update['brand_id']          = $update_common['brand_id'];
                //佣金
                $update['commis_rate']          = $update_common['commis_rate'];
                $update['goods_price']       = $update_common['goods_price'];
				$update['goods_app_price']   = $update_common['goods_app_price'];
                $update['goods_marketprice'] = $update_common['goods_marketprice'];
                $update['goods_costprice'] = $update_common['goods_costprice'];
                $update['goods_presalenum']  = $update_common['goods_presalenum'];
                $update['goods_serial']      = $update_common['goods_serial'];
                $update['goods_barcode']      = $_POST['g_barcode'];
                $update['is_cw']       = intval($_POST['is_cw']);
                $update['goods_storage_alarm']= $update_common['goods_storage_alarm'];
                $update['goods_spec']        = serialize(null);
                $update['goods_storage']     = intval($_POST['g_storage']);
                $update['goods_state']       = $update_common['goods_state'];
                $update['goods_verify']      = $update_common['goods_verify'];
                $update['goods_edittime']    = TIMESTAMP;
                $update['areaid_1']          = $update_common['areaid_1'];
                $update['areaid_2']          = $update_common['areaid_2'];
                $update['color_id']          = 0;
                $update['transport_id']      = $update_common['transport_id'];
                $update['goods_freight']     = $update_common['goods_freight'];
                $update['goods_vat']         = $update_common['goods_vat'];
                $update['goods_commend']     = $update_common['goods_commend'];
                $update['goods_stcids']      = $update_common['goods_stcids'];
                $update['is_virtual']        = $update_common['is_virtual'];
                $update['virtual_indate']    = $update_common['virtual_indate'];
                $update['virtual_limit']     = $update_common['virtual_limit'];
                $update['virtual_invalid_refund'] = $update_common['virtual_invalid_refund'];
                $update['is_fcode']          = $update_common['is_fcode'];
                $update['is_appoint']        = $update_common['is_appoint'];
                $update['is_presell']        = $update_common['is_presell'];
                $update['is_mode']          = $update_common['is_mode'];
                if($update['is_mode'] == 0){$update['goods_shipper_id'] = 0;}
                else {$update['goods_shipper_id']  = $update_common['goods_shipper_id'];}
                
                $update['goods_hs']          = $update_common['goods_hs'];
                $update['goods_tax']          = $update_common['goods_tax'];
                $update['goods_tax_rate']          = $update_common['goods_tax_rate'];
                // jinp170608 商品净重/毛重、件数
                $update['goods_weight']       = $update_common['goods_weight'];
                $update['goods_all_weight']       = $update_common['goods_all_weight'];
                $update['goods_packages']        = $update_common['goods_packages'];

                //商品阶梯价格
                $p_ladder_id = $_POST['ladder'];
                $update['p_ladder_id'] = $p_ladder_id;
				$goods_group_ladder = $goods_info['is_group_ladder'];
				if($p_ladder_id >0){
					if($goods_group_ladder <= 1){
						$update['is_group_ladder'] = 1;
					}
				}else{
					if($goods_group_ladder <= 1){
						$update['is_group_ladder'] = 0;
					}
				}
				//if($goods_group_ladder == 0){
					//if($p_ladder_id >0){
						//$update['is_group_ladder'] = 1;
					//}else{
						//$update['is_group_ladder'] = 0;
					//}
				//}
                //销售规格、最小单位、误差、进销存设置
                $update['xiao_gui']          = intval($_POST['xiao_gui']);
                $update['wu_gui']          = intval($_POST['wu_gui']);
                $update['danwei_id']          = intval($_POST['ladder1']);
                $update['is_jin']          = intval($_POST['is_jin']);
                if ($update_common['is_virtual'] == 1) {
                    $update['have_gift']    = 0;
                    $model_gift->delGoodsGift(array('goods_id' => $goods_id));
                }
                $update['is_own_shop']       = $update_common['is_own_shop'];
                // var_dump($update);die;
                $model_goods->editGoodsById($update, $goods_id);
		 // 生成商品二维码
                    //$PhpQRCode->set('date',WAP_SITE_URL . '/tmpl/product_detail.html?goods_id='.$goods_id);
                    $PhpQRCode->set('date',WAP_SITE_URL . '/goods/goodsDetail.shtml?goodsId='.$goods_id);
                    $PhpQRCode->set('pngTempName', $goods_id . '.png');
                    $PhpQRCode->init();
		
            } else {
                $insert = array();
                $insert['goods_commonid']    = $common_id;
                $insert['goods_kuajingD_id'] = $kuajing_id;
                $insert['goods_name']        = $update_common['goods_name'];
                // 额外送积分
                 $insert['points_send']        = $update_common['points_send']; 
                $insert['goods_jingle']      = $update_common['goods_jingle'];
                 $insert['deliverer_id']        = $update_common['deliverer_id'];
                $insert['store_id']          = $_SESSION['store_id'];
                $insert['store_name']        = $_SESSION['store_name'];
                $insert['gc_id']             = $update_common['gc_id'];
                $insert['gc_id_1']           = $update_common['gc_id_1'];
                $insert['gc_id_2']           = $update_common['gc_id_2'];
                $insert['gc_id_3']           = $update_common['gc_id_3'];
                $insert['brand_id']          = $update_common['brand_id'];
                //佣金
                $insert['commis_rate']       = $update_common['commis_rate'];
                $insert['goods_price']       = $update_common['goods_price'];
				$insert['goods_app_price']   = $update_common['goods_app_price'];
                $insert['goods_promotion_price']=$update_common['goods_price'];
                $insert['goods_marketprice'] = $update_common['goods_marketprice'];
                $insert['goods_costprice'] = $update_common['goods_costprice'];
                $insert['goods_presalenum'] = $update_common['goods_presalenum'];
                $insert['goods_serial']      = $update_common['goods_serial'];
                $insert['goods_barcode']      = $_POST['g_barcode'];
                $insert['is_cw']       = intval($_POST['is_cw']);
                $insert['goods_storage_alarm']= $update_common['goods_storage_alarm'];
                $insert['goods_spec']        = serialize(null);
                $insert['goods_storage']     = intval($_POST['g_storage']);
                $insert['goods_image']       = $update_common['goods_image'];
                $insert['goods_state']       = $update_common['goods_state'];
                $insert['goods_verify']      = $update_common['goods_verify'];
                $insert['goods_addtime']     = TIMESTAMP;
                $insert['goods_edittime']    = TIMESTAMP;
                $insert['areaid_1']          = $update_common['areaid_1'];
                $insert['areaid_2']          = $update_common['areaid_2'];
                $insert['color_id']          = 0;
                $insert['transport_id']      = $update_common['transport_id'];
                $insert['goods_freight']     = $update_common['goods_freight'];
                $insert['goods_vat']         = $update_common['goods_vat'];
                $insert['goods_commend']     = $update_common['goods_commend'];
                $insert['goods_stcids']      = $update_common['goods_stcids'];
                $insert['is_virtual']        = $update_common['is_virtual'];
                $insert['virtual_indate']    = $update_common['virtual_indate'];
                $insert['virtual_limit']     = $update_common['virtual_limit'];
                $insert['virtual_invalid_refund'] = $update_common['virtual_invalid_refund'];
                $insert['is_fcode']          = $update_common['is_fcode'];
                $insert['is_appoint']        = $update_common['is_appoint'];
                $insert['is_presell']        = $update_common['is_presell'];
                $insert['is_own_shop']       = $update_common['is_own_shop'];

                //商品阶梯价格
                $p_ladder_id = $_POST['ladder'];
                $insert['p_ladder_id'] = $p_ladder_id;
				if($p_ladder_id >0){
					$insert['is_group_ladder'] = 1;
				}else{
					$insert['is_group_ladder'] = 0;
				}
                 //销售规格、最小单位、误差、进销存设置
                 $insert['xiao_gui']          = intval($_POST['xiao_gui']);
                 $insert['wu_gui']          = intval($_POST['wu_gui']);
                $insert['danwei_id']          = intval($_POST['ladder1']);
                 $insert['is_jin']          = intval($_POST['is_jin']);

                $insert['is_mode']          = $update_common['is_mode'];
                if($insert['is_mode'] == 0){$insert['goods_shipper_id'] = 0;}
                else {$insert['goods_shipper_id']  = $update_common['goods_shipper_id'];}
                
                $insert['goods_hs']          = $update_common['goods_hs'];
                $insert['goods_tax']          = $update_common['goods_tax'];
                $insert['goods_tax_rate']          = $update_common['goods_tax_rate'];
                // jinp170608 商品净重/毛重、件数
                $insert['goods_weight']       = $update_common['goods_weight'];
                $insert['goods_all_weight']       = $update_common['goods_all_weight'];
                $insert['goods_packages']        = $update_common['goods_packages'];
                $goods_id = $model_goods->addGoods($insert);
            }
            $goodsid_array[] = intval($goods_id);
            $colorid_array[] = 0;
//            $model_type->addGoodsType($goods_id, $common_id, array('cate_id' => $_POST['cate_id'], 'type_id' => $_POST['type_id'], 'attr' => $_POST['attr']));
        }

        // 生成商品二维码
        if (!empty($goodsid_array)) {
            //QueueClient::push('createGoodsQRCode', array('store_id' => $_SESSION['store_id'], 'goodsid_array' => $goodsid_array));
             // 生成商品二维码
                    //$PhpQRCode->set('date',WAP_SITE_URL . '/tmpl/product_detail.html?goods_id='.$goods_id);
                    $PhpQRCode->set('date',WAP_SITE_URL . '/goods/goodsDetail.shtml?goodsId='.$goods_id);
                    $PhpQRCode->set('pngTempName', $goods_id . '.png');
                    $PhpQRCode->init();	
	}

        // 清理商品数据
//        $model_goods->delGoods(array('goods_id' => array('not in', $goodsid_array), 'goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id']));
        //编辑商品为已删除
        $model_goods->editGoods(array('is_deleted' => 1), array('goods_id' => array('not in', $goodsid_array), 'goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id']));
        // 清理商品图片表
        //$colorid_array = array_unique($colorid_array);
        //$model_goods->delGoodsImages(array('goods_commonid' => $common_id, 'color_id' => array('not in', $colorid_array)));
        // 更新商品默认主图
        $default_image_list = $model_goods->getGoodsImageList(array('goods_commonid' => $common_id, 'is_default' => 1), 'color_id,goods_image');
        if (!empty($default_image_list)) {
            foreach ($default_image_list as $val) {
                $model_goods->editGoods(array('goods_image' => $val['goods_image']), array('goods_commonid' => $common_id, 'color_id' => $val['color_id']));
            }
        }

        // 商品加入上架队列
        if (isset($_POST['starttime'])) {
            $selltime = strtotime($_POST['starttime']) + intval($_POST['starttime_H'])*3600 + intval($_POST['starttime_i'])*60;
            if ($selltime > TIMESTAMP) {
                $this->addcron(array('exetime' => $selltime, 'exeid' => $common_id, 'type' => 1), true);
            }
        }
        // 添加操作日志
        $this->recordSellerLog('编辑商品，平台货号：'.$common_id);

        if ($update_common['is_virtual'] == 1 || $update_common['is_fcode'] == 1 || $update_common['is_presell'] == 1) {
            // 如果是特殊商品清理促销活动，抢购、限时折扣、组合销售
            QueueClient::push('clearSpecialGoodsPromotion', array('goods_commonid' => $common_id, 'goodsid_array' => $goodsid_array));
        } else {
            // 更新商品促销价格
            QueueClient::push('updateGoodsPromotionPriceByGoodsCommonId', $common_id);
        }

        // 生成F码
        if ($update_common['is_fcode'] == 1) {
            QueueClient::push('createGoodsFCode', array('goods_commonid' => $common_id, 'fc_count' => intval($_POST['g_fccount']), 'fc_prefix' => $_POST['g_fcprefix']));
        }
        //S多规格 变成无规格后删除该商品的所有数据 0908MX
//        $spec = Model()->table('goods_common')->where(array('goods_commonid' => $common_id))->select();
//        if(count($colorid_array)==1 && $colorid_array[0]==0 && $spec[0]['spec_value']!='N;'){//判断提交时是否勾选了规格，以及表中存储的商品是否存在多种规格
//            $return = $model_goods->delGoodsonline(array('goods_commonid' => $common_id));
//        }else{
            $return = $model_goods->editGoodsCommon($update_common, array('goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id']));
//        }
        //E多规格
        if ($return) {
            //提交事务
            Model()->commit();
            showDialog(L('nc_common_op_succ'), $_POST['ref_url'], 'succ');
        } else {
            //回滚事务
            Model()->rollback();
            showDialog(L('store_goods_index_goods_edit_fail'), urlShop('store_goods_online', 'index'));
        }
    }

    /**
     * 编辑图片
     */
    public function edit_imageOp() {
        $common_id = intval($_GET['commonid']);
        if ($common_id <= 0) {
            showMessage(L('wrong_argument'), urlShop('seller_center'), 'html', 'error');
        }
        $model_goods = Model('goods');
        $common_list = $model_goods->getGoodeCommonInfoByID($common_id, 'store_id,goods_lock,spec_value,is_virtual,is_fcode,is_presell');
        if ($common_list['store_id'] != $_SESSION['store_id'] || $common_list['goods_lock'] == 1) {
            showMessage(L('wrong_argument'), urlShop('seller_center'), 'html', 'error');
        }
        
        $spec_value = unserialize($common_list['spec_value']);
        Tpl::output('value', $spec_value['1']);

        $image_list = $model_goods->getGoodsImageList(array('goods_commonid' => $common_id));
        $image_list = array_under_reset($image_list, 'color_id', 2);

        $img_array = $model_goods->getGoodsList(array('goods_commonid' => $common_id,'is_deleted'=>0), 'color_id,goods_image', 'color_id');
        // 整理，更具id查询颜色名称
        if (!empty($img_array)) {
            foreach ($img_array as $val) {
                if (isset($image_list[$val['color_id']])) {
                    $image_array[$val['color_id']] = $image_list[$val['color_id']];
                } else {
                    $image_array[$val['color_id']][0]['goods_image'] = $val['goods_image'];
                    $image_array[$val['color_id']][0]['is_default'] = 1;
                }
                $colorid_array[] = $val['color_id'];
            }
        }
        Tpl::output('img', $image_array);


        $model_spec = Model('spec');
        $value_array = $model_spec->getSpecValueList(array('sp_value_id' => array('in', $colorid_array), 'store_id' => $_SESSION['store_id']), 'sp_value_id,sp_value_name');
        if (empty($value_array)) {
            $value_array[] = array('sp_value_id' => '0', 'sp_value_name' => '无颜色');
        }
        Tpl::output('value_array', $value_array);

        Tpl::output('commonid', $common_id);

        $menu_promotion = array(
                'lock' => $common_list['goods_lock'] == 1 ? true : false,
                'gift' => $model_goods->checkGoodsIfAllowGift($common_list),
                'combo' => $model_goods->checkGoodsIfAllowCombo($common_list)
        );
        $this->profile_menu('edit_detail', 'edit_image', $menu_promotion);
        Tpl::output('edit_goods_sign', true);
        Tpl::showpage('store_goods_add.step3');
    }

    /**
     * 保存商品图片
     */
    public function edit_save_imageOp() {
        if (chksubmit()) {
            $common_id = intval($_POST['commonid']);
            if ($common_id <= 0 || empty($_POST['img'])) {
                showDialog(L('wrong_argument'), urlShop('store_goods_online', 'index'));
            }
            $model_goods = Model('goods');
            // 删除原有图片信息
            $model_goods->delGoodsImages(array('goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id']));
            // 保存
            $insert_array = array();
            foreach ($_POST['img'] as $key => $value) {
                foreach ($value as $v) {
                    if ($v['name'] == '') {
                        continue;
                    }
                    //$k = 0;
                    // 商品默认主图
                    $update_array = array();        // 更新商品主图
                    $update_where = array();
                    $update_array['goods_image']    = $v['name'];
                    $update_where['goods_commonid'] = $common_id;
                    $update_where['store_id']       = $_SESSION['store_id'];
                    $update_where['color_id']       = $key;
                    if ($k == 0 || $v['default'] == 1) {
                        $k++;
                        $update_array['goods_image']    = $v['name'];
                        $update_where['goods_commonid'] = $common_id;
                        $update_where['store_id']       = $_SESSION['store_id'];
                        $update_where['color_id']       = $key;
                        // 更新商品主图
                        $model_goods->editGoods($update_array, $update_where);
                    }
                    $tmp_insert = array();
                    $tmp_insert['goods_commonid']   = $common_id;
                    $tmp_insert['store_id']         = $_SESSION['store_id'];
                    $tmp_insert['color_id']         = $key;
                    $tmp_insert['goods_image']      = $v['name'];
                    $tmp_insert['goods_image_sort'] = ($v['default'] == 1) ? 0 : $v['sort'];
                    $tmp_insert['is_default']       = $v['default'];
                    $insert_array[] = $tmp_insert;
                }
            }
            $rs = $model_goods->addGoodsImagesAll($insert_array);
            if ($rs) {
            // 添加操作日志
            $this->recordSellerLog('编辑商品，平台货号：'.$common_id);
                showDialog(L('nc_common_op_succ'), $_POST['ref_url'], 'succ');
            } else {
                showDialog(L('nc_common_save_fail'), urlShop('store_goods_online', 'index'));
            }
        }
    }

    /**
     * 编辑分类
     */
    public function edit_classOp() {
        // 实例化商品分类模型
        $model_goodsclass = Model('goods_class');
        // 商品分类
        $goods_class = $model_goodsclass->getGoodsClass($_SESSION['store_id']);

        // 常用商品分类
        $model_staple = Model('goods_class_staple');
        $param_array = array();
        $param_array['member_id'] = $_SESSION['member_id'];
        $staple_array = $model_staple->getStapleList($param_array);

        Tpl::output('staple_array', $staple_array);
        Tpl::output('goods_class', $goods_class);

        Tpl::output('commonid', $_GET['commonid']);
        $this->profile_menu('edit_class', 'edit_class');
        Tpl::output('edit_goods_sign', true);
        Tpl::showpage('store_goods_add.step1');
    }

    /**
     * 删除商品
     */
    public function drop_goodsOp() {
        $common_id = $this->checkRequestCommonId($_GET['commonid']);
        $commonid_array = explode(',', $common_id);
        $model_goods = Model('goods');
        $where = array();
        $where['goods_commonid'] = array('in', $commonid_array);
        $where['store_id'] = $_SESSION['store_id'];
        $return = $model_goods->delGoodsNoLock($where);
        if ($return) {
            // 添加操作日志
            $this->recordSellerLog('删除商品，平台货号：'.$common_id);
            showDialog(L('store_goods_index_goods_del_success'), 'reload', 'succ');
        } else {
            showDialog(L('store_goods_index_goods_del_fail'), '', 'error');
        }
    }

    /**
     * 商品下架
     */
    public function goods_unshowOp() {
        $common_id = $this->checkRequestCommonId($_GET['commonid']);
        $commonid_array = explode(',', $common_id);
        $model_goods = Model('goods');
        $where = array();
        $where['goods_commonid'] = array('in', $commonid_array);
        $where['store_id'] = $_SESSION['store_id'];
        $return = Model('goods')->editProducesOffline($where);
        if ($return) {
            // 更新优惠套餐状态关闭
            $goods_list = $model_goods->getGoodsList($where, 'goods_id');
            if (!empty($goods_list)) {
                $goodsid_array = array();
                foreach ($goods_list as $val) {
                    $goodsid_array[] = $val['goods_id'];
                }
                Model('p_bundling')->editBundlingCloseByGoodsIds(array('goods_id' => array('in', $goodsid_array)));
            }
            // 添加操作日志
            $this->recordSellerLog('商品下架，平台货号：'.$common_id);
            showDialog(L('store_goods_index_goods_unshow_success'), getReferer() ? getReferer() : 'index.php?act=store_goods_online&op=goods_list', 'succ', '', 2);
        } else {
            showDialog(L('store_goods_index_goods_unshow_fail'), '', 'error');
        }
    }

    /**
     * 设置广告词
     */
    public function edit_jingleOp() {
        if (chksubmit()) {
            $common_id = $this->checkRequestCommonId($_POST['commonid']);
            $commonid_array = explode(',', $common_id);
            $where = array('goods_commonid' => array('in', $commonid_array), 'store_id' => $_SESSION['store_id']);
            $update = array('goods_jingle' => trim($_POST['g_jingle']));
            $return = Model('goods')->editProducesNoLock($where, $update);
            if ($return) {
                // 添加操作日志
                $this->recordSellerLog('设置广告词，平台货号：'.$common_id);
                showDialog(L('nc_common_op_succ'), 'reload', 'succ');
            } else {
                showDialog(L('nc_common_op_fail'), 'reload');
            }
        }
        $common_id = $this->checkRequestCommonId($_GET['commonid']);

        Tpl::showpage('store_goods_list.edit_jingle', 'null_layout');
    }

    /**
     * 设置关联版式
     */
    public function edit_plateOp() {
        if (chksubmit()) {
            $common_id = $this->checkRequestCommonId($_POST['commonid']);
            $commonid_array = explode(',', $common_id);
            $where = array('goods_commonid' => array('in', $commonid_array), 'store_id' => $_SESSION['store_id']);
            $update = array();
            $update['plateid_top']        = intval($_POST['plate_top']) > 0 ? intval($_POST['plate_top']) : '';
            $update['plateid_bottom']     = intval($_POST['plate_bottom']) > 0 ? intval($_POST['plate_bottom']) : '';
            $return = Model('goods')->editGoodsCommon($update, $where);
            if ($return) {
                // 添加操作日志
                $this->recordSellerLog('设置关联版式，平台货号：'.$common_id);
                showDialog(L('nc_common_op_succ'), 'reload', 'succ');
            } else {
                showDialog(L('nc_common_op_fail'), 'reload');
            }
        }
        $common_id = $this->checkRequestCommonId($_GET['commonid']);

        // 关联版式
        $plate_list = Model('store_plate')->getStorePlateList(array('store_id' => $_SESSION['store_id']), 'plate_id,plate_name,plate_position');
        $plate_list = array_under_reset($plate_list, 'plate_position', 2);
        Tpl::output('plate_list', $plate_list);

        Tpl::showpage('store_goods_list.edit_plate', 'null_layout');
    }
    /**
     * 批量设置是否开具发票
     */
    public function edit_invoiceOp() {
        // var_dump($_POST['plate_top']);die;
        if (chksubmit()) {
            // var_dump($_POST['plate_top']);die;
            $common_id = $this->checkRequestCommonId($_POST['commonid']);
            $commonid_array = explode(',', $common_id);
            $where = array('goods_commonid' => array('in', $commonid_array), 'store_id' => $_SESSION['store_id']);
            $update = array();
            $update['goods_vat']        = intval($_POST['invoice_top']);
            // $update['plateid_bottom']     = intval($_POST['plate_bottom']) > 0 ? intval($_POST['plate_bottom']) : '';
            $return = Model('goods')->editGoodsCommon($update, $where);
            $return2 = Model('goods')->editGoods($update, $where);
            // var_dump($return);die;
            if ($return) {
                // 添加操作日志
                $this->recordSellerLog('设置关联版式，平台货号：'.$common_id);
                showDialog(L('nc_common_op_succ'), 'reload', 'succ');
            } else {
                showDialog(L('nc_common_op_fail'), 'reload');
            }
        }
        $common_id = $this->checkRequestCommonId($_GET['commonid']);

        // 关联版式aaaaaaaaaaaaaadadwdaszcsaffaf
        $plate_list = Model('store_plate')->getStorePlateList(array('store_id' => $_SESSION['store_id']), 'plate_id,plate_name,plate_position');
        $plate_list = array_under_reset($plate_list, 'plate_position', 2);
        Tpl::output('plate_list', $plate_list);

        Tpl::showpage('store_goods_list.edit_invoice', 'null_layout');
    }
     /**
     * 批量设为参与会员折扣
     */
    public function edit_vippriceOp() {
        $common_id = $this->checkRequestCommonId($_GET['commonid']);
        $commonid_array = explode(',', $common_id);
        $model_goods = Model('goods');
        $where = array();
        $where['goods_commonid'] = array('in', $commonid_array);
        $where['store_id'] = $_SESSION['store_id'];
        $return = Model('goods')->editProducesvip($where);
        // var_dump($return);die;
        if ($return) {
            // 更新优惠套餐状态关闭
            $goods_list = $model_goods->getGoodsList($where, 'goods_id');
            if (!empty($goods_list)) {
                $goodsid_array = array();
                foreach ($goods_list as $val) {
                    $goodsid_array[] = $val['goods_id'];
                }
                Model('p_bundling')->editBundlingCloseByGoodsIds(array('goods_id' => array('in', $goodsid_array)));
            }
            // 添加操作日志
        
            showDialog('设置成功', getReferer() ? getReferer() : 'index.php?act=store_goods_online&op=goods_list', 'succ', '', 2);
        } else {
            showDialog('设置失败', '', 'error');
        }
    }
     /**
     * 批量取消商品会员折扣
     */
    public function cancel_vippriceOp() {
        $common_id = $this->checkRequestCommonId($_GET['commonid']);
        $commonid_array = explode(',', $common_id);
        $model_goods = Model('goods');
        $where = array();
        $where['goods_commonid'] = array('in', $commonid_array);
        $where['store_id'] = $_SESSION['store_id'];
        $return = Model('goods')->cancelProducesvip($where);
        // var_dump($return);die;
        if ($return) {
            // 更新优惠套餐状态关闭
            $goods_list = $model_goods->getGoodsList($where, 'goods_id');
            if (!empty($goods_list)) {
                $goodsid_array = array();
                foreach ($goods_list as $val) {
                    $goodsid_array[] = $val['goods_id'];
                }
                Model('p_bundling')->editBundlingCloseByGoodsIds(array('goods_id' => array('in', $goodsid_array)));
            }
            // 添加操作日志
        
            showDialog('取消成功', getReferer() ? getReferer() : 'index.php?act=store_goods_online&op=goods_list', 'succ', '', 2);
        } else {
            showDialog('取消失败', '', 'error');
        }
    }
    /**
     * 添加赠品
     */
    public function add_giftOp() {
        $common_id = intval($_GET['commonid']);
        if ($common_id <= 0) {
            showMessage(L('wrong_argument'), urlShop('seller_center'), 'html', 'error');
        }
        $model_goods = Model('goods');
        $goodscommon_info = $model_goods->getGoodeCommonInfoByID($common_id, 'store_id,goods_lock');
        if (empty($goodscommon_info) || $goodscommon_info['store_id'] != $_SESSION['store_id']) {
            showMessage(L('wrong_argument'), urlShop('seller_center'), 'html', 'error');
        }

        // 商品列表
        $goods_array = $model_goods->getGoodsListForPromotion(array('goods_commonid' => $common_id), '*', 0, 'gift');
        Tpl::output('goods_array', $goods_array);

        // 赠品列表
        $gift_list = Model('goods_gift')->getGoodsGiftList(array('goods_commonid' => $common_id));
        $gift_array = array();
        if (!empty($gift_list)) {
            foreach ($gift_list as $val) {
                $gift_array[$val['goods_id']][] = $val;
            }
        }
        Tpl::output('gift_array', $gift_array);
        $menu_promotion = array(
                'lock' => $goodscommon_info['goods_lock'] == 1 ? true : false,
                'gift' => $model_goods->checkGoodsIfAllowGift($goods_array[0]),
                'combo' => $model_goods->checkGoodsIfAllowCombo($goods_array[0])
        );
        $this->profile_menu('edit_detail', 'add_gift', $menu_promotion);
        Tpl::showpage('store_goods_edit.add_gift');
    }

    /**
     * 保存赠品
     */
    public function save_giftOp() {
        if (!chksubmit()) {
            showDialog(L('wrong_argument'));
        }
        $data = $_POST['gift'];
        $commonid = intval($_POST['commonid']);
        if ($commonid <= 0) {
            showDialog(L('wrong_argument'));
        }

        $model_goods = Model('goods');
        $model_gift = Model('goods_gift');

        // 验证商品是否存在
        $goods_list = $model_goods->getGoodsListForPromotion(array('goods_commonid' => $commonid, 'store_id' => $_SESSION['store_id']), 'goods_id', 0, 'gift');
        if (empty($goods_list)) {
            showDialog(L('wrong_argument'));
        }
        // 删除该商品原有赠品
        $model_gift->delGoodsGift(array('goods_commonid' => $commonid));
        // 重置商品礼品标记
        $model_goods->editGoods(array('have_gift' => 0), array('goods_commonid' => $commonid));
        // 商品id
        $goodsid_array = array();
        foreach ($goods_list as $val) {
            $goodsid_array[] = $val['goods_id'];
        }

        $insert = array();
        $update_goodsid = array();
        foreach ($data as $key => $val) {

            $owner_gid = intval($key);  // 主商品id
            // 验证主商品是否为本店铺商品,如果不是本店商品继续下一个循环
            if (!in_array($owner_gid, $goodsid_array)) {
                continue;
            }
            $update_goodsid[] = $owner_gid;
            foreach ($val as $k => $v) {
                $gift_gid = intval($k); // 礼品id
                // 验证赠品是否为本店铺商品，如果不是本店商品继续下一个循环
                $gift_info = $model_goods->getGoodsInfoByID($gift_gid, 'goods_name,store_id,goods_image,is_virtual,is_fcode,is_presell');
                $is_general = $model_goods->checkIsGeneral($gift_info);     // 验证是否为普通商品
                if ($gift_info['store_id'] != $_SESSION['store_id'] || $is_general == false) {
                    continue;
                }

                $array = array();
                $array['goods_id'] = $owner_gid;
                $array['goods_commonid'] = $commonid;
                $array['gift_goodsid'] = $gift_gid;
                $array['gift_goodsname'] = $gift_info['goods_name'];
                $array['gift_goodsimage'] = $gift_info['goods_image'];
                $array['gift_amount'] = intval($v);
                $insert[] = $array;
            }
        }
        // 插入数据
        if (!empty($insert)) $model_gift->addGoodsGiftAll($insert);
        // 更新商品赠品标记
        if (!empty($update_goodsid)) $model_goods->editGoodsById(array('have_gift' => 1), $update_goodsid);
        showDialog(L('nc_common_save_succ'), $_POST['ref_url'], 'succ');
    }

    /**
     * 推荐搭配
     */
    public function add_comboOp() {
        $common_id = intval($_GET['commonid']);
        if ($common_id <= 0) {
            showMessage(L('wrong_argument'), urlShop('seller_center'), 'html', 'error');
        }
        $model_goods = Model('goods');
        $goodscommon_info = $model_goods->getGoodeCommonInfoByID($common_id, 'store_id,goods_lock');
        if (empty($goodscommon_info) || $goodscommon_info['store_id'] != $_SESSION['store_id']) {
            showMessage(L('wrong_argument'), urlShop('seller_center'), 'html', 'error');
        }

        $goods_array = $model_goods->getGoodsListForPromotion(array('goods_commonid' => $common_id), '*', 0, 'combo');
        Tpl::output('goods_array', $goods_array);

        // 推荐组合商品列表
        $combo_list = Model('goods_combo')->getGoodsComboList(array('goods_commonid' => $common_id));
        $combo_goodsid_array = array();
        if (!empty($combo_list)) {
            foreach ($combo_list as $val) {
                $combo_goodsid_array[] = $val['combo_goodsid'];
            }
        }

        $combo_goods_array = $model_goods->getGeneralGoodsList(array('goods_id' => array('in', $combo_goodsid_array)), 'goods_id,goods_name,goods_image,goods_price');
        $combo_goods_list = array();
        if (!empty($combo_goods_array)) {
            foreach ($combo_goods_array as $val) {
                $combo_goods_list[$val['goods_id']] = $val;
            }
        }

        $combo_array = array();
        foreach ($combo_list as $val) {
            $combo_array[$val['goods_id']][] = $combo_goods_list[$val['combo_goodsid']];
        }
        Tpl::output('combo_array', $combo_array);

        $menu_promotion = array(
                'lock' => $goodscommon_info['goods_lock'] == 1 ? true : false,
                'gift' => $model_goods->checkGoodsIfAllowGift($goods_array[0]),
                'combo' => $model_goods->checkGoodsIfAllowCombo($goods_array[0])
        );
        $this->profile_menu('edit_detail', 'add_combo', $menu_promotion);
        Tpl::showpage('store_goods_edit.add_combo');
    }

    /**
     * 保存赠品
     */
    public function save_comboOp() {
        if (!chksubmit()) {
            showDialog(L('wrong_argument'));
        }
        $data = $_POST['combo'];
        $commonid = intval($_POST['commonid']);
        if ($commonid <= 0) {
            showDialog(L('wrong_argument'));
        }

        $model_goods = Model('goods');
        $model_combo = Model('goods_combo');

        // 验证商品是否存在
        $goods_list = $model_goods->getGoodsListForPromotion(array('goods_commonid' => $commonid, 'store_id' => $_SESSION['store_id']), 'goods_id', 0, 'combo');
        if (empty($goods_list)) {
            showDialog(L('wrong_argument'));
        }
        // 删除该商品原有赠品
        $model_combo->delGoodsCombo(array('goods_commonid' => $commonid));
        // 商品id
        $goodsid_array = array();
        foreach ($goods_list as $val) {
            $goodsid_array[] = $val['goods_id'];
        }

        $insert = array();
        if (!empty($data)) {
            foreach ($data as $key => $val) {
    
                $owner_gid = intval($key);  // 主商品id
                // 验证主商品是否为本店铺商品,如果不是本店商品继续下一个循环
                if (!in_array($owner_gid, $goodsid_array)) {
                    continue;
                }
                $val = array_unique($val);
                foreach ($val as $v) {
                    $combo_gid = intval($v); // 礼品id
                    // 验证推荐组合商品是否为本店铺商品，如果不是本店商品继续下一个循环
                    $combo_info = $model_goods->getGoodsInfoByID($combo_gid, 'store_id,is_virtual,is_fcode,is_presell');
                    $is_general = $model_goods->checkIsGeneral($combo_info);     // 验证是否为普通商品
                    if ($combo_info['store_id'] != $_SESSION['store_id'] || $is_general == false || $owner_gid ==$combo_gid) {
                        continue;
                    }
    
                    $array = array();
                    $array['goods_id'] = $owner_gid;
                    $array['goods_commonid'] = $commonid;
                    $array['combo_goodsid'] = $combo_gid;
                    $insert[] = $array;
                }
            }
            // 插入数据
            $model_combo->addGoodsComboAll($insert);
        }
        showDialog(L('nc_common_save_succ'), $_POST['ref_url'], 'succ');
    }

    /**
     * 搜索商品（添加赠品/推荐搭配)
     */
    public function search_goodsOp() {
        $where = array();
        $where['store_id'] = $_SESSION['store_id'];
        // if ($_POST['name']) {
        //     $where['goods_name'] = array('like', '%'. $_POST['name'] .'%');
        // }
         if ($_GET['name']) {
            $where['goods_name'] = array('like', '%'. $_GET['name'] .'%');
        }
        $model_goods = Model('goods');
        $goods_list = $model_goods->getGeneralGoodsList($where, '*', 5);
        Tpl::output('show_page', $model_goods->showpage(2));
        Tpl::output('goods_list', $goods_list);
        Tpl::showpage('store_goods_edit.search_goods', 'null_layout');
    }
    
    /**
     * 下载F码
     */
    public function download_f_code_excelOp() {
        $common_id = $_GET['commonid'];
        if ($common_id <= 0) {
            showMessage(L('wrong_argument'), '', '', 'error');
        }
        $common_info = Model('goods')->getGoodeCommonInfoByID($common_id);
        if (empty($common_info) || $common_info['store_id'] != $_SESSION['store_id']) {
            showMessage(L('wrong_argument'), '', '', 'error');
        }
        import('libraries.excel');
        $excel_obj = new Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
        //header
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'号码');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'使用状态');
        $data = Model('goods_fcode')->getGoodsFCodeList(array('goods_commonid' => $common_id));
        foreach ($data as $k=>$v){
            $tmp = array();
            $tmp[] = array('data'=>$v['fc_code']);
            $tmp[] = array('data'=>$v['fc_state'] ? '已使用' : '未使用');
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset($common_info['goods_name'],CHARSET));
        $excel_obj->generateXML($excel_obj->charset($common_info['goods_name'],CHARSET).'-'.date('Y-m-d-H',time()));
    }

    /**
     * 验证commonid
     */
    private function checkRequestCommonId($common_ids) {
        if (!preg_match('/^[\d,]+$/i', $common_ids)) {
            showDialog(L('para_error'), '', 'error');
        }
        return $common_ids;
    }

    /**
     * ajax获取商品列表
     */
    public function get_goods_list_ajaxOp() {
        $common_id = $_GET['commonid'];
        if ($common_id <= 0) {
            echo 'false';exit();
        }
        $model_goods = Model('goods');
        $goodscommon_list = $model_goods->getGoodeCommonInfoByID($common_id, 'spec_name,store_id');
        if (empty($goodscommon_list) || $goodscommon_list['store_id'] != $_SESSION['store_id']) {
            echo 'false';exit();
        }
        $goods_list = $model_goods->getGoodsList(array('store_id' => $_SESSION['store_id'], 'goods_commonid' => $common_id,'is_deleted'=>0), 'goods_id,goods_spec,store_id,goods_price,goods_serial,goods_barcode,goods_storage_alarm,goods_storage,goods_image,is_group_ladder');
        if (empty($goods_list)) {
            echo 'false';exit();
        }

        $spec_name = array_values((array)unserialize($goodscommon_list['spec_name']));
        foreach ($goods_list as $key => $val) {
            $goods_spec = array_values((array)unserialize($val['goods_spec']));
            $spec_array = array();
            foreach ($goods_spec as $k => $v) {
                $spec_array[] = '<div class="goods_spec">' . $spec_name[$k] . L('nc_colon') . '<em title="' . $v . '">' . $v .'</em>' . '</div>';
            }
            $goods_list[$key]['goods_image'] = thumb($val, '60');
            $goods_list[$key]['goods_spec'] = implode('', $spec_array);
            $goods_list[$key]['alarm'] = ($val['goods_storage_alarm'] != 0 && $val['goods_storage'] <= $val['goods_storage_alarm']) ? 'style="color:red;"' : '';
            $goods_list[$key]['url'] = urlShop('goods', 'index', array('goods_id' => $val['goods_id']));
            switch ($val['is_group_ladder']){
                case 0:
                    $goods_list[$key]['p_name'] = '无活动';
                    break;
                case 1:
                    $goods_list[$key]['p_name'] = '阶梯价';
                    break;
                case 2:
                    $goods_list[$key]['p_name'] = '团购';
                    break;
                case 3:
                    $goods_list[$key]['p_name'] = '新人专享';
                    break;
                case 4:
                    $goods_list[$key]['p_name'] = '限时秒杀';
                    break;
                case 5:
                    $goods_list[$key]['p_name'] = '即买即送';
                    break;
            }
		}

        /**
         * 转码
         */
        if (strtoupper(CHARSET) == 'GBK') {
            Language::getUTF8($goods_list);
        }
        echo json_encode($goods_list);
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @param boolean $allow_promotion
     * @return
     */
    private function profile_menu($menu_type,$menu_key, $allow_promotion = array()) {
        $menu_array = array();
        switch ($menu_type) {
            case 'goods_list':
                $menu_array = array(
                   array('menu_key' => 'goods_list',    'menu_name' => '出售中的商品', 'menu_url' => urlShop('store_goods_online', 'index')),
                    array('menu_key' => 'setting',    'menu_name' => '库存同步设置', 'menu_url' => urlShop('store_goods_online', 'setting'))
                );
                break;
            case 'edit_detail':
                if ($allow_promotion['lock'] === false) {
                    $menu_array = array(
                        array('menu_key' => 'edit_detail',  'menu_name' => '编辑商品', 'menu_url' => urlShop('store_goods_online', 'edit_goods', array('commonid' => $_GET['commonid'], 'ref_url' => $_GET['ref_url']))),
                        array('menu_key' => 'edit_image',   'menu_name' => '编辑图片', 'menu_url' => urlShop('store_goods_online', 'edit_image', array('commonid' => $_GET['commonid'], 'ref_url' => ($_GET['ref_url'] ? $_GET['ref_url'] : getReferer())))),
                    );
                }
                // if ($allow_promotion['gift']) {
                //     $menu_array[] = array('menu_key' => 'add_gift', 'menu_name' => '赠送赠品', 'menu_url' => urlShop('store_goods_online', 'add_gift', array('commonid' => $_GET['commonid'], 'ref_url' => ($_GET['ref_url'] ? $_GET['ref_url'] : getReferer()))));
                // }
                // if ($allow_promotion['combo']) {
                //     $menu_array[] = array('menu_key' => 'add_combo', 'menu_name' => '推荐组合', 'menu_url' => urlShop('store_goods_online', 'add_combo', array('commonid' => $_GET['commonid'], 'ref_url' => ($_GET['ref_url'] ? $_GET['ref_url'] : getReferer()))));
                // }
                break;
            case 'edit_class':
                $menu_array = array(
                    array('menu_key' => 'edit_class',   'menu_name' => '选择分类', 'menu_url' => urlShop('store_goods_online', 'edit_class', array('commonid' => $_GET['commonid'], 'ref_url' => $_GET['ref_url']))),
                    array('menu_key' => 'edit_detail',  'menu_name' => '编辑商品', 'menu_url' => urlShop('store_goods_online', 'edit_goods', array('commonid' => $_GET['commonid'], 'ref_url' => $_GET['ref_url']))),
                    array('menu_key' => 'edit_image',   'menu_name' => '编辑图片', 'menu_url' => urlShop('store_goods_online', 'edit_image', array('commonid' => $_GET['commonid'], 'ref_url' => ($_GET['ref_url'] ? $_GET['ref_url'] : getReferer())))),
                );
                break;
        }
        Tpl::output ( 'member_menu', $menu_array );
        Tpl::output ( 'menu_key', $menu_key );
    }
	//批量生成二维码
	public function maker_qrcodeOp()
	{
	header("Content-Type: text/html; charset=utf-8");
		echo '正在生成，请耐心等待...';
		echo '<br/>';
		$store_id=$_SESSION['store_id'];
        require_once(BASE_RESOURCE_PATH.DS.'phpqrcode'.DS.'index.php');
        $PhpQRCode = new PhpQRCode();
        $PhpQRCode->set('pngTempDir',BASE_UPLOAD_PATH.DS.ATTACH_STORE.DS.$_SESSION['store_id'].DS);
		//print_r($PhpQRCode);
		$model_goods = Model('goods');
		$where=array();
	    $where['store_id'] = $store_id;
		//$count=$model_goods->getGoodsCount($where);
		$lst=$model_goods->getGoodsList($where,'goods_id');
		if(empty($lst))
		{
			echo '未找到商品信息';
			retrun;
		}
		foreach($lst as $k=>$v)
		{
			$goods_id=$v['goods_id'];
			//$qrcode_url=WAP_SITE_URL . '/tmpl/product_detail.html?goods_id='.$goods_id;
            $qrcode_url=WAP_SITE_URL . '/goods/goodsDetail.shtml?goodsId='.$goods_id;
			$PhpQRCode->set('date',$qrcode_url);
			$PhpQRCode->set('pngTempName', $goods_id . '.png');
			$PhpQRCode->init();
			echo '生成成功'.$qrcode_url;
			echo '<br/>';
		}
		
		//生成店铺二维码
		//$qrcode_url=WAP_SITE_URL . '/tmpl/product_store.html?store_id='.$store_id;
        $qrcode_url=WAP_SITE_URL . '/goods/store.shtml?storeId='.$store_id;
		$PhpQRCode->set('date',$qrcode_url);
		$PhpQRCode->set('pngTempName', $store_id . '_store.png');
		$PhpQRCode->init();
		echo '生成店铺二维码成功'.$qrcode_url;
		echo '<br/>';
		echo '<br/><b>全部生成完成</b>';
		
		
		
		
		
	}
    /**
     * 简单修改商品
     */
    public function edit_goods_simpleOp() {
        $model_goods = Model('goods');
        if (chksubmit()) {
            $common_id = $this->checkRequestCommonId($_POST['commonid']);
            $spec_value = $_POST['spec'];
            if (is_array($spec_value) && !empty($spec_value)) {
                foreach ($spec_value as $key => $value) {
            $goods_info = $model_goods->getGoodsInfo(array('goods_commonid'=>$common_id));
            if($goods_info){
                        $update = array('goods_serial' => $value['serial'],'goods_barcode' => $value['barcode'], 'goods_storage' => $value['storage'],'p_ladder_id'=>$value['ladder']);
						$goods_group_ladder = $model_goods->getfby_goods_id($value['goods_id'],'is_group_ladder');
						if($value['ladder'] >0){
							if($goods_group_ladder <= 1){
								$update['is_group_ladder'] = 1;
							}
						}else{
							if($goods_group_ladder <= 1){
								$update['is_group_ladder'] = 0;
							}
						}
						//if($goods_group_ladder == 0){
							//if($value['ladder'] >0){
								//$update['is_group_ladder'] = 1;
							//}else{
								//$update['is_group_ladder'] = 0;
							//}
						//}
                        $result = $model_goods->editGoodsById($update, $value['goods_id']);
                if($result){
                            $this->recordSellerLog('修改商品货号、库存，平台货号：' . $common_id . '，商品ID为' . $value['goods_id'] . '。库存修改前为' . $goods_info['goods_storage'] . '，修改后为' . $value['storage'] . '。');
                }else{
                    showDialog('修改失败', 'reload');
                }
            }else{
                showDialog('商品信息不存在', 'reload');
            }
        }
                if($result){
                    showDialog(L('nc_common_op_succ'), 'reload', 'succ');
                }
            }
        }
        $common_id = $this->checkRequestCommonId($_GET['commonid']);
        if ($common_id <= 0) {
            showMessage(L('wrong_argument'), '', 'html', 'error');
        }
        $goodscommon_info = $model_goods->getGoodeCommonInfoByID($common_id);
        if (empty($goodscommon_info) || $goodscommon_info['store_id'] != $_SESSION['store_id'] || $goodscommon_info['goods_lock'] == 1) {
            showMessage(L('wrong_argument'), '', 'html', 'error');
        }

        $goods_list = $model_goods->getGoodsList(array('goods_commonid' => $common_id,'is_deleted'=>0), 'goods_spec,goods_storage,goods_serial,goods_barcode,goods_id,goods_name,p_ladder_id');
        if (count($goods_list) > 1) {
            if ($goods_list) {
                foreach ($goods_list as $k => $v) {
                    $goods_list[$k]['spec_id'] = array_keys(unserialize($v['goods_spec']))[0];
                    $goods_list[$k]['spec_name'] = array_values(unserialize($v['goods_spec']))[0];
                    unset($goods_list[$k]['goods_spec']);
                }
            }
        }
        $ladder_list = Model('p_ladder')->getMansongList(['store_id'=>$_SESSION['store_id']],'','','p_ladder_id,p_name');
        Tpl::output('ladder_list', $ladder_list);
        Tpl::output('goods_list', $goods_list);
        Tpl::showpage('store_goods_list.edit_goods', 'null_layout');
    }
	
	/**
     * 根据活动批量设置关联版式
     */
    public function edit_plate_activeOp() {
        if (chksubmit()) {
            $model_goods = Model('goods');
            $is_group_ladder = $_POST['active'];
            $goods_common_id = $model_goods->getGoodsList(array('is_group_ladder'=>$is_group_ladder),'goods_commonid');
            if(is_array($goods_common_id)){
                foreach ($goods_common_id as $key => $value) {
                    $common_id[] = $value['goods_commonid'];
                }
            }
            $where = array('goods_commonid' => array('in', $common_id), 'store_id' => $_SESSION['store_id']);
            $update = array();
            $update['plateid_top']        = intval($_POST['plate_top']) > 0 ? intval($_POST['plate_top']) : '';
            $update['plateid_bottom']     = intval($_POST['plate_bottom']) > 0 ? intval($_POST['plate_bottom']) : '';
            $return = $model_goods->editGoodsCommon($update, $where);

            if ($return) {
                // 添加操作日志
                $this->recordSellerLog('批量设置关联版式，活动类型：'.$is_group_ladder);
                showDialog(L('nc_common_op_succ'), 'reload', 'succ');
            } else {
                showDialog(L('nc_common_op_fail'), 'reload');
            }
        }

        //商品活动类型
        $active_list = array(
            '0'=>'无活动',
            '1'=>'阶梯价',
            '2'=>'团购',
            '3'=>'新人专享',
            '4'=>'限时秒杀',
            '5'=>'即买即送',
        );
        Tpl::output('active_list', $active_list);

        // 关联版式
        $plate_list = Model('store_plate')->getStorePlateList(array('store_id' => $_SESSION['store_id']), 'plate_id,plate_name,plate_position');
        $plate_list = array_under_reset($plate_list, 'plate_position', 2);
        Tpl::output('plate_list', $plate_list);

        Tpl::showpage('store_goods_list.edit_plate_active', 'null_layout');
    }

    /**
     * 图片下载
     */
    public function download_proOp()
    {
        $model_goods = Model('goods');
        $goods_commonid = $_GET['goods_commonid'];
        if(!$goods_commonid){
            showDialog('请选择需要操作的记录！','','fail');
        }
        $goods_commonid_arr = explode(',', $goods_commonid);
        if (is_array($goods_commonid_arr)) {
            $ii = 0;
            foreach ($goods_commonid_arr as $key => $value) {
                //通过goods_commonid-$value在goods_common表里获取所有规格id-color_id和名称
                $goods_common_info = $model_goods->getGoodeCommonInfo(array('store_id' => $_SESSION['store_id'], 'goods_commonid' => $value));
                $goods_name = iconv('utf-8', 'gbk//IGNORE', $goods_common_info['goods_name']);
                $spec_value = unserialize($goods_common_info['spec_value']);
                $goods_mobile_body = unserialize($model_goods->table('goods_common')->getfby_goods_commonid($value,'mobile_body'));
                $file_name_tmp0 = str_replace('/',' ',$goods_name);
                if(is_array($goods_mobile_body)){
                    foreach($goods_mobile_body as $km=>$vm){
                        $img1[$km]['name'] = iconv('utf-8', 'gbk//IGNORE', '详情_').str_replace('/',' ',$goods_name).'/'.$file_name_tmp0;
                        $img1[$km]['url'] = $vm['value'];
                    }
                }
                if ($spec_value) {
                    foreach ($spec_value as $k => $v) {
                        foreach ($v as $kk => $vv) {
                            //再通过goods_commonid和color_id在goods_images表里获取对应的图片名称和store_id，拼接成url
                            $goods_image_list = $model_goods->getGoodsImageList(array('goods_commonid' => $value, 'store_id' => $_SESSION['store_id'], 'color_id' => $kk));
                            if ($goods_image_list) {
                                foreach ($goods_image_list as $vl) {
                                    $file_name = $goods_name . iconv('utf-8', 'gbk//IGNORE', $vv);
                                    $file_name_tmp = str_replace('/',' ',$file_name);
                                    if($vl['is_default']==1){
                                        $file_name_tmp = iconv('utf-8','GBK//IGNORE','主图_').$file_name_tmp ;
                                    }
                                    $img[$ii]['name'] = str_replace('/',' ',$goods_name).'/'.$file_name_tmp;
                                    $img[$ii]['url'] = UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/' . $vl['store_id'] . '/' . $vl['goods_image'];
                                    $ii ++;
                                }
                            }
                        }
                    }
                }else{
                    $goods_image_list = $model_goods->getGoodsImageList(array('goods_commonid' => $value, 'store_id' => $_SESSION['store_id'], 'color_id' => 0));
                    if ($goods_image_list) {
                        foreach ($goods_image_list as $vl) {
//                            $file_name = $goods_name . iconv('utf-8', 'gbk//IGNORE', $vv);
                            $file_name_tmp = str_replace('/',' ',$goods_name);
                            if($vl['is_default']==1){
                                $file_name_tmp = iconv('utf-8','GBK//IGNORE','主图_').$file_name_tmp ;
                            }
                            $img[$ii]['name'] = str_replace('/',' ',$goods_name).'/'.$file_name_tmp;
                            $img[$ii]['url'] = UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/' . $vl['store_id'] . '/' . $vl['goods_image'];
                            $ii ++;
                        }
                    }
                }
            }
            $imgs = array_merge($img,$img1);
            $model_zipfile = Model('zipfile');
            return $model_zipfile->download($imgs);
        }
    }

    /**
     * 图片下载优化
     */
    public function downloadOp()
    {
        $model_goods = Model('goods');
        $goods_commonid = $_GET['goods_commonid'];
        if(!$goods_commonid){
            showDialog('请选择需要操作的记录！','','fail');
        }
        $goods_commonid_arr = explode(',', $goods_commonid);
        $img1 = array();
        $img = array();
        if (is_array($goods_commonid_arr)) {
            $ii = 0;
            foreach ($goods_commonid_arr as $key => $value) {
                //通过goods_commonid-$value在goods_common表里获取所有规格id-color_id和名称
                $goods_common_info = $model_goods->getGoodeCommonInfo(array('store_id' => $_SESSION['store_id'], 'goods_commonid' => $value));
                $goods_name = iconv('utf-8', 'gbk//IGNORE', $goods_common_info['goods_name']);
                $spec_value = unserialize($goods_common_info['spec_value']);
                $goods_mobile_body = unserialize($model_goods->table('goods_common')->getfby_goods_commonid($value,'mobile_body'));
                $file_name_tmp0 = str_replace('/',' ',$goods_name);
                if(is_array($goods_mobile_body)){
                    foreach($goods_mobile_body as $km=>$vm){
                        $img1[$key.$km]['name'] = str_replace('/',' ',$goods_name).'/'.iconv('utf-8', 'gbk//IGNORE', '详情').'/'.$file_name_tmp0;
                        $img1[$key.$km]['url'] = $vm['value'];
                    }
                }
                if ($spec_value) {
                    foreach ($spec_value as $k => $v) {
                        foreach ($v as $kk => $vv) {
                            //再通过goods_commonid和color_id在goods_images表里获取对应的图片名称和store_id，拼接成url
                            $goods_image_list = $model_goods->getGoodsImageList(array('goods_commonid' => $value, 'store_id' => $_SESSION['store_id'], 'color_id' => $kk));
                            if ($goods_image_list) {
                                foreach ($goods_image_list as $vl) {
                                    $file_name = $goods_name . iconv('utf-8', 'gbk//IGNORE', $vv);
                                    $file_name_tmp = str_replace('/',' ',$file_name);
                                    if($vl['is_default']==1){
                                        $file_name_tmp = iconv('utf-8','GBK//IGNORE','主图_').$file_name_tmp ;
                                    }
                                    $img[$ii]['name'] = str_replace('/',' ',$goods_name).'/'.iconv('utf-8', 'gbk//IGNORE', '主图').'/'.iconv('utf-8', 'gbk//IGNORE', str_replace('/','每',$vv)).'/'.$file_name_tmp;
                                    $img[$ii]['url'] = UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/' . $vl['store_id'] . '/' . $vl['goods_image'];
                                    $ii ++;
                                }
                            }
                        }
                    }
                }else{
                    $goods_image_list = $model_goods->getGoodsImageList(array('goods_commonid' => $value, 'store_id' => $_SESSION['store_id'], 'color_id' => 0));
                    if ($goods_image_list) {
                        foreach ($goods_image_list as $vl) {
//                            $file_name = $goods_name . iconv('utf-8', 'gbk//IGNORE', $vv);
                            $file_name_tmp = str_replace('/',' ',$goods_name);
                            if($vl['is_default']==1){
                                $file_name_tmp = iconv('utf-8','GBK//IGNORE','主图_').$file_name_tmp ;
                            }
                            $img[$ii]['name'] = str_replace('/',' ',$goods_name).'/'.iconv('utf-8', 'gbk//IGNORE', '主图').'/'.$file_name_tmp;
                            $img[$ii]['url'] = UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/' . $vl['store_id'] . '/' . $vl['goods_image'];
                            $ii ++;
                        }
                    }
                }
            }
            $imgs = array_merge($img,$img1);
            $model_zipfile = Model('zipfile');
            return $model_zipfile->download($imgs);
        }
    }

    /**
     * 库存同步-商品列表
     */
    public function storage_synchro_goodsOp()
    {
        $goods_commonid = $_GET['goods_commonid'];
        if (!$goods_commonid) {
            showDialog('参数错误！', '', 'error');
        }
        $model_goods = Model('goods');
        $goods_commonid_array = explode(',', $goods_commonid);
//        $goods_serial_list = $model_goods->getGoodsList(array('goods_commonid'=>array('in',$goods_commonid_array)),'goods_serial as goodsCode');
        $goods_serial_list = $model_goods->getGoodsList(array('goods_commonid'=>array('in',$goods_commonid_array)),'goods_id,goods_serial as goodsCode');
        if($goods_serial_list){
            foreach ($goods_serial_list as $key=>$value){
                if(!$value['goodsCode']){
                    $goods_serial_list[$key]['goodsCode'] = $model_goods->getfby_goods_id($value['goods_id'],'goods_barcode');
                }
                unset($goods_serial_list[$key]['goods_id']);
            }
        }

        $model_cw = Model('cw');
        $result = $model_cw->cwPlatGoodsSyn(42, $goods_serial_list);
        //$result_data = json_decode($result,true);
        if($result['code'] == 0){
            if(is_array($result['data']) && count($result['data'])>0){
                $goods_serial = [];
                foreach ($result['data'] as $item){
                    $res = $model_goods->editGoods(array('goods_storage'=>$item['saleInventory']),array('goods_serial'=>$item['goodsCode']));
                    if($res) {
                        $goods_serial[] = $item['goodsCode'];
                    }
                }
                $this->recordSellerLog('同步商品库存，商家货号：'.implode(',',$goods_serial));
                showDialog('商品货号'.implode(',',$goods_serial).'同步成功！', '', 'notice');
            }
        }else{
            showDialog($result['msg'], '', 'error');
        }
    }
	
    public function settingOp(){
        $model_setting = Model('setting');
        $auto_cw = $model_setting->getRowSetting('auto_cw');
        if(chksubmit()){
            $auto_cw = $_POST['auto_cw'];
            $param = array('auto_cw'=>$auto_cw);
            $result = $model_setting->updateSetting($param);
            if($result){
                $_POST['auto_cw'] == 1?$state = '开启':$state = '关闭';
                $this->recordSellerLog($state.'云仓库存同步设置');
                showDialog('提交成功！', 'reload', 'succ');
            }else{
                showDialog('提交失败！', 'reload', 'fail');
            }
        }
        Tpl::output('auto_cw', $auto_cw['value']);
        $this->profile_menu('goods_list', 'setting');
        Tpl::showpage('store_goods_storage.setting');

    }

    /**
     * 全部商品图片下载
     */
    /**public function downloadAllOp()
    {
        $model_goods = Model('goods');
        //$goods_commonid_arr = $model_goods->getGoodsCommonList([],'goods_commonid');
        //$goods_commonid_arr = $model_goods->getGoodsCommonList(array(),'goods_commonid',0);
        $goods_commonid_arr = Model()->table('goods_common')->field('goods_commonid')->where()->limit('20000')->select();
		$date = date("y-m");
            $dateday = date("y-m-d");
            $path = '../logsmx/' . $date . '/';
            if (!is_dir($path)) {
            mkdir($path, 0777, true);
            }
            $filename = $path . $dateday . ".txt";
            if (file_exists($filename)) {
            $content = file_get_contents($filename);
            $content = $content . "\r\n------------------------\r\n" .json_encode($goods_commonid_arr);
            file_put_contents($filename, $content);
            } else {
            file_put_contents($filename, json_encode($goods_commonid_arr));
            }
        if (is_array($goods_commonid_arr)) {
            $ii = 0;
            foreach ($goods_commonid_arr as $key => $value) {
                //通过goods_commonid-$value在goods_common表里获取所有规格id-color_id和名称
                $goods_common_info = $model_goods->getGoodeCommonInfo(array('store_id' => $_SESSION['store_id'], 'goods_commonid' => $value['goods_commonid']));
                $goods_name = iconv('utf-8', 'gbk//IGNORE', $goods_common_info['goods_name']);
                $spec_value = unserialize($goods_common_info['spec_value']);
                $goods_mobile_body = unserialize($model_goods->table('goods_common')->getfby_goods_commonid($value['goods_commonid'],'mobile_body'));
                $file_name_tmp0 = str_replace('/',' ',$goods_name);
                if(is_array($goods_mobile_body)){
                    foreach($goods_mobile_body as $km=>$vm){
                        $img1[$km]['name'] = iconv('utf-8', 'gbk//IGNORE', '详情_').str_replace('/',' ',$goods_name).'/'.$file_name_tmp0;
                        $img1[$km]['url'] = $vm['value'];
                    }
                }
                if ($spec_value) {
                    foreach ($spec_value as $k => $v) {
                        foreach ($v as $kk => $vv) {
                            //再通过goods_commonid和color_id在goods_images表里获取对应的图片名称和store_id，拼接成url
                            $goods_image_list = $model_goods->getGoodsImageList(array('goods_commonid' => $value['goods_commonid'], 'store_id' => $_SESSION['store_id'], 'color_id' => $kk));
                            if ($goods_image_list) {
                                foreach ($goods_image_list as $vl) {
                                    $file_name = $goods_name . iconv('utf-8', 'gbk//IGNORE', $vv);
                                    $file_name_tmp = str_replace('/',' ',$file_name);
                                    if($vl['is_default']==1){
                                        $file_name_tmp = iconv('utf-8','GBK//IGNORE','主图_').$file_name_tmp ;
                                    }
                                    $img[$ii]['name'] = str_replace('/',' ',$goods_name).'/'.$file_name_tmp;
                                    $img[$ii]['url'] = UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/' . $vl['store_id'] . '/' . $vl['goods_image'];
                                    $ii ++;
                                }
                            }
                        }
                    }
                }else{
                    $goods_image_list = $model_goods->getGoodsImageList(array('goods_commonid' => $value['goods_commonid'], 'store_id' => $_SESSION['store_id'], 'color_id' => 0));
                    if ($goods_image_list) {
                        foreach ($goods_image_list as $vl) {
//                            $file_name = $goods_name . iconv('utf-8', 'gbk//IGNORE', $vv);
                            $file_name_tmp = str_replace('/',' ',$goods_name);
                            if($vl['is_default']==1){
                                $file_name_tmp = iconv('utf-8','GBK//IGNORE','主图_').$file_name_tmp ;
                            }
                            $img[$ii]['name'] = str_replace('/',' ',$goods_name).'/'.$file_name_tmp;
                            $img[$ii]['url'] = UPLOAD_SITE_URL . '/' . ATTACH_GOODS . '/' . $vl['store_id'] . '/' . $vl['goods_image'];
                            $ii ++;
                        }
                    }
                }
            }
            $imgs = array_merge($img,$img1);
            $model_zipfile = Model('zipfile');
            return $model_zipfile->download($imgs);
        }
    }
	**/

}
