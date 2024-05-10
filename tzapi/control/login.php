<?php
defined('In718Shop') or exit('Access Invalid!');

class LoginControl extends BaseControl
{

    public function loginOp()
    {
        $code           = $_POST['code'];

        $user_info      = $_POST['user_info'];

        $user_info      = str_replace("&quot;", '"', $user_info);

        $user_info      = json_decode($user_info, true);
        
        $url    = 'https://api.weixin.qq.com/sns/jscode2session';

        $result = json_decode($this->curl($url, [
            'appid'         => 'wx074067c8956a62ea',
            'secret'        => '319a26b4028c605e7468c6926a4f8a35',
            'grant_type'    => 'authorization_code',
            'js_code'       => $code,
        ]), true);

        if (isset($result['errcode'])) {

            die(json_encode(array('code' => '100', 'message' => $result['errmsg'], 'data' => []), 320));

        }

        $tz_wxopenid    = $result['openid'];

        $tz_info        = Model('groupbuy_leader')->getGroupbuyLeaderInfo(array('wx_openid' => $tz_wxopenid));

        $modelGroupbyLeader = Model('groupbuy_leader');

        if ($tz_info) { //存在团长信息

            $condition['groupbuy_leader.groupbuy_leader_id'] = $tz_info['groupbuy_leader_id'];

            $groupbuy_leader_ziti_list = $modelGroupbyLeader->getGroupbuyLeaderAndZitiAddressList($condition);
    
            if(!count($groupbuy_leader_ziti_list)>=1){
    
                $condition['ziti_address.is_current'] = 1;
    
            }
    
            $groupbuy_leader_info = $modelGroupbyLeader->getGroupbuyLeaderAndZitiAddressInfo($condition);

            //图片处理
            $groupbuy_leader_info['id_photo_front']     = UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_FRONT.'/'.$groupbuy_leader_info['groupbuy_leader_id'].'/'.$groupbuy_leader_info['id_photo_front'];

            $groupbuy_leader_info['id_photo_back']      = UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_BACK.'/'.$groupbuy_leader_info['groupbuy_leader_id'].'/'.$groupbuy_leader_info['id_photo_back'];

            $groupbuy_leader_info['wx_avatar']          = UPLOAD_SITE_URL.'/'.ATTACH_TZAVATAR.'/'.$groupbuy_leader_info['wx_avatar'];

            $groupbuy_leader_info['ziti_photo']         = UPLOAD_SITE_URL.'/'.DIR_UPLOAD_ZITI.'/'.$groupbuy_leader_info['groupbuy_leader_id'].'/'.$groupbuy_leader_info['ziti_photo'];

            die(json_encode(array('code' => '200', 'message' => 'login sucess', 'data' => $groupbuy_leader_info), 320));

        } else { //不存在，新增团长数据

            $data['wx_openid']      = $tz_wxopenid;
            $data['wx_nickname']    = $user_info['nickName'];
            $data['avatarUrl']      = $user_info['avatarUrl'];

            $result = $this->register($data);

            if ($result) {

                $groupbuy_leader_info = Model('groupbuy_leader')->getGroupbuyLeaderAndZitiAddressInfo(array('groupbuy_leader.groupbuy_leader_id'=>$result));

                /******图片处理******/
                $groupbuy_leader_info['id_photo_front'] = UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_FRONT.'/'.$groupbuy_leader_info['groupbuy_leader_id'].'/'.$groupbuy_leader_info['id_photo_front'];

                $groupbuy_leader_info['id_photo_back']  = UPLOAD_SITE_URL.'/'.DIR_UPLOAD_GLID_BACK.'/'.$groupbuy_leader_info['groupbuy_leader_id'].'/'.$groupbuy_leader_info['id_photo_back'];

                $groupbuy_leader_info['wx_avatar']      = UPLOAD_SITE_URL.'/'.ATTACH_TZAVATAR.'/'.$groupbuy_leader_info['wx_avatar'];

                $groupbuy_leader_info['ziti_photo']     = UPLOAD_SITE_URL.'/'.DIR_UPLOAD_ZITI.'/'.$groupbuy_leader_info['groupbuy_leader_id'].'/'.$groupbuy_leader_info['ziti_photo'];

                /******图片处理******/

                die(json_encode(array('code' => '200', 'message' => '注册成功', 'data' => $groupbuy_leader_info), 320));

            } else {

                die(json_encode(array('code' => '100', 'message' => '用户信息更新失败', 'data' => ''), 320));

            }

        }

    }
    /**
     * 注册
     */
    public function register($data)
    {
        $openid = $data['wx_openid'];

        if (!empty($openid)) {

            $model_groupbuy_leader = Model('groupbuy_leader');

            $groupbuy_leader                = array();

            $groupbuy_leader['wx_openid']   = $openid;

            $groupbuy_leader['wx_nickname'] = $data['wx_nickname'];

            $groupbuy_leader_id = $model_groupbuy_leader->addGroupbuyLeader($groupbuy_leader);

            if($groupbuy_leader_id){//注册成功

                /******新增一条用户数据******/
                $member_info['member_name']         = '团长'.$groupbuy_leader_id;

                $member_info['member_email']        = '';

                $member_info['member_time']         = TIMESTAMP;

                $member_info['groupbuy_leader_id']  = $groupbuy_leader_id;

                $res = Model('member')->addMember($member_info);

                if(!$res){

                    $model_groupbuy_leader->del($groupbuy_leader_id);

                    die(json_encode(array('code' => '100', 'message' => '用户注册失败【未生成用户数据】', 'data' => ''), 320));

                }

                /******新增一条用户数据******/

                $headimgurl = $data['avatarUrl']; //用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像）

                $avatar     = @copy($headimgurl, BASE_UPLOAD_PATH . '/' . ATTACH_TZAVATAR . "/avatar_" . $groupbuy_leader_id . ".jpg");

                if ($avatar) {

                    $model_groupbuy_leader->editGroupbuyLeader(array('groupbuy_leader_id' => $groupbuy_leader_id), array('wx_avatar' => "avatar_" . $groupbuy_leader_id . ".jpg"));

                }

                return $groupbuy_leader_id;

            }else{

                die(json_encode(array('code' => '100', 'message' => '用户注册失败【未生成团长数据】', 'data' => ''), 320));

            }

        }

    }

    /**
     * curl请求指定url
     * @param $url
     * @param array $data
     * @return mixed
     */
    public function curl($url, $data = [])
    {
        // 处理get数据
        if (!empty($data)) {

            $url = $url . '?' . http_build_query($data);

        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_HEADER, false);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //这个是重点。

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
        
    }

    //团长助手登录——账号密码登录
    public function assistant_loginOp()
    {
        $array['username']    = $_POST['username'];
        $array['password']= md5(trim($_POST['password']));
        $assistant = Model()->table('groupbuy_leader_assistant')->where($array)->order('gl_assistant_id ASC')->find();
        if(!empty($assistant)){
            if($assistant['state'] == 1){
                $message='登陆成功';
                $res = array('code'=>'200' , 'message'=>$message,'data'=>$assistant);
            }else{
                $message='账号状态异常，请联系管理人员';
                $res = array('code'=>'100' , 'message'=>$message,'data'=>'');
            }
            echo json_encode($res,320);
        }else{
            $message ='账号/密码错误，请核对后重新登录';
            $res = array('code'=>'300' , 'message'=>$message,'data'=>'');
            echo json_encode($res,320);
        }
    }

}
