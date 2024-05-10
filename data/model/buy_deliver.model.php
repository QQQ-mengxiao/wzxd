<?php
/**
 * 即买即送活动模型 
 *
 * 
 *
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class buy_deliverModel extends Model{

    const BUY_DELIVER_STATE_NORMAL = 1;
    const BUY_DELIVER_STATE_CLOSE = 2;
    const BUY_DELIVER_STATE_CANCEL = 3;

    private $buy_deliver_state_array = array(
        0 => '全部',
        self::BUY_DELIVER_STATE_NORMAL => '正常',
        self::BUY_DELIVER_STATE_CLOSE => '已结束',
        self::BUY_DELIVER_STATE_CANCEL => '管理员关闭'
    );

    public function __construct(){
        parent::__construct('buy_deliver');
    }

	/**
     * 读取即买即送列表
	 * @param array $condition 查询条件
	 * @param int $page 分页数
	 * @param string $order 排序
	 * @param string $field 所需字段
     * @return array 限时折扣列表
	 *
	 */
	public function getBuyDeliverList($condition, $page=null, $order='', $field='*') {
        $buy_deliver_list = $this->field($field)->where($condition)->page($page)->order($order)->select();
        if(!empty($buy_deliver_list)) {
            for($i =0, $j = count($buy_deliver_list); $i < $j; $i++) {
                $buy_deliver_list[$i] = $this->getBuyDeliverExtendInfo($buy_deliver_list[$i]);
            }
        }
        return $buy_deliver_list;
	}

    /**
	 * 根据条件读取即买即送信息
	 * @param array $condition 查询条件
     * @return array 限时折扣信息
	 *
	 */
    public function getBuyDeliverInfo($condition) {
        $buy_deliver_info = $this->where($condition)->find();
        $buy_deliver_info = $this->getBuyDeliverExtendInfo($buy_deliver_info);
        return $buy_deliver_info;
    }

    /**
	 * 根据即买即送编号读取限制折扣信息
	 * @param array $xianshi_id 限制折扣活动编号
	 * @param int $store_id 如果提供店铺编号，判断是否为该店铺活动，如果不是返回null
     * @return array 限时折扣信息
	 *
	 */
    public function getBuyDeliverInfoByID($buy_deliver_id, $store_id = 0) {
        if(intval($buy_deliver_id) <= 0) {
            return null;
        }

        $condition = array();
        $condition['buy_deliver_id'] = $buy_deliver_id;
        $buy_deliver_info = $this->getBuydeliverInfo($condition);
        if($store_id > 0 && $buy_deliver_info['store_id'] != $store_id) {
            return null;
        } else {
            return $buy_deliver_info;
        }
    }

    /**
     * 即买即送状态数组
     *
     */
    public function getBuyDeliverStateArray() {
        return $this->buy_deliver_state_array;
    }

	/*
	 * 增加 
	 * @param array $param
	 * @return bool
     *
	 */
    public function addBuyDeliver($param){
        //showDialog(Language::get('xianshi_add_fail'));die;
        $param['state'] = self::BUY_DELIVER_STATE_NORMAL;
        return $this->insert($param);	
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
    /*
	 * 更新
	 * @param array $update
	 * @param array $condition
	 * @return bool
     *
	 */
    public function editBuyDeliver($update, $condition){
        return $this->where($condition)->update($update);
    }

	/*
	 * 删除即买即送活动，同时删除即买即送商品
	 * @param array $condition
	 * @return bool
     *
	 */
    public function delBuyDeliver($condition){
        $buy_deliver_list = $this->getBuyDeliverList($condition);
        $buy_deliver_id_string = '';
        if(!empty($buy_deliver_list)) {
            foreach ($buy_deliver_list as $value) {
                $buy_deliver_id_string .= $value['buy_deliver_id'] . ',';
            }
        }

        //删除限时折扣商品
        if($buy_deliver_id_string !== '') {
            $model_buy_deliver_goods = Model('buy_deliver_goods');
            $model_buy_deliver_goods->delBuyDeliverGoods(array('buy_deliver_id'=>array('in', $buy_deliver_id_string)));
        }

        return $this->where($condition)->delete();
    }

	/*
	 * 取消限时折扣活动，同时取消限时折扣商品 
	 * @param array $condition
	 * @return bool
     *
	 */
    public function cancelXianshi($condition){
        $xianshi_list = $this->getXianshiList($condition);
        $xianshi_id_string = '';
        if(!empty($xianshi_list)) {
            foreach ($xianshi_list as $value) {
                $xianshi_id_string .= $value['xianshi_id'] . ',';
            }
        }

        $update = array();
        $update['state'] = self::XIANSHI_STATE_CANCEL;

        //删除限时折扣商品
        if($xianshi_id_string !== '') {
            $model_xianshi_goods = Model('p_xianshi_goods');
            $model_xianshi_goods->editXianshiGoods($update, array('xianshi_id'=>array('in', $xianshi_id_string)));
        }

        return $this->editXianshi($update, $condition);
    }

    /**
     * 获取即买即送扩展信息，包括状态文字和是否可编辑状态
     * @param array $xianshi_info
     * @return string
     *
     */
    public function getBuyDeliverExtendInfo($buy_deliver_info) {
        $buy_deliver_info['buy_deliver_state_text'] = $this->buy_deliver_state_array[$buy_deliver_info['state']];
        if($buy_deliver_info['state'] == self::BUY_DELIVER_STATE_NORMAL) {
            $buy_deliver_info['editable'] = true;
        } else {
            $buy_deliver_info['editable'] = false;
        }

        return $buy_deliver_info;
    }

    /**
     * 过期修改状态
     */
    public function editExpireXianshi($condition) {
        $condition['end_time'] = array('lt', TIMESTAMP);
        
        // 更新商品促销价格
        $xianshigoods_list = Model('p_xianshi_goods')->getXianshiGoodsList($condition);
        if (!empty($xianshigoods_list)) {
            $goodsid_array = array();
            foreach ($xianshigoods_list as $val) {
                $goodsid_array[] = $val['goods_id'];
            }
            // 更新商品促销价格，需要考虑抢购是否在进行中
            QueueClient::push('updateGoodsPromotionPriceByGoodsId', $goodsid_array);
        }
        $condition['state'] = self::XIANSHI_STATE_NORMAL;
        
        $updata = array();
        $update['state'] = self::XIANSHI_STATE_CLOSE;
        $this->editXianshi($update, $condition);
        return true;
    }

}
