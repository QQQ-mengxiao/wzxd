

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>支付宝报关接口接口</title>
</head>
<?php
/* *
 * 功能：报关接口接入页
 * 版本：3.3
 * 修改日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************注意*************************
 * 如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
 * 1、商户服务中心（https://b.alipay.com/support/helperApply.htm?action=consultationApply），提交申请集成协助，我们会有专业的技术工程师主动联系您协助解决
 * 2、商户帮助中心（http://help.alipay.com/support/232511-16307/0-16307.htm?sh=Y&info_type=9）
 * 3、支付宝论坛（http://club.alipay.com/read-htm-tid-8681712.html）
 * 如果不想使用扩展功能请把扩展功能参数赋空值。
 */

require_once("alipay.config.php");
require_once("lib/alipay_submit.class.php");

/**************************请求参数**************************/

        //报关流水号

        $out_request_no = $_POST['WIDout_request_no'];
        //支付宝交易号

        $trade_no = $_POST['WIDtrade_no'];
        //商户海关备案编号

        
        //$merchant_customs_code = $_POST['WIDmerchant_customs_code'];
        //$merchant_customs_code = "4101965092";
        $merchant_customs_code = "4101968871";
        //商户海关备案名称

        //$merchant_customs_name = $_POST['WIDmerchant_customs_name'];
        //$merchant_customs_name = "郑州国际陆港开发建设有限公司";
        $merchant_customs_name = "河南中陆进出口贸易有限公司";
        
        //报关金额

        $amount = $_POST['WIDamount'];
        //海关编号
        //$customs_place = $_POST['WIDcustoms_place'];
        $customs_place = "ZONGSHU";


/************************************************************/

//构造要请求的参数数组，无需改动
$parameter = array(
		"service" => "alipay.acquire.customs",
		"partner" => trim($alipay_config['partner']),
		"out_request_no"	=> $out_request_no,
		"trade_no"	=> $trade_no,
		"merchant_customs_code"	=> $merchant_customs_code,
		"merchant_customs_name"	=> $merchant_customs_name,
		"amount"	=> $amount,
		"customs_place"	=> $customs_place,
		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
);

//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestHttp($parameter);
//解析XML
//注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
$doc = new DOMDocument();
$doc->loadXML($html_text);

//请在这里加上商户的业务逻辑程序代码

//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

//解析XML
if( ! empty($doc->getElementsByTagName( "alipay" )->item(0)->nodeValue) ) {
	$alipay = $doc->getElementsByTagName( "alipay" )->item(0)->nodeValue;
	echo $alipay;
        //echo "<br/>+jinp1+<br/>";
        $is_success = $doc->getElementsByTagName("is_success")->item(0)->nodeValue;

        echo "<br/>";
        
        echo  '$is_success='.$is_success;

        echo "<br/>";
        //echo "<br/>+jinp2+";

        if("$is_success" == "T"){

                //$result_code = $doc->getElementsByTagName("response")->item(0)->getElementsByTagName( "alipay" )->item(0)->getElementsByTagName("result_code")->item(0)->nodeValue；
               // echo "<br/> $result_code";


                echo "<br/>支付宝报关请求成功<br/>";

                //echo "<br/>+jinp3+<br/>";
                $alipay_result_code = $doc->getElementsByTagName( "alipay" )->item(1)->getElementsByTagName("result_code")->item(0)->nodeValue;
                echo '$alipay_result_code='.$alipay_result_code;
                //echo "<br/>+jinp4+<br/>";


                if("$alipay_result_code" == "SUCCESS"){
                     echo "<br/>支付宝业务正常受理并报关成功<br/>";

                     //更改订单报关状态 kuaj_order_bill_state(重点),根据相关的订单号order_id来查找相应的信息

                      $out_request_no = $doc->getElementsByTagName("request")->item(0)->getElementsByTagName("param")->item(8)->nodeValue;
                       echo "郑欧商城订单号_报关流水号：$out_request_no";

                        $order_sn = $out_request_no;
                        if($order_sn <= 0){
                            showMessage(L('miss_order_number'),$_POST['ref_url'],'html','error');
                         }

                        // echo "<br/>报关流水号：$order_sn <br/>";



                        

                        
                      //  $model_order = Model('order');

                        //获取订单详细
                    //    $condition = array();
                    //    $condition['order_sn'] = $order_sn;
                    //    $order_info = $model_order->getOrderInfo($condition);

                        //if ($_GET['crossborder_pay_state'] == '1') {
                        //   $result = $this->crossborder_order_change($order_info);
                        // } 

                    //    $order_id = $order_info['order_id'];
                    //    $model_order = Model('order');
                    //    $logic_order = Logic('order');
                        //$if_allow = $model_order->getOrderOperateState('system_cancel',$order_info);
                        
                    //    $result =  $logic_order->changeCrossborderOrderState($order_info,'system', $this->admin_info['name']);
                                        
                                        


                       
                        
                 

                        




                        








                }elseif("$alipay_result_code" == "FAIL"){
                        echo "<br/>支付宝报关请求成功但是业务受理失败<br/>";
                        //window.alert('报关失败！！！')；

                        


                }


                //for($i=0;$i<10;$i++){

                //$trade_no = $doc->getElementsByTagName("request")->item(0)->getElementsByTagName("param")->item($i)->nodeValue;
                //echo "param $i=+++++ $trade_no +++++";

                //}
                 //echo "<br/>+jinp5+<br/>";
                 //报关流水号为i=8(第9个)param

                 $out_request_no = $doc->getElementsByTagName("request")->item(0)->getElementsByTagName("param")->item(8)->nodeValue;
                //echo "郑欧商城订单号_报关流水号：$out_request_no";

                 //支付宝交易号i=3 

                 $trade_no = $doc->getElementsByTagName("request")->item(0)->getElementsByTagName("param")->item(3)->nodeValue;

                 echo "<br/>支付宝交易号：$trade_no <br/>";

                 //echo "<br/>+jinp6+<br/>";

                //$time=time();
                // echo "$time";


                 //06231530保存不了XML文件
                  //$doc->saveXML("$html_text");

                 //echo "<br/>+jinp7+<br/>";


         }
         //06231535

         elseif("$is_success" == "F"){

               echo "支付宝报关请求失败或者接入数据错误";

          }



        //$alipay_trade_no = $doc->getElementsByTagName( "alipay" )->item(1)->getElementsByTagName("trade_no")->item(0)->nodeValue;
        //echo '$alipay_result_code='.$alipay_trade_no;
        //echo $alipay->saveXML();

   }

//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

?>
</body>

<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script type="text/javascript">
$(function(){
    $('#query_start_time').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_time').datepicker({dateFormat: 'yy-mm-dd'});
    $('#ncsubmit').click(function(){
        $('input[name="op"]').val('index');$('#formSearch').submit();
    });
});
</script> 

</html>
