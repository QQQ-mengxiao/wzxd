<?php
/**
 * 订单管理
 **/
defined('In718Shop') or exit('Access Invalid!');
class orderModel extends Model {

    public function __construct(){
        parent::__construct('order');
    }



    /**
     * 取单条订单信息
     *
     * @param unknown_type $condition
     * @param array $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return unknown
     */
    public function getOrderInfo($condition = array(), $extend = array(), $fields = '*', $order = '',$group = '') {
        $order_info = $this->table('order')->field($fields)->where($condition)->group($group)->order($order)->find();
        if (empty($order_info)) {
            return array();
        }
        if (isset($order_info['order_state'])) {
            $order_info['state_desc'] = orderState($order_info);
        }
        if (isset($order_info['payment_code'])) {
            $order_info['payment_name'] = orderPaymentName($order_info['payment_code']);
        }

        //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_info['extend_order_common'] = $this->getOrderCommonInfo(array('order_id'=>$order_info['order_id']));
            $order_info['extend_order_common']['reciver_info'] = unserialize($order_info['extend_order_common']['reciver_info']);
            $order_info['extend_order_common']['invoice_info'] = unserialize($order_info['extend_order_common']['invoice_info']);
            $order_info['extend_order_common']['voucher_code'] = unserialize($order_info['extend_order_common']['voucher_code']);
            $order_info['extend_order_common']['waybill_info'] = unserialize($order_info['extend_order_common']['waybill_info']);
        }

        //追加返回店铺信息
        if (in_array('store',$extend)) {
            $order_info['extend_store'] = Model('store')->getStoreInfo(array('store_id'=>$order_info['store_id']));
        }

