<?php
/**
 * 新人专享活动商品模型 
 **/
defined('In718Shop') or exit('Access Invalid!');
class p_xinren_goodsModel extends Model{                        

    const XINREN_GOODS_STATE_CANCEL = 0;
    const XINREN_GOODS_STATE_NORMAL = 1;

    public function __construct(){
        parent::__construct('p_xinren_goods');
    }
	 /**
     * 新人专享表获取详细商品信息
     *
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     * @return array
     */
  	public function getGoodsListInfoFromXinRen($condition,$page=null,$order='',$field='*',$limit=''){
        $on = 'p_xinren_goods.goods_id = goods.goods_id';
        $result = $this->table('p_xinren_goods,goods')->field($field)->join('left')->on($on)->where($condition)->page($page)->order($order)->limit($limit)->select();
        return $result;
	}
    
	/**
	 * 读取新人专享商品列表
	 * @param array $condition 查询条件
	 * @param int $page 分页数
	 * @param string $order 排序
	 * @param string $field 所需字段
     * @param int $limit 个数限制
     * @return array 新人专享商品列表
	 *
	 */
	 
	public function getXinRenGoodsList($condition, $page=null, $order='', $field='*', $limit = 0) {
        return $xinren_goods_list = $this->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
	}

	/**
	 * 读取新人专享商品列表
	 * @param array $condition 查询条件
	 * @param int $page 分页数
	 * @param string $order 排序
	 * @param string $field 所需字段
     * @param int $limit 个数限制
     * @return array 新人专享商品列表
	 *
	 */
	public function getXinRenGoodsExtendList($condition, $page=null, $order='', $field='*', $limit = 0) {
        $xinren_goods_list = $this->getXinRenGoodsList($condition, $page, $order, $field, $limit);
        if(!empty($xinren_goods_list)) {
            for($i=0, $j=count($xinren_goods_list); $i < $j; $i++) {
                $xinren_goods_list[$i] = $this->getXinRenGoodsExtendInfo($xinren_goods_list[$i]);
            }
        }
        return $xinren_goods_list;
	}
    /**
     * api读取新人专享商品列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @param int $limit 个数限制
     * @return array 新人专享商品列表
     *
     */
    public function getApiXinRenGoodsExtendList($condition, $page=null, $order='', $field='*', $limit = 0) {
        // $xinren_goods_list = $this->getXinRenGoodsList($condition, $page, $order, $field, $limit);
        $xinren_goods_list = $this->getGoodsListInfoFromXinRen($condition, $page, $order, $field, $limit);
        
        if(!empty($xinren_goods_list)) {
            for($i=0, $j=count($xinren_goods_list); $i < $j; $i++) {
                $xinren_goods_list[$i] = $this->getXinRenGoodsExtendInfo($xinren_goods_list[$i]);
            }
        }
        return $xinren_goods_list;
    }
	// str
	public function getXianshiGoodsExtendIds($condition, $page=null, $order='', $field='goods_id', $limit = 0) {
        $xianshi_goods_id_list = $this->getXianshiGoodsList($condition, $page, $order, $field, $limit);
      
		if(!empty($xianshi_goods_id_list)){
			for($i=0;$i<count($xianshi_goods_id_list); $i++){
				
				$xianshi_goods_id_list[$i]=$xianshi_goods_id_list[$i]['goods_id'];
				 
			}
		}
		
        return $xianshi_goods_id_list;
	}
	//  end

    /**
	 * 根据条件读取新人专享商品信息
	 * @param array $condition 查询条件
     * @return array 新人专享商品信息
	 *
	 */
    public function getXinrenGoodsInfo($condition) {
        $result = $this->where($condition)->find();
        return $result;
    }

    /**
	 * 根据新人专享商品编号读取新人专享商品信息
	 * @param int $xinren_goods_id
     * @return array 新人专享商品信息
	 *
	 */
    public function getXinRenGoodsInfoByID($xinren_goods_id, $store_id = 0) {
        if(intval($xinren_goods_id) <= 0) {
            return null;
        }

        $condition = array();
        $condition['xinren_goods_id'] = $xinren_goods_id;
        $xinren_goods_info = $this->getXinrenGoodsInfo($condition);

        if($store_id > 0 && $xinren_goods_info['store_id'] != $store_id) {
            return null;
        } else {
            return $xinren_goods_info;
        }
    }

    /**
     * 增加新人专享商品 
     * @param array $xinren_goods_info
     * @return bool
     *
     */
    public function addXinRenGoods($xinren_goods_info){
        $xinren_goods_info['state'] = self::XINREN_GOODS_STATE_NORMAL;
        $xinren_goods_id = $this->insert($xinren_goods_info);
        
        // 删除商品新人专享缓存
        $this->_dGoodsXinRenCache($xinren_goods_info['goods_id']);
        
        $xinren_goods_info['xinren_goods_id'] = $xinren_goods_id;
        $xinren_goods_info = $this->getXinRenGoodsExtendInfo($xinren_goods_info);
        return $xinren_goods_info;
    }

