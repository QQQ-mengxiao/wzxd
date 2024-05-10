<?php
/**
 * 团长模型
 **/
defined('In718Shop') or exit('Access Invalid!');
class background_colorModel extends Model
{
    /**
     * 获取全部列表
     */
    public function getAllBackgroundColorList(){
        return $this->table('background_color')->select();
    }

    /**
     * 通过id获取背景色
     */
    public function getColorById($id){
        return $this->table('background_color')->field('color')->where(['id'=>$id])->find();
    }

    /**
     * 编辑背景色
     */
    public function editBackgroundColor($update, $where){
        return $this->table('background_color')->where($where)->update($update);
    }

}
