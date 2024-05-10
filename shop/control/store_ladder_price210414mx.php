<?php
/**
 * 商户中心-满就送
 *
 **/


defined('In718Shop') or exit('Access Invalid!');
class store_ladder_priceControl extends BaseSellerControl {

    public function __construct() {

        parent::__construct() ;

        Language::read('member_layout,promotion_mansong');

        //检查满就送是否开启
        if (intval(C('promotion_allow')) !== 1) {
            showMessage(Language::get('promotion_unavailable'),'index.php?act=seller_center','','error');
        }

    }

    public function indexOp() {
        $this->mansong_listOp();
    }

    /**
     * 发布的满就送活动列表
     **/
    public function mansong_listOp() {

        $model_mansong = Model('p_ladder');
        $model_mansong_rule = Model('p_ladder_rule');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        if(!empty($_GET['mansong_name'])) {
            $condition['p_name'] = array('like', '%'.$_GET['mansong_name'].'%');
        }
        $mansong_list = $model_mansong->getMansongList($condition, 10);
        Tpl::output('list', $mansong_list);
        Tpl::output('show_page',$model_mansong->showpage());
        self::profile_menu('mansong_list');
        Tpl::showpage('store_ladder_price.list');
    }

    /**
     * 添加满就送活动
     **/
    public function mansong_addOp() {
        $model_mansong_quota = Model('p_ladder_quota');
        $model_mansong = Model('p_ladder');

        //输出导航
        self::profile_menu('mansong_add');
        Tpl::showpage('store_ladder_price.add');
    }

    /**
     * 保存添加的满就送活动
     **/
    public function mansong_saveOp() {
        $mansong_name = trim($_POST['mansong_name']);
         $deliver_time = trim($_POST['deliver_time']);
        $model_mansong = Model('p_ladder');
        $model_mansong_rule = Model('p_ladder_rule');

        if(empty($_POST['mansong_rule'])) {
            showDialog('满即送规则不能为空');
        }

        $param = array();
        $param['p_name'] = $mansong_name;
        $param['deliver_time'] = $deliver_time;
        $param['store_id'] = $_SESSION['store_id'];
        $param['store_name'] = $_SESSION['store_name'];
        $param['member_id'] = $_SESSION['member_id'];
        $param['member_name'] = $_SESSION['member_name'];
        $param['remark'] = trim($_POST['remark']);
        $param['add_time'] = time();
        $mansong_id = $model_mansong->addMansong($param);
        if($mansong_id) {
            $mansong_rule_array = array();
            foreach ($_POST['mansong_rule'] as $value) {
                list($price, $discount, $goods_id) = explode(',', $value);
                $mansong_rule = array();
                $mansong_rule['p_ladder_id'] = $mansong_id;
                $mansong_rule['time'] = $price;
                $mansong_rule['discount'] = $discount;
        $flag=1;
                foreach($mansong_rule_array as $mansong_rule_1){
                    if($mansong_rule['time']==$mansong_rule_1['time']){
                        showDialog('同样的时间内只能加一个');
                        $flag=0;
                    }
                }
                if($flag==1){
                    $mansong_rule_array[]=$mansong_rule;
                }
            }
            //生成规则
            $result = $model_mansong_rule->addMansongRuleArray($mansong_rule_array);

            $this->recordSellerLog('添加代金券活动，活动名称：'.$mansong_name);

            showDialog('活动添加成功', urlShop('store_ladder_price', 'mansong_list'), 'succ');
        } else {
            showDialog('活动添加失败');
        }
    }