        //返回买家信息
        if (in_array('member',$extend)) {
            $order_info['extend_member'] = Model('member')->getMemberInfoByID($order_info['buyer_id']);
        }

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>$order_info['order_id']));
            $order_info['extend_order_goods'] = $order_goods_list;
        }

        return $order_info;
    }
    /**
     * 扶贫API我的-订单详情取单条订单信息
     *
     * @param unknown_type $condition
     * @param array $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return unknown
     */
    public function getFpOrderInfo($condition = array(), $extend = array(), $fields = '*', $order = '',$group = '') {
        $order_info = $this->table('order')->field($fields)->where($condition)->group($group)->order($order)->find();
        if (empty($order_info)) {
            return array();
        }
        if (isset($order_info['order_state'])) {
            $order_info['state_desc'] =$this->orderState($order_info);
        }
        if (isset($order_info['payment_code'])) {
            $order_info['payment_name'] = $this->orderPaymentName($order_info['payment_code']);
        }

        //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_info['extend_order_common'] = $this->getOrderCommonInfo(array('order_id'=>$order_info['order_id']));
            $order_info['extend_order_common']['reciver_info'] = unserialize($order_info['extend_order_common']['reciver_info']);
            $order_info['extend_order_common']['invoice_info'] = unserialize($order_info['extend_order_common']['invoice_info']);
            $order_info['extend_order_common']['voucher_code'] = unserialize($order_info['extend_order_common']['voucher_code']);
            $order_info['extend_order_common']['waybill_info'] = unserialize($order_info['extend_order_common']['waybill_info']);
        }

        //追加返回店铺信息
        // if (in_array('store',$extend)) {
        //     $order_info['extend_store'] = Model('store')->getStoreInfo(array('store_id'=>$order_info['store_id']));
        // }

        //返回买家信息
        if (in_array('member',$extend)) {
            $order_info['extend_member'] = Model('member')->getMemberInfoByID($order_info['buyer_id']);
        }

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>$order_info['order_id']));
			foreach ($order_goods_list as $key=>$value){
                $order_goods_list[$key]['is_group_ladder'] = Model('goods')->getfby_goods_id($value['goods_id'],'is_group_ladder');
            }
            $order_info['extend_order_goods'] = $order_goods_list;
        }

        return $order_info;
    }
    /**
    * 取得订单支付类型文字输出形式
    *
    * @param array $payment_code
    * @return string
    */
    function orderPaymentName($payment_code) {
        return str_replace(
            array('zihpay','offline','online','alipay','tenpay','chinabank','predeposit'),
            array('一卡通支付','货到付款','在线付款','支付宝','财付通','网银在线','站内余额支付'),
            $payment_code);
    }
    /**
    * 取得订单状态文字输出形式
    *
    * @param array $order_info 订单数组
    * @return string $order_state 描述输出
    */
    function orderState($order_info) {
        switch ($order_info['order_state']) {
            case 0:
                $order_state = '订单已取消';
                break;
            case 10:
                $order_state = '订单待付款';
            break;
            case 20:
                $order_state = '买家已付款，等待卖家发货';
                break;
            case 25:
                $order_state = '卖家已发货，货物运输中';
                break;
            case 30:
                $order_state = '卖家已发货，等待买家收货';
                break;
            case 40:
                $order_state = '已收货，交易成功';
            break;
        }
        return $order_state;
    }
    /**
    * 取得订单状态文字输出形式
    *
    * @param array $order_info 订单数组
    * @return string $order_state 描述输出
    */
    function orderState1($order_info) {
        switch ($order_info['order_state']) {
            case 0:
                $order_state = '已取消';
                break;
            case 10:
                $order_state = '待付款';
            break;
            case 20:
                $order_state = '待发货';
                break;
            case 25:
                $order_state = '已发货';
                break;
            case 30:
                $order_state = '待收货';
                break;
            case 40:
                if($order_info['evaluation_state'] == 0){
                    $order_state = '待评价';
                }else{
                    $order_state = '交易成功';
                }
                break;
        }
        return $order_state;
    }
    public function getOrderCommonInfo($condition = array(), $field = '*') {
        return $this->table('order_common')->field($field)->where($condition)->find();
    }

    public function getOrderPayInfo($condition = array(), $master = false) {
        return $this->table('order_pay')->where($condition)->master($master)->find();
    }

    /**
     * 取得支付单列表
     *
     * @param unknown_type $condition
     * @param unknown_type $pagesize
     * @param unknown_type $filed
     * @param unknown_type $order
     * @param string $key 以哪个字段作为下标,这里一般指pay_id
     * @return unknown
     */
    public function getOrderPayList($condition, $pagesize = '', $filed = '*', $order = '', $key = '') {
        return $this->table('order_pay')->field($filed)->where($condition)->order($order)->page($pagesize)->key($key)->select();
    }

    public function getOrderList12($condition,$condition2, $field = '*',$page = 0, $order = 'order.order_id desc',$flag=true, $limit = ''){
        $model = Model();
        $field = 'member.member_id';
        $on = 'order.buyer_id=member.member_id ';
        $model->table('order,member')->field($field);
        $list_not_in_arr = $model->join('right')->on($on)->where($condition)->select();
        $list_not_in = array();
        foreach ($list_not_in_arr as $valuee1){
            foreach ($valuee1 as $valuee2) {
                $list_not_in[]=$valuee2;
            }
        }
        if($flag==true){
            $condition2['member_id '] = array('in', $list_not_in); //
        }else{
            $condition2['member_id '] = array('not in', $list_not_in);
        }
        $model_member = Model('member');
        $member = $model_member ->field('*')->where($condition2)->page(0)->limit(20000)->select();
        $member_list = $model_member ->field('*')->where($condition2)->page($page)->select();
        return array($member_list,$member);
    }

    /**
     * 取得订单列表(未被删除)
     * @param unknown $condition
     * @param string $pagesize
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getNormalOrderList($condition, $pagesize = '', $field = '*', $order = 'order_id desc', $limit = '', $extend = array()){
        $condition['delete_state'] = 0;
        return $this->getOrderList($condition, $pagesize, $field, $order, $limit, $extend);
    }
    /**
     * 扶贫我的-取得订单列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function apiGetOrderList($condition, $field = '*', $order = 'order_id desc', $limit = '', $extend = array(), $master = false){
        $condition['delete_state'] = 0;
        $list = $this->table('order')->field($field)->where($condition)->order($order)->limit($limit)->master($master)->select();
        if (empty($list)) return array();
        $order_list = array();
        foreach ($list as $order) {
            if (isset($order['order_state'])) {
                $order['state_desc'] = $this->orderState1($order);
            }
            // if (isset($order['payment_code'])) {
            //     $order['payment_name'] = orderPaymentName($order['payment_code']);
            // }
            if (!empty($extend)) $order_list[$order['order_id']] = $order;
        }
        if (empty($order_list)) $order_list = $list;

        // //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_common_list = $this->getOrderCommonList(array('order_id'=>array('in',array_keys($order_list))));
            foreach ($order_common_list as $value) {
                $order_list[$value['order_id']]['extend_order_common'] = $value;
                $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
                $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
                $order_list[$value['order_id']]['extend_order_common']['waybill_info'] = @unserialize($value['waybill_info']);
            }
        }
        //追加返回店铺信息
        // if (in_array('store',$extend)) {
        //     $store_id_array = array();
        //     foreach ($order_list as $value) {
        //         if (!in_array($value['store_id'],$store_id_array)) $store_id_array[] = $value['store_id'];
        //     }
        //     $field = '';
        //     $store_list = Model('store')->getStoreList(array('store_id'=>array('in',$store_id_array)),null,'',$field,'');
        //     $store_new_list = array();
        //     foreach ($store_list as $store) {
        //         $store_new_list[$store['store_id']] = $store;
        //     }
        //     foreach ($order_list as $order_id => $order) {
        //         $order_list[$order_id]['extend_store'] = $store_new_list[$order['store_id']];
        //     }
        // }

        // //追加返回买家信息
        // if (in_array('member',$extend)) {
        //     foreach ($order_list as $order_id => $order) {
        //         $order_list[$order_id]['extend_member'] = Model('member')->getMemberInfoByID($order['buyer_id']);
        //     }
        // }

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>array('in',array_keys($order_list))));
            
            if (!empty($order_goods_list)) {
                foreach ($order_goods_list as $key => $value) {
                    $order_list[$value['order_id']]['extend_order_goods'][$key] = $value;
                    // $order_list[$value['order_id']]['extend_order_goods'][$key]['goods_image'] = $this->thumb($order_list[$value['order_id']]['extend_order_goods'][$key], 60);
                    $order_list[$value['order_id']]['extend_order_goods'][$key]['goods_image'] = $this->cthumb($order_list[$value['order_id']]['extend_order_goods'][$key]['goods_image'], 240,$order_list[$value['order_id']]['extend_order_goods'][$key]['store_id']);
                }
                
            } else {
                $order_list[$value['order_id']]['extend_order_goods'] = array();
            }
        }
        return $order_list;
    }

    public function apiGetOrderListPage($member_id, $num_page,$condition)
    {
        $sql = "SELECT * FROM 718shop_order WHERE order_id IN (SELECT order_id FROM 718shop_order WHERE buyer_id = " . $member_id . " AND delete_state = 0 ".$condition.") ORDER BY order_id DESC LIMIT 10 offset " . ($num_page - 1)*10;
        $list = Model()->query($sql);
        // print_r($sql);die;
        if (empty($list)) return array();
        $order_list = array();
        foreach ($list as $order) {
            if (isset($order['order_state'])) {
                $order['state_desc'] = $this->orderState1($order);
            }
            $order_list[$order['order_id']] = $order;
        } 
        if (empty($order_list)) {
            $order_list = $list;
        }

        // //追加返回订单扩展表信息
        $order_common_list = $this->getOrderCommonList(array('order_id' => array('in', array_keys($order_list))));
        foreach ($order_common_list as $value) {
            $order_list[$value['order_id']]['extend_order_common'] = $value;
            $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
            $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
            $order_list[$value['order_id']]['extend_order_common']['waybill_info'] = @unserialize($value['waybill_info']);
        }
        //取商品列表
        $order_goods_list = $this->getOrderGoodsList(array('order_id' => array('in', array_keys($order_list))));
        if (!empty($order_goods_list)) {
            foreach ($order_goods_list as $key => $value) {
                $order_list[$value['order_id']]['extend_order_goods'][$key] = $value;
                // $order_list[$value['order_id']]['extend_order_goods'][$key]['goods_image'] = $this->thumb($order_list[$value['order_id']]['extend_order_goods'][$key], 60);
                $order_list[$value['order_id']]['extend_order_goods'][$key]['goods_image'] = $this->cthumb($order_list[$value['order_id']]['extend_order_goods'][$key]['goods_image'], 240, $order_list[$value['order_id']]['extend_order_goods'][$key]['store_id']);
            }
        } else {
            $order_list[$value['order_id']]['extend_order_goods'] = array();
        }
        return $order_list;
    }
    public function apiGetOrderListStatePage($member_id,$condition)
    {
        $sql = "SELECT * FROM 718shop_order WHERE order_id IN (SELECT order_id FROM 718shop_order WHERE buyer_id = " . $member_id . " AND delete_state = 0 ".$condition.") ORDER BY order_id DESC";
        $list = Model()->query($sql);
        // print_r($sql);die;
        if (empty($list)) return array();
        $order_list = array();
        foreach ($list as $order) {
            if (isset($order['order_state'])) {
                $order['state_desc'] = $this->orderState1($order);
            }
            $order_list[$order['order_id']] = $order;
        } 
        if (empty($order_list)) {
            $order_list = $list;
        }

        // //追加返回订单扩展表信息
        $order_common_list = $this->getOrderCommonList(array('order_id' => array('in', array_keys($order_list))));
        foreach ($order_common_list as $value) {
            $order_list[$value['order_id']]['extend_order_common'] = $value;
            $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
            $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
            $order_list[$value['order_id']]['extend_order_common']['waybill_info'] = @unserialize($value['waybill_info']);
        }
        //取商品列表
        $order_goods_list = $this->getOrderGoodsList(array('order_id' => array('in', array_keys($order_list))));
        if (!empty($order_goods_list)) {
            foreach ($order_goods_list as $key => $value) {
                $order_list[$value['order_id']]['extend_order_goods'][$key] = $value;
                // $order_list[$value['order_id']]['extend_order_goods'][$key]['goods_image'] = $this->thumb($order_list[$value['order_id']]['extend_order_goods'][$key], 60);
                $order_list[$value['order_id']]['extend_order_goods'][$key]['goods_image'] = $this->cthumb($order_list[$value['order_id']]['extend_order_goods'][$key]['goods_image'], 240, $order_list[$value['order_id']]['extend_order_goods'][$key]['store_id']);
            }
        } else {
            $order_list[$value['order_id']]['extend_order_goods'] = array();
        }
        return $order_list;
    }
    
    /**
    * 取得订单商品销售类型文字输出形式
    *
    * @param array $goods_type
    * @return string 描述输出
    */
    function orderGoodsType($goods_type) {
        return str_replace(
            array('1','2','3','4','5'),
            array('','抢购','限时折扣','优惠套装','赠品'),
            $goods_type);
    }
    /**
    * 取得商品缩略图的完整URL路径，接收图片名称与店铺ID
    *
    * @param string $file 图片名称
    * @param string $type 缩略图尺寸类型，值为60,240,360,1280
    * @param mixed $store_id 店铺ID 如果传入，则返回图片完整URL,如果为假，返回系统默认图
    * @return string
    */
    function cthumb($file, $type = '', $store_id = false) {
        $type_array = explode(',_', ltrim('_60,_240,_360,_1280', '_'));
        if (!in_array($type, $type_array)) {
            $type = '240';
        }
        if (empty($file)) {
            return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( $type );
        }
        $search_array = explode(',', '_60,_240,_360,_1280');
        $file = str_ireplace($search_array,'',$file);
        $fname = basename($file);
        // 取店铺ID
        if ($store_id === false || !is_numeric($store_id)) {
            $store_id = substr ( $fname, 0, strpos ( $fname, '_' ) );
        }
        // 本地存储时，增加判断文件是否存在，用默认图代替
        if ( !file_exists(BASE_UPLOAD_PATH . '/' . ATTACH_GOODS . '/' . $store_id . '/' . ($type == '' ? $file : str_ireplace('.', '_' . $type . '.', $file)) )) {
        return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
        }
        $thumb_host = UPLOAD_SITE_URL . '/' . ATTACH_GOODS;
        return $thumb_host . '/' . $store_id . '/' . ($type == '' ? $file : str_ireplace('.', '_' . $type . '.', $file));
    }

    /**
    * 取得商品缩略图的完整URL路径，接收商品信息数组，返回所需的商品缩略图的完整URL
    *
    * @param array $goods 商品信息数组
    * @param string $type 缩略图类型  值为60,240,360,1280
    * @return string
    */
    function thumb($goods = array(), $type = ''){
        $type_array = explode(',_', ltrim(GOODS_IMAGES_EXT, '_'));
        if (!in_array($type, $type_array)) {
            $type = '240';
        }
        if (empty($goods)){
            return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
        }
        if (array_key_exists('apic_cover', $goods)) {
            $goods['goods_image'] = $goods['apic_cover'];
        }
        if (empty($goods['goods_image'])) {
            return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
        }
        $search_array = explode(',', GOODS_IMAGES_EXT);
        $file = str_ireplace($search_array,'',$goods['goods_image']);
        $fname = basename($file);
        //取店铺ID
        if (preg_match('/^(\d+_)/',$fname)){
            $store_id = substr($fname,0,strpos($fname,'_'));
        }else{
            $store_id = $goods['store_id'];
        }
        $file = $type == '' ? $file : str_ireplace('.', '_' . $type . '.', $file);
        if (!file_exists(BASE_UPLOAD_PATH.'/'.ATTACH_GOODS.'/'.$store_id.'/'.$file)){
            return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
        }
        $thumb_host = UPLOAD_SITE_URL.'/'.ATTACH_GOODS;
        return $thumb_host.'/'.$store_id.'/'.$file;
    }
     public function getOrdersum($condition, $pagesize = '', $field = '*'){
        $result = $this->table('order')->field($field)->where($condition)->page($pagesize)->select();
        return  $result;
    }
    /**
     * 取得订单列表(所有)
     * @param unknown $condition
     * @param string $pagesize
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getOrderList($condition, $pagesize = '', $field = '*', $order = 'order_id desc', $limit = '', $extend = array(), $master = false){
        $list = $this->table('order')->field($field)->where($condition)->page($pagesize)->order($order)->limit($limit)->master($master)->select();
        if (empty($list)) return array();
        $order_list = array();
        foreach ($list as $order) {
            if (isset($order['order_state'])) {
                $order['state_desc'] = orderState($order);
            }
            if (isset($order['payment_code'])) {
                $order['payment_name'] = orderPaymentName($order['payment_code']);
            }
        	if (!empty($extend)) $order_list[$order['order_id']] = $order;
        }
        if (empty($order_list)) $order_list = $list;

        //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_common_list = $this->getOrderCommonList(array('order_id'=>array('in',array_keys($order_list))));
            foreach ($order_common_list as $value) {
                $order_list[$value['order_id']]['extend_order_common'] = $value;
                $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
                $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
                $order_list[$value['order_id']]['extend_order_common']['waybill_info'] = @unserialize($value['waybill_info']);
                $order_list[$value['order_id']]['extend_order_common']['voucher_code'] = @unserialize($value['voucher_code']);
            }
        }
        //追加返回店铺信息
        if (in_array('store',$extend)) {
            $store_id_array = array();
            foreach ($order_list as $value) {
            	if (!in_array($value['store_id'],$store_id_array)) $store_id_array[] = $value['store_id'];
            }
            $store_list = Model('store')->getStoreList(array('store_id'=>array('in',$store_id_array)));
            $store_new_list = array();
            foreach ($store_list as $store) {
            	$store_new_list[$store['store_id']] = $store;
            }
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_store'] = $store_new_list[$order['store_id']];
            }
        }

        //追加返回买家信息
        if (in_array('member',$extend)) {
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_member'] = Model('member')->getMemberInfoByID($order['buyer_id']);
            }
        }

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>array('in',array_keys($order_list))));
            if (!empty($order_goods_list)) {
                foreach ($order_goods_list as $value) {
                    $order_list[$value['order_id']]['extend_order_goods'][] = $value;
                }
            } else {
                $order_list[$value['order_id']]['extend_order_goods'] = array();
            }
        }

        //追加跨境商品信息
        // if (in_array('goods_kuajing_d',$extend)) {
        //     //取商品列表
        //     $order_goods_kuajing = Model('goods_kuajing_d')->where(array('order_id'=>array('in',array_keys($order_list))))->select();
        //     if (!empty($$order_goods_kuajing)) {
        //         foreach ($order_goods_kuajing as $value) {
        //             $order_list[$value['order_id']]['extend_goods_kuajing_d'][] = $value;
        //         }
        //     } else {
        //         $order_list[$value['order_id']]['extend_goods_kuajing_d'] = array();
        //     }
        // }

        return $order_list;
    }

 /**
     * 多表联查订单信息 join on 
     * @param unknown $condition
     * @param string $pagesize
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getOrderList2($consignee_name = '',$condition, $pagesize = '', $field = '*', $order = 'order.order_id desc', $limit = '', $extend = array(), $master = false,$type=''){
        $model = Model();
        $field = '*';
        $on = 'order.order_id=order_common.order_id';
        $model->table('order,order_common')->field($field);
        $list = $model->join('inner')->on($on)->where($condition)->page($pagesize)->order("order_common.order_id desc")->limit($limit)->master($master)->select();


        if (empty($list)) return array();
        $order_list = array();
		$arraystates = array(20,30,40);
        foreach ($list as $key=>$order) {
			if($type == 'type'){
				if($order['is_zorder']==0 && in_array($order['order_state'], $arraystates)){
					unset($list[$key]);
					continue;
				}
            }
            if (isset($order['order_state'])) {
                $order['state_desc'] = orderState($order);
            }
            if (isset($order['payment_code'])) {
                $order['payment_name'] = orderPaymentName($order['payment_code']);
            }
            if (!empty($extend)) $order_list[$order['order_id']] = $order;
        }
        if (empty($order_list)) $order_list = $list;


        //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_common_list = $this->getOrderCommonList(array('order_id'=>array('in',array_keys($order_list))));
            foreach ($order_common_list as $value) {
                $order_list[$value['order_id']]['extend_order_common'] = $value;
                $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
                $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
            }
        }
        //追加返回店铺信息
        if (in_array('store',$extend)) {
            $store_id_array = array();
            foreach ($order_list as $value) {
                if (!in_array($value['store_id'],$store_id_array)) $store_id_array[] = $value['store_id'];
            }
            $store_list = Model('store')->getStoreList(array('store_id'=>array('in',$store_id_array)));
            $store_new_list = array();
            foreach ($store_list as $store) {
                $store_new_list[$store['store_id']] = $store;
            }
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_store'] = $store_new_list[$order['store_id']];
            }
        }

        //追加返回买家信息
        if (in_array('member',$extend)) {
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_member'] = Model('member')->getMemberInfoByID($order['buyer_id']);
            }
        }

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>array('in',array_keys($order_list))));
            if (!empty($order_goods_list)) {
                foreach ($order_goods_list as $value) {
                    $order_list[$value['order_id']]['extend_order_goods'][] = $value;
                    //xinzeng 11.1
                    //$order_list[$value['order_id']]['extend_order_goods']['kuajing_info'] = @unserialize($value['kuajing_info']);
                }
            } else {
                $order_list[$value['order_id']]['extend_order_goods'] = array();
            }
        }



        return $order_list;
    }
    public function getOrderList2cw($consignee_name = '',$condition, $pagesize = '', $field = '*', $order = 'order.order_id desc', $limit = '', $extend = array(), $master = false,$type=''){
        $model = Model();
        $field = '*';
        $on = 'order.order_id=order_common.order_id,cw_log.order_id=order.order_id';
        $model->table('order,order_common,cw_log')->field($field);
        $list = $model->join('inner')->on($on)->where($condition)->page($pagesize)->order("order_common.order_id desc")->limit($limit)->master($master)->select();


        if (empty($list)) return array();
        $order_list = array();
        $arraystates = array(20,30,40);
        foreach ($list as $key=>$order) {
            if($type == 'type'){
                if($order['is_zorder']==0 && in_array($order['order_state'], $arraystates)){
                    unset($list[$key]);
                    continue;
                }
            }
            if (isset($order['order_state'])) {
                $order['state_desc'] = orderState($order);
            }
            if (isset($order['payment_code'])) {
                $order['payment_name'] = orderPaymentName($order['payment_code']);
            }
            if (!empty($extend)) $order_list[$order['order_id']] = $order;
        }
        if (empty($order_list)) $order_list = $list;


        //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_common_list = $this->getOrderCommonList(array('order_id'=>array('in',array_keys($order_list))));
            foreach ($order_common_list as $value) {
                $order_list[$value['order_id']]['extend_order_common'] = $value;
                $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
                $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
            }
        }
        //追加返回店铺信息
        if (in_array('store',$extend)) {
            $store_id_array = array();
            foreach ($order_list as $value) {
                if (!in_array($value['store_id'],$store_id_array)) $store_id_array[] = $value['store_id'];
            }
            $store_list = Model('store')->getStoreList(array('store_id'=>array('in',$store_id_array)));
            $store_new_list = array();
            foreach ($store_list as $store) {
                $store_new_list[$store['store_id']] = $store;
            }
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_store'] = $store_new_list[$order['store_id']];
            }
        }

        //追加返回买家信息
        if (in_array('member',$extend)) {
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_member'] = Model('member')->getMemberInfoByID($order['buyer_id']);
            }
        }

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>array('in',array_keys($order_list))));
            if (!empty($order_goods_list)) {
                foreach ($order_goods_list as $value) {
                    $order_list[$value['order_id']]['extend_order_goods'][] = $value;
                    //xinzeng 11.1
                    //$order_list[$value['order_id']]['extend_order_goods']['kuajing_info'] = @unserialize($value['kuajing_info']);
                }
            } else {
                $order_list[$value['order_id']]['extend_order_goods'] = array();
            }
        }



        return $order_list;
    }
 public function getOrderList3($consignee_name = '',$condition, $pagesize = '', $field = '*', $order = 'order.order_id desc', $limit = '', $extend = array(), $master = false){
        $model = Model();
        $field = '*';
        $on = 'order.order_id=order_common.order_id';
        $model->table('order,order_common')->field($field);
        $list = $model->join('inner')->on($on)->where($condition)->page($pagesize)->order("order_common.order_id desc")->limit($limit)->master($master)->select();

        if (empty($list)) return array();
        $order_list = array();
        foreach ($list as $order) {
            if (isset($order['order_state'])) {
                $order['state_desc'] = orderState($order);
            }
            if (isset($order['payment_code'])) {
                $order['payment_name'] = orderPaymentName($order['payment_code']);
            }
            if (!empty($extend)) $order_list[$order['order_id']] = $order;
        }
        if (empty($order_list)) $order_list = $list;



        //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_common_list = $this->getOrderCommonList(array('order_id'=>array('in',array_keys($order_list))));
            foreach ($order_common_list as $value) {
                $order_list[$value['order_id']]['extend_order_common'] = $value;
                $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
                $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
            }
        }
        //追加返回店铺信息
        if (in_array('store',$extend)) {
            $store_id_array = array();
            foreach ($order_list as $value) {
                if (!in_array($value['store_id'],$store_id_array)) $store_id_array[] = $value['store_id'];
            }
            $store_list = Model('store')->getStoreList(array('store_id'=>array('in',$store_id_array)));
            $store_new_list = array();
            foreach ($store_list as $store) {
                $store_new_list[$store['store_id']] = $store;
            }
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_store'] = $store_new_list[$order['store_id']];
            }
        }

        //追加返回买家信息
        if (in_array('member',$extend)) {
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_member'] = Model('member')->getMemberInfoByID($order['buyer_id']);
            }
        }

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>array('in',array_keys($order_list))));
            if (!empty($order_goods_list)) {
                foreach ($order_goods_list as $value) {
                    $order_list[$value['order_id']]['extend_order_goods'][] = $value;
                    //xinzeng 11.1
                    // $order_list[$value['order_id']]['extend_order_goods']['kuajing_info'] = @unserialize($value['kuajing_info']);
                }
            } else {
                $order_list[$value['order_id']]['extend_order_goods'] = array();
            }
        }

        

        return $order_list;
    }
	
	public function getOrderGoodsExportList($condition,$limit,$goods_serial=''){
        if($goods_serial){
            $condition['order_goods.goods_serial'] = array('like','%'.$goods_serial.'%');
        }
        $field = 'order.order_id,order.z_order_sn,order.order_sn,order.add_time,order.payment_time,order.finnshed_time,order.goods_amount,order.order_amount,order.pd_amount,order.rcb_amount,order.shipping_fee,order.store_tax_total,order.buyer_name,order.buyer_id,order_common.reciver_info,order_common.reciver_name,order_common.deliver_explain,order_common.order_message,order_common.daddress_id,order.store_name,order_common.voucher_price,order.payment_code,order_common.shipping_time,order_common.reciver_ziti_id,order_common.order_message,order_common.deliver_explain,order.refund_state,order.refund_amount,order.shipping_code,order.order_type,order_common.voucher_code,order.order_state,order.is_zorder,order_common.ziti_ladder_time';
        $on = 'order.order_id=order_common.order_id,order.order_id=order_goods.order_id,order_goods.goods_id=goods.goods_id';
        $list = Model()->table('order,order_common,order_goods,goods')->field($field)->join('inner')->on($on)->group('order.order_id')->where($condition)->order("order.order_id asc")->limit($limit)->select();

        foreach ($list as $key => $value) {
            //获取上级用户及用户所属公司
            $member_info = Model('member')->getMemberInfo(array('member_id'=>$value['buyer_id']),'share_id');
            if (is_array($member_info) && !empty($member_info['share_id'])) {
                $share_id = $member_info['share_id'];
                $share_info = Model('member')->getMemberInfo(array('member_id'=>$share_id),'member_name, company_id');
                $company_id = $share_info['company_id'];
                if ($company_id != 0) {
                    $company_info = Model()->table('company')->where(array('company_id' => $company_id))->find();
                    $list[$key]['company_name'] = $company_info['company_name'];
                }else{
                    $list[$key]['company_name'] = '';
                }
                $list[$key]['share_name'] = $share_info['member_name'];
            }else
            {
                $list[$key]['share_name'] = '';
                $list[$key]['company_name'] = '';
            }
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>$value['order_id']),'goods_id,goods_name,goods_num,goods_price,goods_pay_price,goods_cost_price,voucher_price,goods_type,goods_serial as order_goods_serial,goods_barcode as order_goods_barcode,deliverer_id as order_goods_deliverer_id');//,goods_spec,gc_id,goods_storage
            if($order_goods_list){
                foreach ($order_goods_list as $k => $v){
                    $goods_info = Model('goods')->getGoodsInfo(array('goods_id'=>$v['goods_id']));
                    $order_goods_list[$k]['goods_spec'] = $goods_info['goods_spec'];
                    $order_goods_list[$k]['gc_id'] = $goods_info['gc_id'];
                    $order_goods_list[$k]['goods_storage'] = $goods_info['goods_storage'];
                    $order_goods_list[$k]['goods_weight'] = $goods_info['goods_weight'];
                    $order_goods_list[$k]['goods_serial'] = $goods_info['goods_serial'];
                    $order_goods_list[$k]['goods_barcode'] = $goods_info['goods_barcode'];
                    $order_goods_list[$k]['deliverer_id'] = $goods_info['deliverer_id'];
                    //增加佣金比例
                    $order_goods_list[$k]['commis_rate'] = $goods_info['commis_rate'];
                }
            }
            $list[$key]['extend_order_goods'] = $order_goods_list;
            $list[$key]['order_goods_count'] = count($order_goods_list);
        }
        return $list;
    }

    /**
     * 取得(买/卖家)订单某个数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id   买家ID、店铺ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount，分别取相应数量缓存，只许传入一个
     * @return array
     */
    public function getOrderCountCache($type, $id, $key) {
        if (!C('cache_open')) return array();
        $type = 'ordercount'.$type;
        $ins = Cache::getInstance('cacheredis');
        $order_info = $ins->hget($id,$type,$key);
        return !is_array($order_info) ? array($key => $order_info) : $order_info;
    }

    /**
     * 设置(买/卖家)订单某个数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id 买家ID、店铺ID
     * @param array $data
     */
    public function editOrderCountCache($type, $id, $data) {
        if (!C('cache_open') || empty($type) || !intval($id) || !is_array($data)) return ;
        $ins = Cache::getInstance('cacheredis');
        $type = 'ordercount'.$type;
        $ins->hset($id,$type,$data);
    }
    
    /**
     * 取得买卖家订单数量某个缓存
     * @param string $type $type 买/卖家标志，允许传入 buyer、store
     * @param int $id 买家ID、店铺ID
     * @param string $key 允许传入  NewCount、PayCount、SendCount、EvalCount，分别取相应数量缓存，只许传入一个
     * @return int
     */
    public function getOrderCountByID($type, $id, $key) {
        $cache_info = $this->getOrderCountCache($type, $id, $key);
        
        if (is_string($cache_info[$key])) {
            //从缓存中取得
            $count = $cache_info[$key];
        } else {
            //从数据库中取得
            $field = $type == 'buyer' ? 'buyer_id' : 'store_id';
            $condition = array($field => $id);
            $func = 'getOrderState'.$key;
            $count = $this->$func($condition);
            $this->editOrderCountCache($type,$id,array($key => $count));
        }
        return $count;
    }

    /**
     * 删除(买/卖家)订单全部数量缓存
     * @param string $type 买/卖家标志，允许传入 buyer、store
     * @param int $id   买家ID、店铺ID
     * @return bool
     */
    public function delOrderCountCache($type, $id) {
        if (!C('cache_open')) return true;
        $ins = Cache::getInstance('cacheredis');
        $type = 'ordercount'.$type;
        return $ins->hdel($id,$type);
    }

    /**
     * 待付款订单数量
     * @param unknown $condition
     */
    public function getOrderStateNewCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_NEW;
        return $this->getOrderCount($condition);
    }

    /**
     * 待发货订单数量
     * @param unknown $condition
     */
    public function getOrderStatePayCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_PAY;
        return $this->getOrderCount($condition);
    }

    /**
     * 待收货订单数量
     * @param unknown $condition
     */
    public function getOrderStateSendCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_SEND;
        return $this->getOrderCount($condition);
    }

    /**
     * 待评价订单数量
     * @param unknown $condition
     */
    public function getOrderStateEvalCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['evaluation_state'] = 0;
        return $this->getOrderCount($condition);
    }

    /**
     * 交易中的订单数量
     * @param unknown $condition
     */
    public function getOrderStateTradeCount($condition = array()) {
        $condition['order_state'] = array(array('neq',ORDER_STATE_CANCEL),array('neq',ORDER_STATE_SUCCESS),'and');
        return $this->getOrderCount($condition);
    }

    /**
     * 取得订单数量
     * @param unknown $condition
     */
    public function getOrderCount($condition) {
        $condition['delete_state'] = 0;
        return $this->table('order')->where($condition)->count();
    }

    /**
     * 取得订单商品表详细信息
     * @param unknown $condition
     * @param string $fields
     * @param string $order
     */
    public function getOrderGoodsInfo($condition = array(), $fields = '*', $order = '') {
        return $this->table('order_goods')->where($condition)->field($fields)->order($order)->find();
    }

    /**
     * 取得订单商品表列表
     * @param unknown $condition
     * @param string $fields
     * @param string $limit
     * @param string $page
     * @param string $order
     * @param string $group
     * @param string $key
     */
    public function getOrderGoodsList($condition = array(), $fields = '*', $limit = null, $page = null, $order = 'goods_type asc , rec_id desc', $group = null, $key = null)
   {
        return $this->table('order_goods')->field($fields)->where($condition)->limit($limit)->order($order)->group($group)->key($key)->page($page)->select();
    } 
    // public function getOrderGoodsList($condition = array(), $fields = '*', $limit = null, $page = null, $order = 'rec_id desc', $group = null, $key = null) {
    //     return $this->table('order_goods')->field($fields)->where($condition)->limit($limit)->order($order)->group($group)->key($key)->page($page)->select();
    // }

    /**
     * 取得订单扩展表列表
     * @param unknown $condition
     * @param string $fields
     * @param string $limit
     */
    public function getOrderCommonList($condition = array(), $fields = '*', $order = '', $limit = null) {
        return $this->table('order_common')->field($fields)->where($condition)->order($order)->limit($limit)->select();
    }

    /**
     * 插入订单支付表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderPay($data) {
        return $this->table('order_pay')->insert($data);
    }

    /**
     * 插入订单表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrder($data) {
        $insert = $this->table('order')->insert($data);
        if ($insert) {
            //更新缓存
            QueueClient::push('delOrderCountCache',array('buyer_id'=>$data['buyer_id'],'store_id'=>$data['store_id']));
        }
        return $insert;
    }

    /**
     * 插入订单扩展表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderCommon($data) {
        return $this->table('order_common')->insert($data);
    }

    /**
     * 插入订单扩展表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderGoods($data) {
        return $this->table('order_goods')->insertAll($data);
    }

	/**
	 * 添加订单日志
	 */
	public function addOrderLog($data) {
	    $data['log_role'] = str_replace(array('buyer','seller','system','admin'),array('买家','商家','系统','管理员'), $data['log_role']);
	    $data['log_time'] = TIMESTAMP;
	    return $this->table('order_log')->insert($data);
	}

	/**
	 * 更改订单信息
	 *
	 * @param unknown_type $data
	 * @param unknown_type $condition
	 */
	public function editOrder($data,$condition,$limit = '') {
		$update = $this->table('order')->where($condition)->limit($limit)->update($data);
		if ($update) {
		    //更新缓存
		    QueueClient::push('delOrderCountCache',$condition);
		}
		return $update;
	}

	/**
	 * 更改订单信息
	 *
	 * @param unknown_type $data
	 * @param unknown_type $condition
	 */
	public function editOrderCommon($data,$condition) {
	    return $this->table('order_common')->where($condition)->update($data);
	}

	/**
	 * 更改订单信息
	 *
	 * @param unknown_type $data
	 * @param unknown_type $condition
	 */
	public function editOrderGoods($data,$condition) {
	    return $this->table('order_goods')->where($condition)->update($data);
	}

	/**
	 * 更改订单支付信息
	 *
	 * @param unknown_type $data
	 * @param unknown_type $condition
	 */
	public function editOrderPay($data,$condition) {
		return $this->table('order_pay')->where($condition)->update($data);
	}

	/**
	 * 订单操作历史列表
	 * @param unknown $order_id
	 * @return Ambigous <multitype:, unknown>
	 */
    public function getOrderLogList($condition) {
        return $this->table('order_log')->where($condition)->select();
    }

    /**
     * 取得单条订单操作记录
     * @param unknown $condition
     * @param string $order
     */
    public function getOrderLogInfo($condition = array(), $order = '') {
        return $this->table('order_log')->where($condition)->order($order)->find();
    }

    /**
     * 返回是否允许某些操作
     * @param unknown $operate
     * @param unknown $order_info
     */
    public function getOrderOperateState($operate,$order_info){
        if (!is_array($order_info) || empty($order_info)) return false;

        switch ($operate) {

            //买家取消订单
        	case 'buyer_cancel':
        	   $state = ($order_info['order_state'] == 10) ||
        	       ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == 20);
        	   break;

    	   //申请退款
    	   case 'refund_cancel':
    	       $state = $order_info['refund'] == 1 && !intval($order_info['lock_state']);
    	       break;

    	   //商家取消订单
    	   case 'store_cancel':
    	       $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
    	       ($order_info['payment_code'] == 'offline' &&
    	       in_array($order_info['order_state'],array(ORDER_STATE_PAY,ORDER_STATE_SEND)));
    	       break;

           //平台取消订单
           case 'system_cancel':
               $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
               ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
               break;

           //平台收款
           case 'system_receive_pay':
               $state = $order_info['order_state'] == ORDER_STATE_NEW && $order_info['payment_code'] == 'online';
               break;

	       //买家投诉
	       case 'complain':
	           $state = in_array($order_info['order_state'],array(ORDER_STATE_PAY,ORDER_STATE_SEND)) ||
	               intval($order_info['finnshed_time']) > (TIMESTAMP - C('complain_time_limit'));
	           break;

	       case 'payment':
	           $state = $order_info['order_state'] == ORDER_STATE_NEW && $order_info['payment_code'] == 'online';
	           break;

            //调整运费
        	case 'modify_price':
        	    $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
        	       ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
        	    $state = floatval($order_info['shipping_fee']) > 0 && $state;
        	   break;
	        //调整商品价格
        	case 'spay_price':
        	    $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
        	       ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
				   $state = floatval($order_info['goods_amount']) > 0 && $state;
        	   break;

        	//发货
        	case 'send':
        	    $state = !$order_info['lock_state'] && $order_info['order_state'] == ORDER_STATE_PAY;
        	    break;

        	//收货
    	    case 'receive':
    	        $state = !$order_info['lock_state'] && $order_info['order_state'] == ORDER_STATE_SEND;
                //增加邮寄订单运输中状态判断
                if ($order_info['by_post'] == '2') {
                    $state = !$order_info['lock_state'] && in_array($order_info['order_state'],array(25,30));
                }
    	        break;

    	    //评价
    	    case 'evaluation':
    	        $state = !$order_info['lock_state'] && !$order_info['evaluation_state'] && $order_info['order_state'] == ORDER_STATE_SUCCESS;
    	        break;

        	//锁定
        	case 'lock':
        	    $state = intval($order_info['lock_state']) ? true : false;
        	    break;

        	//快递跟踪
        	case 'deliver':
        	    $state = !empty($order_info['shipping_code']) && in_array($order_info['order_state'],array(ORDER_STATE_SEND,ORDER_STATE_SUCCESS));
        	    break;

        	//放入回收站
        	case 'delete':
        	    $state = in_array($order_info['order_state'], array(ORDER_STATE_CANCEL,ORDER_STATE_SUCCESS)) && $order_info['delete_state'] == 0;
        	    break;

        	//永久删除、从回收站还原
        	case 'drop':
        	case 'restore':
        	    $state = in_array($order_info['order_state'], array(ORDER_STATE_CANCEL,ORDER_STATE_SUCCESS)) && $order_info['delete_state'] == 1;
        	    break;

        	//分享
        	case 'share':
        	    $state = true;
        	    break;

        }
        return $state;

    }
    
    /**
     * 联查订单表订单商品表
     *
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     * @return array
     */
    public function getOrderAndOrderGoodsList($condition, $field = '*', $page = 0, $order = 'rec_id desc') {
        return $this->table('order_goods,order')->join('inner')->on('order_goods.order_id=order.order_id')->where($condition)->field($field)->page($page)->order($order)->select();
    }
    
    /**
     * 订单销售记录 订单状态为20、30、40时
     * @param unknown $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getOrderAndOrderGoodsSalesRecordList($condition, $field="*", $page = 0, $order = 'rec_id desc') {
        $condition['order_state'] = array('in', array(ORDER_STATE_PAY, ORDER_STATE_SEND, ORDER_STATE_SUCCESS));
        return $this->getOrderAndOrderGoodsList($condition, $field, $page, $order);
    }

    /**
     * 导出子订单新
     */
    public function getSubOrderInfo($condition){
        $columns = " order1.order_sn,order1.z_order_sn,order_goods.goods_name,SUBSTRING_INDEX(SUBSTRING_INDEX(goods.goods_spec,':\"',-1),'\";',1) AS goods_spec,gc1.gc_name AS gc1_name,gc.gc_name,order_goods.goods_num,daddress.seller_name,order_common.reciver_name,SUBSTRING(order_common.reciver_info,INSTR(order_common.reciver_info,'s:11:\"')+6,11)mobile,ziti_address.seller_name as ziti_address,order_common.mall_info,order_goods.goods_id,order1.buyer_name,order1.add_time,order1.payment_time,order1.finnshed_time,order1.ruku_time,order_common.ziti_ladder_time,order_goods.goods_serial,order_goods.goods_barcode,order_goods.goods_price,order_goods.goods_cost_price,order_goods.goods_cost_price*order_goods.goods_num AS all_goods_cost_price,order_goods.goods_price*order_goods.goods_num AS all_goods_price,order_goods.voucher_price,order_goods.goods_pay_price,order1.order_amount,order1.payment_code,order_common.order_message,order_common.deliver_explain,'' AS transaction_number,order1.order_state,order1.refund_state AS order_refund_state,refund_return.refund_amount,refund_return.admin_time,refund_return.seller_message,refund_return.admin_message,refund_return.reason_info,refund_return.buyer_message,refund_return.seller_state,refund_return.refund_state,order_goods.goods_type,voucher.voucher_title,member.member_name,order1.company_id,order_goods.commis_rate ";
        
        $tables = " 718shop_order order1 INNER JOIN 718shop_order_common order_common ON order1.order_id = order_common.order_id INNER JOIN 718shop_order_goods order_goods ON order1.order_id = order_goods.order_id LEFT JOIN 718shop_goods goods ON order_goods.goods_id = goods.goods_id LEFT JOIN 718shop_goods_class gc1 ON goods.gc_id_1 = gc1.gc_id LEFT JOIN 718shop_goods_class gc ON goods.gc_id = gc.gc_id LEFT JOIN 718shop_ziti_address ziti_address ON order_common.reciver_ziti_id = ziti_address.address_id LEFT JOIN 718shop_daddress daddress ON order_goods.deliverer_id = daddress.address_id LEFT JOIN 718shop_voucher voucher ON order_common.voucher_id = voucher.voucher_id LEFT JOIN (select * from 718shop_refund_return where seller_state=2 and refund_state=3) refund_return ON order1.order_id = refund_return.order_id AND (refund_return.goods_id=0 OR order_goods.goods_id = refund_return.goods_id) LEFT JOIN 718shop_member member ON order1.share_id = member.member_id ";
        
        $condition .= " AND NOT (order1.is_zorder=0 AND order1.order_state=20) ORDER BY order1.order_id ASC ";
        
        $sql = "SELECT ".$columns."FROM".$tables.$condition;
//        print_r($sql);die;
        $list = Model()->query($sql);
        
        return $list;
    }

    public function getTuanOrderList($condition){
        $columns = " order1.order_sn, order_goods.goods_name, order_goods.goods_num, daddress.seller_name, order_common.reciver_name, ziti_address.seller_name as ziti_address, SUBSTRING( order_common.reciver_info, INSTR( order_common.reciver_info, 's:11:\"' ) + 6, 11 ) AS mobile, order_goods.goods_id, order1.buyer_name, order1.payment_time, order1.finnshed_time, order_goods.goods_serial, order_goods.goods_barcode, order_goods.goods_price, order_goods.goods_cost_price, order_goods.goods_cost_price * order_goods.goods_num AS all_goods_cost_price, order_goods.goods_price * order_goods.goods_num AS all_goods_price, order_goods.voucher_price, order_goods.goods_pay_price, order1.order_amount, order1.payment_code, order1.ruku_time, order1.order_state, order1.refund_state AS order_refund_state, refund_return.seller_state, refund_return.refund_state, order_goods.goods_type, voucher.voucher_title, gc1.gc_name AS gc1_name, order_goods.commis_rate, order_goods.goods_pay_price * order_goods.commis_rate AS commission ";

        $tables = " 718shop_order order1 INNER JOIN 718shop_order_common order_common ON order1.order_id = order_common.order_id INNER JOIN 718shop_order_goods order_goods ON order1.order_id = order_goods.order_id LEFT JOIN 718shop_goods_class gc3 ON gc3.gc_id = order_goods.gc_id LEFT JOIN 718shop_goods_class gc2 ON gc2.gc_id = gc3.gc_parent_id LEFT JOIN 718shop_goods_class gc1 ON gc1.gc_id = gc2.gc_parent_id LEFT JOIN 718shop_ziti_address ziti_address ON order_common.reciver_ziti_id = ziti_address.address_id LEFT JOIN 718shop_daddress daddress ON order_goods.deliverer_id = daddress.address_id LEFT JOIN 718shop_voucher voucher ON order_common.voucher_id = voucher.voucher_id LEFT JOIN 718shop_refund_return refund_return ON order1.order_id = refund_return.order_id AND ( refund_return.goods_id = 0 OR order_goods.goods_id = refund_return.goods_id ) ";

        $condition .= " AND NOT ( order1.is_zorder = 0 AND order1.order_state = 20 ) ORDER BY order1.order_id ASC LIMIT 20000";

        $sql = "SELECT ".$columns."FROM".$tables.$condition;
        
        $list = Model()->query($sql);
        
        return $list;
    }

    public function getFaOrderList($condition){
        $columns = " order1.order_sn, order_goods.goods_name, order_goods.goods_num, daddress.seller_name, order_goods.goods_id, order1.payment_time, order_goods.goods_price, order_goods.goods_cost_price, order_goods.goods_cost_price * order_goods.goods_num AS all_goods_cost_price, order_goods.goods_price * order_goods.goods_num AS all_goods_price, order_goods.voucher_price, order_goods.goods_pay_price, order1.payment_code, order1.order_state, order1.refund_state AS order_refund_state, refund_return.seller_state, refund_return.refund_state, order_goods.goods_type, voucher.voucher_title ";

        $tables = " 718shop_order order1 INNER JOIN 718shop_order_common order_common ON order1.order_id = order_common.order_id INNER JOIN 718shop_order_goods order_goods ON order1.order_id = order_goods.order_id LEFT JOIN 718shop_daddress daddress ON order_goods.deliverer_id = daddress.address_id LEFT JOIN 718shop_voucher voucher ON order_common.voucher_id = voucher.voucher_id LEFT JOIN 718shop_refund_return refund_return ON order1.order_id = refund_return.order_id AND ( refund_return.goods_id = 0 OR order_goods.goods_id = refund_return.goods_id ) ";

        $condition .= " AND NOT ( order1.is_zorder = 0 AND order1.order_state = 20 ) ORDER BY order1.order_id ASC LIMIT 20000";
        
        $sql = "SELECT ".$columns."FROM".$tables.$condition;

        $list = Model()->query($sql);

        return $list;
    }

    
    /**
     * add,2021.12.6,zyf
     * 仓库报表-订单信息
     */
    public function getWarehouseOrderGoodsExportList($condition) {
        $columns = "order1.order_sn,order_common.reciver_name,daddress.address_id,daddress.seller_name,order_goods.goods_num,order_goods.goods_name,order_goods.goods_serial,SUBSTRING(order_common.reciver_info,INSTR(order_common.reciver_info,'s:11:\"')+6,11) AS mobile,ziti_address.seller_name AS ziti_name,order_common.mall_info AS detail_address,order1.buyer_name,order1.payment_time,order_common.ziti_ladder_time,order_common.order_message,order_common.deliver_explain,order1.order_state,order_goods.goods_type,order_goods.goods_cost_price,order1.refund_state AS order_refund_state,refund.seller_state,refund.refund_state ";
        
        $tables = "718shop_order order1 INNER JOIN 718shop_order_common order_common ON order1.order_id = order_common.order_id INNER JOIN 718shop_order_goods order_goods ON order1.order_id = order_goods.order_id LEFT JOIN 718shop_daddress daddress ON order_goods.deliverer_id = daddress.address_id LEFT JOIN 718shop_ziti_address ziti_address ON order_common.reciver_ziti_id = ziti_address.address_id LEFT JOIN 718shop_refund_return refund ON order1.order_id = refund.order_id AND (refund.goods_id=0 OR order_goods.goods_id = refund.goods_id) ";
        
        $preCondition = "WHERE NOT (order1.is_zorder=0 AND order1.order_state=20) ";
        
        $endCondition = "ORDER BY order1.order_id ASC LIMIT 20000";

        $sql = "SELECT ".$columns."FROM ".$tables.$preCondition.$condition.$endCondition;
        
        $list = Model()->query($sql);

        return $list;
    }
             public function tzGetOrderListPage($ziti_id, $num_page,$condition)
    {
        $fields1 ='order_id,order_sn,buyer_id,buyer_name,add_time,payment_time,finnshed_time,goods_amount,order_amount,order_state,refund_state,lock_state,refund_amount,is_zorder';
        // $sql = "SELECT $fields1 FROM 718shop_order WHERE order_id IN (SELECT order_id FROM 718shop_order_common WHERE reciver_ziti_id = " . $ziti_id . " AND delete_state = 0 ".$condition.") ORDER BY payment_time DESC,  order_id DESC LIMIT 10 offset " . ($num_page - 1)*10;
        $sql = "SELECT $fields1 FROM 718shop_order WHERE order_id IN (SELECT order_id FROM 718shop_order_common WHERE reciver_ziti_id = " . $ziti_id . " )AND delete_state = 0 ". $condition." ORDER BY payment_time DESC,  order_id DESC LIMIT 10 offset " . ($num_page - 1)*10;
        $list = Model()->query($sql);
        // print_r($sql);die;
        if (empty($list)) return array();
        $order_list = array();
        foreach ($list as $order) {
            if (isset($order['order_state'])) {
                $order['state_desc'] = $this->orderState1($order);
            }
            $order_list[$order['order_id']] = $order;
        } 
        if (empty($order_list)) {
            $order_list = $list;
        }
        $fields2 ='order_id,order_message,reciver_name,reciver_info,reciver_ziti_id';
        // // //追加返回订单扩展表信息
        $order_common_list = $this->getOrderCommonList(array('order_id' => array('in', array_keys($order_list))),$fields2);
        // var_dump($order_common_list);die;
        foreach ($order_common_list as $value) {
            $order_list[$value['order_id']]['extend_order_common'] = $value;
            $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
  
        }
        //取商品列表
         $fields3 ='rec_id,order_id,goods_id,goods_name,goods_price,goods_num,goods_image,goods_pay_price,buyer_id,goods_type,commis_rate';
        $order_goods_list = $this->getOrderGoodsList(array('order_id' => array('in', array_keys($order_list))),$fields3);
        if (!empty($order_goods_list)) {
            foreach ($order_goods_list as $key => $value) {
                $order_list[$value['order_id']]['extend_order_goods'][$key] = $value;
                $order_list[$value['order_id']]['extend_order_goods'][$key]['goods_image'] = $this->cthumb($order_list[$value['order_id']]['extend_order_goods'][$key]['goods_image'], 240, $order_list[$value['order_id']]['extend_order_goods'][$key]['store_id']);
            }
        } else {
            $order_list[$value['order_id']]['extend_order_goods'] = array();
        }
        return $order_list;
    }
   public function tzGetOrderListStatePage($ziti_id,$condition)
    { 
        $field ='order_id';
        $sql = "SELECT $field FROM 718shop_order WHERE order_id IN (SELECT order_id FROM 718shop_order_common WHERE reciver_ziti_id = " . $ziti_id  . $condition.")";
        $list = Model()->query($sql);
        
        return $list;
    }
    
}
