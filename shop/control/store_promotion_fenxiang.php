<?php
/**
 * 商户中心-满就送
 *
 **/


defined('In718Shop') or exit('Access Invalid!');
class store_promotion_fenxiangControl extends BaseSellerControl {

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
           // echo'2222';die;
        $model_mansong = Model('p_fenxiang');
        $model_mansong_rule = Model('p_fenxiang_rule');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        if(!empty($_GET['mansong_name'])) {
            $condition['p_name'] = array('like', '%'.$_GET['mansong_name'].'%');
        }
        $mansong_list = $model_mansong->getMansongList($condition, 10);
        // foreach ($mansong_list as $key => $value) {
        //     $rule_list = $model_mansong_rule->getMansongRuleListByID($value['p_fenxiang_id']);
        //      $model_voucher = Model('voucher');
        //     //查询代金券详情
        //     $where = array();
        //     $where['voucher_t_id'] = $rule_list['voucher_t_id'];
        //     $t_info = $model_voucher->getVoucherTemplateInfo($where);
        // }
        
        Tpl::output('list', $mansong_list);
        Tpl::output('show_page',$model_mansong->showpage());
        self::profile_menu('mansong_list');
        Tpl::showpage('store_promotion_fenxiang.list');
    }

    /**
     * 添加满就送活动
     **/
    public function mansong_addOp() {
        $model_mansong_quota = Model('p_fenxiang_quota');
        $model_mansong = Model('p_fenxiang');
         
        //输出导航
        self::profile_menu('mansong_add');
        Tpl::showpage('store_promotion_fenxiang.add');
    }

    /**
     * 保存添加的满就送活动
     **/
    public function mansong_saveOp() {
        $mansong_name = trim($_POST['mansong_name']);
        if( $mansong_name==0){
           $mansong_name='分享送券';
        }elseif($mansong_name==1){
             $mansong_name='分享新人注册送券';
        }elseif($mansong_name==2){
             $mansong_name='分享新人下单送券';
        }

        $model_mansong = Model('p_fenxiang');
        $model_mansong_rule = Model('p_fenxiang_rule');

        if(empty($_POST['mansong_rule'])) {
            showDialog('满即送规则不能为空');
        }

        $param = array();
        $param['p_name'] = $mansong_name;
        $param['store_id'] = $_SESSION['store_id'];
        $param['store_name'] = $_SESSION['store_name'];
        $param['member_id'] = $_SESSION['member_id'];
        $param['member_name'] = $_SESSION['member_name'];
        $param['remark'] = trim($_POST['remark']);
        $param['type'] = $_POST['mansong_name'];
        $param['add_time'] = time();
        $mansong_id = $model_mansong->addMansong($param);
        if($mansong_id) {
            $mansong_rule_array = array();
            foreach ($_POST['mansong_rule'] as $value) {
                list($price, $discount, $goods_id) = explode(',', $value);
                $mansong_rule = array();
                $mansong_rule['p_fenxiang_id'] = $mansong_id;
                $mansong_rule['voucher_t_id'] = $price;
                $mansong_rule['count'] = $discount;
 		$flag=1;
                foreach($mansong_rule_array as $mansong_rule_1){
                    if($mansong_rule['voucher_t_id']==$mansong_rule_1['voucher_t_id']){
                        showDialog('同样的代金券只能加一个');
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

            showDialog('活动添加成功', urlShop('store_promotion_fenxiang', 'mansong_list'), 'succ');
        } else {
            showDialog('活动添加失败');
        }
    }

    /**
     * 满就送活动详细信息
     **/
    public function mansong_detailOp() {
        $mansong_id = intval($_GET['mansong_id']);

        $model_mansong = Model('p_fenxiang');
        $model_mansong_rule = Model('p_fenxiang_rule');

        $mansong_info = $model_mansong->getMansongInfoByID($mansong_id, $_SESSION['store_id']);
        if(empty($mansong_info)) {
            showDialog(L('param_error'));
        }
        if($mansong_info['is_use']==1){
          $mansong_info['is_use']='启用';
        }else{
           $mansong_info['is_use']='未启用';
        }
        Tpl::output('mansong_info', $mansong_info);

        $rule_list = $model_mansong_rule->getMansongRuleListByID($mansong_id);
        Tpl::output('list',$rule_list);
// var_dump($rule_list);die;
        //输出导航
        self::profile_menu('mansong_detail');
        Tpl::showpage('store_promotion_fenxiang.detail');
    }
     /**
     * 设置默认发货地址
     */
   public function fenxiang_default_setOp() {
   
       $p_fenxiang_id = intval($_GET['p_fenxiang_id']);
       if ($p_fenxiang_id <=  0) return false;
       $model_mansong = Model('p_fenxiang');
       $mansong_info = $model_mansong->getMansongInfoByID($p_fenxiang_id, $_SESSION['store_id']);
       $type=$mansong_info['type'];
       $condition = array();
       $condition['store_id'] = $_SESSION['store_id'];
       $condition['type'] = $type;
       $update = Model('p_fenxiang')->editMansong(array('is_use'=>0),$condition);
       $condition['p_fenxiang_id'] = $p_fenxiang_id;
       $update = Model('p_fenxiang')->editMansong(array('is_use'=>1),$condition); 
       // if( $update){
       //  exit(json_encode('222222222')) ;
       // }
       
   }

    /**
     * 满就送活动删除
     **/
    public function mansong_delOp() {
        $mansong_id = intval($_POST['mansong_id']);

        $model_mansong = Model('p_fenxiang');

        $mansong_info = $model_mansong->getMansongInfoByID($mansong_id, $_SESSION['store_id']);
        if(empty($mansong_info)) {
            showDialog(L('param_error'));
        }

        $condition = array();
        $condition['p_fenxiang_id'] = $mansong_id;
        $result = $model_mansong->delMansong($condition);

        if($result) {
            $this->recordSellerLog('删除代金券活动，活动名称：'.$mansong_info['p_name']);
            showDialog(L('nc_common_op_succ'), urlShop('store_promotion_fenxiang', 'mansong_list'), 'succ');
        } else {
            showDialog(L('nc_common_op_fail'));
        }
    }

    /**
     * 满就送套餐购买
     **/
    public function mansong_quota_addOp() {
        self::profile_menu('mansong_quota_add');
        Tpl::showpage('store_promotion_fenxiang_quota.add');
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
        $model_mansong_quota = Model('p_fenxiang_quota');
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

        showDialog(Language::get('mansong_quota_add_success'), urlShop('store_promotion_fenxiang', 'mansong_list'), 'succ');
    }

    /**
     * 选择活动商品
     **/
    public function search_goodsOp() {
        $model_goods = Model('goods');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        $goods_list = $model_goods->getGeneralGoodsList($condition, '*', 8);

        Tpl::output('goods_list', $goods_list);
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::showpage('store_promotion_fenxiang.goods', 'null_layout');
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
        $menu_array = array(
            1=>array('menu_key'=>'mansong_list','menu_name'=>Language::get('promotion_active_list'),'menu_url'=>urlShop('store_promotion_fenxiang', 'mansong_list')),
        );
        switch ($menu_key){
        	case 'mansong_add':
                $menu_array[] = array('menu_key'=>'mansong_add','menu_name'=>Language::get('promotion_join_active'),'menu_url'=>urlShop('store_promotion_fenxiang', 'mansong_add'));
        		break;
        	case 'mansong_quota_add':
                $menu_array[] = array('menu_key'=>'mansong_quota_add','menu_name'=>Language::get('promotion_buy_product'),'menu_url'=>urlShop('store_promotion_fenxiang', 'mansong_quota_add'));
        		break;
        	case 'mansong_detail':
                $menu_array[] = array('menu_key'=>'mansong_detail','menu_name'=>Language::get('mansong_active_content'),'menu_url'=>urlShop('store_promotion_fenxiang', 'mansong_detail', array('mansong_id' => $_GET['mansong_id'])));
        		break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }

}
