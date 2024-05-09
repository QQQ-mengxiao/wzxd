<?php
/**
 * 商品品牌模型
 **/
defined('In718Shop') or exit('Access Invalid!');

class sharepicModel extends Model {
    public function __construct() {
        parent::__construct('sharepic');
    }
    
    /**
     * 添加品牌
     * @param array $insert
     * @return boolean
     */
    public function addsharepic($insert) {
        return $this->insert($insert);
    }
    
    /**
     * 编辑品牌
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editsharepic($condition, $update) {
        return $this->where($condition)->update($update);
    }
    
    /**
     * 删除品牌
     * @param unknown $condition
     * @return boolean
     */
    public function delsharepic($condition) {
        $sharepic_array = $this->getsharepicList($condition, 'sharepic_id,share_pic');
        $sharepicid_array = array();
        foreach ($sharepic_array as $value) {
            $sharepicid_array[] = $value['sharepic_id'];
            @unlink(BASE_UPLOAD_PATH.DS.ATTACH_SHAREPIC.DS.$value['share_pic']);
        }
        return $this->where(array('sharepic_id' => array('in', $sharepicid_array)))->delete();
    }
    
    /**
     * 查询品牌数量
     * @param array $condition
     * @return array
     */
    public function getsharepicCount($condition) {
        return $this->where($condition)->count();
    }
    
    /**
     * 品牌列表
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param number $page
     * @param string $limit
     * @return array
     */
    public function getsharepicList($condition, $field = '*', $page = 0, $order = 'sharepic_id desc', $limit = '') {
        return $this->where($condition)->field($field)->order($order)->page($page)->limit($limit)->select();
    }
    
    /**
     * 通过的品牌列表
     * 
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getsharepicPassedList($condition, $field = '*', $page = 0, $order = 'sharepic_sort asc, sharepic_id desc', $limit = '') {
        $condition['sharepic_apply'] = 1;
        return $this->getsharepicList($condition, $field, $page, $order, $limit);
    }
    
    /**
     * 未通过的品牌列表
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getsharepicNoPassedList($condition, $field = '*', $page = 0) {
        $condition['sharepic_apply'] = 0;
        return $this->getsharepicList($condition, $field, $page);
    }
    
    /**
     * 取单个品牌内容
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getsharepicInfo($condition, $field = '*') {
        return $this->field($field)->where($condition)->find();
    }
}