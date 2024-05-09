<?php
defined('In718Shop') or exit('Access Invalid!');
require_once template('TCPDF/tcpdf');
require_once template('TCPDF/examples/lang/chi');
$pdf=new TCPDF(PDF_PAGE_ORIENTATION,PDF_UNIT, PDF_PAGE_FORMAT,true,'utf-8',false);
$pdf->setLanguageArray($l);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
// 设置默认等宽字体 
$pdf->SetDefaultMonospacedFont('courier'); 
 
// 设置间距 
$pdf->SetMargins(5, 10, 0);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(8);

// 设置分页 
$pdf->SetAutoPageBreak(TRUE, 0);
 
// 设置图像比例因子
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
 
// 设置默认字体子集模式
$pdf->setFontSubsetting(true); 

//设置字体 
$pdf->SetFont('droidsansfallback', 'B', 12);
$pdf->AddPage('L','A5');

//数据
//$store_label=$output['store_info']['store_label'];
$store_label=UPLOAD_SITE_URL.DS.ATTACH_COMMON.DS.$output['setting_config']['site_logo'];
// var_dump($store_label);die;
$store_name=$output['store_info']['store_name'];
$store_phone=$output['store_info']['store_phone'];
$store_qq=$output['store_info']['store_qq'];
$receiver_name=$output['order_info']['extend_order_common']['reciver_name'];
$phone=$output['order_info']['extend_order_common']['reciver_info']['phone'];
$address=$output['order_info']['extend_order_common']['reciver_info']['address'];
$order_sn=$output['order_info']['order_sn'];
$order_add_time=date('Y-m-d H:i:s',$output['order_info']['add_time']);
$order_pay_time=date('Y-m-d H:i:s',$output['order_info']['payment_time']);
$store_printdesc= $output['store_info']['store_printdesc'];
$store_stamp=$output['store_info']['store_stamp'];
$logisticsNo=$output['logisticsNo']?$output['logisticsNo']:'无';
$invoicetype=$output['order_info']['extend_order_common']['invoice_info']['类型']?$output['order_info']['extend_order_common']['invoice_info']['类型']:'无';
$invoicetitle=$output['order_info']['extend_order_common']['invoice_info']['抬头']?$output['order_info']['extend_order_common']['invoice_info']['抬头']:'无';

$html1 = '
<table cellspacing="0" >
  <tr>
    <td height="70px" width="210px" align="left" colspan="1"><img src="'.$store_label.'"></td>
    <td align="left" colspan="2"><p style="font-size:30px;line-height:60px">发货单</p></td>
  </tr>
  <br/>
  <tr height="5px" width="100%"></tr>
  <tr>
    <td height="18px" align="left" colspan="1">收货人：'.$receiver_name.'</td>
    <td height="18px" align="left" colspan="2">电话：'.$phone.'</td>
  </tr>
  <tr>
    <td height="18px" align="left" colspan="3">地址：'.$address.'</td>
  </tr>  
  <tr>    
    <td height="18px" align="left" >订单号：'.$order_sn.'</td>
    <td height="18px" align="left" >支付时间：'.$order_pay_time.'</td>
  </tr>
</table>
<p></p>
<table>
  <tr bgcolor="#E7E7E7">
    <td height="25px" width="40px" style="border-top:3px solid black;border-bottom:3px solid black;"><p style="line-height:25px">序号</p></td>
    <td height="25px" width="490px" style="border-top:3px solid black;border-bottom:3px solid black;"><p style="line-height:25px">商品名称</p></td>
    <td height="25px" width="80px" style="border-top:3px solid black;border-bottom:3px solid black;"><p style="line-height:25px"></p></td>
    <td height="25px" width="40px" style="border-top:3px solid black;border-bottom:3px solid black;"><p style="line-height:25px">数量</p></td>
    <td height="25px" width="80px" style="border-top:3px solid black;border-bottom:3px solid black;"><p style="line-height:25px"></p></td>
  </tr>';
$html2='';
foreach($output['goods_list'] as $key=>$val){
  foreach($val as $k=>$v){ 
    $count=$k;
    $goods_name=$v['goods_name'];
    $price=$v['goods_price'];
    $goods_serial=$v['goods_serial'];
    $goods_num=$v['goods_num'];
    $all_price=$v['goods_all_price'];
    $html2=$html2.'
      <tr>
        <td height="5px"><p></p></td>
      </tr>
      <tr align="center">
        <td height="18px" width="40px" >'.$k.'</td>
        <td height="18px" width="490px"  align="left">'.$goods_name.$goods_serial.'</td>
        <td height="18px" width="80px" ></td>
        <td height="18px" width="40px" >'.$goods_num.'</td>
        <td height="18px" width="80px" ></td>
      </tr><br/>';
  }
}
$goods_all_num=$output['goods_all_num'];
$goods_total_price=$output['goods_total_price'];
$shipping_fee=$output['order_info']['shipping_fee'];
$promotion_amount=$output['promotion_amount'];
$order_amount=$output['order_info']['order_amount'];
$order_message=!empty($output['order_info']['extend_order_common']['order_message'])?'留言：'.$output['order_info']['extend_order_common']['order_message']:'';
$html=$html1.$html2.'
  <tr>
    <td height="36px" width="730px" style="border-top:3px solid black;border-bottom:3px solid black;">'.$order_message.'</td>
    <td height="36px" colspan="2" style="border-top:3px solid black;border-bottom:3px solid black;"><p style="line-height:36px"></p></td>
    <td height="36px" width="40px" style="border-top:3px solid black;border-bottom:3px solid black;" align="center"><p style="line-height:36px"></p></td>
    <td height="36px" width="80px" style="border-top:3px solid black;border-bottom:3px solid black;" align="center"><p style="line-height:36px"></p></td>
  </tr>
   <tr>
    <td height="18px" width="170px">运单号：'.$logisticsNo.'</td>
    <td height="18px" width="340px">发票类型：'.$invoicetype.'</td>
    <td height="18px" width="220px">发票抬头：'.$invoicetitle.'</td>
  </tr>
  <tr>
    <td height="18px" width="170px">店铺：'.$store_name.'</td>
    <td height="18px" width="340px">客服QQ：'.$store_qq.'</td>
    <td height="18px" width="220px">联系电话：'.$store_phone.'</td>
  </tr>
  <tr>
    <td height="18px" width="730px" style="border-bottom:3px solid black;">感谢您的惠顾，期待您再次光临！</td>
  </tr>
  '.$store_printdesc.'
  <img src="'.$store_stamp.'"/>
</table>';
 
// 打印文件
$pdf->writeHTML($html, true, 0, true, 0, '');
 // var_dump('77777');die;
$pdf->Output('deliver_order.pdf','I');
?>