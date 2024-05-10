<?php
/**
 * 海关数据实时传输
 */
//require_once(BASE_CORE_PATH.'/framework/db/mysql.php');
//require_once(BASE_CORE_PATH.'/framework/db/mysqli.php');
require_once(BASE_CORE_PATH.'/framework/websocket-php-master/lib/Exception.php');
//载入函数库
require_once(BASE_CORE_PATH.'/framework/websocket-php-master/lib/BadOpcodeException.php');
require_once(BASE_CORE_PATH.'/framework/websocket-php-master/lib/BadUriException.php');
require_once(BASE_CORE_PATH.'/framework/websocket-php-master/lib/Base.php');
require_once(BASE_CORE_PATH.'/framework/websocket-php-master/lib/Client.php');
require_once(BASE_CORE_PATH.'/framework/websocket-php-master/lib/ConnectionException.php');
require_once(BASE_CORE_PATH.'/framework/websocket-php-master/lib/Exception.php');
require_once(BASE_CORE_PATH.'/framework/websocket-php-master/lib/Server.php');
date_default_timezone_set("PRC");


use WebSocket\Client;

// require_once(ROOT_PATH .ADMIN_PATH.'/'.'js/client.js');

class haiguan{

	/**
     *企业返回实时数据-数据接口
     * @param  string $order_id [订单编号]
     * @param  string $session_id  [海关发起请求时，平台接收的会话ID]
     * @return  string 
     */
	
