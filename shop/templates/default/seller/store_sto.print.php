<?php
defined('In718Shop') or exit('Access Invalid!');
require_once template('TCPDF/tcpdf');
require_once template('TCPDF/examples/lang/chi');
include_once template('phpqrcode/phpqrcode');
//$pdf=new TCPDF(PDF_PAGE_ORIENTATION,'mm',array(110,200*$output['numbers']),true,'utf-8',false);
$pdf=new TCPDF(PDF_PAGE_ORIENTATION,'mm',array(100,180),true,'utf-8',false);
$pdf->setLanguageArray($l);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
// 设置默认等宽字体
$pdf->SetDefaultMonospacedFont('courier');

// 设置间距
$pdf->SetMargins(0, 0, 0);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);

// 设置分页
$pdf->SetAutoPageBreak(TRUE, 0);

// 设置图像比例因子
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// 设置默认字体子集模式
$pdf->setFontSubsetting(true);

//设置字体
//$pdf->SetFont('droidsansfallback', 'B', 10);
$pdf->SetFont('stsongstdlight', 'B', 10);
$pdf->AddPage();

//变量赋值
$site=SHOP_TEMPLATES_URL;
for($i=0;$i<$output['numbers'];$i++){
  $centercode[]=$output['order_list'][$i]['extend_order_common']['waybill_info']['packagecentercode'];
  $logisticsNo[]=$output['order_list'][$i]['extend_order_common']['waybill_info']['logisticsNo'];
  $three_code[]=$output['order_list'][$i]['extend_order_common']['waybill_info']['shortaddress'];
  $reciver_name[]=$output['order_list'][$i]['extend_order_common']['reciver_name'];
  $phone[]=$output['order_list'][$i]['extend_order_common']['reciver_info']['phone'];
  $address[]=$output['order_list'][$i]['extend_order_common']['reciver_info']['address'];
  $order_sn[]=$output['order_list'][$i]['order_sn'];
}
for($i=0;$i<$output['numbers'];$i++){
  $seller_name[]=$output['shippingArr'][$i]['senderusername'];
  $telphone[]=$output['shippingArr'][$i]['senderusertelephone'];
  $shipper_address[]=$output['shippingArr'][$i]['senderuseraddress'];
}
for($i=0;$i<$output['numbers'];$i++){
  $goods_all_num[]=$output['goods_all_number'][$i];
}
for($i=0;$i<$output['numbers'];$i++){
  $goods_total_quantity[]=$output['goods_all_quantity'][$i];
}

$img_adr = array();
for($i=0;$i<$output['numbers'];$i++){
  $img_adr[]=$output['qrcode'][$i];
}

$date1=date("Y/m/d");
$date2=date("h:i:s");

$goods=array();
for($i=0;$i<$output['numbers'];$i++){
  foreach($output['order_list'][$i]['extend_order_goods'] as $v){
    $goods[$i][]=$v['goods_name'];
    $goodnums[$i][]=$v['goods_num'];
  }
//  if(count($goods[$i])>3){
    foreach($goods[$i] as $j=>$r){
      $goods_names3[$i][]=$goods[$i][$j].'*'.$goodnums[$i][$j];
    }
//    $goods_names3[$i][4]='';
    $goods_names[$i]=implode(',',$goods_names3[$i]);
    $goods_name[$i]=str_replace(',', ' ', $goods_names[$i]);
//  }else{
//    $goods_names[$i]=implode(',',$goods[$i]);
//    $goods_name[$i]=str_replace(',', ' ', $goods_names[$i]);
//  }


//  for($j=0;$j<3;$j++){
//    $goods_names3[$i][]=$goods[$i][$j];
//  }
//  $goods_names[$i]=implode(',',$goods_names3[$i]);
//  $goods_name[$i]=str_replace(',', '<br/>', $goods_names[$i]);

//  $goods_names[$i]=implode(',',$goods[$i]);
//  $goods_name1[$i]=mb_substr($goods_names[$i],0,120,"utf-8");
//  $goods_name[$i]=str_replace(',', '<br/>', $goods_name1[$i]);

}


//for($i=0;$i<$output['numbers'];$i++) {
//  QRcode::png($output['order_list'][$i]['extend_order_common']['waybill_info']['shortaddress'], $output['order_list'][$i]['order_id'] . '.png', 'L', '3', 2);
//  $img_adr[] = $output['order_list'][$i]['order_id'] . '.png';
//}
//$centercode=$output['order_info']['extend_order_common']['waybill_info']['packagecentercode'];
//$logisticsNo=$output['order_info']['extend_order_common']['waybill_info']['logisticsNo'];
//$three_code=$output['order_info']['extend_order_common']['waybill_info']['shortaddress'];
//$reciver_name=$output['order_info']['extend_order_common']['reciver_name'];
//$phone=$output['order_info']['extend_order_common']['reciver_info']['phone'];
//$address=$output['order_info']['extend_order_common']['reciver_info']['address'];
//$seller_name=$output['shippingArr'][0]['seller_name'];
//$telphone=$output['shippingArr'][0]['telphone'];
//$shipper_address=$output['shippingArr'][0]['address'];
//$date1=date("Y/m/d");
//$date2=date("h:i:s");
//$goods_all_num=$output['goods_all_num'];
//$goods_total_quantity=$output['goods_total_quantity'];
//$img_adr=$output['shortaddress'];
//$order_sn=$output['order_info']['order_sn'];

