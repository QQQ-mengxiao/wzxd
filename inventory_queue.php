<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('10.10.11.61', 5672, 'yuncang', 'yuncang');
$channel = $connection->channel();

$channel->exchange_declare('inventory_exchange', 'topic', true, false, false);

$queue_name="inventory_queue";

$binding_key = "inventory.#";

$channel->queue_bind($queue_name, 'inventory_exchange', $binding_key);

$callback = function ($msg) {
    $body = $msg->body;
    file_put_contents('/data/default/wzxd/qlog/inventory.log', date("Y-m-d H:i:s",time()).'--'.$body." \n", FILE_APPEND);
    $data = json_decode($body, true);
    if($data[0]['goodQuantity']<0){
        addSellerLog($body.'数据更新失败，商品库存不能小于0');
    }else {
        $result = editGoodsStorage($data[0]['goodQuantity'], $data[0]['goodCode']);
        if ($result) {
            addSellerLog($body.'数据更新成功');
        } else {
            addSellerLog($body.'数据更新失败，可能是商品不存在或库存一致');
        }
    }
    $msg->ack();
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();

function editGoodsStorage($goodQuantity,$goodCode){
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=wzxd", "root", "vGXtbG3v@VP7GsWAYdy");//创建一个pdo对象
    $sql = "UPDATE 718shop_goods SET goods_storage=".$goodQuantity." WHERE goods_serial='".$goodCode."' AND is_cw=1";
    $result = $pdo->exec($sql);
    $pdo = null;//关闭连接
    return $result;
}

function addSellerLog($content){
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=wzxd", "root", "vGXtbG3v@VP7GsWAYdy");//创建一个pdo对象
    $sql = "INSERT INTO 718shop_seller_log SET log_content='".$content."',log_time=".time().",log_seller_id=0,log_seller_name='云仓',log_store_id=4,log_seller_ip='10.10.11.61',log_url='queue',log_state=1";
    $pdo->exec($sql);
    $pdo = null;//关闭连接
}