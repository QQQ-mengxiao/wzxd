<?php
defined('In718Shop') or exit('Access Invalid!');
    
class Member_uidControl extends BaseControl{

  public function add_uidOp()
  {
    header("Content-type:application/json;charset=utf-8");
    $member_uid_model = Model('member_uid');
    $member_uid_log_model = Model('member_uid_log');
    $time = time();
    $result = array();
    $member_id = isset($_GET['member_id'])?$_GET['member_id']:0;
    if (!$member_id) {
      echo $this->returnMsg(100,'用户id错误','');
      die;
    }else{
      $member_info = Model('member')->getMemberInfoByID($member_id);
      if (!$member_info) {
        echo $this->returnMsg(100,'用户id错误','');
        die;
      }
    }
    $phone = isset($_GET['phone'])?$_GET['phone']:0;
    if (!$phone) {
      echo $this->returnMsg(100,'手机号错误','');
      die;
    }
    $uid = isset($_GET['uid'])?$_GET['uid']:0;
    if (!$uid) {
      echo $this->returnMsg(100,'uid错误','');
      echo json_encode($result,320);
      die;
    }
    //验证码验证
    $captcha = isset($_GET['captcha'])?$_GET['captcha']:0;
    $check_captcha = $this->check_captcha($phone,$captcha);
    //1.验证是否已经绑定uid
    $member_uid = $member_uid_model->getUid($member_id);
    if ($member_uid) {
      echo $this->returnMsg(100,'已绑定线下id：'.$member_uid,'');
      die;
    }
    //2.验证uid是否合法
    $post_data = array('phone' => $phone, 'uid' => $uid);
    $response = $member_uid_model->bindUser($post_data);
    if (!$response || $response['code'] != 0) {
      $log_data = array('member_id' => $member_id, 'uid' => $uid, 'action' => 1, 'log_time' => $time, 'content' => $response['msg'], 'result' => 3);
      $member_uid_log_model->addLog($log_data);
      echo $this->returnMsg(300,'绑定失败','');
      die;
    }

    //3.绑定线下uid
    $data = array('member_id' => $member_id, 'uid' => $uid, 'status' => 1);
    $log_data = array('member_id' => $member_id, 'uid' => $uid, 'action' => 1, 'log_time' => $time, 'content' => '绑定成功', 'result' => 2);
    $member_uid_model->addUid($data);
    $member_uid_log_model->addLog($log_data);

     //4.绑定成功，添加二级会员经验值
    $model_setting = Model('setting');
    $list_setting = $model_setting->getListSetting();
    $member_grade = $list_setting['member_grade']?unserialize($list_setting['member_grade']):array();
    if(empty($member_grade[1])){
      $second_grade = 0;
    }else{
      $second_grade = $member_grade[1]['exppoints'];
    }
    $model_member = Model('member');
    $member_exppoints = $model_member->table('member')->getfby_member_id($member_id,'member_exppoints');
    $update_member['member_exppoints'] = $second_grade + $member_exppoints;
    $update = $model_member->table('member')->where(array('member_id'=>$member_id))->update($update_member);

    
    $result['code'] = 200;
    $result['message'] = '绑定成功';
    echo json_encode($result,320);
    die;
  }

