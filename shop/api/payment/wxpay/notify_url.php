<?php
/**
 * 接收微信支付异步通知回调地址
 *
 *
 */
error_reporting(7);
$_GET['act']	= 'payment';
$_GET['op']		= 'wxpay_notify';
require_once(dirname(__FILE__).'/../../../index.php');
