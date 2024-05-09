<?php
/**
 *商品名称前缀
 **/
defined('In718Shop') or exit('Access Invalid!');
class store_prefixControl extends BaseSellerControl {

    const LINK_PREFIX_LIST = 'index.php?act=store_prefix&op=prefix_list';
    const LINK_PREFIX_MANAGE = 'index.php?act=store_prefix&op=prefix_manage&prefix_id=';

    public function __construct() {
        parent::__construct() ;
        //读取语言包
        Language::read('member_layout,promotion_xianshi');

    }

    public function indexOp() {
        $this->prefix_listOp();
    }

    /**
     * 前缀词条列表
     **/
    public function prefix_listOp() {
        $model_prefix = Model('prefix');

        if (checkPlatformStore()) {
            Tpl::output('isOwnShop', true);
        }

        $condition = array();
        // $condition['store_id'] = $_SESSION['store_id'];
        if(!empty($_GET['prefix_name'])) {
            $condition['prefix_name'] = array('like', '%'.$_GET['prefix_name'].'%');
            Tpl::output('prefix_name', $_GET['prefix_name']);
        }

        $prefix_list = $model_prefix->getPrefixList($condition, 10);
        Tpl::output('prefix_list', $prefix_list);
        Tpl::output('show_page', $model_prefix->showpage());

        // self::profile_menu('buy_delive_list');
        Tpl::showpage('store_prefix.list');
    }

    /**
     * 添加前缀词条
     **/
    public function prefix_addOp() {
        if (checkPlatformStore()) {
            Tpl::output('isOwnShop', true);
        }
        //输出添加页面
        //输出导航
        //self::profile_menu('buy_deliver_add');
        Tpl::showpage('store_prefix.add');

    }

    /**
     * 保存前缀词条
     **/
    public function prefix_saveOp() {
        $prefix_name = trim($_POST['prefix_name']);
        $prefix_explain = trim($_POST['prefix_explain']);
        if(empty($prefix_name)) {
            showDialog(Language::get('名称不能为空'));
        }

        // //生成活动
        $model_prefix = Model('prefix');
        $param = array();
        $param['prefix_name'] = $prefix_name;
        $param['prefix_explain'] = $prefix_explain;
        $result = $model_prefix->addPrefix($param);
        if($result) {
            $this->recordSellerLog('添加前缀词条，词条编号：'.$result);
            showDialog("前缀词条添加成功",self::LINK_PREFIX_MANAGE.$result,'succ','',3);
        }else {
            showDialog("前缀词条添加失败");
        }
    }

    /**
     * 编辑前缀词条
     **/
    public function prefix_editOp() {
        $model_prefix = Model('prefix');

        $prefix_info = $model_prefix->getPrefixInfo(array('prefix_id' => $_GET['prefix_id']));
        if(empty($prefix_info)) {
            showMessage(L('param_error'),'','','error');
        }
        if(!empty($_GET['prefix_name'])) {
            Tpl::output('search_name', $_GET['prefix_name']);
        }
        Tpl::output('prefix_info', $prefix_info);
        //输出导航
        // self::profile_menu('buy_deliver_edit');
        Tpl::showpage('store_prefix.add');
    }

