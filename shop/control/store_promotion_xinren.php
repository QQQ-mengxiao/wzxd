<?php
/**
 * 用户中心-新人专享
 *
 *
 *
 ***/


defined('In718Shop') or exit('Access Invalid!');
class store_promotion_xinrenControl extends BaseSellerControl {

    // const LINK_XIANSHI_LIST = 'index.php?act=store_promotion_xinren&op=xinrengoods_list';
    // const LINK_XIANSHI_MANAGE = 'index.php?act=store_promotion_xinren&op=xianshi_manage&xianshi_id=';

    public function __construct() {
        parent::__construct();
        //检查是否开启
       // echo'zhi';die;
        if (intval(C('promotion_allow')) !== 1) {
            showMessage(Language::get('promotion_unavailable'),'index.php?act=store','','error');
        }
    }

    public function indexOp() {
        $this->xinrengoods_listOp();
    }
     /**
     * 新人专享商品列表管理
     **/
    public function xinrengoods_listOp() {

        $model_xinren_goods = Model('p_xinren_goods');
        //获取新人专享商品列表
        $condition = array();
        //对活动商品搜索
        if(!empty($_GET['goods_name'])) {
            $condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        }
        $condition['store_id'] = $_SESSION['store_id'];
//        $xinren_goods_list = $model_xinren_goods->getXinRenGoodsExtendList($condition,5);
        $xinren_goods_list = $model_xinren_goods->getXinRenGoodsExtendList($condition,5,'goods_sort asc','','',$_GET['goods_serial']);
        Tpl::output('xinren_goods_list', $xinren_goods_list);

        //输出导航
        self::profile_menu('xianshi_manage');
        Tpl::output('show_page',$model_xinren_goods->showpage(2));
        Tpl::showpage('store_promotion_xinren.goods');
    }
   

    /**
     * 选择活动商品
     **/
    public function goods_selectOp() {
        $model_goods = Model('goods');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['is_deleted'] = 0;
        if($_GET['goods_name']) {
            $condition['goods_name'] = array('like', '%' . $_GET['goods_name'] . '%');
        }
        if($_GET['goods_serial']) {
            $condition['goods_serial'] = array('like', '%' . $_GET['goods_serial'] . '%');
        }
        //$condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        //$condition['goods_serial'] = $_GET['goods_serial'];
        $goods_list = $model_goods->getGoodsListForPromotion($condition, '*', 10, 'xinren');
        //addnew
        
        $goodspromotion_arr=array();
        foreach ($goods_list as $key => $value) {
            $model_goodspromotion=Model('goods_promotion'); 
            $condition=array();
            $condition['goods_id']= $value['goods_id'];
            $goodspromotion = $model_goodspromotion->getgoods_promotionList($condition,'promotion_type');
            $goodspromotion_arr=array_unique(array_column($goodspromotion,'promotion_type'));
            foreach ($goodspromotion_arr as $k => $v) {
               $goodspromotion_arr[$k]=promotion_typeName($v);
            }
            $goods_list[$key ]['goods_promotion']=implode(',',$goodspromotion_arr);
        }
        //addnew
        Tpl::output('goods_list', $goods_list);
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::showpage('store_promotion_xinren.addgoods', 'null_layout');
    }

