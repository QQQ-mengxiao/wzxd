<?php
defined('In718Shop') or exit('Access Invalid!');
require_once template('TCPDF/tcpdf');
require_once template('TCPDF/examples/lang/chi');
$pdf=new TCPDF(PDF_PAGE_ORIENTATION,'mm',array(110,200),true,'utf-8',false);
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
$pdf->SetFont('droidsansfallback', 'B', 10);
$pdf->AddPage();

//变量赋值
$site=SHOP_TEMPLATES_URL;
$centercode=$output['order_info']['extend_order_common']['waybill_info']['packagecentercode'];
$logisticsNo=$output['order_info']['extend_order_common']['waybill_info']['logisticsNo'];
$three_code=$output['order_info']['extend_order_common']['waybill_info']['shortaddress'];
$reciver_name=$output['order_info']['extend_order_common']['reciver_name'];
$phone=$output['order_info']['extend_order_common']['reciver_info']['phone'];
$address=$output['order_info']['extend_order_common']['reciver_info']['address'];
$seller_name=$output['kuajing_info']['senderusername'];
$telphone=$output['kuajing_info']['senderusertelephone'];
$shipper_address=$output['kuajing_info']['senderuseraddress'];
$date1=date("Y/m/d");
$date2=date("h:i:s");
$goods_all_num=$output['goods_all_num'];
$goods_total_quantity=$output['goods_total_quantity'];
$img_adr=$output['shortaddress'];
$order_sn=$output['order_info']['order_sn']; 
$goods=array();
$i=0;
foreach($output['order_info']['extend_order_goods'] as $v){
  $goods[$i]=$v['goods_name'];
  $i++;
}
$goods_names=implode(',',$goods);
$goods_name=str_replace(',', '<br/>', $goods_names);

//内容开始
$html = '<link href="'.$site.'/css/pdf.css" rel="stylesheet" type="text/css"/>
<table cellspacing="0" cellpadding="0.5mm" style="table-layout:fixed;">
  <tr>
    <td height="14mm" width="100%" align="left"></td>
  </tr>
  <tr>
    <td height="15mm" width="100%" align="left" colspan="2"><p style="font-size:36pt;text-align:center;">'.$three_code.'</p></td>
  </tr>
  <tr>
    <td style="font-size: 0;" width="108mm" height="10mm" align="right"><img src="'.$site.'/barcode/testt.php?codebar=BCGcode128&text='.$centercode.'" style="width: 60mm;height: 10mm; "></td>
    <td width="2mm" height="10mm" ></td>
  </tr>  
  <tr class="row4">
    
    <td HEIGHT="15mm" width="9%" align="left" ><p align="right" style="font-size:14pt; font-family:Microsoft Yahei">收 </p></td>
    <td HEIGHT="15mm" width="91%" align="left" style="border-bottom:1px solid black;"><p style="font-weight:1500;font-size:12pt;font-family:SimHei">'.$reciver_name.'&nbsp;&nbsp;'.$phone.'<br/>'.$address.'<br/></p></td>
  </tr>
  <tr class="row5">
    <td HEIGHT="12mm" width="9%" align="left" ><p align="right" style="font-size:14pt;">寄 </p></td>
    <td HEIGHT="12mm" width="91%" align="left" >'.$seller_name.'&nbsp;&nbsp;'.$telphone.'<br/>'.$shipper_address.'<br/></td>
  </tr>
  <tr>
    <td style="width: 100%;height: 23mm;font-size: 0" align="center">
      <img src="'.$site.'/barcode/test.php?codebar=BCGcode128&text='.$logisticsNo.'"  style="width:90mm;height: 23mm;">
    </td>
  </tr>
  <tr class="row7">
    <td style="width: 22mm;height: 20mm;float: left;">&nbsp;'.$date1.'<br>&nbsp;'.$date2.'<br>&nbsp;数量:'.$goods_all_num.'<br>&nbsp;重量:'.$goods_total_quantity.'kg</td>
    <td style="width:68mm;height: 20mm;">
      <p></p>
      <p style="font-family: Black;font-size: 7pt; text-align: right;">签收栏：&nbsp;&nbsp;&nbsp;&nbsp;</p>
    </td>
    <td style="width:20mm;height:20mm;border-right:1px dashed black"><img width="19mm" src="'.$img_adr.'"/></td>
  </tr>  
  <tr>
    <td style="width: 38mm;height: 10mm;border-top: 5px solid black"></td>
    <td style="width: 70mm;height: 10mm;border-top: 5px solid black;margin-right: 2mm;font-size: 0">
      <img src="'.$site.'/barcode/test.php?codebar=BCGcode128&text='.$logisticsNo.'" align="right" style="width: 68mm;height: 14mm;">
    </td>
    <td width="2mm" height="10mm" style="border-top: 5px solid black;"></td>
  </tr>        
  <tr>
    <td HEIGHT="14mm" width="9%" align="left" ><p align="right" style="font-size:14pt;">收 </p></td>
    <td HEIGHT="14mm" width="91%" align="left" >'.$reciver_name.'&nbsp;&nbsp;'.$phone.'<br/>'.$address.'</td>
  </tr>
  <tr>
    <td HEIGHT="12mm" width="9%" align="left" ><p align="right" style="font-size:14pt;">寄 </p></td>
    <td HEIGHT="12mm" width="91%" align="left" >'.$seller_name.'&nbsp;&nbsp;'.$telphone.'<br/>'.$shipper_address.'</td>
  </tr>
  <tr>
    <td style="width: 100%;height: 28mm;align-content: left;column-span: 3;border-top: 5px solid black;" >'.$goods_name.'</td>
  </tr>
  <tr class="row12">
    <td width="110mm" height="6mm" align="left" colspan="2"><div class="right" style="font-size: 10pt">订单号:'.$order_sn.'</div></td>
  </tr>  
</table>';

// 打印文件
$pdf->writeHTML($html, true, 0, true, 0, '');
$pdf->Output($order_sn.'.pdf','I');
?>