<?php
/**
 * 前台商品   */


defined('In718Shop') or exit('Access Invalid!');

class goodsControl extends BaseGoodsControl {
    public function __construct() {
        parent::__construct ();
        Language::read('store_goods_index');
    }

    /**
     * 单个商品信息页
     * 添加会员折扣商品显示   先判断该商品是否参加了会员折扣  如果参加  商品显示的价格则为原来的价格*会员等级对应的折扣率
     * 需要获取 当前用户的会员等级 当前商品是否参与会员折扣标志
     */
    public function indexOp() {
        $goods_id = intval($_GET['goods_id']);

        //获取折扣率
        $discount = Model('member')->getDiscount($_SESSION['member_id']);

        // 商品详细信息
        $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id);
        $goods_info = $goods_detail['goods_info'];
        //echo $goods_info['goods_tax'];
        //break;
        if (empty($goods_info)) {
            showMessage(L('goods_index_no_goods'), '', 'html', 'error');
        }
		// by
		$rs = $model_goods->getGoodsList(array('goods_commonid'=>$goods_info['goods_commonid']));
		$count = 0;
		foreach($rs as $v){
			$count += $v['goods_salenum'];
		}
		$goods_info['goods_salenum'] = $count;
        $goods_info['goods_presalenum'] =$goods_info['goods_presalenum'] + $count;
		//  添加 end
        $this->getStoreInfo($goods_info['store_id']);
	 // 看了又看（同分类本店随机商品）v3-b12
        $size = $goods_info['is_own_shop'] ? 8 : 4;/////////是否为自营店铺，TRUE:大小设置为8，否则是4
        ///////////获取指定分类指定店铺下的随机商品列表(一级分类ID，店铺ID，此商品除外，列表最大长度)
        ///////////此商品除外即推荐列表里面不显示当前查看的商品
        $goods_rand_list = Model('goods')->getGoodsGcStoreRandList($goods_info['gc_id_1'], $goods_info['store_id'], $goods_info['goods_id'], $size);
        //20181120 修改，隐藏会员折扣价
        foreach ($goods_rand_list as $key => $value){
             $is_vip_price = Model()->table('goods_common')->getfby_goods_commonid($value['goods_commonid'],'is_vip_price');
             if($is_vip_price == 1){
                 $goods_rand_list[$key]['goods_price'] = ncPriceFormat($value['goods_price'] * $discount);
                 $goods_rand_list[$key]['goods_promotion_price'] = ncPriceFormat($value['goods_promotion_price'] * $discount);
             }
         }
        Tpl::output('goods_rand_list', $goods_rand_list);

        Tpl::output('spec_list', $goods_detail['spec_list']);
        Tpl::output('spec_image', $goods_detail['spec_image']);
        Tpl::output('goods_image', $goods_detail['goods_image']);
	Tpl::output('mansong_all', $goods_detail['mansong_all']);//xinjia
        Tpl::output('mansong_info', $goods_detail['mansong_info']);
        Tpl::output('gift_array', $goods_detail['gift_array']);

        // 生成缓存的键值
        $hash_key = $goods_info['goods_id'];
        $_cache = rcache($hash_key, 'product');
        if (empty($_cache)) {
            // 查询SNS中该商品的信息
            $snsgoodsinfo = Model('sns_goods')->getSNSGoodsInfo(array('snsgoods_goodsid' => $goods_info['goods_id']), 'snsgoods_likenum,snsgoods_sharenum');
            $data = array();
            $data['likenum'] = $snsgoodsinfo['snsgoods_likenum'];
            $data['sharenum'] = $snsgoodsinfo['snsgoods_sharenum'];
            // 缓存商品信息
            wcache($hash_key, $data, 'product');
        }
        $goods_info = array_merge($goods_info, $_cache);

