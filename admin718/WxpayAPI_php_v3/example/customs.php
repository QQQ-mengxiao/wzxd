<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" /> 
    <title>微信支付样例-支付报关</title>
</head>
<?php
//ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
require_once "../lib/WxPay.Api.php";
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("./logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

function printf_info($data)
{
    foreach($data as $key=>$value){
        echo "<font color='#f00;'>$key</font> : $value <br/>";
    }
}


if((isset($_REQUEST["transaction_id"]) && $_REQUEST["transaction_id"] != "")&&
	(isset($_REQUEST["out_trade_no"]) && $_REQUEST["out_trade_no"] != ""))

	{
	$transaction_id = $_REQUEST["transaction_id"];
	$out_trade_no = $_REQUEST["out_trade_no"];
	//jinp12092113
	$sub_order_no = $_REQUEST["sub_order_no"];
	$fee_type = $_REQUEST["fee_type"];
	$order_fee = $_REQUEST["order_fee"];
	$transport_fee = $_REQUEST["transport_fee"];
	$product_fee = $_REQUEST["product_fee"];

	$input = new WxPayCustoms();
	$input->SetTransaction_id($transaction_id);
	$input->SetOut_trade_no($out_trade_no);
	//jinp12092113
	$input->SetSub_order_no($sub_order_no);
	$input->SetFee_type($fee_type);
	$input->SetOrder_fee($order_fee);
	$input->SetTransport_fee($transport_fee);
	$input->SetProduct_fee($product_fee);

	printf_info(WxPayApi::customs($input));
	exit();
	}

//if(isset($_REQUEST["out_trade_no"]) && $_REQUEST["out_trade_no"] != ""){
//	$out_trade_no = $_REQUEST["out_trade_no"];
//	$input = new WxPayCustoms();
//	$input->SetOut_trade_no($out_trade_no);
//	printf_info(WxPayApi::customs($input));
//	exit();
//}

?>
<body>  
	<form action="#" method="post">
        <div style="margin-left:2%;color:#f00">微信订单号和商户订单号必填：</div><br/>
        <div style="margin-left:2%;">微信订单号：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="transaction_id" /><br /><br />
        <div style="margin-left:2%;">商户订单号：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="out_trade_no" /><br /><br />

        <div style="margin-left:2%;">商户子订单号：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="sub_order_no" /><br /><br />
        <div style="margin-left:2%;">币种：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="fee_type" /><br /><br />
        <div style="margin-left:2%;">应付金额：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="order_fee" /><br /><br />
        <div style="margin-left:2%;">物流费：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="transport_fee" /><br /><br />
        <div style="margin-left:2%;">商品价格：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="product_fee" /><br /><br />

		<div align="center">
			<input type="submit" value="微信支付报关" style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" />
		</div>
	</form>
</body>
</html>