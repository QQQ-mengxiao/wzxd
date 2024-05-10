<?php
defined('In718Shop') or exit('Access Invalid!');
class LoginControl extends BaseControl{

   public function loginOp() {
      	$code=$_POST['code'];
        $share_id=$_POST['share_id'];
      	$user_info=$_POST['user_info'];
      	$user_info=str_replace("&quot;",'"',$user_info);
      	$user_info =  json_decode($user_info,true);
      	// var_dump($user_info);die;
      	 // echo json_encode($user_info,320);die;
   	    $payment_code = 'wxpay_jsapi';
        $condition = array();
        $condition['payment_code'] = $payment_code;
        $payment_info = Model()->table('mb_payment')->where($condition)->find();
        $payment_info = unserialize($payment_info['payment_config']);
        $APPID=$payment_info['appId'];//小程序id
        $appSecret=$payment_info['appSecret'];
        // if($_POST['test']==1){
        //        var_dump($payment_info);die;
        // }
        // $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $url = 'https://sh.api.weixin.qq.com/sns/jscode2session';
        
        $result = json_decode($this->curl($url, [
            'appid' => $APPID,
            'secret' => $appSecret,
            'grant_type' => 'authorization_code',
            'js_code' => $code
        ]), true);
        if($_POST['test']==1){
               var_dump($result);die;
        }
        if (isset($result['errcode'])) {
            $message=$result['errmsg'];
            $res = array('code'=>'200' , 'message'=>$message,'data'=>$data);
             echo json_encode($res,320);die;
        }
        $member_wxopenid = $result['openid'];
        // $member_wxopenid = 'opVrJw_x9cvPT4K-kGvcQ2shf2SA';
        $member_info = Model('member')->getMemberInfo(array('member_wxopenid'=>$member_wxopenid));
        if($member_info){
             if( $member_info['is_xinren']==1&&$member_info['is_new']==1){
                $this->voucher_xinren($member_info['member_id']);
            }
            $headimgurl = $user_info['avatarUrl'];//用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像）
            // $headimgurl = substr($headimgurl, 0, -1).'132';
            $member_id=$member_info['member_id'];
            if(empty($member_info['member_avatar'])){
                 $avatar = @copy($headimgurl,BASE_UPLOAD_PATH.'/'.ATTACH_AVATAR."/avatar_$member_id.jpg");
            }
           
            $member_info['member_avatar']=UPLOAD_SITE_URL.DS.ATTACH_AVATAR.DS.'avatar_'.$member_id.'.jpg';
            $member_info['member_name']=$user_info['nickName'];
            $message='login sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$member_info);
            echo json_encode($res,320);die;
        }else{
        	$user_info['open_id']=$member_wxopenid;
        	$result=$this->register($user_info,$share_id);
            if( $result['is_xinren']==1&&$result['is_new']==1){
                $this->voucher_xinren($result['member_id']);
                //给分享着发新人分享劵
                if(!empty($result['share_id'])){
                    $this->liebian_xinren($result['share_id']);
                }
            }
            if($result){
                 $result['member_avatar']=UPLOAD_SITE_URL.DS.ATTACH_AVATAR.DS.$result['member_avatar'];
	            $res = array('code'=>'100' , 'message'=>$message,'data'=>$result);
	             echo json_encode($res,320);
            }else{
            	$message='用户信息更新失败';
                $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);
            }
        }
    }
     /**
     * 注册
     */
    public function register($user_info,$share_id){
        $openid = $user_info['open_id'];
        $nickname = $user_info['nickName'];
              	// var_dump($user_info);die;
        if(!empty($openid)) {
            $rand = rand(100, 899);
            if(strlen($nickname) < 3) $nickname = $nickname.$rand;
            $member_name = $nickname;
            $model_member = Model('member');
            $member_info = $model_member->getMemberInfo(array('member_name'=> $member_name));
            $member = array();
            $member['member_email'] = '';
            $member['member_wxopenid'] = $openid;
            $member['member_name'] = $member_name;
            $member['share_id'] = $share_id;
            if(!empty($share_id)){
                $member['is_liebian'] = 1;
            }else{
                $member['is_liebian'] = 0;
            }
            $result = $model_member->addMember($member);

            $headimgurl = $user_info['avatarUrl'];//用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像）
            // $headimgurl = substr($headimgurl, 0, -1).'132';
            $avatar = @copy($headimgurl,BASE_UPLOAD_PATH.'/'.ATTACH_AVATAR."/avatar_$result.jpg");
            if($avatar) {
                $model_member->editMember(array('member_id'=> $result),array('member_avatar'=> "avatar_$result.jpg"));
            }
            $member = $model_member->getMemberInfo(array('member_id'=> $result));
            if(!empty($member)) {
                return $member;
            }
        }
    }

		 /**
		 * curl请求指定url
		 * @param $url
		 * @param array $data
		 * @return mixed
		 */
		public  function curl($url, $data = [])
		{
		    // 处理get数据
		    if (!empty($data)) {
		        $url = $url . '?' . http_build_query($data);
		    }

		    $curl = curl_init();
		    curl_setopt($curl, CURLOPT_URL, $url);
		    curl_setopt($curl, CURLOPT_HEADER, false);
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
		    $result = curl_exec($curl);
		    curl_close($curl);
		    return $result;
		}
                     /* 领取新人券
    */
    private function voucher_xinren($member_id){
            $model = Model('voucher');
            $member_info = Model('member')->where(array('member_id' =>trim($member_id)))->find();
               $recommend_voucher = Model('voucher')->getRecommendTemplate2();
               if(!empty($recommend_voucher)){
                    foreach ($recommend_voucher as $key => $value) {
                         $voucher_t_id=$value['voucher_t_id'];
                        //验证是否可以领取代金券
                        $data = $model->getCanChangeTemplateInfo3($voucher_t_id,intval($member_id));
                        if ($data['state'] == ture){              
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
                    }
                }
            if ($result){
                $result = $model->editVoucherTemplate(array('voucher_t_id'=>$template_info['voucher_t_id']), array('voucher_t_giveout'=>array('exp','voucher_t_giveout+1')));
            }
            $model_member=Model('member');
            $model_member->editMember(array('member_id'=> $member_id),array('is_new'=>0));
    }
     /* 裂变新人劵
    */
    private function liebian_xinren($member_id){
        //发放人ID
        $member_id = $member_id;
        $model_member = Model('member');
        $condition=array();
        $condition['is_use'] = 1;
        $condition['type'] = 1;
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
                // if (!$result1){
                //   echo $this->returnMsg(10002, '发放失败2!', array('member_id'=>$member_id));exit;
                // }
            }
          //  else {
          //   echo $this->returnMsg(10003, '发放失败!', array('member_id'=>$member_id));exit;
          // }
          // $voucher_msg[]=$template_info['voucher_t_title'];
        }
    // echo $this->returnMsg(10001, '发放成功!', array('voucher'=> $voucher_msg));exit; 
    }
}