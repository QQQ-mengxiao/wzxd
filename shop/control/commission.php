<?php
/**
 * 佣金管理
 *
 **/


defined('In718Shop') or exit('Access Invalid!');
class commissionControl extends BaseSellerControl {
    public function __construct() {
        parent::__construct();
    }

    public function indexOp() {
        $this->settingOp();
    }

    /**
     * 佣金比例设置
     */
    public function settingOp(){
        // 获取商品分类
        $gc_list = Model()->table('goods_class')->where(array('gc_parent_id'=>0))->field('gc_id,gc_name')->order('gc_sort asc')->select();
        foreach($gc_list as $key=>$value){
            $commission = Model()->table('commission')->where(array('gc_id'=>$value['gc_id']))->find();
            $gc_list[$key]['commis_rate'] = $commission['commis_rate']?$commission['commis_rate']:0;
            $gc_list[$key]['edittime'] = $commission['edittime']?date('Y-m-d H:i:s',$commission['edittime']):'-';
            $gc_list[$key]['seller_name'] = $commission['seller_name']?$commission['seller_name']:'无';
        }
        Tpl::output('gc_list', $gc_list);

        $this->_profile_menu('spec', 'spec');
        Tpl::showpage('commission.index');
    }

    /**
     * 编辑佣金比例
     */
    public function editOp(){
        $gc_id = $_GET['gc_id'];
        $commission = Model()->table('commission')->where(array('gc_id'=>$gc_id))->find();
        if(!$commission){
            $commission['commis_rate'] = 0;
            $commission['gc_id'] = $gc_id;
        }
        Tpl::output('commission', $commission);
        Tpl::showpage('commission.edit', 'null_layout');
    }

    /**
     * 保存佣金比例
     */
    public function edit_saveOp(){
        if(chksubmit()){
            $gc_id = $_POST['gc_id'];
            $commis_rate = $_POST['commis_rate'];
            $commission = Model()->table('commission')->where(array('gc_id'=>$gc_id))->find();
            if($commission){//更新
                $data['commis_rate'] = $commis_rate;
                $data['edittime'] = TIMESTAMP;
                $data['seller_id'] = $_SESSION['seller_id'];
                $data['seller_name'] = $_SESSION['seller_name'];
                $result = Model()->table('commission')->where(array('gc_id'=>$gc_id))->update($data);
            }else{
                $data['gc_id'] = $gc_id;
                $data['gc_name'] = Model('goods_class')->getfby_gc_id($gc_id,'gc_name');
                $data['commis_rate'] = $commis_rate;
                $data['edittime'] = TIMESTAMP;
                $data['seller_id'] = $_SESSION['seller_id'];
                $data['seller_name'] = $_SESSION['seller_name'];
                $result = Model()->table('commission')->insert($data);
            }
            if($result){
                Model()->table('goods')->where(array('gc_id_1'=>$gc_id))->update(array('commis_rate'=>$commis_rate));
                Model()->table('goods_common')->where(array('gc_id_1'=>$gc_id))->update(array('commis_rate'=>$commis_rate));
                showDialog('操作成功！', 'index.php?act=commission&op=setting', 'succ');
            }else{
                showDialog('操作失败！', '', 'error');
            }
        }
        showDialog('参数有误！', '', 'error');
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string	$menu_type	导航类型
     * @param string 	$menu_key	当前导航的menu_key
     * @return
     */
    private function _profile_menu($menu_type, $menu_key) {
        $menu_array = array();
        switch ($menu_type) {
            case 'spec':
                $menu_array = array(
                    array('menu_key' => 'spec', 'menu_name' => "佣金管理", 'menu_url' => 'index.php?act=store_spec')
                );
            break;
        }
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key',$menu_key);
    }
}