    /**
     * 编辑保存词条
     * @return [type] [description]
     */
    public function prefix_edit_saveOp() {
        
        $prefix_id = $_POST['prefix_id'];
        $model_prefix = Model('prefix');
        $model_goods = Model('goods');
        $model_prefix_goods = Model('prefix_goods');
        $prefix_info = $model_prefix->getPrefixInfo(array('prefix_id'=>$prefix_id));
        if(empty($prefix_info)) {
            showMessage(L('param_error'),'','','error');
        }
        //验证输入
        $prefix_name = trim($_POST['prefix_name']);
        $prefix_explain = trim($_POST['prefix_explain']);
        if(empty($prefix_name)) {
            showDialog("前缀名称不能为空");
        }
        //生成活动
        $param = array();
        $param['prefix_name'] = $prefix_name;
        $param['prefix_explain'] = $prefix_explain;

        $result = $model_prefix->editPrefix($param, array('prefix_id'=>$prefix_id));
        //编辑商品
        if ($result) {
            $ori_prefix_name = $prefix_info['prefix_name'];
            $goods_list = $model_prefix_goods->getPrefixGoodsExtendList(array('prefix_id'=>$prefix_id));
            if(is_array($goods_list)){
              foreach ($goods_list as $key => $goods_info) {
                $goods_common_name = $goods_info['goods_name'];
                $goods_commonid = $goods_info['goods_commonid'];
                $is_flag_common = strstr($goods_common_name, $ori_prefix_name);//名称中是否匹配原词条
                if ($is_flag_common) {
                    $goods_common_new_name = str_replace($ori_prefix_name, $prefix_name, $goods_common_name);
                }
                else{
                    $goods_common_new_name = $prefix_name.$goods_common_name;
                }
                $updste_result = $model_goods->editGoodsCommonById(array('goods_name' => $goods_common_new_name),$goods_commonid);
                //某个商品编辑失败，处理？
                if ($updste_result) {
                    $goods_list = $model_goods->getGoodsList(array('goods_commonid' => $goods_commonid), 'goods_id, goods_name');
                    foreach ($goods_list as $k => $goods) {
                        $is_flag = strstr($goods['goods_name'], $ori_prefix_name);
                        if ($is_flag) {
                            $goods_new_name = str_replace($ori_prefix_name, $prefix_name, $goods['goods_name']);
                        }else{
                            $goods_new_name = $prefix_name.$goods['goods_name'];
                        }
                        
                        $model_goods->editGoodsById(array('goods_name'=>$goods_new_name),$goods['goods_id']);
                    }
                }
              }
            }
            $this->recordSellerLog('编辑前缀词条，前缀名称：'.$ori_prefix_name.'，编号：'.$prefix_id);
            if (!empty($_POST['search_name'])) {
                showDialog(Language::get('nc_common_op_succ'),self::LINK_PREFIX_LIST.'&prefix_name='.$_POST['search_name'],'succ','',3); 
            }else{
                showDialog(Language::get('nc_common_op_succ'),self::LINK_PREFIX_LIST,'succ','',3); 
            }  
            // showDialog(Language::get('nc_common_op_succ'),self::LINK_PREFIX_LIST,'succ','',3);        
        }else {
            showDialog(Language::get('nc_common_op_fail'));
        }
    }

    /**
     * 前缀词条删除，同时批量恢复商品名称——commonid
     **/
    public function prefix_delOp() {
        $prefix_id = intval($_POST['prefix_id']);
        $model_prefix = Model('prefix');
        $model_goods = Model('goods');
        $model_prefix_goods = Model('prefix_goods');
        $data = array();
        $data['result'] = true;

        $prefix_info = $model_prefix->getPrefixInfo(array('prefix_id'=>$prefix_id));
        if(!$prefix_info) {
            showDialog(L('param_error'));
        }

        $result = $model_prefix->delPrefix(array('prefix_id'=>$prefix_id));

        if($result) {
            $ori_prefix_name = $prefix_info['prefix_name'];
            $goods_list = $model_prefix_goods->getPrefixGoodsExtendList(array('prefix_id'=>$prefix_id));
            if(is_array($goods_list)){
              foreach ($goods_list as $key => $goods_info) {
                $goods_common_name = $goods_info['goods_name'];
                $goods_commonid = $goods_info['goods_commonid'];
                $goods_common_new_name = str_replace($ori_prefix_name, '', $goods_common_name);
                $updste_result = $model_goods->editGoodsCommonById(array('goods_name' => $goods_common_new_name),$goods_commonid);
                //某个商品编辑失败，处理？
                if ($updste_result) {
                    $goods_list = $model_goods->getGoodsList(array('goods_commonid' => $goods_commonid), 'goods_id, goods_name');
                    foreach ($goods_list as $k => $goods) {
                        $goods_new_name = str_replace($ori_prefix_name, '', $goods['goods_name']);
                        $model_goods->editGoodsById(array('goods_name'=>$goods_new_name),$goods['goods_id']);
                    }
                }
                $this->recordSellerLog('删除前缀，前缀：'.$prefix_info['prefix_name'].'，平台货号：'.$goods_commonid);
              }
              $model_prefix_goods->delPrefixGoods(array('prefix_id'=>$prefix_id));
            }
            showDialog(L('nc_common_op_succ'), urlShop('store_prefix', 'prefix_list'), 'succ');
        } else {
            showDialog(L('nc_common_op_fail'));
        }
    }

