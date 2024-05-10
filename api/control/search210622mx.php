<?php
defined('In718Shop') or exit('Access Invalid!');
class searchControl extends BaseControl{

   //每页显示商品数
    const PAGESIZE = 30;

    //模型对象
    private $_model_search;
//20200415优化搜索以及排序

        public function indexOp() {
        Language::read('home_goods_class_index');
        // echo $_POST['keyword'];die;
        $this->_model_search = Model('search');
        //显示左侧分类
        //默认分类，从而显示相应的属性和品牌
        $default_classid = intval($_POST['cate_id']);
        if (isset($_POST['num_page'])) {
            $num = intval($_POST['num_page']*self::PAGESIZE);
        }else{
            $num =self::PAGESIZE;
        }
        if (intval($_POST['cate_id']) > 0) {
            $goods_class_array = $this->_model_search->getLeftCategory(array($_POST['cate_id']));
        } elseif ($_POST['keyword'] != '') {
            //从TAG中查找分类
            $goods_class_array = $this->_model_search->getTagCategory($_POST['keyword']);
            //取出第一个分类作为默认分类，从而显示相应的属性和品牌
            $default_classid = $goods_class_array[0];
            $goods_class_array = $this->_model_search->getLeftCategory($goods_class_array, 1);
        }

        //优先从全文索引库里查找
        list($indexer_ids,$indexer_count) = $this->_model_search->indexerSearch($_POST,$num);
        //获得经过属性过滤的商品信息
        list($goods_param, $brand_array, $initial_array, $attr_array, $checked_brand, $checked_attr) = $this->_model_search->getAttr($_POST, $default_classid);
        $order = 'goods_click desc,goods_salenum desc,goods_id desc';
        if (in_array($_POST['key'],array('1','2','3'))) {
            $sequence = $_POST['order'] == 'true' ? 'asc' : 'desc';
            $order = str_replace(array('1','2','3'), array('goods_salenum+goods_presalenum','goods_click','goods_promotion_price'), $_POST['key']);
            $order .= ' '.$sequence;
        }
        $model_goods = Model('goods');
        // 字段
        $fields = "goods_id,goods_commonid,goods_name,goods_jingle,gc_id,store_id,store_name,goods_price,goods_promotion_price,goods_promotion_type,goods_marketprice,goods_storage,goods_image,goods_freight,goods_salenum,goods_presalenum,color_id,evaluation_good_star,evaluation_count,is_virtual,is_fcode,is_appoint,is_presell,have_gift,is_mode,is_group_ladder";

        $condition = array();
        if (is_array($indexer_ids)) {
            //商品主键搜索
            $condition['goods_id'] = array('in',$indexer_ids);
            $goods_list = $model_goods->getGoodsOnlineList($condition, $fields, 0, $order, $num, null, false);
            $goods_list_num = count($model_goods->getGoodsOnlineList($condition, $fields, 0, $order, 1000000000000, null, false));
            //如果有商品下架等情况，则删除下架商品的搜索索引信息
            if (count($goods_list) != count($indexer_ids)) {
                $this->_model_search->delInvalidGoods($goods_list, $indexer_ids);
            }

            pagecmd('setEachNum',$num);
            pagecmd('setTotalNum',$indexer_count);

        } else {
            if (isset($goods_param['class'])) {
                $condition['gc_id_'.$goods_param['class']['depth']] = $goods_param['class']['gc_id'];
            }
            $str=$_POST['keyword'];
            if($str) {
                $str = Model('search')->decorateSearch_pre($str);
                $condition['goods_name|goods_jingle'] = array('like', '%' . $str . '%');
            }
            if (intval($_POST['area_id']) > 0) {
                $condition['areaid_1'] = intval($_POST['area_id']);
            }
            if ($_POST['type'] == 1) {
                $condition['is_own_shop'] = 1;
            }
            if ($_POST['gift'] == 1) {
                $condition['have_gift'] = 1;
            }
            if (isset($goods_param['goodsid_array'])){
                $condition['goods_id'] = array('in', $goods_param['goodsid_array']);
            }
        //按价格搜索
            if (intval($_POST['priceMin']) >= 0) {
                $condition['goods_price'] = array('egt', intval($_POST['priceMin']));
            }
            if (intval($_POST['priceMax']) >= 0) {
                $condition['goods_price'] = array('elt', intval($_POST['priceMax']));
            }
            if (intval($_POST['priceMin']) >= 0 && intval($_POST['priceMax']) >= 0) {
                $condition['goods_price'] = array('between',array(intval($_POST['priceMin']),intval($_POST['priceMax'])));
            }
        //end
            $goods_list = $model_goods->getGoodsListByColorDistinct($condition, $fields.',min(color_id) as color_id', 'goods_storage desc,goods_id desc', $num);
            //$goods_list = $model_goods->getGoodsListByNoColorDistinct($condition, $fields, $order, $num);
            foreach ($goods_list as $key => $value) {
                $goods_list[$key]['goods_image'] = cthumb($value['goods_image'], '360', $value['store_id']);
                $goods_commond_info = Model()->table('goods_common')->field('is_vip_price')->where(array('goods_commonid'=>$value['goods_commonid']))->find();
                $goods_list[$key]['is_vip_price'] =$goods_commond_info['is_vip_price'];
            }
            //$goods_list_num =count($model_goods->getGoodsListByColorDistinct($condition, $fields, $order));
            $goods_list_num =count($goods_list);
        }
        if (isset($_POST['test'])) {
            if (isset($_POST['ziti_id'])) {
                //var_dump($goods_list);die;
                foreach ($goods_list as $key => $value) {
                    if ($value['is_group_ladder'] == 5) {
                        //获取活动id，活动id+goods_id是否存在，不存在剔除
                        $buy_deliver_info = model('buy_deliver')->getBuyDeliverInfo(array('ziti_id' => $_POST['ziti_id']));
                        //var_dump($buy_deliver_info['buy_deliver_id']);die;
                            if (empty($buy_deliver_info['buy_deliver_id'])) {
                                unset($goods_list[$key]);
                            }else{
                                $buy_deliver_id = $buy_deliver_info['buy_deliver_id'];
                                $result = model('buy_deliver_goods')->getBuyDeliverGoodsList(array('buy_deliver_id'=>$buy_deliver_id,'goods_id'=>$value['goods_id']));
                                if ($value['goods_id'] == '13358') {
                                    var_dump($result);die;
                                }
                                // $result = model('buy_deliver_goods')->getBuyDeliverGoodsExtendInfo($result[0]);
                                if (!$result) {
                                    unset($goods_list[$key]);
                                }
                            }
                    }
                }
            }
            var_dump($goods_list);die;
        }
        if (!empty($goods_list)) {
            if (isset($_POST['ziti_id'])) {
                foreach ($goods_list as $key => $value) {
                    if ($value['is_group_ladder'] == 5) {
                        //获取活动id，活动id+goods_id是否存在，不存在剔除
                        $buy_deliver_info = model('buy_deliver')->getBuyDeliverInfo(array('ziti_id' => $_POST['ziti_id']));
                        //var_dump($buy_deliver_info['buy_deliver_id']);die;
                            if (empty($buy_deliver_info['buy_deliver_id'])) {
                                unset($goods_list[$key]);
                            }else{
                                $buy_deliver_id = $buy_deliver_info['buy_deliver_id'];
                                $result = model('buy_deliver_goods')->getBuyDeliverGoodsList(array('buy_deliver_id'=>$buy_deliver_id,'goods_id'=>$value['goods_id']));
                                if (!$result) {
                                    unset($goods_list[$key]);
                                }
                            }
                    }
                }
            }
        }
        // 商品多图
        if (!empty($goods_list)) {
            $commonid_array = array(); // 商品公共id数组
            $storeid_array = array();       // 店铺id数组
            foreach ($goods_list as $value) {
                $commonid_array[] = $value['goods_commonid'];
                $storeid_array[] = $value['store_id'];
            }
            $commonid_array = array_unique($commonid_array);
            $storeid_array = array_unique($storeid_array);

            // 商品多图
            $goodsimage_more = Model('goods')->getGoodsImageList(array('goods_commonid' => array('in', $commonid_array)));
            foreach ($goodsimage_more as $key => $value) {
                
                $goodsimage_more[$key]['goods_image'] = cthumb($value['goods_image'], '', $value['store_id']);
            }
            // var_dump($goodsimage_more);die;
            // 店铺
            $store_list = Model('store')->getStoreMemberIDList($storeid_array);
            //搜索的关键字
            $search_keyword = trim($_POST['keyword']);
            foreach ($goods_list as $key => $value) {
        // 商品多图
        //商品列表主图限制不越过5个
        $n=0;
                foreach ($goodsimage_more as $v) {
                    if ($value['goods_commonid'] == $v['goods_commonid'] && $value['store_id'] == $v['store_id'] && $value['color_id'] == $v['color_id']) {
                        $n++;
                        $goods_list[$key]['image'][] = $v;
                        if($n>=5)break;
                    }
                }
                // 店铺的开店会员编号
                $store_id = $value['store_id'];
                $goods_list[$key]['member_id'] = $store_list[$store_id]['member_id'];
                $goods_list[$key]['store_domain'] = $store_list[$store_id]['store_domain'];
                $goods_list[$key]['sale_num'] = $value['goods_salenum']+$value['goods_presalenum'];
                //多规格库存累加
                $goods_storage =  Model('goods')->getGoodsList(array('goods_commonid'=>$value['goods_commonid']),'sum(goods_storage) as goods_storage');
                $goods_list[$key]['goods_storage'] = $goods_storage[0]['goods_storage'];
            }
        }
        // var_dump($goods_list);die;
        // foreach ($goods_list as $key => $value) {
        //     $cart['goods_image'] = cthumb($cart['goods_image'], '', $cart['store_id']);
        // }
        if($_POST['key']==2) {//综合排序
            if ($_POST['order'] == 'true') {
                $goods_list = $this->multi_array_sort($goods_list, 'goods_storage');
            }else{
                $goods_list = $this->multi_array_sort($goods_list, 'goods_storage',SORT_DESC);
            }
        }elseif ($_POST['key']==1){//销量排序
            if($_POST['order'] == 'true'){
                $goods_list = $this->multi_array_sort($goods_list, 'sale_num');
            }else{
                $goods_list = $this->multi_array_sort($goods_list, 'sale_num',SORT_DESC);
            }
        }elseif ($_POST['key']==3) {//价格排序
            if($_POST['order'] == 'true'){
                $goods_list = $this->multi_array_sort($goods_list, 'goods_promotion_price');
            }else{
                $goods_list = $this->multi_array_sort($goods_list, 'goods_promotion_price',SORT_DESC);
            }
        }
        $pagecount=intval($goods_list_num/self::PAGESIZE)+1;
        $list['goods_data']=$goods_list;
        $list['PageCount']=$pagecount;
        $list['goods_list_num']=$goods_list_num;
        if ($goods_list) {
            if ($num>count($goods_list)) {
                $message='没有更多待加载数据';
            }else{
                $message='sucess';
            }
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$list);
             echo json_encode($res,320);
        } else {
			$condition = array(
                'item_type' => 'goods',
                'special_id' => 1
            );

            $mbSpecialModel = Model('mb_special1');
            $goodsList = $mbSpecialModel->getMbSpecialItemList($condition);

            foreach ($goodsList[0]['item_data']['item'] as $key => $value) {
                $goods_id_array[] = $value['goods_id'];
            }

            $condition1['goods_id'] = array('in', $goods_id_array);
            $goods_list = $model_goods->getGoodsOnlineList($condition1, $fields, 0, $order, '', null, false);
            if (isset($_POST['test'])) {
                var_dump($goods_list);die;
            }
            if (!empty($goods_list)) {
                if (isset($_POST['ziti_id'])) {
                    foreach ($goods_list as $key => $value) {
                        if ($value['is_group_ladder'] == 5) {
                            //获取活动id，活动id+goods_id是否存在，不存在剔除
                            $buy_deliver_info = model('buy_deliver')->getBuyDeliverInfo(array('ziti_id' => $_POST['ziti_id']));
                            //var_dump($buy_deliver_info['buy_deliver_id']);die;
                                if (empty($buy_deliver_info['buy_deliver_id'])) {
                                    unset($goods_list[$key]);
                                }else{
                                    $buy_deliver_id = $buy_deliver_info['buy_deliver_id'];
                                    $result = model('buy_deliver_goods')->getBuyDeliverGoodsList(array('buy_deliver_id'=>$buy_deliver_id,'goods_id'=>$value['goods_id']));
                                    if (!$result) {
                                        unset($goods_list[$key]);
                                    }
                                }
                        }
                    }
                }
            }
			$goods_list_num = count($model_goods->getGoodsOnlineList($condition1, $fields, 0, $order, 1000000000000, null, false));
            if (!empty($goods_list)) {
                $commonid_array = array(); // 商品公共id数组
                $storeid_array = array();       // 店铺id数组
                foreach ($goods_list as $value) {
                    $commonid_array[] = $value['goods_commonid'];
                    $storeid_array[] = $value['store_id'];
                }
                $commonid_array = array_unique($commonid_array);
                $storeid_array = array_unique($storeid_array);

                // 商品多图
                $goodsimage_more = Model('goods')->getGoodsImageList(array('goods_commonid' => array('in', $commonid_array)));
                foreach ($goodsimage_more as $key => $value) {

                    $goodsimage_more[$key]['goods_image'] = cthumb($value['goods_image'], '', $value['store_id']);
                }

                // 店铺
                $store_list = Model('store')->getStoreMemberIDList($storeid_array);
                foreach ($goods_list as $key => $value) {
                    // 商品多图
                    //商品列表主图限制不越过5个
                    $n = 0;
                    foreach ($goodsimage_more as $v) {
                        if ($value['goods_commonid'] == $v['goods_commonid'] && $value['store_id'] == $v['store_id'] && $value['color_id'] == $v['color_id']) {
                            $n++;
                            $goods_list[$key]['image'][] = $v;
                            if ($n >= 5) break;
                        }
                    }
                    // 店铺的开店会员编号
                    $store_id = $value['store_id'];
                    $goods_list[$key]['member_id'] = $store_list[$store_id]['member_id'];
                    $goods_list[$key]['store_domain'] = $store_list[$store_id]['store_domain'];
                    $goods_list[$key]['goods_image'] = cthumb($value['goods_image'], '360', $value['store_id']);
                }
            }
            $pagecount = intval($goods_list_num / self::PAGESIZE) + 1;
            $list['goods_data'] = $goods_list;
            $list['PageCount'] = $pagecount;
            $list['goods_list_num'] = $goods_list_num;
            if ($goods_list) {
                if ($num > count($goods_list)) {
                    $message = '没有更多待加载数据';
                } else {
                    $message = 'sucess';
                }
                $res = array('code' => '100', 'message' => $message, 'data' => $list);
                echo json_encode($res, 320);
            } else {
                $message = 'fail';
                $this->recommend();
                $res = array('code' => '200', 'message' => $message, 'data' => $list);
                echo json_encode($res, 320);
            }
        }
			
