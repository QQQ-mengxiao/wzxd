<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('10.10.11.141', 5672, 'wzxd', 'WZXDRMQpython~XX2');

$channel = $connection->channel();

$channel->exchange_declare('post_topic_exchange', 'topic', true, false, false);

$queue_name="post_wx_queue";

$binding_key = "post.#";

$channel->queue_bind($queue_name, 'post_topic_exchange', $binding_key);

$callback = function ($msg) {
    $order_id = $msg->body;
    post_wx($order_id);
    $msg->ack();
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();

function post_wx($order_id){
    $sql = 'SELECT so.order_sn,so.shipping_code,se.e_name,sm.member_wxopenid FROM 718shop_order so LEFT JOIN 718shop_order_common soc ON so.order_id=soc.order_id LEFT JOIN 718shop_express se ON soc.shipping_express_id=se.id LEFT JOIN 718shop_member sm ON so.buyer_id=sm.member_id WHERE so.order_id='.$order_id;
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=wzxd", "statistics", "zUhQmDdZdlsXHatt");//创建一个pdo对象
    $result = $pdo->query($sql);
    $order_info = $result->fetch();
    $pdo = null;//关闭连接
    $result = sendMessagePost($order_info['member_wxopenid'], $order_info['order_sn'], $order_id, $order_info['shipping_code'], $order_info['e_name']);
    if(!$result){
        file_put_contents('/data/default/wzxd/qlog/post_wx.log', date("Y-m-d H:i:s",time()).'--'.$order_info['order_sn']."微信通知发送失败 \n", FILE_APPEND);
        addSellerLog($order_info['order_sn']."微信发货通知发送失败");
    }else{
        $data = json_decode($result,true);
        if($data['errcode']==0){
            file_put_contents('/data/default/wzxd/qlog/post_wx.log', date("Y-m-d H:i:s",time()).'--'.$order_info['order_sn']."微信通知发送成功 \n", FILE_APPEND);
            addSellerLog($order_info['order_sn']."微信发货通知发送成功");
        }else{
            file_put_contents('/data/default/wzxd/qlog/post_wx.log', date("Y-m-d H:i:s",time()).'--'.$order_info['order_sn']."微信通知发送失败，errmsg：".$data['errmsg']." \n", FILE_APPEND);
            addSellerLog($order_info['order_sn']."微信发货通知发送失败，errmsg：".$data['errmsg']);
        }
    }
}

function sendMessagePost($touser, $order_sn, $order_id, $shipping_code, $e_name){
    $access_token = accessToken();
    if(!$access_token){
        return false;
    }
    $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$access_token;
    $data = [];
    $data['touser'] = $touser;
    $data['template_id'] = "WpkKVUzQW_C2cD6dUZPygApKzyrsC-rbQBz67OlFKfE";
    $data['page'] = "pages/pageUser/order/detail?order_id=" . $order_id;
    $data['data'] = [
        "character_string1" => [
            'value' => $order_sn
        ],
        "thing20" => [
            'value' => $e_name
        ],
        "character_string5" => [
            'value' => $shipping_code
        ]
    ];
    $data['miniprogram_state'] = 'formal';
    return posturl($url, $data);
}

function accessToken(){
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=wzxd", "statistics", "zUhQmDdZdlsXHatt");//创建一个pdo对象
    $sql = "SELECT access_token FROM 718shop_wx_info WHERE expires_in > ".time();
    $result = $pdo->query($sql);
    $wx_info = $result->fetch();
    $pdo = null;//关闭连接
    if(!$wx_info){
        $access_token = getWxAccessToken();
    }else{
        $access_token = $wx_info['access_token'];
    }
    return $access_token;
}

function getWxAccessToken(){
    $wx_appid = 'wx5a1936970131d93f';
    $wx_appsecret = 'a504dedfe6f392b258fc1fe39fd77b99';
    $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$wx_appid.'&secret='.$wx_appsecret;
    $result = geturl($url);
    if (!$result['access_token']) {
        return false;
    }
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=wzxd", "statistics", "zUhQmDdZdlsXHatt");//创建一个pdo对象
    $sql = "UPDATE 718shop_wx_info SET access_token = '".$result['access_token']."' ,expires_in = ". ( time() + 6900 );
    $pdo->exec($sql);
    $pdo = null;//关闭连接
    return $result['access_token'];
}

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

function addSellerLog($content){
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=wzxd", "statistics", "zUhQmDdZdlsXHatt");//创建一个pdo对象
    $sql = "INSERT INTO 718shop_seller_log SET log_content='".$content."',log_time=".time().",log_seller_id=0,log_seller_name='微信发货通知',log_store_id=4,log_seller_ip='10.10.11.123',log_url='queue',log_state=1";
    $pdo->exec($sql);
    $pdo = null;//关闭连接
}