<?php
/**
 * 商城板块初始化文件
 *
 *
 **/
//
/*
 *---------------------------------------------------------------
 *PHP CONSOLE DEBUG
 *---------------------------------------------------------------
 *
 */
// define('BASE_DEBUG_PATH_ADMIN',str_replace('\\','/',dirname(__FILE__)));
// require_once(BASE_DEBUG_PATH_ADMIN.'/../php-console-master/src/PhpConsole/__autoload.php');
// PhpConsole\Helper::register();
//PC::debug('hi');


define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));
if (!@include(dirname(dirname(__FILE__)).'/global.php')) exit('global.php isn\'t exists!');
if (!@include(BASE_CORE_PATH.'/718shop.php')) exit('718shop.php isn\'t exists!');
define('TPL_NAME',TPL_ADMIN_NAME);
define('ADMIN_TEMPLATES_URL',ADMIN_SITE_URL.'/templates/'.TPL_NAME);
define('BASE_TPL_PATH',BASE_PATH.'/templates/'.TPL_NAME);

if (!@include(BASE_PATH.'/control/control.php')) exit('control.php isn\'t exists!');

Base::run();
?>