<?php
/**
 * 商城板块初始化文件
 *
 *
 **/

/*
 *---------------------------------------------------------------
 *PHP CONSOLE DEBUG
 *---------------------------------------------------------------
 *
 */
// require_once('../../php-console-master/src/PhpConsole/__autoload.php');
// PhpConsole\Helper::register();
// PC::debug('hi');
// define('BASE_DEBUG_PATH',str_replace('\\','/',dirname(__FILE__)));
// require_once(BASE_DEBUG_PATH.'/../php-console-master/src/PhpConsole/__autoload.php');
// PhpConsole\Helper::register();
//PC::debug('hi');


define('APP_ID','shop');
define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));
if (!@include(dirname(dirname(__FILE__)).'/global.php')) exit('global.php isn\'t exists!');
if (!@include(BASE_PATH.'/control/control.php')) exit('control.php isn\'t exists!');
if (!@include(BASE_CORE_PATH.'/718shop.php')) exit('718shop.php isn\'t exists!');
	//$wapurl = WAP_SITE_URL;
	$wapurl = 'http://m.banliego.com/banliego';
	$agent = $_SERVER['HTTP_USER_AGENT'];
	
	if(strpos($agent,"comFront") || strpos($agent,"iPhone") || strpos($agent,"MIDP-2.0") || strpos($agent,"Opera Mini") || strpos($agent,"UCWEB") || strpos($agent,"Android") || strpos($agent,"Windows CE") || strpos($agent,"SymbianOS")){
		global $config;
        /*
        if(!empty($config['wap_site_url'])){
            $url = $config['wap_site_url'];
            switch ($_GET['act']){
			case 'goods':
			  $url .= '/tmpl/product_detail.html?goods_id=' . $_GET['goods_id'];
			  break;
			case 'store_list':
			  $url .= '/shop.html';
			  break;
			case 'show_store':
			  $url .= '/tmpl/product_store.html?store_id=' . $_GET['store_id'];
			  break;
			}
        } else {
            header("Location:$wapurl");
        }
        header('Location:' . $url);
        */
        header("Location:$wapurl");
        exit();	
	}
	
define('APP_SITE_URL',SHOP_SITE_URL);
define('TPL_NAME',TPL_SHOP_NAME);
define('SHOP_RESOURCE_SITE_URL',SHOP_SITE_URL.DS.'resource');
define('SHOP_TEMPLATES_URL',SHOP_SITE_URL.'/templates/'.TPL_NAME);
define('BASE_TPL_PATH',BASE_PATH.'/templates/'.TPL_NAME);

Base::run();
?>
