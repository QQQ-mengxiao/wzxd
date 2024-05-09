<?php defined('In718Shop') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['order_manage'];?></h3>
      <ul class="tab-base">
        <li><a href="index.php?act=<?php echo $_GET['act'];?>&op=index"><span><?php echo $lang['manage'];?></span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span>微信支付报关接口</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>


  <!--
  <form method="post" name="form1" id="form1" action="index.php?act=<?php echo $_GET['act'];?>&op=crossborder_pay_change_state&crossborder_pay_state=2&order_id=<?php echo intval($_GET['order_id']);?>">
  -->

  <?php
//ini_set('date.timezone','Asia/Shanghai');
//error_reporting(E_ERROR);
//require_once "../lib/WxPay.Api.php";
require_once "../../WxpayAPI_php_v3/lib/WxPay.Api.php";
//require_once 'log.php';

//初始化日志
//$logHandler= new CLogFileHandler("./logs/".date('Y-m-d').'.log');
//$log = Log::Init($logHandler, 15);

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
//  $out_trade_no = $_REQUEST["out_trade_no"];
//  $input = new WxPayCustoms();
//  $input->SetOut_trade_no($out_trade_no);
//  printf_info(WxPayApi::customs($input));
//  exit();
//}

?>
  <form action="#" method="post">
        <div style="margin-left:2%;color:#f00"></div><br/>
        <div style="margin-left:2%;">微信订单号：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="transaction_id" value="<?php echo $output['order_info_jp']['pay_number'];?>"/><br /><br />
        <div style="margin-left:2%;">商户订单号：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="out_trade_no" value="<?php echo $output['order_info']['order_sn'];?>"/><br /><br />

        <div style="margin-left:2%;">商户子订单号：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="sub_order_no" value="<?php echo $output['order_info']['order_sn'];?>"/><br /><br />
        <div style="margin-left:2%;">币种：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="fee_type" value="CNY" /><br /><br />
        <div style="margin-left:2%;">应付金额：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="order_fee" value="<?php echo $output['order_info']['order_amount']*100;?>"/><br /><br />
        <div style="margin-left:2%;">物流费：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="transport_fee" value="0"/><br /><br />
        <div style="margin-left:2%;">商品价格：</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="product_fee" value="<?php echo $output['order_info']['order_amount']*100;?>"/><br /><br />

    <div align="center">
      <input type="submit" value="微信支付报关" style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" />
    </div>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script type="text/javascript">
$(function(){
    $('#payment_time').datepicker({dateFormat: 'yy-mm-dd',maxDate: '<?php echo date('Y-m-d',TIMESTAMP);?>'});
    $('#ncsubmit').click(function(){
    	if($("#form1").valid()){
        	if (confirm("操作提醒：\n提交前请务必确认是否需要跨境推单\n继续操作吗?")){
        	}else{
        		return false;
        	}
        	$('#form1').submit();
    	}
    });
	$("#form1").validate({
		errorPlacement: function(error, element){
			error.appendTo(element.parent().parent().prev().find('td:first'));
        },
        rules : {
        	
            WIDtrade_no    :{
                required : true
            }       
        },
        messages : {
        	payment_time : {
                required : '请填写付款准确时间'
            },
            payment_code : {
                required : '请选择付款方式'
            },
            WIDtrade_no : {
                required : '请填写第三方支付平台交易号'
            }
        }
	});
});
</script> 