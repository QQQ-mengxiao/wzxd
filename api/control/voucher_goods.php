<?php
defined('In718Shop') or exit('Access Invalid!');
class voucher_goodsControl extends BaseControl{

    /**
     * 商品详情页此商品可兑换代金券列表
     */
    public function voucher_listOp(){
        $goods_id=$_GET['goods_id'];
        $where = array();
        $where['voucher_t_state'] = 1;
        $where['voucher_t_end_date'] = array('gt',time());
        $where['voucher_t_type'] = array('gt',0);
        $recommend_voucher = Model('voucher')->getVoucherTemplateList($where, $field = '*', 0, 0, 'voucher_t_recommend desc,voucher_t_id desc');
        foreach ($recommend_voucher as $key => $value) {
           $recommend_voucher[$key]['voucher_t_start_date']=date("Y-m-d H:i",$value['voucher_t_start_date']);
           $recommend_voucher[$key]['voucher_t_end_date']=date("Y-m-d H:i",$value['voucher_t_end_date']);
           $condition=array();
           $voucher_type = Model('voucher_type');
           $condition['voucher_tid']= $value['voucher_t_id'];
           $type_info=$voucher_type->getvouchertypeInfo($condition);
           if($type_info['type']==1){
                $model_goods = Model('goods');
                $goods_info = $model_goods->getGoodsInfoByID($goods_id);
                // var_dump($goods_info);die;
                $goodsclass_arr=explode(',', $type_info['goodsclass_id']);
                if($type_info['is_use']==1){
                    if(!in_array($goods_info['gc_id_3'], $goodsclass_arr)||empty($goods_info)){
                       unset($recommend_voucher[$key]);
                    }
                }else{
                    if(in_array($goods_info['gc_id_3'], $goodsclass_arr)||empty($goods_info)){
                       unset($recommend_voucher[$key]);
                    }
                }           
           }else{
                $goods_arr=explode(',', $type_info['goods_id']);
                
                if($type_info['is_use']==1){
                    if(!in_array($goods_id, $goods_arr)){
                       unset($recommend_voucher[$key]);
                     }
                }else{
                    if(in_array($goods_id, $goods_arr)){
                    unset($recommend_voucher[$key]);
                }
                }       
           }
       }
        if($recommend_voucher){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$recommend_voucher);
            echo json_encode($res,320);
      }else{
            $message='empty';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
            echo json_encode($res,320);
      }
    }

 


}