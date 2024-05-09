<?php
defined('In718Shop') or exit('Access Invalid!');

class xianshi_discountControl extends BaseControl
{

    //模型对象
    private $_model_search;
    //每页显示商品评价数
    const PAGESIZE = 10;
    //页数
    const PAGENUM = 1;

    public function goodsOp()
    {
        $xianshi_goods_id = intval($_POST['xianshi_goods_id']);
        if(empty($xianshi_goods_id)){
            $message = 'xianshi_goods_id不能为空';
            $res = array('code' => '-1', 'message' => $message, 'data' => array());
            die(json_encode($res, 320));
        }
        $model_xianshi_goods = Model('p_xianshi_goods');
        $xianshi_goods_info = $model_xianshi_goods->getXianshiGoodsInfoByID($xianshi_goods_id);
        $goods_id = $xianshi_goods_info['goods_id'];

        // 商品详细信息
        $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id);

        $goods_info = $goods_detail['goods_info'];
        $goods_info['goods_image'] = cthumb($goods_info['goods_image']);

        if ($goods_info['goods_state'] != 1 || $goods_info['goods_verify'] != 1) {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => $goods_info);
            echo json_encode($res, 320);
            exit();
        }
        if (empty($goods_info)) {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => $goods_info);
            echo json_encode($res, 320);
            exit();
        }
        $rs = $model_goods->getGoodsList(array('goods_commonid' => $goods_info['goods_commonid']));
        $count = 0;
        foreach ($rs as $v) {
            $count += $v['goods_salenum'];
        }
        $goods_info['goods_salenum'] = $count;
        $goods_info['goods_presalenum'] = $goods_info['goods_presalenum'] + $count;
        if (!is_array($goods_detail['goods_image'][0])) {
            $goods_detail['goods_image'][0] = explode(",", $goods_detail['goods_image'][0]);
        }
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

        //小程序码
        $payment_code = 'wxpay_jsapi';
        $condition = array();
        $condition['payment_code'] = $payment_code;
        $payment_info = Model()->table('mb_payment')->where($condition)->find();
        $appletcode = $payment_info['appletcode'];
        $goods_detail['appletcode'] = UPLOAD_SITE_URL . DS . ATTACH_MOBILE . '/appletcode/' . $appletcode;

        //评价信息
        $goods_evaluate_info = Model('evaluate_goods')->getEvaluateGoodsInfoByGoodsID($goods_id);
        $goods_info = array_merge($goods_info, $goods_evaluate_info);
        $goods_info['xianshi_goods_info'] = $xianshi_goods_info;
