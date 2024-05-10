<?php
/**
 * 
 *跨境接口第2版
 * 
 */
defined('In718Shop') or exit('Access Invalid!');
class kuajing2Logic {

public  function DorderJM78() {


		$model_order = Model('order');
		//$model_goods = Model('goods');
		//$model_order_goods = Model('order_goods');
		$model_country = Model('kuajing_country');
		
		$condition = array();
		$order_id = $_GET['order_id'];
		$condition['order_id'] = $order_id;
		
		$order_list = $model_order->getOrderList($condition,5,'*','order_id desc','',array('order_goods','order_common','member','goods_kuajing_d'));


//Start baowen_1

$guid_logic = Logic('guid');
$guid = $guid_logic->guid_create();


//报文版本号 默认1.0  version    required
$version = "1.0";

//发送方代码  sendCode    required
$sendCode = 'sendCode';

//接收方代码  reciptCode    required
$reciptCode = 'reciptCode';

$baowen_1 = <<<abc
<?xml version="1.0" encoding="UTF-8"?>
<ENT311Message xmlns="http://www.chinaport.gov.cn/ENT" guid="$guid" version="$version" sendCode="$sendCode" reciptCode="$reciptCode">
	<Order>
abc;

//--End baowen_1
//---------------

//--Start baowen_3

$baowen_3='';


//获取orderlist具体的商品信息

$gnum = 0;
$gross_weight_total = 0;
$net_weight_total = 0;


//$value['kuajing_info']
foreach ($order_list[$order_id]['extend_order_goods'] as $value) {

	$value['kuajing_info'] = @unserialize($value['kuajing_info']);

	$goods_id = $value['goods_id'];



//从1开始的递增序号    gnum
$gnum = $gnum + 1;

//电商企业自定义的商品货号（SKU）    itemNo
$itemNo    = $value['kuajing_info']['itemNo'];

//交易平台销售商品的中文名称    itemName
$itemName = $value['goods_name'];

//交易平台销售商品的描述信息    itemDescribe    非必填
$itemDescribe = $value['kuajing_info']['itemDescribe'];
if ($itemDescribe == '') {$itemDescribe = $itemName;}

//国际通用的商品条形码，一般由前缀部分、制造厂商代码、商品代码和校验码组成    barCode    非必填



//填写海关标准的参数代码，参照《JGS-20 海关业务代码集》- 计量单位代码    unit
$unit = $value['kuajing_info']['unit'];

//商品实际数量    qty
$qty = $value['goods_num'];

//商品单价。赠品单价填写为“0”    price
$price = round($value['goods_pay_price']/$value['goods_num'],2);


//商品总价，等于单价乘以数量    totalPrice
$goodsNum = $value['goods_num'];
$totalPrice = $price * $goodsNum;

//限定为人民币，填写“142”    currency
$currency = "142";  //人民币

//表头用
//原产国
$country_origin_ID     = $value['kuajing_info']['country_origin_ID'];
$country_origin_guan   = $model_country->getfby_country_id($country_origin_ID,'code_guan');
$country_origin_jian   = $model_country->getfby_country_id($country_origin_ID,'code_jian');
//贸易国别
$country_trade_ID     = $value['kuajing_info']['country_trade_ID'];
$country_trade_guan   = $model_country->getfby_country_id($country_trade_ID,'code_guan');
$country_trade_jian   = $model_country->getfby_country_id($country_trade_ID,'code_jian');


//填写海关标准的参数代码，参照《JGS-20 海关业务代码集》-国家（地区）代码表    country  原产国
//----------------------------------------------------------------------------------
//$country = $kuajingArr[0]['country_origin'];
//$country = "133";
$country   = $country_origin_guan;

//促销活动，商品单价偏离市场价格的，可以在此说明    note



//海关备案商品编号    itemno    非必填

//申报品名    goodname    非必填


//规格型号    specifications
//-------------------------------------------------
$specifications = $value['kuajing_info']['specifications'];

//HS编号    ciqbarcode    非必填
$ciqbarcode = $value['kuajing_info']['ciqbarcode'];

//行邮税号    taxid    非必填


//是否赠品    flag
$flag = "N";
if($price==0){$flag = "Y";}

//检验检疫商品备案编号    goodidinsp
$goodidinsp = $value['kuajing_info']['goodidinsp'];


//订单编号    orderid    非必填
//货物名称(英文)    goodnameenglish    非必填


//毛重    weigth   非必填
$weight = $value['kuajing_info']['weight'] * $goodsNum;

//重量单位代码    weightunit   非必填
$weightunit = $value['kuajing_info']['weightunit'];

//净重  --自定义
$net_weight = $value['kuajing_info']['net_weight'] * $goodsNum;

$gross_weight_total = $gross_weight_total + $weight;
$net_weight_total   = $net_weight_total + $net_weight;

//包装类型代码    packcategoryinsp
$packcategoryinsp = $value['kuajing_info']['packcategoryinsp'];


//废旧标识    wasterornotinsp    非必填

//备注    remarksinsp    非必填


//币制(检验检疫)    coininsp
$coininsp = "156";//中国

//计量单位(检验检疫)    unitinsp
$unitinsp = $value['kuajing_info']['unitinsp'];

//原产国(检验检疫)    srccountryinsp
//----------------------------------------------------------------------------------下一步加入
//$srccountryinsp = "410";
$srccountryinsp = $country_origin_jian;

//关联H2010项号(保税模式必填，一般模式非必填)    gno    非必填



//表头用
//发货人姓名    senderusername
$senderusername = $value['kuajing_info']['senderusername'];
//发货人地址    senderuseraddress
$senderuseraddress = $value['kuajing_info']['senderuseraddress'];
//发货人电话    senderusertelephone
$senderusertelephone = $value['kuajing_info']['senderusertelephone'];


$baowen_3_1 = <<<abc
<OrderList>
			<gnum>$gnum</gnum>
			<itemNo>$itemNo</itemNo>
			<itemName>$itemName</itemName>
			<itemDescribe>$itemDescribe</itemDescribe>
			<barCode>$barCode</barCode>
			<unit>$unit</unit>
			<qty>$qty</qty>
			<price>$price</price>
			<totalPrice>$totalPrice</totalPrice>
			<currency>$currency</currency>
			<country>$country</country>
			<note>$note</note>
			<goodname>$goodname</goodname>
			<specifications>$specifications</specifications>
			<ciqbarcode>$ciqbarcode</ciqbarcode>
			<flag>$flag</flag>
			<goodidinsp>$goodidinsp</goodidinsp>
			<goodnameenglish>$goodnameenglish</goodnameenglish>
			<weightunit>$weightunit</weightunit>
			<packcategoryinsp>$packcategoryinsp</packcategoryinsp>
			<wasterornotinsp>$wasterornotinsp</wasterornotinsp>
			<remarksinsp>$remarksinsp</remarksinsp>
			<coininsp>$coininsp</coininsp>
			<unitinsp>$unitinsp</unitinsp>
			<srccountryinsp>$srccountryinsp</srccountryinsp>
			<gno>$gno</gno>
		</OrderList>
abc;
$baowen_3 = $baowen_3.$baowen_3_1;
}

//--End baowen_3
//---------------
//Start baowen_2

//企业系统生成36位唯一序号（英文字母大写） guid


//企业报送类型。1-新增 2-变更 3-删除。默认为1  appType
$appType = $_GET['op_type'];

//企业报送时间。格式:YYYYMMDDhhmmss。mss      appTime
$appTime = date("YmdHis",time());              

//业务状态:1-暂存,2-申报,默认为2    appStatus
$appStatus = "2";

//电子订单类型：I进口    电子订单类型：I进口    orderType
$orderType = "I";

//交易平台的订单编号，同一交易平台的订单编号应唯一。订单编号长度不能超过60位     orderNo
//$orderNo = $order_list[$order_id]['pay_sn']."_1";  //用支付单号当订单号，临时处理
$orderNo = $order_list[$order_id]['order_sn'];

//电商平台的海关注册登记编号；ebpCode (电商平台未在海关注册登记，由电商企业发送订单的，以中国电子口岸发布的电商平台标识编号为准）  
$ebpCode = "4101965092";

//电商平台的海关注册登记名称；ebpName（电商平台未在海关注册登记，由电商企业发送订单的，以中国电子口岸发布的电商平台名称为准）
$ebpName = "郑州国际陆港开发建设有限公司";//--------------------------------------------

//电商企业的海关注册登记编号  ebcCode
$ebcCode = "4101917335";

//电商企业的海关注册登记名称  ebcName
$ebcName = "郑州郑欧贸易有限公司";//----------------------------------------

//商品实际成交价，含非现金抵扣金额  goodsValue
$goodsValue = $order_list[$order_id]['goods_amount'];

//不包含在商品价格中的运杂费，无则填写"0"  freight
$freight = "0";

//使用积分、虚拟货币、代金券等非现金支付金额，无则填写"0"   discount
$discount = "0";

//企业预先代扣的税款金额，无则填写“0”  taxTotal
//$taxTotal = "0";
$taxTotal = $order_list[$order_id]['store_tax_total'];

//商品价格+运杂费+代扣税款-非现金抵扣金额，与支付凭证的支付金额一致  acturalPaid
$acturalPaid = $order_list[$order_id]['order_amount'];

//限定为人民币，填写“142”  currency
$currency = "142";  

//订购人的交易平台注册号  buyerRegNo
$buyerRegNo = $order_list[$order_id]['buyer_name'];

//订购人的真实姓名    buyerName
$buyerName = $order_list[$order_id]['extend_order_common']['reciver_name'];

//1-身份证,2-其它。限定为身份证，填写“1”    buyerIdType
$buyerIdType = "1";

//订购人的身份证件号码    buyerIdNumber
$buyerIdNumber = $order_list[$order_id]['extend_order_common']['reciver_info']['id_card'];


//支付企业的海关注册登记编号  payCode    非必填
//支付企业在海关注册登记的企业名称  payName    非必填
if($order_list[$order_id]['payment_code'] == 'alipay'){
	$payCode = "312226T001";  //支付宝代码  -总署
	$payName = "支付宝（中国）网络技术有限公司";
}else if(($order_list[$order_id]['payment_code'] == 'wx_saoma')||($order_list[$order_id]['payment_code'] == 'wxpay')){
	$payCode = "440316T004";  //财付通代码
	$payName = "财付通支付科技有限公司";
}else {
	$payCode = "";  
	$payName = "";
}




//支付企业唯一的支付流水号  payTransactionId  非必填
//写到下面了 使用 $paynumber

//商品批次号  batchNumbers    非必填


//收货人姓名，必须与电子运单的收货人姓名一致  consignee
$consignee  = $order_list[$order_id]['extend_order_common']['reciver_name'];

//收货人联系电话，必须与电子运单的收货人电话一致  consigneeTelephone
$consigneeTelephone  = substr($order_list[$order_id]['extend_order_common']['reciver_info']['phone'],0,11);

//收货地址，必须与电子运单的收货地址一致  consigneeAddress
$consigneeAddress = $order_list[$order_id]['extend_order_common']['reciver_info']['address'];


//参照国家统计局公布的国家行政区划标准填制  consigneeDistrict    非必填

//订单备注  note  非必填


//订单总价  ordersum
$ordersum = $order_list[$order_id]['order_amount'];//---------------------------待验证正确性


//其它费用  otherfee  非必填

//进口行邮费  taxfee  非必填

//收货人所在国(海关代码)    collectionusercountry   非必填

//发货人所在国(海关代码)    senderusercountry    非必填


//发货人姓名    senderusername
//$senderusername = "The Kyong International";//-------------------------------------------稍后处理
//$senderusername = $shipperArr[0]['shipper_name'];

//发货人地址    senderuseraddress
//$senderuseraddress = "Oryu-dong,Seo-gu,INcheon,Korea";
//$senderuseraddress = $shipperArr[0]['shipper_address'];


//发货人电话    senderusertelephone
//$senderusertelephone = "0082-032-568-5600";
//$senderusertelephone = $shipperArr[0]['shipper_phone'];


//模式代码    billmode 模式代码1、 一般模式 ； 2、 保税模式
$billmode = "1";

//是否有废旧物品(检验检疫)    wasterornot
$wasterornot = "N";  //放到运单手动录入

//是否带有植物性包装及铺垫材料(检验检疫)    botanyornot
$botanyornot = "N";  //放到运单手动录入


//缴税单位海关备案编码    taxedenterprise    非必填

//电商检验检疫CIQ备案编号    cbecodeinsp    
$cbecodeinsp = "4100606874";

//电商平台检验检疫CIQ备案编号    ecpcodeinsp    非必填


//物流企业CIQ检验检疫备案编号    trepcodeinsp
$trepcodeinsp = "4100910023";//--申通

//订单提交时间    submittime
$addTime = $order_list[$order_id]['add_time'];
$submittime = date("YmdHis",$addTime); //-----------------------------需确定时间格式

//贸易国别(检验检疫)    tradecompany
//$tradecompany = "410";
$tradecompany = $country_trade_jian;

//总费用单位(检验检疫)    totalfeeunit
$totalfeeunit = "156";

//商品种类数(检验检疫)    countofgoodstype
//***************************************************************************************待验证正确性
$countofgoodstype = $gnum;

//毛重(检验检疫)    weight
$weight = $gross_weight_total;

//毛重单位(检验检疫)    weightunit
//$weightunit = "kg";
$weightunit = $weightunit;

//净重(检验检疫)    netweight
$netweight = $net_weight_total;

//净重单位(检验检疫)    netweightunit
//$netweightunit = "kg";
$netweightunit = $weightunit;

//平台网址   platformurl    非必填


//发货人所在国(检验检疫)    collusercountryinsp
//$collusercountryinsp = "410";
$collusercountryinsp = $country_trade_jian;

//收货人所在国(检验检疫)    sendusercountryinsp
$sendusercountryinsp = "156";//中国

//支付交易号    paynumber
//交易流水号
            $model_order_log = Model('order_log');
            $logdata = $model_order_log->where(array('order_id'=>$order_id,'log_msg'=>array('like','%支付平台交易号%')))->select();
            $tradeNo = explode(' ',$logdata[0]['log_msg']);
            $paynumber = $tradeNo[4];
//****************************************

//支付企业海关代码    payenterprisecode    非必填

//支付企业海关名称    payenterprisename    非必填

//其他支付金额    otherpayment    非必填

//其它支付备注    otherpaymenttype   非必填

//S账册号    lmsno   非必填

//关联H2010账册号    manualno   非必填


//订购人名称    purchasername
$purchasername = $buyerName;

//订购人注册号    buyerregno
$buyerregno = $buyerRegNo;


//订购人电话    purchasertelephone
$purchasertelephone = $consigneeTelephone;



$baowen_2 = <<<abc
		<OrderHead>
			<guid>$guid</guid>
			<appType>$appType</appType>
			<appTime>$appTime</appTime>
			<appStatus>$appStatus</appStatus>
			<orderType>$orderType</orderType>
			<orderNo>$orderNo</orderNo>
			<ebpCode>$ebpCode</ebpCode>
			<ebpName>$ebpName</ebpName>
			<ebcCode>$ebcCode</ebcCode>
			<ebcName>$ebcName</ebcName>
			<goodsValue>$goodsValue</goodsValue>
			<freight>$freight</freight>
			<discount>$discount</discount>
			<taxTotal>$taxTotal</taxTotal>
			<acturalPaid>$acturalPaid</acturalPaid>
			<currency>$currency</currency>
			<buyerRegNo>$buyerRegNo</buyerRegNo>
			<buyerName>$buyerName</buyerName>
			<buyerIdType>$buyerIdType</buyerIdType>
			<buyerIdNumber>$buyerIdNumber</buyerIdNumber>
			<payCode>$payCode</payCode>
			<payName>$payName</payName>
			<consignee>$consignee</consignee>
			<consigneeTelephone>$consigneeTelephone</consigneeTelephone>
			<consigneeAddress>$consigneeAddress</consigneeAddress>
			<note>$note</note>
			<ordersum>$ordersum</ordersum>
			<senderusername>$senderusername</senderusername>
			<senderuseraddress>$senderuseraddress</senderuseraddress>
			<senderusertelephone>$senderusertelephone</senderusertelephone>
			<billmode>$billmode</billmode>
			<wasterornot>$wasterornot</wasterornot>
			<botanyornot>$botanyornot</botanyornot>
			<cbecodeinsp>$cbecodeinsp</cbecodeinsp>
			<ecpcodeinsp>$ecpcodeinsp</ecpcodeinsp>
			<trepcodeinsp>$trepcodeinsp</trepcodeinsp>
			<submittime>$submittime</submittime>
			<tradecompany>$tradecompany</tradecompany>
			<totalfeeunit>$totalfeeunit</totalfeeunit>
			<countofgoodstype>$countofgoodstype</countofgoodstype>
			<weight>$weight</weight>
			<weightunit>$weightunit</weightunit>
			<netweight>$netweight</netweight>
			<netweightunit>$netweightunit</netweightunit>
			<platformurl>$platformurl</platformurl>
			<collusercountryinsp>$collusercountryinsp</collusercountryinsp>
			<sendusercountryinsp>$sendusercountryinsp</sendusercountryinsp>
			<paynumber>$paynumber</paynumber>
			<payenterprisecode>$payenterprisecode</payenterprisecode>
			<payenterprisename>$payenterprisename</payenterprisename>	
			<otherpaymenttype>$otherpaymenttype</otherpaymenttype>
			<lmsno>$lmsno</lmsno>
			<manualno>$manualno</manualno>
			<purchasername>$purchasername</purchasername>
			<buyerregno>$buyerregno</buyerregno>
			<purchasertelephone>$purchasertelephone</purchasertelephone>
		</OrderHead>
abc;
//--End baowen_2
//---------------
//--Start baowen_4

//报文传输的企业代码（需要与接入客户端的企业身份一致）    copCode
$copCode = '4101917335';

//报文传输的企业名称    copName
$copName = '郑州郑欧贸易有限公司';

//默认为DXP；指中国电子口岸数据交换平台    dxpMode
$dxpMode = "DXP";

//向中国电子口岸数据中心申请数据交换平台的用户编号    dxpId
$dxpId = 'DXPENT0000011502';


//备注    note    非必填



$baowen_4 = <<<abc
	</Order>
	<BaseTransfer>
		<copCode>$copCode</copCode>
		<copName>$copName</copName>
		<dxpMode>$dxpMode</dxpMode>
		<dxpId>$dxpId</dxpId>
		<note>$note</note>
	</BaseTransfer>
</ENT311Message>
abc;

$baowen = $baowen_1.$baowen_2.$baowen_3.$baowen_4;
return $baowen;

/*
try {
$baowen = $baowen_1.$baowen_2.$baowen_3.$baowen_4;

$dirName = 'orderpush/'.date("Ymd");
if (!file_exists($dirName)){ mkdir ($dirName); } 
$file_name = $dirName."/".$appTime."_".$orderNo.".xml";

$myfile = fopen($file_name, 'w');//存在时打开，不存在时新建;
fputs($myfile,   $baowen);//向文件中写入内容; 
fclose($myfile); 

return "成功生成订单报文！";
} catch (SOAPFault $e) {
    		//print_r('Exception:'.$e);
    		return '生成失败';
}
*/


}



//---------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------
function rsa_publickey_encrypt($pubk, $data) {
    $pubk = openssl_get_publickey($pubk);
    openssl_public_encrypt($data, $en, $pubk, OPENSSL_PKCS1_PADDING);
    return $en;
	}
	

