<?php

/**
 * 购买行为
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class buyLogic
{
    /**
     * 购买第一步
     * @param $cart_id
     * @param $ifcart购物车标记
     * @param $member_id用户id
     */
    public function buyStep1($cart_id, $ifcart, $member_id)
    {
        //得到购买商品信息
        if ($ifcart) { //获取购物车中的商品信息，需要在此处加上gc_id_1商品一级分类id,以便于进行商品分类计算。
            $result = $this->getCartList($cart_id, $member_id);
        } else { //获取立即购买的商品信息，同上
            $result = $this->getGoodsList($cart_id, $member_id);
        }

        if (!$result['state']) {
            return $result;
        }

        return $result;
    }

    /**
     * 第一步：处理购物车
     * @param array $cart_id 购物车
     * @param int $member_id 会员编号
     */
    public function getCartList($cart_id, $member_id)
    {
        //取得cart_id和购买数量
        $buy_items = $this->_parseItems($cart_id);
        if (empty($buy_items)) {
            return callback(false, '所购商品无效');
        }
        if (count($buy_items) > 50) {
            return callback(false, '一次购买商品不得超过50种');
        }

        //商品信息[得到最新商品属性及促销信息]
        list($goods_list, $voucher_list,$goods_total_price) = $this->getGoodsOnlineList($buy_items, $member_id);

        $goods_list1['store_cart_list'] = $goods_list;
        $goods_list1['voucher_list'] = $voucher_list?$voucher_list:[];
        $goods_list1['store_goods_total'] = number_format($goods_total_price,2,'.','');

        return callback(true, '', $goods_list1);
    }

    /**
     * 取得商品最新的属性及促销[购物车购买]
     * @param int $goods_id
     * @param int $quantity
     * @return array
     */
    public function getGoodsOnlineList($buy_items, $member_id)
    {
        $cart_id = array_keys($buy_items); //取键值，goods_id
        $cart_id_array = implode(',', $cart_id); //goods_id用,连成字符串
        if(!$cart_id_array){
            die(json_encode(array('code' => '200', 'message' => '数据异常', 'data' => []), 320));
        }

        //判断当前用户是否新人,is_xinren是否享受新人专享，1享受，2不享受
        $member_info = Model()->table('member')->where(array('member_id' => $member_id, 'is_xinren' => 1))->find();
        if ($member_info) { //是新人
            $xinren_str = "";
        } else { //非新人
            $xinren_str = " sgp2.promotion_type != 50 AND";
            $xinren_str1 = " sgp1.promotion_type != 50 AND";
        }
        $member_grade =  (Model('member')->getGrade($member_id))+1 ;

        //获取商品目前促销信息
        $sql = "SELECT goods_id1 AS goods_id,goods_commonid,goods_name,goods_marketprice,hui_discount,goods_price,gc_id_3,deliverer_id,goods_storage,goods_image,goods_promotion_id,promotion_type_id,promotion_type,price,member_levels,sort,state,upper_limit,cart_id,is_vip_price FROM ((SELECT sg.goods_id AS goods_id1,sg.goods_marketprice,sg.goods_commonid,sg.goods_name,sg.hui_discount,sg.gc_id_3,sg.deliverer_id,sg.goods_price,sg.goods_storage,sg.goods_image,sc.cart_id,sgc.is_vip_price FROM 718shop_goods sg LEFT JOIN 718shop_goods_common sgc ON sg.goods_commonid = sgc.goods_commonid LEFT JOIN 718shop_cart sc ON sg.goods_id = sc.goods_id WHERE sg.goods_verify=1 AND sc.cart_id IN (" . $cart_id_array . ") AND sg.goods_state=1 AND sg.is_deleted=0 AND sg.goods_storage> 0) b LEFT JOIN (SELECT sgp1.goods_id as goods_id,sgp1.price as price,sgp1.goods_promotion_id,sgp1.promotion_type_id,sgp1.promotion_type,sgp1.member_levels,sgp1.sort,sgp1.state,sgp1.upper_limit FROM 718shop_goods_promotion sgp1 JOIN (SELECT a.goods_id,a.price AS sgp2_price,substring_index(goods_promotion_id,',',1) AS goods_promotion_id FROM (SELECT sgp2.goods_id,min(sgp2.price) AS price,group_concat(sgp2.goods_promotion_id ORDER BY sgp2.price,sgp2.goods_promotion_id ASC) AS goods_promotion_id FROM 718shop_goods_promotion sgp2 WHERE " . $xinren_str . " IF (sgp2.end_time> 0,sgp2.end_time> UNIX_TIMESTAMP(),1) AND CASE sgp2.promotion_type WHEN 30 THEN sgp2.member_levels<=".$member_grade." ELSE 1 END GROUP BY sgp2.goods_id) a) AS s3 ON sgp1.goods_id = s3.goods_id AND sgp1.price = s3.sgp2_price AND sgp1.goods_promotion_id = s3.goods_promotion_id WHERE" . $xinren_str1 . " IF (sgp1.end_time> 0,sgp1.end_time> UNIX_TIMESTAMP(),1) AND CASE sgp1.promotion_type WHEN 30 THEN sgp1.member_levels<=".$member_grade." ELSE 1 END GROUP BY goods_id) c ON b.goods_id1=c.goods_id)";
        $goods_list = Model()->query($sql);
        $goods_voucher_list = array();
        $goods_voucher_list['goods_class_v'] = array(); //分类代金券
        $goods_voucher_list['goods_id_v'] = array(); //单品代金券
        if ($goods_list) {
            $goods_total_price = 0;
            foreach ($goods_list as $k => $v) {
                if ($buy_items[$v['cart_id']] > $v['goods_storage']) { //判断购买数量是否超过可买库存
                    die(json_encode(array('code' => '200', 'message' => '商品' . $v['goods_name'] . '库存不足请重新购买', 'data' => []), 320));
                }
                switch ($v['promotion_type']) {
                    case 10:
                    case 20: //10秒杀20折扣
                        //获取当前用户已购该商品的数量
                        // $sql_num = "select sum(goods_num) as goods_num from 718shop_order_goods where goods_id=" . $v['goods_id'] . " and promotions_id=" . $v['promotion_type_id'] . " and buyer_id = " . $member_id;
                        // $goods_num_sum = Model()->query($sql_num)[0]['goods_num'];
                        //申slkedit
                    $order_info = Model()->table('order,order_goods')->field('goods_num')->join('inner right')->on('order.order_id = order_goods.order_id')->where(array('order_goods.goods_id' => $v['goods_id'], 'order_goods.buyer_id' => $member_id, 'order_goods.promotions_id' => $v['promotion_type_id'], 'order.order_state' => array('gt', 0)))->select();
                        if (is_array($order_info)) {
                            $goods_num_sum = 0;
                            foreach ($order_info as $key => $value) {
                                $goods_num_sum += $value['goods_num'];
                            }
                        }
                        if ($goods_num_sum + $buy_items[$v['cart_id']] > $v['upper_limit'] && $v['upper_limit']>0) { //已购+本次购买>限购数量 并且购买上限大于0
                            // return callback(false, $v['goods_name'] . '购买数量已达到上限');
                            die(json_encode(array('code' => '200', 'message' => '商品购买数量已达到上限', 'data' => []), 320));
                        }
                        $goods_list[$k]['goods_promotion_price'] = $v['price']; //活动价格
                        $goods_list[$k]['is_group_ladder'] = 4;
                        $goods_list[$k]['type_name'] = promotion_typeName($v['promotion_type']);
                        $goods_list[$k]['xianshi_type'] = $v['promotion_type'] == 10 ? 1 : 2;

                        //代金券金额处理，排除秒杀商品
                        if ($v['promotion_type'] == 20) {
                            $goods_voucher_list['v'] += $v['price'] * $buy_items[$v['cart_id']]; //全场
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $v['price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $v['price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] += $v['price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] = $v['price'] * $buy_items[$v['cart_id']];
                            }
                        }
                        break;
                    case 30: //30会员价
                        if ($v['is_vip_price'] == 1) {
                            $sql_vip = "select goods_promotion_id,promotion_type_id,promotion_type,price,member_levels,sort,state from 718shop_goods_promotion where member_levels<=" . $member_grade. " and promotion_type=30 and goods_id=".$v['goods_id']." order by price asc limit 1";
                            $vip_info = Model()->query($sql_vip)[0];
                            $goods_list[$k]['goods_promotion_id'] = $vip_info['goods_promotion_id'];
                            $goods_list[$k]['promotion_type_id'] = $vip_info['promotion_type_id'];
                            $goods_list[$k]['promotion_type'] = $vip_info['promotion_type'];
                            $goods_list[$k]['goods_promotion_price'] = $vip_info['price']; //会员价格
                            $goods_list[$k]['member_levels'] = $vip_info['member_levels'];
                            $goods_list[$k]['sort'] = $vip_info['sort'];
                            $goods_list[$k]['state'] = $vip_info['state'];
                            $goods_list[$k]['is_group_ladder'] = 8; //会员价格标签
                            $goods_list[$k]['type_name'] = promotion_typeName($vip_info['promotion_type']);
                        }

                        //代金券金额处理
                        $goods_voucher_list['v'] += $vip_info['price'] * $buy_items[$v['cart_id']]; //全场
                        if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                            $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $vip_info['price'] * $buy_items[$v['cart_id']];
                        } else {
                            $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $vip_info['price'] * $buy_items[$v['cart_id']];
                        }
                        if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                            $goods_voucher_list['goods_id_v'][$v['goods_id']] += $vip_info['price'] * $buy_items[$v['cart_id']];
                        } else {
                            $goods_voucher_list['goods_id_v'][$v['goods_id']] = $vip_info['price'] * $buy_items[$v['cart_id']];
                        }
                        break;
                    case 40: //40新品
                        $goods_list[$k]['goods_promotion_price'] = $v['price']; //活动价格
                        $goods_list[$k]['is_group_ladder'] = 7;
                        $goods_list[$k]['type_name'] = promotion_typeName($v['promotion_type']);

                        //代金券金额处理
                        $goods_voucher_list['v'] += $v['price'] * $buy_items[$v['cart_id']]; //全场
                        if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                            $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $v['price'] * $buy_items[$v['cart_id']];
                        } else {
                            $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $v['price'] * $buy_items[$v['cart_id']];
                        }
                        if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                            $goods_voucher_list['goods_id_v'][$v['goods_id']] += $v['price'] * $buy_items[$v['cart_id']];
                        } else {
                            $goods_voucher_list['goods_id_v'][$v['goods_id']] = $v['price'] * $buy_items[$v['cart_id']];
                        }
                        break;
                    case 50: //50新人
                        die(json_encode(array('code' => '200', 'message' => '商品' . $v['goods_name'] . '为新用户专享商品，只可单独购买', 'data' => []), 320));
                        break;
                    case 60: //60阶梯价
                        $goods_list[$k]['is_group_ladder'] = 1;
                        $goods_list[$k]['type_name'] = promotion_typeName($v['promotion_type']);
                        $goods_list[$k]['goods_promotion_price'] = $v['price']; //活动价格

                        //代金券金额处理
                        $goods_voucher_list['v'] += $v['price'] * $buy_items[$v['cart_id']]; //全场
                        if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                            $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $v['price'] * $buy_items[$v['cart_id']];
                        } else {
                            $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $v['price'] * $buy_items[$v['cart_id']];
                        }
                        if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                            $goods_voucher_list['goods_id_v'][$v['goods_id']] += $v['price'] * $buy_items[$v['cart_id']];
                        } else {
                            $goods_voucher_list['goods_id_v'][$v['goods_id']] = $v['price'] * $buy_items[$v['cart_id']];
                        }
                        break;
                    case 70: //70团购
                        $goods_list[$k]['is_group_ladder'] = 2;
                        $goods_list[$k]['type_name'] = promotion_typeName($v['promotion_type']);
                        $goods_list[$k]['goods_promotion_price'] = $v['price']; //活动价格

                        //代金券金额处理
                        $goods_voucher_list['v'] += $v['price'] * $buy_items[$v['cart_id']]; //全场
                        if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                            $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $v['price'] * $buy_items[$v['cart_id']];
                        } else {
                            $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $v['price'] * $buy_items[$v['cart_id']];
                        }
                        if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                            $goods_voucher_list['goods_id_v'][$v['goods_id']] += $v['price'] * $buy_items[$v['cart_id']];
                        } else {
                            $goods_voucher_list['goods_id_v'][$v['goods_id']] = $v['price'] * $buy_items[$v['cart_id']];
                        }
                        break;
                    case 80: //80周边
                        $goods_list[$k]['is_group_ladder'] = 5;
                        $goods_list[$k]['type_name'] = promotion_typeName($v['promotion_type']);
                        $goods_list[$k]['goods_promotion_price'] = $v['price']; //活动价格

                        //代金券金额处理
                        $goods_voucher_list['v'] += $v['price'] * $buy_items[$v['cart_id']]; //全场
                        if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                            $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $v['price'] * $buy_items[$v['cart_id']];
                        } else {
                            $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $v['price'] * $buy_items[$v['cart_id']];
                        }
                        if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                            $goods_voucher_list['goods_id_v'][$v['goods_id']] += $v['price'] * $buy_items[$v['cart_id']];
                        } else {
                            $goods_voucher_list['goods_id_v'][$v['goods_id']] = $v['price'] * $buy_items[$v['cart_id']];
                        }
                        break;
                    case 90: //90进口商品
                        $goods_list[$k]['is_group_ladder'] = 9;
                        $goods_list[$k]['type_name'] = promotion_typeName($v['promotion_type']);
                        $goods_list[$k]['goods_promotion_price'] = $v['price']; //活动价格

                        //代金券金额处理
                        $goods_voucher_list['v'] += $v['price'] * $buy_items[$v['cart_id']]; //全场
                        if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                            $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $v['price'] * $buy_items[$v['cart_id']];
                        } else {
                            $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $v['price'] * $buy_items[$v['cart_id']];
                        }
                        if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                            $goods_voucher_list['goods_id_v'][$v['goods_id']] += $v['price'] * $buy_items[$v['cart_id']];
                        } else {
                            $goods_voucher_list['goods_id_v'][$v['goods_id']] = $v['price'] * $buy_items[$v['cart_id']];
                        }
                        break;
                    default: //无活动按原价处理
                        $goods_list[$k]['is_group_ladder'] = 0;
                        $goods_list[$k]['goods_promotion_price'] = $v['goods_price'];//活动价=原价
                        $goods_list[$k]['type_name'] = '普通商品';
                        $goods_list[$k]['promotion_type'] = 0;

                        //代金券金额处理
                        $goods_voucher_list['v'] += $v['goods_price'] * $buy_items[$v['cart_id']]; //全场
                        if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                            $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                        } else {
                            $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                        }
                        if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                            $goods_voucher_list['goods_id_v'][$v['goods_id']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                        } else {
                            $goods_voucher_list['goods_id_v'][$v['goods_id']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                        }
                        break;
                }
                if($v['price']==$v['goods_price'] && $v['price']>0){
                    $goods_list[$k]['goods_price'] = $v['goods_marketprice'];//原价=市场价
                }
                $goods_list[$k]['goods_num'] = $buy_items[$v['cart_id']]; //加入购买数量
                $goods_list[$k]['goods_image'] = cthumb($v['goods_image']); //拼接图片地址
                $goods_total_price += $goods_list[$k]['goods_num'] * $goods_list[$k]['goods_promotion_price'];
            }
        }

        if ($goods_voucher_list['v'] > 0) {
            $vstr = "((svt.type IS NULL OR svt.type = 0) AND sv.voucher_limit<=" . $goods_voucher_list['v'] . ")";
        }
        if ($goods_voucher_list['goods_class_v']) {
            foreach ($goods_voucher_list['goods_class_v'] as $goodsclass_id => $goods_class_limit) {
                $vstr .= " OR (svt.type=1 AND sv.voucher_limit<=" . $goods_class_limit . " AND svt.is_use=0 AND svt.goodsclass_id NOT LIKE '%" . $goodsclass_id . "%') OR (svt.type=1 AND sv.voucher_limit<=" . $goods_class_limit . " AND svt.is_use=1 AND svt.goodsclass_id LIKE '%" . $goodsclass_id . "%')";
            }
        }
        if ($goods_voucher_list['goods_id_v']) {
            foreach ($goods_voucher_list['goods_id_v'] as $goods_id => $goods_id_limit) {
                $vstr .= " OR (svt.type=2 AND sv.voucher_limit<=" . $goods_id_limit . " AND svt.is_use=0 AND svt.goods_id NOT LIKE '%" . $goods_id . "%') OR (svt.type=2 AND sv.voucher_limit<=" . $goods_id_limit . " AND svt.is_use=1 AND svt.goods_id LIKE '%" . $goods_id . "%')";
            }
        }
        $sql_vstr = "";
        $voucher_list = [];
        if ($vstr) {
            $sql_vstr = " AND (" . $vstr . ")";
        }
        if($sql_vstr){
            $sql_voucher = "SELECT sv.voucher_id,sv.voucher_code,sv.voucher_t_id,sv.voucher_title,sv.voucher_price,sv.voucher_limit,sv.voucher_order_type,svt.is_use,svt.goods_id,svt.goodsclass_id,svt.type FROM 718shop_voucher sv LEFT JOIN 718shop_voucher_type svt ON sv.voucher_t_id=svt.voucher_tid WHERE UNIX_TIMESTAMP() BETWEEN sv.voucher_start_date AND sv.voucher_end_date AND sv.voucher_owner_id=" . $member_id . " AND sv.voucher_state=1 " . $sql_vstr." GROUP BY sv.voucher_t_id";
            $voucher_list = Model()->query($sql_voucher);
        }
        return array($goods_list, $voucher_list,$goods_total_price);
    }

    /**
     * 第一步：处理立即购买
     *
     * @param array $cart_id 购物车
     * @param int $member_id 会员编号
     */
    public function getGoodsList($cart_id, $member_id)
    {
        //取得POST ID和购买数量
        $buy_items = $this->_parseItems($cart_id);
        if (empty($buy_items)) {
            die(json_encode(array('code' => '200', 'message' => '所购商品无效', 'data' => []), 320));
        }

        $goods_id = key($buy_items); //取键值，goods_id
        $quantity = intval(current($buy_items)); //取值，商品数量

        //获取当前库存，判断购买数量是否大于当前库存，获取已审核过、已上架、未删除的商品信息
        $goods_info = Model()->table('goods')->field('goods_storage')->where(array('goods_id' => $goods_id, 'goods_state' => 1, 'goods_verify' => 1, 'is_deleted' => 0))->find();
        if (!$goods_info) {
            die(json_encode(array('code' => '200', 'message' => '商品已下架或不存在', 'data' => []), 320));
        }
        if ($quantity > $goods_info['goods_storage']) {
            die(json_encode(array('code' => '200', 'message' => '商品库存不足请重新购买', 'data' => []), 320));
        }

        //商品信息[得到最新商品属性及促销信息]
        list($goods_list, $voucher_list,$goods_total_price) = $this->getGoodsOnlineInfo($goods_id, $quantity, $member_id);

        $goods_list1['store_cart_list'] = $goods_list;
        $goods_list1['voucher_list'] = $voucher_list?$voucher_list:[];
        $goods_list1['store_goods_total'] = number_format($goods_total_price,2,'.','');

        return callback(true, '', $goods_list1);
    }
    /**
     * 取得商品最新的属性及促销[立即购买]
     * @param int $goods_id
     * @param int $quantity
     * @return array
     */
    public function getGoodsOnlineInfo($goods_id, $quantity, $member_id)
    {
        //判断当前用户是否新人,is_xinren是否享受新人专享，1享受，2不享受
        $member_info = Model()->table('member')->where(array('member_id' => $member_id, 'is_xinren' => 1))->find();
        if ($member_info) { //是新人
            $xinren_str = "";
        } else { //非新人
            $xinren_str = " sgp2.promotion_type != 50 AND";
            $xinren_str1 = " sgp1.promotion_type != 50 AND";
        }
        $member_grade = (Model('member')->getGrade($member_id))+1;

        //获取商品目前促销信息
        $sql = "SELECT goods_id1 AS goods_id,goods_commonid,goods_marketprice,goods_name,hui_discount,goods_price,gc_id_3,deliverer_id,goods_storage,goods_image,goods_promotion_id,promotion_type_id,promotion_type,price,member_levels,sort,state,upper_limit,is_vip_price FROM ((SELECT sg.goods_id AS goods_id1,sg.goods_commonid,sg.goods_marketprice,sg.goods_name,sg.hui_discount,sg.gc_id_3,sg.deliverer_id,sg.goods_price,sg.goods_storage,sg.goods_image,sgc.is_vip_price FROM 718shop_goods sg LEFT JOIN 718shop_goods_common sgc ON sg.goods_commonid = sgc.goods_commonid WHERE sg.goods_verify=1 AND sg.goods_state=1 AND sg.is_deleted=0 AND sg.goods_storage> 0 AND sg.goods_id IN (" . $goods_id . ")) b LEFT JOIN (SELECT sgp1.goods_id as goods_id,sgp1.price as price,sgp1.goods_promotion_id,sgp1.promotion_type_id,sgp1.promotion_type,sgp1.member_levels,sgp1.sort,sgp1.state,sgp1.upper_limit FROM 718shop_goods_promotion sgp1 JOIN (SELECT a.goods_id,a.price AS sgp2_price,substring_index(goods_promotion_id,',',1) AS goods_promotion_id FROM (SELECT sgp2.goods_id,min(sgp2.price) AS price,group_concat(sgp2.goods_promotion_id ORDER BY sgp2.price,sgp2.goods_promotion_id ASC) AS goods_promotion_id FROM 718shop_goods_promotion sgp2 WHERE " . $xinren_str . " IF (sgp2.end_time> 0,sgp2.end_time> UNIX_TIMESTAMP(),1) AND CASE sgp2.promotion_type WHEN 30 THEN sgp2.member_levels<=".$member_grade." ELSE 1 END AND sgp2.goods_id IN (" . $goods_id . ") GROUP BY sgp2.goods_id) a) AS s3 ON sgp1.goods_id = s3.goods_id AND sgp1.price = s3.sgp2_price AND sgp1.goods_promotion_id = s3.goods_promotion_id WHERE " . $xinren_str1 . " IF (sgp1.end_time> 0,sgp1.end_time> UNIX_TIMESTAMP(),1) AND CASE sgp1.promotion_type WHEN 30 THEN sgp1.member_levels<=".$member_grade." ELSE 1 END GROUP BY goods_id) c ON b.goods_id1=c.goods_id)";
        $goods_info = Model()->query($sql)[0];
        $goods_info['goods_num'] = $quantity; //加入购买数量
        $goods_info['goods_image'] = cthumb($goods_info['goods_image']);

        switch ($goods_info['promotion_type']) {
            case 10:
            case 20: //10秒杀20折扣
                //获取当前用户已购该商品的数量
                // $sql_num = "select sum(goods_num) as goods_num from 718shop_order_goods where goods_id=" . $goods_id . " and promotions_id=" . $goods_info['promotion_type_id'] . " and buyer_id = " . $member_id;
                // $goods_num_sum = Model()->query($sql_num)[0]['goods_num'];
             // slkedit已买，order_state>0
                $order_info = Model()->table('order,order_goods')->field('goods_num')->join('inner right')->on('order.order_id = order_goods.order_id')->where(array('order_goods.goods_id' => $goods_id, 'order_goods.buyer_id' => $member_id, 'order_goods.promotions_id' => $goods_info['promotion_type_id'], 'order.order_state' => array('gt', 0)))->select();
                if (is_array($order_info)) {
                    $goods_num_sum = 0;
                    foreach ($order_info as $key => $value) {
                        $goods_num_sum += $value['goods_num'];
                    }
                }
                if ($goods_num_sum + $quantity > $goods_info['upper_limit'] && $goods_info['upper_limit']>0) { //已购+本次购买>限购数量
                    die(json_encode(array('code' => '200', 'message' => '购买数量已达到上限', 'data' => []), 320));
                }
                $goods_info['goods_promotion_price'] = $goods_info['price']; //活动价格
                $goods_info['is_group_ladder'] = 4;
                $goods_info['type_name'] = promotion_typeName($goods_info['promotion_type']);
                $goods_info['xianshi_type'] = $goods_info['promotion_type'] == 10 ? 1 : 2;
                break;
            case 30: //30会员价
                if ($goods_info['is_vip_price'] == 1) {
                    $sql_vip = "select goods_promotion_id,promotion_type_id,promotion_type,price,member_levels,sort,state from 718shop_goods_promotion where member_levels<=" .$member_grade. " and promotion_type=30 and goods_id=".$goods_info['goods_id']." order by price asc limit 1";
                    $vip_info = Model()->query($sql_vip)[0];
                    $goods_info['goods_promotion_id'] = $vip_info['goods_promotion_id'];
                    $goods_info['promotion_type_id'] = $vip_info['promotion_type_id'];
                    $goods_info['promotion_type'] = $vip_info['promotion_type'];
                    $goods_info['goods_promotion_price'] = $vip_info['price']; //会员价格
                    $goods_info['member_levels'] = $vip_info['member_levels'];
                    $goods_info['sort'] = $vip_info['sort'];
                    $goods_info['state'] = $vip_info['state'];
                    $goods_info['is_group_ladder'] = 8; //会员价格标签
                    $goods_info['type_name'] = promotion_typeName($vip_info['promotion_type']);
                }
                break;
            case 40: //40新品
                $goods_info['goods_promotion_price'] = $goods_info['price']; //活动价格
                $goods_info['is_group_ladder'] = 7;
                $goods_info['type_name'] = promotion_typeName($goods_info['promotion_type']);
                break;
            case 50: //50新人
                if (!$member_info) { //非新人返回错误
                    die(json_encode(array('code' => '200', 'message' => $goods_info['goods_name'] . '为新用户专享商品，非新用户不可购买', 'data' => []), 320));
                }
                $goods_info['goods_promotion_price'] = $goods_info['price']; //活动价格
                $goods_info['is_group_ladder'] = 3;
                $goods_info['type_name'] = promotion_typeName($goods_info['promotion_type']);
                break;
            case 60: //60阶梯价
                $goods_info['goods_promotion_price'] = $goods_info['price']; //活动价格
                $goods_info['is_group_ladder'] = 1;
                $goods_info['type_name'] = promotion_typeName($goods_info['promotion_type']);
                break;
            case 70: //70团购
                $goods_info['goods_promotion_price'] = $goods_info['price']; //活动价格
                $goods_info['is_group_ladder'] = 2;
                $goods_info['type_name'] = promotion_typeName($goods_info['promotion_type']);
                break;
            case 80: //80即买即送
                $goods_info['goods_promotion_price'] = $goods_info['price']; //活动价格
                $goods_info['is_group_ladder'] = 5;
                $goods_info['type_name'] = promotion_typeName($goods_info['promotion_type']);
                break;
            case 90: //90进口商品
                $goods_info['goods_promotion_price'] = $goods_info['price']; //活动价格
                $goods_info['is_group_ladder'] = 9;
                $goods_info['type_name'] = promotion_typeName($goods_info['promotion_type']);
                break;
            default: //无活动按原价处理
                $goods_info['goods_promotion_price'] = $goods_info['goods_price']; //活动价格
                $goods_info['is_group_ladder'] = 0;
                $goods_info['type_name'] = '普通商品';
                $goods_info['promotion_type'] = 0;
                break;
        }
        if($goods_info['price']==$goods_info['goods_price'] && $goods_info['price']>0){
            $goods_info['goods_price'] = $goods_info['goods_marketprice'];//原价=市场价
        }
        //计算商品总价，单价*数量
        $goods_total_price = $goods_info['goods_promotion_price'] * $goods_info['goods_num'];

        if (($goods_info['xianshi_type']) != 1) { //非限时秒杀商品
            //可用代金券列表
            //mansong_gc_id全场1分类$goods_info['gc_id_3']单品$goods_id
            $sql_voucher = "SELECT sv.voucher_id,sv.voucher_code,sv.voucher_t_id,sv.voucher_title,sv.voucher_price,sv.voucher_limit,sv.voucher_order_type,svt.is_use,svt.goods_id,svt.goodsclass_id,svt.type FROM 718shop_voucher sv LEFT JOIN 718shop_voucher_type svt ON sv.voucher_t_id=svt.voucher_tid WHERE UNIX_TIMESTAMP() BETWEEN sv.voucher_start_date AND sv.voucher_end_date AND sv.voucher_limit<=".$goods_total_price." AND sv.voucher_owner_id=" . $member_id . " AND sv.voucher_state=1 AND (svt.type IS NULL OR svt.type=1 OR svt.type=2 OR svt.type = 0) GROUP BY sv.voucher_t_id";

            $voucher_list = Model()->query($sql_voucher);
            if ($voucher_list) {
                foreach ($voucher_list as $k => $v) {
                    //判断代金券是否可用
                    if ($v['type'] == 1) { //全场0,分类1,商品2
                        if (($v['is_use'] == 0 && in_array($goods_info['gc_id_3'], explode(',', $v['goodsclass_id']))) || $v['is_use'] == 1 && !in_array($goods_info['gc_id_3'], explode(',', $v['goodsclass_id']))) { //(不包含且分类在)或(包含且分类不在)，剔除该条代金券数据
                            unset($voucher_list[$k]);
                        }
                    } elseif ($v['type'] == 2) {
                        if (($v['is_use'] == 0 && in_array($goods_id, explode(',', $v['goods_id']))) || $v['is_use'] == 1 && !in_array($goods_id, explode(',', $v['goods_id']))) { //(不包含且商品在)或(包含且商品不在)，剔除该条代金券数据
                            unset($voucher_list[$k]);
                        }
                    } else {
                        continue;
                    }
                }
                $voucher_list = array_values($voucher_list);
            }
        }
        if($goods_info['promotion_type'==50]){//新人商品不返代金券数据
            $voucher_list = [];
        }
        $goods_list = [$goods_info];

        return array($goods_list, $voucher_list, $goods_total_price);
    }

    /**
     * 购买第二步
     * @param array $post
     * @param int $member_id
     * @param string $member_name
     * @param string $member_email
     * @return array
     */
    public function buyStep2($data, $member_id)
    {
        //判断当前用户是否新人,is_xinren是否享受新人专享，1享受，2不享受
        $member_info = Model()->table('member')->where(array('member_id' => $member_id, 'is_xinren' => 1))->find();
        if ($member_info) { //是新人
            $xinren_str = "";
        } else { //非新人
            $xinren_str = " sgp2.promotion_type != 50 AND ";
            $xinren_str1 = " sgp1.promotion_type != 50 AND";
        }
        $member_grade = (Model('member')->getGrade($member_id))+1;
        $buy_items = $this->_parseItems($data['cart_id']);

        //1.获取商品数据
        if ($data['ifcart']) { //来自购物车
            $cart_id = array_keys($buy_items); //取键值，cart_id
            $cart_id_array = implode(',', $cart_id); //cart_id用,连成字符串

            //判断商品是否存在即买即送活动
            $sql_promotion = "SELECT sgp.goods_id FROM 718shop_goods_promotion sgp LEFT JOIN 718shop_cart sc ON sgp.goods_id=sc.goods_id WHERE sc.cart_id IN (" . $cart_id_array . ") AND sgp.promotion_type=80";
            $goods_promotion_info = Model()->query($sql_promotion);
            if ($goods_promotion_info) { //存在需要判断自提点
                $goods_id_array_p = array_column($goods_promotion_info, 'goods_id'); //循环获取商品id进行判断
                foreach ($goods_id_array_p as $item) {
                    $ziti_info = Model()->table('buy_deliver_goods')->field('goods_id')->where(array('goods_id' => $item, 'ziti_id' => $data['ziti_id']))->find();
                    if (!$ziti_info) { //所购买商品不在当前自提点
                        $goods_name = Model('goods')->getfby_goods_id($item, 'goods_name');
                        die(json_encode(array('code' => '200', 'message' => '商品' . $goods_name . '在当前区域内不可售卖', 'data' => []), 320));
                    }
                }
            }

            //获取商品目前促销信息
            $sql = "SELECT goods_id1 AS goods_id,goods_commonid,goods_name,hui_discount,goods_price,storage_id,gc_id_3,deliverer_id,goods_storage,goods_image,goods_promotion_id,promotion_type_id,promotion_type,price,member_levels,sort,state,upper_limit,IFNULL(commis_rate_p,commis_rate) AS commis_rate,goods_serial,goods_barcode,is_cw,points_send,is_vip_price,goods_costprice,cart_id FROM ((SELECT sg.goods_id AS goods_id1,sg.goods_commonid,sg.goods_name,sg.hui_discount,sg.gc_id_3,sg.deliverer_id,sg.goods_price,sg.goods_storage,sg.goods_image,sd.storage_id,sg.commis_rate,sg.goods_serial,sg.goods_barcode,sg.is_cw,sg.points_send,sgc.is_vip_price,sg.goods_costprice,sc.cart_id FROM 718shop_goods sg LEFT JOIN 718shop_goods_common sgc ON sg.goods_commonid = sgc.goods_commonid LEFT JOIN 718shop_daddress sd ON sd.address_id=sg.deliverer_id LEFT JOIN 718shop_cart sc ON sc.goods_id=sg.goods_id WHERE sg.goods_verify=1 AND sc.cart_id IN (" . $cart_id_array . ") AND sg.goods_state=1 AND sg.is_deleted=0 AND sg.goods_storage> 0) b LEFT JOIN (SELECT sgp1.goods_id as goods_id,sgp1.price as price,sgp1.goods_promotion_id,sgp1.promotion_type_id,sgp1.promotion_type,sgp1.member_levels,sgp1.sort,sgp1.state,sgp1.upper_limit,sgp1.commis_rate AS commis_rate_p FROM 718shop_goods_promotion sgp1 JOIN (SELECT a.goods_id,a.price AS sgp2_price,substring_index(goods_promotion_id,',',1) AS goods_promotion_id FROM (SELECT sgp2.goods_id,min(sgp2.price) AS price,group_concat(sgp2.goods_promotion_id ORDER BY sgp2.price,sgp2.goods_promotion_id ASC) AS goods_promotion_id FROM 718shop_goods_promotion sgp2 WHERE " . $xinren_str . " IF (sgp2.end_time> 0,sgp2.end_time> UNIX_TIMESTAMP(),1) AND CASE sgp2.promotion_type WHEN 30 THEN sgp2.member_levels<=".$member_grade." ELSE 1 END GROUP BY sgp2.goods_id) a) AS s3 ON sgp1.goods_id = s3.goods_id AND sgp1.price = s3.sgp2_price AND sgp1.goods_promotion_id = s3.goods_promotion_id WHERE" . $xinren_str1 . " IF (sgp1.end_time> 0,sgp1.end_time> UNIX_TIMESTAMP(),1) AND CASE sgp1.promotion_type WHEN 30 THEN sgp1.member_levels<=".$member_grade." ELSE 1 END GROUP BY goods_id) c ON b.goods_id1=c.goods_id)";
            $goods_list = Model()->query($sql);

            $goods_voucher_list = array();
            $goods_voucher_list['goods_class_m'] = array(); //分类满赠
            $goods_voucher_list['goods_class_v'] = array(); //分类代金券
            $goods_voucher_list['goods_id_m'] = array(); //单品满赠
            $goods_voucher_list['goods_id_v'] = array(); //单品代金券
            $goods_voucher_list['v_num'] = 0; //全场代金券商品种数
            if ($goods_list) {
                foreach ($goods_list as $k => $v) {
                    if ($buy_items[$v['cart_id']] > $v['goods_storage']) { //判断购买数量是否超过可买库存
                        die(json_encode(array('code' => '200', 'message' => '商品' . $v['goods_name'] . '库存不足请重新购买', 'data' => []), 320));
                    }
                    switch ($v['promotion_type']) {
                        case 10:
                        case 20: //10秒杀20折扣
                            //获取当前用户已购该商品的数量
                            // $sql_num = "select sum(goods_num) as goods_num from 718shop_order_goods where goods_id=" . $v['goods_id'] . " and promotions_id=" . $v['promotion_type_id'] . " and buyer_id = " . $member_id;
                            // $goods_num_sum = Model()->query($sql_num)[0]['goods_num'];
                         // slkedit已买，order_state>0
                    $order_info = Model()->table('order,order_goods')->field('goods_num')->join('inner right')->on('order.order_id = order_goods.order_id')->where(array('order_goods.goods_id' =>  $v['goods_id'], 'order_goods.buyer_id' => $member_id, 'order_goods.promotions_id' => $v['promotion_type_id'], 'order.order_state' => array('gt', 0)))->select();
                    if (is_array($order_info)) {
                        $goods_num_sum = 0;
                        foreach ($order_info as $key => $value) {
                            $goods_num_sum += $value['goods_num'];
                        }
                    }
                            if ($v['upper_limit'] > 0 && ($goods_num_sum + $buy_items[$v['cart_id']] > $v['upper_limit'])) { //已购+本次购买>限购数量
                                die(json_encode(array('code' => '200', 'message' => '商品' . $v['goods_name'] . '购买数量已达到上限', 'data' => []), 320));
                            }
                            $goods_list[$k]['goods_price'] = $v['price']; //活动价格
                            $goods_list[$k]['is_group_ladder'] = 4;
                            $goods_list[$k]['type_name'] = promotion_typeName($v['promotion_type']);
                            $goods_list[$k]['xianshi_type'] = $v['promotion_type'] == 10 ? 1 : 2;

                            //满送金额处理
                            $goods_voucher_list['m'] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_m']))) { //分类
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] = $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_m']))) { //单品
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] = $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            //代金券金额处理，排除秒杀商品
                            if ($v['promotion_type'] == 20) {
                                $goods_voucher_list['v'] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']]; //全场
                                $goods_voucher_list['v_num']++;
                                if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                                    $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                                } else {
                                    $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                                }
                                if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                                    $goods_voucher_list['goods_id_v'][$v['goods_id']] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                                } else {
                                    $goods_voucher_list['goods_id_v'][$v['goods_id']] = $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                                }
                            }
                            break;
                        case 30: //30会员价
                            if ($v['is_vip_price'] == 1) {
                                $sql_vip = "select goods_promotion_id,promotion_type_id,promotion_type,price,member_levels,sort,state from 718shop_goods_promotion where goods_id=" . $v['goods_id'] . " and member_levels<=" . $member_grade. " and promotion_type=" . $v['promotion_type'] . " order by price asc limit 1";
                                $vip_info = Model()->query($sql_vip)[0];
                                $goods_list[$k]['goods_promotion_id'] = $vip_info['goods_promotion_id'];
                                $goods_list[$k]['promotion_type_id'] = $vip_info['promotion_type_id'];
                                $goods_list[$k]['promotion_type'] = $vip_info['promotion_type'];
                                $goods_list[$k]['goods_price'] = $vip_info['price']; //会员价格
                                $goods_list[$k]['member_levels'] = $vip_info['member_levels'];
                                $goods_list[$k]['sort'] = $vip_info['sort'];
                                $goods_list[$k]['state'] = $vip_info['state'];
                                $goods_list[$k]['is_group_ladder'] = 8; //会员价格标签
                                $goods_list[$k]['type_name'] = promotion_typeName($vip_info['promotion_type']);
                            } else {
                                $goods_list[$k]['is_group_ladder'] = 0; //普通商品处理
                                $goods_list[$k]['type_name'] = '普通商品';
                            }
                            //满送金额处理
                            $goods_voucher_list['m'] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_m']))) { //分类
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] = $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_m']))) { //单品
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] = $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            //代金券金额处理
                            $goods_voucher_list['v'] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            $goods_voucher_list['v_num']++;
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] = $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            break;
                        case 40: //40新品
                            $goods_list[$k]['goods_price'] = $v['price']; //活动价格
                            $goods_list[$k]['is_group_ladder'] = 7;
                            $goods_list[$k]['type_name'] = promotion_typeName($v['promotion_type']);

                            //满送金额处理
                            $goods_voucher_list['m'] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_m']))) { //分类
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] = $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_m']))) { //单品
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] = $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            //代金券金额处理
                            $goods_voucher_list['v'] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            $goods_voucher_list['v_num']++;
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] += $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] = $goods_list[$k]['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            break;
                        case 50: //50新人
                            die(json_encode(array('code' => '200', 'message' => '商品' . $v['goods_name'] . '为新用户专享商品，只可单独购买', 'data' => []), 320));
                            break;
                        case 60: //60阶梯价
                            $goods_list[$k]['is_group_ladder'] = 1;
                            $goods_list[$k]['type_name'] = promotion_typeName($v['promotion_type']);

                            //满送金额处理
                            $goods_voucher_list['m'] += $v['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_m']))) { //分类
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_m']))) { //单品
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            //代金券金额处理
                            $goods_voucher_list['v'] += $v['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            $goods_voucher_list['v_num']++;
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            break;
                        case 70: //70团购
                            $goods_list[$k]['is_group_ladder'] = 2;
                            $goods_list[$k]['type_name'] = promotion_typeName($v['promotion_type']);

                            //满送金额处理
                            $goods_voucher_list['m'] += $v['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_m']))) { //分类
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_m']))) { //单品
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            //代金券金额处理
                            $goods_voucher_list['v'] += $v['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            $goods_voucher_list['v_num']++;
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            break;
                        case 80: //80即买即送
                            $goods_list[$k]['is_group_ladder'] = 5;
                            $goods_list[$k]['type_name'] = promotion_typeName($v['promotion_type']);

                            //满送金额处理
                            $goods_voucher_list['m'] += $v['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_m']))) { //分类
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_m']))) { //单品
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            //代金券金额处理
                            $goods_voucher_list['v'] += $v['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            $goods_voucher_list['v_num']++;
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            break;
                        case 90: //90进口商品
                            $goods_list[$k]['is_group_ladder'] = 9;
                            $goods_list[$k]['type_name'] = promotion_typeName($v['promotion_type']);

                            //满送金额处理
                            $goods_voucher_list['m'] += $v['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_m']))) { //分类
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_m']))) { //单品
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            //代金券金额处理
                            $goods_voucher_list['v'] += $v['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            $goods_voucher_list['v_num']++;
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            break;
                        default: //无活动按原价处理
                            $goods_list[$k]['is_group_ladder'] = 0;
                            $goods_list[$k]['type_name'] = '普通商品';

                            //满送金额处理
                            $goods_voucher_list['m'] += $v['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_m']))) { //分类
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_m'][$v['gc_id_3']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_m']))) { //单品
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_m'][$v['goods_id']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            //代金券金额处理
                            $goods_voucher_list['v'] += $v['goods_price'] * $buy_items[$v['cart_id']]; //全场
                            $goods_voucher_list['v_num']++;
                            if (in_array($v['gc_id_3'], array_keys($goods_voucher_list['goods_class_v']))) { //分类
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_class_v'][$v['gc_id_3']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            if (in_array($v['goods_id'], array_keys($goods_voucher_list['goods_id_v']))) { //单品
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] += $v['goods_price'] * $buy_items[$v['cart_id']];
                            } else {
                                $goods_voucher_list['goods_id_v'][$v['goods_id']] = $v['goods_price'] * $buy_items[$v['cart_id']];
                            }
                            break;
                    }
                    $goods_list[$k]['goods_num'] = $buy_items[$v['cart_id']]; //加入购买数量
                    $goods_list[$k]['goods_image'] = $v['goods_image']; //拼接图片地址
                    $goods_list[$k]['goods_pay_price'] = $goods_list[$k]['goods_price'] * $goods_list[$k]['goods_num']; //商品实际支付金额
                    $goods_list[$k]['voucher_price'] = 0; //记录优惠券优惠的部分
                    if($v['promotion_type']>0){
                        //序列化商品当前活动信息
                        $goods_list[$k]['promotion_info'] = serialize(array('promotion_type' => $v['promotion_type'], 'price' => $v['price'], 'member_levels' => $v['member_levels'], 'upper_limit' => $v['upper_limit'], 'commis_rate' => $v['commis_rate']));
                    }
                    $goods_list['goods_total_price'] += $goods_list[$k]['goods_pay_price']; //订单实际总金额
                }
            }
            if ($goods_voucher_list['v'] > 0) {
                $vstr = "((svt.type IS NULL OR svt.type = 0) AND sv.voucher_limit<=" . $goods_voucher_list['v'] . ")";
            }
            if ($goods_voucher_list['goods_class_v']) {
                foreach ($goods_voucher_list['goods_class_v'] as $goodsclass_id => $goods_class_limit) {
                    $vstr .= " OR (svt.type=1 AND sv.voucher_limit<=" . $goods_class_limit . " AND svt.is_use=0 AND svt.goodsclass_id NOT LIKE '%" . $goodsclass_id . "%') OR (svt.type=1 AND sv.voucher_limit<=" . $goods_class_limit . " AND svt.is_use=1 AND svt.goodsclass_id LIKE '%" . $goodsclass_id . "%')";
                }
            }
            if ($goods_voucher_list['goods_id_v']) {
                foreach ($goods_voucher_list['goods_id_v'] as $goods_id => $goods_id_limit) {
                    $vstr .= " OR (svt.type=2 AND sv.voucher_limit<=" . $goods_id_limit . " AND svt.is_use=0 AND svt.goods_id NOT LIKE '%" . $goods_id . "%') OR (svt.type=2 AND sv.voucher_limit<=" . $goods_id_limit . " AND svt.is_use=1 AND svt.goods_id LIKE '%" . $goods_id . "%')";
                }
            }
            $sql_vstr = "";
            if ($vstr) {
                $sql_vstr = " AND (" . $vstr . ")";
            }
            $sql_voucher = "SELECT sv.voucher_id,sv.voucher_code,sv.voucher_t_id,sv.voucher_title,sv.voucher_price,sv.voucher_limit,sv.voucher_order_type,svt.is_use,svt.goods_id,svt.goodsclass_id,svt.type FROM 718shop_voucher sv LEFT JOIN 718shop_voucher_type svt ON sv.voucher_t_id=svt.voucher_tid WHERE UNIX_TIMESTAMP() BETWEEN sv.voucher_start_date AND sv.voucher_end_date AND sv.voucher_owner_id=" . $member_id . " AND sv.voucher_state=1 AND sv.voucher_t_id = " . $data['voucher_t_id'] . " " . $sql_vstr . "LIMIT 1";
            $voucher_info = Model()->query($sql_voucher)[0];
            if ($voucher_info) {
                //判断代金券类型
                if ($voucher_info['type'] == 1) { //分类1
                    $voucher_price = $voucher_info['voucher_price']; //代金券金额
                    $voucher_limit_price = 0; //符合使用代金券的总金额
                    $temp_voucher_price = $voucher_price;
                    $goodsclass_id_array_v = explode(',', $voucher_info['goodsclass_id']); //代金券信息中商品分类id数组
                    $goods_classid_array_vv = array_keys($goods_voucher_list['goods_class_v']); //可能可用代金券的商品分类id数组
                    $fi_goods_classid_array = array();
                    if ($voucher_info['is_use'] == 0) { //id里的不能用
                        foreach ($goods_classid_array_vv as $goods_classid_v) {
                            if (!in_array($goods_classid_v, $goodsclass_id_array_v)) {
                                array_push($fi_goods_classid_array, $goods_classid_v); //排除代金券设置规则的商品分类id，挑选id不在设置中的商品分类id，得到最终可用代金券的goods_classid数组
                                $voucher_limit_price += $goods_voucher_list['goods_class_v'][$goods_classid_v];
                            }
                        }
                        $voucher_num = count($fi_goods_classid_array);
                        foreach ($goods_list as $key => $value) {
                            if ($value['promotion_type'] != 10 && in_array($value['gc_id_3'], $fi_goods_classid_array)) { //处理非秒杀且在规定id里的商品
                                if ($voucher_num == 1) { //最后一件商品取差值
                                    $goods_voucher_price = $temp_voucher_price;
                                } else {
                                    $goods_voucher_price = number_format(($value['goods_pay_price'] / $voucher_limit_price) * $voucher_price, 2);
                                }
                                $goods_list[$key]['goods_pay_price'] = $value['goods_pay_price'] - $goods_voucher_price; //更新实际支付金额
                                $goods_list[$key]['voucher_price'] = $goods_voucher_price; //记录优惠券优惠的部分
                                $voucher_num--; //可用代金券的商品种数-1
                                $temp_voucher_price -= $goods_voucher_price; //每处理一次代金券分摊金额，减去分摊部分
                            }
                        }
                    } else { //id里的可用
                        foreach ($goods_classid_array_vv as $goods_classid_v) {
                            if (in_array($goods_classid_v, $goodsclass_id_array_v)) {
                                array_push($fi_goods_classid_array, $goods_classid_v); //筛选代金券设置规则的商品id，挑选id在设置中的商品id，得到最终可用代金券的goods_id数组
                                $voucher_limit_price += $goods_voucher_list['goods_class_v'][$goods_classid_v];
                            }
                        }
                        $voucher_num = count($fi_goods_classid_array);
                        foreach ($goods_list as $key => $value) {
                            if ($value['promotion_type'] != 10 && in_array($value['gc_id_3'], $fi_goods_classid_array)) { //处理非秒杀且在规定id里的商品
                                if ($voucher_num == 1) { //最后一件商品取差值
                                    $goods_voucher_price = $temp_voucher_price;
                                } else {
                                    $goods_voucher_price = number_format(($value['goods_pay_price'] / $voucher_limit_price) * $voucher_price, 2);
                                }
                                $goods_list[$key]['goods_pay_price'] = $value['goods_pay_price'] - $goods_voucher_price; //更新实际支付金额
                                $goods_list[$key]['voucher_price'] = $goods_voucher_price; //记录优惠券优惠的部分
                                $voucher_num--; //可用代金券的商品种数-1
                                $temp_voucher_price -= $goods_voucher_price; //每处理一次代金券分摊金额，减去分摊部分
                            }
                        }
                    }
                } elseif ($voucher_info['type'] == 2) { //商品2
                    $voucher_price = $voucher_info['voucher_price']; //代金券金额
                    $voucher_limit_price = 0; //符合使用代金券的总金额
                    $temp_voucher_price = $voucher_price;
                    $goods_id_array_v = explode(',', $voucher_info['goods_id']); //代金券信息中商品数组
                    $goods_id_array_vv = array_keys($goods_voucher_list['goods_id_v']); //可能可用代金券的商品数组
                    $fi_goods_id_array = array();
                    if ($voucher_info['is_use'] == 0) { //id里的不能用
                        foreach ($goods_id_array_vv as $goods_id_v) {
                            if (!in_array($goods_id_v, $goods_id_array_v)) {
                                array_push($fi_goods_id_array, $goods_id_v); //排除代金券设置规则的商品id，挑选id不在设置中的商品id，得到最终可用代金券的goods_id数组
                                $voucher_limit_price += $goods_voucher_list['goods_id_v'][$goods_id_v];
                            }
                        }
                        $voucher_num = count($fi_goods_id_array);
                        foreach ($goods_list as $key => $value) {
                            if ($value['promotion_type'] != 10 && in_array($value['goods_id'], $fi_goods_id_array)) { //处理非秒杀且在规定id里的商品
                                if ($voucher_num == 1) { //最后一件商品取差值
                                    $goods_voucher_price = $temp_voucher_price;
                                } else {
                                    $goods_voucher_price = number_format(($value['goods_pay_price'] / $voucher_limit_price) * $voucher_price, 2);
                                }
                                $goods_list[$key]['goods_pay_price'] = $value['goods_pay_price'] - $goods_voucher_price; //更新实际支付金额
                                $goods_list[$key]['voucher_price'] = $goods_voucher_price; //记录优惠券优惠的部分
                                $voucher_num--; //可用代金券的商品种数-1
                                $temp_voucher_price -= $goods_voucher_price; //每处理一次代金券分摊金额，减去分摊部分
                            }
                        }
                    } else { //id里的可用
                        foreach ($goods_id_array_vv as $goods_id_v) {
                            if (in_array($goods_id_v, $goods_id_array_v)) {
                                array_push($fi_goods_id_array, $goods_id_v); //筛选代金券设置规则的商品id，挑选id在设置中的商品id，得到最终可用代金券的goods_id数组
                                $voucher_limit_price += $goods_voucher_list['goods_id_v'][$goods_id_v];
                            }
                        }
                        $voucher_num = count($fi_goods_id_array);
                        foreach ($goods_list as $key => $value) {
                            if ($value['promotion_type'] != 10 && in_array($value['goods_id'], $fi_goods_id_array)) { //处理非秒杀且在规定id里的商品
                                if ($voucher_num == 1) { //最后一件商品取差值
                                    $goods_voucher_price = $temp_voucher_price;
                                } else {
                                    $goods_voucher_price = number_format(($value['goods_pay_price'] / $voucher_limit_price) * $voucher_price, 2);
                                }
                                $goods_list[$key]['goods_pay_price'] = $value['goods_pay_price'] - $goods_voucher_price; //更新实际支付金额
                                $goods_list[$key]['voucher_price'] = $goods_voucher_price; //记录优惠券优惠的部分
                                $voucher_num--; //可用代金券的商品种数-1
                                $temp_voucher_price -= $goods_voucher_price; //每处理一次代金券分摊金额，减去分摊部分
                            }
                        }
                    }
                } else { //全场0或空
                    $voucher_price = $voucher_info['voucher_price']; //代金券金额
                    $voucher_limit_price = $goods_voucher_list['v']; //符合使用代金券的总金额
                    $voucher_num = $goods_voucher_list['v_num']; //符合使用代金券的商品种数
                    $temp_voucher_price = $voucher_price;
                    //遍历商品数据，处理代金券分摊
                    foreach ($goods_list as $key => $value) {
                        if ($value['promotion_type'] != 10) { //处理非秒杀
                            if ($voucher_num == 1) { //最后一件商品取差值
                                $goods_voucher_price = $temp_voucher_price;
                            } else {
                                $goods_voucher_price = number_format(($value['goods_pay_price'] / $voucher_limit_price) * $voucher_price, 2);
                            }
                            $goods_list[$key]['goods_pay_price'] = $value['goods_pay_price'] - $goods_voucher_price; //更新实际支付金额
                            $goods_list[$key]['voucher_price'] = $goods_voucher_price; //记录优惠券优惠的部分
                            $voucher_num--; //可用代金券的商品种数-1
                            $temp_voucher_price -= $goods_voucher_price; //每处理一次代金券分摊金额，减去分摊部分
                        }
                    }
                }
                $goods_list['goods_total_price'] = $goods_list['goods_total_price'] - $voucher_price; //修改订单实际总金额
            }
            //满赠商品
            //mansong_gc_id全场1分类$goods_info['gc_id_3']单品$goods_id
            //根据商品价格，mansong_gc_id，开始时间结束时间，state=2查询满送活动列表
            $sql_mansong = "SELECT spmr.goods_id,sg.goods_name,sg.goods_storage,sg.goods_image,0 AS goods_price,'赠品' AS goods_type,sd.storage_id,sg.goods_commonid,sg.gc_id_3,sg.deliverer_id,sg.commis_rate,sg.goods_serial,sg.goods_barcode,sg.is_cw,sg.points_send,sg.goods_costprice,1 AS goods_num,6 AS is_group_ladder,0 AS voucher_price,0 AS goods_pay_price FROM 718shop_p_mansong spm LEFT JOIN 718shop_p_mansong_rule spmr ON spm.mansong_id=spmr.mansong_id LEFT JOIN 718shop_goods sg ON spmr.goods_id = sg.goods_id LEFT JOIN 718shop_daddress sd ON sg.deliverer_id = sd.address_id WHERE UNIX_TIMESTAMP() BETWEEN spm.start_time AND spm.end_time AND (spm.mansong_gc_id=1) AND spm.state=2 AND spmr.price<=" . $goods_list['goods_total_price'] . " ORDER BY price DESC LIMIT 1";
            $mansong_info = Model()->query($sql_mansong);
            if ($mansong_info[0]['goods_storage'] >= 1) { //赠品库存充足
                $mansong['mansong_id'] = $mansong_info[0]['mansong_id'];
                $mansong['goods_id'] = $mansong_info[0]['goods_id'];
                $mansong['mansong_name'] = $mansong_info[0]['mansong_name'];
                $mansong['goods_image'] = $mansong_info[0]['goods_image'];
                $mansong['goods_name'] = $mansong_info[0]['goods_name'];
                $mansong['goods_num'] = 1;
                $mansong_goods_id = $mansong_info[0]['goods_id'];
                //将赠品数据加入到商品列表中
                array_push($goods_list, $mansong_info[0]);
            }
        } else { //直接购买
            $cart_id = $this->_parseItems($data['cart_id']);
            $goods_id = key($cart_id);
            $quantity = current($cart_id);

            //判断该商品是否存在即买即送活动
            $goods_promotion_info = Model()->table('goods_promotion')->field('goods_id')->where(array('goods_id' => $goods_id, 'promotion_type' => 80))->find();
            if ($goods_promotion_info) { //存在需要判断自提点
                $ziti_info = Model()->table('buy_deliver_goods')->field('goods_id')->where(array('goods_id' => $goods_id, 'ziti_id' => $data['ziti_id']))->find();
                if (!$ziti_info) { //所购买商品不在当前自提点
                    $goods_name = Model()->getfby_goods_id($goods_id, 'goods_name');
                    die(json_encode(array('code' => '200', 'message' => '商品' . $goods_name . '在当前区域内不可售卖', 'data' => []), 320));
                }
            }

            //获取商品目前促销信息
            $sql = "SELECT goods_id1 AS goods_id,goods_commonid,goods_name,hui_discount,goods_price,storage_id,gc_id_3,deliverer_id,goods_storage,goods_image,goods_promotion_id,promotion_type_id,promotion_type,price,member_levels,sort,state,upper_limit,IFNULL( commis_rate_p, commis_rate ) AS commis_rate,goods_serial,goods_barcode,is_cw,points_send,is_vip_price,goods_costprice FROM ((SELECT sg.goods_id AS goods_id1,sg.goods_commonid,sg.goods_name,sg.hui_discount,sg.gc_id_3,sg.deliverer_id,sg.goods_price,sg.goods_storage,sg.goods_image,sd.storage_id,sg.commis_rate,sg.goods_serial,sg.goods_barcode,sg.is_cw,sg.points_send,sgc.is_vip_price,sg.goods_costprice FROM 718shop_goods sg LEFT JOIN 718shop_goods_common sgc ON sg.goods_commonid = sgc.goods_commonid LEFT JOIN 718shop_daddress sd ON sd.address_id = sg.deliverer_id WHERE sg.goods_verify=1 AND sg.goods_state=1 AND sg.is_deleted=0 AND sg.goods_storage> 0 AND sg.goods_id IN (" . $goods_id . ")) b LEFT JOIN (SELECT sgp1.goods_id as goods_id,sgp1.price as price,sgp1.goods_promotion_id,sgp1.promotion_type_id,sgp1.promotion_type,sgp1.member_levels,sgp1.sort,sgp1.state,sgp1.upper_limit,sgp1.commis_rate AS commis_rate_p FROM 718shop_goods_promotion sgp1 JOIN (SELECT a.goods_id,a.price AS sgp2_price,substring_index(goods_promotion_id,',',1) AS goods_promotion_id FROM (SELECT sgp2.goods_id,min(sgp2.price) AS price,group_concat(sgp2.goods_promotion_id ORDER BY sgp2.price,sgp2.goods_promotion_id ASC) AS goods_promotion_id FROM 718shop_goods_promotion sgp2 WHERE " . $xinren_str . " IF (sgp2.end_time> 0,sgp2.end_time> UNIX_TIMESTAMP(),1) AND CASE sgp2.promotion_type WHEN 30 THEN sgp2.member_levels<=".$member_grade." ELSE 1 END AND sgp2.goods_id IN (" . $goods_id . ") GROUP BY sgp2.goods_id) a) AS s3 ON sgp1.goods_id = s3.goods_id AND sgp1.price = s3.sgp2_price AND sgp1.goods_promotion_id = s3.goods_promotion_id WHERE" . $xinren_str1 . " IF (sgp1.end_time> 0,sgp1.end_time> UNIX_TIMESTAMP(),1) AND CASE sgp1.promotion_type WHEN 30 THEN sgp1.member_levels<=".$member_grade." ELSE 1 END GROUP BY goods_id) c ON b.goods_id1=c.goods_id)";
            $goods_info = Model()->query($sql)[0];
            if($goods_info['goods_storage']<$quantity){
                die(json_encode(array('code' => '200', 'message' => '商品' . $goods_info['goods_name'] . '库存不足，请重新购买', 'data' => []), 320));
            }
            $goods_info['goods_num'] = $quantity; //加入购买数量
            $goods_info['goods_image'] = $goods_info['goods_image'];
            switch ($goods_info['promotion_type']) {
                case 10:
                case 20: //10秒杀20折扣
                    //获取当前用户已购该商品的数量
                    // $sql_num = "select sum(goods_num) as goods_num from 718shop_order_goods where goods_id=" . $goods_id . " and promotions_id=" . $goods_info['promotion_type_id'] . " and buyer_id = " . $member_id;
                    // $goods_num_sum = Model()->query($sql_num)[0]['goods_num'];
                $order_info = Model()->table('order,order_goods')->field('goods_num')->join('inner right')->on('order.order_id = order_goods.order_id')->where(array('order_goods.goods_id' =>  $goods_id, 'order_goods.buyer_id' => $member_id, 'order_goods.promotions_id' => $goods_info['promotion_type_id'], 'order.order_state' => array('gt', 0)))->select();
                    if (is_array($order_info)) {
                        $goods_num_sum = 0;
                        foreach ($order_info as $key => $value) {
                            $goods_num_sum += $value['goods_num'];
                        }
                    }
                    if ($goods_info['upper_limit'] > 0 && ($goods_num_sum + $quantity > $goods_info['upper_limit'])) { //已购+本次购买>限购数量
                        die(json_encode(array('code' => '200', 'message' => '商品' . $goods_info['goods_name'] . '购买数量已达到上限', 'data' => []), 320));
                    }
                    $goods_info['goods_price'] = $goods_info['price']; //活动价格
                    $goods_info['is_group_ladder'] = 4;
                    $goods_info['type_name'] = promotion_typeName($goods_info['promotion_type']);
                    $goods_info['xianshi_type'] = $goods_info['promotion_type'] == 10 ? 1 : 2;
                    break;
                case 30: //30会员价
                    if ($goods_info['is_vip_price'] == 1) {
                        $sql_vip = "select goods_promotion_id,promotion_type_id,promotion_type,price,member_levels,sort,state from 718shop_goods_promotion where member_levels<=" . $member_grade. " and goods_id=" . $goods_id . " and promotion_type=30 order by price asc limit 1";
                        $vip_info = Model()->query($sql_vip)[0];
                        $goods_info['goods_promotion_id'] = $vip_info['goods_promotion_id'];
                        $goods_info['promotion_type_id'] = $vip_info['promotion_type_id'];
                        $goods_info['promotion_type'] = $vip_info['promotion_type'];
                        $goods_info['goods_price'] = $vip_info['price']; //会员价格
                        $goods_info['member_levels'] = $vip_info['member_levels'];
                        $goods_info['sort'] = $vip_info['sort'];
                        $goods_info['state'] = $vip_info['state'];
                        $goods_info['is_group_ladder'] = 8; //会员价格标签
                        $goods_info['type_name'] = promotion_typeName($vip_info['promotion_type']);
                    }
                    break;
                case 40: //40新品
                    $goods_info['goods_price'] = $goods_info['price']; //活动价格
                    $goods_info['is_group_ladder'] = 7;
                    $goods_info['type_name'] = promotion_typeName($goods_info['promotion_type']);
                    break;
                case 50: //50新人
                    if (!$member_info) { //非新人返回错误
                        die(json_encode(array('code' => '200', 'message' => '商品' . $goods_info['goods_name'] . '为新用户专享商品，非新用户不可购买', 'data' => []), 320));
                    }
                    $goods_info['goods_price'] = $goods_info['price']; //活动价格
                    $goods_info['is_group_ladder'] = 3;
                    $goods_info['type_name'] = promotion_typeName($goods_info['promotion_type']);
                    break;
                case 60: //60阶梯价
                    $goods_info['is_group_ladder'] = 1;
                    $goods_info['type_name'] = promotion_typeName($goods_info['promotion_type']);
                    break;
                case 70: //70团购
                    $goods_info['is_group_ladder'] = 2;
                    $goods_info['type_name'] = promotion_typeName($goods_info['promotion_type']);
                    break;
                case 80: //80即买即送
                    $goods_info['is_group_ladder'] = 5;
                    $goods_info['type_name'] = promotion_typeName($goods_info['promotion_type']);
                    break;
                case 90: //90进口商品
                    $goods_info['is_group_ladder'] = 9;
                    $goods_info['type_name'] = promotion_typeName($goods_info['promotion_type']);
                    break;
                default: //无活动按原价处理
                    $goods_info['is_group_ladder'] = 0;
                    $goods_info['type_name'] = '普通商品';
                    break;
            }
            //计算商品总价，单价*数量
            $goods_list['goods_total_price'] = $goods_info['goods_price'] * $goods_info['goods_num'];
            if($goods_info['promotion_type']>0){
                //序列化商品当前活动信息
                $goods_info['promotion_info'] = serialize(array('promotion_type' => $goods_info['promotion_type'], 'price' => $goods_info['price'], 'member_levels' => $goods_info['member_levels'], 'upper_limit' => $goods_info['upper_limit'], 'commis_rate' => $goods_info['commis_rate']));
            }
            $goods_list[0] = $goods_info;
            
            $goods_list[0]['goods_pay_price'] = $goods_info['goods_price'] * $goods_info['goods_num'];
            //判断当前商品类型，非秒杀再进行验证代金券
            if ($goods_info['promotion_type'] != 10) {
                //获取代金券数据
                $voucher_t_id = $data['voucher_t_id'];
                $sql_voucher = "SELECT sv.voucher_id,sv.voucher_code,sv.voucher_t_id,sv.voucher_price,sv.voucher_limit,svt.is_use,svt.goods_id,svt.goodsclass_id,svt.type FROM 718shop_voucher sv LEFT JOIN 718shop_voucher_type svt ON sv.voucher_t_id=svt.voucher_tid WHERE sv.voucher_t_id=" . $voucher_t_id . " AND sv.voucher_owner_id=" . $member_id . " AND sv.voucher_state=1 AND UNIX_TIMESTAMP() BETWEEN sv.voucher_start_date AND sv.voucher_end_date and sv.voucher_limit <=" . $goods_list['goods_total_price'] . " LIMIT 1";
                $voucher_info = Model()->query($sql_voucher)[0];
                if ($voucher_info) {
                    $goods_list['goods_total_price'] = $goods_list['goods_total_price'] - $voucher_info['voucher_price']; //修改订单实际总金额
                    $goods_list[0]['goods_pay_price'] -= $voucher_info['voucher_price']; //修改商品实际支付金额
                    $goods_list[0]['voucher_price'] = $voucher_info['voucher_price']; //记录优惠券优惠的部分
                }
            }
            
            //获取满送商品数据
            $sql_mansong = "SELECT spmr.goods_id,sg.goods_name,sg.goods_storage,sg.goods_image,0 AS goods_price,'赠品' AS goods_type,sd.storage_id,sg.goods_commonid,sg.gc_id_3,sg.deliverer_id,sg.commis_rate,sg.goods_serial,sg.goods_barcode,sg.is_cw,sg.points_send,sg.goods_costprice,1 as goods_num,6 as is_group_ladder,0 as voucher_price,0 as goods_pay_price FROM 718shop_p_mansong spm LEFT JOIN 718shop_p_mansong_rule spmr ON spm.mansong_id=spmr.mansong_id LEFT JOIN 718shop_goods sg ON spmr.goods_id = sg.goods_id LEFT JOIN 718shop_daddress sd ON sg.deliverer_id = sd.address_id WHERE UNIX_TIMESTAMP() BETWEEN spm.start_time AND spm.end_time AND (spm.mansong_gc_id=1 OR spm.mansong_gc_id=" . $goods_info['gc_id_3'] . " OR spm.mansong_gc_id=" . $goods_id . ") AND spm.state=2 AND spmr.price<=" . $goods_list['goods_total_price'] . " ORDER BY price DESC LIMIT 1";
            $mansong_info = Model()->query($sql_mansong);
            if ($mansong_info[0]['goods_storage'] >= 1) { //赠品库存充足
                $mansong_info[0]['goods_image'] = $mansong_info[0]['goods_image'];
                $mansong_goods_id = $mansong_info[0]['goods_id'];
                //将赠品数据加入到商品列表中
                array_push($goods_list, $mansong_info[0]);
            }
        }
        try {
            $model = Model('order');
            //开启事务
            $model->beginTransaction();

            //生成订单
            $this->createOrder($goods_list, $voucher_info, $data);
            
            //订单后续
            $this->disposal($buy_items, $voucher_info['voucher_id'], $data['ifcart'], $mansong_goods_id);

            $model->commit();
        } catch (Exception $e) {
            $model->rollback();
            die(json_encode(array('code' => '200', 'message' => $e->getMessage(), 'data' => []), 320));
        }
        return callback(true, 'succ',['order_list'=>$this->order_list]);
    }
    /**
     * 生成订单
     * @param array $input
     * @throws Exception
     * @return array array(支付单sn,订单列表)
     */
    private function createOrder($goods_list, $voucher_info, $data)
    {
        $member_id = $data['member_id'];
        $member_info = Model()->table('member')->field('member_name,share_id,company_id,is_xinren')->where(array('member_id' => $member_id))->find();
        $model_order = Model('order');
        $model_member = Model('member');

        //生成支付单
        $order_pay = array();
        $order_pay['pay_sn'] = 0;  //0表示为生成sn号
        $order_pay['buyer_id'] = $member_id;
        $order_pay_id = $model_order->addOrderPay($order_pay);
        if (!$order_pay_id) {
            throw new Exception('订单保存失败[未生成支付单]');
        }
        $order_sn = $this->makeOrderSn($order_pay_id);
        $pay_sn = $order_sn;
        //更新支付单
        $where = array();
        $where['pay_id'] = $order_pay_id;
        $order_pay = array();
        $order_pay_update['pay_sn'] = $pay_sn;
        $order_pay_update_result = $model_order->editOrderPay($order_pay_update, $where);
        if (!$order_pay_update_result) {
            throw new Exception('订单保存失败[未更新支付单]');
        }

        $order = array(); //订单表数据
        $order_common = array(); //公共表数据
        $order_goods = array(); //订单商品表数据

        //array_column获取每个商品的仓库id,array_unique去重
        $order['storage_id'] = '';
        $storage_info = array_unique(array_column($goods_list, 'storage_id'));
        if (count($storage_info) > 1) { //判断库存种类数是否大于一个，大于一个需要拆单；0总单1分单，2不拆单
            $order['is_zorder'] = 0; //总单
            $order['z_order_sn'] = $order_sn;
        } else { //非大于一，不需要拆单
            $order['is_zorder'] = 2; //不拆
            $order['z_order_sn'] = $order_sn;
            $order['storage_id'] = current($storage_info);
            $order['chai_time'] = time();
        }
        //邮寄地址
        $order['address_you_id'] = $data['address_you_id'];
        $order['by_post'] = $data['by_post'];
        $order['order_sn'] = $order_sn;
        $order['pay_sn'] = $pay_sn;
        $order['store_id'] = 4;
        $order['store_name'] = '物资小店';
        $order['buyer_id'] = $member_id;
        $order['buyer_name'] = $member_info['member_name'];
        //增加分享公司id、分享人id
        $order['share_id'] = $member_info['share_id'] ? $member_info['share_id'] : 0;
        $order['company_id'] = $member_info['company_id'] ? $member_info['company_id'] : 0;
        $order['buyer_email'] = '';
        $order['add_time'] = TIMESTAMP;
        $order['payment_code'] = 'online'; //支付方式
        $order['order_state'] = 10;
        $order['order_amount'] = $goods_list['goods_total_price']; //订单总金额
        $order['shipping_fee'] = 0; //运费
        $order['store_tax_total'] = 0; //税费
        $order['goods_amount'] = $goods_list['goods_total_price']; //商品总金额
        $order['order_from'] = 2;
        $order['is_mode'] =  0; //非跨境商品
        $order['order_type'] = 0; //订单表存商品活动类型无意义
        
        $order_id = $model_order->addOrder($order);
        if (!$order_id) {
            throw new Exception('订单保存失败[未生成订单数据]');
        }
        $order_info = $order;
        $order_info['order_id'] = $order_id;
        $this->order_list = $order_info;

        //更改用户是否为新用户状态
        if ($member_info['is_xinren'] == 1) {
            //若为新用户1，改为老用户2
            $update_array['is_xinren'] = 2;
            $result = $model_member->editMember(array('member_id' => intval($member_id)), $update_array);
            if (!$result) {
                throw new Exception('用户新人状态更改失败！');
            }
        }

        $order_common['order_id'] = $order_id;
        $order_common['store_id'] = 4;
        //买家留言
        $order_common['order_message'] = $data['pay_message'];

        //记录使用的代金券的编码
        if ($voucher_info) {
            $voucher_code['voucher_code'] = $voucher_info['voucher_code'];
            $voucher_code['voucher_price'] = $voucher_info['voucher_price'];
            $order_common['voucher_code'] = serialize([$voucher_code]);
        }

        //记录代金券id
        $order_common['voucher_id'] = $voucher_info['voucher_id'];
        //记录使用的代金券的总金额
        $order_common['voucher_price'] = $voucher_info['voucher_price'];
        //自提地址
        $order_common['reciver_info'] = $data['reciver_info'];
        $order_common['reciver_name'] = $data['true_name'];
        // $order_common['reciver_city_id'] = $data['buy_city_id'];
        //自提点id
        $order_common['reciver_ziti_id'] = $data['ziti_id'];
        // $order_common['reciver_province_id'] = $data['reciver_province_id']; //省
        //详细地址
        $order_common['mall_info'] = $data['mall_info'];
        //阶梯折扣商品ziti时间
        // $order_common['ziti_ladder_time'] = $ziti_ladder_time; 

        $order_id = $model_order->addOrderCommon($order_common);
        if (!$order_id) {
            throw new Exception('订单保存失败[未生成订单扩展数据]');
        }
        //去掉商品总价
        unset($goods_list['goods_total_price']);
        //分摊金额计算在事务开始前处理完
        foreach ($goods_list as $k => $goods_info) {
            $order_goods[$k]['order_id'] = $order_id;
            $order_goods[$k]['goods_id'] = $goods_info['goods_id'];
            $order_goods[$k]['store_id'] = 4;
            $order_goods[$k]['goods_name'] = $goods_info['goods_name'];
            $order_goods[$k]['points_send'] =  $goods_info['points_send'];
            $order_goods[$k]['goods_price'] = $goods_info['goods_price'];
            $order_goods[$k]['goods_num'] = $goods_info['goods_num'];
            $order_goods[$k]['goods_serial'] = $goods_info['goods_serial'];
            $order_goods[$k]['goods_barcode'] = $goods_info['goods_barcode'];
            $order_goods[$k]['deliverer_id'] = $goods_info['deliverer_id'];
            $order_goods[$k]['is_cw'] = $goods_info['is_cw'];
            $order_goods[$k]['commis_rate'] = $goods_info['commis_rate']?$goods_info['commis_rate']:0;
            $order_goods[$k]['goods_image'] = $goods_info['goods_image'];
            $order_goods[$k]['buyer_id'] = $member_id;
            $order_goods[$k]['goods_type'] = $goods_info['is_group_ladder'];
            $order_goods[$k]['promotions_id'] = $goods_info['promotion_type_id'] ? $goods_info['promotion_type_id'] : 0;
            $order_goods[$k]['goods_cost_price'] = $goods_info['goods_costprice'];
            $order_goods[$k]['gc_id'] = $goods_info['gc_id_3'];
            $order_goods[$k]['voucher_price'] = $goods_info['voucher_price']?$goods_info['voucher_price']:0;
            $order_goods[$k]['goods_pay_price'] = $goods_info['goods_pay_price'];
            $order_goods[$k]['promotion_info'] = $goods_info['promotion_info'] ? $goods_info['promotion_info'] : '';
        }
        $insert = $model_order->addOrderGoods($order_goods);
        if (!$insert) {
            throw new Exception('订单保存失败[未生成商品数据]');
        }
    }

    /**
     * 订单后续其它处理
     *
     */
    private function disposal($buy_items, $voucher_id, $ifcart, $mansong_goods_id)
    {
        //变更库存和销量
        $this->changeStorageAndSalenum($buy_items, $ifcart, $mansong_goods_id);

        //更新使用的代金券状态
        $this->editVoucherState($voucher_id);
    }

    /**
     * 变更库存和销量，删除购物车中的商品
     */
    private function changeStorageAndSalenum($buy_items, $ifcart, $mansong_goods_id)
    {
        if ($ifcart) { //购物车商品
            foreach ($buy_items as $cart_id => $quantity) {
                $goods_id = Model()->table('cart')->getfby_cart_id($cart_id, 'goods_id');
                $goods_list[$goods_id] = $quantity;
            }
        } else {
            $goods_list = $buy_items;
        }
        if ($mansong_goods_id) {
            $goods_list[$mansong_goods_id] = 1;
        }
        $model_goods = Model('goods');
        foreach ($goods_list as $goods_id => $quantity) {
            $data = array();
            $data['goods_storage'] = array('exp', 'goods_storage-' . $quantity);
            $data['goods_salenum'] = array('exp', 'goods_salenum+' . $quantity);
            $result = $model_goods->editGoodsById($data, $goods_id);
        }
        if (!$result) {
            throw new Exception('变更商品库存与销量失败');
        }
        if ($ifcart) { //删除购物车中的商品
            $cart_str = implode(',',array_keys($buy_items));
            $sql = "DELETE FROM 718shop_cart WHERE cart_id IN (".$cart_str.")";
            $del = Model()->execute($sql);
            // $del = Model()->table('cart')->where(array('cart_id' => array('in', array_keys($buy_items))))->delete();
            if (!$del) {
                throw new Exception('删除购物车数据失败');
            }
        }
    }

    /**
     * 更新使用的代金券状态
     */
    private function editVoucherState($voucher_id)
    {
        $model_voucher = Model('voucher');
        $result = $model_voucher->editVoucher(array('voucher_state' => 2), array('voucher_id' => $voucher_id));
        if (!$result) {
            throw new Exception('更新代金券状态失败');
        }
    }

    /**
     * 删除购物车商品
     * @param unknown $ifcart
     * @param unknown $cart_ids
     */
    public function delCart($ifcart, $member_id, $cart_ids)
    {
        if (!$ifcart || !is_array($cart_ids)) return;
        $cart_id_str = implode(',', $cart_ids);
        if (preg_match('/^[\d,]+$/', $cart_id_str)) {
            QueueClient::push('delCart', array('buyer_id' => $member_id, 'cart_ids' => $cart_ids));
        }
    }

    /**
     * 得到所购买的id和数量
     */
    private function _parseItems($cart_id)
    {
        //存放所购商品ID和数量组成的键值对
        $buy_items = array();
        if (is_array($cart_id)) {
            foreach ($cart_id as $value) {
                if (preg_match_all('/^(\d{1,10})\|(\d{1,6})$/', $value, $match)) {
                    if (intval($match[2][0]) > 0) {
                        $buy_items[$match[1][0]] = $match[2][0];
                    }
                }
            }
        }
        return $buy_items;
    }

    /**
     * 订单编号生成规则，n(n>=1)个订单表对应一个支付表，
     * 生成订单编号(年取1位 + $pay_id取13位 + 第N个子订单取2位)
     * 1000个会员同一微秒提订单，重复机率为1/100
     * @param $pay_id 支付表自增ID
     * @return string
     */
    public function makeOrderSn($pay_id)
    {
        //记录生成子订单的个数，如果生成多个子订单，该值会累加
        static $num;
        if (empty($num)) {
            $num = 1;
        } else {
            $num++;
        }
        return (date('y', time()) % 9 + 3) . sprintf('%013d', $pay_id) . sprintf('%02d', $num);
    }
}
