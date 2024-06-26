<?php
/**
 * 购买行为
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class buy_1Logic {

    /**
     * 取得商品最新的属性及促销[购物车]
     * @param unknown $cart_list
     */
    public function getGoodsCartList($cart_list) {

        $cart_list = $this->_getOnlineCartList($cart_list);

        //优惠套装
        $this->_getBundlingCartList($cart_list);

        //抢购
        $this->getGroupbuyCartList($cart_list);

        //限时折扣
        $this->getXianshiCartList($cart_list);

        //赠品
        $this->_getGiftCartList($cart_list);

        return $cart_list;

    }
	
	/**
     * 特殊订单站内支付处理
     */
    public function extendInPay($order_list) {
        //处理预定订单
        if ($order_list[0]['order_type'] == 2) {
            $model_order_book = Model('order_book');
            $order_info = $order_list[0];
            $data = array();
            if (!empty($order_info['rcb_amount'])) {
                $data['book_rcb_amount'] = $order_info['rcb_amount'];
            }
            if (!empty($order_info['pd_amount'])) {
                $data['book_pd_amount'] = $order_info['pd_amount'];
            }

            //如果未使用站内余额，返回
            if (empty($order_info['rcb_amount']) && empty($order_info['pd_amount'])) {
                return callback(true);
            }

            if ($order_info['order_state'] == ORDER_STATE_PAY) {
                //使用站内余额即可全部支付，说明支付完成，记录支付时间和支付方式
                $data['book_pay_time'] = TIMESTAMP;
                $data['book_pay_name'] = '站内余额支付';
                //更新预定人数
                $order_goods_info = Model('order')->getOrderGoodsInfo(array('order_id'=>$order_info['order_id']),'goods_id','rec_id asc');
                $update = Model('goods')->editGoods(array('book_buyers'=>array('exp','book_buyers+1')),array('goods_id'=>$order_goods_info['goods_id']));
                if (!$update) {
                    throw new Exception('更新商品预定人数失败');
                }
            }
            $condition = array();
            $condition['book_order_id'] = $order_info['order_id'];
            if (empty($order_info['book_list'][0]['book_pay_time'])) {
                //付定金或全款
                $condition['book_id'] = $order_info['book_list'][0]['book_id'];
            } else {
                //付尾款
                $condition['book_id'] = $order_info['book_list'][1]['book_id'];
            }
            $update = $model_order_book->editOrderBook($data,$condition);
            if (!$update) {
                throw new Exception('更新站内余额失败');
            }
        }
        return callback(true);
    }

    /**
     * 取得商品最新的属性及促销[立即购买]
     * @param int $goods_id
     * @param int $quantity
     * @return array
     */
    public function getGoodsOnlineInfo($goods_id,$quantity) {
        $goods_info = $this->_getGoodsOnlineInfo($goods_id,$quantity);

        //抢购
        $this->getGroupbuyInfo($goods_info);

        //限时折扣
        $this->getXianshiInfo($goods_info,$goods_info['goods_num']);

        //赠品
        $this->_getGoodsGiftList($goods_info);

        return $goods_info;
    }

    /**
     * 商品金额计算(分别对每个商品/优惠套装小计、每个店铺小计)
     * @param unknown $store_cart_list 以店铺ID分组的购物车商品信息
     * @return array
     */
    public function calcCartList($store_cart_list) {
        if (empty($store_cart_list) || !is_array($store_cart_list)) return array($store_cart_list,array(),0);

        //存放每个店铺的商品总金额
        $store_goods_total = array();
        //存放本次下单所有店铺商品总金额
        $order_goods_total = 0;
    
        foreach ($store_cart_list as $store_id => $store_cart) {
            $tmp_amount = 0;
            foreach ($store_cart as $key => $cart_info) {
                $store_cart[$key]['goods_total'] = ncPriceFormat($cart_info['goods_price'] * $cart_info['goods_num']);
                $store_cart[$key]['goods_image_url'] = cthumb($store_cart[$key]['goods_image']);
                $tmp_amount += $store_cart[$key]['goods_total'];
            }
            $store_cart_list[$store_id] = $store_cart;
            $store_goods_total[$store_id] = ncPriceFormat($tmp_amount);
        }
        return array($store_cart_list,$store_goods_total);
    }
 /* liuly
    * 商品金额计算(分别对每类商品/优惠套装小计、每个店铺小计)
    */    //xinzeng
	public function calcCartGCList($store_cart_list) {
        if (empty($store_cart_list) || !is_array($store_cart_list)) return array(array(),0);

        //存放每个店铺的商品总金额
        $store_agc_total = array();
        foreach ($store_cart_list as $store_id => $store_cart) {
            $tmp_amount=0;
		    $store_gc_total=array();
            foreach ($store_cart as $key => $cart_info) {
                if(($cart_info['groupbuy_info']==null)&&$cart_info['bl_id']==0){
                    $gc_id_1=$cart_info['gc_id_1'];
                    $cart_info['goods_total'] = ncPriceFormat($cart_info['goods_price'] * $cart_info['goods_num']);
                    if($store_gc_total[$gc_id_1]){
                        $store_gc_total[$gc_id_1] += $cart_info['goods_total'];
                    }else{
                        $store_gc_total[$gc_id_1]=$cart_info['goods_total'];
                    }
                    $tmp_amount += $store_cart[$key]['goods_total'];
                    $store_gc_total['1']=$tmp_amount;
                }
            }
            $store_agc_total[$store_id] = $store_gc_total;
        }
		return array($store_agc_total);
    }


    /**
     * 计算商品总价格和分类总价格
     */
    public function calcCartList1($store_cart_list) {
        if (empty($store_cart_list) || !is_array($store_cart_list)) return array($store_cart_list,array(),0);
        //存放每个店铺的商品总金额
        $store_class_total = array();
        foreach ($store_cart_list as $store_id => $store_cart) {
            $store_goods_total = array();
            $store_goods_total[1]=0;
            foreach($store_cart as $key =>$cart_info)
            {
                if($cart_info['groupbuy_info']==null){ //抢购商品不参与代金券计算
                    $gc_id_1 = $cart_info['gc_id_1'];
                    if($store_goods_total[$gc_id_1]==''){$store_goods_total[$gc_id_1]=0;}
                    $cart_info['goods_total'] = ncPriceFormat($cart_info['goods_price'] * $cart_info['goods_num']);
                    $store_goods_total[$gc_id_1]+=$cart_info['goods_total']; //分类总价格
                    $store_goods_total[1]+= $cart_info['goods_total'];//商品总价格
                }
            }
            $store_class_total[$store_id]=$store_goods_total;
        }
        return array($store_class_total);
    }
	 /**(goods_class)
     * 取得店铺级优惠 - 跟据商品金额返回每个店铺当前符合的一条活动规则，如果有赠品，则自动追加到购买列表，价格为0
     * @param unknown $store_goods_total 每个店铺的商品金额小计，以店铺ID为下标
     * @return array($premiums_list,$mansong_rule_list) 分别为赠品列表[下标自增]，店铺满送规则列表[店铺ID为下标]
     */   //gai
	public function getMansongRuleCartgcListByTotal($store_goods_total) {
	    if (!C('promotion_allow') || empty($store_goods_total) || !is_array($store_goods_total)) return array(array(),array());

        $model_mansong = Model('p_mansong');
        $model_goods = Model('goods');

        //定义赠品数组，下标为店铺ID
        $premiums_list = array();
        //定义满送活动数组，下标为店铺ID
        $mansong_rule_list = array();

        foreach($store_goods_total as $store_id => $goods_total){
			 $mansong_rule_gclist = array();
			 $premiums_gclist = array();
			 foreach($goods_total as $gc_id_1=>$gc_total){
				$rule_info = $model_mansong->getMansongRuleByStoregcID($store_id,$gc_id_1,$gc_total);
				if (is_array($rule_info) && !empty($rule_info)) {
					//即不减金额，也找不到促销商品时(已下架),此规则无效
					if (empty($rule_info['discount']) && empty($rule_info['mansong_goods_name'])) {
						continue;
					}
					if($rule_info['mansong_gc_id']==$gc_id_1){
						$rule_info['desc'] = $this->_parseMansongRuleDesc($rule_info);
						$rule_info['discount'] = ncPriceFormat($rule_info['discount']);
						//$mansong_rule_list[$store_id] = $rule_info;
						$mansong_rule_gclist[$gc_id_1] = $rule_info;

						//如果赠品在售,有库存,则追加到购买列表
						if (!empty($rule_info['mansong_goods_name']) && !empty($rule_info['goods_storage'])) {
							$data = array();
							$data['goods_id'] = $rule_info['goods_id'];
							$data['goods_name'] = $rule_info['mansong_goods_name'];
							$data['goods_num'] = 1;
							$data['goods_price'] = 0.00;
							$data['goods_image'] = $rule_info['goods_image'];
							$data['goods_image_url'] = cthumb($rule_info['goods_image']);
							$data['goods_storage'] = $rule_info['goods_storage'];
							//$premiums_list[$store_id][] = $data;
							$premiums_gclist[$gc_id_1] = $data;
						}
					}
				}
			  }
$total=0;
            $mansong=array();
            foreach($mansong_rule_gclist as $key =>$mansong_list){
                if($key!=1){
                    $total+=$mansong_list['discount'];
                }
                else{
                    $mansong[$key]=$mansong_list['discount'];
                }
            }
            $mansong[2]=$total;
            if($mansong[2]==$mansong[1]){
                $max=1;
            }else{
                $max=array_search(max($mansong),$mansong);
            }
            $mansong_rule_list=array();
            foreach($mansong_rule_gclist as $key=>$list){

                if($max==1){
                    if($key==$max){
                        $mansong_rule_list[$key]=$list;
                        break;
                    }
                }
                else{
                    if($key!=1){
                        $mansong_rule_list[$key]=$list;


                    }
                }
            }
            $man=array();
            $man[$store_id] =$mansong_rule_list;
            $premiums_list[$store_id] = $premiums_gclist;
       }
        return array($premiums_list,$man);
	}
	 public function cartList($store_mansong_rule_list,$store_class_total){
        foreach ($store_mansong_rule_list as $store_id =>$store_mansong_list){
            $mansong_list=array();
            foreach($store_mansong_list as $key=>$mansong){
                $mansong_list[$key]=$mansong['discount'];
            }
            
            $goods=$store_class_total[$store_id][1];
            foreach ($mansong_list as $key =>$value){
                if($key==1){//全场满减
                    foreach ($store_class_total[$store_id] as $key1=>$class_total){
                        if($key1==1){ //全场代金券减去全场满减的价格
                            $goods=$class_total;
                            $store_class_total[$store_id][$key1]=$class_total-$value;
                        }else {
                            $store_class_total[$store_id][$key1]=ncPriceFormat($class_total-($class_total/$goods)*$value);
                        }
                    }
                }else{ //商品价格减去分类满减的价格
                    foreach ($store_class_total[$store_id] as $key1=>$class_total){
                        if($key1==1||($key!=1&&$key==$key1)){
                            $store_class_total[$store_id][$key1]=$class_total-$value;
                        }
                    }
                }
            }
        }
        
        return $store_class_total;
    }



    /**
     * 取得店铺级优惠 - 跟据商品金额返回每个店铺当前符合的一条活动规则，如果有赠品，则自动追加到购买列表，价格为0
     * @param unknown $store_goods_total 每个店铺的商品金额小计，以店铺ID为下标
     * @return array($premiums_list,$mansong_rule_list) 分别为赠品列表[下标自增]，店铺满送规则列表[店铺ID为下标]
     */
	public function getMansongRuleCartListByTotal($store_goods_total) {
	    if (!C('promotion_allow') || empty($store_goods_total) || !is_array($store_goods_total)) return array(array(),array());

        $model_mansong = Model('p_mansong');
        $model_goods = Model('goods');

        //定义赠品数组，下标为店铺ID
        $premiums_list = array();
        //定义满送活动数组，下标为店铺ID
        $mansong_rule_list = array();

        foreach ($store_goods_total as $store_id => $goods_total) {
            $rule_info = $model_mansong->getMansongRuleByStoreID($store_id,$goods_total);
            if (is_array($rule_info) && !empty($rule_info)) {
                //即不减金额，也找不到促销商品时(已下架),此规则无效
                if (empty($rule_info['discount']) && empty($rule_info['mansong_goods_name'])) {
                    continue;
                }
                $rule_info['desc'] = $this->_parseMansongRuleDesc($rule_info);
                $rule_info['discount'] = ncPriceFormat($rule_info['discount']);
                $mansong_rule_list[$store_id] = $rule_info;
                //如果赠品在售,有库存,则追加到购买列表
                if (!empty($rule_info['mansong_goods_name']) && !empty($rule_info['goods_storage'])) {
                    $data = array();
                    $data['goods_id'] = $rule_info['goods_id'];
                    $data['goods_name'] = $rule_info['mansong_goods_name'];
                    $data['goods_num'] = 1;
                    $data['goods_price'] = 0.00;
                    $data['goods_image'] = $rule_info['goods_image'];
                    $data['goods_image_url'] = cthumb($rule_info['goods_image']);
                    $data['goods_storage'] = $rule_info['goods_storage'];
                    $premiums_list[$store_id][] = $data;
                }
            }
        }
        return array($premiums_list,$mansong_rule_list);
	}

	/**
	 * 重新计算每个店铺最终商品总金额(最初计算金额减去各种优惠/加运费)
	 * @param array $store_goods_total 店铺商品总金额
	 * @param array $preferential_array 店铺优惠活动内容
	 * @param string $preferential_type 优惠类型，目前只有一个 'mansong'
	 * @return array 返回扣除优惠后的店铺商品总金额
	 */
	public function reCalcGoodsTotal($store_goods_total, $preferential_array, $preferential_type) {
	    $deny = empty($store_goods_total) || !is_array($store_goods_total) || empty($preferential_array) || !is_array($preferential_array);
	    if ($deny) return $store_goods_total;
	
	    switch ($preferential_type) {
	    	case 'mansong':
	    	    if (!C('promotion_allow')) return $store_goods_total;
	    	    foreach ($preferential_array as $store_id => $preferential) {
	    	        foreach ($preferential as $gc_id_1 => $rule_info) {
						if (is_array($rule_info) && $rule_info['discount'] > 0) {
							$store_goods_total[$store_id] -= $rule_info['discount'];
						}
					}
	    	    }
	    	    break;
	
	    	case 'voucher':
	    	    if (!C('voucher_allow')) return $store_goods_total;
                foreach ($preferential_array as $store_id => $voucher_info) {
                    foreach ($voucher_info as $key =>$voucher_list){
                        $store_goods_total[$store_id] -= $voucher_list['voucher_price'];
                    }
                }
	    	    break;
	
	    	case 'freight':
	    	    foreach ($preferential_array as $store_id => $freight_total) {
	    	        $store_goods_total[$store_id] += $freight_total;
	    	    }
	    	    break;

            case 'tax':
                foreach ($preferential_array as $store_id => $store_tax) {
                    $store_goods_total[$store_id] += $store_tax;
                }
                break;
	    }
	    return $store_goods_total;
	}

 /**
     * 计算单个模式下所有店铺最终税金
     */
    public function calcStoreTax($store_cart_list) {
         
         //存放每个店铺的商品总金额
        $store_goods_tax_total = array();
        $store_is_mode = array();
        //存放本次下单所有店铺商品总金额
        $order_goods_tax_total = 0;
    
        foreach ($store_cart_list as $store_id => $store_cart) {
            $tmp_amount = 0;
            foreach ($store_cart as $key => $cart_info) {
                $store_cart[$key]['goods_tax_total'] = ncPriceFormat($cart_info['goods_tax']*$cart_info['goods_num']);
                //ncPriceFormat($cart_info['goods_tax']*$cart_info['goods_num']*$cart_info['xianshi_info']['xianshi_price']/$cart_info['xianshi_info']['goods_price']);
                $tmp_amount += $store_cart[$key]['goods_tax_total'];
                $is_mode = $cart_info['is_mode'];
            }
            $store_cart_list[$store_id] = $store_cart;
            $store_goods_tax_total[$store_id] = ncPriceFormat($tmp_amount);
            $store_is_mode = $is_mode;
        }
        return array($store_cart_list,$store_goods_tax_total,$store_is_mode);


    }
    public function getReciverAddr1($voucher_code = array()) {
        $code = array();
        foreach($voucher_code as $key =>$voucher){
            //$i=0;
            if (intval($voucher['voucher_code'])) {
                $code[$key]['voucher_code']=$voucher['voucher_code'];
                $code[$key]['voucher_price']=$voucher['voucher_price'];
            }
        }
        return array(serialize($code));//serialize(string) 序列化 unserializa(string)反序列化
    }

	/**
	 * 取得店铺可用的代金券
	 * @param array $store_goods_total array(店铺ID=>商品总金额)
	 * @return array
	 */
    public function getStoreAvailableVoucherList($store_goods_total, $member_id) {
        if (!C('voucher_allow')) return $store_goods_total;
        $store_class_voucher=array();
        $model_voucher = Model('voucher');
        foreach ($store_goods_total as $store_id => $goods_total) {//第一层解析出来
            $condition = array();
            $voucher_list = array();
            $condition['voucher_store_id'] = $store_id;
            $condition['voucher_owner_id'] = $member_id;
            foreach($goods_total as $key =>$gc_total)//第二层解析出来
            {
                $condition['voucher_gc_id']=$key;
                $voucher_list[$key] = $model_voucher->getCurrentAvailableVoucher($condition,$gc_total);
                $condition1 = array();
                /*if($key==1){
                    //$platform_voucher_list=array();
                    $condition1['voucher_store_id'] = -1;
                    $condition1['voucher_owner_id'] = $member_id;
                    $condition1['voucher_gc_id']=$key;
                    $voucher_list[-1] = $model_voucher->getCurrentAvailableVoucher($condition1,$gc_total);
                }*/
            }
            $store_class_voucher[$store_id]=$voucher_list;
        }
        return $store_class_voucher;
    }

    /**
     * 取得可用的平台红包
     * @param floot $goods_total 总金额 
     * @return array
     */
    public function getStoreAvailableRptList($member_id,$goods_total = 0) {
        if (!C('redpacket_allow')) return array();
        $condition = array();
        $condition['rpacket_owner_id'] = $member_id;
        return Model('redpacket')->getCurrentAvailableRpt($condition,$goods_total);
    }

    /**
     * 验证平台红包有效性
     * @param floot $goods_total 总金额
     * @return array
     */
    public function reParseRptInfo($input_rpt_info,$order_total,$member_id) {
        if (empty($input_rpt_info)) return array();
        $condition = array();
        $condition['rpacket_owner_id'] = $member_id;
        $condition['rpacket_t_id'] = $input_rpt_info['rpacket_t_id'];
        $info = Model('redpacket')->getCurrentAvailableRpt($condition,$order_total);
        if ($info) {
            return $info[$input_rpt_info['rpacket_t_id']];
        } else {
            return array();
        }
    }

    /**
     * 
     * @param array $store_order_total 每个店铺应付总金额(含运费)
     * @param number $rpt_total 红包金额
     * @return array array(每个订单减去红包后的总金额,每个订单使用的红包值)
     */
    public function parseOrderRpt($store_order_total = array(), $rpt_total = 0) {
        if (empty($store_order_total) || $rpt_total <= 0) return array($store_order_total,array());

        //总的红包优惠比例,保留3位小数
        $all_order_total = array_sum($store_order_total);
        $rpt_rate = abs(number_format($rpt_total/$all_order_total,5));
        if ($rpt_rate <= 1) {
            $rpt_rate = floatval(substr($rpt_rate,0,5));
        } else {
            $rpt_rate = 0;
        }
        //每个订单的优惠金额累加保存入 $rpt_sum
        $rpt_sum = 0;
        //存放每个订单使用了多少红包
        $store_rpt_total = array();

        foreach ($store_order_total as $store_id => $order_total) {
            //计算本订单优惠红包金额
            $rpt_value = floor($order_total*$rpt_rate);
            $store_order_total[$store_id] -= $rpt_value;
            $store_rpt_total[$store_id] = $rpt_value;
            $rpt_sum += $rpt_value; 
        }
        //将因舍出小数部分出现的差值补到其中一个订单的实际成交价中
        if ($rpt_total > $rpt_sum) {
            foreach ($store_order_total as $store_id => $order_total) {
                if ($order_total > 0) {
                    $store_order_total[$store_id] -= $rpt_total - $rpt_sum;
                    $store_rpt_total[$store_id] += $rpt_total - $rpt_sum;
                    break;
                }
            }
        }
        return array($store_order_total,$store_rpt_total);
    }

    /**
     * 将店铺红包减去运费的余额追加到店铺总优惠里
     * @param unknown $store_promotion_total
     * @param unknown $store_freight_total
     * @param unknown $store_rpt_total
     */
    public function reCalcStorePromotionTotal($store_promotion_total,$store_freight_total,$store_rpt_total) {
        if (!is_array($store_rpt_total) || empty($store_rpt_total)) return $store_promotion_total;
        foreach ($store_rpt_total as $store_id => $rpt_total) {
            $ptotal = $rpt_total - $store_freight_total[$store_id];
            if ($ptotal > 0) {
                $store_promotion_total[$store_id] += $ptotal;
            }
        }
        return $store_promotion_total;
    }

    /**
     * 验证传过来的代金券是否可用有效，如果无效，直接删除
     * @param array $input_voucher_list 代金券列表
     * @param array $store_goods_total (店铺ID=>商品总金额)
     * @return array
     */
    public function reParseVoucherList($input_voucher_list = array(), $store_goods_total = array(), $member_id) {
        if (empty($input_voucher_list) || !is_array($input_voucher_list)) return array();
        $store_voucher_list = $this->getStoreAvailableVoucherList($store_goods_total, $member_id);
        foreach ($input_voucher_list as $store_id => $voucher_list) {
            $list = $store_voucher_list[$store_id];
            $tmp=$this->getdanvoucherList($list);
            $voucherlist=array();
            foreach ($voucher_list as $key =>$voucher){
                if (is_array($tmp) && isset($tmp[$voucher['voucher_t_id']])) {
                    $voucher['voucher_id'] = $tmp[$voucher['voucher_t_id']]['voucher_id'];
                    $voucher['voucher_code'] = $tmp[$voucher['voucher_t_id']]['voucher_code'];
                    $voucher['voucher_owner_id'] = $tmp[$voucher['voucher_t_id']]['voucher_owner_id'];
                } else {
                    unset($voucher);
                }
                $voucherlist[$key]=$voucher;
            }
            $input_voucher_list[$store_id]=$voucherlist;
        }
        return $input_voucher_list;
    }
    public function getdanvoucherList($tmp){
        $list=array();
        foreach($tmp as $voucher_list){
            foreach($voucher_list as $key =>$voucher){
                $list[$key]=$voucher;
            }
        }
        return $list;
    }

    /**
     * 判断商品是不是限时折扣中，如果购买数量若>=规定的下限，按折扣价格计算,否则按原价计算
     * @param array $goods_info
     * @param number $quantity 购买数量
     */
    public function getXianshiInfo( & $goods_info, $quantity) {
        if (empty($quantity)) $quantity = 1;
        if (!C('promotion_allow') || empty($goods_info['xianshi_info'])) return ;
        $goods_info['xianshi_info']['down_price'] = ncPriceFormat($goods_info['goods_price'] - $goods_info['xianshi_info']['xianshi_price']);
        if ($quantity >= $goods_info['xianshi_info']['lower_limit']) {
            $goods_info['goods_price'] = $goods_info['xianshi_info']['xianshi_price'];
            $goods_info['promotions_id'] = $goods_info['xianshi_info']['xianshi_id'];
            $goods_info['ifxianshi'] = true;
        }
    }

    /**
     * 输出有货到付款时，在线支付和货到付款及每种支付下商品数量和详细列表
     * @param $buy_list 商品列表
     * @return 返回 以支付方式为下标分组的商品列表
     */
    public function getOfflineGoodsPay($buy_list) {
        //以支付方式为下标，存放购买商品
        $buy_goods_list = array();
        $offline_pay = Model('payment')->getPaymentOpenInfo(array('payment_code'=>'offline'));
        if ($offline_pay) {
            //下单里包括平台自营商品并且平台已开启货到付款，则显示货到付款项及对应商品数量,取出支持货到付款的店铺ID组成的数组，目前就一个，DEFAULT_PLATFORM_STORE_ID
            $offline_store_id_array = model('store')->getOwnShopIds();
            foreach ($buy_list as $value) {
                //if (in_array($value['store_id'],$offline_store_id_array)) {
                    $buy_goods_list['offline'][] = $value;
                //} else {
                //    $buy_goods_list['online'][] = $value;
                //}
            }
        }
        return $buy_goods_list;
    }

    /**
     * 计算每个店铺(所有店铺级优惠活动)总共优惠多少金额
     * @param array $store_goods_total 最初店铺商品总金额
     * @param array $store_final_goods_total 去除各种店铺级促销后，最终店铺商品总金额(不含运费)
     * @return array
     */
    public function getStorePromotionTotal($store_goods_total, $store_final_goods_total) {
        if (!is_array($store_goods_total) || !is_array($store_final_goods_total)) return array();
        $store_promotion_total = array();
        foreach ($store_goods_total as $store_id => $goods_total) {
            $store_promotion_total[$store_id] = abs($goods_total - $store_final_goods_total[$store_id]);
        }
        return $store_promotion_total;
    }

    /**
     * 返回需要计算运费的店铺ID组成的数组 和 免运费店铺ID及免运费下限金额描述
     * @param array $store_goods_total 每个店铺的商品金额小计，以店铺ID为下标
     * @return array
     */
    public function getStoreFreightDescList($store_goods_total) {
        if (empty($store_goods_total) || !is_array($store_goods_total)) return array(array(),array());

        //定义返回数组
        $need_calc_sid_array = array();
        $cancel_calc_sid_array = array();

        //如果商品金额未达到免运费设置下线，则需要计算运费
        $condition = array('store_id' => array('in',array_keys($store_goods_total)));
        $store_list = Model('store')->getStoreOnlineList($condition,null,'','store_id,store_free_price');
        foreach ($store_list as $store_info) {
            $limit_price = floatval($store_info['store_free_price']);
            if ($limit_price == 0 || $limit_price > $store_goods_total[$store_info['store_id']]) {
                //需要计算运费
                $need_calc_sid_array[] = $store_info['store_id'];
            } else {
                //返回免运费金额下限
                $cancel_calc_sid_array[$store_info['store_id']]['free_price'] = $limit_price;
                $cancel_calc_sid_array[$store_info['store_id']]['desc'] = sprintf('满%s免运费',$limit_price);
            }
        }
        return array($need_calc_sid_array,$cancel_calc_sid_array);
    }

    /**
     * 取得店铺运费(使用运费模板的商品运费不会计算，但会返回模板信息)
     * 先将免运费的店铺运费置0，然后算出店铺里没使用运费模板的商品运费之和 ，存到iscalced下标中
     * 然后再计算使用运费模板的信息(array(店铺ID=>array(运费模板ID=>购买数量))，放到nocalced下标里
     * @param array $buy_list 购买商品列表
     * @param array $free_freight_sid_list 免运费的店铺ID数组
     */
    public function getStoreFreightList($buy_list = array(), $free_freight_sid_list) {
        //定义返回数组
        $return = array();
        //先将免运费的店铺运费置0(格式:店铺ID=>0)
        $freight_list = array();
        if (!empty($free_freight_sid_list) && is_array($free_freight_sid_list)) {
            foreach ($free_freight_sid_list as $store_id) {
                $freight_list[$store_id] = 0;
            }
        }

        //然后算出店铺里没使用运费模板(优惠套装商品除外)的商品运费之和(格式:店铺ID=>运费)
        //定义数组，存放店铺优惠套装商品运费总额 store_id=>运费
        $store_bl_goods_freight = array();
        foreach ($buy_list as $key => $goods_info) {
            //免运费店铺的商品不需要计算
            if (in_array($goods_info['store_id'], $free_freight_sid_list)) {
                unset($buy_list[$key]);
                continue;
            }
            //优惠套装商品运费另算
            if (intval($goods_info['bl_id'])) {
                unset($buy_list[$key]);
                $store_bl_goods_freight[$goods_info['store_id']] = $goods_info['bl_id'];
                continue;
            }
            if (!intval($goods_info['transport_id']) &&  !in_array($goods_info['store_id'],$free_freight_sid_list)) {
                $freight_list[$goods_info['store_id']] += $goods_info['goods_freight'];
                unset($buy_list[$key]);
            }
        }
        //计算优惠套装商品运费
        if (!empty($store_bl_goods_freight)) {
            $model_bl = Model('p_bundling');
            foreach (array_unique($store_bl_goods_freight) as $store_id => $bl_id) {
                $bl_info = $model_bl->getBundlingInfo(array('bl_id'=>$bl_id));
                if (!empty($bl_info)) {
                    $freight_list[$store_id] += $bl_info['bl_freight'];
                }
            }
        }

        $return['iscalced'] = $freight_list;

        //最后再计算使用运费模板的信息(店铺ID，运费模板ID，购买数量),使用使用相同运费模板的商品数量累加
        $freight_list = array();
        foreach ($buy_list as $goods_info) {
            $freight_list[$goods_info['store_id']][$goods_info['transport_id']] += $goods_info['goods_num'];
        }
        $return['nocalced'] = $freight_list;

        return $return;
    }

    /**
     * 根据地区选择计算出所有店铺最终运费
     * @param array $freight_list 运费信息(店铺ID，运费，运费模板ID，购买数量)
     * @param int $city_id 市级ID
     * @return array 返回店铺ID=>运费
     */
    public function calcStoreFreight($freight_list, $city_id) {
		if (!is_array($freight_list) || empty($freight_list) || empty($city_id)) return;
		//免费和固定运费计算结果
		$return_list = $freight_list['iscalced'];

		//使用运费模板的信息(array(店铺ID=>array(运费模板ID=>购买数量))
		$nocalced_list = $freight_list['nocalced'];

		//然后计算使用运费运费模板的在该$city_id时的运费值
		if (!empty($nocalced_list) && is_array($nocalced_list)) {
		    //如果有商品使用的运费模板，先计算这些商品的运费总金额
            $model_transport = Model('transport');
            foreach ($nocalced_list as $store_id => $value) {
                if (is_array($value)) {
                    foreach ($value as $transport_id => $buy_num) {
                        $freight_total = $model_transport->calc_transport($transport_id, $city_id);
						 if ($freight_total === false) {
							 $return_list[$store_id] = !($freight_total) ? $freight_total : '0';
						 }
						 else{
							if (empty($return_list[$store_id])) {
								$return_list[$store_id] = $freight_total;
							} else {
								$return_list[$store_id] += $freight_total;
							}
						}
                    }
                }
            }
		}
		return $return_list;
    }

   


    /**
     * 追加赠品到下单列表,并更新购买数量
     * @param array $store_cart_list 购买列表
     * @param array $store_premiums_list 赠品列表
     * @param array $store_mansong_rule_list 满即送规则
     */
    public function appendPremiumsToCartList($store_cart_list, $store_premiums_list = array(), $store_mansong_rule_list = array(), $member_id) {
        if (empty($store_cart_list)) return array();

        //处理商品级赠品
        foreach ($store_cart_list as $store_id => $cart_list) {
            foreach ($cart_list as $cart_info) {
                if (empty($cart_info['gift_list'])) continue;
                if (!is_array($store_premiums_list)) $store_premiums_list = array();
                if (!array_key_exists($store_id,$store_premiums_list)) $store_premiums_list[$store_id] = array();
                $zenpin_info = array();
                foreach ($cart_info['gift_list'] as $gift_info) {
                    $zenpin_info['goods_id'] = $gift_info['gift_goodsid'];
                    $zenpin_info['goods_name'] = $gift_info['gift_goodsname'];
                    $zenpin_info['goods_image'] = $gift_info['gift_goodsimage'];
                    $zenpin_info['goods_storage'] = $gift_info['goods_storage'];
                    $zenpin_info['goods_num'] = $cart_info['goods_num'] * $gift_info['gift_amount'];
                    $store_premiums_list[$store_id][] = $zenpin_info;
                }
            }
        }

        //取得每种商品的库存[含赠品]
        $goods_storage_quantity = $this->_getEachGoodsStorageQuantity($store_cart_list,$store_premiums_list);

        //取得每种商品的购买量[不含赠品]
        $goods_buy_quantity = $this->_getEachGoodsBuyQuantity($store_cart_list);
        foreach ($goods_buy_quantity as $goods_id => $quantity) {
            $goods_storage_quantity[$goods_id] -= $quantity;
            if ($goods_storage_quantity[$goods_id] < 0) {
                //商品库存不足，请重购买
                return false;
            }
        }
        //将赠品追加到购买列表
        
        if(is_array($store_premiums_list)) {
            foreach ($store_premiums_list as $store_id => $goods_list) {
                $zp_list = array();
                $gift_desc = '';
                foreach ($goods_list as $goods_info) {
                    //如果没有库存了，则不再送赠品
                    if ($goods_storage_quantity[$goods_info['goods_id']] == 0) {
                        $gift_desc = '，赠品库存不足，未能全部送出 ';
                        continue;
                    }

                    
                    $new_data = array();
                    $new_data['buyer_id'] = $member_id;
                    $new_data['store_id'] = $store_id;
                    $new_data['store_name'] = $store_cart_list[$store_id][0]['store_name'];
                    $new_data['goods_id'] = $goods_info['goods_id'];
                    $new_data['goods_name'] = $goods_info['goods_name'];
                    $new_data['goods_price'] = 0;
                    $new_data['goods_image'] = $goods_info['goods_image'];
                    $new_data['bl_id'] = 0;
                    $new_data['state'] = true;
                    $new_data['storage_state'] = true;
                    $new_data['gc_id'] = 0;
                    $new_data['transport_id'] = 0;
                    $new_data['goods_freight'] = 0;
                    $new_data['goods_vat'] = 0;
                    $new_data['goods_total'] = 0;
                    $new_data['ifzengpin'] = true;

                    //计算赠送数量，有就赠，赠完为止
                    if ($goods_storage_quantity[$goods_info['goods_id']] - $goods_info['goods_num'] >= 0) {
                        $goods_buy_quantity[$goods_info['goods_id']] += $goods_info['goods_num'];
                        $goods_storage_quantity[$goods_info['goods_id']] -= $goods_info['goods_num'];
                        $new_data['goods_num'] = $goods_info['goods_num'];
                    } else {
                        $new_data['goods_num'] = $goods_storage_quantity[$goods_info['goods_id']];
                        $goods_buy_quantity[$goods_info['goods_id']] += $goods_storage_quantity[$goods_info['goods_id']];
                        $goods_storage_quantity[$goods_info['goods_id']] = 0;
                    }
                    if (array_key_exists($goods_info['goods_id'],$zp_list)) {
                        $zp_list[$goods_info['goods_id']]['goods_num'] += $new_data['goods_num'];
                    } else {
                        $zp_list[$goods_info['goods_id']] = $new_data;
                    }
                }
                sort($zp_list);
                $store_cart_list[$store_id] = array_merge($store_cart_list[$store_id],$zp_list);

                $store_mansong_rule_list[$store_id]['desc'] .= $gift_desc;
                $store_mansong_rule_list[$store_id]['desc'] = trim($store_mansong_rule_list[$store_id]['desc'],'，');
            }
        }
        return array($store_cart_list,$goods_buy_quantity,$store_mansong_rule_list);
    }

    /**
     * 充值卡支付,依次循环每个订单
     * 如果充值卡足够就单独支付了该订单，如果不足就暂时冻结，等API支付成功了再彻底扣除
     */
    public function rcbPay($order_list, $input, $buyer_info) {
        $member_id = $buyer_info['member_id'];
        $member_name = $buyer_info['member_name'];

        $available_rcb_amount = floatval($buyer_info['available_rc_balance']);
        if ($available_rcb_amount <= 0) return;

        $model_order = Model('order');
        $model_pd = Model('predeposit');
        foreach ($order_list as $key => $order_info) {

            //货到付款的订单跳过
            if ($order_info['payment_code'] == 'offline') continue;

            $order_amount = floatval($order_info['order_amount']);
            $data_pd = array();
            $data_pd['member_id'] = $member_id;
            $data_pd['member_name'] = $member_name;
            $data_pd['amount'] = $order_info['order_amount'];
            $data_pd['order_sn'] = $order_info['order_sn'];

            if ($available_rcb_amount >= $order_amount) {
                //立即支付，订单支付完成
                $model_pd->changeRcb('order_pay',$data_pd);
                $available_rcb_amount -= $order_amount;

                //记录订单日志(已付款)
                $data = array();
                $data['order_id'] = $order_info['order_id'];
                $data['log_role'] = 'buyer';
                $data['log_msg'] = L('order_log_pay');
                $data['log_orderstate'] = ORDER_STATE_PAY;
                $insert = $model_order->addOrderLog($data);
                if (!$insert) {
                    throw new Exception('记录订单充值卡支付日志出现错误');
                }

                //订单状态 置为已支付
                $data_order = array();
                $order_list[$key]['order_state'] = $data_order['order_state'] = ORDER_STATE_PAY;
                $data_order['payment_time'] = TIMESTAMP;
                $data_order['payment_code'] = 'predeposit';
                $data_order['rcb_amount'] = $order_amount;
                $result = $model_order->editOrder($data_order,array('order_id'=>$order_info['order_id']));
                if (!$result) {
                    throw new Exception('订单更新失败');
                }
                // 发送商家提醒
                $param = array();
                $param['code'] = 'new_order';
                $param['store_id'] = $order_info['store_id'];
                $param['param'] = array(
                        'order_sn' => $order_info['order_sn']
                );
                QueueClient::push('sendStoreMsg', $param);
            } else {
                //暂冻结充值卡,后面还需要 API彻底完成支付
                if ($available_rcb_amount > 0) {
                    $data_pd['amount'] = $available_rcb_amount;
                    $model_pd->changeRcb('order_freeze',$data_pd);
                    //支付金额保存到订单
                    $data_order = array();
                    $order_list[$key]['rcb_amount'] = $data_order['rcb_amount'] = $available_rcb_amount;
                    $result = $model_order->editOrder($data_order,array('order_id'=>$order_info['order_id']));
                    $available_rcb_amount = 0;
                    if (!$result) {
                        throw new Exception('订单更新失败');
                    }
                }
            }
        }
        return $order_list;
    }

    /**
     * 预存款支付,依次循环每个订单
     * 如果预存款足够就单独支付了该订单，如果不足就暂时冻结，等API支付成功了再彻底扣除
     */
    public function pdPay($order_list, $input, $buyer_info) {
        $member_id = $buyer_info['member_id'];
        $member_name = $buyer_info['member_name'];

//         $model_payment = Model('payment');
//         $pd_payment_info = $model_payment->getPaymentOpenInfo(array('payment_code'=>'predeposit'));
//         if (empty($pd_payment_info)) return;

        $available_pd_amount = floatval($buyer_info['available_predeposit']);
        if ($available_pd_amount <= 0) return;

        $model_order = Model('order');
        $model_pd = Model('predeposit');
        foreach ($order_list as $order_info) {

            //货到付款的订单、已经充值卡支付的订单跳过
            if ($order_info['payment_code'] == 'offline') continue;
            if ($order_info['order_state'] == ORDER_STATE_PAY) continue;

            $order_amount = floatval($order_info['order_amount']) - floatval($order_info['rcb_amount']);
            $data_pd = array();
            $data_pd['member_id'] = $member_id;
            $data_pd['member_name'] = $member_name;
            $data_pd['amount'] = $order_amount;
            $data_pd['order_sn'] = $order_info['order_sn'];

            if ($available_pd_amount >= $order_amount) {
                //预存款立即支付，订单支付完成
                $model_pd->changePd('order_pay',$data_pd);
                $available_pd_amount -= $order_amount;

                //支付被冻结的充值卡
                $rcb_amount = floatval($order_info['rcb_amount']);
                if ($rcb_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $member_id;
                    $data_pd['member_name'] = $member_name;
                    $data_pd['amount'] = $rcb_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changeRcb('order_comb_pay',$data_pd);
                }

                //记录订单日志(已付款)
                $data = array();
                $data['order_id'] = $order_info['order_id'];
                $data['log_role'] = 'buyer';
                $data['log_msg'] = L('order_log_pay');
                $data['log_orderstate'] = ORDER_STATE_PAY;
                $insert = $model_order->addOrderLog($data);
                if (!$insert) {
                    throw new Exception('记录订单预存款支付日志出现错误');
                }

                //订单状态 置为已支付
                $data_order = array();
                $data_order['order_state'] = ORDER_STATE_PAY;
                $data_order['payment_time'] = TIMESTAMP;
                $data_order['payment_code'] = 'predeposit';
                $data_order['pd_amount'] = $order_amount;
                $result = $model_order->editOrder($data_order,array('order_id'=>$order_info['order_id']));
                if (!$result) {
                    throw new Exception('订单更新失败');
                }
                // 发送商家提醒
                $param = array();
                $param['code'] = 'new_order';
                $param['store_id'] = $order_info['store_id'];
                $param['param'] = array(
                        'order_sn' => $order_info['order_sn']
                );
                QueueClient::push('sendStoreMsg', $param);
            } else {
                //暂冻结预存款,后面还需要 API彻底完成支付
                if ($available_pd_amount > 0) {
                    $data_pd['amount'] = $available_pd_amount;
                    $model_pd->changePd('order_freeze',$data_pd);
                    //预存款支付金额保存到订单
                    $data_order = array();
                    $data_order['pd_amount'] = $available_pd_amount;
                    $result = $model_order->editOrder($data_order,array('order_id'=>$order_info['order_id']));
                    $available_pd_amount = 0;
                    if (!$result) {
                        throw new Exception('订单更新失败');
                    }
                }
            }
        }
    }

	/**
	 * 生成支付单编号(两位随机 + 从2000-01-01 00:00:00 到现在的秒数+微秒+会员ID%1000)，该值会传给第三方支付接口
	 * 长度 =2位 + 10位 + 3位 + 3位  = 18位
	 * 1000个会员同一微秒提订单，重复机率为1/100
	 * @return string
	 */
	public function makePaySn($member_id) {
		return mt_rand(10,99)
		      . sprintf('%010d',time() - 946656000)
		      . sprintf('%03d', (float) microtime() * 1000)
		      . sprintf('%03d', (int) $member_id % 1000);
	}

	/**
	 * 订单编号生成规则，n(n>=1)个订单表对应一个支付表，
	 * 生成订单编号(年取1位 + $pay_id取13位 + 第N个子订单取2位)
	 * 1000个会员同一微秒提订单，重复机率为1/100
	 * @param $pay_id 支付表自增ID
	 * @return string
	 */
	public function makeOrderSn($pay_id) {
	    //记录生成子订单的个数，如果生成多个子订单，该值会累加
	    static $num;
	    if (empty($num)) {
	        $num = 1;
	    } else {
	        $num ++;
	    }
		return (date('y',time()) % 9+1) . sprintf('%013d', $pay_id) . sprintf('%02d', $num);
	}

	/**
	 * 更新库存与销量
	 *
	 * @param array $buy_items 商品ID => 购买数量
	 */
	public function editGoodsNum($buy_items) {
        foreach ($buy_items as $goods_id => $buy_num) {
        	$data = array('goods_storage'=>array('exp','goods_storage-'.$buy_num),'goods_salenum'=>array('exp','goods_salenum+'.$buy_num));
        	$result = Model('goods')->editGoods($data,array('goods_id'=>$goods_id));
        	if (!$result) throw new Exception(L('cart_step2_submit_fail'));
        }
	}

    /**
     * 取得店铺级活动 - 每个店铺可用的满即送活动规则列表
     * @param unknown $store_id_array 店铺ID数组
     */
    public function getMansongRuleList($store_id_array) {
        if (!C('promotion_allow') || empty($store_id_array) || !is_array($store_id_array)) return array();
        $model_mansong = Model('p_mansong');
        $mansong_rule_list = array();
        foreach ($store_id_array as $store_id) {
            $store_mansong_rule = $model_mansong->getMansongInfoByStoreID($store_id);
            if (!empty($store_mansong_rule['rules']) && is_array($store_mansong_rule['rules'])) {
                foreach ($store_mansong_rule['rules'] as $rule_info) {
                    //如果减金额 或 有赠品(在售且有库存)
                    if (!empty($rule_info['discount']) || (!empty($rule_info['mansong_goods_name']) && !empty($rule_info['goods_storage']))) {
                        $mansong_rule_list[$store_id][] = $this->_parseMansongRuleDesc($rule_info);
                    }
                }
            }
        }
        return $mansong_rule_list;
    }
    /**
     * 取得店铺级活动 - 每个店铺可用的满即送活动规则列表
     * @param unknown $store_id_array 店铺ID数组
     */  //xinzeng
    public function getMansonggcRuleList($store_cart_list) {
        if (!C('promotion_allow') || empty($store_cart_list) || !is_array($store_cart_list)) return array();
        $model_mansong = Model('p_mansong');
		$mansong_rule_list = array();
        foreach ($store_cart_list as $store_id=>$cart_list) {
		    $store_mansong_rule = array();
			foreach($cart_list as $cart_goods_list){
				$gc_id_1=$cart_goods_list['gc_id_1'];
                $mansong_rule = $model_mansong->getMansongInfoByStoregcID($store_id,$gc_id_1);
				$mansong_gc_name=$mansong_rule['mansong_gc_name'];//xin
				if (!empty($mansong_rule['rules']) && is_array($mansong_rule['rules'])&&empty($store_mansong_rule[$gc_id_1])) {
					$store_mansong_rule[$gc_id_1]['mansong_gc_name']= $mansong_gc_name;//xin
					foreach ($mansong_rule['rules'] as $rule_info) {
						//如果减金额 或 有赠品(在售且有库存)
						if (!empty($rule_info['discount']) || (!empty($rule_info['mansong_goods_name']) && !empty($rule_info['goods_storage']))) {
							$store_mansong_rule[$gc_id_1]['rule'][]= $this->_parseMansongRuleDesc($rule_info);
						}
					}
				}
			 }
		     $mansong_rule_list[$store_id]= $store_mansong_rule;
        }
        return $mansong_rule_list;
    }

    /**
     * 取得哪些店铺有满免运费活动
     * @param array $store_id_array 店铺ID数组
     * @return array
     */
    public function getFreeFreightActiveList($store_id_array) {
        if (empty($store_id_array) || !is_array($store_id_array)) return array();
    
        //定义返回数组
        $store_free_freight_active = array();
    
        //如果商品金额未达到免运费设置下线，则需要计算运费
        $condition = array('store_id' => array('in',$store_id_array));
        $store_list = Model('store')->getStoreOnlineList($condition,null,'','store_id,store_free_price');
        foreach ($store_list as $store_info) {
            $limit_price = floatval($store_info['store_free_price']);
            if ($limit_price > 0) {
                $store_free_freight_active[$store_info['store_id']] = sprintf('满%s免运费',$limit_price);
            }
        }
        return $store_free_freight_active;
    }

    /**
     * 取得收货人地址信息
     * @param array $address_info
     * @return array
     */
    public function getReciverAddr($address_info = array()) {
        if (intval($address_info['dlyp_id'])) {
            $reciver_info['phone'] = trim($address_info['dlyp_mobile'].($address_info['dlyp_telephony'] ? ','.$address_info['dlyp_telephony'] : null),',');
            $reciver_info['tel_phone'] = $address_info['dlyp_telephony'];
            $reciver_info['mob_phone'] = $address_info['dlyp_mobile'];
            $reciver_info['address'] = $address_info['dlyp_area_info'].' '.$address_info['dlyp_address'];
            $reciver_info['area'] = $address_info['dlyp_area_info'];
            $reciver_info['street'] = $address_info['dlyp_address'];
            $reciver_info['id_card'] = $address_info['id_card'];
            $reciver_info['dlyp'] = 1;
            $reciver_info = serialize($reciver_info);
            $reciver_name = $address_info['dlyp_address_name'];
        } else {
            $reciver_info['phone'] = trim($address_info['mob_phone'].($address_info['tel_phone'] ? ','.$address_info['tel_phone'] : null),',');
            $reciver_info['mob_phone'] = $address_info['mob_phone'];
            $reciver_info['tel_phone'] = $address_info['tel_phone'];
            $reciver_info['address'] = $address_info['area_info'].' '.$address_info['address'];
            $reciver_info['area'] = $address_info['area_info'];
            $reciver_info['street'] = $address_info['address'];
            $reciver_info['id_card'] = $address_info['id_card'];
            $reciver_info = serialize($reciver_info);
            $reciver_name = $address_info['true_name'];  
        }
        return array($reciver_info, $reciver_name);
    }

    /**
     * 整理发票信息
     * @param array $invoice_info 发票信息数组
     * @return string
     */
    public function createInvoiceData($invoice_info){
        //发票信息
        $inv = array();
        if ($invoice_info['inv_state'] == 1) {
            $inv['类型'] = '普通发票 ';
            $inv['抬头'] = $invoice_info['inv_title_select'] == 'person' ? '个人' : $invoice_info['inv_title'];
            $inv['内容'] = $invoice_info['inv_content'];
        } elseif (!empty($invoice_info)) {
            $inv['单位名称'] = $invoice_info['inv_company'];
            $inv['纳税人识别号'] = $invoice_info['inv_code'];
            $inv['注册地址'] = $invoice_info['inv_reg_addr'];
            $inv['注册电话'] = $invoice_info['inv_reg_phone'];
            $inv['开户银行'] = $invoice_info['inv_reg_bname'];
            $inv['银行账户'] = $invoice_info['inv_reg_baccount'];
            $inv['收票人姓名'] = $invoice_info['inv_rec_name'];
            $inv['收票人手机号'] = $invoice_info['inv_rec_mobphone'];
            $inv['收票人省份'] = $invoice_info['inv_rec_province'];
            $inv['送票地址'] = $invoice_info['inv_goto_addr'];
        }
        return !empty($inv) ? serialize($inv) : serialize(array());
    }
    
    /**
     * 计算本次下单中每个店铺订单是货到付款还是线上支付,店铺ID=>付款方式[online在线支付offline货到付款]
     * @param array $store_id_array 店铺ID数组
     * @param boolean $if_offpay 是否支持货到付款 true/false
     * @param string $pay_name 付款方式 online/offline
     * @return array
     */
    public function getStorePayTypeList($store_id_array, $if_offpay, $pay_name) {
        $store_pay_type_list = array();
        if ($_POST['pay_name'] == 'online') {
            foreach ($store_id_array as $store_id) {
                $store_pay_type_list[$store_id] = 'online';
            }
        } else {
            $offline_pay = Model('payment')->getPaymentOpenInfo(array('payment_code'=>'offline'));
            if ($offline_pay) {
                //下单里包括平台自营商品并且平台已开启货到付款
                $offline_store_id_array = model('store')->getOwnShopIds();
                foreach ($store_id_array as $store_id) {
                    //if (in_array($store_id,$offline_store_id_array)) {
                        $store_pay_type_list[$store_id] = 'offline';
                    //} else {
                    //    $store_pay_type_list[$store_id] = 'online';
                    //}
                }
            }
        }
        return $store_pay_type_list;
    }

    /**
     * 直接购买时返回最新的在售商品信息（需要在售）
     *
     * @param int $goods_id 所购商品ID
     * @param int $quantity 购买数量
     * @return array
     */
    private function _getGoodsOnlineInfo($goods_id,$quantity) {
        //取目前在售商品
        $goods_info = Model('goods')->getGoodsOnlineInfoAndPromotionById($goods_id);
        if(empty($goods_info)){
            return null;
        }
        $new_array = array();
        $new_array['goods_num'] = $goods_info['is_fcode'] ? 1 : $quantity;
        $new_array['goods_id'] = $goods_id;
        $new_array['goods_commonid'] = $goods_info['goods_commonid'];
        $new_array['gc_id'] = $goods_info['gc_id'];
	$new_array['gc_id_1'] = $goods_info['gc_id_1'];  //xinzeng
        $new_array['store_id'] = $goods_info['store_id'];
        $new_array['goods_name'] = $goods_info['goods_name'];
        $new_array['goods_price'] = $goods_info['goods_price'];
        $new_array['store_name'] = $goods_info['store_name'];
        $new_array['goods_image'] = $goods_info['goods_image'];
        $new_array['transport_id'] = $goods_info['transport_id'];
        $new_array['goods_freight'] = $goods_info['goods_freight'];
        $new_array['goods_vat'] = $goods_info['goods_vat'];
        $new_array['goods_storage'] = $goods_info['goods_storage'];
        $new_array['goods_storage_alarm'] = $goods_info['goods_storage_alarm'];
        $new_array['is_fcode'] = $goods_info['is_fcode'];
        $new_array['have_gift'] = $goods_info['have_gift'];
        $new_array['state'] = true;
        $new_array['storage_state'] = intval($goods_info['goods_storage']) < intval($quantity) ? false : true;
        $new_array['groupbuy_info'] = $goods_info['groupbuy_info'];
        $new_array['xianshi_info'] = $goods_info['xianshi_info'];
        $new_array['is_mode'] = $goods_info['is_mode'];
    
        if ($goods_info['xianshi_info']['goods_price'] == 0){
        //if ($goods_info['goods_promotion_type'] == 2){
            $new_array['goods_tax'] = $goods_info['goods_tax'];
        }
            else {
        $new_array['goods_tax'] = $goods_info['goods_tax']*$goods_info['xianshi_info']['xianshi_price']/$goods_info['xianshi_info']['goods_price'];
}
        //填充必要下标，方便后面统一使用购物车方法与模板
        //cart_id=goods_id,优惠套装目前只能进购物车,不能立即购买
        $new_array['cart_id'] = $goods_id;
        $new_array['bl_id'] = 0;

        
        return $new_array;
    }
    
    /**
     * 直接购买时，判断商品是不是正在抢购中，如果是，按抢购价格计算，购买数量若超过抢购规定的上限，则按抢购上限计算
     * @param array $goods_info
     */
    public function getGroupbuyInfo(& $goods_info = array()) {
        if (!C('groupbuy_allow') || empty($goods_info['groupbuy_info'])) return ;
        $groupbuy_info = $goods_info['groupbuy_info'];

        $goods_info['goods_price'] = $groupbuy_info['groupbuy_price'];
        if ($groupbuy_info['upper_limit'] && $goods_info['goods_num'] > $groupbuy_info['upper_limit']) {
            $goods_info['goods_num'] = $groupbuy_info['upper_limit'];
        }
        $goods_info['upper_limit'] = $groupbuy_info['upper_limit'];
        $goods_info['promotions_id'] = $goods_info['groupbuy_id'] = $groupbuy_info['groupbuy_id'];
        $goods_info['ifgroupbuy'] = true;

		//$goods_model=Model('order');
		  $ordergoods=Model()->table('order_goods')->where(array('buyer_id'=>$_SESSION['member_id'],'goods_type'=>2,'promotions_id'=>$groupbuy_info['groupbuy_id']))->sum('goods_num');
		  if(!empty($ordergoods)&&intval($ordergoods)>0)
		  {
		   $tnum=intval($groupbuy_info['upper_limit'])-intval($ordergoods);//-intval($goods_info['goods_num']);
		   if($tnum<=0)
			$goods_info=null;
			//return;
		   else{
			if($goods_info['goods_num']>$tnum){
			 $goods_info['goods_num'] = $tnum;
			}
		   }
		  }
		//end
    }

    /**
     * 取得某商品赠品列表信息
     * @param array $goods_info
     */
    private function _getGoodsGiftList( & $goods_info) {
        if (!$goods_info['have_gift']) return ;
        $gift_list = Model('goods_gift')->getGoodsGiftListByGoodsId($goods_info['goods_id']);
        //取得赠品当前信息，如果未在售踢除，如果在售取出库存
        if (empty($gift_list)) return array();
        $model_goods = Model('goods');
        foreach ($gift_list as $k => $v) {
            $goods_online_info = $model_goods->getGoodsOnlineInfoByID($v['gift_goodsid'],'goods_storage');
            if (empty($goods_online_info)) {
                unset($gift_list[$k]);
            } else {
                $gift_list[$k]['goods_storage'] = $goods_online_info['goods_storage'];
            }
        }
        $goods_info['gift_list'] = $gift_list;
    }


    /**
     * 取商品最新的在售信息
     * @param unknown $cart_list
     * @return array
     */
    private function _getOnlineCartList($cart_list) {
        if (empty($cart_list) || !is_array($cart_list)) return $cart_list;
        //验证商品是否有效
        $goods_id_array = array();
        foreach ($cart_list as $key => $cart_info) {
            if (!intval($cart_info['bl_id'])) {
                $goods_id_array[] = $cart_info['goods_id'];
            }
        }
        $model_goods = Model('goods');
        $goods_online_list = $model_goods->getGoodsOnlineListAndPromotionByIdArray($goods_id_array);
        $goods_online_array = array();
        foreach ($goods_online_list as $goods) {
            $goods_online_array[$goods['goods_id']] = $goods;
        }

        foreach ((array)$cart_list as $key => $cart_info) {
            if (intval($cart_info['bl_id'])) continue;
            $cart_list[$key]['state'] = true;
            $cart_list[$key]['storage_state'] = true;
            if (in_array($cart_info['goods_id'],array_keys($goods_online_array))) {
                $goods_online_info = $goods_online_array[$cart_info['goods_id']];
                $cart_list[$key]['goods_commonid'] = $goods_online_info['goods_commonid'];
                $cart_list[$key]['goods_name'] = $goods_online_info['goods_name'];
                $cart_list[$key]['gc_id'] = $goods_online_info['gc_id'];
                $cart_list[$key]['goods_image'] = $goods_online_info['goods_image'];
                $cart_list[$key]['goods_price'] = $goods_online_info['goods_price'];
                $cart_list[$key]['transport_id'] = $goods_online_info['transport_id'];
                $cart_list[$key]['goods_freight'] = $goods_online_info['goods_freight'];
                $cart_list[$key]['goods_vat'] = $goods_online_info['goods_vat'];
                $cart_list[$key]['goods_storage'] = $goods_online_info['goods_storage'];
                $cart_list[$key]['goods_storage_alarm'] = $goods_online_info['goods_storage_alarm'];
                $cart_list[$key]['is_fcode'] = $goods_online_info['is_fcode'];
                $cart_list[$key]['have_gift'] = $goods_online_info['have_gift'];
                $cart_list[$key]['gc_id_1'] = $goods_online_info['gc_id_1'];
                if ($cart_info['goods_num'] > $goods_online_info['goods_storage']) {
                    $cart_list[$key]['storage_state'] = false;
                }
                $cart_list[$key]['groupbuy_info'] = $goods_online_info['groupbuy_info'];
                $cart_list[$key]['xianshi_info'] = $goods_online_info['xianshi_info'];
            } else {
                //如果商品下架
                $cart_list[$key]['state'] = false;
                $cart_list[$key]['storage_state'] = false;
            }
        }
    
        return $cart_list;
    }

    /**
     *  直接购买时，判断商品是不是正在抢购中，如果是，按抢购价格计算，购买数量若超过抢购规定的上限，则按抢购上限计算
     * @param array $cart_list
     */
    public function getGroupbuyCartList(& $cart_list) {
        if (!C('promotion_allow') || empty($cart_list)) return ;
        $model_goods = Model('goods');
        foreach ($cart_list as $key => $cart_info) {
            if ($cart_info['bl_id'] === '1' || empty($cart_info['groupbuy_info'])) continue;
            $this->getGroupbuyInfo($cart_info);
            $cart_list[$key] = $cart_info;
        }
    }

    /**
     * 批量判断购物车内的商品是不是限时折扣中，如果购买数量若>=规定的下限，按折扣价格计算,否则按原价计算
     * 并标识该商品为限时商品
     * @param array $cart_list
     */
    public function getXianshiCartList(& $cart_list) {
        if (!C('promotion_allow') || empty($cart_list)) return ;
        foreach ($cart_list as $key => $cart_info) {
            if ($cart_info['bl_id'] === '1' || empty($cart_info['xianshi_info'])) continue;
            $this->getXianshiInfo($cart_info, $cart_info['goods_num']);
            $cart_list[$key] = $cart_info;
        }
    }

    /**
     * 取得购物车商品的赠品列表[商品级赠品]
     *
     * @param array $cart_list
     */
    private function _getGiftCartList(& $cart_list) {
        foreach ($cart_list as $k => $cart_info) {
            if ($cart_info['bl_id']) continue;
            $this->_getGoodsGiftList($cart_info);
            $cart_list[$k] = $cart_info;
        }
    }

    /**
     * 取得购买车内组合销售信息以及包含的商品及有效状态
     * @param array $cart_list
     */
    private function _getBundlingCartList(& $cart_list) {
        if (!C('promotion_allow') || empty($cart_list)) return ;
        $model_bl = Model('p_bundling');
        $model_goods = Model('goods');
        foreach ($cart_list as $key => $cart_info) {
            if (!intval($cart_info['bl_id'])) continue;
            $cart_list[$key]['state'] = true;
            $cart_list[$key]['storage_state'] = true;
            $bl_info = $model_bl->getBundlingInfo(array('bl_id'=>$cart_info['bl_id']));
    
            //标志优惠套装是否处于有效状态
            if (empty($bl_info) || !intval($bl_info['bl_state'])) {
                $cart_list[$key]['state'] = false;
            }
    
            //取得优惠套装商品列表
            $cart_list[$key]['bl_goods_list'] = $model_bl->getBundlingGoodsList(array('bl_id'=>$cart_info['bl_id']));
    
            //取最新在售商品信息
            $goods_id_array = array();
            foreach ($cart_list[$key]['bl_goods_list'] as $goods_info) {
                $goods_id_array[] = $goods_info['goods_id'];
            }
            $goods_list = $model_goods->getGoodsOnlineListAndPromotionByIdArray($goods_id_array);
            $goods_online_list = array();
            foreach ($goods_list as $goods_info) {
                $goods_online_list[$goods_info['goods_id']] = $goods_info;
            }
            unset($goods_list);
    
            //使用最新的商品名称、图片,如果一旦有商品下架，则整个套装置置为无效状态
            $total_down_price = 0;
            foreach ($cart_list[$key]['bl_goods_list'] as $k => $goods_info) {
                if (array_key_exists($goods_info['goods_id'],$goods_online_list)) {
                    $goods_online_info = $goods_online_list[$goods_info['goods_id']];
                    //如果库存不足，标识false
                    if ($cart_info['goods_num'] > $goods_online_info['goods_storage']) {
                        $cart_list[$key]['storage_state'] = false;
                    }
                    $cart_list[$key]['bl_goods_list'][$k]['goods_id'] = $goods_online_info['goods_id'];
                    $cart_list[$key]['bl_goods_list'][$k]['goods_commonid'] = $goods_online_info['goods_commonid'];
                    $cart_list[$key]['bl_goods_list'][$k]['store_id'] = $goods_online_info['store_id'];
                    $cart_list[$key]['bl_goods_list'][$k]['goods_name'] = $goods_online_info['goods_name'];
                    $cart_list[$key]['bl_goods_list'][$k]['goods_image'] = $goods_online_info['goods_image'];
                    $cart_list[$key]['bl_goods_list'][$k]['goods_storage'] = $goods_online_info['goods_storage'];
                    $cart_list[$key]['bl_goods_list'][$k]['goods_storage_alarm'] = $goods_online_info['goods_storage_alarm'];
                    $cart_list[$key]['bl_goods_list'][$k]['gc_id'] = $goods_online_info['gc_id'];
                    //每个商品直降多少
                    $total_down_price += $cart_list[$key]['bl_goods_list'][$k]['down_price'] = ncPriceFormat($goods_online_info['goods_price'] - $goods_info['bl_goods_price']);
                } else {
                    //商品已经下架
                    $cart_list[$key]['state'] = false;
                    $cart_list[$key]['storage_state'] = false;
                }
            }
            $cart_list[$key]['down_price'] = ncPriceFormat($total_down_price);
        }
    }

    /**
     * 取得每种商品的库存
     * @param array $store_cart_list 购买列表
     * @param array $store_premiums_list 赠品列表
     * @return array 商品ID=>库存
     */
    private function _getEachGoodsStorageQuantity($store_cart_list, $store_premiums_list = array()) {
        if(empty($store_cart_list) || !is_array($store_cart_list)) return array();
        $goods_storage_quangity = array();
        foreach ($store_cart_list as $store_cart) {
            foreach ($store_cart as $cart_info) {
                if (!intval($cart_info['bl_id'])) {
                    //正常商品
                    $goods_storage_quangity[$cart_info['goods_id']] = $cart_info['goods_storage'];
                } elseif (!empty($cart_info['bl_goods_list']) && is_array($cart_info['bl_goods_list'])) {
                    //优惠套装
                    foreach ($cart_info['bl_goods_list'] as $goods_info) {
                        $goods_storage_quangity[$goods_info['goods_id']] = $goods_info['goods_storage'];
                    }
                }
            }
        }
        //取得赠品商品的库存
        if (is_array($store_premiums_list)) {
            foreach ($store_premiums_list as $store_id => $goods_list) {
                foreach($goods_list as $goods_info) {
                    if (!isset($goods_storage_quangity[$goods_info['goods_id']])) {
                        $goods_storage_quangity[$goods_info['goods_id']] = $goods_info['goods_storage'];
                    }
                }
            }
        }
        return $goods_storage_quangity;
    }
    
    /**
     * 取得每种商品的购买量
     * @param array $store_cart_list 购买列表
     * @return array 商品ID=>购买数量
     */
    private function _getEachGoodsBuyQuantity($store_cart_list) {
        if(empty($store_cart_list) || !is_array($store_cart_list)) return array();
        $goods_buy_quangity = array();
        foreach ($store_cart_list as $store_cart) {
            foreach ($store_cart as $cart_info) {
                if (!intval($cart_info['bl_id'])) {
                    //正常商品
                    $goods_buy_quangity[$cart_info['goods_id']] += $cart_info['goods_num'];
                } elseif (!empty($cart_info['bl_goods_list']) && is_array($cart_info['bl_goods_list'])) {
                    //优惠套装
                    foreach ($cart_info['bl_goods_list'] as $goods_info) {
                        $goods_buy_quangity[$goods_info['goods_id']] += $cart_info['goods_num'];
                    }
                }
            }
        }
        return $goods_buy_quangity;
    }

    /**
     * 得到所购买的id和数量
     *
     */
    private function _parseItems($cart_id) {
        //存放所购商品ID和数量组成的键值对
        $buy_items = array();
        if (is_array($cart_id)) {
            foreach ($cart_id as $value) {
                if (preg_match_all('/^(\d{1,10})\|(\d{1,6})$/', $value, $match)) {
                    $buy_items[$match[1][0]] = $match[2][0];
                }
            }
        }
        return $buy_items;
    }

    /**
     * 拼装单条满即送规则页面描述信息
     * @param array $rule_info 满即送单条规则信息
     * @return string
     */
    private function _parseMansongRuleDesc($rule_info) {
        if (empty($rule_info) || !is_array($rule_info)) return;
        $discount_desc = !empty($rule_info['discount']) ? '减'.$rule_info['discount'] : '';
        $goods_desc = (!empty($rule_info['mansong_goods_name']) && !empty($rule_info['goods_storage'])) ?
        " 送<a href='".urlShop('goods','index',array('goods_id'=>$rule_info['goods_id']))."' title='{$rule_info['mansong_goods_name']}' target='_blank'>[赠品]</a>" : '';
        return sprintf('满%s%s%s',$rule_info['price'],$discount_desc,$goods_desc);
    }

}