			/**
            $message='fail';
			//$this->recommend();
            $res = array('code'=>'200' , 'message'=>$message,'data'=>$list);
             echo json_encode($res,320);
        }
		**/
    }
      
	public function recommend(){
        $hot_search = explode(',',C('hot_search'));
        $hot_keyword = $hot_search[rand(0,count($hot_search)-1)];
        //设置一个随机数，将热搜词的随机一个传入keyword进行查询
        Language::read('home_goods_class_index');
        // echo $_POST['keyword'];die;
        $this->_model_search = Model('search');
        //显示左侧分类
        //默认分类，从而显示相应的属性和品牌
        $default_classid = intval($_POST['cate_id']);
        if (isset($_POST['num_page'])) {
            $num = intval($_POST['num_page']*self::PAGESIZE);
        }else{
            $num =self::PAGESIZE;
        }
        if (intval($_POST['cate_id']) > 0) {
            $goods_class_array = $this->_model_search->getLeftCategory(array($_POST['cate_id']));
        } elseif ($_POST['keyword'] != '') {
            //从TAG中查找分类
            $goods_class_array = $this->_model_search->getTagCategory($_POST['keyword']);
            //取出第一个分类作为默认分类，从而显示相应的属性和品牌
            $default_classid = $goods_class_array[0];
            $goods_class_array = $this->_model_search->getLeftCategory($goods_class_array, 1);
        }

        //优先从全文索引库里查找
        list($indexer_ids,$indexer_count) = $this->_model_search->indexerSearch($_POST,$num);
        //获得经过属性过滤的商品信息
        list($goods_param, $brand_array, $initial_array, $attr_array, $checked_brand, $checked_attr) = $this->_model_search->getAttr($_POST, $default_classid);
        $order = 'goods_click desc,goods_salenum desc,goods_id desc';
        if (in_array($_POST['key'],array('1','2','3'))) {
            $sequence = $_POST['order'] == 'true' ? 'asc' : 'desc';
            $order = str_replace(array('1','2','3'), array('goods_salenum+goods_presalenum','goods_click','goods_promotion_price'), $_POST['key']);
            $order .= ' '.$sequence;
        }
        $model_goods = Model('goods');
        // 字段
        $fields = "goods_id,goods_commonid,goods_name,goods_jingle,gc_id,store_id,store_name,goods_price,goods_promotion_price,goods_promotion_type,goods_marketprice,goods_storage,goods_image,goods_freight,goods_salenum,goods_presalenum,color_id,evaluation_good_star,evaluation_count,is_virtual,is_fcode,is_appoint,is_presell,have_gift,is_mode";

        $condition = array();
        if (is_array($indexer_ids)) {
            //商品主键搜索
            $condition['goods_id'] = array('in',$indexer_ids);
            $goods_list = $model_goods->getGoodsOnlineList($condition, $fields, 0, $order, $num, null, false);
            $goods_list_num = count($model_goods->getGoodsOnlineList($condition, $fields, 0, $order, 1000000000000, null, false));
            //如果有商品下架等情况，则删除下架商品的搜索索引信息
            if (count($goods_list) != count($indexer_ids)) {
                $this->_model_search->delInvalidGoods($goods_list, $indexer_ids);
            }

            pagecmd('setEachNum',$num);
            pagecmd('setTotalNum',$indexer_count);

        } else {
            if (isset($goods_param['class'])) {
                $condition['gc_id_'.$goods_param['class']['depth']] = $goods_param['class']['gc_id'];
            }
            $str=$hot_keyword;
            if($str) {
                $str = Model('search')->decorateSearch_pre($str);
                $condition['goods_name|goods_jingle'] = array('like', '%' . $str . '%');
            }
            if (intval($_POST['area_id']) > 0) {
                $condition['areaid_1'] = intval($_POST['area_id']);
            }
            if ($_POST['type'] == 1) {
                $condition['is_own_shop'] = 1;
            }
            if ($_POST['gift'] == 1) {
                $condition['have_gift'] = 1;
            }
            if (isset($goods_param['goodsid_array'])){
                $condition['goods_id'] = array('in', $goods_param['goodsid_array']);
            }
            //按价格搜索
            if (intval($_POST['priceMin']) >= 0) {
                $condition['goods_price'] = array('egt', intval($_POST['priceMin']));
            }
            if (intval($_POST['priceMax']) >= 0) {
                $condition['goods_price'] = array('elt', intval($_POST['priceMax']));
            }
            if (intval($_POST['priceMin']) >= 0 && intval($_POST['priceMax']) >= 0) {
                $condition['goods_price'] = array('between',array(intval($_POST['priceMin']),intval($_POST['priceMax'])));
            }
            //end
            $goods_list = $model_goods->getGoodsListByColorDistinct($condition, $fields, $order, $num);
            foreach ($goods_list as $key => $value) {
                $goods_list[$key]['goods_image'] = cthumb($value['goods_image'], '', $value['store_id']);
            }
            $goods_list_num =count($model_goods->getGoodsListByColorDistinct($condition, $fields, $order));
        }
        // 商品多图
        if (!empty($goods_list)) {
            $commonid_array = array(); // 商品公共id数组
            $storeid_array = array();       // 店铺id数组
            foreach ($goods_list as $value) {
                $commonid_array[] = $value['goods_commonid'];
                $storeid_array[] = $value['store_id'];
            }
            $commonid_array = array_unique($commonid_array);
            $storeid_array = array_unique($storeid_array);

            // 商品多图
            $goodsimage_more = Model('goods')->getGoodsImageList(array('goods_commonid' => array('in', $commonid_array)));
            foreach ($goodsimage_more as $key => $value) {

                $goodsimage_more[$key]['goods_image'] = cthumb($value['goods_image'], '', $value['store_id']);
            }
            // var_dump($goodsimage_more);die;
            // 店铺
            $store_list = Model('store')->getStoreMemberIDList($storeid_array);
            //搜索的关键字
            $search_keyword = trim($_POST['keyword']);
            foreach ($goods_list as $key => $value) {
                // 商品多图
                //商品列表主图限制不越过5个
                $n=0;
                foreach ($goodsimage_more as $v) {
                    if ($value['goods_commonid'] == $v['goods_commonid'] && $value['store_id'] == $v['store_id'] && $value['color_id'] == $v['color_id']) {
                        $n++;
                        $goods_list[$key]['image'][] = $v;
                        if($n>=5)break;
                    }
                }
                // 店铺的开店会员编号
                $store_id = $value['store_id'];
                $goods_list[$key]['member_id'] = $store_list[$store_id]['member_id'];
                $goods_list[$key]['store_domain'] = $store_list[$store_id]['store_domain'];
            }
        }
        // var_dump($goods_list);die;
        // foreach ($goods_list as $key => $value) {
        //     $cart['goods_image'] = cthumb($cart['goods_image'], '', $cart['store_id']);
        // }
        $pagecount=intval($goods_list_num/self::PAGESIZE)+1;
        $list['goods_data']=$goods_list;
        $list['PageCount']=$pagecount;
        $list['goods_list_num']=$goods_list_num;
            if ($num>count($goods_list)) {
                $message='没有更多待加载数据';
            }else{
                $message='sucess';
            }
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$list);
            die(json_encode($res,320));
    }
}