	function rsa_encrypt($method, $key, $data, $rsa_bit = 1024) {
    	$inputLen = strlen($data);
    	$offSet = 0;
    	$i = 0;
 
    	$maxDecryptBlock = $rsa_bit / 8 - 11;
 
    	$en = '';
 
    	// 对数据分段加密
    	while ($inputLen - $offSet > 0) {
        	if ($inputLen - $offSet > $maxDecryptBlock) {
           	 	$cache = $this->$method($key, substr($data, $offSet, $maxDecryptBlock));
        		} else {
           		 $cache = $this->$method($key, substr($data, $offSet, $inputLen - $offSet));
        	}
 
        	$en = $en . $cache;
 
        	$i++;
        	$offSet = $i * $maxDecryptBlock;
    	}
    	return $en;
	}


/*
/*申通运单推送
/*
/*
/*
/**/
	public function sto_baowen_Push($data = array()) {
		header('Content-Type:text/html;charset=gbk');

	$baowen = $this->sto_baowen_JM($data);

	////echo iconv("UTF-8", "GBK//IGNORE", $baowen );
	$data_digest = urlencode(base64_encode(md5($baowen)));

$public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQD
DXmrh+xMnUR7OMY5ztb3jvcu9F1GpULgHFKQydh
8NqVdKUcChTgdWcnFnLi6QlqwxnOtPQi/daRVmF
RvT3NVVC6rn2IqPt1gUI+oaWViuXXsAfcVPbcqY
KDcYIweP0ERHd8x9Jtq80nuyfINULdBM8zzyt6V
h7upftR+oZ4vTSwIDAQAB
-----END PUBLIC KEY-----
';

$baowen_jm = $this->rsa_encrypt('rsa_publickey_encrypt', $public_key, $baowen);


	$postArray=array(
	"version"=>'3.0',
	"encrypt_type"=>'RSA',
	"billinfo_interface"=>urlencode(base64_encode($baowen_jm)),
	"data_digest"=>$data_digest,
	"key"=>'!gKmsKk{J#8F2e6Ptt'
	);

	$data = http_build_query($postArray);

	////echo ($data);
	//$URL = "http://vip.hnsto.cn:88/hnsto/servlet/EPortBillinfoInterfaceRSA";
	$URL = "http://interface.hnsto.cn:88/hnsto/servlet/eportBillinfoInterface";
 
			$ch = curl_init($URL);
			//curl_setopt($ch, CURLOPT_MUTE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			//curl_setopt($ch, CURLOPT_HEADER,0);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded;charset=utf-8'));
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			curl_close($ch);
			//echo $output;
			//return iconv("UTF-8", "GBK//IGNORE", $output);

			return $output;

	}


//申通运单报文组装
//
//
//
//
public   function sto_baowen_JM($data = array()) {
//header('Content-Type:text/html;charset=gbk');

		$model_order = Model('order');
		//$model_goods = Model('goods');
		$model_country = Model('kuajing_country');
		$model_trans_type = Model('kuajing_trans_type');
		$model_trans_tool = Model('kuajing_trans_tool');
		
		$condition = array();
		$order_id = $_GET['order_id'];
		$condition['order_id'] = $order_id;
		
		$order_list = $model_order->getOrderList($condition,5,'*','order_id desc','',array('order_goods','order_common','member','goods_kuajing_d'));



//------------------------
//baowen_1 Start
$baowen_1 = <<< abc
<?xml version="1.0" encoding="utf-8"?>
<rows>
abc;

//baowen_1 End
//------------------------------
//baowen_3 Start 货物清单

$gnum = 0;
$gross_weight_total = 0;
$net_weight_total = 0;

foreach ($order_list[$order_id]['extend_order_goods'] as $value) {

	$value['kuajing_info'] = @unserialize($value['kuajing_info']);

	$goods_id = $value['goods_id'];
	

//从1开始的递增序号    gnum
$gnum = $gnum + 1;

//货号  itemNo     非必填
$itemNo    = $value['kuajing_info']['itemNo'];

//货品名  itemName     非必填
$itemName = $value['goods_name'];

$itemNameTotal = $itemNameTotal.$itemName.'/';

//货物规格  itemModel     非必填
$specifications = $value['kuajing_info']['specifications'];

//货物数量  itemQuantity     非必填
$itemQuantity = $value['goods_num'];

$itemQuantityTotal = $itemQuantityTotal + $itemQuantity;

//货物描述  itemDescribe     非必填

//毛重    weigth   非必填
$weight = $value['kuajing_info']['weight'];

//重量单位代码    weightunit   非必填
$weightunit = $value['kuajing_info']['weightunit'];

//净重  --自定义
$net_weight = $value['kuajing_info']['net_weight'];

$gross_weight_total = $gross_weight_total + $weight;
$net_weight_total   = $net_weight_total + $net_weight;

//表头用
//原产国
$country_origin_ID     = $value['kuajing_info']['country_origin_ID'];
$country_origin_guan   = $model_country->getfby_country_id($country_origin_ID,'code_guan');
$country_origin_jian   = $model_country->getfby_country_id($country_origin_ID,'code_jian');
//贸易国别
$country_trade_ID     = $value['kuajing_info']['country_trade_ID'];
$country_trade_guan   = $model_country->getfby_country_id($country_trade_ID,'code_guan');
$country_trade_jian   = $model_country->getfby_country_id($country_trade_ID,'code_jian');

$baowen_3_1 = <<< abc

<item id="$gnum">
<itemNo>$itemNo</itemNo>
<itemName>$itemName</itemName>
<itemModel>$itemModel</itemModel>
<itemQuantity>$itemQuantity</itemQuantity>
<itemDescribe>$itemDescribe</itemDescribe>
</item>

abc;

$baowen_3 = $baowen_3.$baowen_3_1;
}


//baowen_3 End
//---------------------------------
//baowen_2 Start

//运输方式
$transType_id = $order_list[$order_id]['extend_order_common']['waybill_info']['transType'];
$trans_type_name   = $model_trans_type->getfby_trans_type_id($transType_id,'trans_type_name');
$trans_type_guan   = $model_trans_type->getfby_trans_type_id($transType_id,'code_guan');
$trans_type_jian   = $model_trans_type->getfby_trans_type_id($transType_id,'code_jian');
//运输工具
$transTool_id = $order_list[$order_id]['extend_order_common']['waybill_info']['transTool'];
$trans_tool_name   = $model_trans_tool->getfby_tool_id($transTool_id,'tool_name');
$trans_tool_guan   = $model_trans_tool->getfby_tool_id($transTool_id,'code_guan');
$trans_tool_jian   = $model_trans_tool->getfby_tool_id($transTool_id,'code_jian');


//申报日期  declareDate
$declareDate =  date("Y-m-d",time());

//客户编号  StoCustomerID
$StoCustomerID = 91381;

//电商平台代码  ecpCode     非必填   
//**********************************************必填？
$ecpCode = '4101965092';


//电商平台名称  ecpName     非必填
//**********************************************必填？
$ecpName = '郑州国际陆港开发建设有限公司';

//电商企业代码  cbeCode
//*****************************************************
$cbeCode = '4101965092';//填成平台的了？

//订单号  orderNo
//$orderNo = $order_list[$order_id]['pay_sn'];//."_1";  //支付单号当订单号，临时处理用
$orderNo = $order_list[$order_id]['order_sn'];

//校验码  CheckData
$CheckData = md5($StoCustomerID.'||'.$cbeCode.'||'.$orderNo);

//申通运单编号  LogisticsNo         方案一非必填，返回运单号


//申通跟踪单号  TrackingNo     非必填


//国外发件人  shipper     非必填


//国外发件地址  shipperAddress     非必填


//国外发件电话  shipperTelephone     非必填


//发件人所在国(海)  shipperCountryCus
//$shipperCountryCus = "133";
$shipperCountryCus = $country_trade_guan;

//国内发件人  Consignor     非必填


//国内发件人电话  TelephoneNum     非必填


//国内发件地址  ConsignorAdd     非必填


//收件人  consignee
$consignee = $order_list[$order_id]['extend_order_common']['reciver_name'];


//证件类型  idType     非必填
//******************************************必填？
$idType = "TOC001";

//证件号码  customerId     非必填
//******************************************必填？
$customerId = $order_list[$order_id]['extend_order_common']['reciver_info']['id_card'];


//收件人电话  consigneeTelephone
$consigneeTelephone = substr($order_list[$order_id]['extend_order_common']['reciver_info']['phone'],0,11);


//收件省份  Province     非必填


//收件市  City     非必填


//收件区  Zone     非必填


//收件地址  consigneeAddress
$consigneeAddress = $order_list[$order_id]['extend_order_common']['reciver_info']['address'];

//收件人所在国(检)  consigneeCountryCiq
$consigneeCountryCiq = "156";

//收件人所在国(海)  consigneeCountryCus
$consigneeCountryCus = "142";

//毛量(Kg)  weight
$weight = round($gross_weight_total,2);

//净重(Kg)  netWeight
$netWeight = round($net_weight_total,2);

//件数  packNum     非必填


//商品数量  quantity
$quantity = $itemQuantityTotal;

//进出口标识  ieType
$ieType = "I";

//备注  note


//总单号  totalLogisticsNo
//*****************************************************
//$totalLogisticsNo = "78416990304_HOS132993"; //20160714
//$totalLogisticsNo = "78417504572_HOS133412"; //20161108
$totalLogisticsNo = $order_list[$order_id]['extend_order_common']['waybill_info']['totalLogisticsNo'];

//货物品名  goodsName
//*****************************************************
$goodsName = $itemNameTotal;

//装运港/指运港  destinationPort       非必填


//包装类别代码(海)  packageTypeCus
$packageTypeCus = "2";

//包装类别代码(检)  packageTypeCiq
$packageTypeCiq = "4M";

//运输方式代码(检)  transportationMethod
$transportationMethod = $trans_type_jian;

//运输工具代码(检)  shipCodeInsp
$shipCodeInsp = $trans_tool_jian;

//贸易国别  tradeCountry 关
//$tradeCountry = "133";
$tradeCountry = $country_trade_guan;

//进/出境日期  jcbOrderTime
//***************************************************
//$jcbOrderTime = "2016-07-13";//20160714
$jcbOrderTime = $order_list[$order_id]['extend_order_common']['waybill_info']['jcbOrderTime'];


//进/出境口岸  jcbOrderPort  关
//*****************************************************
//$jcbOrderPort = '4604';
$jcbOrderPort = $order_list[$order_id]['extend_order_common']['waybill_info']['jcbOrderPort'];


//运输方式代码  transferType 关
//$transferType = "5";
$transferType = $trans_type_guan;

//进/出境口岸(检) jcbOrderPortInsp  检
//*****************************************************
//$jcbOrderPortInsp = '410010';
$jcbOrderPortInsp = $order_list[$order_id]['extend_order_common']['waybill_info']['jcbOrderPortInsp'];


//运输工具名称(关)  shipName
$shipName = $trans_tool_name;

//运输工具名称(检)  shipNameInsp
$shipNameInsp = $trans_tool_name;

//检疫申报口岸  applyPortInsp   考虑是否做成可编辑的
//*****************************************************
$applyPortInsp = '410020'; //410020 河南局郑州东站口岸


//检疫起运/抵运国代码  transferRegionInsp
//*****************************************************
//$transferRegionInsp = '410';  //410韩国
$transferRegionInsp = $country_trade_jian;

//电商企业国检备案编号  cbeCodeInsp
//*****************************************************
$cbeCodeInsp = '4100606874';



//币制代码  coinInsp 检
$coinInsp = "156";


//电商企业代码  cbeCode
//*****************************************************
//$cbeCode = '4101965092';


//电商企业名称  cbeName      非必填


//海关申报口岸代码  declarePort
//*****************************************************
$declarePort = '4606';


//操作标识  modifyMark     非必填  1 新增，2 修改系统默认为 1最多允许三次修改
$modifyMark = $_GET['op_type'];

//集货/备货标识  stockFlag   1-集货，2-备货
$stockFlag = "1";



$baowen_2 = <<< abc
<row id="1">
<CheckData>$CheckData</CheckData>
<declareDate>$declareDate</declareDate>
<StoCustomerID>$StoCustomerID</StoCustomerID>
<ecpCode>$ecpCode</ecpCode>
<ecpName>$ecpName</ecpName>
<orderNo>$orderNo</orderNo>
<LogisticsNo />
<TrackingNo />
<shipper>$shipper</shipper>
<shipperAddress>$shipperAddress</shipperAddress>
<shipperTelephone>$shipperTelephone</shipperTelephone>
<shipperCountryCus>$shipperCountryCus</shipperCountryCus>
<Consignor>$Consignor</Consignor>
<TelephoneNum>$TelephoneNum</TelephoneNum>
<ConsignorAdd>$ConsignorAdd</ConsignorAdd>
<consignee>$consignee</consignee>
<idType>$idType</idType>
<stockFlag>$stockFlag</stockFlag>
<customerId>$customerId</customerId>
<consigneeTelephone>$consigneeTelephone</consigneeTelephone>
<Province>$Province</Province>
<City>$City</City>
<Zone>$Zone</Zone>
<consigneeAddress>$consigneeAddress</consigneeAddress>
<consigneeCountryCiq>$consigneeCountryCiq</consigneeCountryCiq>
<consigneeCountryCus>$consigneeCountryCus</consigneeCountryCus>
<weight>$weight</weight>
<netWeight>$netWeight</netWeight>
<quantity>$quantity</quantity>
<ieType>$ieType</ieType>
<totalLogisticsNo>$totalLogisticsNo</totalLogisticsNo>
<destinationPort>$destinationPort</destinationPort>
<packageTypeCus>$packageTypeCus</packageTypeCus>
<packageTypeCiq>$packageTypeCiq</packageTypeCiq>
<transportationMethod>$transportationMethod</transportationMethod>
<shipCodeInsp>$shipCodeInsp</shipCodeInsp>
<tradeCountry>$tradeCountry</tradeCountry>
<jcbOrderTime>$jcbOrderTime</jcbOrderTime>
<jcbOrderPort>$jcbOrderPort</jcbOrderPort>
<transferType>$transferType</transferType>
<jcbOrderPortInsp>$jcbOrderPortInsp</jcbOrderPortInsp>
<shipName>$shipName</shipName>
<shipNameInsp>$shipNameInsp</shipNameInsp>
<applyPortInsp>$applyPortInsp</applyPortInsp>
<transferRegionInsp>$transferRegionInsp</transferRegionInsp>
<cbeCodeInsp>$cbeCodeInsp</cbeCodeInsp>
<coinInsp>$coinInsp</coinInsp>
<cbeCode>$cbeCode</cbeCode>
<cbeName>$cbeName</cbeName>
<declarePort>$declarePort</declarePort>
<modifyMark>$modifyMark</modifyMark>
<goodsName>$goodsName</goodsName>
<goodsItem>
abc;


//baowen_2 End

//--------------------------
//baowen_4 Start

$baowen_4 = <<< abc
</goodsItem>
<note></note>
</row>
abc;

//baowen_4 End
//------------------------
//baowen_5 Start

$baowen_5 = <<< abc
</rows>
abc;

//baowen_5 End
//------------------------

$baowen = $baowen_1.$baowen_2.$baowen_3.$baowen_4.$baowen_5;

// print_r($baowen);
// break;
//echo iconv("UTF-8", "GBK//IGNORE", $baowen );
return $baowen;

/*
$baowen_linshi = <<< abc
<?xml version="1.0" encoding="utf-8"?>
<rows><row id="1">
<CheckData>1dec629343db84b380291c04bc6eaa35</CheckData>
<declareDate>2016-11-29</declareDate>
<StoCustomerID>91381</StoCustomerID>
<ecpCode>4101965092</ecpCode>
<ecpName>郑州国际陆港开发建设有限公司</ecpName>
<orderNo>8000000000415901</orderNo>
<LogisticsNo />
<TrackingNo />
<shipper>The Kyong International</shipper>
<shipperAddress>Oryu-dong,Seo-gu,INcheon,Korea</shipperAddress>
<shipperTelephone>0082-032-568-5600</shipperTelephone>
<shipperCountryCus>133</shipperCountryCus>
<Consignor></Consignor>
<TelephoneNum>13526831934</TelephoneNum>
<ConsignorAdd>河南郑州市金水区金水路英协路楷林国际五楼A座平安保险两核管理部</ConsignorAdd>
<consignee>段清杰</consignee>
<idType>TOC001</idType>
<stockFlag>1</stockFlag>
<customerId>410221198404191823</customerId>
<consigneeTelephone>13526831934</consigneeTelephone>
<Province></Province>
<City></City>
<Zone></Zone>
<consigneeAddress>河南郑州市金水区金水路英协路楷林国际五楼A座平安保险两核管理部</consigneeAddress>
<consigneeCountryCiq>156</consigneeCountryCiq>
<consigneeCountryCus>142</consigneeCountryCus>
<weight>0.7</weight>
<netWeight>0.5</netWeight>
<quantity>1</quantity>
<ieType>I</ieType>
<totalLogisticsNo>78417507350</totalLogisticsNo>
<destinationPort></destinationPort>
<packageTypeCus>2</packageTypeCus>
<packageTypeCiq>4M</packageTypeCiq>
<transportationMethod>5</transportationMethod>
<shipCodeInsp>50</shipCodeInsp>
<tradeCountry>133</tradeCountry>
<jcbOrderTime>2016-11-27</jcbOrderTime>
<jcbOrderPort>4604</jcbOrderPort>
<transferType>5</transferType>
<jcbOrderPortInsp>410010</jcbOrderPortInsp>
<shipName>飞机</shipName>
<shipNameInsp>飞机</shipNameInsp>
<applyPortInsp>410020</applyPortInsp>
<transferRegionInsp>410</transferRegionInsp>
<cbeCodeInsp>4100606874</cbeCodeInsp>
<coinInsp>156</coinInsp>
<cbeCode>4101965092</cbeCode>
<cbeName></cbeName>
<declarePort>4606</declarePort>
<modifyMark>1</modifyMark>
<goodsName>Clinie可莱丝NMF针剂水库面膜（跨境直邮） 2盒/组（10片/盒）/</goodsName>
<goodsItem>
<item id="1">
<itemNo></itemNo>
<itemName>Clinie可莱丝NMF针剂水库面膜（跨境直邮） 2盒/组（10片/盒）</itemName>
<itemModel></itemModel>
<itemQuantity>1</itemQuantity>
<itemDescribe></itemDescribe>
</item>
</goodsItem>
<note></note>
</row></rows>
abc;

return $baowen_linshi;
*/
 }