    /**
     * 管理模块
     * @return [type] [description]
     */
    public function prefix_manageOp() {
        $model_prefix = Model('prefix');
        $model_prefix_goods = Model('prefix_goods');

        $prefix_id = intval($_GET['prefix_id']);
        $prefix_info = $model_prefix->getPrefixInfo(array('prefix_id'=>$prefix_id));
        if(empty($prefix_info)) {
            showDialog(L('param_error'));
        }
        Tpl::output('prefix_info',$prefix_info);
        //获取商品列表
        $condition = array();
        if(!empty($_GET['goods_name'])) {
            $condition['goods_name'] = array('like', '%'.$_GET['goods_name'].'%');
        }
        if($_GET['goods_serial']) {
            $condition['goods_serial'] = array('like', '%' . $_GET['goods_serial'] . '%');
        }       
        $condition['prefix_id'] = $prefix_id;
        $prefix_goods_list = $model_prefix_goods->getPrefixGoodsExtendList($condition,5);
        Tpl::output('prefix_goods_list', $prefix_goods_list);
        //输出导航
        // self::profile_menu('prefix_manage');
        Tpl::output('show_page',$model_prefix->showpage(5));
        Tpl::showpage('store_prefix.manage');
    }

    /*
    ** goods_commonid选择商品
     */
    public function goods_selectOp($value='')
    {
        $model_goods = Model('goods');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        if($_GET['goods_name']) {
            $condition['goods_name'] = array('like', '%' . $_GET['goods_name'] . '%');
        }
        if($_GET['goods_serial']) {
            $condition['goods_serial'] = array('like', '%' . $_GET['goods_serial'] . '%');
        }
        //获取活动表数据
        $goods_commonid_list = Model()->table('prefix_goods')->field('goods_commonid')->where(array('prefix_id' => $_GET['prefix_id']))->select();
        $goods_commonid_str = '';
        foreach ($goods_commonid_list as $key => $value) {
            if ($key > 0) {
                $goods_commonid_str .= ','.$value['goods_commonid'];
            }else{
                $goods_commonid_str .= $value['goods_commonid'];
            }
        }
        $condition['goods_commonid'] = array('not in',$goods_commonid_str);
        $goods_list = $model_goods->getGoodsCommonOnlineList($condition, '*', 10);
        Tpl::output('prefix_id', $_GET['prefix_id']);
        Tpl::output('goods_list', $goods_list);
        Tpl::output('show_page', $model_goods->showpage());
        Tpl::showpage('store_prefix.goods_list', 'null_layout');
    }

    /**
     * 根据commonid添加商品
     **/
    public function prefix_goods_addOp() {        
        $goods_commonid = intval($_POST['goods_commonid']);
        $prefix_id = intval($_POST['prefix_id']);

        $model_goods = Model('goods');
        $model_prefix = Model('prefix');
        $model_prefix_goods = Model('prefix_goods');
        $data = array();
        $data['result'] = true;
        $goods_info = $model_goods->getGoodeCommonInfo(array('goods_commonid' => $goods_commonid),'goods_name, goods_serial, store_id');//注意函数名称
        //$goods_info = $model_goods->getGoodsInfoByID($goods_id);
        if(empty($goods_info) || $goods_info['store_id'] != $_SESSION['store_id'])
        {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }                
        $prefix_info = $model_prefix->getPrefixInfo(array('prefix_id' => $prefix_id));
        if(!$prefix_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }

        //检查商品是否已经添加前缀
        $condition = array();
        $condition['goods_commonid'] = $goods_commonid;
        $condition['prefix_id'] = $prefix_id;
        $prefix_goods = $model_prefix_goods->getPrefixGoodsInfo($condition);
        if (!empty($prefix_goods)) {
            $data['result'] = false;
            $data['message'] = '该前缀已存在';
            echo json_encode($data);die;
        }
        // if(!empty($prefix_goods)) {
        //     //获取前缀名称
        //     $prefix_exist_id = $prefix_goods['prefix_id'];
        //     $prefix_exist_info = $model_prefix->getPrefixInfo(array('prefix_id' => $prefix_exist_id));
        //     $prefix_exist_name = $prefix_exist_info['prefix_name'];
        //     $data['result'] = false;
        //     $data['message'] = '前缀已存在，前缀名称：'.$prefix_exist_name;
        //     echo json_encode($data);die;
        // }

        $param = array();
        $param['prefix_id'] = $prefix_info['prefix_id'];
        $prefix_name = $prefix_info['prefix_name'];
        $param['goods_commonid'] = $goods_commonid;
        $param['goods_name'] = $goods_info['goods_name'];
        $param['goods_serial'] = $goods_info['goods_serial'];
        $result = array();
        $prefix_goods_id = $model_prefix_goods->addPrefixGoods($param);
        if($prefix_goods_id) {
            $goods_name = $prefix_name.$goods_info['goods_name'];

            $update = array('goods_name' => $goods_name);
            $updste_result = $model_goods->editGoodsCommonById($update,$goods_commonid);
            if ($updste_result) {
                $goods_list = $model_goods->getGoodsList(array('goods_commonid' => $goods_commonid), 'goods_id, goods_name');
                foreach ($goods_list as $key => $goods) {
                    $goods_new_name = $prefix_name.$goods['goods_name'];
                    //更新
                    $model_goods->editGoodsById(array('goods_name'=>$goods_new_name),$goods['goods_id']);
                }
            }
            $this->recordSellerLog('添加前缀'.$param['prefix_name'].'，平台货号：'.$goods_commonid);
            $result['result'] = true;
            $data['message'] = '添加成功';
            //$data['prefix_goods'] = $prefix_goods_info;
        } else {
            $data['result'] = false;
            $data['message'] = L('param_error');
        }
        echo json_encode($data);die;
    }