    /**
     * 新人专享商品添加
     **/
    public function xinren_goods_addOp() {
        $goods_id = intval($_POST['goods_id']);
        $xinren_price = floatval($_POST['xinren_price']);
        $goods_sort = floatval($_POST['goods_sort']);
		//$xinren_app_price = floatval($_POST['xinren_app_price']);

        $model_goods = Model('goods');
        $model_xinren_goods = Model('p_xinren_goods');

        $data = array();
        $data['result'] = true;

        $goods_info = $model_goods->getGoodsInfoByID($goods_id);
        if(empty($goods_info) || $goods_info['store_id'] != $_SESSION['store_id']) {
            $data['result'] = false;
            $data['message'] = '参数错误';
            echo json_encode($data);die;
        }

         //检查商品是否已经参加专享活动
        $condition = array();
        $condition['goods_id'] = $goods_id;
        $xinren_goods = $model_xinren_goods->getXinRenGoodsExtendList($condition);
        // if(!empty($xianshi_goods)&&$xianshi_goods['end_time']>TIMESTAMP) {
        if(!empty($xinren_goods)) {
            $data['result'] = false;
            $data['message'] = '该商品已经参加了新人专享活动';
            echo json_encode($data);die;
        }

        //添加到活动商品表
        $param = array();
        $param['goods_id'] = $goods_info['goods_id'];
        $param['store_id'] = $goods_info['store_id'];
        $param['goods_name'] = $goods_info['goods_name'];
        $param['goods_price'] = $goods_info['goods_price'];
        $param['gc_id_1'] = $goods_info['gc_id_1'];
        $param['xinren_goods_tax'] = $goods_info['goods_tax'];
		//市场价gc_id_1
		//$param['goods_marketprice'] = $goods_info['goods_marketprice'];
        $param['xinren_price'] = $xinren_price;
        $param['goods_sort'] = $goods_sort;
		//$param['xinren_app_price'] = $xinren_app_price;
        $param['goods_image'] = $goods_info['goods_image'];
        // $param['start_time'] = $xianshi_info['start_time'];
        // $param['end_time'] = $xianshi_info['end_time'];
        // $param['lower_limit'] = $xianshi_info['lower_limit'];
         $param['lower_limit'] = 1;

        $result = array();
        $xinren_goods_info = $model_xinren_goods->addXinRenGoods($param);
        if($xinren_goods_info) {
            //add商品活动表
            $model_goodspromotion=Model('goods_promotion'); 
            $array_goodspromotion=array();
            $array_goodspromotion['goods_id']=$goods_info['goods_id'];
            $array_goodspromotion['promotion_type']=50;
            $array_goodspromotion['price']= $xinren_price;
            $array_goodspromotion['promotion_type_id']=$xinren_goods_info['xinren_goods_id'];
            $model_goodspromotion->addgoods_promotion($array_goodspromotion);
            //add商品活动表
            $result['result'] = true;
            $data['message'] = '添加成功';
            $data['xinren_goods'] = $xinren_goods_info;
            // 自动发布动态
            // goods_id,store_id,goods_name,goods_image,goods_price,goods_freight,xianshi_price
            $data_array = array();
            $data_array['goods_id']         = $goods_info['goods_id'];
            $data_array['store_id']         = $_SESSION['store_id'];
            $data_array['goods_name']       = $goods_info['goods_name'];
            $data_array['goods_image']      = $goods_info['goods_image'];
            $data_array['goods_price']      = $goods_info['goods_price'];
			//市场价
			//$data_array['goods_marketprice'] = $goods_info['goods_marketprice'];
            $data_array['goods_freight']    = $goods_info['goods_freight'];
            $data_array['xinren_price']    = $xinren_price;
			//$data_array['xinren_app_price']    = $xinren_app_price;
            $this->storeAutoShare($data_array, 'xinren');
            $this->recordSellerLog('添加新人专享商品，商品名称：'.$goods_info['goods_name']);

            // 添加任务计划
            // $this->addcron(array('type' => 2, 'exeid' => $goods_info['goods_id'], 'exetime' => $param['start_time']));
        } else {
            $data['result'] = false;
            $data['message'] = '新增新人专享商品失败';
        }
        echo json_encode($data);die;
    }