    /**
     * 满就送活动详细信息
     **/
    public function mansong_detailOp() {
        $mansong_id = intval($_GET['mansong_id']);

        $model_mansong = Model('p_ladder');
        $model_mansong_rule = Model('p_ladder_rule');

        $mansong_info = $model_mansong->getMansongInfoByID($mansong_id, $_SESSION['store_id']);
        if(empty($mansong_info)) {
            showDialog(L('param_error'));
        }
        Tpl::output('mansong_info', $mansong_info);

        $rule_list = $model_mansong_rule->getMansongRuleListByID($mansong_id);
        Tpl::output('list',$rule_list);
// var_dump($rule_list);die;
        //输出导航
        self::profile_menu('mansong_detail');
        Tpl::showpage('store_ladder_price.detail');
    }

    /**
     * 满就送活动删除
     **/
    public function mansong_delOp() {
        $mansong_id = intval($_POST['mansong_id']);

        $model_mansong = Model('p_ladder');

        $mansong_info = $model_mansong->getMansongInfoByID($mansong_id, $_SESSION['store_id']);
        if(empty($mansong_info)) {
            showDialog(L('param_error'));
        }

        $condition = array();
        $condition['p_ladder_id'] = $mansong_id;
        $result = $model_mansong->delMansong($condition);

        if($result) {
            $this->recordSellerLog('删除代金券活动，活动名称：'.$mansong_info['p_name']);
            showDialog(L('nc_common_op_succ'), urlShop('store_ladder_price', 'mansong_list'), 'succ');
        } else {
            showDialog(L('nc_common_op_fail'));
        }
    }

    /**
     * 满就送套餐购买
     **/
    public function mansong_quota_addOp() {
        self::profile_menu('mansong_quota_add');
        Tpl::showpage('store_ladder_price_quota.add');
    }