        $inform_switch = true;
        // 检测商品是否下架,检查是否为店主本人
        if ($goods_info['goods_state'] != 1 || $goods_info['goods_verify'] != 1 || $goods_info['store_id'] == $_SESSION['store_id']) {
            $inform_switch = false;
        }
        Tpl::output('inform_switch',$inform_switch );

        // 如果使用售卖区域
        if ($goods_info['transport_id'] > 0) {
            // 取得三种运送方式默认运费
            $model_transport = Model('transport');
            $transport = $model_transport->getExtendList(array('transport_id' => $goods_info['transport_id'], 'is_default' => 1));
            if (!empty($transport) && is_array($transport)) {
                foreach ($transport as $v) {
                    $goods_info[$v['type'] . "_price"] = $v['sprice'];
                }
            }
        }
        if($goods_info['is_vip_price'] ==1){
            $goods_info['goods_vip_price'] =  ncPriceFormat($goods_info['goods_price'] *$discount);
             $goods_info['goods_app_price'] =  ncPriceFormat($goods_info['goods_app_price'] *$discount);
             if($goods_info['goods_promotion_price']) {
             $goods_info['goods_promotion_price'] = ncPriceFormat($goods_info['goods_promotion_price'] * $discount);
             }
             if($goods_info['promotion_price']) {
            $goods_info['promotion_price'] = ncPriceFormat($goods_info['promotion_price'] * $discount);
             }
             $goods_info['down_price'] = ncPriceFormat($goods_info['goods_price'] - $goods_info['promotion_price']);
         }
        Tpl::output('goods', $goods_info);

        //获取等级名称
        $model_member = Model('member');
        $member_exppoints = $model_member->getfby_member_id($_SESSION['member_id'],'member_exppoints');
        $member_grade = ($t = $model_member->getOneMemberGrade($member_exppoints))?$t['level_name']:'';
        Tpl::output('member_grade', $member_grade);

		//抢购商品是否开始
		$IsHaveBuy=0;
		if(!empty($_SESSION['member_id']))
		{
		   $buyer_id=$_SESSION['member_id'];
		   $promotion_type=$goods_info["promotion_type"];
		   if($promotion_type=='groupbuy')
		   {   
		    //检测是否限购数量
			$upper_limit=$goods_info["upper_limit"];
			if($upper_limit>0)
			{
				//查询些会员的订单中，是否已买过了
				$model_order= Model('order');
				 //取商品列表
                $order_goods_list = $model_order->getOrderGoodsList(array('goods_id'=>$goods_id,'buyer_id'=>$buyer_id,'goods_type'=>2));
				if($order_goods_list)
				{   
				    //取得上次购买的活动编号(防一个商品参加多次团购活动的问题)
				    $promotions_id=$order_goods_list[0]["promotions_id"];
					//用此编号取数据，检测是否这次活动的订单商品。
					 $model_groupbuy = Model('groupbuy');
					 $groupbuy_info = $model_groupbuy->getGroupbuyInfo(array('groupbuy_id' => $promotions_id));
                    //MX0825判断order_goods表里同一用户购买抢购商品的数量是否超过抢购上限
                    $num = $model_order->getOrderGoodsList(array('promotions_id' => $promotions_id));
                    if($groupbuy_info){
                        if(count($num)>=$goods_info['upper_limit']) {
                            $IsHaveBuy = 1;
                        }
                    }else{
                        $IsHaveBuy=0;
                    }
                }
            }
           }
        }
		Tpl::output('IsHaveBuy',$IsHaveBuy);
		//end

        $model_plate = Model('store_plate');
        // 顶部关联版式
        if ($goods_info['plateid_top'] > 0) {
            $plate_top = $model_plate->getStorePlateInfoByID($goods_info['plateid_top']);
            Tpl::output('plate_top', $plate_top);
        }
        // 底部关联版式
        if ($goods_info['plateid_bottom'] > 0) {
            $plate_bottom = $model_plate->getStorePlateInfoByID($goods_info['plateid_bottom']);
            Tpl::output('plate_bottom', $plate_bottom);
        }
        Tpl::output('store_id', $goods_info['store_id']);
        
