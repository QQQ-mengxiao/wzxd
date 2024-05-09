<?php
/**
 * 新品管理
 *
 **/


defined('In718Shop') or exit ('Access Invalid!');
class store_goods_newControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct ();
        Language::read ('member_store_goods_index');
    }
    public function indexOp() {
        $this->settingOp();
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
        $menu_array = array(
            array('menu_key' => 'setting',    'menu_name' => '新品规则设置', 'menu_url' => urlShop('store_goods_new', 'setting')),
            array('menu_key' => 'new_goods_list',    'menu_name' => '新品列表', 'menu_url' => urlShop('store_goods_new', 'new_goods_list'))
        );
        Tpl::output ( 'member_menu', $menu_array );
        Tpl::output ( 'menu_key', $menu_key );
    }
    
    /**
     *新品规则设置
     */
    public function settingOp(){
        $model_setting = Model('setting');
        $goods_show_time = $model_setting->getRowSetting('goods_show_time');
        $goods_show_discount = $model_setting->getRowSetting('goods_show_discount');
        if(chksubmit()){
            $goods_show_time = $_POST['goods_show_time'];
            $goods_show_discount = $_POST['goods_show_discount'];
            $param = array('goods_show_time'=>$goods_show_time,'goods_show_discount'=>$goods_show_discount);
            $result = $model_setting->updateSetting($param);
            if($result){

                /**********时间同步**********/
                Model()->table('goods_common')->where(['is_new'=>1])->update(['goods_show_time'=>(3600 * $goods_show_time)]);
                /**********时间同步**********/

                $this->recordSellerLog('新品规则设置，展示时间'.$goods_show_time.'，折扣'.$goods_show_discount);
                showDialog('提交成功！', 'reload', 'succ');
            }else{
                showDialog('提交失败！', 'reload', 'fail');
            }
        }
        Tpl::output('goods_show_time', $goods_show_time['value']);
        Tpl::output('goods_show_discount', $goods_show_discount['value']);
        $this->profile_menu('goods_list', 'setting');
        Tpl::showpage('store_goods_new.setting');
    }

    /**
     *新品列表
     */
    public function new_goods_listOp(){
        $goods_list = Model()->table('goods_common')->where(array('goods_state'=>1,'is_new'=>1))->select();
        // echo '<pre>';print_r($goods_list);die;
        Tpl::output('goods_list', $goods_list);
        $this->profile_menu('goods_list', 'new_goods_list');
        Tpl::showpage('store_goods_new.list');
    }

    /**
     * 取消新品
     */
    public function goods_unshowOp() {
        $sql = "UPDATE 718shop_goods_common gc,718shop_goods g SET gc.goods_puton_time=NULL,gc.goods_show_time=NULL,gc.goods_new_discount=NULL,gc.is_new=0,g.is_group_ladder=0 WHERE gc.goods_commonid IN (".$_GET['commonid'].") AND g.goods_commonid IN (".$_GET['commonid'].") AND g.is_group_ladder =7";
        $result = Model()->execute($sql);
        if ($result) {
            // 添加操作日志
            $this->recordSellerLog('取消新品，平台货号：'.$_GET['commonid']);

            //活动表去除商品新品标签
            $model_goodspromotion = Model('goods_promotion');
            $goods_list = Model('goods')->getGoodsList(array('goods_commonid' => array('in', $_GET['commonid']),'is_deleted'=>0), 'goods_id');

            $where = array();
            $where['goods_id'] = array('in',array_column($goods_list,'goods_id'));
            $where['promotion_type'] = 40;
            $model_goodspromotion->delgoods_promotion($where);

            showDialog(L('store_goods_index_goods_unshow_success'), getReferer() ? getReferer() : 'index.php?act=store_goods_new&op=new_goods_list', 'succ', '', 2);
        } else {
            showDialog(L('store_goods_index_goods_unshow_fail'), '', 'error');
        }
    }


}
