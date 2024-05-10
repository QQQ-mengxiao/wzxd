<?php
/**
 * 我的地址
 *
 * 
 *
 *
 */
defined('In718Shop') or exit('Access Invalid!');
class wxsend_admin_refundModel extends Model {
    
    public function __construct() {
        parent::__construct('wxsend_admin_refund');
    }
    
    public function sendMessage($touser, $refund, $state, $refund_array)
    {
        // 一次性订阅消息取货通知模版ID
        $template_id = "-dd7_DsuT2mgDceSpaOj4ImhTYYVjQcsIvCvZRN59KQ";
        
        // 获取access_token
        $access_token = $this->getWxAccessToken();
        if (empty($access_token)) {
            return;
        }

        //请求url
        $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$access_token;
        
        //发送内容
        $data = [];

        //接收者（用户）的 openid
        $data['touser'] = $touser;
        //所需下发的订阅模板id
        $data['template_id'] = $template_id;

        //点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,（示例index?foo=bar）。该字段不填则模板无跳转。
        $data['page'] = "pages/pageUser/order/aftermarket/detail?refund_id=" . $refund['refund_id'];

        if($state==1){
            $thing11 = '同意退款，'.$refund_array['admin_message'];
        }else{
            $thing11 = '拒绝退款，'.$refund_array['admin_message'];
        }

        //模板内容，格式形如 { "key1": { "value": any }, "key2": { "value": any } }
        $data['data'] = [
            "character_string12" => [
                'value' => $refund['order_sn']
            ],
            "character_string1" => [
                'value' => $refund['refund_sn']
            ],
            "amount3" => [
                'value' => $refund['refund_amount']
            ],
            "time4" => [
                'value' =>date('Y-m-d H:i:s',time()) 
            ],
            "thing11" => [
                'value' => $thing11
            ]
        ];

        //跳转小程序类型：developer为开发版；trial为体验版；formal为正式版；默认为正式版
        $data['miniprogram_state'] = 'formal';
        $result=$this->posturl($url, $data);
        
        return  $result;
    }


    /**
     * 获取微信access_token
     */
    private function getWxAccessToken()
    {
         $wx_info = Model()->table('wx_info')->field('access_token,expires_in')->where(array('id' => 1))->find();
         
        if ($wx_info) {
            // 如果access_token未过期直接返回缓存的access_token
            if ($wx_info['expires_in'] > TIMESTAMP) {
                return $wx_info['access_token'];
            }
        }
        
        // 微信配置信息
        $wx_appid = 'wx5a1936970131d93f';
        $wx_appsecret = 'a504dedfe6f392b258fc1fe39fd77b99';
        
        // 请求地址
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$wx_appid.'&secret='.$wx_appsecret;

        // 获取新access_token
        $result = $this->geturl($url);
        
        if ($result['errcode']) {
            return;
        }

        // 缓存获取的access_token
        $access_token = array();
        $access_token['access_token'] = $result['access_token'];
        $access_token['expires_in'] = TIMESTAMP + $result['expires_in'];
        $access_token['update_time'] = TIMESTAMP ;
          $result = Model()->table('wx_info')->where(array('id' => 1))->update($access_token);

        return $access_token['access_token'];
    }
    
    // get请求
    function geturl($url){
        $headerArray =array("Content-type:application/json;","Accept:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headerArray);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output,true);
        return $output;
    }
    
    // post请求
    function posturl($url,$data){
        $data  = json_encode($data);
        $headerArray =array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl,CURLOPT_HTTPHEADER,$headerArray);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}
