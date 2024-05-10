<?php
/**
 * APP分销
 */
defined('In718Shop') or exit('Access Invalid!');

class fenxiaoModel extends Model {
    public function __construct(){
        parent::__construct('fenxiao');
    }

    /*
     * 获取数据
     */
    public function dataQuery($field = '*'){
        $data = $this->field($field)->find();
        if($data['fenxiao_con']==2){
            $data['fenxiao_con_val'] = implode(';',unserialize($data['fenxiao_con_val']));
        }
        return $data;
    }

    /*
     * 更新数据
     */
    public function dataUpdate($data = []){
        //exit(print_r($data));
        //var_dump($data);die;
        $res = $this->where(array('id'=>1))->update($data);
        if(!$res){
            showMessage('保存失败','','html','error');
        }
        return $res;
    }
    /*
     *  获取列表
     */
    public function getfenxiaotypeList($condition, $field = '*') {
        $type = $this->table('fenxiao_type')->field($field)->where($condition)->order('fenxiao_type_id asc')->limit(false)->select();
        return $type;
    }
        /*
     *  获取分销
     */
/*    public function getfenxiaoList($field = '*') {
        $type = $this->table('fenxiao')->field($field)->select();
        return $type;
    }*/

        /**
     * 获取分销信息
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getfenxiaoList($field = '*') {
        return $this->table('fenxiao')->field($field)->find();
    }

}