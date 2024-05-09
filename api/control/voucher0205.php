<?php
defined('In718Shop') or exit('Access Invalid!');
class voucherControl extends BaseControl{
         /**
     * 新增配送地址
     */
    public function my_voucherOp(){
        $model = Model('voucher');
        $member_id =$_GET['member_id'];
        $list = $model->getMemberVoucherList($member_id, $_GET['state']);//代金券状态(1-未用,2-已用,3-过期,4-收回)
        // var_dump($list);die;
        foreach ($list as $key => $value) {
          $list[$key]['voucher_start_date']=date("Y-m-d H:i:s",$value['voucher_start_date']);
          $list[$key]['voucher_end_date']=date("Y-m-d H:i:s",$value['voucher_end_date']);
        }
      //取已经使用过并且未有voucher_order_id的代金券的订单ID
      $used_voucher_code = array();
      $voucher_order = array();
      if (!empty($list)) {
          foreach ($list as $v) {

              if ($v['voucher_state'] == 2 && empty($v['voucher_order_id'])) {
                  $used_voucher_code[] = $v['voucher_code'];
              }
          }
      }
        if (!empty($used_voucher_code)) {
            $order_list = Model('order')->getOrderCommonList(array('voucher_code'=>array('in',$used_voucher_code)),'order_id,voucher_code');
            if (!empty($order_list)) {
                foreach ($order_list as $v) {
                    $voucher_order[$v['voucher_code']] = $v['order_id'];
                    $model->editVoucher(array('voucher_order_id'=>$v['order_id']),array('voucher_code'=>$v['voucher_code']));
                }
            }
        }
        if($list){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$list);
            echo json_encode($res,320);
      }else{
            $message='fail';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
            echo json_encode($res,320);
      }
    }
      /**
     * 新增配送地址
     */
     public function get_voucherOp(){
           $model_daddress = Model('ziti_address');
           $member_id =$_GET['member_id'];
           $condition = array();
           // $condition['store_id'] =  $store_id;
           $address_list = $model_daddress->getAddressList($condition);
           $member_info = Model('member')->getMemberInfo(array('member_id'=>$member_id));
           $address_id=$member_info['ziti_id'];
           foreach ($address_list as $key => $value) {   
               if($member_info['ziti_id']==$value['address_id']){
                $address_list[$key]['is_default']=1;
               }else{
                 $address_list[$key]['is_default']=0;
               }          
            }
        if(!empty($address_list)){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$address_list);
            echo json_encode($res,320);
        }else{
                $message='fail';
                $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);
        }
    }





}