 	//申通-获取热敏打印单号
 	public   function sto_number_get() {

header('Content-Type:text/html;charset=gbk');

//请求时间 Rdate  格式：yyyy-mm-ddhh24:mi:ss
$Rdate = "2016-07-11 15:32:00";

//客户编号 CustomerID  由申通分配
$CustomerID = "90000";

//申请号码数 Rnumber    大于 1 且小于等于12000
$Rnumber = "1";

//校验码 CheckData   MD5
$CheckData = md5($CustomerID.$Rnumber."@Sto.2014");


$bw = <<<abc
<?xml version="1.0" encoding="UTF-8"?>
<RequestInfo>
	<CheckData>$CheckData</CheckData>
	<Rdate>$Rdate</Rdate>
	<LastSuccessSn>8375</LastSuccessSn>
	<CustomerID>$CustomerID</CustomerID>
	<Key>ti7ldVJuppUWqjCU</Key>
	<Rnumber>$Rnumber</Rnumber>
</RequestInfo>
abc;

echo ($bw);
$postArray=array(
	"billnum_interface"=>$bw,
	//"key"=>'ti7ldVJuppUWqjCU'
	);

$data = http_build_query($postArray);
//$data = "billnum_interface=".http_build_query($bw);

//urlencode(iconv('UTF-8','GBK//IGNORE',utf8_encode($bw)));
echo ($data);
$URL = "http://vip.hnsto.cn:88/hnsto/servlet/RequestStoBillNumberVip";
 
			$ch = curl_init($URL);
			//curl_setopt($ch, CURLOPT_MUTE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			//curl_setopt($ch, CURLOPT_HEADER,0);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded;charset=GBK'));
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			curl_close($ch);
			//echo $output;
			return iconv("UTF-8", "GBK//IGNORE", $output);

	 }