    /**
     * 满就送套餐购买保存
     **/
    public function mansong_quota_add_saveOp() {
        $mansong_quota_quantity = intval($_POST['mansong_quota_quantity']);

        if($mansong_quota_quantity <= 0 || $mansong_quota_quantity > 12) {
            showDialog(Language::get('mansong_quota_quantity_error'));
        }

        //获取当前价格
        $current_price = intval(C('promotion_mansong_price'));

        //获取该用户已有套餐
        $model_mansong_quota = Model('p_ladder_quota');
        $current_mansong_quota= $model_mansong_quota->getMansongQuotaCurrent($_SESSION['store_id']);
        $add_time = 86400 * 30 * $mansong_quota_quantity;
        if(empty($current_mansong_quota)) {
            //生成套餐
            $param = array();
            $param['member_id'] = $_SESSION['member_id'];
            $param['member_name'] = $_SESSION['member_name'];
            $param['store_id'] = $_SESSION['store_id'];
            $param['store_name'] = $_SESSION['store_name'];
            $param['start_time'] = TIMESTAMP;
            $param['end_time'] = TIMESTAMP + $add_time;
            $model_mansong_quota->addMansongQuota($param);
        } else {
            $param = array();
            $param['end_time'] = array('exp', 'end_time + ' . $add_time);
            $model_mansong_quota->editMansongQuota($param, array('quota_id' => $current_mansong_quota['quota_id']));
        }

        //记录店铺费用
        $this->recordStoreCost($current_price * $mansong_quota_quantity, '购买满即送');

        $this->recordSellerLog('购买'.$mansong_quota_quantity.'份满即送套餐，单价'.$current_price.$lang['nc_yuan']);

        showDialog(Language::get('mansong_quota_add_success'), urlShop('store_ladder_price', 'mansong_list'), 'succ');
    }
    /**
     * 设置默认发货地址
     */
   public function default_setOp() {
       $address_id = intval($_GET['p_ladder_id']);
       if ($address_id <=  0) return false;
       $condition = array();
       $condition['store_id'] = $_SESSION['store_id'];
       $update = Model('p_ladder')->editAddress(array('is_default'=>0),$condition);
       $condition['p_ladder_id'] = $address_id;
       $update = Model('p_ladder')->editAddress(array('is_default'=>1),$condition);
   }
    /**
     * 限时折扣活动管理
     **/
    public function ladder_manageOp() {
        $model = Model('goods');
        $condition = array();
        $condition['is_group_ladder'] = 1;
        $condition['store_id'] = $_SESSION['store_id'];
        if(!empty($_GET['goods_name'])) {
            $condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        }
        if(!empty($_GET['goods_serial'])){
            $condition['goods_serial'] = $_GET['goods_serial'];
        }
         $goods_list = $model->getGoodsList($condition,'*','','',0,5);
        foreach ($goods_list as $key => $value) {
         $goods_list[$key]['goods_url'] = urlShop('goods', 'index', array('goods_id' => $value['goods_id']));
        $goods_list[$key]['image_url'] = cthumb($value['goods_image'], 60, $_SESSION['store_id']);
         $goods_list[$key]['xianshi_goods_id'] = $value['goods_id'];
        }
        Tpl::output('xianshi_goods_list', $goods_list);
        //输出导航
        self::profile_menu('ladder_manage');
        Tpl::output('show_page',$model->showpage(2));
        Tpl::showpage('store_promotion_ladder.manage');
    }
    /**
     * 选择活动商品
     **/
    public function goods_selectOp() {
        $model_goods = Model('goods');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        if($_GET['goods_name']) {
            $condition['goods_name'] = array('like', '%' . $_GET['goods_name'] . '%');
        }
        if($_GET['goods_serial']) {
            $condition['goods_serial'] = $_GET['goods_serial'];
        }
        //$condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        //$condition['goods_serial'] = $_GET['goods_serial'];
        $goods_list = $model_goods->getGoodsListForPromotion($condition, '*', 10, 'jieti');

        Tpl::output('goods_list', $goods_list);
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::showpage('store_promotion_ladder.goods', 'null_layout');
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
        // if(empty($goods_info) || $goods_info['store_id'] != $_SESSION['store_id']) {
        //     $data['result'] = false;
        //     $data['message'] = L('param_error');
        //     echo json_encode($data);die;
        // }
        $goods_info['goods_url'] = urlShop('goods', 'index', array('goods_id' =>  $goods_id));
        $goods_info['image_url'] = cthumb($goods_info['goods_image'], 60, $_SESSION['store_id']);
         $goods_info['xianshi_goods_id'] = $goods_info['goods_id'];
        $xianshi_info =  $model_goods->editGoods( array('is_group_ladder' =>1 , ),array('goods_id' => $goods_id) );
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
        if(empty($goods_info) || $goods_info['store_id'] != $_SESSION['store_id']) {
            $data['result'] = false;
            $data['message'] = '666666666666';
            echo json_encode($data);die;
        }
         $xianshi_info =  $model_goods->editGoods( array('is_group_ladder' =>0,), array('goods_id' => $goods_id) );
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
            1=>array('menu_key'=>'mansong_list','menu_name'=>Language::get('promotion_active_list'),'menu_url'=>urlShop('store_ladder_price', 'mansong_list')),
              2=>array('menu_key'=>'ladder_manage','menu_name'=>'商品管理','menu_url'=>urlShop('store_ladder_price', 'ladder_manage')),
        );
        switch ($menu_key){
            case 'mansong_add':
                $menu_array[] = array('menu_key'=>'mansong_add','menu_name'=>Language::get('promotion_join_active'),'menu_url'=>urlShop('store_ladder_price', 'mansong_add'));
                break;
            case 'mansong_quota_add':
                $menu_array[] = array('menu_key'=>'mansong_quota_add','menu_name'=>Language::get('promotion_buy_product'),'menu_url'=>urlShop('store_ladder_price', 'mansong_quota_add'));
                break;
            case 'mansong_detail':
                $menu_array[] = array('menu_key'=>'mansong_detail','menu_name'=>Language::get('mansong_active_content'),'menu_url'=>urlShop('store_ladder_price', 'mansong_detail', array('mansong_id' => $_GET['mansong_id'])));
                break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }

}
