<?php 
class test4Control extends BaseSellerControl {
    public function __construct() {
        parent::__construct ();
        Language::read ('member_store_goods_index');
    }
    public function indexOp() {
        $this->edit_beihuoOp();
    }



public function orderPushOp() {

	//$dataInfo = base64_encode($this->orderJM());
	$dataInfo = $this->orderJM();
	//$r = $this->orderJM();
	//echo $r;

$xmldata = <<<abc
xml=<?xml version="1.0" encoding="UTF-8"?>
<Root>
	<PubInfo>
		<Version>1.0</Version>  
		<CompanyCode>D00500</CompanyCode>
		<Key>87b115641af340ac883870a41cbe6842</Key>
		<MsgType>O</MsgType>    
		<OptType>1</OptType> 
		<Signature>2c746b6b27d9e8f097eb9c8de92711e4</Signature>
		<CreatTime>2016-04-21 12:12:56</CreatTime>
	</PubInfo>
	<DataInfo>
		$dataInfo
	</DataInfo>
</Root>
abc;
//echo $xmldata;

$URL = "http://218.28.185.212:9092/BIService/service/order/pushOrder";
 
			$ch = curl_init($URL);
			//curl_setopt($ch, CURLOPT_MUTE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xmldata);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			curl_close($ch);
			echo $output;

	

}





