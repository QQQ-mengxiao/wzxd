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
        $info = $model->table('voucher')->where(array('voucher_id'=>$value['voucher_id']))->field('voucher_t_id')->find();
         $TemplateInfo=$model->getVoucherTemplateInfo(array('voucher_t_id'=>$info['voucher_t_id']));
          if($TemplateInfo['voucher_t_state']==2){
            unset( $list[$key]);
          }else{
            $list[$key]['voucher_start_date']=date("Y-m-d H:i",$value['voucher_start_date']);
           $list[$key]['voucher_end_date']=date("Y-m-d H:i",$value['voucher_end_date']);
           $list[$key]['voucher_active_date']=date("Y-m-d H:i:s",$value['voucher_active_date']);
           $list[$key]['voucher_t_type']= $value['voucher_t_type'];
          }
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
     * 用户领取代金券
     */
     public function exchange_voucherOp(){
            $model = Model('voucher');
            $member_id =$_GET['member_id'];
            $voucher_t_id=$_GET['voucher_t_id'];
             if (empty( $member_id)){
                    $message='请登录';
                    $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                    echo json_encode($res,320);exit();
                }
             //验证是否可以兑换代金券
            $data = $model->getCanChangeTemplateInfo2($voucher_t_id,intval($member_id));
            if ($data['state'] == false){
                    $message=$data['msg'];
                    $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                    echo json_encode($res,320);exit();
            }
           $member_info = Model('member')->where(array('member_id' =>trim($member_id)))->find();
           $template_info= Model()->table('voucher_template')->where(array('voucher_t_id' =>$voucher_t_id))->find();
            $insert_arr = array();
            $insert_arr['voucher_code'] = $model->get_voucher_code($member_id);
            $insert_arr['voucher_t_id'] = $template_info['voucher_t_id'];
            $insert_arr['voucher_title'] = $template_info['voucher_t_title'];
            $insert_arr['voucher_desc'] = $template_info['voucher_t_desc'];
            //所发放代金券时用户得到的代金券有效起止日期
            if ($template_info['voucher_t_start_date']>time()) {
              $insert_arr['voucher_start_date'] =$template_info['voucher_t_start_date'];
            } else {
               $insert_arr['voucher_start_date']= time();
            }
           $d =$insert_arr['voucher_start_date']+$template_info['voucher_t_validity']*24*3600;
            if($template_info['voucher_t_validity']&&($d<=$template_info['voucher_t_end_date'])){
                $insert_arr['voucher_end_date'] = $d;
            }else{
                $insert_arr['voucher_end_date'] = $template_info['voucher_t_end_date'];
            }
            $insert_arr['voucher_price'] = $template_info['voucher_t_price'];
            $insert_arr['voucher_limit'] = $template_info['voucher_t_limit'];
            $insert_arr['voucher_store_id'] = $template_info['voucher_t_store_id'];
            $insert_arr['voucher_state'] = 1;
             $insert_arr['voucher_order_type'] = $template_info['voucher_t_ordertype'];
            $insert_arr['voucher_active_date'] = time();
            $insert_arr['voucher_owner_id'] = $member_info['member_id'];
            $insert_arr['voucher_owner_name'] = $member_info['member_name'];
            $insert_arr['voucher_gc_id'] = $template_info['voucher_t_gc_id'];
            $result =  Model()->table('voucher')->insert($insert_arr);
              //扣除会员积分
            if ($template_info['voucher_t_points'] > 0){
                $points_arr['pl_memberid'] = $member_id;
                $points_arr['pl_membername'] =  $member_info['member_name'];
                $points_arr['pl_points'] = -$template_info['voucher_t_points'];
                $points_arr['point_ordersn'] = $insert_arr['voucher_code'];
                $points_arr['pl_desc'] = '代金券'.$insert_arr['voucher_code'].'消耗积分';
                $result = Model('points')->savePointsLog('app',$points_arr,true);
                if (!$result){
                    $message='兑换失败';
                    $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                    echo json_encode($res,320);exit();
                }
            }
            if ($result){
                $result = $model->editVoucherTemplate(array('voucher_t_id'=>$template_info['voucher_t_id']), array('voucher_t_giveout'=>array('exp','voucher_t_giveout+1')));
                if (!$result){
                    $message='兑换失败';
                    $res = array('code'=>'200' , 'message'=>$message,'data'=>$result );
                    echo json_encode($res,320);exit();
                }
            } else {
                $message='兑换失败';
                $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);exit();
            }
        
            $message='兑换成功';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$result);
            echo json_encode($res,320);
       }
    /**
     * 可兑换代金券列表
     */
    public function voucher_listOp(){
       $recommend_voucher = Model('voucher')->getRecommendTemplate(10);
       foreach ($recommend_voucher as $key => $value) {
         $recommend_voucher[$key]['voucher_t_start_date']=date("Y-m-d H:i",$value['voucher_t_start_date']);
          $recommend_voucher[$key]['voucher_t_end_date']=date("Y-m-d H:i",$value['voucher_t_end_date']);
          if($value['voucher_t_display']!=0){
              unset($recommend_voucher[$key]);
          }
       }
        if($recommend_voucher){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$recommend_voucher);
            echo json_encode($res,320);
      }else{
            $message='fail';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
            echo json_encode($res,320);
      }
    }

 /**
     * 可兑换代金券列表
     */
    public function voucher_fafangOp(){
            $model_card= Model('card');
            $t_id =$_POST['voucher_t_id'];
            $gonghao =$_POST['gonghao'];
            $array_gonghao=explode(',', $gonghao);
            $num=count($array_gonghao);
            for ($i=0; $i < $num ; $i++) { 
             $card_info=$model_card->getMemberCardInfobygh($array_gonghao[$i]);
             // var_dump($card_info);die;
              $member_info=Model()->table('member_card')->where(array('cardno' =>$card_info['Sno']))->select();
              if(!empty($member_info)){
                $num2=count($member_info);
                if($num2>1){
                   for ($a=1; $a < $num2 ; $a++) { 
                     $fail_member_id[]=$member_info[$a]['member_id'];
                   }
                    $member_id[]=$member_info[0]['member_id'];
                }else{
                  $member_id[]=$member_info[0]['member_id'];
                }         
              }else{
                $no_member[]=$array_gonghao[$i];
              }
               
            }
            $data=array();
            $data['success']= $member_id;
            $data['fail']= $fail_member_id;
            $data['no_member']= $no_member;

            // var_dump($fail_member_id);die;
            $result = Model('voucher')->batch_sendVoucher($t_id,$member_id,$_POST['number']);
    if($result['state']==true){
            $message=$result['msg'];
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
            echo json_encode($res,320);
      }else{
            $message=$result['msg'];
            $res = array('code'=>'200' , 'message'=>$message,'data'=>$data);
            echo json_encode($res,320);
      }
    }
  /**
     * 到期代金券
     */
    public function linqi_voucherOp(){
        $model = Model('voucher');
        $where=array();
        $where['voucher_owner_id']=$_GET['member_id'];
        $time=time()+86400*3;//一天
        $where['voucher_end_date']=array('elt',$time);
         $where['voucher_state'] = 1;
         // var_dump($where);die;
        $list = Model()->table('voucher')->where($where)->select();
         foreach ($list as $k => $v) {
        if($v['voucher_end_date']<time()){
              unset($list[$k]);
          }
       }
        foreach ($list as $key => $value) {
          $list[$key]['voucher_start_date']=date("Y-m-d H:i",$value['voucher_start_date']);
          $list[$key]['voucher_end_date']=date("Y-m-d H:i",$value['voucher_end_date']);
           $list[$key]['voucher_active_date']=date("Y-m-d H:i:s",$value['voucher_active_date']);
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

}