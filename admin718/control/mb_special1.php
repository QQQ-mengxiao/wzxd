<?php
/**
 * 手机专题
 */

defined('In718Shop') or exit('Access Invalid!');
class mb_special1Control extends SystemControl{
    public function __construct(){
        parent::__construct();
    }

    /**
     * 专题列表
     */
    public function special_listOp() {
        $model_mb_special = Model('mb_special1');

         if($_GET['search_special_desc']){
            $condition['special_desc'] = $_GET['search_special_desc'];
    }

        $mb_special_list = $model_mb_special->getMbSpecialList($condition, 10,'special_id asc');
        Tpl::output('search_special_desc',$_GET['search_special_desc']);
        Tpl::output('list', $mb_special_list);
        Tpl::output('page', $model_mb_special->showpage(2));

        $this->show_menu('special_list');
        Tpl::showpage('mb_special1.list');
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
    /**
     * 专题项目保存
     */
    public function special_item_saveOp() {
         $model_mb_special = Model('mb_special1');
        if ($_POST['item_data']['title'] == '轮播图') {
            foreach ($_POST['item_data']['item'] as $key => $item) {
                $volume[$key] = $item['sort'];
            }
            array_multisort($volume, SORT_ASC, $_POST['item_data']['item']);
        }
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
  /*  public function special_item_saveOp() {

        $model_mb_special = Model('mb_special1');
        if ($_POST['item_data']['title'] == '轮播图') {
            foreach ($_POST['item_data']['item'] as $key => $item) {
                $volume[$key] = $item['sort'];
            }
            array_multisort($volume, SORT_ASC, $_POST['item_data']['item']);
        }
       
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
    }*/

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
            $goods_list = $model_goods->getGoodsOnlineList($condition, 'goods_id,goods_name,goods_promotion_price,goods_price,goods_image', 10);
            // var_dump($goods_list);die;
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
        $a=$item_info['item_data']['data'];
        $b=strpos($a,'=');
        $item_info['item_data']['data']=substr($a,$b+1);
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

