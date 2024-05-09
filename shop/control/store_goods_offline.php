<?php
/**
 * 商品管理
 *
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
class store_goods_offlineControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct ();
        Language::read ('member_store_goods_index');
    }
    public function indexOp() {
        $this->goods_storageOp();
    }

    /**
     * 仓库中的商品列表
     */
    public function goods_storageOp() {
        $model_goods = Model('goods');

        $where = array();
        $where['store_id'] = $_SESSION['store_id'];
       /* if (intval($_GET['stc_id']) > 0) {
            $where['goods_stcids'] = array('like', '%,' . intval($_GET['stc_id']) . ',%');
        }*/
        /**
         * 处理商品分类
         */
        $choose_gcid = ($t = intval($_REQUEST['choose_gcid']))>0?$t:0;
        $gccache_arr = Model('goods_class')->getGoodsclassCache($choose_gcid,3);
        Tpl::output('gc_json',json_encode($gccache_arr['showclass']));
        Tpl::output('gc_choose_json',json_encode($gccache_arr['choose_gcid']));
        if ($choose_gcid > 0){
          $where['gc_id_'.($gccache_arr['showclass'][$choose_gcid]['depth'])] = $choose_gcid;
        }
         //供货商
        $seller_group_id = Model()->table('seller')->getfby_member_id($_SESSION['member_id'],"seller_group_id");
        Tpl::output('seller_group_id',$seller_group_id);
        if (!empty($_GET['address_id'])) {
           $where['deliverer_id']=intval($_GET['address_id']);
        }
        if($seller_group_id == 46){
            $address_id = Model()->table('seller')->getfby_member_id($_SESSION['member_id'],"address_id");
            $where['deliverer_id']=$address_id;
        }
        /*if (trim($_GET['keyword']) != '') {
            switch ($_GET['search_type']) {
                case 0:
                    $where['goods_name'] = array('like', '%' . trim($_GET['keyword']) . '%');
                    break;
                case 1:
                    $where['goods_serial'] = array('like', '%' . trim($_GET['keyword']) . '%');
                    break;
                case 2:
                    $where['goods_commonid'] = intval($_GET['keyword']);
                    break;
            }
        }*/
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
        //lxs
        switch ($_GET['type']) {
            // 违规的商品
            case 'lock_up':
                $this->profile_menu('goods_lockup');
                $goods_list = $model_goods->getGoodsCommonLockUpList($where);
                break;
            // 等待审核或审核失败的商品
            case 'wait_verify':
                $this->profile_menu('goods_verify');
                if (isset($_GET['verify']) && in_array($_GET['verify'], array('0', '10'))) {
                    $where['goods_verify']  = $_GET['verify'];
                }
                $goods_list = $model_goods->getGoodsCommonWaitVerifyList($where);
                break;
            // 仓库中的商品
            default:
                $this->profile_menu('goods_storage');
                $goods_list = $model_goods->getGoodsCommonOfflineList($where);
                break;
        }

		if(is_array($goods_list)){
            foreach($goods_list as $k=>$v){
                $goods_list[$k]['seller_name'] = Model()->table('daddress')->getfby_address_id($v['deliverer_id'],'seller_name');
                $goods_list[$k]['is_cw'] = Model()->table('goods')->getfby_goods_commonid($v['goods_commonid'],'is_cw');
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

        //发货人
        $scondition['address_id'] = array('gt',0);
        if($seller_group_id == 46){
            $address_id = Model()->table('seller')->getfby_member_id($_SESSION['member_id'],"address_id");
            $_GET['address_id'] = $address_id;
            $scondition['address_id'] = $address_id;
        }
        $fahuo_list =Model()->table('daddress')->where($scondition)->select();
        Tpl::output('fahuo_list',$fahuo_list);

        switch ($_GET['type']) {
            // 违规的商品
            case 'lock_up':
                Tpl::showpage('store_goods_list.offline_lockup');
                break;
            // 等待审核或审核失败的商品
            case 'wait_verify':
                Tpl::output('verify', array('0' => '未通过', '10' => '等待审核'));
                Tpl::showpage('store_goods_list.offline_waitverify');
                break;
            // 仓库中的商品
            default:
                Tpl::showpage('store_goods_list.offline');
                break;
        }
    }
     public function export1Op() {
        $model_goods = Model('goods');

        $where = array();
        $where['store_id'] = $_SESSION['store_id'];
        if (intval($_GET['stc_id']) > 0) {
            $where['goods_stcids'] = array('like', '%,' . intval($_GET['stc_id']) . ',%');
        }
        /*if (trim($_GET['keyword']) != '') {
            switch ($_GET['search_type']) {
                case 0:
                    $where['goods_name'] = array('like', '%' . trim($_GET['keyword']) . '%');
                    break;
                case 1:
                    $where['goods_serial'] = array('like', '%' . trim($_GET['keyword']) . '%');
                    break;
                case 2:
                    $where['goods_commonid'] = intval($_GET['keyword']);
                    break;
            }
        }*/
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
        if (!is_numeric($_GET['curpage'])){
             //lxs
            switch ($_GET['type']) {
                // 违规的商品
                case 'lock_up':
                    $this->profile_menu('goods_lockup');
                    $goods_list = $model_goods->getGoodsCommonLockUpList1($where);
                    $this->createExcel($goods_list,$_GET['type']);
                    break;
                // 等待审核或审核失败的商品
                case 'wait_verify':
                    $this->profile_menu('goods_verify');
                    if (isset($_GET['verify']) && in_array($_GET['verify'], array('0', '10'))) {
                        $where['goods_verify']  = $_GET['verify'];
                    }
                    $goods_list = $model_goods->getGoodsCommonWaitVerifyList1($where);
                   $this->createExcel($goods_list,$_GET['type']);
                    break;
                // 仓库中的商品
                default:
                    $this->profile_menu('goods_storage');
                    $goods_list = $model_goods->getGoodsCommonOfflineList1($where);
                   $this->createExcel($goods_list,$_GET['type']);
                    break;
            }
                // $data = $model_goods->getGoodsCommonOnlineList($where,'*',0);
                // // var_dump($data);die;
                // $this->createExcel($data);
            //}
        }else{  //下载
            // echo'66677';die;
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
             //lxs
            switch ($_GET['type']) {
                // 违规的商品
                case 'lock_up':
                    $this->profile_menu('goods_lockup');
                    $goods_list = $model_goods->getGoodsCommonLockUpList($where);
                    $this->createExcel($goods_list,$_GET['type']);
                    break;
                // 等待审核或审核失败的商品
                case 'wait_verify':
                    $this->profile_menu('goods_verify');
                    if (isset($_GET['verify']) && in_array($_GET['verify'], array('0', '10'))) {
                        $where['goods_verify']  = $_GET['verify'];
                    }
                    $goods_list = $model_goods->getGoodsCommonWaitVerifyList($where);
                   $this->createExcel($goods_list,$_GET['type']);
                    break;
                // 仓库中的商品
                default:
                    $this->profile_menu('goods_storage');
                    $goods_list = $model_goods->getGoodsCommonOfflineList($where);
                    $this->createExcel($goods_list,$_GET['type']);
                    break;
            }
            // $data = $model_goods->getGoodsCommonOnlineList($where,'*',0);
            // $this->createExcel($data);
        }
    }
    /**
     * 生成excel
     *
     * @param array $data
     * @param varchar $type   lock_up:违规的商品,  wait_verify  等待审核或审核失败的商品  其他 仓库中的商品
     */
    private function createExcel1($data = array(),$type){
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT');
        $tableheader = array('商品分类', '商品名称', '商品卖点', '商品价格','成本价', '商品净重', '商品件数', '预设销量', '商品库存', '库存预警值', '商家货号', '运费');
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
            $excel->getActiveSheet()->getStyle("$letter[$i]1", "$tableheader[$i]")->getFont()->setBold(true);
        }

         foreach ((array)$data as $k=>$v){
            // var_dump($v);die;        
            $tmp = array();
            $goods_class = Model('goods_class')->getGoodsClassLineForTag($v['gc_id']);
            $where = array('goods_commonid' => $v['goods_commonid'], 'store_id' => $_SESSION['store_id']);
            $goods_storage =Model('goods')->getGoodsSum($where, 'goods_storage');
            $order_data[] = [
                   $goods_class['gc_tag_name'],$v['goods_name'],$v['goods_jingle'],$v['goods_price'],$v['goods_costprice'],$v['goods_weight'],$v['goods_packages'],$v['goods_presalenum'],
                    $goods_storage, $v['goods_storage_alarm'], $v['goods_serial'], $v['goods_freight'],
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
        //根据不同导出类型命名表格名称
        switch ($type) {
            // 违规的商品
            case 'lock_up':
                $filename = '违规商品-' . date('Y-m-d-H', time()) . '.xls';
                break;
            // 等待审核或审核失败的商品
            case 'wait_verify':
                $filename = ' 等待审核商品-' . date('Y-m-d-H', time()) . '.xls';
                break;
            // 仓库中的商品
            default:
                $filename = ' 仓库中商品-' . date('Y-m-d-H', time()) . '.xls';
                break;
            }
        
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
          //供货商
        if (!empty($_GET['address_id'])) {
           $where['deliverer_id']=intval($_GET['address_id']);
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
        switch ($_GET['type']) {
            // 违规的商品
            case 'lock_up':
                $this->profile_menu('goods_lockup');
                $where['goods_state'] = 10;
                $where['goods_verify'] = 1;
                break;
            // 等待审核或审核失败的商品
            case 'wait_verify':
                $this->profile_menu('goods_verify');
                $where['goods_verify']  = array('neq', 1);
                break;
            // 仓库中的商品
            default:
                $this->profile_menu('goods_storage');
                $where['goods_state'] = 0;
                $where['goods_verify'] = 1;
                break;
        }
        $goods_list = $model_goods->getGoodsAllList($where,'goods_commonid','','','20000');
        $goods_list_all = array();
        if($goods_list){
            foreach ($goods_list as $key=>$value){
                $goods_spec_list = $model_goods->getGoodsList(array('goods_commonid'=>$value['goods_commonid']),'gc_id_1,gc_id_2,gc_id_3,goods_name,goods_jingle,goods_price,goods_costprice,goods_weight,goods_presalenum,goods_serial,goods_storage,goods_storage_alarm,goods_spec,goods_freight,goods_packages,goods_barcode,deliverer_id');
                if($goods_spec_list){
                    foreach ($goods_spec_list as $k=>$v){
                        $goods_list_all[] = $v;
                    }
                }
            }
        }
//        echo '<pre>';var_dump($goods_list_all);die;
        $this->createExcel($goods_list_all,$_GET['type']);
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
    private function createExcel($data = array(),$type){
        $excel = new PHPExcel();
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT');
        $tableheader = array('商品分类1','商品分类2','商品分类3', '商品名称','商品规格', '商品卖点', '商品价格','成本价', '商品净重', '商品件数', '预设销量', '商品库存', '库存预警值', '商家货号', '商品编码', '运费','发货人');
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
                $goods_class1,$goods_class2,$goods_class3,$v['goods_name'],$goods_spec_info,$v['goods_jingle'],$v['goods_price'],$v['goods_costprice'],$v['goods_weight'],$v['goods_packages'],$v['goods_presalenum'],intval($v['goods_storage']), $v['goods_storage_alarm'], $v['goods_serial'], $v['goods_barcode'], $v['goods_freight'],$deliverer_name
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
        switch ($type) {
            // 违规的商品
            case 'lock_up':
                $filename = '违规商品-' . date('Y-m-d-H', time()) . '.xls';
                break;
            // 等待审核或审核失败的商品
            case 'wait_verify':
                $filename = ' 等待审核商品-' . date('Y-m-d-H', time()) . '.xls';
                break;
            // 仓库中的商品
            default:
                $filename = ' 仓库中商品-' . date('Y-m-d-H', time()) . '.xls';
                break;
        }
        //创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
//        $filename = '仓库中商品-' . date('Y-m-d-H', time()) . '.xls';
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
     * 商品上架
     */
    public function goods_showOp() {
        $commonid = $_GET['commonid'];
        if (!preg_match('/^[\d,]+$/i', $commonid)) {
            showDialog(L('para_error'), '', 'error');
        }
        $commonid_array = explode(',', $commonid);
        if ($this->store_info['store_state'] != 1) {
            showDialog(L('store_goods_index_goods_show_fail') . '，店铺正在审核中或已经关闭', '', 'error');
        }
        $return = Model('goods')->editProducesOnline(array('goods_commonid' => array('in', $commonid_array), 'store_id' => $_SESSION['store_id']));
        if ($return) {
            // 添加操作日志
            $this->recordSellerLog('商品上架，平台货号：'.$commonid);
            showDialog(L('store_goods_index_goods_show_success'), 'reload', 'succ');
        } else {
            showDialog(L('store_goods_index_goods_show_fail'), '', 'error');
        }
    }
    
    /**
     * 新品上架
     */
    public function goods_show_newOp() {
        $commonid = $_GET['commonid'];
        if (!preg_match('/^[\d,]+$/i', $commonid)) {
            showDialog(L('para_error'), '', 'error');
        }
        $commonid_array = explode(',', $commonid);
        if ($this->store_info['store_state'] != 1) {
            showDialog(L('store_goods_index_goods_show_fail') . '，店铺正在审核中或已经关闭', '', 'error');
        }
        $return = Model('goods')->editProducesNewOnline(array('goods_commonid' => array('in', $commonid_array), 'store_id' => $_SESSION['store_id']),$commonid);
        if ($return) {
            // 添加操作日志
            $this->recordSellerLog('商品新品上架，平台货号：'.$commonid);
            showDialog(L('store_goods_index_goods_show_success'), 'reload', 'succ');
        } else {
            showDialog(L('store_goods_index_goods_show_fail'), '', 'error');
        }
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key = '') {
        $menu_array = array(
            array('menu_key' => 'goods_storage',    'menu_name' => L('nc_member_path_goods_storage'),   'menu_url' => urlShop('store_goods_offline', 'index')),
            array('menu_key' => 'goods_lockup',     'menu_name' => L('nc_member_path_goods_state'),     'menu_url' => urlShop('store_goods_offline', 'index', array('type' => 'lock_up'))),
            array('menu_key' => 'goods_verify',     'menu_name' => L('nc_member_path_goods_verify'),    'menu_url' => urlShop('store_goods_offline', 'index', array('type' => 'wait_verify')))
        );
        Tpl::output ( 'member_menu', $menu_array );
        Tpl::output ( 'menu_key', $menu_key );
    }
}
