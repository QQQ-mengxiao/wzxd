<?php
defined('In718Shop') or exit('Access Invalid!');
class promotion_goodsControl extends BaseControl{
         /**
     * 新增
     */
    public function xinrenOp(){
        $model_goodspromotion=Model('goods_promotion'); 
        $model_xinren_goods = Model('p_xinren_goods');
        $condition=array();
        // $condition['is_group_ladder']=3;//未同步
        // $condition['state']=1;
        $xinrengoods_list =$model_xinren_goods->getXinRenGoodsList($condition);
        // var_dump($xianshigoods);die;
        foreach ($xinrengoods_list as $key => $value) {
            $tmp=array();
            $tmp['goods_id']=$value['goods_id'];
            $tmp['price']=$value['xinren_price'];
            $tmp['promotion_type']=50;
            $tmp['promotion_type_id']=$value['xinren_goods_id'];
            $insert_arr[] = $tmp;
        }
        //批量增加
        $result=$model_goodspromotion->addallgoods_promotion($insert_arr);
       var_dump($result);die;
    }
    public function jimaiOp(){
        $model_goodspromotion=Model('goods_promotion'); 
        $buy_deliver_goods = Model('buy_deliver_goods');
        $condition=array();
        $condition['state']=1;//未同步
         $condition['buy_deliver_id']=10;//未同步
        // $condition['state']=1;
         $buy_deliver_goods_list =$buy_deliver_goods ->getBuyDeliverGoodsList($condition);
        // $xinrengoods_list =$model_xinren_goods->getXinRenGoodsList($condition);
        // var_dump($buy_deliver_goods_list);die;
        foreach ($buy_deliver_goods_list as $key => $value) {
                    $where=array();
                    $where['goods_id']=$value['goods_id'];
                    $where['promotion_type']=80;
                    $where['promotion_type_id']=10;
                    $goodsdeliver_info=$model_goodspromotion->getgoods_promotionInfo($where);
                    if(empty($goodsdeliver_info)){
                        $tmp=array();
                        $tmp['goods_id']=$value['goods_id'];
                        $tmp['price']=$value['goods_price'];
                         $tmp['promotion_type']=80;
                        $tmp['promotion_type_id']=$value['buy_deliver_id'];
                        $insert_arr[] = $tmp;
                    }
        }
        //批量增加
        $result=$model_goodspromotion->addallgoods_promotion($insert_arr);
       var_dump($result);die;
    }
    public function xinpinOp(){
        $model_goodspromotion=Model('goods_promotion'); 
        $model_goods = Model('goods');
        $condition=array();
        $condition['is_group_ladder']=7;//未同步//未同步
        $condition['is_deleted']=0;
        //  $condition['goods_id']=array('in',array(1111114,4111115,4111111114,411114,11111));//未同步//未同步
        // $condition['promotion_type']=88;
        //  $model_goodspromotion->delgoods_promotion($condition);die;
        $goods_list =$model_goods->getGoodsList($condition, 'goods_id,goods_price');
        foreach ($goods_list as $key => $value) {
            $where=array();
            $where['goods_id']=$value['goods_id'];
            $where['promotion_type']=40;
            $goodsxinpin_info=$model_goodspromotion->getgoods_promotionInfo($where);
            if(empty( $goodsxinpin_info)){
                $tmp=array();
                $tmp['goods_id']=$value['goods_id'];
                $tmp['price']=$value['goods_price'];
                $tmp['promotion_type']=40;
                $insert_arr[] = $tmp;
           }    
        }
        //批量增加
        $result=$model_goodspromotion->addallgoods_promotion($insert_arr);
       var_dump($result);die;
    }
 public function jietiOp(){
        $model_goodspromotion=Model('goods_promotion'); 
        $model_goods = Model('goods');
        $condition=array();
        $condition['is_group_ladder']=1;//未同步//未同步
        // $condition['state']=1;
        $goods_list =$model_goods->getGoodsList($condition, 'goods_id,goods_price,p_ladder_id');
        foreach ($goods_list as $key => $value) {
            $tmp=array();
            $tmp['goods_id']=$value['goods_id'];
            $tmp['price']=$value['goods_price'];
            $tmp['promotion_type']=60;
            $tmp['promotion_type_id']=$value['p_ladder_id'];
            $insert_arr[] = $tmp;
        }
        //批量增加
        $result=$model_goodspromotion->addallgoods_promotion($insert_arr);
       var_dump($result);die;
    }
  







}