    /**
     * 新人专享商品价格修改
     **/
    public function xinren_goods_price_editOp() {

        $xinren_goods_id = intval($_POST['xinren_goods_id']);
        $xinren_price = floatval($_POST['xinren_price']);
		//$xinren_app_price = floatval($_POST['xinren_app_price']);

        $data = array();
        $data['result'] = true;

        $model_xinren_goods = Model('p_xinren_goods');

        $xinren_goods_info = $model_xinren_goods->getXinRenGoodsInfoByID($xinren_goods_id, $_SESSION['store_id']);
        if(!$xinren_goods_info) {
            $data['result'] = false;
           //$data['message'] = '参数错误';
            $data['message'] = '参数错误';
            echo json_encode($data);die;
        }

        $update = array();
        $update['xinren_price'] = $xinren_price;
		//$update['xinren_app_price'] = $xinren_app_price;
        $condition = array();
        $condition['xinren_goods_id'] = $xinren_goods_id;
        $result = $model_xinren_goods->editXinRenGoods($update, $condition);
        if($result) {
             //多活动商品活动编辑
            $model_goodspromotion=Model('goods_promotion'); 
            $array_goodspromotion=array();
            $array_goodspromotion['promotion_type_id']=$xinren_goods_id;
            $array_goodspromotion['promotion_type']=50;
            $update_goodspromotion=array();
            $update_goodspromotion['price']=$xinren_price;
            $model_goodspromotion->editgoods_promotion($update_goodspromotion,$array_goodspromotion);
   //          $xinren_goods_info['xinren_price'] = $xinren_price;
			// //$xinren_goods_info['xinren_app_price'] = $xinren_app_price;
   //          $xinren_goods_info = $model_xinren_goods->getXianshiGoodsExtendInfo($xianshi_goods_info);
            $data['xinren_price'] = $xinren_goods_info['xinren_price'];
			//$data['xinren_app_price'] = $xinren_goods_info['xinren_app_price'];
            $data['xinren_discount'] = $xinren_goods_info['xinren_discount'];

   //          // 添加对列修改商品促销价格
   //          QueueClient::push('updateGoodsPromotionPriceByGoodsId', $xianshi_goods_info['goods_id']);

            // $this->recordSellerLog('新品专享价格修改为：'.$xinren_goods_info['xinren_price'].'，限时折扣App端价格修改为：'.$xianshi_goods_info['xianshi_app_price'].'，商品名称：'.$xianshi_goods_info['goods_name']);
            $this->recordSellerLog('新品专享价格修改为：'.$xinren_goods_info['xinren_price'].'，商品名称：'.$xinren_goods_info['goods_name']);
        } else {
            $data['result'] = false;
            $data['message'] = '操作成功';
        }
        echo json_encode($data);die;
    }

