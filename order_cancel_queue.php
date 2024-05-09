<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('10.10.11.141', 5672, 'wzxd', 'WZXDRMQpython~XX2');
$channel = $connection->channel();

$channel->exchange_declare('order_cancel_topic_exchange', 'topic', true, false, false);

$queue_name="order_cancel_queue";

$binding_key = "cancel.#";

$channel->queue_bind($queue_name, 'order_cancel_topic_exchange', $binding_key);

$callback = function ($msg) {
    $order_id = $msg->body;
    inventory_sync($order_id);
    $msg->ack();
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();

function inventory_sync($order_id){
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=wzxd", "root", "vGXtbG3v@VP7GsWAYdy");//创建一个pdo对象
    $sql = "SELECT goods_serial,is_cw FROM 718shop_order_goods WHERE is_cw=1 AND order_id=".$order_id;
    $result = $pdo->query($sql);
    $order_info = $result->fetchAll();
    $pdo = null;//关闭连接
    if($order_info){
        foreach ($order_info as $key=>$goods){
            $goods_list[$key]['goodsCode'] = $goods['goods_serial'];
        }
    }
    $data['tenantId'] = 42;
    $data['goodsList'] = $goods_list;
    $result = cwPlatGoodsSyn($data);
    if($result && $result['code']==0){
        syn($result,$order_id);
    }
}

function cwPlatGoodsSyn($data){
    $url = 'http://219.157.200.43:8083/cloud-admin/cwApi/cwPlatGoodsSyn';
    $result = Post_curls($url, json_encode($data,320));
    return json_decode($result, true);
}

function Post_curls($url, $post){
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
    return $response; // 返回数据，json格式
}

function syn($result,$order_id){
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=wzxd", "root", "vGXtbG3v@VP7GsWAYdy");//创建一个pdo对象
    $update_sql = "UPDATE 718shop_goods SET goods_storage= CASE goods_serial";
    foreach ($result['data'] as $k=>$goods){
        $update_sql .= " WHEN '".$goods['goodsCode']."' THEN ".$goods['saleInventory'];
        $goods_serial_list[$k]['goods_serial'] = "'".$goods['goodsCode']."'";
    }
    $update_sql .= " END WHERE is_cw=1 AND goods_serial IN (".implode(',',array_column($goods_serial_list,'goods_serial')).")";
    $res = $pdo->exec($update_sql);
    file_put_contents('/data/default/wzxd/qlog/order_cancel.log', date("Y-m-d H:i:s",time()).'--订单id：'.$order_id."取消，库存同步：".$update_sql."，影响行数：".$res." \n", FILE_APPEND);
    $pdo = null;//关闭连接
}