    /**
     * 批量添加商品
     * @return [type] [description]
     */
    public function prefix_goods_add_allOp(){
        $goods_ids = $_GET['goods_id'];
        $goods_id_arr = explode(',',$goods_ids);
        array_pop($goods_id_arr);
        $goods_list = Model('goods')->getGoodsCommonList(array('goods_commonid'=>array('in',$goods_id_arr)),'goods_commonid,goods_name,goods_image');
        Tpl::output('prefix_id', $_GET['prefix_id']);
        Tpl::output('goods_list', $goods_list);
        Tpl::showpage('store_prefix.goods_add','null_layout');
    }

    /**
     * 批量添加保存商品
     * @return [type] [description]
     */
    public function prefix_goods_add_all_saveOp(){
        $goods_list = $_POST['goods'];
        $model_goods = Model('goods');
        $model_prefix = Model('prefix');
        $model_prefix_goods = Model('prefix_goods');
        $prefix_id = intval($_POST['prefix_id']);
        $succ = array();
        $fail = array();
        if($goods_list){
            if(is_array($goods_list)){
                foreach ($goods_list as $key=>$value) {
                    $goods_info = $model_goods->getGoodeCommonInfo(array('goods_commonid' => $key),'store_id,goods_name,goods_serial');//通过commonid获取商品common信息
                    if (empty($goods_info) || $goods_info['store_id'] != $_SESSION['store_id']) {
                        $fail[] = $value['goods_name']."不存在";
                        continue;
                    }

                    $prefix_info = $model_prefix->getPrefixInfo(array('prefix_id' => $prefix_id));
                    if(!$prefix_info) {
                        $fail[] = "前缀词条不存在";
                        break;
                    }

                    $condition = array();
                    $condition['goods_commonid'] = $key;
                    $condition['prefix_id'] = $prefix_id;
                    $prefix_goods = $model_prefix_goods->getPrefixGoodsList($condition);
                    if (!empty($prefix_goods)) {
                        $fail[] = $value['goods_name']."该前缀已存在";
                        continue;
                    }
                    // if(!empty($prefix_goods)) {
                    //     //获取前缀名称
                    //     $prefix_exist_id = $prefix_goods['prefix_id'];
                    //     $prefix_exist_info = $model_prefix->getPrefixInfo(array('prefix_id' => $prefix_exist_id));
                    //     $prefix_exist_name = $prefix_exist_info['prefix_name'];
                    //     $fail[] = $value['goods_name']."前缀存在，前缀名称：".$prefix_exist_name;
                    //     continue;
                    // }

                    $param = array();
                    $param['prefix_id'] = $prefix_id;
                    $prefix_name = $prefix_info['prefix_name'];
                    //$param['goods_id'] = $key;
                    $param['goods_name'] = $goods_info['goods_name'];
                    $param['goods_serial'] = $goods_info['goods_serial'];
                    $param['goods_commonid'] = $key;
                    $result = array();
                    $prefix_goods_id = $model_prefix_goods->addPrefixGoods($param);
                    if($prefix_goods_id) {
                        $goods_name = $prefix_name.$goods_info['goods_name'];
                        $update = array('goods_name' => $goods_name);
                        $updste_result = $model_goods->editGoodsCommonById($update,$key);
                        if ($updste_result) {
                            $goods_list = $model_goods->getGoodsList(array('goods_commonid' => $key), 'goods_id, goods_name');
                            foreach ($goods_list as $k => $goods) {
                                $goods_new_name = $prefix_name.$goods['goods_name'];
                                //更新
                                $model_goods->editGoodsById(array('goods_name'=>$goods_new_name),$goods['goods_id']);
                            }
                        }
                        $this->recordSellerLog('批量添加前缀'.$param['prefix_name'].'，平台货号：'.$key);
                        $succ[] = $value['goods_name'];
                    } else {
                        $fail[] = $value['goods_name'];
                    }
                }
                !empty($succ) ? $succ = '商品名称：' . implode(',', $succ) . '添加成功。' : $succ = '';
                !empty($fail) ? $fail = '商品名称：' . implode(',', $fail) . '添加失败。' : $fail = '';
                $msg = '批量添加商品前缀，'. $succ . $fail;                
                $this->recordSellerLog($msg);
                showDialog($msg,'reload','notice','',5);
            }
        }
    }

