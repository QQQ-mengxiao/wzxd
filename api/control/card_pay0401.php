<?php
defined('In718Shop') or exit('Access Invalid!');
class card_payControl extends BaseControl{
         /**
     * 新增或者修改支付密码
     */
    public function member_passwardOp(){
            $model_member = Model('member');
            $password=$_GET['password'];
            $member_id= $_GET['member_id'];
            if(strlen($password)!=6) {
                $message='支付密码填写异常';
                $res = array('code'=>'200' , 'message'=>$message,'data'=>$password );
                echo json_encode($res,320);exit();
              }
            $password=md5($_GET['password']);
            $data=$model_member->editMember(array('member_id'=> $member_id),array('member_paypwd'=>$password ));
        if($data){
            $message='sucess';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
            echo json_encode($res,320);
      }else{
            $message='fail';
            $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
            echo json_encode($res,320);
      }
    }

   
        /**
     * 验证支付密码是否正确
     */
    public function check_pwdOp(){
            $member_id= $_GET['member_id'];
            $member_info = Model('member')->getMemberInfo(array('member_id'=>$member_id));
            if (empty($_GET['password'])){
            $message='支付密码输入';
            $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
            echo json_encode($res,320);exit();
              }
        $buyer_info = Model('member')->getMemberInfoByID($member_id,'member_paypwd');
        // if($buyer_info['member_paypwd'] === md5($_GET['password'])){
        //     //添加日志
        //     Model('card_balance_log')->addcard_balance_log($_SESSION['member_id'],'zihpay');
        // }
        if($buyer_info['member_paypwd'] != '' && $buyer_info['member_paypwd'] === md5($_GET['password'])) {
           
                //     //添加日志
            //Model('card_balance_log')->addcard_balance_log($member_id,'zihpay');          
            $message='密码正确';
            $res = array('code'=>'100' , 'message'=>$message,'data'=>$_GET['password']);
            echo json_encode($res,320);
      }else{
            $message='支付密码错误';
            $res = array('code'=>'300' , 'message'=>$message,'data'=>$_GET['password']);
            echo json_encode($res,320);
      }
    }
   /**
     * g根据工号查询手机号
     */
        public function is_membercardOp(){
            $member_info=Model()->table('member_card')->where(array('member_id' => $_GET['member_id']))->find();
            if(!empty($member_info)){
              $message='已绑定';
              // $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
              $res = array('code'=>'100' , 'message'=>$message,'data'=>'');
              echo json_encode($res,320);
            }else{
              $message='未绑定';
              $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
              echo json_encode($res,320);
            }
          }
     /**
     * g根据工号查询手机号
     */
        public function getphoneby_ghOp(){
            $member_model= Model('member');
            $model_card= Model('card');
            // $member_id= $_GET['member_id'];
            $gonghao= $_GET['gonghao'];
            // $phone= $_GET['phone'];
            $card_info=$model_card->getMemberCardInfobygh($gonghao);
            if (empty($card_info)){
                $message='此工号不存在';
                $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
                echo json_encode($res,320);exit();
              }
            //调取接口根据工号查询手机号
              // $url='http://117.159.2.168:8083/wzxdplus/card/phone';
              // $url='http://219.157.200.45:8086/wzxdplus/card/phone';
              $url='http://10.10.11.35:8086/wzxdplus/card/phone';
             $res = json_decode($this->curl($url, ['jobNum' => $gonghao]), true);
             // var_dump($res);die;
            if( $res['state']==0){
              $data=  $res['data']['phone']; 
              $message='success';
              $res = array('code'=>'100' , 'message'=>$message,'data'=>$data);
              echo json_encode($res,320);
            }else{
              $message='fail';
              $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
              echo json_encode($res,320);
            }
          }
    public function member_addOp(){
            $member_model= Model('member');
            $model_card= Model('card');
            $member_id= $_GET['member_id'];
            $phone= $_GET['phone'];
             $gonghao= $_GET['gonghao'];
            $auth_code= $_GET['auth_code'];
            $card_info=$model_card->getMemberCardInfobygh($gonghao);
            $member_info=Model()->table('member_card')->where(array('member_id' =>$member_id))->find();
            if (empty($card_info)|| !empty($member_info)){
              $message='此工号不存在或已绑定';
              $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
              echo json_encode($res,320);exit();
              }
            $condition=array();
            $condition['cardno'] = $card_info['Sno'];
            $list=Model()->table('member_card')->where($condition)->find();
            if (!empty($list)){
              $message='此工号豫卡通已绑定';
              $res = array('code'=>'200' , 'message'=>$message,'data'=>'' );
              echo json_encode($res,320);exit();
            }
            $res=$this->auth( $member_id, $auth_code);
            if($res){
              $sno=$card_info['Sno'];//一卡通卡号
              $model=Model();
              $insert_array = array();
              $insert_array['member_id']  = $member_id;
              $insert_array['cardno'] =   $sno;
              $insert_array['status'] = 1;
              $insert_array['gonghao'] = $gonghao;
              $result = $model->table('member_card')->insert($insert_array);
              $member_model->editMember(array('member_id'=> $member_id),array('member_mobile'=>$phone));
               //插入绑定日志表
              $insert=array();
              $insert['member_id']=$member_id;
              $insert['log_time']=time();
              $insert['log_desc']='新增绑定卡号'.$insert_array['cardno'];
              $insert['log_admin']=$member_id;
              $result1=$model->table('member_card_log')->insert($insert);
              if($result){
                  $message='success';
                  $res = array('code'=>'100' , 'message'=>$message,'data'=>'' );
                  echo json_encode($res,320);exit();
              }else{
                   $message='绑定失败';
                  $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
                  echo json_encode($res,320);exit();
              }
            }else{
              $message='验证码验证失败';
              $res = array('code'=>'300' , 'message'=>$message,'data'=>'' );
              echo json_encode($res,320);
           }
    }