        //推荐商品
        $goods_commend_list = $model_goods->getGoodsOnlineList(array('store_id' => $goods_info['store_id'], 'goods_commend' => 1), 'goods_id,goods_name,goods_jingle,goods_image,store_id,goods_price,is_vip_price', 0, 'rand()', 5, 'goods_commonid');
        Tpl::output('goods_commend',$goods_commend_list);//多获取is_vip_price


        // 当前位置导航
        $nav_link_list = Model('goods_class')->getGoodsClassNav($goods_info['gc_id'], 0);
        $nav_link_list[] = array('title' => $goods_info['goods_name']);
        Tpl::output('nav_link_list', $nav_link_list);

		//商品分类id'
		$gc_list=rkcache('gc_list1',true);
	
        Tpl::output('gc_list', $gc_list);

        //181119 新添加 根据会员不同等级获取对应的一级比例佣金
       
        $condition = $goods_info['goods_commonid'];
        $goods_common_info = $model_goods->getGoodeCommonInfo1(array('goods_commonid'=>$condition));
        if ($goods_common_info['goods_fx_percent']!=null) {
            $goods_fx_percent = unserialize($goods_common_info['goods_fx_percent']);
           
        //获取登录会员的会员等级名称
        $model_member = Model('member');
        $member_exppoints = $model_member->getfby_member_id($_SESSION['member_id'],'member_exppoints');
        $member_grade = ($t = $model_member->getOneMemberGrade($member_exppoints))?$t['level_name']:'';
       // var_dump($member_grade);die();
    
             
            if($member_grade == 'V1 青铜'){
                $goods_most_percent = ncPriceFormat($goods_common_info['goods_price']*$goods_fx_percent[0]/100);

            }
            if($member_grade == 'V2 白银'){
                $goods_most_percent = ncPriceFormat($goods_common_info['goods_price']*$goods_fx_percent[1]/100);

            }
            if($member_grade == 'V3 黄金'){
                $goods_most_percent = ncPriceFormat($goods_common_info['goods_price']*$goods_fx_percent[2]/100);

            }
            if($member_grade == 'V4 铂金'){
                $goods_most_percent = ncPriceFormat($goods_common_info['goods_price']*$goods_fx_percent[3]/100);

            }
           
           
           //获取一级分销比例的最大值
           // $goods_fx_percent_most = max($goods_fx_percent);
           // $goods_most_percent = ncPriceFormat($goods_common_info['goods_price']*$goods_fx_percent_most/100);
            $goods_is_fenxiao = $goods_common_info['is_fenxiao'];
        }
        Tpl::output('goods_most_percent', $goods_most_percent);//计算商品郑欧币对应不同会员等级的获取值
        Tpl::output('goods_is_fenxiao', $goods_is_fenxiao);//判断商品是否是分销商品

        //评价信息
        $goods_evaluate_info = Model('evaluate_goods')->getEvaluateGoodsInfoByGoodsID($goods_id);
        Tpl::output('goods_evaluate_info', $goods_evaluate_info);

