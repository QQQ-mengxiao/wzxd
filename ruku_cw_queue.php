<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('10.10.11.141', 5672, 'wzxd', 'WZXDRMQpython~XX2');
$channel = $connection->channel();

$channel->exchange_declare('ruku_topic_exchange', 'topic', true, false, false);

$queue_name="ruku_cw_queue";

$binding_key = "ruku.#";

$channel->queue_bind($queue_name, 'ruku_topic_exchange', $binding_key);

$callback = function ($msg) {
    $order_id = $msg->body;
    ruku_cw($order_id);
    $msg->ack();
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();

function ruku_cw($order_id){
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=wzxd", "root", "vGXtbG3v@VP7GsWAYdy");//创建一个pdo对象
    $sql = "SELECT order_sn FROM 718shop_order WHERE order_id=".$order_id;
    $result = $pdo->query($sql);
    $order_info = $result->fetchAll();
    $pdo = null;//关闭连接
    cwOrderOver($order_info[0]['order_sn']);
}

function cwOrderOver($order_sn){
    $data = [
        "tenantId" => 42,
        "orderSn" => $order_sn,
        "orderStatus" => "7"
    ];
    $url = 'http://219.157.200.43:8083/cloud-admin/cwApi/cwOrderOver';
    Post_curls($url, json_encode($data),$order_sn);
}

function Post_curls($url, $post, $order_sn){
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
    curl_close($curl);
    file_put_contents('/data/default/wzxd/qlog/ruku_cw.log', date("Y-m-d H:i:s",time()).'--'.$order_sn.'--'.$response." \n", FILE_APPEND);
}

//function addSellerLog($content){
//    $pdo = new PDO("mysql:host=127.0.0.1;dbname=wzxd", "root", "root");//创建一个pdo对象
//    $sql = "SELECT order_sn FROM 718shop_order WHERE order_id=";
//    $pdo->exec($sql);
//    $data = [
//        "tenantId" => 42,
//        "orderSn" => $orderSn,
//        "orderStatus" => "7"
//    ];
//    $pdo = null;//关闭连接
//}