  /**
   * 消费记录
   */
  public function select_recordOp()
  {
    header("Content-type:application/json;charset=utf-8");
    $member_uid_model = Model('member_uid');
    $member_uid_log_model = Model('member_uid_log');
    $time = time();
    $result = array();
    $member_id = isset($_GET['member_id'])?$_GET['member_id']:0;
    $num = isset($_GET['num'])?$_GET['num']:1;
    if (!$member_id) {
      echo $this->returnMsg(100,'用户id错误','');
      die;
    }else{
      $member_info = Model('member')->getMemberInfoByID($member_id);
      if (!$member_info) {
        echo $this->returnMsg(100,'用户id错误','');
        die;
      }
    }
    //1.验证是否已经绑定uid
    $member_uid = $member_uid_model->getUid($member_id);//获取uid
    if (!$member_uid) {
      echo $this->returnMsg(100,'未绑定线下id：'.$member_uid,'');
      die;
    }

    //通过uid查询记录
    $post_data = array('uid' => $member_uid, 'current' => $num, 'size' => 30);
    $response = $member_uid_model->selectLineRecord($post_data);
    // if (!$response['code']) {
    //   echo $this->returnMsg(300,'请求失败','');
    //   die;
    // }
    //var_dump($response);die;
    if (!$response || $response['code'] != 0) {
      $log_data = array('member_id' => $member_id, 'uid' => $uid, 'action' => 5, 'log_time' => $time, 'content' => $response['msg'], 'result' => 3);
      $member_uid_log_model->addLog($log_data);
      echo $this->returnMsg(300,'请求失败','');
      die;
    }
    $result['code'] = 200;
    $result['message'] = '查询成功';
    $result['lineRecords']['total'] = $response['lineRecords']['total'];
    $result['lineRecords']['size'] = $response['lineRecords']['size'];
    $result['lineRecords']['pages'] = $response['lineRecords']['pages'];
    $result['lineRecords']['current'] = $response['lineRecords']['current'];
    foreach ($response['lineRecords']['records'] as $key => $value) {
      unset($response['lineRecords']['records'][$key]['delFlag']);
      unset($response['lineRecords']['records'][$key]['id']);
      unset($response['lineRecords']['records'][$key]['updateTime']);
      unset($response['lineRecords']['records'][$key]['userId']);
    }
    $result['lineRecords']['records'] = $response['lineRecords']['records'];
    echo json_encode($result,320);
    die;
  }

  public function select_balanceOp()
  {
    header("Content-type:application/json;charset=utf-8");
    $member_uid_model = Model('member_uid');
    $member_uid_log_model = Model('member_uid_log');
    $time = time();
    $result = array();
    $member_id = isset($_GET['member_id'])?$_GET['member_id']:0;
    if (!$member_id) {
      echo $this->returnMsg(100,'用户id错误','');
      die;
    }else{
      $member_info = Model('member')->getMemberInfoByID($member_id);
      if (!$member_info) {
        echo $this->returnMsg(100,'用户id错误','');
        die;
      }
    }
    //1.验证是否已经绑定uid
    $member_uid = $member_uid_model->getUid($member_id);//获取uid
    if (!$member_uid) {
      echo $this->returnMsg(100,'未绑定线下id：'.$member_uid,'');
      die;
    }
    $response = $member_uid_model->selectBalance($member_uid);
    if (!$response || $response['code'] != 0) {
      $log_data = array('member_id' => $member_id, 'uid' => $member_uid, 'action' => 5, 'log_time' => $time, 'content' => $response['msg'], 'result' => 3);
      $member_uid_log_model->addLog($log_data);
      echo $this->returnMsg(300,'请求失败','');
      die;
    }
    $result['code'] = 200;
    $result['msg'] = '成功';
    $result['balance'] = $response['balance'];
    echo json_encode($result,320);
  }