	/**
	 * 电子口岸（总署版）清单生成
	 */
	public  function DlistJM78() {


		$model_order = Model('order');
		//$model_goods = Model('goods');
		$model_country = Model('kuajing_country');
		$model_trans_type = Model('kuajing_trans_type');
		$model_trans_tool = Model('kuajing_trans_tool');
		
		$condition = array();
		$order_id = $_GET['order_id'];
		$condition['order_id'] = $order_id;
		
		$order_list = $model_order->getOrderList($condition,5,'*','order_id desc','',array('order_goods','order_common','member','goods_kuajing_d'));

$guid_logic = Logic('guid');
$guid = $guid_logic->guid_create();


//start baowen_1
$baowen_1 = <<<abc
<?xml version="1.0" encoding="UTF-8"?>
<ENT621Message xmlns="http://www.chinaport.gov.cn/ENT"  guid="$guid" version="v1.0" sendCode="sendcode" reciptCode="reciptcode">
	<Inventory>
abc;

//end baowen_1
//-------------------------
//-------------------------
//start 表体  baowen_3
$baowen_3='';

//获取orderlist具体的商品信息

$gnum = 0;
$gross_weight_total = 0;
$net_weight_total = 0;
$value_total = 0;

foreach ($order_list[$order_id]['extend_order_goods'] as $value) {

$value['kuajing_info'] = @unserialize($value['kuajing_info']);

$goods_id = $value['goods_id'];


// 序号	gnum	N4	是	从1开始连续序号，与关联的电子订单表体序号一一对应。
$gnum = $gnum + 1;

// 账册备案料号	itemRecordNo	C..30	否	保税进口必填
//
//

// 企业商品货号	itemNo	C..20	是	电商企业自定义的商品货号（SKU）。
$itemNo    = $value['kuajing_info']['itemNo'];


// 企业商品品名	itemName	C..250	是	交易平台销售商品的中文名称。
$itemName = $value['goods_name'];

// 商品编码	gcode	C10	是	按商品分类编码规则确定的进出口商品的商品编号，分为商品编号和附加编号，其中商品编号栏应填报《中华人民共和国进出口税则》8位税则号列，附加编号应填报商品编号，附加编号第9、10位。
$gcode = $value['kuajing_info']['ciqbarcode'];

// 商品名称	gname	C..250	是	商品名称应据实填报，与电子订单一致。
$gname = $value['goods_name'];

// 商品规格型号	gmodel	C..250	是	满足海关归类、审价以及监管的要求为准。包括：品牌、规格、型号等。
$gmodel = $value['kuajing_info']['specifications'];

// 条码	barCode	C..50	否	商品条码一般由前缀部分、制造厂商代码、商品代码和校验码组成。没有条码填“无”
//
//

//表头用
//原产国
$country_origin_ID     = $value['kuajing_info']['country_origin_ID'];
$country_origin_guan   = $model_country->getfby_country_id($country_origin_ID,'code_guan');
$country_origin_jian   = $model_country->getfby_country_id($country_origin_ID,'code_jian');
//贸易国别
$country_trade_ID     = $value['kuajing_info']['country_trade_ID'];
$country_trade_guan   = $model_country->getfby_country_id($country_trade_ID,'code_guan');
$country_trade_jian   = $model_country->getfby_country_id($country_trade_ID,'code_jian');


// 原产国（地区）	country	C3	是	海关标准的参数代码  《JGS-20 海关业务代码集》 国家（地区）代码表填写代码。
//$country = '133';  
$country = $country_origin_guan;

// 币制	currency	C3	是	限定为人民币，填写“142”。
$currency = '142';

//ciqcurrency
$ciqcurrency = '156';

// 数量	qty	N19,4	是	按成交计量单位的实际数量
$qty = $value['goods_num'];

// 法定数量	qty1	N19,4	是	按照商品编码规则对应的法定计量单位的实际数量填写。
// 法定第一数量=净重*数量
//$qty1 = $value['kuajing_info']['qty1'];
$qty1 = $value['kuajing_info']['net_weight'] * $qty;

// 第二数量	qty2	N19,4	否	
$qty2 = $value['kuajing_info']['qty2'];

// 计量单位	unit	C3	是	海关标准的参数代码  《JGS-20 海关业务代码集》- 计量单位代码
$unit = $value['kuajing_info']['unit'];

// 法定计量单位	unit1	C3	是	海关标准的参数代码 《JGS-20 海关业务代码集》- 计量单位代码
$unit1 = $value['kuajing_info']['unit1'];

// 第二计量单位	unit2	C3	否	海关标准的参数代码 《JGS-20 海关业务代码集》- 计量单位代码
$unit2 = $value['kuajing_info']['unit2'];

// 单价	price	N19,2	是	成交单价
$price = round($value['goods_pay_price']/$value['goods_num'],2);

// 总价	totalPrice	N19,2	是	成交总价
$totalPrice = $price * $qty;

// 备注	note	C..1000	否	
//
//

//毛重    weigth   非必填
$weight = $value['kuajing_info']['weight'];

//重量单位代码    weightunit   非必填
$weightunit = $value['kuajing_info']['weightunit'];

//净重  --自定义
$net_weight = $value['kuajing_info']['net_weight'];

$gross_weight_total = $gross_weight_total + $weight;
$net_weight_total   = $net_weight_total + $net_weight;

//用于计算货值
$value_total = $value_total + $totalPrice;

//表头用
//发货人姓名    senderusername
$senderusername = $value['kuajing_info']['senderusername'];
//发货人地址    senderuseraddress
$senderuseraddress = $value['kuajing_info']['senderuseraddress'];
//发货人电话    senderusertelephone
$senderusertelephone = $value['kuajing_info']['senderusertelephone'];



$baowen_3_1 = <<<abc
<InventoryList>
			<gnum>$gnum</gnum>
			<itemRecordNo>$itemRecordNo</itemRecordNo>
			<itemNo>$itemNo</itemNo>
			<itemName>$itemName</itemName>
			<gcode>$gcode</gcode>
			<gname>$gname</gname>
			<gmodel>$gmodel</gmodel>
			<barCode>$barCode</barCode>
			<country>$country</country>
			<currency>$currency</currency>
			<ciqcurrency>$ciqcurrency</ciqcurrency>
			<qty>$qty</qty>
			<unit>$unit</unit>
			<qty1>$qty1</qty1>
			<unit1>$unit1</unit1>
			<qty2>$qty2</qty2>
			<unit2>$unit2</unit2>
			<price>$price</price>
			<totalPrice>$totalPrice</totalPrice>
			<note>$note</note>
		</InventoryList>
abc;
$baowen_3 = $baowen_3.$baowen_3_1;
}


//end  表体 baowen_3
// ----------------------------
// ----------------------------
// ----------------------------
// ----------------------------
//Start 表头 baowen_2

//运输方式
$transType_id = $order_list[$order_id]['extend_order_common']['waybill_info']['transType'];
$trans_type_name   = $model_trans_type->getfby_trans_type_id($transType_id,'trans_type_name');
$trans_type_guan   = $model_trans_type->getfby_trans_type_id($transType_id,'code_guan');
$trans_type_jian   = $model_trans_type->getfby_trans_type_id($transType_id,'code_jian');
//运输工具
$transTool_id = $order_list[$order_id]['extend_order_common']['waybill_info']['transTool'];
$trans_tool_name   = $model_trans_tool->getfby_tool_id($transTool_id,'tool_name');
$trans_tool_guan   = $model_trans_tool->getfby_tool_id($transTool_id,'code_guan');
$trans_tool_jian   = $model_trans_tool->getfby_tool_id($transTool_id,'code_jian');



//系统唯一序号  guid  企业系统生成36位唯一序号（英文字母大写）
//$guid = '';

// 报送类型	appType		是	企业报送类型。1-新增 2-变更 3-删除。默认为1。
$appType = $_GET['op_type'];

// 报送时间	appTime		是	企业报送时间。格式:YYYYMMDDhhmmss。
$appTime = date("YmdHis",time());

// 业务状态	appStatus		是	业务状态:1-暂存,2-申报,默认为1。填写2时,Signature节点必须填写.
$appStatus = '2';

// 订单编号	orderNo		是	交易平台的订单编号，同一交易平台的订单编号应唯一。订单编号长度不能超过60位。
$orderNo = $order_list[$order_id]['order_sn'];

// 电商平台代码	ebpCode		是	电商平台的海关注册登记编号；电商平台未在海关注册登记，由电商企业发送订单的，以中国电子口岸发布的电商平台标识编号为准。
$ebpCode = "4101965092";

// 电商平台名称	ebpName		是	电商平台的海关注册登记名称；电商平台未在海关注册登记，由电商企业发送订单的，以中国电子口岸发布的电商平台名称为准。
$ebpName = '郑州国际陆港开发建设有限公司';

// 电商企业代码	ebcCode		是	电商企业的海关注册登记编号。
$ebcCode = '4101917335';

// 电商企业名称	ebcName		是	电商企业的海关注册登记名称。
$ebcName = '郑州郑欧贸易有限公司';

// 物流运单编号	logisticsNo		是	物流企业的运单包裹面单号。同一物流企业的运单编号在6个月内不重复。运单编号长度不能超过60位。
$logisticsNo = $order_list[$order_id]['extend_order_common']['waybill_info']['logisticsNo'];

// 物流企业代码	logisticsCode		是	物流企业的海关注册登记编号。
$logisticsCode = '4101985823';

// 物流企业名称	logisticsName		是	物流企业在海关注册登记的名称。
$logisticsName = '河南申通实业有限公司';

// 企业内部编号	copNo		是	企业内部标识单证的编号。
$copNo = date("Ymd",time()).substr($orderNo,-8);

// 预录入编号	preNo		否	电子口岸标识单证的编号。
//
//

// 担保企业编号	assureCode		是	担保扣税的企业海关注册登记编号，只限清单的电商平台企业、电商企业、物流企业。
$assureCode = '4101917335';

// 账册编号	emsNo		否	保税模式必填，填写区内仓储企业在海关备案的账册编号，用于保税进口业务在特殊区域辅助系统记账（二线出区核减）。
// 清单编号	invtNo		否	海关接受申报生成的清单编号。

// 进出口标记	ieFlag		是	I-进口,E-出口
$ieFlag = 'I';

// 申报日期	declTime		是	申报日期，以海关计算机系统接受清单申报数据时记录的日期为准。格式:YYYYMMDD。
$declTime = date("Ymd",time());

// 申报海关代码	customsCode		是	接受清单申报的海关关区代码，参照JGS/T 18《海关关区代码》。
$customsCode = '4606';

// 口岸海关代码	portCode		是	商品实际进出我国关境口岸海关的关区代码，参照JGS/T 18《海关关区代码》。
$portCode = '4604';

// 进口日期	ieDate		否	运载所申报商品的运输工具申报进境的日期，进口申报时无法确知相应的运输工具的实际进境日期时，免填。格式:YYYYMMDD
//
$ieDate = $order_list[$order_id]['extend_order_common']['waybill_info']['jcbOrderTime'];
if($ieDate!=''){
	$ieDate = str_replace("-","",$ieDate);
}

// 订购人证件类型	buyerIdType		是	1-身份证,2-其它。限定为身份证，填写“1”。
$buyerIdType = '1';

// 订购人证件号码	buyerIdNumber		是	订购人的身份证件号码。
$buyerIdNumber = $order_list[$order_id]['extend_order_common']['reciver_info']['id_card'];

// 订购人姓名	buyerName		是	订购人的真实姓名。
$buyerName = $order_list[$order_id]['extend_order_common']['reciver_name'];

// 订购人电话	buyerTelephone		是	订购人电话。
$buyerTelephone = substr($order_list[$order_id]['extend_order_common']['reciver_info']['phone'],0,11);


// 收件地址	consigneeAddress		是	收件地址。
$consigneeAddress = $order_list[$order_id]['extend_order_common']['reciver_info']['address'];

// 申报企业代码	agentCode		是	申报单位的海关注册登记编号。
$agentCode = '4101985699';

// 申报企业名称	agentName		是	申报单位在海关注册登记的名称。
$agentName = '郑州聚通国际货运代理有限公司';

// 区内企业代码	areaCode		否	保税模式必填，区内仓储企业的海关注册登记编号。
// 区内企业名称	areCame		否	保税模式必填，区内仓储企业在海关注册登记的名称。


// 贸易方式	tradeMode		是	直购进口填写“9610”，保税进口填写“1210”。
$tradeMode = '9610';

// 运输方式	trafMode		是	填写海关标准的参数代码，参照《JGS-20 海关业务代码集》- 运输方式代码。直购进口指跨境段物流运输方式，保税进口指二线出区物流运输方式。
$trafMode = $trans_type_guan;

// 运输工具编号	trafNo		是	直购进口必填。货物进出境的运输工具的名称或运输工具编号。填报内容应与运输部门向海关申报的载货清单所列相应内容一致；同报关单填制规范。保税进口免填。
$trafNo = $trans_tool_name ;

// 航班航次号	voyageNo		否	直购进口必填。货物进出境的运输工具的航次编号。保税进口免填。
$voyageNo = $order_list[$order_id]['extend_order_common']['waybill_info']['voyageNo'];

// 提运单号	billNo		否	直购进口必填。货物提单或运单的编号，保税进口免填。
//根据目前了解的情况，暂时提运单号按总运单号
$billNo = $order_list[$order_id]['extend_order_common']['waybill_info']['totalLogisticsNo'];  

// 监管场所代码	loctNo		否	针对同一申报地海关下有多个跨境电子商务的监管场所,需要填写区分
$loctNo = '4101965092';


// 许可证件号	licenseNo		否	商务主管部门及其授权发证机关签发的进出口货物许可证件的编号


// 起运国（地区）	country		是	直购进口填写起始发出国家（地区）代码，参照《JGS-20 海关业务代码集》的国家（地区）代码表；保税进口填写代码“142”。
//$country = '133';
$country = $country_trade_guan;

// 运费	freight		是	物流企业实际收取的运输费用。
$freight = '0';

// 保费	insuredFee		是	物流企业实际收取的商品保价费用。
$insuredFee = '0';

// 币制	currency		是	限定为人民币，填写“142”。
$currency = '142';

// 包装种类代码	wrapType		否	海关对进出口货物实际采用的外部包装方式的标识代码，采用1 位数字表示，如：木箱、纸箱、桶装、散装、托盘、包、油罐车等
//
//

// 件数	packNo		是	件数为包裹数量，限定为“1”。
$packNo = '1';

// 毛重（公斤）	grossWeight		是	货物及其包装材料的重量之和，计量单位为千克。
$grossWeight = $gross_weight_total;

// 净重（公斤）	netWeight		是	货物的毛重减去外包装材料后的重量，即货物本身的实际重量，计量单位为千克。
$netWeight = $net_weight_total;

// 备注	note		否	
//
//

// 清单编号（检验检疫）	detailscode		是	【v1.5.1变更】6位检验检疫机构代码+1位模式代码+10位申报企业CIQ编码+4位年份+2位月份+2位日期+8位流水       共33位
$detailscode = '410020I4100910036'.$declTime.substr($orderNo,-8); 

// 进出口岸(代码)	ieport		是	进出口岸(代码)（检验检疫）
$ieport = '410010';

// 单位联系人	contact		是	单位联系人
$contact = '邢文莉';

// 联系电话	fixedtelephone		是	联系电话
$fixedtelephone = '15136025201';

// 电商代码	companycode		是	电商代码（检验检疫）
$companycode = '4100606874';

// 电商名称	companyname		是	电商名称
$companyname = '郑州郑欧贸易有限公司';

// 物流企业代码	logistprisescode		是	物流企业代码
$logistprisescode = '4100910023';

// 物流企业名称	logistprisesname		是	物流企业名称
$logistprisesname = '河南申通实业有限公司';

// 是否废旧	isold		是	0否  1是
$isold = '0';

// 是否带有植物性包装及铺垫材料	vegpacmaterials		是	0否  1是
$vegpacmaterials = '0';

// 附件名称	fjname		否	附件名称
// 附件类型	fjtype		否	附件类型


// 运单模式（0 、一般模式 1、保税模式）	wbmode		是	运单模式（0 、一般模式 1、保税模式）
$wbmode = '0';

// 进出境日期	ietime		是	进出境日期
$ietime = $order_list[$order_id]['extend_order_common']['waybill_info']['jcbOrderTime'];
if($ietime!=''){
	$ietime = str_replace("-","",$ietime);
}

// 运输方式（代码）	transtion		是	运输方式（代码）
$transtion = '50';

// 申报口岸代码（属地检验检疫机构）	applyport		是	申报口岸代码（属地检验检疫机构）
$applyport = '410020';

// 支付编号	applycode		是	支付编号
$model_order_log = Model('order_log');
$logdata = $model_order_log->where(array('order_id'=>$order_id,'log_msg'=>array('like','%支付平台交易号%')))->select();
$tradeNo = explode(' ',$logdata[0]['log_msg']);
$applycode = $tradeNo[4];

// 件数	packingnum		是	件数
$packingnum = $gnum;

// 发货人名称	sendname		是	发货人名称
$sendname = $senderusername;

// 运输工具名称	transtionname		是	运输工具名称
$transtionname = $trans_tool_name;

// 起运国（地区）/(代码)	sendctiycode		是	起运国（地区）/(代码)
//$sendctiycode = '410';
$sendctiycode = $country_trade_jian;

// 申报种类（1：新增、2：变更）	bashstste		是	申报种类（1：新增、2：变更）
$bashstste = $appType;

// 收货人姓名	recname		是	收货人姓名
$recname = $order_list[$order_id]['extend_order_common']['reciver_name'];

// 货值	goodsvalue		是	货值
$goodsvalue = $value_total;

// 货值单位代码	goodsvaluecode		是	货值单位代码
$goodsvaluecode = '156';

// 毛重（公斤）	grossweight		是	毛重（公斤）
$grossweight = $gross_weight_total;

// 收货人电话	recphone		是	收货人电话
$recphone = substr($order_list[$order_id]['extend_order_common']['reciver_info']['phone'],0,11);

// 收货人国别代码	reccontray		是	收货人国别代码
$reccontray = '156';

// 申报企业名称	applyunitname		是	申报企业名称
$applyunitname = '郑州聚通国际货运代理有限公司';

// 申报企业备案编码	applyunitcode		是	申报企业备案编码
$applyunitcode = '4100910036';

// 总运单号	wbcountcode		是	总运单号
$wbcountcode = $order_list[$order_id]['extend_order_common']['waybill_info']['totalLogisticsNo'];

// 批次号	wbpccond		是	批次号
$wbpccond = $declTime;

// // 支付企业检验检疫备案编号	payecode		是	【 v1.5新增】
// $payecode = '4109600025';

// // 支付企业检验检疫备案名称	payename		是	【 v1.5新增】
// $payename = '支付宝（中国）网络技术有限公司';


//支付方式支持微信
// 支付企业检验检疫备案编号	payecode		是	【 v1.5新增】
// 支付企业检验检疫备案名称	payename		是	【 v1.5新增】

if($order_list[$order_id]['payment_code'] == 'alipay'){
	$payCode = "4109600025";  //支付宝代码  -总署
	$payName = "支付宝（中国）网络技术有限公司";
}else if(($order_list[$order_id]['payment_code'] == 'wx_saoma')||($order_list[$order_id]['payment_code'] == 'wxpay')){
	$payCode = "不知道";  //财付通代码  //P460400004
	$payName = "财付通支付科技有限公司";
}else {
	$payCode = "";  
	$payName = "";
}


$baowen_2 = <<<abc
<InventoryHead>
			<guid>$guid</guid>
			<appType>$appType</appType>
			<appTime>$appTime</appTime>
			<appStatus>$appStatus</appStatus>
			<orderNo>$orderNo</orderNo>
			<ebpCode>$ebpCode</ebpCode>
			<ebpName>$ebpName</ebpName>
			<ebcCode>$ebcCode</ebcCode>
			<ebcName>$ebcName</ebcName>
			<logisticsNo>$logisticsNo</logisticsNo>
			<logisticsCode>$logisticsCode</logisticsCode>
			<logisticsName>$logisticsName</logisticsName>
			<copNo>$copNo</copNo>
			<preNo/>
			<assureCode>$assureCode</assureCode>
			<emsNo>$emsNo</emsNo>
			<invtNo/>
			<ieFlag>$ieFlag</ieFlag>
			<declTime>$declTime</declTime>
			<customsCode>$customsCode</customsCode>
			<portCode>$portCode</portCode>
			<ieDate>$ieDate</ieDate>
			<buyerIdType>$buyerIdType</buyerIdType>
			<buyerIdNumber>$buyerIdNumber</buyerIdNumber>
			<buyerName>$buyerName</buyerName>
			<buyerTelephone>$buyerTelephone</buyerTelephone>
			<consigneeAddress>$consigneeAddress</consigneeAddress>
			<agentCode>$agentCode</agentCode>
			<agentName>$agentName</agentName>
			<areaCode>$areaCode</areaCode>
			<areaName>$areaName</areaName>
			<tradeMode>$tradeMode</tradeMode>
			<trafMode>$trafMode</trafMode>
			<trafNo>$trafNo</trafNo>
			<voyageNo>$voyageNo</voyageNo>
			<billNo>$billNo</billNo>
			<loctNo>$loctNo</loctNo>
			<licenseNo/>
			<country>$country</country>
			<freight>$freight</freight>
			<insuredFee>$insuredFee</insuredFee>
			<currency>$currency</currency>
			<packNo>$packNo</packNo>
			<grossWeight>$grossWeight</grossWeight>
			<netWeight>$netWeight</netWeight>
			<note>$note</note>
			<detailscode>$detailscode</detailscode>
			<ieport>$ieport</ieport>
			<contact>$contact</contact>
			<fixedtelephone>$fixedtelephone</fixedtelephone>
			<companycode>$companycode</companycode>
			<companyname>$companyname</companyname>
			<logistprisescode>$logistprisescode</logistprisescode>
			<logistprisesname>$logistprisesname</logistprisesname>
			<isold>$isold</isold>
			<vegpacmaterials>$vegpacmaterials</vegpacmaterials>
			<fjname/>
			<fjtype/>
			<wbmode>$wbmode</wbmode>
			<ietime>$ietime</ietime>
			<transtion>$transtion</transtion>
			<applyport>$applyport</applyport>
			<applycode>$applycode</applycode>
			<packingnum>$packingnum</packingnum>
			<sendname>$sendname</sendname>
			<transtionname>$transtionname</transtionname>
			<sendctiycode>$sendctiycode</sendctiycode>
			<bashstste>$bashstste</bashstste>
			<recname>$recname</recname>
			<goodsvalue>$goodsvalue</goodsvalue>
			<goodsvaluecode>$goodsvaluecode</goodsvaluecode>
			<grossweight>$grossweight</grossweight>
			<recphone>$recphone</recphone>
			<reccontray>$reccontray</reccontray>
			<applyunitname>$applyunitname</applyunitname>
			<applyunitcode>$applyunitcode</applyunitcode>
			<wbcountcode>$wbcountcode</wbcountcode>
			<wbpccond>$wbpccond</wbpccond>
			<payecode>$payecode</payecode>
			<payename>$payename</payename>
		</InventoryHead>
abc;

//end 表头  baowen_2
// -----------------------------------
// -----------------------------------
//start baowen_4
// 传输企业代码	copCode	C..18	是	报文传输的企业代码（需要与接入客户端的企业身份一致）
$copCode = '4101985699';

// 传输企业名称	copName	C..100	是	报文传输的企业名称
$copName = '郑州聚通国际货运代理有限公司';

// 报文传输模式	dxpMode	C3	是	默认为DXP；指中国电子口岸数据交换平台
$dxpMode = 'DXP';

// 报文传输编号	dxpId	C..30	是	向中国电子口岸数据中心申请数据交换平台的用户编号
$dxpId = 'DXPENT0000011501';

// 备注	note	C..1000	否	
//
//

$baowen_4 = <<<abc
</Inventory>
	<BaseTransfer>
		<copCode>$copCode</copCode>
		<copName>$copName</copName>
		<dxpMode>$dxpMode</dxpMode>
		<dxpId>$dxpId</dxpId>
		<note>$note</note>
	</BaseTransfer>
</ENT621Message>
abc;

//end baowen_4

$baowen = $baowen_1.$baowen_2.$baowen_3.$baowen_4;
return $baowen;


	}



}

?>
