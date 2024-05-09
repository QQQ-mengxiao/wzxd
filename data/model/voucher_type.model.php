<?php
/**
 * 限时折扣活动模型 
 *
 * 
 *
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class voucher_typeModel extends Model{

    const vouchertype_STATE_NORMAL = 1;
    const vouchertype_STATE_CLOSE = 2;
    const vouchertype_STATE_CANCEL = 3;

    private $vouchertype_state_array = array(
        0 => '全部',
        self::vouchertype_STATE_NORMAL => '正常',
        self::vouchertype_STATE_CLOSE => '已结束',
        self::vouchertype_STATE_CANCEL => '管理员关闭'
    );

    public function __construct(){
        parent::__construct('voucher_type');
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
	public function getvouchertypeList($condition, $page=null, $order='end_time desc,vouchertype_id asc', $field='*') {
        $vouchertype_list = $this->field($field)->where($condition)->page($page)->order($order)->select();
        if(!empty($vouchertype_list)) {
            for($i =0, $j = count($vouchertype_list); $i < $j; $i++) {
                $vouchertype_list[$i] = $this->getvouchertypeExtendInfo($vouchertype_list[$i]);
            }
        }
        return $vouchertype_list;
	}

    /**
	 * 根据条件读取限制折扣信息
	 * @param array $condition 查询条件
     * @return array 限时折扣信息
	 *
	 */
    public function getvouchertypeInfo($condition) {
        $vouchertype_info = $this->where($condition)->find();
       
        return $vouchertype_info;
    }

    /**
	 * 根据限时折扣编号读取限制折扣信息
	 * @param array $vouchertype_id 限制折扣活动编号
	 * @param int $store_id 如果提供店铺编号，判断是否为该店铺活动，如果不是返回null
     * @return array 限时折扣信息
	 *
	 */
    public function getvouchertypeInfoByID($vouchertype_id, $store_id = 0) {
        if(intval($vouchertype_id) <= 0) {
            return null;
        }

        $condition = array();
        $condition['vouchertype_id'] = $vouchertype_id;
        $vouchertype_info = $this->getvouchertypeInfo($condition);
        if($store_id > 0 && $vouchertype_info['store_id'] != $store_id) {
            return null;
        } else {
            return $vouchertype_info;
        }
    }

    /**
     * 限时折扣状态数组
     *
     */
    public function getvouchertypeStateArray() {
        return $this->vouchertype_state_array;
    }

	/*
	 * 增加 
	 * @param array $param
	 * @return bool
     *
	 */
    public function addvouchertype($param){
     
        return $this->insert($param);	
    }

    /*
	 * 更新
	 * @param array $update
	 * @param array $condition
	 * @return bool
     *
	 */
    public function editvouchertype($update, $condition){
        return $this->where($condition)->update($update);
    }

	/*
	 * 删除限时折扣活动，同时删除限时折扣商品
	 * @param array $condition
	 * @return bool
     *
	 */
    public function delvouchertype($condition){
        $vouchertype_list = $this->getvouchertypeList($condition);
        $vouchertype_id_string = '';
        if(!empty($vouchertype_list)) {
            foreach ($vouchertype_list as $value) {
                $vouchertype_id_string .= $value['vouchertype_id'] . ',';
            }
        }

        //删除限时折扣商品
        if($vouchertype_id_string !== '') {
            $model_vouchertype_goods = Model('voucher_type_goods');
            $model_vouchertype_goods->delvouchertypeGoods(array('vouchertype_id'=>array('in', $vouchertype_id_string)));
        }

        return $this->where($condition)->delete();
    }

	/*
	 * 取消限时折扣活动，同时取消限时折扣商品 
	 * @param array $condition
	 * @return bool
     *
	 */
    public function cancelvouchertype($condition){
        $vouchertype_list = $this->getvouchertypeList($condition);
        $vouchertype_id_string = '';
        if(!empty($vouchertype_list)) {
            foreach ($vouchertype_list as $value) {
                $vouchertype_id_string .= $value['vouchertype_id'] . ',';
            }
        }

        $update = array();
        $update['state'] = self::vouchertype_STATE_CANCEL;
        $update['end_time'] = TIMESTAMP;//mx0829

        //删除限时折扣商品
        if($vouchertype_id_string !== '') {
            $model_vouchertype_goods = Model('voucher_type_goods');
            $model_vouchertype_goods->editvouchertypeGoods($update, array('vouchertype_id'=>array('in', $vouchertype_id_string)));
        }

        return $this->editvouchertype($update, $condition);
    }

    /**
     * 获取限时折扣扩展信息，包括状态文字和是否可编辑状态
     * @param array $vouchertype_info
     * @return string
     *
     */
    public function getvouchertypeExtendInfo($vouchertype_info) {
        if($vouchertype_info['end_time'] > TIMESTAMP) {
            $vouchertype_info['vouchertype_state_text'] = $this->vouchertype_state_array[$vouchertype_info['state']];
        } else {
            $vouchertype_info['vouchertype_state_text'] = '已结束';
        }

        if($vouchertype_info['state'] == self::vouchertype_STATE_NORMAL && $vouchertype_info['end_time'] > TIMESTAMP) {
            $vouchertype_info['editable'] = true;
        } else {
            $vouchertype_info['editable'] = false;
        }

        return $vouchertype_info;
    }

    /**
     * 过期修改状态
     */
    public function editExpirevouchertype($condition) {
        $condition['end_time'] = array('lt', TIMESTAMP);
        
        // 更新商品促销价格
        $vouchertypegoods_list = Model('voucher_type_goods')->getvouchertypeGoodsList($condition);
        if (!empty($vouchertypegoods_list)) {
            $goodsid_array = array();
            foreach ($vouchertypegoods_list as $val) {
                $goodsid_array[] = $val['goods_id'];
            }
            // 更新商品促销价格，需要考虑抢购是否在进行中
            QueueClient::push('updateGoodsPromotionPriceByGoodsId', $goodsid_array);
        }
        $condition['state'] = self::vouchertype_STATE_NORMAL;
        
        $updata = array();
        $update['state'] = self::vouchertype_STATE_CLOSE;
        $this->editvouchertype($update, $condition);
        return true;
    }

}