  /**
   * 获取短信验证码
   */
  public function get_captchaOp()
  {
    $member_id = isset($_GET['member_id'])?$_GET['member_id']:0;
    $uid = isset($_GET['uid'])?$_GET['uid']:0;
    if (!$member_id) {
      echo $this->returnMsg(100,'用户id错误','');
      die;
    }
    $member_info = Model('member')->getMemberInfoByID($member_id);
    if (!$member_info) {
      echo $this->returnMsg(100,'用户不存在','');
      die;
    }
    if (!$uid) {
      echo $this->returnMsg(100,'uid错误','');
      die;
    }
    $phone = isset($_GET['phone'])?$_GET['phone']:0;
    if (!$phone) {
      echo $this->returnMsg(100,'手机号错误','');
      die;
    }
    $member_uid_model = Model('member_uid');
    $member_uid_log_model = Model('member_uid_log');
    $time = time();
    //1.验证是否已经绑定uid
    $member_uid = $member_uid_model->getUid($member_id);
    if ($member_uid) {
      echo $this->returnMsg(100,'已绑定线下id：'.$member_uid,'');
      die;
    }
    //2.验证uid是否合法
    $post_data = array('phone' => $phone, 'uid' => $uid);
    $response = $member_uid_model->bindUser($post_data);
    if (!$response || $response['code'] != 0) {
      $log_data = array('member_id' => $member_id, 'uid' => $uid, 'action' => 1, 'log_time' => $time, 'content' => $response['msg'], 'result' => 3);
      $member_uid_log_model->addLog($log_data);
      echo $this->returnMsg(300,'绑定失败','');
      die;
    }
    //3.获取短信验证码
    $condition = array();
    $condition['phone'] = $phone;
    $condition['type'] = 1;
    $condition['ip'] = getIp();
    $model_sms_captcha = Model('sms_captcha');
    $sms_captcha_info = $model_sms_captcha->getSmsInfo($condition);
    $where = $condition;
    $where['add_time'] = array('gt', TIMESTAMP-86400);
    $count = $model_sms_captcha->getSmsCount($where);
    //每分钟最多发一条，24h内发5条
    if ((!empty($sms_captcha_info) && ($sms_captcha_info['add_time'] > TIMESTAMP-60)) || $count >= 5) {
      echo $this->returnMsg(400,'请勿多次获取动态码！','');
      die;
    }
    $code = rand(100000,999999);
    //4.发送验证码
    $result = $this->sendSms($phone,$code);
    if (!$result || $result['code'] != 0) {
      var_dump($result);die;
      echo $this->returnMsg(500,'验证码发送失败','');
      die;
    }
    $sms_data['phone'] = $phone;
    $sms_data['captcha'] = $code;
    $sms_data['ip'] = getIp();
    $sms_data['msg'] = '【物资小店】您的验证码是'.$code.'。如非本人操作，请忽略本短信';
    $sms_data['type'] = 1;
    $sms_data['add_time'] = time();
    $sms_data['uid'] = $uid;
    $sms_data['member_id'] = $member_id;
    $sms_data['code'] = $result['code'];
    $model_sms_captcha->addSms($sms_data);
    echo $this->returnMsg(200,'验证码已发送','');
  }

  /**
  * 手机验证码验证
  */
  private function check_captcha($phone,$captcha,$type='1'){
    if (strlen($phone) == 11 && strlen($captcha) == 6){
        $condition = array();
        $condition['phone'] = $phone;
        $condition['captcha'] = $captcha;
        $condition['type'] = $type;
        $model_sms_captcha = Model('sms_captcha');
        $sms_captcha_info = $model_sms_captcha->getSmsInfo($condition);
        //var_dump($sms_captcha_info);die;
        //验证码10分钟有效期
        if(empty($sms_captcha_info) || ($sms_captcha_info['add_time'] < TIMESTAMP-600)) {
          echo $this->returnMsg(400,'验证码错误或已过期','');
          die;
        }
    }else{
      echo $this->returnMsg(400,'验证码错误','');
      die;
    }
  }

  /**
   * 发送短信
   */
  private function sendSms($phone,$code)
  {
    $url = 'http://sms.yunpian.com/v2/sms/single_send.json';
    $data = array();
    $data['apikey'] = '45c2d1cfd15cb2d034209190a0e34ab3';//45c2d1cfd15cb2d034209190a0e34ab3
    $data['mobile'] = $phone;
    $data['text'] = '【物资小店】您的验证码是'.$code.'，10分钟内有效。如非本人操作，请忽略本短信';
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query($data),
    ));

    $response = curl_exec($curl);


    curl_close($curl);
    if (is_null(json_decode($response))){
        return false;
    }
    $result = json_decode($response,true);
    return $result;
  }
}