    /**
     * 新人专享商品删除
     **/
    public function xinren_goods_deleteOp() {
        $model_xinren_goods = Model('p_xinren_goods');
        $data = array();
        $data['result'] = true;

        $xinren_goods_id = intval($_POST['xinren_goods_id']);
        $xinren_goods_info = $model_xinren_goods->getXinRenGoodsInfoByID($xinren_goods_id);
        if(!$xinren_goods_info) {
            $data['result'] = false;
            $data['message'] = '参数错误，无此专享商品信息';
            echo json_encode($data);die;
        }

        if(!$model_xinren_goods->delXinRenGoods(array('xinren_goods_id'=>$xinren_goods_id))) {
            $data['result'] = false;
            $data['message'] = '新人专享活动商品删除失败';
            echo json_encode($data);die;
        }
          //del商品活动表
            $model_goodspromotion=Model('goods_promotion'); 
            $array_goodspromotion=array();
            $array_goodspromotion['goods_id']=$xinren_goods_info['goods_id'];
            $array_goodspromotion['promotion_type']=50;
            $model_goodspromotion->delgoods_promotion($array_goodspromotion);
            //adel商品活动表
        $this->recordSellerLog('删除新人专享商品，商品名称：'.$xinren_goods_info['goods_name']);
        echo json_encode($data);die;
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string	$menu_type	导航类型
     * @param string 	$menu_key	当前导航的menu_key
     * @param array 	$array		附加菜单
     * @return
     */
    private function profile_menu($menu_key='') {
        // $menu_array = array(
        //     1=>array('menu_key'=>'xinren_list','menu_name'=>Language::get('promotion_active_list'),'menu_url'=>'index.php?act=store_promotion_xinren&op=xinrengoods_list'),
        // );
        switch ($menu_key){
        	/*case 'xinren_add':
                $menu_array[] = array('menu_key'=>'xinren_add','menu_name'=>Language::get('promotion_join_active'),'menu_url'=>'index.php?act=store_promotion_xianshi&op=xianshi_add');
        		break;
        	case 'xianshi_edit':
                $menu_array[] = array('menu_key'=>'xianshi_edit','menu_name'=>'编辑活动','menu_url'=>'javascript:;');
        		break;
        	case 'xianshi_quota_add':
                $menu_array[] = array('menu_key'=>'xianshi_quota_add','menu_name'=>Language::get('promotion_buy_product'),'menu_url'=>'index.php?act=store_promotion_xianshi&op=xianshi_quota_add');
        		break;*/
        	case 'xianshi_manage':
                $menu_array[] = array('menu_key'=>'xianshi_manage','menu_name'=>'商品管理','menu_url'=>'index.php?act=store_promotion_xinren&op=xinrengoods_list');
        		break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }

    public function goods_sortOp(){
        $xinren_goods_id = $_POST['xinren_goods_id'];
        if(!$xinren_goods_id){
            showDialog('参数错误！');
        }
        $model_xinren_goods = Model('p_xinren_goods');
        $goods_sort = $model_xinren_goods->getXinRenGoodsInfoByID($xinren_goods_id,$_SESSION['store_id']);
        $update = $model_xinren_goods->editXinRenGoods(array('goods_sort' => $_POST['goods_sort']), array('xinren_goods_id' => $xinren_goods_id));
        $this->recordSellerLog('修改新人活动商品排序，id：' . $xinren_goods_id . '修改之前为' . $goods_sort['goods_sort'] . '修改之后为' . $_POST['goods_sort']);
        if ($update) {
            die(json_encode(array('code' => 1, 'msg' => '序号修改成功！')));
        } else {
            die(json_encode(array('code' => 0, 'msg' => '序号修改失败！')));
        }
    }

    public function xinren_goods_add_allOp(){
        $goods_ids = $_GET['goods_id'];
        $goods_id_arr = explode(',',$goods_ids);
        array_pop($goods_id_arr);
        $goods_list = Model('goods')->getGoodsList(array('goods_id'=>array('in',$goods_id_arr)),'goods_id,goods_name,goods_price,goods_image');
        Tpl::output('xianshi_id', $_GET['xianshi_id']);
        Tpl::output('goods_list', $goods_list);
        Tpl::showpage('store_promotion_xinren.goods_add','null_layout');
    }

    public function xinren_goods_add_all_saveOp(){
        $goods_list = $_POST['goods'];
        if($goods_list){
            if(is_array($goods_list)){
                $model_xinren_goods = Model('p_xinren_goods');
                $model_goods = Model('goods');
                foreach ($goods_list as $key=>$value){
                    $goods_info = $model_goods->getGoodsInfoByID($key);
                    if(empty($goods_info) || $goods_info['store_id'] != $_SESSION['store_id']) {
                        showDialog('参数错误！');
                        break;
                    }
                    $condition = array();
                    $condition['goods_id'] = $key;
                    $xinren_goods = $model_xinren_goods->getXinRenGoodsExtendList($condition);
                    if(!empty($xinren_goods)) {
                        showDialog($value['goods_name'].'已经参加了新人专享活动！');
                        break;
                    }
                    $tmp = 1;
                    foreach($goods_list as $item) {
                        if($item['xinren_price'] == '') {
                            showDialog($item['goods_name'] . '的新人专享价格不能为空！');
                        }else{
                            if ($item['xinren_price'] > $item['goods_price']) {
                                showDialog($item['goods_name'] . '的新人专享价格不能高于当前售价！');
                                $tmp = 0;
                                break;
                            }
                        }
                    }
                    if($tmp == 0){
                        break;
                    }
                    $param = array();
                    $param['goods_id'] = $goods_info['goods_id'];
                    $param['store_id'] = $goods_info['store_id'];
                    $param['goods_name'] = $goods_info['goods_name'];
                    $param['goods_price'] = $goods_info['goods_price'];
                    $param['gc_id_1'] = $goods_info['gc_id_1'];
                    $param['xinren_goods_tax'] = $goods_info['goods_tax'];
                    $param['xinren_price'] = $value['xinren_price'];
                    $param['goods_sort'] = $value['goods_sort'];
                    $param['goods_image'] = $goods_info['goods_image'];
                    $param['lower_limit'] = 1;

                    $result = $model_xinren_goods->addXinRenGoods($param);
                    if($result){
                         //add商品活动表
                        $model_goodspromotion=Model('goods_promotion'); 
                        $array_goodspromotion=array();
                        $array_goodspromotion['goods_id']=$goods_info['goods_id'];
                        $array_goodspromotion['promotion_type']=50;
                        $array_goodspromotion['price']=  $param['xinren_price'];
                        $array_goodspromotion['promotion_type_id']=$result;
                        $model_goodspromotion->addgoods_promotion($array_goodspromotion);
                        //add商品活动表
                        $succ[] = $value['goods_name'];
                    } else {
                        $fail[] = $value['goods_name'];
                    }
                }
                $succ ? $succ = '，商品名称：' . implode(',', $succ) . '添加成功' : '';
                $fail ? $fail = '，商品名称：' . implode(',', $fail) . '添加失败。' : '';
                $msg = '添加新人专享'. $succ . $fail;
                showDialog($msg,'reload','notice');
                $this->recordSellerLog($msg);
            }
        }
    }
}
