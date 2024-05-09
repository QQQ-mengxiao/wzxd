<?php
/**
 * 商户中心-满就送
 *
 **/


defined('In718Shop') or exit('Access Invalid!');
class store_tuangouControl extends BaseSellerControl {

    public function __construct() {

        parent::__construct() ;

        Language::read('member_layout,promotion_mansong');

        //检查满就送是否开启
        if (intval(C('promotion_allow')) !== 1) {
            showMessage(Language::get('promotion_unavailable'),'index.php?act=seller_center','','error');
        }

    }

    /**
     * 限时折扣活动管理
     **/
    public function ladder_manageOp() {
        $model = Model('goods');
        $condition = array();
        $condition['is_group_ladder'] = 2;
        $condition['store_id'] = $_SESSION['store_id'];
        if(!empty($_GET['goods_name'])) {
            $condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        }
         $goods_list = $model->getGoodsList( $condition,'*','','',0,5);
        foreach ($goods_list as $key => $value) {
         $goods_list[$key]['goods_url'] = urlShop('goods', 'index', array('goods_id' => $value['goods_id']));
        $goods_list[$key]['image_url'] = cthumb($value['goods_image'], 60, $_SESSION['store_id']);
        $goods_list[$key]['xianshi_goods_id'] = $value['goods_id'];
        }
        Tpl::output('xianshi_goods_list', $goods_list);
        //输出导航
        self::profile_menu('ladder_manage');
        Tpl::output('show_page',$model->showpage(2));
        Tpl::showpage('store_promotion_tuangou.manage');
    }
    /**
     * 选择活动商品
     **/
    public function goods_selectOp() {
        $model_goods = Model('goods');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        $goods_list = $model_goods->getGoodsListForPromotion($condition, '*', 10, 'xianshi');

        Tpl::output('goods_list', $goods_list);
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::showpage('store_promotion_tuangou.goods', 'null_layout');
    }

     /**
     * 限时折扣商品添加
     **/
    public function ladder_goods_addOp() {
        $goods_id = intval($_POST['goods_id']);
        $model_goods = Model('goods');

        $data = array();
        $data['result'] = true;

        $goods_info = $model_goods->getGoodsInfoByID($goods_id);
          $goods_info['goods_url'] = urlShop('goods', 'index', array('goods_id' =>  $goods_id));
        $goods_info['image_url'] = cthumb($goods_info['goods_image'], 60, $_SESSION['store_id']);
         $goods_info['xianshi_goods_id'] = $goods_info['goods_id'];
        // if(empty($goods_info) || $goods_info['store_id'] != $_SESSION['store_id']) {
        //     $data['result'] = false;
        //     $data['message'] = L('param_error');
        //     echo json_encode($data);die;
        // }
       $xianshi_info =  $model_goods->editGoods( array('is_group_ladder' =>2,), array('goods_id' => $goods_id) );
        if(!$xianshi_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
          $data['message'] = '添加成功';
            $data['xianshi_goods'] = $goods_info;
    
        echo json_encode($data);die;
    }
    /**
     * 限时折扣商品删除
     **/
    public function ladder_goods_deleteOp() {
       $goods_id = intval($_POST['xianshi_goods_id']);
        $model_goods = Model('goods');

        $data = array();
        $data['result'] = true;

        $goods_info = $model_goods->getGoodsInfoByID($goods_id);
        // if(empty($goods_info) || $goods_info['store_id'] != $_SESSION['store_id']) {
        //     $data['result'] = false;
        //     $data['message'] = '666666666666';
        //     echo json_encode($data);die;
        // }
         $xianshi_info =  $model_goods->editGoods( array('is_group_ladder' =>0 , ),array('goods_id' => $goods_id) );
        if(!$xianshi_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
       
        echo json_encode($data);die;
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @param array     $array      附加菜单
     * @return
     */
    private function profile_menu($menu_key='') {
        $menu_array = array(
             1=>array('menu_key'=>'ladder_manage','menu_name'=>'商品管理','menu_url'=>urlShop('store_tuangou', 'ladder_manage')),
        );
        // switch ($menu_key){
        //     case 'mansong_add':
        //         $menu_array[] = array('menu_key'=>'mansong_add','menu_name'=>Language::get('promotion_join_active'),'menu_url'=>urlShop('store_tuangou', 'mansong_add'));
        //         break;
        //     case 'mansong_quota_add':
        //         $menu_array[] = array('menu_key'=>'mansong_quota_add','menu_name'=>Language::get('promotion_buy_product'),'menu_url'=>urlShop('store_tuangou', 'mansong_quota_add'));
        //         break;
        //     case 'mansong_detail':
        //         $menu_array[] = array('menu_key'=>'mansong_detail','menu_name'=>Language::get('mansong_active_content'),'menu_url'=>urlShop('store_tuangou', 'mansong_detail', array('mansong_id' => $_GET['mansong_id'])));
        //         break;
        // }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }

}
