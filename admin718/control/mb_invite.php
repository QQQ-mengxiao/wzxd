<?php
/**
 * 邀新规则管理
 */

defined('In718Shop') or exit('Access Invalid!');
class mb_inviteControl extends SystemControl{
    public function __construct(){
        parent::__construct();
    }

    /**
     *邀新标题列表
    */
    public function title_listOp() {
        $model_mb_tanchu = Model('tanchu');
        if($_GET['title']){
            $title = $_GET['title'];
            $condition['title'] =  $title;
            $tanchu_list = $model_mb_tanchu->table('tanchu')->condition($condition)->page(10)->order('tanchu_id asc')->select();
        }else{
            $tanchu_list = $model_mb_tanchu->table('tanchu')->page(10)->order('tanchu_id asc')->select();
        }
        Tpl::output('title',$title);       
        Tpl::output('tanchu_list',$tanchu_list);
        //Tpl::output('list', $mb_special_list);
        Tpl::output('page',$model_mb_tanchu->showpage());

        //$this->show_menu('special_list');
        //Tpl::showpage('mb_special1.list');
        Tpl::showpage('tanchu_list.index');
    }
     /**
     * 弹窗新增
     */
    public function mb_tanchuOp() {
        $this->show_menu('special_item_list');
        Tpl::showpage('tanchu.add');
        
    }
    /**
     * 弹窗保存
     */
    public function tanchu_saveOp() {
        $model_tanchu = Model('tanchu');
       //验证表单信息
        $tanchu_id      = trim($_POST['tanchu_id']);
        $data['title']       = trim($_POST['item_data']['title']);
        $data['pic']       = trim($_POST['item_data']['image']);
        $data['type']       = intval($_POST['item_data']['type']);
        $data['content']       = trim($_POST['item_data']['data']);
        $data['start_time']       = strtotime(trim($_POST['item_data']['start_time']));
        $data['end_time']       = strtotime(trim($_POST['item_data']['end_time']));
        //is_open 为手动开启字段，1：开启，2关闭；新增弹出框，默认为0 
        $is_open       = intval($_POST['item_data']['is_open']);
        if(!empty($is_open)){
            $data['is_open']       = $is_open;
            $data['state']       = $is_open;
        }else{
            $data['is_open']       = 0;
            $now = time();
            if($now >= $data['start_time'] && $now <= $data['end_time'] ){
                $data['state']       = 1;
            }else{
                $data['state']       = 2;
            }
        }
        $data['acs']       = intval($_POST['item_data']['acs']);
        if(!empty($tanchu_id)){
            $condition['tanchu_id'] = $tanchu_id;
            $result = $model_tanchu->table('tanchu')->where($condition)->update($data);
             if ($result){
                showMessage('编辑弹出框成功!',urlAdmin('mb_tanchu', 'tanchu_list'));
            }else {
                showMessage('编辑弹出框失败!',urlAdmin('mb_tanchu', 'tanchu_list'));
            }
        }else{
            $result = $model_tanchu->insert($data);
             if ($result){
                showMessage('新增弹出框成功!',urlAdmin('mb_tanchu', 'tanchu_list'));
            }else {
                showMessage('新增弹出框失败!',urlAdmin('mb_tanchu', 'tanchu_list'));
            }
        }
       
    }
    /**
     * 弹窗保存
     */
    public function is_openeditOp() {
        $model_tanchu = Model('tanchu');
       //验证表单信息
        $tanchu_id      = trim($_GET['tanchu_id']);
        $data['is_open']       = trim($_GET['is_open']);
        $data['state']       = $data['is_open'];
        $condition['tanchu_id'] = $tanchu_id;
        $result = $model_tanchu->table('tanchu')->where($condition)->update($data);
        if ($result){
            showMessage('弹出框状态变更成功!',urlAdmin('mb_tanchu', 'tanchu_list'));
        }else {
            showMessage('弹出框状态变更成功!',urlAdmin('mb_tanchu', 'tanchu_list'));
        }
       
    }
    /**
     * 编辑弹出框
     */
    public function tanchu_editOp() {

        //弹出框信息
        $model_tanchu = Model('tanchu');
        $condition = array();
        $tanchu_id = intval($_GET['tanchu_id']);
        $condition['tanchu_id'] = $tanchu_id;
        $tanchu_info = $model_tanchu->where($condition)->find();
        Tpl::output('item_info', $tanchu_info);
        $condition2 = array();
 		$model_daddress = Model('ziti_address');
 		$address_list = $model_daddress->getAddressList($condition2);
 		Tpl::output('address_list',$address_list);

        $this->show_menu('special_item_list');
        // //输出导航
        //self::profile_menu('xianshi_edit');
        Tpl::showpage('tanchu.add');
    }
    /**
     * 删除
     */
    public function tanchu_delOp(){

        if (intval($_GET['tanchu_id']) > 0){
            $model_tanchu = Model('tanchu');
            $tanchu_id = intval($_GET['tanchu_id']);
            $condition['tanchu_id'] = $tanchu_id;
            $tanchu_info = $model_tanchu->where($condition)->find();
            /**
             * 删除图片
             */
            $name_array = explode('_', $tanchu_info['pic']);
            $img =  BASE_UPLOAD_PATH.DS. ATTACH_MOBILE . DS . 'special1' . DS .$name_array[0] . DS .$tanchu_info['pic'];
            if (!empty($img)){
                @unlink($img);
            }
            $model_tanchu->table('tanchu')->where($condition)->delete();
            showMessage('弹出框删除成功!',urlAdmin('mb_tanchu', 'tanchu_list'));
        }else {
            showMessage('弹出框删除失败!',urlAdmin('mb_tanchu', 'tanchu_list'));
        }
    }