	function realTimeDateUp_data($order_id,$session_id){
        //链接数据库
  $db = array(
    'dsn' => 'mysql:host=127.0.0.1;dbname=718blg',
    'host' => '127.0.0.1',
    'dbname' => '718blg',
    'username' => 'root',
    'password' => 'ZLC8@7hT1N#mE.Q5u',
    'charset' => 'utf8',
);
    //建立连接
    $link = mysqli_connect($db['host'], $db['username'], $db['password']) or die( 'Could not connect: '  .  mysqli_error ());
    //选择数据库
    mysqli_select_db($link,$db['dbname'] ) or die ( 'Can\'t use foo : '  .  mysqli_error ($link));

    mysqli_set_charset($link,$db['charset'] );


        //$guid_logic=Logic("guid");
		//$order_model=Model("order");
		//海关发起请求时，平台接收的会话ID  sessionID
		$sessionID = $session_id;
		/*支付原始数据表头  payExchangeInfoHead*/
		//系统唯一序号  guid
		$guid =  $this->guid();
		//获取订单信息
		//$model= new orderModel();
		$result  = mysqli_query($link,"SELECT * FROM 718shop_order WHERE order_id=$order_id" );
	    $order_info=mysqli_fetch_array($result);
		//$order_info=$this->getOrderInfo(array("order_sn"=>$order_sn));
		//原始请求
		$initalRequest = @urlencode($order_info['initalRequest']);

		//$initalRequest = @urlencode('2n2n2n2n2nn2n2n2n2nn2n2n2n');
		//echo $initalRequest.'<br/><br/>';
		
		// //项目存放路径
		// $ROOT_PATH = ROOT_PATH;
		// //截取更目录
		// $ROOT_PATH = $this ->cut_str($ROOT_PATH,'/',-2);
		//echo  $ROOT_PATH;
		// $initalResponse_ip = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/'.$ROOT_PATH.'/respond.php');
		// //原始响应
		// $initalResponse[$initalResponse_ip] = json_decode($order_info['initalResponse']);
		$initalResponse = 'ok';
		// print_r ($initalResponse);
		// $initalResponse = json_encode($initalResponse);
		// $initalResponse = urldecode($initalResponse);
		// echo ($initalResponse);die;
		//电商平台代码
		$ebpCode = '4101968871';
		//$ebpCode = '4100201004';
		//支付企业代码 payCode
		// $payCode = '312226T001';
		if($order_info['payment_code'] == 'alipay'){
		$payCode = "31222699S7";  //支付宝代码  -总署2019.329修改
	    }else if( $order_info['payment_code'] == 'wx_saoma' || $order_info['payment_code'] == 'wxpay'){
	    	$payCode = "4403169D3W";//财付通代码20190527修改
	    }else{
	    	$payCode = ""; 
	    }
		//交易流水号
		//交易流水号
		$model_order_log  = mysqli_query($link,"SELECT log_msg FROM 718shop_order_log WHERE order_id=$order_id" );
		$logdata=mysqli_fetch_array($model_order_log);
        $tradeNo = explode(' ',$logdata['log_msg']);
		$payTransactionId  = $tradeNo[4];
		//print_r($payTransactionId);die;
		//交易金额  totalAmount
		$totalAmount = (double)$order_info['order_amount'];
		//var_dump($totalAmount );
		//币制
		$currency = '142';
		//验核机构
		$verDept = '3';
		//支付类型 payType
		if($order_info['order_from'] == 1){
            $payType = '2';
        }else if($order_info['order_from'] == 2){
            $payType = '4';
        }else{
            $payType = '1';
        }
		//交易成功时间 
		$pay_time = $order_info['payment_time'];
		$tradingTime = date("YmdHis",$pay_time);
		//备注
		$note = @urlencode ('无');
		//将支付原始数据表头组合为数组
		$payExchangeInfoHead = compact("guid","initalRequest","initalResponse","ebpCode","payCode","payTransactionId","totalAmount","currency","verDept","payType","tradingTime","note");
		//将数组转换为json
		$payExchangeInfoHead_sign = json_encode($payExchangeInfoHead);
		$payExchangeInfoHead_sign = urldecode($payExchangeInfoHead_sign);
		//$payExchangeInfoHead_sign = str_replace("&","&amp;",$payExchangeInfoHead_sign);
		//echo $payExchangeInfoHead_sign ;die;
		//echo $payExchangeInfoHead_sign;die;

		/*支付原始数据表体   payExchangeInfoLists*/
		//订单编号
		$orderNo = $order_info['order_sn'];

		/*属性描述   goodsInfo*/
		//获取订单商品
		//$order_goods=$order_model->getOrderGoodsList(array("order_sn"=>$order_sn));
		$order_goods  = mysqli_query($link,"SELECT * FROM 718shop_order_goods WHERE order_id=$order_id" );
		$order_goods=mysqli_fetch_array($order_goods);
		// //print_r($order_goods);die;
		// foreach ($order_goods as $goods) {
			//商品编码  goods_id	
		$goods_id = $order_goods['goods_id'];
			//商品编号  gname
		$order_goods['goods_name'] = preg_replace('# #','',$order_goods['goods_name']);
		$gname  = @urlencode ($order_goods['goods_name']);
		
		//echo $gname ;die;
			//商品展示链接地址  itemLink
		$itemLink =@urlencode ("http://www.banliego.com/shop/index.php?act=goods&op=index&goods_id=".$goods_id);
			//echo $itemLink.'<br/><br/>';
		$goodsInfo[] = compact("gname","itemLink");

		// }
		//print_r($goodsInfo);
		// $goodsInfo = json_encode($goodsInfo);
		// $goodsInfo = urldecode($goodsInfo);
		//echo $goodsInfo;die;
		//收款账号 recpAccount
		//$recpAccount = 'zezhb@zih718.com';
		if($order_info['payment_code'] == 'alipay'){
            $recpAccount = 'zezhb@zih718.com';
        }else if($order_info['payment_code'] == 'wxpay'){
            $recpAccount ='1484832052@1484832052';
        }else if($order_info['payment_code'] == 'wx_saoma'){
            $recpAccount ='145530602@145530602';
        }
		//收款企业代码
		$recpCode = '';
		//收款企业名称
		$recpName = @urlencode ('河南中陆进出口贸易有限公司');

		//将支付原始数据表体组合为数组
		$payExchangeInfoLists[] = compact("orderNo","goodsInfo","recpAccount","recpCode","recpName");
		//将数组转换为json
		$payExchangeInfoList_sign = json_encode($payExchangeInfoLists);
		$payExchangeInfoList_sign = urldecode($payExchangeInfoList_sign);
		//echo '<br/><br/>'.$payExchangeInfoList_sign;

		//返回时的系统时间
		$serviceTime =  time();
		//证书编号  certNo
		$certNo = '';


		//加签原文
		$initData = '"sessionID":"'.$sessionID.'"||"payExchangeInfoHead":"'.$payExchangeInfoHead_sign.'"||"payExchangeInfoLists":"'.$payExchangeInfoList_sign.'"||"serviceTime":"'.$serviceTime.'"';
		
       // print_r($initData);die;
		/*完整请求内容  payExInfoStr*/
		$payExInfoStr = compact("sessionID","payExchangeInfoHead","payExchangeInfoLists","serviceTime","certNo","signValue");
		//将数组转换为json
		$payExInfoStr = json_encode($payExInfoStr);
		
		//echo $payExInfoStr;
		//die;
		//整合数据
		$data = array('payExInfoStr'=>$payExInfoStr,'initData'=>$initData);
		return $data ;
	}

