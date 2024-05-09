<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$dbhost = '127.0.0.1';
$dbuser = 'statistics';
$dbpswd = 'zUhQmDdZdlsXHatt';
$dbname = 'wzxd';

$mqhost = '10.10.11.141';
$mquser = 'wzxd';
$mqpswd = 'WZXDRMQpython~XX2';
$exchange = 'post_topic_exchange';
$queue = 'post_wx_queue';
$routing_key = 'post.#';

$url_log = '/data/default/wzxd/qlog';
$template_id = "WpkKVUzQW_C2cD6dUZPygApKzyrsC-rbQBz67OlFKfE";
$wx_appid = 'wx5a1936970131d93f';
$wx_appsecret = 'a504dedfe6f392b258fc1fe39fd77b99';
$url_wx_accessToken = 'https://api.weixin.qq.com/cgi-bin/token?';
$url_wx_subscribe = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?';
$url_cw_completed = 'http://10.10.11.61:8083/cloud-admin/cwApi/cwOrderCompleteNew';

$connection = new AMQPStreamConnection($mqhost, 5672, $mquser, $mqpswd);

$channel = $connection->channel();

$channel->exchange_declare($exchange, 'topic', true, false, false);

$queue_name = $queue;

$binding_key = $routing_key;

$channel->queue_bind($queue_name, $exchange, $binding_key);

