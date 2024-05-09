<?php
/**
 * 限时折扣活动商品模型 
 **/
defined('In718Shop') or exit('Access Invalid!');
class buy_deliver_goodsModel extends Model{

    const XIANSHI_GOODS_STATE_CANCEL = 0;
    const XIANSHI_GOODS_STATE_NORMAL = 1;

    public function __construct(){
        parent::__construct('buy_deliver_goods');
    }
    
    /**
     * 读取即买即送商品列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @param int $limit 个数限制
     * @return array 即买即送商品列表
     *
     */
    public function getBuyDeliverGoodsExtendList($condition, $page=null, $order='', $field='*', $limit = 0) {
        $buy_deliver_goods_list = $this->getBuyDeliverGoodsList($condition, $page, $order, $field, $limit);
        if(!empty($buy_deliver_goods_list)) {
            for($i=0, $j=count($buy_deliver_goods_list); $i < $j; $i++) {
                $buy_deliver_goods_list[$i] = $this->getBuyDeliverGoodsExtendInfo($buy_deliver_goods_list[$i]);
                if ($buy_deliver_goods_list[$i]['goods_state'] != 1 || $buy_deliver_goods_list[$i]['goods_verify'] != 1) {
                    unset($buy_deliver_goods_list[$i]);
                }
            }
        }
        return $buy_deliver_goods_list;
    }
	
	 /**
     * 读取即买即送商品列表--商家中心
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @param int $limit 个数限制
     * @return array 即买即送商品列表
     *
     */
    public function getBuyDeliverGoodsExtendListShop($condition, $page=null, $order='', $field='*', $limit = 0,$goods_serial='') {
        if($goods_serial){
            $buy_deliver_goods_list = $this->getBuyDeliverGoodsOnList($condition, $page, $order, $field, $limit, $goods_serial);
        }else {
        $buy_deliver_goods_list = $this->getBuyDeliverGoodsList($condition, $page, $order, $field, $limit);
        }
        if(!empty($buy_deliver_goods_list)) {
            for($i=0, $j=count($buy_deliver_goods_list); $i < $j; $i++) {
                $buy_deliver_goods_list[$i] = $this->getBuyDeliverGoodsExtendInfo($buy_deliver_goods_list[$i]);
            }
        }
        return $buy_deliver_goods_list;
    }


    /**
     * 读取即买即送商品列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @param int $limit 个数限制
     * @return array 限时折扣商品列表
     *
     */
     
    public function getBuyDeliverGoodsList($condition, $page=null, $order='', $field='*', $limit = 0) {
        return $buy_deliver_goods_list = $this->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
    }

    public function getBuyDeliverGoodsOnList($condition, $page = null, $order = '', $field = '*', $limit = 0, $goods_serial){
        $where = array();
        if($condition['goods_name']){
            $where['buy_deliver_goods.goods_name'] = $condition['goods_name'];
        }
        $where['buy_deliver_goods.buy_deliver_id'] = $condition['buy_deliver_id'];
        $where['goods.goods_serial'] = $goods_serial;
        $on = 'goods.goods_id = buy_deliver_goods.goods_id';
        return $buy_deliver_goods_list = $this->table('buy_deliver_goods,goods')->join('left')->field($field)->where($where)->on($on)->page($page)->order($order)->limit($limit)->select();
    }


    /**
     * 获取即买即送商品扩展信息
     * @param array $xianshi_info
     * @return array 扩展限时折扣信息
     *
     */
    public function getBuyDeliverGoodsExtendInfo($buy_deliver_info) {
        $buy_deliver_info['goods_url'] = urlShop('goods', 'index', array('goods_id' => $buy_deliver_info['goods_id']));
        $buy_deliver_info['is_group_ladder'] = 5;
        $storage_arr = $this->table('goods')->field('goods_storage,goods_name,goods_price,goods_image,goods_state,goods_verify')->where(array('goods_id' => $buy_deliver_info['goods_id']))->find();
        $buy_deliver_info['goods_storage'] = $storage_arr['goods_storage'];
        $buy_deliver_info['goods_image'] = $storage_arr['goods_image'];
        $buy_deliver_info['image_url'] = cthumb($buy_deliver_info['goods_image'], 240, $buy_deliver_info['store_id']);
        $buy_deliver_info['goods_state'] = $storage_arr['goods_state'];
        $buy_deliver_info['goods_verify'] = $storage_arr['goods_verify'];
        // $buy_deliver_info['goods_name'] = $storage_arr['goods_name'];
        // $buy_deliver_info['goods_price'] = $storage_arr['goods_price'];
        // $buy_deliver_info['xianshi_price'] = ncPriceFormat($buy_deliver_info['xianshi_price']);
        // $buy_deliver_info['xianshi_app_price'] = ncPriceFormat($buy_deliver_info['xianshi_app_price']);
        // //$xianshi_info['xianshi_discount'] = number_format($xianshi_info['xianshi_price'] / $xianshi_info['goods_marketprice'] * 10, 1).'折';
        // $buy_deliver_info['xianshi_discount'] = number_format($buy_deliver_info['xianshi_price'] / $buy_deliver_info['goods_price'] * 10, 1).'折';
        return $buy_deliver_info;
    }