  public  function orderJM() {
    	$model = Model('ctax_shopemallyi');

$modelpt = Model('ctax_emallyibase');


//获取电商代码（检）
$ecCode = $model->getfby_shop_id(5,'eccode');

//获取电商代码（关）
$cbeCode = $model->getfby_shop_id(5,'cbecode');

//获取电商名称（检）
$ecName = $model->getfby_shop_id(5,'ecname');

//获取电商名称（关）
$cbeName = $model->getfby_shop_id(5,'cbename');

//获取电商平台代码（检）
$ecpCodeCiq = $modelpt->getfby_emallyi_id(1,'ecpcodeciq');

//获取电商平台代码（关）
$ecpCodeCus = $modelpt->getfby_emallyi_id(1,'ecpcodecus');

//获取电商平台名称（检）
$ecpNameCiq = $modelpt->getfby_emallyi_id(1,'ecpnameciq');

//获取电商平台名称（关）
$ecpNameCus = $modelpt->getfby_emallyi_id(1,'ecpnamecus');

//获取订单编号
$orderNo = $_GET['order_id'];

$jiamidata = <<<abc
<?xml version="1.0" encoding="utf-8"?>
<Order>
    <OrderHead>
		<ecCode>$ecCode</ecCode>       
		<cbeCode>$cbeCode</cbeCode>
		<ecName>$ecName</ecName>
		<cbeName>$cbeName</cbeName>
		<ecpCodeCiq>$ecpCodeCiq</ecpCodeCiq>
		<ecpCodeCus>$ecpCodeCus</ecpCodeCus>
		<ecpNameCiq>$ecpNameCiq</ecpNameCiq>
		<ecpNameCus>$ecpNameCus</ecpNameCus>
		<orderNo>$orderNo</orderNo>    
		<charge>总费用</charge>
		<goodsValue>货值</goodsValue>
		<freight></freight>
		<other></other>
		<tax></tax>
		<customer></customer>
		<shipper>发货人名称</shipper>
		<shipperAddress>发货人地址</shipperAddress>
		<shipperTelephone>发货人电话</shipperTelephone>
		<shipperCountryCiq>发货人所在国（检）</shipperCountryCiq>
		<shipperCountryCus>发货人所在国（关）</shipperCountryCus>
		<consignee>收货人名称</consignee>
		<consigneeProvince>收货人省份</consigneeProvince>
		<consigneeCity>收货人城市</consigneeCity>
		<consigneeZone>收货人区县</consigneeZone>
		<consigneeAddress>收货人地址</consigneeAddress>
		<consigneeTelephone>收货人电话</consigneeTelephone>
		<consigneeCountryCiq>收获人所在国（检）</consigneeCountryCiq>
		<consigneeCountryCus>收获人所在国（关）</consigneeCountryCus>
		<idType>证件类型</idType>
		<idNumber>证件号码</idNumber>
		<ieType>I</ieType>
		<stockFlag>2</stockFlag>
		<batchNumbers>批次号</batchNumbers>
		<totalLogisticsNo>总运单号</totalLogisticsNo>
		<tradeCountryCiq>贸易国别（检）</tradeCountryCiq>
		<tradeCountryCus>贸易国别（关）</tradeCountryCus>
		<agentCodeCiq>代理企业（检）</agentCodeCiq>
		<agentCodeCus>代理企业（关）</agentCodeCus>
		<agentNameCiq>代理企业名称（检）</agentNameCiq>						
		<agentNameCus>代理企业名称（关）</agentNameCus>
		<packageTypeCiq>包装种类（检）</packageTypeCiq>						
		<packageTypeCus>包装种类（关）</packageTypeCus>		
		<modifyMark>1</modifyMark>
		<note></note>
	</OrderHead>      
	<OrderList>
		<itemNoCiq></itemNoCiq>
		<itemNoCus></itemNoCus>
		<goodsNo>商品货号</goodsNo>
		<shelfGoodsName>商品上架品名</shelfGoodsName>
		<goodsName></goodsName>
		<describe></describe>
		<codeTs></codeTs>
		<ciqCode></ciqCode>
		<goodsModel></goodsModel>
		<taxCode></taxCode>
		<price></price>
		<currencyCiq>币制（检）</currencyCiq>
		<currencyCus>币制（关）</currencyCus>
		<quantity>数量</quantity>
		<priceTotal>成交总价</priceTotal>
		<unitCiq>计量单位（检）</unitCiq>
		<unitCus>计量单位（关）</unitCus>
		<discount></discount>
		<giftsFlag></giftsFlag>
		<originCountryCiq>原产国（检）</originCountryCiq>
		<originCountryCus>原产国（关）</originCountryCus>
		<usage></usage>
		<wasteMaterials>1</wasteMaterials>
		<wrapTypeCiq></wrapTypeCiq>
		<wrapTypeCus></wrapTypeCus>
		<packNum></packNum>
	</OrderList>
	<OrderPaymentLogistics>
		<paymentCode>支付企业代码</paymentCode>
		<paymentName>支付企业名称</paymentName>
		<paymentType></paymentType>
		<paymentNo>支付交易号</paymentNo>
		<logisticsCodeCiq>物流企业代码（检）</logisticsCodeCiq>
		<logisticsCodeCus>物流企业代码（关）</logisticsCodeCus>
		<logisticsNameCiq>物流企业名称（检）</logisticsNameCiq>		
		<logisticsNameCus>物流企业名称（关）</logisticsNameCus>
		<subLogisticsNo></subLogisticsNo>
		<logisticsNo></logisticsNo>
		<trackNo></trackNo>
		<trackStatus></trackStatus>
		<crossFreight></crossFreight>
		<supportValue></supportValue>
		<weight>毛重</weight>
		<netWeight></netWeight>
		<quantity></quantity>
		<deliveryWay></deliveryWay>
		<transportationWay>运输方式（检）</transportationWay>
		<shipCode>运输工具（检）</shipCode>
		<shipName></shipName>
		<destinationPort></destinationPort>
	</OrderPaymentLogistics>
</Order>
abc;



//$data ="123hjsjkgpdfleskmf";
$public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDBo64u
7wGn82rKHcpJnCnhOsej9hGqag0IDuGawP/Os74U7S18
oh96Yi6r3ZEwwZQvcaO4ssRNOOHO0pccdaspjP2jupn+
F4olXPpNmHHJ0I1oPNuKblhvRaU10l2UIfsKEccrB1Ue
QUQVRbzUjLGDAKA5IWft019ie08I5fQBMwIDAQAB
-----END PUBLIC KEY-----
';
//$encrypted = ""; 

//$pu_key = openssl_pkey_get_public($public_key);

//$ab = openssl_public_encrypt($xmldata,$encrypted,$public_key);
//echo $ab.'123';
//echo $encrypted;
$r = $this->rsa_encrypt('rsa_publickey_encrypt', $public_key, $jiamidata);
//$qianming= md5($jiamidata);
//return $qianming;
//$r = $this->rsa_publickey_encrypt($public_key, $qianming);
//$r = $this->rsa_encrypt('rsa_publickey_encrypt', $public_key, $qianming);
return urlencode(base64_encode($r));
//return $r;
//$rr = base64_encode($r);

/*
$URL = "http://218.28.185.212:9092/BIService/service/order/pushOrder";
 
			$ch = curl_init($URL);
			//curl_setopt($ch, CURLOPT_MUTE, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $rr);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			curl_close($ch);
			echo $output;
*/		
	
}


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

	

}
?>