  /**
       * 统一发送身份验证码
       */
  public function send_auth_codeOp() {
         $member_id= $_GET['member_id'];
              $phone= $_GET['phone'];
              // var_dump($phone);die;
      // private function send_auth_code($member_id,$phone) {
          $model_member = Model('member');
          $member_info = $model_member->getMemberInfoByID($member_id,'member_email,member_mobile');

          $verify_code = rand(100,999).rand(100,999);
          $data = array();
          $data['auth_code'] = $verify_code;
          $data['send_acode_time'] = TIMESTAMP;
          $update = $model_member->editMemberCommon($data,array('member_id'=>$member_id));
          if (!$update) {
               $message='系统发生错误';
              $res = array('code'=>'200' , 'message'=>$message,'data'=>$data);
              echo json_encode($res,320);
          }

          $model_tpl = Model('mail_templates');
          $tpl_info = $model_tpl->getTplInfo(array('code'=>'authenticate'));

          $param = array();
          $param['send_time'] = date('Y-m-d H:i',TIMESTAMP);
          $param['verify_code'] = $verify_code;
          $param['site_name'] = C('site_name');
          $subject = ncReplaceText($tpl_info['title'],$param);
          $message = ncReplaceText($tpl_info['content'],$param);
              $sms = new Sms();
              $result = $sms->send($phone,$message);
              // var_dump($result);die;
          if ($result['code'] == 0) {
               $message='验证码发送成功';
                  $res = array('code'=>'100' , 'message'=>$result['msg'],'data'=>'');
                  echo json_encode($res,320);die;
          } else {
               $message='验证码发送失败';
                  $res = array('code'=>'200' , 'message'=>$result['msg'],'data'=>'');
                  echo json_encode($res,320);die;
          }
      }
        /**
     * 统一身份验证入口
     */
    private function auth($member_id, $auth_code){
            $model_member = Model('member');
            $member_common_info = $model_member->getMemberCommonInfo(array('member_id'=>$member_id));
            if (empty($member_common_info) || !is_array($member_common_info)) {
               $message='系统发生错误';
                // $res = array('code'=>'200' , 'message'=>$message,'data'=>$data);
                $res = array('code'=>'200' , 'message'=>$message,'data'=>'');
                echo json_encode($res,320);die;
            }
            if ($member_common_info['auth_code'] != $auth_code || TIMESTAMP - $member_common_info['send_acode_time'] > 1800) {
                 return false;
            }else{
               $data = array();
            $data['auth_code'] = '';
            $data['send_acode_time'] = 0;
            $update = $model_member->editMemberCommon($data,array('member_id'=>$member_id));
            return true;
          }
    }

          /**
     * curl请求指定url
     * @param $url
     * @param array $data
     * @return mixed
     */
    private  function curl($url, $data = [])
    { 
      // var_dump($url);die;
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

}