    /**
     * 增加即买即送商品 
     * @param array $xianshi_goods_info
     * @return bool
     *
     */
    public function addBuyDeliverGoods($buy_deliver_goods_info){
        $buy_deliver_goods_info['state'] = self::XIANSHI_GOODS_STATE_NORMAL;
        $buy_deliver_goods_id = $this->insert($buy_deliver_goods_info);
        
        // 删除商品限时折扣缓存
        //$this->_dGoodsBuyDeliverCache($buy_deliver_goods_info['goods_id']);
        
        $buy_deliver_goods_info['buy_deliver_goods_id'] = $buy_deliver_goods_id;
        $buy_deliver_goods_info = $this->getBuyDeliverGoodsExtendInfo($buy_deliver_goods_info);
        return $buy_deliver_goods_info;
    }

    /**
     * 删除商品即买即送缓存
     * @param int $goods_id
     * @return boolean
     */
    private function _dGoodsBuyDeliverCache($goods_id) {
        return dcache($goods_id, 'goods_buy_deliver');
    }

    /**
     * 根据即买即送商品编号读取限制折扣商品信息
     * @param int $xianshi_goods_id
     * @return array 限时折扣商品信息
     *
     */
    public function getBuyDeliverGoodsInfoByID($buy_deliver_goods_id, $store_id = 0) {
        if(intval($buy_deliver_goods_id) <= 0) {
            return null;
        }

        $condition = array();
        $condition['buy_deliver_goods_id'] = $buy_deliver_goods_id;
        $buy_deliver_goods_info = $this->getBuyDeliverGoodsInfo($condition);

        if($store_id > 0 && $buy_deliver_goods_info['store_id'] != $store_id) {
            return null;
        } else {
            return $buy_deliver_goods_info;
        }
    }
 /**
     * 根据即买即送商品编号读取限制折扣商品信息
     * @param int $xianshi_goods_id
     * @return array 限时折扣商品信息
     *
     */
    public function getBuyDeliverGoodsInfoBygoodsID($buy_deliver_goods_id, $store_id = 0) {
        if(intval($buy_deliver_goods_id) <= 0) {
            return null;
        }

        $condition = array();
        $condition['goods_id'] = $buy_deliver_goods_id;
        $buy_deliver_goods_info = $this->getBuyDeliverGoodsInfo($condition);

        if($store_id > 0 && $buy_deliver_goods_info['store_id'] != $store_id) {
            return null;
        } else {
            return $buy_deliver_goods_info;
        }
    }
    /**
     * 根据条件读取即买即送商品信息
     * @param array $condition 查询条件
     * @return array 限时折扣商品信息
     *
     */
    public function getBuyDeliverGoodsInfo($condition) {
        $result = $this->where($condition)->find();
        return $result;
    }

    /**
     * 删除
     * @param array $condition
     * @return bool
     *
     */
    public function delBuyDeliverGoods($condition){
        $buy_deliver_goods_list = $this->getBuyDeliverGoodsList($condition, null, '', 'goods_id');
        $result = $this->where($condition)->delete();
        if ($result) {
            if (!empty($buy_deliver_goods_list)) {
                foreach ($buy_deliver_goods_list as $val) {
                    
                    // 删除商品限时折扣缓存
                    //$this->_dGoodsXianshiCache($val['goods_id']);
                    // 插入对列 更新促销价格
                    QueueClient::push('updateGoodsPromotionPriceByGoodsId', $val['goods_id']);
                }
            }
        }
        return $result;
    }

	/**
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     *
     */
     public function editBuyDeliverGoods($update, $condition){
         $result = $this->where($condition)->update($update);
         return $result;
     }

    /**
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     *
     */
    // public function editBuyDeliverGoods($update, $condition){
    //     $result = $this->where($condition)->update($update);
    //     showDialog("即买即送添加失败");die;
    //     if ($result) {
    //         $buy_deliver_goods_list = $this->getBuyDeliverGoodsList($condition, null, '', 'goods_id');
    //         if (!empty($buy_deliver_goods_list)) {
    //             foreach ($buy_deliver_goods_list as $val) {
    //                 // 删除商品限时折扣缓存
    //                 $this->_dGoodsBuyDeliverCache($val['goods_id']);
    //                 // 插入对列 更新促销价格
    //                 //QueueClient::push('updateGoodsPromotionPriceByGoodsId', $val['goods_id']);
    //             }
    //         }
    //     }
    //     return $result;
    // }

    /**
     * 删除商品即买即送缓存
     * @param int $goods_id
     * @return boolean
     */
    // private function _dGoodsBuyDeliverCache($goods_id) {
    //     return dcache($goods_id, 'buy_deliver');
    // }
//}
}
