<?php
defined('In718Shop') or exit('Access Invalid!');
require_once BASE_ROOT_PATH . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQ
{

    //连接
    public function connection($host, $port = 5672, $user, $password)
    {
        return new AMQPStreamConnection($host, $port, $user, $password);
    }

    //发送队列Topic模式
    public function sendTopic($connection, $data, $exchange, $routing_key)
    {
        $channel = $connection->channel();

        $channel->exchange_declare($exchange, 'topic', false, true, false);

        $msg = new AMQPMessage($data, array('content_encoding' => 'UTF-8', 'content_type' => 'text/plain'));

        $channel->basic_publish($msg, $exchange, $routing_key);

        $channel->close();
    }

    //关闭连接
    public function close($connection)
    {
        $connection->close();
    }
}
