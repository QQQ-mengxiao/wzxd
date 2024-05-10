<?php
/**
 * 接收微信支付异步通知回调地址
 *
 *
 */
error_reporting(7);
$_GET['act']	= 'order';
$_GET['op']		= 'notify';
require_once(dirname(__FILE__).'/index.php');