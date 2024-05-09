<?php
defined('In718Shop') or exit('Access Invalid!');
class searchControl extends BaseControl{

   //每页显示商品数
    const PAGESIZE = 1000000;

    //模型对象
    private $_model_search;

    public function indexOp() {
        Language::read('home_goods_class_index');
        $this->_model_search = Model('search');
        //显示左侧分类
        //默认分类，从而显示相应的属性和品牌
        $default_classid = intval($_POST['cate_id']);
        if (intval($_POST['cate_id']) > 0) {
            $goods_class_array = $this->_model_search->getLeftCategory(array($_POST['cate_id']));
        } elseif ($_POST['keyword'] != '') {
            //从TAG中查找分类
            $goods_class_array = $this->_model_search->getTagCategory($_POST['keyword']);
            //取出第一个分类作为默认分类，从而显示相应的属性和品牌
            $default_classid = $goods_class_array[0];
            $goods_class_array = $this->_model_search->getLeftCategory($goods_class_array, 1);;
        }
        //优先从全文索引库里查找
        list($indexer_ids,$indexer_count) = $this->_model_search->indexerSearch($_POST,self::PAGESIZE);
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
        $fields = "goods_id,goods_commonid,goods_name,goods_jingle,gc_id,store_id,store_name,goods_price,goods_promotion_price,goods_promotion_type,goods_marketprice,goods_storage,goods_image,goods_freight,goods_salenum,goods_presalenum,color_id,evaluation_good_star,evaluation_count,is_virtual,is_fcode,is_appoint,is_presell,have_gift,is_mode,deliverer_id";

        $condition = array();
        if (is_array($indexer_ids)) {
            //商品主键搜索
            $condition['goods_id'] = array('in',$indexer_ids);
            $goods_list = $model_goods->getGoodsOnlineList($condition, $fields, 0, $order, self::PAGESIZE, null, false);
            //如果有商品下架等情况，则删除下架商品的搜索索引信息
            if (count($goods_list) != count($indexer_ids)) {
                $this->_model_search->delInvalidGoods($goods_list, $indexer_ids);
            }

            pagecmd('setEachNum',self::PAGESIZE);
            pagecmd('setTotalNum',$indexer_count);

        } else {
            // var_dump(isset($goods_param['class']));die;
            //执行正常搜索

            if (isset($goods_param['class'])) {
                $condition['gc_id_'.$goods_param['class']['depth']] = $goods_param['class']['gc_id'];
            }
            // if ($_POST['keyword'] != '') {
            //     $condition['goods_name|goods_jingle'] = array('like', '%' . $_POST['keyword'] . '%');
            // }
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
            $goods_list = $model_goods->getGoodsListByColorDistinct($condition, $fields, $order, self::PAGESIZE);
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
        if ($goods_list) {
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$goods_list);
             echo json_encode($res,320);
        } else {
            $message='fail';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>$goods_list);
             echo json_encode($res,320);
        }
    }
}