    /**
     * 保存专题
     */
    public function special_saveOp() {
        $model_mb_special = Model('mb_special1');

        $param = array();
        $param['special_desc'] = $_POST['special_desc'];
        $result = $model_mb_special->addMbSpecial($param);

        if($result) {
            $this->log('添加手机专题' . '[ID:' . $result. ']', 1);
            showMessage(L('nc_common_save_succ'), urlAdmin('mb_special1', 'special_list'));
        } else {
            $this->log('添加手机专题' . '[ID:' . $result. ']', 0);
            showMessage(L('nc_common_save_fail'), urlAdmin('mb_special1', 'special_list'));
        }
    }

    /**
     * 编辑专题描述
     */
    public function update_special_descOp() {
        $model_mb_special = Model('mb_special1');

        $param = array();
        $param['special_desc'] = $_GET['value'];
        $result = $model_mb_special->editMbSpecial($param, $_GET['id']);

        $data = array();
        if($result) {
            $this->log('保存手机专题' . '[ID:' . $result. ']', 1);
            $data['result'] = true;
        } else {
            $this->log('保存手机专题' . '[ID:' . $result. ']', 0);
            $data['result'] = false;
            $data['message'] = '保存失败';
        }
        echo json_encode($data);die;
    }

    /**
     * 删除专题
     */
    public function special_delOp() {
        $model_mb_special = Model('mb_special1');

        $result = $model_mb_special->delMbSpecialByID($_POST['special_id']);

        if($result) {
            $this->log('删除手机专题' . '[ID:' . $_POST['special_id'] . ']', 1);
            showMessage(L('nc_common_del_succ'), urlAdmin('mb_special1', 'special_list'));
        } else {
            $this->log('删除手机专题' . '[ID:' . $_POST['special_id'] . ']', 0);
            showMessage(L('nc_common_del_fail'), urlAdmin('mb_special1', 'special_list'));
        }
    }

