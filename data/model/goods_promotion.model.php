<?php
/**
 * 裂变管理
 */
defined('In718Shop') or exit('Access Invalid!');

class goods_promotionModel extends Model{
    public function __construct(){
        parent::__construct('goods_promotion');
    }

    /**
     * 新增数据
     *
     */
    public function addgoods_promotion($insert) {
        $result = $this->table('goods_promotion')->insert($insert);
        return $result;
    }

    /**
     * 批量新增数据
     *
     */
    public function addallgoods_promotion($insert) {
        $result = $this->table('goods_promotion')->insertAll($insert);
        return $result;
    }
    /**
     * 新增会员价数据
     *
     */
    public function addgoods_promotion_vip($goods_id,$goods_price,$hui_discount) {
        $insertarr=array();
        if($hui_discount!=0){
            $array_goodspromotion=array();
            $array_goodspromotion['goods_id']=$goods_id;
            $array_goodspromotion['promotion_type']=30;
            $array_goodspromotion['price']=$goods_price*$hui_discount;
            $array_goodspromotion['member_levels']=0;
            $insert_arr[] = $array_goodspromotion;
        }
         $member_grade_setting = Model('setting')->getRowSetting('member_grade');
        $member_grade= $member_grade_setting['value']?unserialize($member_grade_setting['value']):array();
        foreach ($member_grade as $key => $value) {
            $tmp = array();
            $tmp['goods_id']=$goods_id;
            $tmp['promotion_type']=30;
            $tmp['price']=$goods_price*$value['discount']/100;
            $tmp['member_levels']=$key+1;
            $insert_arr[] = $tmp;
        }
        $result = $this->table('goods_promotion')->insertAll($insert_arr);
        return $result;
    }
     /**
     * 编辑会员价数据
     *
     */
    public function updategoods_promotion_vip($goods_id,$goods_price,$hui_discount) {
        $updatearr=array();
         if($hui_discount!=0){
            $array_goodspromotion=array();
            $array_goodspromotion['goods_id']=$goods_id;
            $array_goodspromotion['promotion_type']=30;
            $array_goodspromotion['price']=ncPriceFormat($goods_price*$hui_discount);
            $array_goodspromotion['member_levels']=0;
            $updatearr[] = $array_goodspromotion;
        }
        $member_grade_setting = Model('setting')->getRowSetting('member_grade');
        $member_grade= $member_grade_setting['value']?unserialize($member_grade_setting['value']):array();
        foreach ($member_grade as $key => $value) {
            $tmp = array();
            $tmp['goods_id']=$goods_id;
            $tmp['price']=ncPriceFormat($goods_price*$value['discount']/100);
            $tmp['member_levels']=$key+1;
            $updatearr[] = $tmp;
        }
           // var_dump($updatearr);die;
        foreach($updatearr as $k => $v) {
            $condition = array();
             $condition['goods_id']=$goods_id;
            $condition['promotion_type']=30;
            $condition['member_levels']= $v['member_levels'];
            $update=array();
            $update['price']=$v['price'];
            $result = $this->table('goods_promotion')->where($condition)->update($update);
        }
        return $result;
    }

    /**
     * 获取一条数据
     */
    public function getgoods_promotionInfo($condition, $field = '*'){
            return $this->table('goods_promotion')->field($field)->where($condition)->find();
    }

    /**
     * 更新数据
     */
    public function editgoods_promotion($update,$condition){
            $result = $this->table('goods_promotion')->where($condition)->update($update);
            return $result;
    }
     /**
     * 删除数据
     */
    public function delgoods_promotion($condition){
              
            $result =$this->where($condition)->delete();
            // var_dump($_SERVER['HTTP_REFERER']);die;
            if(!empty($condition['goods_promotion_id'][1])){
                foreach ($condition['goods_promotion_id'][1] as $key => $value) {
               file_put_contents('/data/default/wzxd/qlog/delgoods_p.log', date("Y-m-d H:i:s",time()).'goods_promotion_id'. $value." \n", FILE_APPEND);
            }
            }
            
    file_put_contents('/data/default/wzxd/qlog/delgoods_p.log', date("Y-m-d H:i:s",time()).'goods-id:'. $condition['goods_id'].'-goods_promotion_id:'.count($condition['goods_promotion_id'][1])."-类型：".$condition['promotion_type'].'-allgoods_id--'.implode(',', $condition['goods_id'][1]) .'-路径-'.$_SERVER['HTTP_REFERER']." \n", FILE_APPEND);
            return $result;
    }

    /**
     * 获取列表
     */
    public function getgoods_promotionList($condition,  $field = '*') {
        return $this->table('goods_promotion')->field($field)->where($condition)->select();
    }
    /**
     * 获取列表
     */
    public function getgoods_promotion_lowest($condition,  $field = '*',$limit=1) {
        return $this->table('goods_promotion')->field($field)->where($condition)->order('price asc')->limit($limit)->select();
    }

    /**
     * 获取个数
     */
    public function getgoods_promotionCount($condition) {
        return $this->table('goods_promotion')->where($condition)->count();
    }
        /**
     * 更新即买即送和新品的价格
     */
    public function update_changeprice($goods_id,$goods_price) {
        //即买即送更新
        $array_goodspromotion=array();
        $array_goodspromotion['goods_id']=$goods_id;
        $array_goodspromotion['promotion_type']=80;
        $jimai_info=$this->getgoods_promotionInfo($array_goodspromotion);
        if(!empty($jimai_info)){
            $update_goodspromotion=array();
            $update_goodspromotion['price']=$goods_price;
            $this->editgoods_promotion($update_goodspromotion,$array_goodspromotion);
        // }else{
        //      $array_goodspromotion['price']=$goods_price;
        //      $this->addgoods_promotion($array_goodspromotion);
        }
        $array_goodspromotion['promotion_type']=40;
        $xinpin_info=$this->getgoods_promotionInfo($array_goodspromotion);
        //获取新品规则，更改goods_common表
        $model_setting = Model('setting');
        $goods_show_discount = $model_setting->getRowSetting('goods_show_discount');
        if(!empty($xinpin_info)){
            $update_goodspromotion=array();
            $update_goodspromotion['price']=$goods_show_discount['value']*$goods_price/100;
            $this->editgoods_promotion($update_goodspromotion,$array_goodspromotion);
        // }else{
        //      $array_goodspromotion['price']=$goods_show_discount['value']*$goods_price/100;
        //      $this->addgoods_promotion($array_goodspromotion);
        }
    }
	
}