//        $goods_info = array_merge($goods_info, $deliverer);
        $goods_detail['goods_info'] = $goods_info;

        if ($goods_detail) {
            $message = 'success';
            $res = array('code' => '100', 'message' => $message, 'data' => $goods_detail);
            echo json_encode($res, 320);
        } else {
            $message = 'fail';
            $res = array('code' => '200', 'message' => $message, 'data' => $goods_detail);
            echo json_encode($res, 320);
        }

    }

    // /**
    //  * 现时活动列表
    //  */
    // public function xianshi_listOp()
    // {
    //     $model_xianshi = Model('p_xianshi');

    //     $condition = array();
    //     // $condition['xianshi_type']=2;
    //     $xianshi_list = $model_xianshi->getXianshiList($condition, 10, 'end_time desc,xianshi_id asc');
    //     $xianshi_list1['xianshi_list'] = $xianshi_list;
    //     $res = array('code' => '1', 'message' => '', 'data' => $xianshi_list1);
    //     die(json_encode($res, 320));
    // }

    /**
     * 商品列表
     */
    public function goods_listOp()
    {
        // echo'333';die;
//        $xianshi_id = intval($_POST['xianshi_id']);
//        if (empty($xianshi_id)) {
//            $message = 'xianshi_id不能为空';
//            $res = array('code' => '-1', 'message' => $message, 'data' => array());
//            die(json_encode($res, 320));
//        }
        $model_xianshi_goods = Model('p_xianshi_goods');
        $model_xianshi = Model('p_xianshi');
        $model_goods = Model('goods');
        $discount = Model('member')->getDiscount($_SESSION['member_id']);
        $condition = array();
        $condition['state'] = 1;
//        $condition['xianshi_id'] = $xianshi_id;
        $condition['start_time'] = array('elt',TIMESTAMP);
        $condition['end_time'] = array('gt', TIMESTAMP);
        $xianshi_list = $model_xianshi->getXianshiList(array('xianshi_type'=>2),'','','xianshi_id');
        foreach($xianshi_list as $item){
            $xianshi_id_list[] = $item['xianshi_id'];
        }
        $condition['xianshi_id'] = array('in', $xianshi_id_list);
        //$condition['xianshi_type'] = 1;
        if ($_GET['gc_id']) {
            $condition['gc_id_1'] = intval($_GET['gc_id']);
        }

        //$goods_list = $model_xianshi_goods->getXianshiGoodsExtendList($condition, self::PAGESIZE, 'xianshi_goods_id ');
        $goods_list = $model_xianshi_goods->getXianshiGoodsExtendList($condition, '', 'goods_sort asc,xianshi_goods_id desc');

        $total_page = pagecmd('gettotalpage');
        if (intval($_GET['curpage'] > $total_page)) {
            exit();
        }
        $xs_goods_list = array();
        foreach ($goods_list as $k => $goods_info) {
             $goods_state = $model_goods->getfby_goods_id($goods_info['goods_id'], 'goods_state');
$is_deleted = $model_goods->getfby_goods_id($goods_info['goods_id'], 'is_deleted');
            // var_dump($goods_state);die;
            if ($goods_state == 1 &&$is_deleted==0) {
               $xs_goods_list[$goods_info['goods_id']] = $goods_info;
            // $xs_goods_list[$goods_info['goods_id']]['image_url_240'] = cthumb($goods_info['goods_image'], 240, $goods_info['store_id']);
            $goods_image = $model_goods->getfby_goods_id($goods_info['goods_id'], 'goods_image');
            $xs_goods_list[$goods_info['goods_id']]['image_url_240'] = cthumb($goods_image, 240, $goods_info['store_id']);
            //取得最新的商品名字
            $goods_name = $model_goods->getfby_goods_id($goods_info['goods_id'], 'goods_name');
            $xs_goods_list[$goods_info['goods_id']]['goods_name'] = $goods_name;
            //限时折扣活动页面下架商品不显示，20191219slk
            $goods_state = $model_goods->getfby_goods_id($goods_info['goods_id'], 'goods_state');
            // var_dump($goods_state);die;
            if ($goods_state == 1) {
                // echo "111";
                $xs_goods_list[$goods_info['goods_id']]['is_online'] = 1;
            } else {
                $xs_goods_list[$goods_info['goods_id']]['is_online'] = 0;
            }
            $goods_commonid = $model_goods->getfby_goods_id($goods_info['goods_id'], 'goods_commonid');
            // 20181120 隐藏商品促销活动中的会员折扣价
            $is_vip_price = Model()->table('goods_common')->getfby_goods_commonid($goods_commonid, 'is_vip_price');
            if ($is_vip_price == 1) {
                $xs_goods_list[$goods_info['goods_id']]['goods_price'] = ncPriceFormat($goods_info['goods_price'] * $discount);
                $xs_goods_list[$goods_info['goods_id']]['xianshi_price'] = ncPriceFormat($goods_info['xianshi_price'] * $discount);
                $xs_goods_list[$goods_info['goods_id']]['down_price'] = $xs_goods_list[$goods_info['goods_id']]['goods_price'] - $xs_goods_list[$goods_info['goods_id']]['xianshi_price'];
            } else {
                $xs_goods_list[$goods_info['goods_id']]['down_price'] = $goods_info['goods_price'] - $goods_info['xianshi_price'];
            }

            //提前显示抢购
            if (TIMESTAMP >= $goods_info['start_time']) {
                $xs_goods_list[$goods_info['goods_id']]['buyNow'] = 1;
            } else {
                $xs_goods_list[$goods_info['goods_id']]['buyNow'] = 0;
            }
            } else {
                unset($xs_goods_list[$goods_info['goods_id']]);
            }
            


        }
       $condition = array();
        $condition = array('goods_id' => array('in', array_keys($xs_goods_list)));
        // $condition = array('is_deleted'=>0);
        $goods_list = $model_goods->getGoodsOnlineList($condition, 'goods_id,goods_verify,gc_id_1,evaluation_good_star,store_id,store_name,is_group_ladder,goods_storage,goods_salenum,goods_presalenum', 0, '', '', null, false);
          if($_GET['test']==1){
 var_dump($goods_list);die;
        }
        foreach ($goods_list as $k => $goods_info) {
            $xs_goods_list[$goods_info['goods_id']]['evaluation_good_star'] = $goods_info['evaluation_good_star'];
            $xs_goods_list[$goods_info['goods_id']]['store_name'] = $goods_info['store_name'];
            $xs_goods_list[$goods_info['goods_id']]['is_group_ladder'] = $goods_info['is_group_ladder'];
            $xs_goods_list[$goods_info['goods_id']]['goods_storage'] = $goods_info['goods_storage'];
            $xs_goods_list[$goods_info['goods_id']]['sale_num'] = $goods_info['goods_salenum'] + $goods_info['goods_presalenum'];
            $xs_goods_list[$goods_info['goods_id']]['xianshi_type'] = 2;
            if ($xs_goods_list[$goods_info['goods_id']]['gc_id_1'] != $goods_info['gc_id_1']) {
                //兼容以前版本，如果限时商品表没有保存一级分类ID，则马上保存
                $model_xianshi_goods->editXianshiGoods(array('gc_id_1' => $goods_info['gc_id_1']), array('xianshi_goods_id' => $xs_goods_list[$goods_info['goods_id']]['xianshi_goods_id']));
            }
        }

        //查询商品评分信息
        $goodsevallist = Model("evaluate_goods")->getEvaluateGoodsList(array('geval_goodsid' => array('in', array_keys($xs_goods_list))));
        $eval_list = array();
        if (!empty($goodsevallist)) {
            foreach ($goodsevallist as $v) {
                if ($v['geval_content'] == '' || count($eval_list[$v['geval_goodsid']]) >= 2) continue;
                $eval_list[$v['geval_goodsid']][] = $v;
            }
        }
        //Tpl::output('goodsevallist',$eval_list);
        //Tpl::output('goods_list', $xs_goods_list);
        //$goods_list1['eval_list'] = $eval_list;

        if (!empty($_POST['num_page'])) {
            $num = $_POST['num_page'];
        } else {
            $num = self::PAGENUM;
        }

        $xs_goods_list = array_values($xs_goods_list);
        $xs_goods_list = $this->multi_array_sort($xs_goods_list,'goods_sort');
        
        if($_POST['key']==2) {//综合排序
            if ($_POST['order'] == 'true') {
                $xs_goods_list = $this->multi_array_sort($xs_goods_list, 'goods_sort',SORT_DESC);
            }else{
                $xs_goods_list = $this->multi_array_sort($xs_goods_list, 'goods_sort');
            }
        }elseif ($_POST['key']==1){//销量排序
            if($_POST['order'] == 'true'){
                $xs_goods_list = $this->multi_array_sort($xs_goods_list, 'sale_num');
            }else{
                $xs_goods_list = $this->multi_array_sort($xs_goods_list, 'sale_num',SORT_DESC);
            }
        }elseif ($_POST['key']==3) {//价格排序
            if($_POST['order'] == 'true'){
                $xs_goods_list = $this->multi_array_sort($xs_goods_list, 'xianshi_price');
            }else{
                $xs_goods_list = $this->multi_array_sort($xs_goods_list, 'xianshi_price',SORT_DESC);
            }
        }

        if ($xs_goods_list) {
            $xs_goods_list = array_slice($xs_goods_list, 0, $num * self::PAGESIZE);
            $goods_list1['xs_goods_list'] = $xs_goods_list;
            $res = array('code' => '1', 'message' => '', 'data' => $goods_list1);
            //$xs_goods_list = array('code' => '400', 'msg' => '查询成功！', 'total_unit' => $unit, 'total_quantity' => $quantity, 'total_price' => $price, 'signList' => $signList);
        } else {
            $res = array('code' => '-1', 'message' => '', 'data' => []);
        }

        die(json_encode($res, 320));
    }

    /**
     *限时折扣商品列表排序优化 
     */
    public function goods_list123Op()
    {
        $discount = Model('member')->getDiscount($_SESSION['member_id']);
        $xs_goods_list = array();
        if (!empty($_POST['num_page'])) {
            $num = $_POST['num_page'];
        } else {
            $num = 1;
        }
        $offset = ($num-1)*10;
        $order = "a.goods_sort desc";
        if($_POST['key']==2) {//综合排序,按设置的顺序
            if ($_POST['order'] == 'true') {//正序
                $order = "a.goods_sort";
            }else{//倒序
                $order = "a.goods_sort desc";
            }
        }
        if($_POST['key']==1) {//销量排序,按设置的顺序
            if ($_POST['order'] == 'true') {//正序
                $order = "a.sale_num";
            }else{//倒序
                $order = "a.sale_num desc";
            }
        }
        if($_POST['key']==3) {//价格排序,按设置的顺序
            if ($_POST['order'] == 'true') {//正序
                $order = "a.xianshi_price";
            }else{//倒序
                $order = "a.xianshi_price desc";
            }
        }
        $sql_count = "select count(*) as page_count from 718shop_p_xianshi_goods pxg left join 718shop_goods g on pxg.goods_id=g.goods_id left join 718shop_p_xianshi px on pxg.xianshi_id=px.xianshi_id where pxg.start_time<UNIX_TIMESTAMP() and pxg.end_time>UNIX_TIMESTAMP() and g.goods_state=1 and g.is_deleted=0 and px.state=1 and px.xianshi_type=2";
        $count_result = Model()->query($sql_count);
        $page_count = $count_result[0]['page_count'];  
        $max_page_num = ceil($page_count/10);//print_r($max_page_num);die;

        $sql = "select a.* from (
            (select pxg.xianshi_goods_id,pxg.xianshi_id,pxg.xianshi_name,pxg.xianshi_title,pxg.xianshi_explain,pxg.goods_id,pxg.store_id,pxg.goods_name,pxg.goods_price,pxg.xianshi_price,pxg.goods_image,pxg.start_time,pxg.end_time,pxg.state,pxg.xianshi_recommend,pxg.gc_id_1,pxg.xianshi_goods_tax,pxg.upper_limit,pxg.goods_sort,g.commis_rate,CONCAT(px.discount,'折') as xianshi_discount,CASE g.goods_state WHEN 1 THEN 1 ELSE 0 END as is_online,g.goods_commonid,g.evaluation_good_star,g.is_group_ladder,g.goods_storage,g.goods_salenum+g.goods_presalenum as sale_num,px.xianshi_type,2 as sqlsort from 718shop_p_xianshi_goods pxg left join 718shop_goods g on pxg.goods_id=g.goods_id left join 718shop_p_xianshi px on pxg.xianshi_id=px.xianshi_id where pxg.start_time<UNIX_TIMESTAMP() and pxg.end_time>UNIX_TIMESTAMP() and g.goods_storage>0 and g.goods_state=1 and g.is_deleted=0 and px.state=1 and px.xianshi_type=2)
            union all
            (select pxg.xianshi_goods_id,pxg.xianshi_id,pxg.xianshi_name,pxg.xianshi_title,pxg.xianshi_explain,pxg.goods_id,pxg.store_id,pxg.goods_name,pxg.goods_price,pxg.xianshi_price,pxg.goods_image,pxg.start_time,pxg.end_time,pxg.state,pxg.xianshi_recommend,pxg.gc_id_1,pxg.xianshi_goods_tax,pxg.upper_limit,pxg.goods_sort,g.commis_rate,CONCAT(px.discount,'折') as xianshi_discount,CASE g.goods_state WHEN 1 THEN 1 ELSE 0 END as is_online,g.goods_commonid,g.evaluation_good_star,g.is_group_ladder,g.goods_storage,g.goods_salenum+g.goods_presalenum as sale_num,px.xianshi_type,1 as sqlsort from 718shop_p_xianshi_goods pxg left join 718shop_goods g on pxg.goods_id=g.goods_id left join 718shop_p_xianshi px on pxg.xianshi_id=px.xianshi_id where pxg.start_time<UNIX_TIMESTAMP() and pxg.end_time>UNIX_TIMESTAMP() and g.goods_storage=0 and g.goods_state=1 and g.is_deleted=0 and px.state=1 and px.xianshi_type=2)
            )a order by a.sqlsort desc,".$order.",a.goods_id desc
            limit 10 offset ".$offset;
        $xs_goods_list = Model()->query($sql); 
        // print_r($xs_goods_list);die;
        if($xs_goods_list){
            foreach($xs_goods_list as $k=>$v){
                $is_vip_price = Model()->table('goods_common')->getfby_goods_commonid($v['goods_commonid'], 'is_vip_price');
                if ($is_vip_price == 1) {
                    $xs_goods_list[$k]['goods_price'] = ncPriceFormat($v['goods_price'] * $discount);
                    $xs_goods_list[$k]['xianshi_price'] = ncPriceFormat($v['xianshi_price'] * $discount);
                    $xs_goods_list[$k]['down_price'] = $xs_goods_list[$k]['goods_price'] - $xs_goods_list[$k]['xianshi_price'];
                } else {
                    $xs_goods_list[$k]['down_price'] = $v['goods_price'] - $v['xianshi_price'];
                }
                $xs_goods_list[$k]['image_url_240'] = cthumb($v['goods_image'], 240, $v['store_id']);
                $xs_goods_list[$k]['image_url'] = cthumb($v['goods_image'], 60, $v['store_id']);
            }
        }
        if ($xs_goods_list) {
            $goods_list1['xs_goods_list'] = $xs_goods_list;
            $goods_list1['end'] = 0;
            if($num > $max_page_num){
                $goods_list1['end'] = 1;
                $goods_list1['xs_goods_list'] = [];
            }
            $res = array('code' => '1', 'message' => '', 'data' => $goods_list1);
        } else {
            if($num > $max_page_num){
                $goods_list1['end'] = 1;
                $goods_list1['xs_goods_list'] = [];
            }
            $res = array('code' => '-1', 'message' => '', 'data' => $goods_list1);
        }
        die(json_encode($res, 320));
    }

}