<?php
/**
 * 商品管理
 *
 *
 *
 ***/


defined('In718Shop') or exit ('Access Invalid!');
class store_kuajingControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct ();
        Language::read ('member_store_goods_index');
    }
    public function indexOp() {
        $this->edit_beihuoOp();
    }

/**
     * 电子口岸参数页面
     */
    public function D_canshuOp() {

        $model_bhcs = Model ( 'ctax_shopemallyi' );
        //Tpl::output('companycode',1123);
        //Tpl::output('companycode',$model_bhcs -> where(array('shop_id'=>5)) ->select());
        Tpl::output('companycode',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'companycode'));
        Tpl::output('emallyikey',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'emallyikey'));
        Tpl::output('eccode',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'eccode'));
        Tpl::output('cbecode',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'cbecode'));
        Tpl::output('ecname',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'ecname'));
        Tpl::output('cbename',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'cbename'));
        Tpl::output('shipper',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'shipper'));
        Tpl::output('shipperaddress',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'shipperaddress'));
        Tpl::output('shippertelephone',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'shippertelephone'));
        Tpl::output('shippercountryciq',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'shippercountryciq'));
        Tpl::output('shippercountrycus',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'shippercountrycus'));
        Tpl::output('agentcodeciq',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'agentcodeciq'));
        Tpl::output('agentcodecus',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'agentcodecus'));
        Tpl::output('agentnameciq',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'agentnameciq'));
        Tpl::output('agentnamecus',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'agentnamecus'));

        Tpl::showpage('store_kuajing_dianzikouan.list');
    }

    /**
     * 电子口岸参数保存
     */
    public function D_canshu_saveOp() {
     $model_bhcs = Model ( 'ctax_shopemallyi' );
         //$data = array ()
        $shop_id = $model_bhcs->getfby_shop_id($_SESSION ['store_id'], 'shop_id');
        $data = array();
        $data['companycode']         = $_POST['bh_companycode'];
        $data['emallyikey']       = $_POST['bh_emallyikey'];
        $data['eccode']       = $_POST['bh_eccode'];
        $data['cbecode']       = $_POST['bh_cbecode'];
        $data['ecname']       = $_POST['bh_ecname'];
        $data['cbename']       = $_POST['bh_cbename'];
        $data['shipper']       = $_POST['bh_shipper'];
        $data['shipperaddress']       = $_POST['bh_shipperaddress'];
        $data['shippertelephone']       = $_POST['bh_shippertelephone'];
        $data['shippercountryciq']       = $_POST['bh_shippercountryciq'];
        $data['shippercountrycus']       = $_POST['bh_shippercountrycus'];
        $data['agentcodeciq']       = $_POST['bh_agentcodeciq'];
        $data['agentcodecus']       = $_POST['bh_agentcodecus'];
        $data['agentnameciq']       = $_POST['bh_agentnameciq'];
        $data['agentnamecus']       = $_POST['bh_agentnamecus'];
        if ($shop_id) {
        $model_bhcs -> where(array('shop_id'=>$_SESSION ['store_id'])) -> update($data);

            showDialog('保存成功', 'reload', 'js');
        } else {
            $data['shop_id'] = $_SESSION ['store_id'];
            $model_bhcs->insert($data);
            showDialog('保存成功', 'reload', 'js');
        }
}


   

/*----------------------------------------------------------------------------------------------------
/*----------------------------------------------------------------------------------------------------
/*----------------------------------------------------------------------------------------------------
/*----------------------------------------------------------------------------------------------------
/*----------------------------------------------------------------------------------------------------
/*----------------------------------------------------------------------------------------------------

/**
     * 备货参数页面
     */
    public function edit_beihuoOp() {
        $common_id = $_GET['commonid'];
        
        $model_goods = Model('goods');
        $goodscommon_info = $model_goods->getGoodeCommonInfoByID($common_id);
        
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
        $goods_array = $model_goods->getGoodsList($where, 'goods_id,goods_marketprice,goods_price,goods_storage,goods_serial,goods_storage_alarm,goods_spec');
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
                    $sp_value ['i_' . $id . '|id'] = $v['goods_id'];
                    $sp_value ['i_' . $id . '|stock'] = $v['goods_storage'];
                    $sp_value ['i_' . $id . '|alarm'] = $v['goods_storage_alarm'];
                    $sp_value ['i_' . $id . '|sku'] = $v['goods_serial'];
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

        $model_bhcs = Model ( 'ctax_shopemallyi' );
        //Tpl::output('companycode',1123);
        //Tpl::output('companycode',$model_bhcs -> where(array('shop_id'=>5)) ->select());
        Tpl::output('companycode',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'companycode'));
        Tpl::output('emallyikey',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'emallyikey'));
        Tpl::output('eccode',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'eccode'));
        Tpl::output('cbecode',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'cbecode'));
        Tpl::output('ecname',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'ecname'));
        Tpl::output('cbename',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'cbename'));
        Tpl::output('shipper',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'shipper'));
        Tpl::output('shipperaddress',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'shipperaddress'));
        Tpl::output('shippertelephone',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'shippertelephone'));
        Tpl::output('shippercountryciq',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'shippercountryciq'));
        Tpl::output('shippercountrycus',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'shippercountrycus'));
        Tpl::output('agentcodeciq',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'agentcodeciq'));
        Tpl::output('agentcodecus',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'agentcodecus'));
        Tpl::output('agentnameciq',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'agentnameciq'));
        Tpl::output('agentnamecus',$model_bhcs->getfby_shop_id($_SESSION ['store_id'],'agentnamecus'));

        Tpl::showpage('store_goods_beihuo.list');
    }

    /**
     * 备货参数保存
     */
    public function edit_save_bhcsOp() {
     $model_bhcs = Model ( 'ctax_shopemallyi' );
         //$data = array ()

        $data = array();
        $data['companycode']         = $_POST['bh_companycode'];
        $data['emallyikey']       = $_POST['bh_emallyikey'];
        $data['eccode']       = $_POST['bh_eccode'];
        $data['cbecode']       = $_POST['bh_cbecode'];
        $data['ecname']       = $_POST['bh_ecname'];
        $data['cbename']       = $_POST['bh_cbename'];
        $data['shipper']       = $_POST['bh_shipper'];
        $data['shipperaddress']       = $_POST['bh_shipperaddress'];
        $data['shippertelephone']       = $_POST['bh_shippertelephone'];
        $data['shippercountryciq']       = $_POST['bh_shippercountryciq'];
        $data['shippercountrycus']       = $_POST['bh_shippercountrycus'];
        $data['agentcodeciq']       = $_POST['bh_agentcodeciq'];
        $data['agentcodecus']       = $_POST['bh_agentcodecus'];
        $data['agentnameciq']       = $_POST['bh_agentnameciq'];
        $data['agentnamecus']       = $_POST['bh_agentnamecus'];

        $model_bhcs -> where(array('shop_id'=>$_SESSION ['store_id'])) -> update($data);
        echo "<script type=\"text/javascript\">alert ('保存成功');</script>";
}



}
