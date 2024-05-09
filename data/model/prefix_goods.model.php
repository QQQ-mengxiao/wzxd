<?php
/**
 * 前缀商品模型 
 **/
defined('In718Shop') or exit('Access Invalid!');
class prefix_goodsModel extends Model{

    public function __construct(){
        parent::__construct('prefix_goods');
    }
    
    /**
     * 商品列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @param int $limit 个数限制
     * @return array 即买即送商品列表
     *
     */
    public function getPrefixGoodsExtendList($condition, $page=null, $order='', $field='*', $limit = 0) {
        $prefix_goods_list = $this->getPrefixGoodsList($condition, $page, $order, $field, $limit);
        if(!empty($prefix_goods_list)) {
            for($i=0, $j=count($prefix_goods_list); $i < $j; $i++) {
                $prefix_goods_list[$i] = $this->getPrefixGoodsExtendInfo($prefix_goods_list[$i]);
            }
        }
        return $prefix_goods_list;
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
     
    public function getPrefixGoodsList($condition, $page=null, $order='', $field='*', $limit = 0) {
        return $prefix_goods_list = $this->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
    }


    /**
     * 获取即买即送商品扩展信息
     * @param array $xianshi_info
     * @return array 扩展限时折扣信息
     *
     */
    public function getPrefixGoodsExtendInfo($prefix_goods_info) {
        $storage_arr = $this->table('goods_common')->field('goods_name,goods_serial')->where(array('goods_commonid' => $prefix_goods_info['goods_commonid']))->find();
        $prefix_goods_info['goods_name'] = $storage_arr['goods_name'];
        $prefix_goods_info['goods_serial'] = $storage_arr['goods_serial'];
        return $prefix_goods_info;
    }

    /**
     * 增加前缀商品
     * @param array $xianshi_goods_info
     * @return bool
     *
     */
    public function addPrefixGoods($prefix_goods_info){
        $prefix_goods_id = $this->insert($prefix_goods_info);        
        return $prefix_goods_id;
    }

    /**
     * 根据条件读取即买即送商品信息
     * @param array $condition 查询条件
     * @return array 限时折扣商品信息
     *
     */
    public function getPrefixGoodsInfo($condition) {
        $result = $this->where($condition)->find();
        return $result;
    }

	/**
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     *
     */
     public function editPrefixGoods($update, $condition){
         $result = $this->table('prefix_goods')->where($condition)->update($update);
         return $result;
     }

     /**
      * 删除
      * @param  [type] $condition [description]
      * @return [type]            [description]
      */
     public function delPrefixGoods($condition)
     {
        $result = $this->table('prefix_goods')->where($condition)->delete();
        return $result;
     }

     /**
      * 获取商品名称
      * @param  [type] $condition [description]
      * @return [type]            [description]
      */
     public function getGoodsName($condition)
     {
         $goods_info = $this->table('goods')->field('goods_name')->where($condition)->find();
         return $goods_info['goods_name'];
     }
}