        $seo_param = array();
        $seo_param['name'] = $goods_info['goods_name'];
        $seo_param['key'] = $goods_info['goods_keywords'];
        $seo_param['description'] = $goods_info['goods_description'];
        Model('seo')->type('product')->param($seo_param)->show();
        Tpl::showpage('goods');
    }
    /**
     * 记录浏览历史
     */
    public function addbrowseOp(){
        $goods_id = intval($_GET['gid']);
        Model('goods_browse')->addViewedGoods($goods_id,$_SESSION['member_id'],$_SESSION['store_id']);
        exit();
    }

    /**
	 * 商品评论
	 */
	public function commentsOp() {
        $goods_id = intval($_GET['goods_id']);
        $this->_get_comments($goods_id, $_GET['type'], 10);
		Tpl::showpage('goods.comments','null_layout');
	}

    /**
     * 商品评价详细页
     */
    public function comments_listOp() {
        $goods_id = intval($_GET['goods_id']);

        // 商品详细信息
        $model_goods = Model('goods');
        $goods_info = $model_goods->getGoodsInfoByID($goods_id, '*');
        // 验证商品是否存在
        if (empty($goods_info)) {
            showMessage(L('goods_index_no_goods'), '', 'html', 'error');
        }
        Tpl::output('goods', $goods_info);

        $this->getStoreInfo($goods_info['store_id']);

        // 当前位置导航
        $nav_link_list = Model('goods_class')->getGoodsClassNav($goods_info['gc_id'], 0);
        $nav_link_list[] = array('title' => $goods_info['goods_name'], 'link' => urlShop('goods', 'index', array('goods_id' => $goods_id)));
        $nav_link_list[] = array('title' => '商品评价');
        Tpl::output('nav_link_list', $nav_link_list );

        //评价信息
        $goods_evaluate_info = Model('evaluate_goods')->getEvaluateGoodsInfoByGoodsID($goods_id);
        Tpl::output('goods_evaluate_info', $goods_evaluate_info);

        $seo_param = array ();

        $seo_param['name'] = $goods_info['goods_name'];
        $seo_param['key'] = $goods_info['goods_keywords'];
        $seo_param['description'] = $goods_info['goods_description'];
        Model('seo')->type('product')->param($seo_param)->show();

        $this->_get_comments($goods_id, $_GET['type'], 20);

		Tpl::showpage('goods.comments_list');
    }

    private function _get_comments($goods_id, $type, $page) {
        $condition = array();
        $condition['geval_goodsid'] = $goods_id;
        switch ($type) {
            case '1':
                $condition['geval_scores'] = array('in', '5,4');
                Tpl::output('type', '1');
                break;
            case '2':
                $condition['geval_scores'] = array('in', '3,2');
                Tpl::output('type', '2');
                break;
            case '3':
                $condition['geval_scores'] = array('in', '1');
                Tpl::output('type', '3');
                break;
        }

        //查询商品评分信息
        $model_evaluate_goods = Model("evaluate_goods");
        $goodsevallist = $model_evaluate_goods->getEvaluateGoodsList($condition, $page);
        Tpl::output('goodsevallist',$goodsevallist);
        Tpl::output('show_page',$model_evaluate_goods->showpage('5'));
    }

    /**
     * 销售记录
     */
    public function salelogOp() {
        $goods_id	 = intval($_GET['goods_id']);
        if ($_GET['vr']) {
            $model_order = Model('vr_order');
            $sales = $model_order->getOrderAndOrderGoodsSalesRecordList(array('goods_id'=>$goods_id), '*', 10);
        } else {
            $model_order = Model('order');
            $sales = $model_order->getOrderAndOrderGoodsSalesRecordList(array('order_goods.goods_id'=>$goods_id), 'order_goods.*, order.buyer_name, order.add_time', 10);
        }
        Tpl::output('show_page',$model_order->showpage());
        Tpl::output('sales',$sales);

        Tpl::output('order_type', array(2=>'抢', 3=>'折', '4'=>'套装'));
        Tpl::showpage('goods.salelog','null_layout');
    }

    /**
     * 产品咨询
     */
    public function consultingOp() {
        $goods_id = intval($_GET['goods_id']);
        if($goods_id <= 0){
            showMessage(Language::get('wrong_argument'),'','html','error');
        }

        //得到商品咨询信息
        $model_consult = Model('consult');
        $where = array();
        $where['goods_id'] = $goods_id;
        if (intval($_GET['ctid']) > 0) {
            $where['ct_id'] = intval($_GET['ct_id']);
        }
        $consult_list = $model_consult->getConsultList($where,'*','10');
        Tpl::output('consult_list',$consult_list);

        // 咨询类型
        $consult_type = rkcache('consult_type', true);
        Tpl::output('consult_type', $consult_type);

        Tpl::output('consult_able',$this->checkConsultAble());
        Tpl::showpage('goods.consulting', 'null_layout');
    }

    /**
     * 产品咨询
     */
    public function consulting_listOp() {
        Tpl::output('hidden_nctoolbar', 1);
        $goods_id    = intval($_GET['goods_id']);
        if($goods_id <= 0){
            showMessage(Language::get('wrong_argument'),'','html','error');
        }

        // 商品详细信息
        $model_goods = Model('goods');
        $goods_info = $model_goods->getGoodsInfoByID($goods_id, '*');
        // 验证商品是否存在
        if (empty($goods_info)) {
            showMessage(L('goods_index_no_goods'), '', 'html', 'error');
        }
        Tpl::output('goods', $goods_info);

        $this->getStoreInfo($goods_info['store_id']);

        // 当前位置导航
        $nav_link_list = Model('goods_class')->getGoodsClassNav($goods_info['gc_id'], 0);
        $nav_link_list[] = array('title' => $goods_info['goods_name'], 'link' => urlShop('goods', 'index', array('goods_id' => $goods_id)));
        $nav_link_list[] = array('title' => '商品咨询');
        Tpl::output('nav_link_list', $nav_link_list);

        //得到商品咨询信息
        $model_consult = Model('consult');
        $where = array();
        $where['goods_id'] = $goods_id;
        if (intval($_GET['ctid']) > 0) {
            $where['ct_id']  = intval($_GET['ctid']);
        }
        $consult_list = $model_consult->getConsultList($where, '*', 0, 20);
        Tpl::output('consult_list',$consult_list);
        Tpl::output('show_page', $model_consult->showpage());

        // 咨询类型
        $consult_type = rkcache('consult_type', true);
        Tpl::output('consult_type', $consult_type);

        $seo_param = array ();
        $seo_param['name'] = $goods_info['goods_name'];
        $seo_param['key'] = $goods_info['goods_keywords'];
        $seo_param['description'] = $goods_info['goods_description'];
        Model('seo')->type('product')->param($seo_param)->show();

        Tpl::output('consult_able',$this->checkConsultAble($goods_info['store_id']));
		Tpl::showpage('goods.consulting_list');
	}

    private function checkConsultAble( $store_id = 0) {
        //检查是否为店主本身
        $store_self = false;
        if(!empty($_SESSION['store_id'])) {
            if (($store_id == 0 && intval($_GET['store_id']) == $_SESSION['store_id']) || ($store_id != 0 && $store_id == $_SESSION['store_id'])) {
                $store_self = true;
            }
        }
        //查询会员信息
        $member_info	= array();
        $member_model = Model('member');
        if(!empty($_SESSION['member_id'])) $member_info = $member_model->getMemberInfoByID($_SESSION['member_id'],'is_allowtalk');
        //检查是否可以评论
        $consult_able = true;
        if((!C('guest_comment') && !$_SESSION['member_id'] ) || $store_self == true || ($_SESSION['member_id']>0 && $member_info['is_allowtalk'] == 0)){
            $consult_able = false;
        }
        return $consult_able;
    }

	/**
	 * 商品咨询添加
	 */
	public function save_consultOp(){
		//检查是否可以评论
        if(!C('guest_comment') && !$_SESSION['member_id']){
            showDialog(L('goods_index_goods_noallow'));
        }
		$goods_id	 = intval($_POST['goods_id']);
		if($goods_id <= 0){
		    showDialog(L('wrong_argument'));
		}
		//咨询内容的非空验证
		if(trim($_POST['goods_content'])== ""){
		    showDialog(L('goods_index_input_consult'));
		}
		//表单验证
		$result = chksubmit(true,C('captcha_status_goodsqa'),'num');
		if (!$result){
		    showDialog(L('invalid_request'));
		} elseif ($result === -11){
	        showDialog(L('invalid_request'));
	    }elseif ($result === -12){
	        showDialog(L('wrong_checkcode'));
	    }
        if (process::islock('commit')){
            showDialog(L('nc_common_op_repeat'));
        }else{
        	process::addprocess('commit');
        }
        if($_SESSION['member_id']){
	        //查询会员信息
	        $member_model = Model('member');
	        $member_info = $member_model->getMemberInfo(array('member_id'=>$_SESSION['member_id']));
			if(empty($member_info) || $member_info['is_allowtalk'] == 0){
			    showDialog(L('goods_index_goods_noallow'));
	        }
        }
		//判断商品编号的存在性和合法性
		$goods = Model('goods');
		$goods_info = $goods->getGoodsInfoByID($goods_id, 'goods_name,store_id');
		if(empty($goods_info)){
		    showDialog(L('goods_index_goods_not_exists'));
		}
        //判断是否是店主本人
        if($_SESSION['store_id'] && $goods_info['store_id'] == $_SESSION['store_id']) {
            showDialog(L('goods_index_consult_store_error'));
        }
		//检查店铺状态
		$store_model = Model('store');
		$store_info	= $store_model->getStoreInfoByID($goods_info['store_id']);
		if($store_info['store_state'] == '0' || intval($store_info['store_state']) == '2' || (intval($store_info['store_end_time']) != 0 && $store_info['store_end_time'] <= time())){
		    showDialog(L('goods_index_goods_store_closed'));
		}
		//接收数据并保存
		$input	= array();
		$input['goods_id']			= $goods_id;
		$input['goods_name']		= $goods_info['goods_name'];
		$input['member_id']			= intval($_SESSION['member_id']) > 0?$_SESSION['member_id']:0;
		$input['member_name']		= $_SESSION['member_name']?$_SESSION['member_name']:'';
		$input['store_id']			= $store_info['store_id'];
		$input['store_name']        = $store_info['store_name'];
		$input['ct_id']             = intval($_POST['consult_type_id']);
		$input['consult_addtime']   = TIMESTAMP;
		if (strtoupper(CHARSET) == 'GBK') {
			$input['consult_content']	= Language::getGBK($_POST['goods_content']);
		}else{
			$input['consult_content']	= $_POST['goods_content'];
		}
		$input['isanonymous']		= $_POST['hide_name']=='hide'?1:0;
		$consult_model	= Model('consult');
		if($consult_model->addConsult($input)){
		    showDialog(L('goods_index_consult_success'), 'reload', 'succ');
		}else{
		    showDialog(L('goods_index_consult_fail'));
		}
	}

    /**
     * 异步显示优惠套装/推荐组合
     */
    public function get_bundlingOp() {
        $goods_id = intval($_GET['goods_id']);
        if ($goods_id <= 0) {
            exit();
        }
        $model_goods = Model('goods');
        $goods_info = $model_goods->getGoodsOnlineInfoByID($goods_id);
        if (empty($goods_info)) {
            exit();
        }

        // 优惠套装
        $array = Model('p_bundling')->getBundlingCacheByGoodsId($goods_id);
        if (!empty($array)) {
            Tpl::output('bundling_array', unserialize($array['bundling_array']));
            Tpl::output('b_goods_array', unserialize($array['b_goods_array']));
        }

        // 推荐组合
        if (!empty($goods_info) && $model_goods->checkIsGeneral($goods_info)) {
            $array = Model('goods_combo')->getGoodsComboCacheByGoodsId($goods_id);
            Tpl::output('goods_info', $goods_info);
            Tpl::output('gcombo_list', unserialize($array['gcombo_list']));
        }

        Tpl::showpage('goods_bundling', 'null_layout');
    }

    /**
     * 商品详细页运费显示
     *
     * @return unknown
     */
    public function calcOp(){
        if (!is_numeric($_GET['area_id']) || !is_numeric($_GET['tid'])) return false;
        $freight_total = Model('transport')->calc_transport(intval($_GET['tid']),intval($_GET['area_id']));
        //$freight_total = Model('transport')->calc_transport_weight(intval($_GET['tid']),intval($_GET['area_id']));
        if ($freight_total > 0) {
            if ($_GET['myf'] > 0) {
                if ($freight_total >= $_GET['myf']) {
                    $freight_total = '免运费，偏远地区除外';
                } else {
                    //$freight_total = '运费：'.$freight_total.' 元，店铺满 '.$_GET['myf'].' 元 免运费';
					$freight_total = '店铺满 '.$_GET['myf'].' 元 免运费，偏远地区除外';
                }      
            } else {
                //$freight_total = '运费：'.$freight_total.' 元';
                $freight_total = '';
            }
        } else {
            if ($freight_total !== false) {
                $freight_total = '免运费，偏远地区除外';
            }
        }
        echo $_GET['callback'].'('.json_encode(array('total'=>$freight_total)).')';
	}

    /**
     * 到货通知
     */
    public function arrival_noticeOp() {
        if (!$_SESSION['is_login'] ){
            showMessage(L('wrong_argument'), '', '', 'error');
        }
        $member_info = Model('member')->getMemberInfoByID($_SESSION['member_id'], 'member_email,member_mobile');
        Tpl::output('member_info', $member_info);

        Tpl::showpage('arrival_notice.submit', 'null_layout');
    }

    /**
     * 到货通知表单
     */
    public function arrival_notice_submitOp() {
        $type = intval($_POST['type']) == 2 ? 2 : 1;
        $goods_id = intval($_POST['goods_id']);
        if ($goods_id <= 0) {
            showDialog(L('wrong_argument'), 'reload');
        }
        // 验证商品数是否充足
        $goods_info = Model('goods')->getGoodsInfoByID($goods_id, 'goods_id,goods_name,goods_storage,goods_state,store_id');
        if (empty($goods_info) || ($goods_info['goods_storage'] > 0 && $goods_info['goods_state'] == 1)) {
            showDialog(L('wrong_argument'), 'reload');
        }

        $model_arrivalnotice = Model('arrival_notice');
        // 验证会员是否已经添加到货通知
        $where = array();
        $where['goods_id'] = $goods_info['goods_id'];
        $where['member_id'] = $_SESSION['member_id'];
        $where['an_type'] = $type;
        $notice_info = $model_arrivalnotice->getArrivalNoticeInfo($where);
        if (!empty($notice_info)) {
            if ($type == 1) {
                showDialog('您已经添加过通知提醒，请不要重复添加', 'reload');
            } else {
                showDialog('您已经预约过了，请不要重复预约', 'reload');
            }
        }

        $insert = array();
        $insert['goods_id'] = $goods_info['goods_id'];
        $insert['goods_name'] = $goods_info['goods_name'];
        $insert['member_id'] = $_SESSION['member_id'];
        $insert['store_id'] = $goods_info['store_id'];
        $insert['an_mobile'] = $_POST['mobile'];
        $insert['an_email'] = $_POST['email'];
        $insert['an_type'] = $type;
        $model_arrivalnotice->addArrivalNotice($insert);

        $title = $type == 1 ? '到货通知' : '立即预约';
        $js = "ajax_form('arrival_notice', '". $title ."', '" . urlShop('goods', 'arrival_notice_succ', array('type' => $type)) . "', 480);";
        showDialog('','','js',$js);
    }

    /**
     * 到货通知添加成功
     */
    public function arrival_notice_succOp() {
        // 可能喜欢的商品
        $goods_list = Model('goods_browse')->getGuessLikeGoods($_SESSION['member_id'], 4);
        Tpl::output('goods_list', $goods_list);
        Tpl::showpage('arrival_notice.message', 'null_layout');
    }
}
