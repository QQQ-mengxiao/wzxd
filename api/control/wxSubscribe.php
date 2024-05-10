<?php
/**
* 
*/
defined('In718Shop') or exit('Access Invalid!');
class wxSubscribe 
{
    public function sendMessage($touser, $order_sn, $order_id, $ziti_name)
    {
        // 一次性订阅消息取货通知模版ID
        $template_id = "Q9vaCMda7Dnmg9c1oAemsliHo-4F9I730HJg2KagMYU";
        
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
        $data['page'] = "pages/pageUser/order/detail?order_id=" . $order_id;

        //模板内容，格式形如 { "key1": { "value": any }, "key2": { "value": any } }
        $data['data'] = [
            "character_string1" => [
                'value' => $order_sn
            ],
            "thing3" => [
                'value' => $ziti_name
            ]
        ];

        //跳转小程序类型：developer为开发版；trial为体验版；formal为正式版；默认为正式版
        $data['miniprogram_state'] = 'formal';
        
        return $this->posturl($url, $data);
    }


    /**
     * 获取微信access_token
     */
    private function getWxAccessToken()
    {
        // 尝试读取缓存的access_token
        $access_token = rkcache('wx_access_token');
        
        if ($access_token) {
            $access_token = unserialize($access_token);
            // 如果access_token未过期直接返回缓存的access_token
            if ($access_token['time'] > TIMESTAMP) {
                return $access_token['token'];
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
        $access_token['token'] = $result['access_token'];
        $access_token['time'] = TIMESTAMP + $result['expires_in'];
        wkcache('wx_access_token', serialize($access_token));

        return $result['access_token'];
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
