<?php
/**
 * 队列
 *
 *
 * 计划任务触发 by 
 */
// define('BASE_DEBUG_PATH',str_replace('\\','/',dirname(__FILE__)));
// require_once(BASE_DEBUG_PATH.'/../php-console-master/src/PhpConsole/__autoload.php');
// PhpConsole\Helper::register();
// PC::debug('hi');


$_SERVER['argv'][1] = $_GET['act'];
@$_SERVER['argv'][2] = $_GET['op'];

if (empty($_SERVER['argv'][1])) exit('Access Invalid!');

define('APP_ID','crontab');
define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));
define('TRANS_MASTER',true);
if (!@include(dirname(dirname(__FILE__)).'/global.php')) exit('global.php isn\'t exists!');
if (!@include(BASE_CORE_PATH.'/718shop.php')) exit('718shop.php isn\'t exists!');

if (PHP_SAPI == 'cli') {
    $_GET['act'] = $_SERVER['argv'][1];
    $_GET['op'] = empty($_SERVER['argv'][2]) ? 'index' : $_SERVER['argv'][2];
}
if (!@include(BASE_PATH.'/control/control.php')) exit('control.php isn\'t exists!');

Base::run();
?>