	/**
     *企业返回实时数据接口
     * @param  string $order_id [订单编号]
     * @param  string $is_mode  [订单类型]
     * @return  string 
     */
	
	function realTimeDateUp($payExInfoStr){

		//echo $signValue;
		// $url = "https://swapptest.singlewindow.cn/ceb2grab/grab/realTimeDataUpload";
		$url = "https://customs.chinaport.gov.cn/ceb2grab/grab/realTimeDataUpload";
 		//$sa = new sha();
		$array_request = array('payExInfoStr'=>$payExInfoStr);
		$res = $this->curl_post_https($url, $array_request);
		return $res;
		// $res = json_decode($res,true);
		// print_r($res);
		// die;
	}
		/**
     *测试加签
     * @param  string $inData   [加签文本]
     * @return  string 
     */
	
		function sign($inData){
		$client = new Client("ws://117.158.11.246:61232");
          //var_dump($client);die;          
		$client->send('1234');
		$client->receive();//先握手
		$jq = $inData;
		$data=array(
    		'_method'=>"cus-sec_SpcSignDataAsPEM",
    		'_id'=>1,
    		'args'=>array(
        		'passwd'=>'88888888',
        		'inData'=>$jq,
    		)
		);

 		//$datag=str_replace("\\/", "/", $this->json_encode($data));
 		//print_r($data);die;
 		$datag=json_encode($data,320);
		$client->send($datag);

		$resl= $client->receive('close');


		// $resl=json_decode($resl,true);
		return $resl;
	}
	
	/**
	* 按符号截取字符串的指定部分
	* @param string $str 需要截取的字符串
	* @param string $sign 需要截取的符号
	* @param int $number 如是正数以0为起点从左向右截 负数则从右向左截
	* @return string 返回截取的内容
	*/
	function cut_str($str,$sign,$number){
 		$array=explode($sign, $str);
 		$length=count($array);
 		if($number<0){
  			$new_array=array_reverse($array);
  			$abs_number=abs($number);
  		if($abs_number>$length){
   			return 'error';
  		}else{
   			return $new_array[$abs_number-1];
  		}
 	}else{
  		if($number>=$length){
   			return 'error';
  		}else{
  			 return $array[$number];
  			}
 		}
	}
	
	/**
	* 按转换字符串空格指定为特殊字符
	* @param string $str 需要转换的字符串
	* @param string $sign 需要截取的符号
	* @return string 返回替换过的字符串
	*/
	public function emptyreplace($str,$sign) {
		$str = str_replace('　', ' ', $str); //替换全角空格为半角
		$str = str_replace('  ', ' ', $str);    //替换连续的空格为一个
		$noe = false;   //是否遇到不是空格的字符
		for ($i=0 ; $i<strlen($str); $i++) { //遍历整个字符串
			if($noe && $str[$i]==' ') $str[$i] = $sign ;   //如果当前这个空格之前出现了不是空格的字符
			elseif($str[$i]!=' ') $noe=true;    //当前这个字符不是空格，定义下 $noe 变量
		}
		return $str;
	}

	   /**
     * POST请求https接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $post [请求的参数]
     * @return  string
     */
    
    function curl_post_https($url,$data){ // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据，json格式
}
 function guid() {
    mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
    $charid = strtoupper(md5(uniqid(rand(), true)));
    $hyphen = chr(45);// "-"
    $guid = //chr(123) // "{"
            substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
    return $guid;
}
 
	
}


?>