    /**
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     *
     */
    public function editXinRenGoods($update, $condition){
        $result = $this->where($condition)->update($update);
        return $result;
    }

    /**
     * 删除
     * @param array $condition
     * @return bool
     *
     */
    public function delXinRenGoods($condition){
        $xinren_goods_list = $this->getXinRenGoodsList($condition, null, '', 'goods_id');
        $result = $this->where($condition)->delete();
        if ($result) {
            if (!empty($xinren_goods_list)) {
                foreach ($xinren_goods_list as $val) {
                    // 删除商品新人专享缓存
                    $this->_dGoodsXinRenCache($val['goods_id']);
                }
            }
        }
        return $result;
    }

    /**
     * 获取新人专享商品扩展信息
     * @param array $xianshi_info
     * @return array 扩展新人专享信息
     *
     */
    public function getXinRenGoodsExtendInfo($xinren_info) {
        $xinren_info['goods_url'] = urlShop('goods', 'index', array('goods_id' => $xinren['goods_id']));
        $xinren_info['image_url'] = cthumb($xinren_info['goods_image'], 360, $xinren_info['store_id']);
        $xinren_info['xinren_price'] = ncPriceFormat($xinren_info['xinren_price']);
		//$xinren_info['xinren_app_price'] = ncPriceFormat($xianshi_info['xianshi_app_price']);
        //$xianshi_info['xianshi_discount'] = number_format($xianshi_info['xianshi_price'] / $xianshi_info['goods_marketprice'] * 10, 1).'折';
        //print_r($xinren_info);die;
		$xinren_info['xinren_discount'] = number_format($xinren_info['xinren_price'] / $xinren_info['goods_price'] * 10, 1).'折';
        return $xinren_info;
    }

    /**
     * 获取推荐限时折扣商品
     * @param int $count 推荐数量
     * @return array 推荐限时活动列表
     *
     */
    public function getXianshiGoodsCommendList($count = 4) {
        $condition = array();
        $condition['state'] = self::XIANSHI_GOODS_STATE_NORMAL;
        $condition['start_time'] = array('lt', TIMESTAMP);
        $condition['end_time'] = array('gt', TIMESTAMP);
        $xianshi_list = $this->getXianshiGoodsExtendList($condition, null, 'xianshi_recommend desc', '*', $count);
        return $xianshi_list;
    }

    /**
     * 根据商品编号查询是否享受新人专享活动，如果有新人专享活动，没有返回null
     * @param int $goods_id
     * @return array $xianshi_info
     *
     */
    public function getXinrenGoodsInfoByGoodsID($goods_id) {
        $info = $this->_rGoodsXinrenCache($goods_id);
        if(empty($info)) {
            $condition['state'] = 1;
            $condition['goods_id'] = $goods_id;
            $xinren_goods_list = $this->getXinRenGoodsExtendList($condition, null, 'xinren_goods_id asc', '*', 1);
            $info['info'] = serialize($xinren_goods_list[0]);
            $this->_wGoodsXinRenCache($goods_id, $info);
        }
        $xinren_goods_info = unserialize($info['info']);
        return $xinren_goods_info;
    }

    /**
     * 根据商品编号查询是否有可用限时折扣活动，如果有返回限时折扣活动，没有返回null
     * @param string $goods_string 商品编号字符串，例：'1,22,33'
     * @return array $xianshi_goods_list
     *
     */
    public function getXianshiGoodsListByGoodsString($goods_string) {
        $xianshi_goods_list = $this->_getXianshiGoodsListByGoods($goods_string);
        $xianshi_goods_list = array_under_reset($xianshi_goods_list, 'goods_id');
        return $xianshi_goods_list;
    }

    /**
     * 根据商品编号查询是否有可用限时折扣活动，如果有返回限时折扣活动，没有返回null
     * @param string $goods_id_string
     * @return array $xianshi_info
     *
     */
    private function _getXianshiGoodsListByGoods($goods_id_string) {
        $condition = array();
        $condition['state'] = self::XIANSHI_GOODS_STATE_NORMAL;
        $condition['start_time'] = array('lt', TIMESTAMP);
        $condition['end_time'] = array('gt', TIMESTAMP);
        $condition['goods_id'] = array('in', $goods_id_string);
        $xianshi_goods_list = $this->getXianshiGoodsExtendList($condition, null, 'xianshi_goods_id desc', '*');
        return $xianshi_goods_list;
    }
    
     /**
     * 读取商品新人专享缓存
     * @param int $goods_id
     * @return array/bool
     */
    private function _rGoodsXinrenCache($goods_id) {
        return rcache($goods_id, 'goods_xinren');
    }
    
   /**
     * 写入商品新人专享缓存
     * @param int $goods_id
     * @param array $info
     * @return boolean
     */
    private function _wGoodsXinRenCache($goods_id, $info) {
        return wcache($goods_id, $info, 'goods_xinren');
    }
    
    /**
     * 删除商品新人专享缓存
     * @param int $goods_id
     * @return boolean
     */
    private function _dGoodsXinRenCache($goods_id) {
        return dcache($goods_id, 'goods_xinren');
    }
}
