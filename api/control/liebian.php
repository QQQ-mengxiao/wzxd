<?php

defined('In718Shop') or exit('Access Invalid!');
class liebianControl extends BaseControl{  
  /* 链接测试
  */
  public function testOp(){
      echo $this->returnMsg(10000, '请求成功！', '');exit;
  }

  /**
  分享裂变
  */
  public function fenlieOp(){
    //店铺ID
    $store_id = $_GET['store_id'];
    //发放人ID
    $member_id = $_GET['member_id'];
    $model_member = Model('member');
    $condition=array();
    $condition['store_id']=$store_id;
    $condition['is_use'] = 1;
    $condition['type'] = 0;
    $order='add_time desc';
    $p_voucher = Model('p_fenxiang');
    $p_voucher_rule = Model('p_fenxiang_rule');
    $fenlie_voucher_list = $p_voucher->table('p_fenxiang')->where($condition)->order($order)->find();
    //print_r($fenlie_voucher_list);
    $member_list = $model_member->getMemberInfoByID($member_id);
    //print_r($member_list);
    $rule_list = $p_voucher_rule->table('p_fenxiang_rule')->where(array('p_fenxiang_id'=>$fenlie_voucher_list['p_fenxiang_id']))->select();
    //print_r($rule_list );
    foreach ($rule_list  as $k => $v) {
      $voucher = Model('voucher');
      $template_info=$voucher->table('voucher_template')->where(array('voucher_t_id' =>$v['voucher_t_id']))->find();
      for($i=0;$i<=$v['count']-1;$i++){
        //验证是否可以领取代金券
        $voucher_t_id=$template_info['voucher_t_id'];
        $data = $voucher->getCanChangeTemplateInfo3($voucher_t_id,intval($member_id));
        //print_r( $data);
        if ($data['state'] == ture){              
            $insert_arr = array();
            $insert_arr['voucher_code'] = $voucher->get_voucher_code($member_list['member_id']);
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
            $insert_arr['voucher_order_type'] = $template_info['voucher_t_order_type'];
            $insert_arr['voucher_active_date'] = time();
            $insert_arr['voucher_owner_id'] = $member_list['member_id'];
            $insert_arr['voucher_owner_name'] = $member_list['member_name'];;
            $insert_arr['voucher_gc_id'] = $template_info['voucher_t_gc_id'];
            // $insert_arr['voucher_is_xinren'] = $template_info['voucher_t_is_xinren'];
            //print_r($insert_arr);die;
            $result= $voucher->table('voucher')->insert($insert_arr);
            // var_dump($insert_arr);die;   
          }
      }
      if ($result){
        $result1 =  $voucher->editVoucherTemplate(array('voucher_t_id'=>$template_info['voucher_t_id']), array('voucher_t_giveout'=>array('exp','voucher_t_giveout + '.$v['count'])));
        if (!$result1){
          echo $this->returnMsg(10002, '发放失败2!', array('member_id'=>$member_id));exit;
        }
      } else {
        echo $this->returnMsg(10003, '发放失败!', array('member_id'=>$member_id));exit;
      }
      $voucher_msg[]=$template_info['voucher_t_title'];
    }
    echo $this->returnMsg(10001, '发放成功!', array('voucher'=> $voucher_msg));exit;
  } 
 
}