    /**
     * 前缀商品删除——commonid
     **/
    public function prefix_goods_deleteOp() {
        $model_prefix_goods = Model('prefix_goods');
        $model_prefix = Model('prefix');
        $model_goods = Model('goods');
        $data = array();
        $data['result'] = true;
        $prefix_goods_id = intval($_POST['prefix_goods_id']);
        $prefix_goods_info = $model_prefix_goods->getPrefixGoodsInfo(array('prefix_goods_id' => $prefix_goods_id));
        if(!$prefix_goods_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
        $prefix_info = $model_prefix->getPrefixInfo(array('prefix_id' => $prefix_goods_info['prefix_id']));
        if(!$prefix_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
        if(!$model_prefix_goods->delPrefixGoods(array('prefix_goods_id'=>$prefix_goods_id))) {
            $data['result'] = false;
            $data['message'] = "商品删除失败";
            echo json_encode($data);die;
        }
        //恢复商品名称 goods_common和goods表
        $goods_commonid = $prefix_goods_info['goods_commonid'];
        $prefix_name = $prefix_info['prefix_name'];
        //获取goods_common名称
        $goods_common_info = $model_goods->getGoodeCommonInfo(array('goods_commonid' => $goods_commonid),'goods_name');
        $goods_common_name = $goods_common_info['goods_name'];
        $prefix_name = $prefix_info['prefix_name'];
        $goods_common_new_name = str_replace($prefix_name, '', $goods_common_name);
        $goods_common_update = array('goods_name' => $goods_common_new_name);
        $model_goods->editGoodsCommonById($goods_common_update,$goods_commonid);
        $goods_list = $model_goods->getGoodsList(array('goods_commonid' => $goods_commonid), 'goods_id, goods_name');
        foreach ($goods_list as $k => $goods) {
            $goods_new_name = str_replace($prefix_name, '', $goods['goods_name']);
            $model_goods->editGoodsById(array('goods_name'=>$goods_new_name),$goods['goods_id']);
        }
        $this->recordSellerLog('删除前缀：'.$prefix_info['prefix_name'].'，平台货号：'.$goods_commonid);
        echo json_encode($data);die;
    }
    
    /**
     * 历史数据处理-同步commonid
     */
    public function save_nameOp(){
        //获取活动商品
        $model_prefix_goods = Model('prefix_goods');
        $condition = array();
        $prefix_goods_list = $model_prefix_goods->getPrefixGoodsList($condition,'','','prefix_goods_id,goods_id,goods_commonid',$limit='500');
        $updateArray = array();
        foreach ($prefix_goods_list as $k => $goods) {
            if ($goods['goods_commonid'] == 0) {
                $prefix_goods_id = $goods['prefix_goods_id'];
                //获取商品commonid，更新goods_commonid
                $goods_info = Model()->table('goods')->field('goods_commonid')->where(array('goods_id' => $goods['goods_id']))->find();
                $goods_commonid = $goods_info['goods_commonid'];
                $model_prefix_goods->editPrefixGoods(array('goods_commonid' => $goods_commonid),array('prefix_goods_id' => $prefix_goods_id));
                $updateArray[$prefix_goods_id] = $goods_commonid;
            }            
        }
        echo "success<br/>";
        var_dump($updateArray);        
    }

    /**
     * 根据发货人添加
     */
    public function prefix_daddress_addOp() {
        $model_goods = Model('goods');
        $model_prefix = Model('prefix');
        $model_prefix_goods = Model('prefix_goods');
        $prefix_id = intval($_POST['prefix_id']);
        $deliver_id = $_POST['daddress_id'];
        if (empty($deliver_id)) {
            $data['result'] = false;
            $data['message'] = '请选择发货人';
            echo json_encode($data);die;
        }
        $goods_common_list = $model_goods->getGoodsCommonList(array('deliverer_id' => $deliver_id),'goods_commonid, goods_name, goods_serial, store_id');
        $prefix_info = $model_prefix->getPrefixInfo(array('prefix_id' => $prefix_id));
        if(!$prefix_info) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
        $succ = array();
        $fail = array();
        $data = array();
        $data['result'] = true; 
        //3. 循环设置
        foreach ($goods_common_list as $key => $goods_info) {
            $goods_commonid = $goods_info['goods_commonid'];             

            //检查商品是否已经添加前缀
            $condition = array();
            $condition['goods_commonid'] = $goods_commonid;
            $condition['prefix_id'] = $prefix_id;
            $prefix_goods = $model_prefix_goods->getPrefixGoodsInfo($condition);
            if(!empty($prefix_goods)) {
                //获取前缀名称
                $prefix_exist_id = $prefix_goods['prefix_id'];
                $prefix_exist_info = $model_prefix->getPrefixInfo(array('prefix_id' => $prefix_exist_id));
                $prefix_exist_name = $prefix_exist_info['prefix_name'];
                $fail[] = $goods_info['goods_name']."前缀已存在";
                continue;
            }

            $param = array();
            $param['prefix_id'] = $prefix_info['prefix_id'];
            $prefix_name = $prefix_info['prefix_name'];
            $param['goods_commonid'] = $goods_commonid;
            $param['goods_name'] = $goods_info['goods_name'];
            $param['goods_serial'] = $goods_info['goods_serial'];
            $result = array();
            $prefix_goods_id = $model_prefix_goods->addPrefixGoods($param);
            if($prefix_goods_id) {
                $goods_name = $prefix_name.$goods_info['goods_name'];
                $update = array('goods_name' => $goods_name);
                $updste_result = $model_goods->editGoodsCommonById($update,$goods_commonid);
                if ($updste_result) {
                    $goods_list = $model_goods->getGoodsList(array('goods_commonid' => $goods_commonid), 'goods_id, goods_name');
                    foreach ($goods_list as $key => $goods) {
                        $goods_new_name = $prefix_name.$goods['goods_name'];
                        //更新
                        $model_goods->editGoodsById(array('goods_name'=>$goods_new_name),$goods['goods_id']);
                    }
                }
                $this->recordSellerLog('添加前缀'.$param['prefix_name'].'，平台货号：'.$goods_commonid);
                $succ[] = $value['goods_name'];
            }else{
                $fail[] = $value['goods_name'];
            }
        }
        !empty($succ) ? $succ = '商品名称：' . implode(',', $succ) . '添加成功。' : $succ = '';
        !empty($fail) ? $fail = '商品名称：' . implode(',', $fail) . '添加失败。' : $fail = '';
        $msg = '添加前缀，'. $succ . $fail;                
        $this->recordSellerLog($msg);
        $data['result'] = true;
        $data['message'] = $msg;
        echo json_encode($data);die;     
        
    }

    //查找发货人
    public function daddress_selectOp()
    {
        $model_daddress = Model('daddress');
        $condition = array();
        if($_GET['seller_name']) {
            $condition['seller_name'] = array('like', '%' . $_GET['seller_name'] . '%');
        }
        $daddress_list = $model_daddress->field('address_id,seller_name')->where($condition)->page(10)->select();
        Tpl::output('prefix_id', $_GET['prefix_id']);
        Tpl::output('daddress_list', $daddress_list);
        Tpl::output('show_page', $model_daddress->showpage());
        Tpl::showpage('store_prefix.daddress_list', 'null_layout');
    }
}