    /**
     * 编辑首页
     */
    public function index_editOp() {
        $model_mb_special = Model('mb_special1');

        $special_item_list = $model_mb_special->getMbSpecialItemListByID($model_mb_special::INDEX_SPECIAL_ID);
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        Tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        Tpl::output('special_id', $model_mb_special::INDEX_SPECIAL_ID);

        $this->show_menu('index_edit');
        Tpl::showpage('mb_special_item.list');
    }

    /**
     * 编辑专题
     */
    public function special_editOp() {
        $model_mb_special = Model('mb_special1');

        $special_item_list = $model_mb_special->getMbSpecialItemListByID($_GET['special_id']);
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        Tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        Tpl::output('special_id', $_GET['special_id']);

        $this->show_menu('special_item_list');
        Tpl::showpage('mb_special1_item.list');
//        Tpl::showpage('mb_special1_item_list');
    }

    /**
     * 编辑专题
     */
    public function mb_special_item_editOp() {
        $model_mb_special = Model('mb_special1');

        $special_item_list = $model_mb_special->getMbSpecialItemListByID($_GET['special_id']);
        Tpl::output('list', $special_item_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        Tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        Tpl::output('special_id', $_GET['special_id']);

        $this->show_menu('special_item_list');
        Tpl::showpage('mb_special1_item.edit');
    }

    /**
     * 专题项目添加
     */
    public function special_item_addOp() {
        $model_mb_special = Model('mb_special1');

        $param = array();
        $param['special_id'] = $_POST['special_id'];
        $param['item_type'] = $_POST['item_type'];

        //广告只能添加一个
        if($param['item_type'] == 'adv_list') {
            $result = $model_mb_special->isMbSpecialItemExist($param);
            if($result) {
                echo json_encode(array('error' => '广告条板块只能添加一个'));die;
            }
        }
        //推荐只能添加一个
        if($param['item_type'] == 'goods1') {
            $result = $model_mb_special->isMbSpecialItemExist($param);
            if($result) {
                echo json_encode(array('error' => '限时板块只能添加一个'));die;
            }
        }
        //团购只能添加一个
        if($param['item_type'] == 'goods2') {
            $result = $model_mb_special->isMbSpecialItemExist($param);
            if($result) {
                echo json_encode(array('error' => '团购板块只能添加一个'));die;
            }
        }

        //end

        $item_info = $model_mb_special->addMbSpecialItem($param);
        if($item_info) {
            echo json_encode($item_info);die;
        } else {
            echo json_encode(array('error' => '添加失败'));die;
        }
    }

    /**
     * 专题项目删除
     */
    public function special_item_delOp() {
        $model_mb_special = Model('mb_special1');

        $condition = array();
        $condition['item_id'] = $_POST['item_id'];

        $result = $model_mb_special->delMbSpecialItem($condition, $_POST['special_id']);
        if($result) {
            echo json_encode(array('message' => '删除成功'));die;
        } else {
            echo json_encode(array('error' => '删除失败'));die;
        }
    }

    /**
     * 专题项目编辑
     */
    public function special_item_editOp() {
        $model_mb_special = Model('mb_special1');
        $theitemid=$_GET['item_id'];
        $item_info = $model_mb_special->getMbSpecialItemInfoByID($theitemid);
        $a=$item_info['item_data']['data'];
        $b=strpos($a,'=');
        $item_info['item_data']['data']=substr($a,$b+1);
        Tpl::output('item_info', $item_info);

        if($item_info['special_id'] == 0) {
            $this->show_menu('index_edit');
        } else {
            $this->show_menu('special_item_list');
        }
        //2015推荐 2016团购
        if($theitemid==2015){
            Tpl::showpage('mb_special_item.edit1');
        }else if($theitemid==2016){
            Tpl::showpage('mb_special_item.edit2');
        }
        Tpl::showpage('mb_special1_item.edit');
    }

    /**
     * 专题项目保存
     */
    public function special_item_saveOp() {

        $model_mb_special = Model('mb_special1');

        $result = $model_mb_special->editMbSpecialItemByID(array('item_data' => $_POST['item_data']), $_POST['item_id'], $_POST['special_id']);

        if($result) {
            if($_POST['special_id'] == $model_mb_special::INDEX_SPECIAL_ID) {
                showMessage(L('nc_common_save_succ'), urlAdmin('mb_special1', 'index_edit'));
            } else {
                showMessage(L('nc_common_save_succ'), urlAdmin('mb_special1', 'special_edit', array('special_id' => $_POST['special_id'])));
            }
        } else {
            showMessage(L('nc_common_save_succ'), '');
        }
    }

    /**
     * 图片上传
     */
    public function special_image_uploadOp() {
        $data = array();
        if(!empty($_FILES['special_image']['name'])) {
            $prefix = 's' . $_POST['special_id'];
            $upload	= new UploadFile();
            $upload->set('default_dir', ATTACH_MOBILE . DS . 'special1' . DS . $prefix);
            $upload->set('fprefix', $prefix);
            $upload->set('allow_type', array('gif', 'jpg', 'jpeg', 'png'));

            $result = $upload->upfile('special_image');
            if(!$result) {
                $data['error'] = $upload->error;
            }
            $data['image_name'] = $upload->file_name;
            $data['image_url'] = getMbSpecial1ImageUrl($data['image_name']);
        }
        echo json_encode($data);
    }

    /**
     * 商品列表
     */

    public function goods_listOp() {
        $keyw=$_GET['keyword'];
        $goods_serial=$_GET['goods_serial'];
        $condition = array();
        $model_true_goods=Model('goods');
        if($keyw=='2015'){
            $model_goods = Model('p_xianshi_goods');
            $condition['goods_name'] = array('like', '%%');
            $goods_id_list=$model_goods->getXianshiGoodsExtendIds($condition);

            $goods_list = $model_true_goods->getGoodsOnlineListAndPromotionByIdArray($goods_id_list);

            Tpl::output('goods_list', $goods_list);
            Tpl::output('show_page', $model_true_goods->showpage());
            Tpl::showpage('mb_special_widget.goods1', 'null_layout');
        }else if($keyw=='2016'){
            $model_goods_ids = Model('groupbuy');
            $condition['goods_name'] = array('like', '%%');
            $goods_list_arr=$model_goods_ids->getGroupbuyGoodsExtendIds($condition);
            $goods_list=$model_true_goods->getGoodsOnlineListAndPromotionByIdArray($goods_list_arr);
            //showMessage($goods_list[1]['goods_id']);
            Tpl::output('goods_list', $goods_list);
            Tpl::output('show_page', $model_true_goods->showpage());
            Tpl::showpage('mb_special_widget.goods2', 'null_layout');
        }else{
            $model_goods = Model('goods');
            $condition['goods_name'] = array('like', '%' . $_GET['keyword'] . '%');
            //$condition['goods_serial'] = $goods_serial;
			if($goods_serial){
                $condition['goods_serial'] = array('like', '%' . $goods_serial . '%');
            }
            $goods_list = $model_goods->getGoodsOnlineList($condition, 'goods_id,goods_name,goods_promotion_price,goods_image', 10);
            Tpl::output('goods_list', $goods_list);
            Tpl::output('show_page', $model_goods->showpage());
            Tpl::showpage('mb_special_widget.goods', 'null_layout');
        }
    }

    /**
     * 更新项目排序
     */
    public function update_item_sortOp() {
        $item_id_string = $_POST['item_id_string'];
        $special_id = $_POST['special_id'];
        if(!empty($item_id_string)) {
            $model_mb_special = Model('mb_special1');
            $item_id_array = explode(',', $item_id_string);
            $index = 0;
            foreach ($item_id_array as $item_id) {
                $result = $model_mb_special->editMbSpecialItemByID(array('item_sort' => $index), $item_id, $special_id);
                $index++;
            }
        }
        $data = array();
        $data['message'] = '操作成功';
        echo json_encode($data);
    }

    /**
     * 更新项目启用状态
     */
    public function update_item_usableOp() {
        $model_mb_special = Model('mb_special1');
        $result = $model_mb_special->editMbSpecialItemUsableByID($_POST['usable'], $_POST['item_id'], $_POST['special_id']);
        $data = array();
        if($result) {
            $data['message'] = '操作成功';
        } else {
            $data['error'] = '操作失败';
        }
        echo json_encode($data);
    }

    /**
     * 页面内导航菜单
     * @param string 	$menu_key	当前导航的menu_key
     * @param array 	$array		附加菜单
     * @return
     */
    private function show_menu($menu_key='') {
        $menu_array = array();
        if($menu_key == 'index_edit') {
            $menu_array[] = array('menu_key'=>'index_edit', 'menu_name'=>'编辑', 'menu_url'=>'javascript:;');
        } else {
            $menu_array[] = array('menu_key'=>'special_list','menu_name'=>'列表', 'menu_url'=>urlAdmin('mb_special1', 'special_list'));
        }
        if($menu_key == 'special_item_list') {
            $menu_array[] = array('menu_key'=>'special_item_list', 'menu_name'=>'编辑专题', 'menu_url'=>'javascript:;');
        }
        if($menu_key == 'index_edit') {
            tpl::output('item_title', '首页编辑');
        } else {
            tpl::output('item_title', '专题设置');
        }
        Tpl::output('menu', $menu_array);
        Tpl::output('menu_key', $menu_key);
    }
    
	 /**
     * 弹窗编辑
     */
    public function popupOp() {
        $model_mb_special = Model('mb_special1');
        $theitemid=320;
        $item_info = $model_mb_special->getMbSpecialItemInfoByID($theitemid);
        print_r($item_info);
        $a=$item_info['item_data']['data'];
        $b=strpos($a,'=');
        $item_info['item_data']['data']=substr($a,$b+1);
        echo 'zhi';
        print_r($item_info);die;
        Tpl::output('item_info', $item_info);

        if($item_info['special_id'] == 0) {
            $this->show_menu('index_edit');
        } else {
            $this->show_menu('special_item_list');
        }

        Tpl::showpage('popup.edit');
    }

    /**
     * 弹窗保存
     */
    public function popup_saveOp() {

        $model_mb_special = Model('mb_special1');

        $result = $model_mb_special->editMbSpecialItemByID(array('item_data' => $_POST['item_data']), $_POST['item_id'], $_POST['special_id']);

        if($result) {
            if($_POST['special_id'] == $model_mb_special::INDEX_SPECIAL_ID) {
                showMessage(L('nc_common_save_succ'), urlAdmin('mb_special1', 'popup'));
            } else {
                showMessage(L('nc_common_save_succ'), urlAdmin('mb_special1', 'popup', array('special_id' => $_POST['special_id'])));
            }
        } else {
            showMessage(L('nc_common_save_succ'), '');
        }
    }

    /**
     * 弹窗开关
     */
    public function popup_setOp() {
        $model_mb_special = Model('mb_special1');
        $theitemid=$_GET['item_id'];
        $item_info = $model_mb_special->getMbSpecialItemInfoByID($theitemid);
        $a=$item_info['item_data']['data'];
        $b=strpos($a,'=');
        $item_info['item_data']['data']=substr($a,$b+1);
        //var_dump($item_info['item_usable']);die;
        Tpl::output('item_usable',$item_info['item_usable']);
        if (chksubmit()){
            $result = $model_mb_special->editMbSpecialItemByID(array('item_usable' => $_POST['item_usable']), '320', '1000');
            if ($result === true){
                $this->log('popup_edit_success',1);
                showMessage('弹窗设置成功');
            }else {
                $this->log('popup_edit_fail',0);
                showMessage('弹窗设置失败');
            }
        }
        Tpl::showpage('popup.setting');
    }
}

