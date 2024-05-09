<?php

defined('In718Shop') or exit('Access Invalid!');

const KEY = '01234567';
const CIPHER = 'DES-ECB';
const IV = '01234567';
const OPTION = OPENSSL_RAW_DATA;
const MD5_KEY = 'kejdifug49';

//des加密
function des_encrypt($data)
{
    $encrypted = openssl_encrypt($data, CIPHER, KEY, OPTION, IV);
    return base64_encode($encrypted);
}

//创建签名
function create_sign($data, $timestamp)
{
    $sign = MD5_KEY . 'timestamp' . $timestamp . 'platJAVAv666data' . $data . MD5_KEY;
    return md5($sign);
}

function api_encrypt($order_data)
{
    $sign = create_sign(json_encode($order_data, 320), TIMESTAMP);
    $data = array(
        'data' => $order_data,
        'v' => 666,
        'sign' => $sign,
        'plat' => 'JAVA',
        'timestamp' => TIMESTAMP,
    );
    return des_encrypt(json_encode($data, 320));
}