//$goods=array();
//$i=0;
//foreach($output['order_list']['extend_order_goods'] as $v){
//  $goods[$i]=$v['goods_name'];
//  $i++;
//}
//$goods_names=implode(',',$goods);
//$goods_name=str_replace(',', '<br/>', $goods_names);


//内容开始
for($i=0;$i<$output['numbers'];$i++){

  if(strlen($address[$i])>120){
    $size = 10;
  }else{
    $size = 12;
  }

  $html[$i] = '<link href="'.$site.'/css/pdf.css" rel="stylesheet" type="text/css"/>
<table cellspacing="0" cellpadding="0.5mm" style="table-layout:fixed;">
  <tr>
    <td height="15mm" width="100mm" align="left"></td>
  </tr>
  <tr>
    <td height="15mm" width="100mm" align="left" colspan="2"><p style="font-size:36pt;text-align:right;">'.$three_code[$i].'</p></td>
  </tr>
  <tr>
    <td style="font-size: 0;" width="100mm" height="9mm" align="right"><img src="'.$site.'/barcode/testt.php?codebar=BCGcode128&text='.$centercode[$i].'" style="width: 60mm;height: 9mm; "></td>
  </tr>
  <tr class="row4">

    <td HEIGHT="14mm" width="9mm" align="left" ><p align="right" style="font-size:14pt; font-family:Microsoft Yahei">收 </p></td>
    <td width="91mm" align="left" style="height:14mm border-bottom:1px solid black;"><p style="font-weight:1500;font-size:'.$size.'pt;font-family:SimHei">'.$reciver_name[$i].'&nbsp;&nbsp;'.$phone[$i].'<br/>'.$address[$i].'</p></td>
  </tr>
  <tr class="row5">
    <td HEIGHT="11mm" width="9mm" align="left" ><p align="right" style="font-size:14pt;">寄 </p></td>
    <td HEIGHT="11mm" width="91mm" align="left" style="font-size:8pt;" >'.$seller_name[$i].'&nbsp;&nbsp;'.$telphone[$i].'<br/>'.$shipper_address[$i].'<br/></td>
  </tr>
  <tr>
    <td style="width: 100mm;height: 19mm;font-size: 0" align="center">
      <img src="'.$site.'/barcode/test.php?codebar=BCGcode128&text='.$logisticsNo[$i].'"  style="width:94mm;height: 17.5mm;">
    </td>
  </tr>
  <tr class="row7">
    <td style="width: 20mm;height: 20.6mm;float: left;">&nbsp;'.$date1.'<br>&nbsp;'.$date2.'</td>
    <td style="width:60mm;height: 20.6mm;">
      <p></p>
      <p style="font-family: Black;font-size: 7pt; text-align: right;">签收栏：&nbsp;&nbsp;&nbsp;&nbsp;</p>
    </td>
    <td style="width:20mm;height:20.6mm;border-right:1px dashed black"><img width="19mm" src="'.$img_adr[$i].'"/></td>
  </tr>
  <tr>
    <td style="width: 30mm;height: 10mm;border-top: 5px solid black"></td>
    <td style="width: 70mm;height: 10mm;border-top: 5px solid black;margin-right: 2mm;font-size: 0">
      <img src="'.$site.'/barcode/test.php?codebar=BCGcode128&text='.$logisticsNo[$i].'" align="right" style="width: 60mm;height: 9mm;">
    </td>
    
  </tr>
  <tr>
    <td style="width: 10mm;height: 10mm;float: left;text-align:right;font-size:14pt;">收 </td>
    <td HEIGHT="10mm" width="90mm" align="left" style="font-size:7pt;">'.$reciver_name[$i].'&nbsp;&nbsp;'.$phone[$i].'<br/>'.$address[$i].'</td>
  </tr>
  <tr>
    <td style="width: 10mm;height: 10mm;float: left;text-align:right;font-size:14pt;">寄 </td>
    <td HEIGHT="10mm" width="90mm" align="left" style="font-size:7pt;">'.$seller_name[$i].'&nbsp;&nbsp;'.$telphone[$i].'<br/>'.$shipper_address[$i].'</td>
  </tr>
   <tr class="row13">

    <td HEIGHT="7mm" width="50mm" align="left" float="left"><p align="left" style="font-size:16pt; font-family:SimHei">申通快递</p></td>
    <td width="50mm" align="right" style="height:7  mm border-bottom:1px solid black;"><p  align="right"style="font-weight:1500;font-size:'.$size.'pt;font-family:SimHei">'.$logisticsNo[$i].'</p></td>
  </tr>
  <tr>
    <td style="width: 100mm;font-size:7pt;height: 10.6mm;align-content: left;column-span: 3;border-top: 1px solid black;" >货物详情：'.$goods_name[$i].'</td>
  </tr>
  <tr class="row12">
    <td width="100mm" height="4mm" align="left" colspan="2"><div class="left" style="font-size: 10pt">订单号:'.$order_sn[$i].'</div></td>
  </tr>
  <tr class="row13">
    <td width="100mm" height="6mm" align="right" colspan="2"><div class="right" style="font-size: 10pt">已验视</div></td>
  </tr>
</table>';


}
//$html = '<link href="'.$site.'/css/pdf.css" rel="stylesheet" type="text/css"/>
//<table cellspacing="0" cellpadding="0.5mm" style="table-layout:fixed;">
//  <tr>
//    <td height="14mm" width="100mm" align="left"></td>
//  </tr>
//  <tr>
//    <td height="15mm" width="100mm" align="left" colspan="2"><p style="font-size:36pt;text-align:center;">'.$three_code.'</p></td>
//  </tr>
//  <tr>
//    <td style="font-size: 0;" width="108mm" height="10mm" align="right"><img src="'.$site.'/barcode/testt.php?codebar=BCGcode128&text='.$centercode.'" style="width: 60mm;height: 10mm; "></td>
//    <td width="2mm" height="10mm" ></td>
//  </tr>
//  <tr class="row4">
//
//    <td HEIGHT="15mm" width="9mm" align="left" ><p align="right" style="font-size:14pt; font-family:Microsoft Yahei">收 </p></td>
//    <td HEIGHT="15mm" width="91mm" align="left" style="border-bottom:1px solid black;"><p style="font-weight:1500;font-size:12pt;font-family:SimHei">'.$reciver_name.'&nbsp;&nbsp;'.$phone.'<br/>'.$address.'<br/></p></td>
//  </tr>
//  <tr class="row5">
//    <td HEIGHT="12mm" width="9mm" align="left" ><p align="right" style="font-size:14pt;">寄 </p></td>
//    <td HEIGHT="12mm" width="91mm" align="left" >'.$seller_name.'&nbsp;&nbsp;'.$telphone.'<br/>'.$shipper_address.'<br/></td>
//  </tr>
//  <tr>
//    <td style="width: 100mm;height: 23mm;font-size: 0" align="center">
//      <img src="'.$site.'/barcode/test.php?codebar=BCGcode128&text='.$logisticsNo.'"  style="width:90mm;height: 23mm;">
//    </td>
//  </tr>
//  <tr class="row7">
//    <td style="width: 22mm;height: 20mm;float: left;">&nbsp;'.$date1.'<br>&nbsp;'.$date2.'<br>&nbsp;数量:'.$goods_all_num.'<br>&nbsp;重量:'.$goods_total_quantity.'kg</td>
//    <td style="width:68mm;height: 20mm;">
//      <p></p>
//      <p style="font-family: Black;font-size: 7pt; text-align: right;">签收栏：&nbsp;&nbsp;&nbsp;&nbsp;</p>
//    </td>
//    <td style="width:20mm;height:20mm;border-right:1px dashed black"><img width="19mm" src="'.$img_adr.'"/></td>
//  </tr>
//  <tr>
//    <td style="width: 38mm;height: 10mm;border-top: 5px solid black"></td>
//    <td style="width: 70mm;height: 10mm;border-top: 5px solid black;margin-right: 2mm;font-size: 0">
//      <img src="'.$site.'/barcode/test.php?codebar=BCGcode128&text='.$logisticsNo.'" align="right" style="width: 68mm;height: 14mm;">
//    </td>
//    <td width="2mm" height="10mm" style="border-top: 5px solid black;"></td>
//  </tr>
//  <tr>
//    <td HEIGHT="14mm" width="9mm" align="left" ><p align="right" style="font-size:14pt;">收 </p></td>
//    <td HEIGHT="14mm" width="91mm" align="left" >'.$reciver_name.'&nbsp;&nbsp;'.$phone.'<br/>'.$address.'</td>
//  </tr>
//  <tr>
//    <td HEIGHT="12mm" width="9mm" align="left" ><p align="right" style="font-size:14pt;">寄 </p></td>
//    <td HEIGHT="12mm" width="91mm" align="left" >'.$seller_name.'&nbsp;&nbsp;'.$telphone.'<br/>'.$shipper_address.'</td>
//  </tr>
//  <tr>
//    <td style="width: 100mm;height: 28mm;align-content: left;column-span: 3;border-top: 5px solid black;" >'.$goods_name.'</td>
//  </tr>
//  <tr class="row12">
//    <td width="110mm" height="6mm" align="left" colspan="2"><div class="right" style="font-size: 10pt">订单号:'.$order_sn.'</div></td>
//  </tr>
//</table>';


// 打印文件
for($i=0;$i<$output['numbers'];$i++){
  $pdf->writeHTML($html[$i], true, 0, true, 0, '');
}
//$pdf->writeHTML($html, true, 0, true, 0, '');
$pdf->Output($output['order_list'][0]['order_sn'].'.pdf','I');

for($i=0;$i<$output['numbers'];$i++) {
  unlink($img_adr[$i]);
}
setcookie('str_id','',time() - 3600);

?>