<?php
/**
 * 文件的简短描述
 *
 * 文件的详细描述
 *
 */
defined('In718Shop') or exit('Access Invalid!');

class noticeboardModel {
	/**
	 * 列表
	 *
	 * @param array $condition 检索条件
	 * @param obj $page 分页
	 * @return array 数组结构的返回结果
	 */
	public function getnoticeboardList($condition,$page){
		// $condition_str = $this->_condition($condition);
		$param = array();
		$param['table'] = 'noticeboard';
		$param['where'] = $condition_str;
		$param['order']	= $condition['order'] ? $condition['order'] : 'no_id';
		$result = Db::select($param,$page);
		return $result;
	}
	
	/**
	 * 构造检索条件
	 *
	 * @param int $id 记录ID
	 * @return string 字符串类型的返回结果
	 */
	private function _condition($condition){
		$condition_str = '';
		
		if ($condition['like_no_title'] != ''){
			$condition_str .= " and no_title like '%". $condition['like_no_title'] ."%'";
		}
		if ($condition['no_location'] != ''){
			$condition_str .= " and no_location = '". $condition['no_location'] ."'";
		}		
		
		return $condition_str;
	}
	
	/**
	 * 取单个内容
	 *
	 * @param int $id ID
	 * @return array 数组类型的返回结果
	 */
	public function getOnenoticeboard($id){
		if (intval($id) > 0){
			$param = array();
			$param['table'] = 'noticeboard';
			$param['field'] = 'no_id';
			$param['value'] = intval($id);
			$result = Db::getRow($param);
			return $result;
		}else {
			return false;
		}
	}
	
	/**
	 * 新增
	 *
	 * @param array $param 参数内容
	 * @return bool 布尔类型的返回结果
	 */
	public function add($param){
		if (empty($param)){
			return false;
		}
		if (is_array($param)){
			$tmp = array();
			foreach ($param as $k => $v){
				$tmp[$k] = $v;
			}
			$result = Db::insert('noticeboard',$tmp);
			return $result;
		}else {
			return false;
		}
	}
	
	/**
	 * 更新信息
	 *
	 * @param array $param 更新数据
	 * @return bool 布尔类型的返回结果
	 */
	public function update($param){
		if (empty($param)){
			return false;
		}
		if (is_array($param)){
			$tmp = array();
			foreach ($param as $k => $v){
				$tmp[$k] = $v;
			}
			$where = " no_id = '". $param['no_id'] ."'";
			$result = Db::update('noticeboard',$tmp,$where);
			return $result;
		}else {
			return false;
		}
	}
	
	/**
	 * 删除
	 *
	 * @param int $id 记录ID
	 * @return bool 布尔类型的返回结果
	 */
	public function del($id){
		if (intval($id) > 0){
			$where = " no_id = '". intval($id) ."'";
			$result = Db::delete('noticeboard',$where);
			return $result;
		}else {
			return false;
		}
	}	
}