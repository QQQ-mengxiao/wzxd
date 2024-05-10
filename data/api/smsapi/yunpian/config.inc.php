<?php
defined('In718Shop') or exit('Access Invalid!');
/*
 * 配置文件
 */
$options = array();
// $options['apikey'] = "a5b55e91debd1e32957ca2a31d8cb8d0"; //apikey
$options['apikey'] = C('mobile_key'); //apikey
$options['signature'] =  C('mobile_signature'); //签名
// $options['signature'] = "班列购"; //签名
return $options;
?>