$callback = function ($msg) {
    $order_id = $msg->body;
    post_wx($order_id);
    // cwOrderComplete($order_id);
    $msg->ack();
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();

function post_wx($order_id)
{
    $sql = 'SELECT so.order_sn,so.shipping_code,se.e_name,sm.member_wxopenid FROM 718shop_order so LEFT JOIN 718shop_order_common soc ON so.order_id=soc.order_id LEFT JOIN 718shop_express se ON soc.shipping_express_id=se.id LEFT JOIN 718shop_member sm ON so.buyer_id=sm.member_id WHERE so.order_id=' . $order_id;
    $pdo = new PDO("mysql:host=" . $GLOBALS['dbhost'] . ";dbname=" . $GLOBALS['dbname'], $GLOBALS['dbuser'], $GLOBALS['dbpswd']);
    $result = $pdo->query($sql);
    $order_info = $result->fetch();
    $pdo = null;
    $result = sendMessagePost($order_info['member_wxopenid'], $order_info['order_sn'], $order_id, $order_info['shipping_code'], $order_info['e_name']);
    if (!$result) {
        $msg = $order_info['order_sn'] . "微信通知发送失败";
        addFilelog($msg);
        addSellerLog($msg);
    } else {
        $data = json_decode($result, true);
        if ($data['errcode'] == 0) {
            $msg = $order_info['order_sn'] . "微信发货通知发送成功";
            addFilelog($msg);
            addSellerLog($msg);
        } else {
            $msg = $order_info['order_sn'] . "微信通知发送失败，errmsg：" . $data['errmsg'];
            addFilelog($msg);
            addSellerLog($msg);
        }
    }
}

function sendMessagePost($touser, $order_sn, $order_id, $shipping_code, $e_name)
{
    $access_token = accessToken();
    if (!$access_token) {
        return false;
    }
    $url = $GLOBALS['url_wx_subscribe'] . 'access_token=' . $access_token;
    $data = [];
    $data['touser'] = $touser;
    $data['template_id'] = $GLOBALS['template_id'];
    $data['page'] = "pages/pageUser/order/detail?order_id=" . $order_id;
    $data['data'] = [
        "character_string1" => [
            'value' => $order_sn,
        ],
        "thing20" => [
            'value' => $e_name,
        ],
        "character_string5" => [
            'value' => $shipping_code,
        ],
    ];
    $data['miniprogram_state'] = 'formal';
    return posturl($url, $data);
}

function accessToken()
{
    $pdo = new PDO("mysql:host=" . $GLOBALS['dbhost'] . ";dbname=" . $GLOBALS['dbname'], $GLOBALS['dbuser'], $GLOBALS['dbpswd']);
    $sql = "SELECT access_token FROM 718shop_wx_info WHERE expires_in > " . time();
    $result = $pdo->query($sql);
    $wx_info = $result->fetch();
    $pdo = null;
    if (!$wx_info) {
        $access_token = getWxAccessToken();
    } else {
        $access_token = $wx_info['access_token'];
    }
    return $access_token;
}

function getWxAccessToken()
{
    $wx_appid = $GLOBALS['wx_appid'];
    $wx_appsecret = $GLOBALS['wx_appsecret'];
    $url = $GLOBALS['url_wx_accessToken'] . 'grant_type=client_credential&appid=' . $wx_appid . '&secret=' . $wx_appsecret;
    $result = geturl($url);
    if (!$result['access_token']) {
        return false;
    }
    $pdo = new PDO("mysql:host=" . $GLOBALS['dbhost'] . ";dbname=" . $GLOBALS['dbname'], $GLOBALS['dbuser'], $GLOBALS['dbpswd']);
    $sql = "UPDATE 718shop_wx_info SET access_token = '" . $result['access_token'] . "' ,expires_in = " . (time() + 6900);
    $pdo->exec($sql);
    $pdo = null;
    return $result['access_token'];
}

function geturl($url)
{
    $headerArray = array("Content-type:application/json;", "Accept:application/json");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
    $output = curl_exec($ch);
    curl_close($ch);
    $output = json_decode($output, true);
    return $output;
}

function posturl($url, $data)
{
    $data = json_encode($data);
    $headerArray = array("Content-type:application/json;charset='utf-8'", "Accept:application/json");
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

function addSellerLog($content)
{
    $pdo = new PDO("mysql:host=" . $GLOBALS['dbhost'] . ";dbname=" . $GLOBALS['dbname'], $GLOBALS['dbuser'], $GLOBALS['dbpswd']);
    $sql = "INSERT INTO 718shop_seller_log SET log_content='" . $content . "',log_time=" . time() . ",log_seller_id=0,log_seller_name='微信发货通知',log_store_id=4,log_seller_ip='10.10.11.123',log_url='queue',log_state=1";
    $pdo->exec($sql);
    $pdo = null;
}

function cwOrderComplete($order_id)
{
    $data = order_data($order_id);
    $url = $GLOBALS['url_cw_completed'];
    $res = Post_curls($url, json_encode($data, 320));
    $res = json_decode($res, true);
    return $res;
}

function order_data($order_id)
{
    $pdo = new PDO("mysql:host=" . $GLOBALS['dbhost'] . ";dbname=" . $GLOBALS['dbname'], $GLOBALS['dbuser'], $GLOBALS['dbpswd']);
    $sql = 'SELECT order_sn,order_amount,refund_amount FROM 718shop_order WHERE order_id=' . $order_id;
    $order_info = $pdo->query($sql)->fetch();
    if ($order_info['refund_amount'] == 0) {
        $order_data = [
            "tenantId" => 42,
            "orderSn" => $order_info['order_sn'],
            "completeStatus" => "0",
            "orderStatus" => "3",
        ];
    } else {
        $goodsListsql = 'SELECT goods_id,goods_serial AS goodsCode FROM 718shop_order_goods WHERE order_id=' . $order_id;
        $goods_list = $pdo->query($goodsListsql)->fetchAll();
        if ($goods_list) {
            foreach ($goods_list as $key => $value) {
                $refundReturnsql = "SELECT refund_id FROM 718shop_refund_return WHERE order_id=" . $order_id . " AND goods_id=" . $value['goods_id'] . " AND seller_state=2 AND refund_state=3";
                $refund_info = $pdo->query($refundReturnsql)->fetch();
                if ($refund_info) {
                    unset($goods_list[$key]);
                } else {
                    unset($goods_list[$key]['goods_id']);
                }
                unset($goods_list[$key][0]);
                unset($goods_list[$key][1]);
            }
        }
        $order_data = [
            "tenantId" => 42,
            "orderSn" => $order_info['order_sn'],
            "completeStatus" => "1",
            "orderStatus" => "3",
            "goodsList" => array_values($goods_list),
        ];
    }

    $pdo = null;
    return $order_data;
}

function Post_curls($url, $post)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_HTTPHEADER => array(
            "Content-Type: text/plain",
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    return $response;
}

function addFilelog($content)
{
    file_put_contents($GLOBALS['url_log'] . '/post_wx.log', date("Y-m-d H:i:s", time()) . '--' . $content . " \n", FILE_APPEND);
}
