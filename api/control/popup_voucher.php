<?php
defined('In718Shop') or exit('Access Invalid!');
class popup_voucherControl extends BaseControl{
      //每页显示商品数
    const PAGESIZE = 10;
    //页数
    const PAGENUM = 1;
    
    /* 新人专享代金券列表
    */
    public function voucher_listOp(){
      $recommend_voucher = Model('voucher')->getRecommendTemplate2();
       foreach ($recommend_voucher as $key => $value) {
         $recommend_voucher[$key]['voucher_t_start_date']=date("Y-m-d H:i",$value['voucher_t_start_date']);
          $recommend_voucher[$key]['voucher_t_end_date']=date("Y-m-d H:i",$value['voucher_t_end_date']);
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
     /* 领取新人券
    */
    public function voucher_lingquOp(){
            $model = Model('voucher');
            $member_id =$_GET['member_id'];
            $member_info = Model('member')->where(array('member_id' =>trim($member_id)))->find();
            if( $member_info['is_xinren']==1&&$member_info['is_new']==1){
               $recommend_voucher = Model('voucher')->getRecommendTemplate2();
              foreach ($recommend_voucher as $key => $value) {
                              $voucher_t_id=$value['voucher_t_id'];
                //验证是否可以领取代金券
                $data = $model->getCanChangeTemplateInfo3($voucher_t_id,intval($member_id));
                if ($data['state'] == false){
                        $message=$data['msg'];
                        $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                        echo json_encode($res,320);exit();
                }
               
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
                $insert_arr['voucher_owner_name'] = $member_info['member_name'];;
                $insert_arr['voucher_gc_id'] = $template_info['voucher_t_gc_id'];
                $result =  Model()->table('voucher')->insert($insert_arr);
              }
            }else{
               $message='已领取过新人券';
                        $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                        echo json_encode($res,320);exit();
            }
            

            if ($result){
                $result = $model->editVoucherTemplate(array('voucher_t_id'=>$template_info['voucher_t_id']), array('voucher_t_giveout'=>array('exp','voucher_t_giveout+1')));
                if (!$result){
                    $message='领取失败';
                    $res = array('code'=>'200' , 'message'=>$message,'data'=>$result );
                    echo json_encode($res,320);exit();
                }
            } else {
                $message='领取失败';
                $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);exit();
            }
            $model_member=Model('member');
            $model_member->editMember(array('member_id'=> $member_id),array('is_new'=>0));
            $message='领取成功';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$result);
            echo json_encode($res,320);
    }
      
}