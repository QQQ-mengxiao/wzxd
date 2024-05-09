<?php
/**
 * 限时折扣活动模型 
 *
 * 
 *
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class p_xianshiModel extends Model{

    const XIANSHI_STATE_NORMAL = 1;
    const XIANSHI_STATE_CLOSE = 2;
    const XIANSHI_STATE_CANCEL = 3;

    private $xianshi_state_array = array(
        0 => '全部',
        self::XIANSHI_STATE_NORMAL => '正常',
        self::XIANSHI_STATE_CLOSE => '已结束',
        self::XIANSHI_STATE_CANCEL => '管理员关闭'
    );

    public function __construct(){
        parent::__construct('p_xianshi');
    }

	/**
     * 读取限时折扣列表
	 * @param array $condition 查询条件
	 * @param int $page 分页数
	 * @param string $order 排序
	 * @param string $field 所需字段
     * @return array 限时折扣列表
	 *
	 */
	public function getXianshiList($condition, $page=null, $order='end_time desc,xianshi_id asc', $field='*') {
        $xianshi_list = $this->field($field)->where($condition)->page($page)->order($order)->select();
        if(!empty($xianshi_list)) {
            for($i =0, $j = count($xianshi_list); $i < $j; $i++) {
                $xianshi_list[$i] = $this->getXianshiExtendInfo($xianshi_list[$i]);
            }
        }
        return $xianshi_list;
	}

    /**
	 * 根据条件读取限制折扣信息
	 * @param array $condition 查询条件
     * @return array 限时折扣信息
	 *
	 */
    public function getXianshiInfo($condition) {
        $xianshi_info = $this->where($condition)->find();
        $xianshi_info = $this->getXianshiExtendInfo($xianshi_info);
        return $xianshi_info;
    }

    /**
	 * 根据限时折扣编号读取限制折扣信息
	 * @param array $xianshi_id 限制折扣活动编号
	 * @param int $store_id 如果提供店铺编号，判断是否为该店铺活动，如果不是返回null
     * @return array 限时折扣信息
	 *
	 */
    public function getXianshiInfoByID($xianshi_id, $store_id = 0) {
        if(intval($xianshi_id) <= 0) {
            return null;
        }

        $condition = array();
        $condition['xianshi_id'] = $xianshi_id;
        $xianshi_info = $this->getXianshiInfo($condition);
        if($store_id > 0 && $xianshi_info['store_id'] != $store_id) {
            return null;
        } else {
            return $xianshi_info;
        }
    }
    /**
     * 根据条件读取限时类型
     * @param array $condition 查询条件
     * @return array 限时折扣信息
     *
     */
    public function getXianshitype($goods_id,$is_group_ladder) {
         if($is_group_ladder==4){
            $model_xianshi_goods = Model('p_xianshi_goods');
            $condition_xs=array();
            $condition_xs['goods_id']=$goods_id;
            $condition_xs['end_time'] = array('gt', TIMESTAMP);
            $condition_xs['state'] = 1;
            $xianshigoods = $model_xianshi_goods->getXianshiGoodsInfo( $condition_xs);
            $xianshi_info = $this->getXianshiInfo(array('xianshi_id' => $xianshigoods['xianshi_id']));
            $xianshi_type=$xianshi_info['xianshi_type'];
         }else{
            if($is_group_ladder==3 ){
             $xianshi_type=1;
            }else{
                $xianshi_type=3;
            }
        }
        return $xianshi_type;
    }
    public function getXianshitype2($goods_id,$is_group_ladder) {
         if($is_group_ladder==4){
            $model_xianshi_goods = Model('p_xianshi_goods');
            $condition_xs=array();
            $condition_xs['goods_id']=$goods_id;
            $condition_xs['end_time'] = array('gt', TIMESTAMP);
            $condition_xs['state'] = 1;
            $xianshigoods = $model_xianshi_goods->getXianshiGoodsInfo( $condition_xs);
            $xianshi_info = $this->getXianshiInfo(array('xianshi_id' => $xianshigoods['xianshi_id']));
            $xianshi_type=$xianshi_info['xianshi_type'];
         }else{
            if($is_group_ladder==3||$is_group_ladder==6 ){
             $xianshi_type=1;
            }else{
                $xianshi_type=3;
            }
        }
        return $xianshi_type;
    }
    /**
     * 限时折扣状态数组
     *
     */
    public function getXianshiStateArray() {
        return $this->xianshi_state_array;
    }

	/*
	 * 增加 
	 * @param array $param
	 * @return bool
     *
	 */
    public function addXianshi($param){
        $param['state'] = self::XIANSHI_STATE_NORMAL;
        return $this->insert($param);	
    }

    /*
	 * 更新
	 * @param array $update
	 * @param array $condition
	 * @return bool
     *
	 */
    public function editXianshi($update, $condition){
        return $this->where($condition)->update($update);
    }

	/*
	 * 删除限时折扣活动，同时删除限时折扣商品
	 * @param array $condition
	 * @return bool
     *
	 */
    public function delXianshi($condition){
        $xianshi_list = $this->getXianshiList($condition);
        $xianshi_id_string = '';
        if(!empty($xianshi_list)) {
            foreach ($xianshi_list as $value) {
                $xianshi_id_string .= $value['xianshi_id'] . ',';
            }
        }

        //删除限时折扣商品
        if($xianshi_id_string !== '') {
            $model_xianshi_goods = Model('p_xianshi_goods');
            $model_xianshi_goods->delXianshiGoods(array('xianshi_id'=>array('in', $xianshi_id_string)));
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
        $update['end_time'] = TIMESTAMP;//mx0829

        //删除限时折扣商品
        if($xianshi_id_string !== '') {
            $model_xianshi_goods = Model('p_xianshi_goods');
            $model_xianshi_goods->editXianshiGoods($update, array('xianshi_id'=>array('in', $xianshi_id_string)));
        }

        return $this->editXianshi($update, $condition);
    }

    /**
     * 获取限时折扣扩展信息，包括状态文字和是否可编辑状态
     * @param array $xianshi_info
     * @return string
     *
     */
    public function getXianshiExtendInfo($xianshi_info) {
        if($xianshi_info['end_time'] > TIMESTAMP) {
            $xianshi_info['xianshi_state_text'] = $this->xianshi_state_array[$xianshi_info['state']];
        } else {
            $xianshi_info['xianshi_state_text'] = '已结束';
        }

        if($xianshi_info['state'] == self::XIANSHI_STATE_NORMAL && $xianshi_info['end_time'] > TIMESTAMP) {
            $xianshi_info['editable'] = true;
        } else {
            $xianshi_info['editable'] = false;
        }

        return $xianshi_info;
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
