<?php
/**
 * 商品前缀
 */
defined('In718Shop') or exit('Access Invalid!');
class prefixModel extends Model{

    public function __construct(){
        parent::__construct('prefix');
    }

	/**
     * 读取前缀商品列表
	 * @param array $condition 查询条件
	 * @param int $page 分页数
	 * @param string $order 排序
	 * @param string $field 所需字段
     * @return array 前缀商品列表
	 */
	public function getPrefixList($condition, $page=null, $order='', $field='*') {
        $prefix_list = $this->field($field)->where($condition)->page($page)->order($order)->select();
        return $prefix_list;
	}

    /**
	 * 根据条件读取前缀信息
	 * @param array $condition 查询条件
     * @return array 限时折扣信息
	 *
	 */
    public function getPrefixInfo($condition) {
        $prefix_info = $this->where($condition)->find();
        return $prefix_info;
    }


	/*
	 * 增加词条前缀
	 * @param array $param
	 * @return bool
     *
	 */
    public function addPrefix($param){
        return $this->insert($param);	
    }

    /*
	 * 更新词条前缀
	 * @param array $update
	 * @param array $condition
	 * @return bool
     *
	 */
    public function editPrefix($update, $condition){
        return $this->where($condition)->update($update);
    }

	/*
	 * 删除即买即送活动，同时删除即买即送商品
	 * @param array $condition
	 * @return bool
     *
	 */
    public function delPrefix($condition){
        return $this->where($condition)->delete();
    }
}
