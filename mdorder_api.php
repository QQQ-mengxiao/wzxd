<?php
// header("Content-type:application/json;charset=UTF-8");
// define('BASE_ROOT_PATH',str_replace('\\','/',dirname(__FILE__)));
// define('BASE_CORE_PATH',BASE_ROOT_PATH.'/core');
// define('SHOP_SITE_URLD',BASE_ROOT_PATH.'/shop');
// //require_once("./data/logic/haiguan.logic.php");
// //require_once("./admin718/control/haiguan.php");
// require_once(BASE_CORE_PATH."/framework/libraries/haiguan.php");
// //配置
$db = array(
    'dsn' => 'mysqli:host=127.0.0.1;dbname=718blg',
    'host' => '127.0.0.1',
    // 'dbname' => '718blg0828',
    'dbname' => '718blg',
    'username' => 'root',
    'password' => 'ZLC8@7hT1N#mE.Q5u',
      // 'password' => 'root',
    'charset' => 'utf8',
);
//建立连接
$link = mysqli_connect($db['host'], $db['username'], $db['password']) or die( 'Could not connect: '  .  mysqli_error ());
//选择数据库
mysqli_select_db($link,$db['dbname'] ) or die ( 'Can\'t use foo : '  .  mysqli_error ($link));

mysqli_set_charset($link,$db['charset'] );

//获取海关发起的请求
$data = file_get_contents("php://input");
// $data_array_orgin = explode('=', $data);
// $data_json = @urldecode($data_array_orgin[1]);
// var_dump ($data);die;
// //echo $data_json;//die;
$data =  json_decode($data,true);
// $order_sn = $_POST['orderNo'];
// echo json_encode($order_sn);
$order_sn = $data['orderNo'];
if($order_sn)
{
   
	
	//判断条件，入库单编号是否存在
	$result  = mysqli_query($link,"SELECT * FROM 718shop_order WHERE order_sn=$order_sn" );
    // var_dump($result);die;
	$rs=mysqli_fetch_array($result);
    // var_dump($rs);die;
	$order_id = $rs['order_id'];
    $order_state =$rs['order_state'];
    // var_dump($order_id);die;
	if($order_id){
	        $order_state=40;
            $finnshed_time=time();
            // $finnshed_time=$data['serviceTime'];
        $update = "update 718shop_order set order_state=$order_state, finnshed_time=$finnshed_time where order_id=$order_id";
        mysqli_query($link,$update);
        if($update){
            // $message = @urlencode('订单已修改状态！');
             $message = '订单已修改状态！';
             $message=iconv("GB2312","UTF-8//IGNORE",$message);
        $message = @urlencode($message);
        $respose_array = array('code'=>'10000' , 'message'=>$message,'serviceTime'=>time());
        $respose = json_encode($respose_array);
        $respose = urldecode($respose);
        echo $respose;
        }else{
        // $message = @urlencode('订单修改错误！');
            $message = '订单修改错误！';
        $message=iconv("GB2312","UTF-8//IGNORE",$message);
        $message = @urlencode($message);
        $respose_array = array('code'=>'40000' , 'message'=>$message,'serviceTime'=>time());
        $respose = json_encode($respose_array);
        $respose = urldecode($respose);
        echo $respose;
        }
		
	}else{
		// $message = @urlencode('该订单号确认收货失败！');
         $message = '该订单号确认收货失败！';
        $message=iconv("GB2312","UTF-8//IGNORE",$message);
        $message = @urlencode($message);
		$respose_array = array('code'=>'20000' , 'message'=>$message,'serviceTime'=>time());
        $respose_array=iconv("GB2312","UTF-8//IGNORE",$respose_array);
		$respose = json_encode($respose_array);
		$respose = urldecode($respose);
		echo $respose;
	}

}else{
	// $message = @urlencode('未接收到传输信息,数据传输格式不对！');
    $message = '未接收到传输信息！';
    $message=iconv("GB2312","UTF-8//IGNORE",$message);
    $message = @urlencode($message);
	$respose_array = array('code'=>'30000' , 'message'=>$message,'serviceTime'=>time());
	$respose = json_encode($respose_array);
	$respose = urldecode($respose);
	echo $respose;
}

?> 