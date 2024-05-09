<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php defined('In718Shop') or exit('Access Invalid!');?>

<link href="<?php echo SHOP_TEMPLATES_URL;?>/css/seller_center.css" rel="stylesheet" type="text/css"/>
<style type="text/css">
body { background: #FFF none;
}
</style>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.printarea.js" charset="utf-8"></script>
<title><?php echo $lang['member_printorder_print'];?>--<?php echo $output['store_info']['store_name'];?><?php echo $lang['member_printorder_title'];?></title>
</head>

<body>
<?php if (!empty($output['order_info'])){?>
<div class="print-layout">
  <div class="print-btn" id="printbtn" title="<?php echo $lang['member_printorder_print_tip'];?>"><i></i><a href="javascript:void(0);"><?php echo $lang['member_printorder_print'];?></a></div>
  <div class="a5-size"></div>
  <dl class="a5-tip">
    <dt>
      <h1>A5</h1>
      <em>Size: 210mm x 148mm</em></dt>
    <dd><?php echo $lang['member_printorder_print_tip_A5'];?></dd>
  </dl>
  <div class="a4-size"></div>
  <dl class="a4-tip">
    <dt>
      <h1>A4</h1>
      <em>Size: 210mm x 297mm</em></dt>
    <dd><?php echo $lang['member_printorder_print_tip_A4'];?></dd>
  </dl>
  <div class="print-page">
    <div id="printarea">
      <?php foreach ($output['goods_list'] as $item_k =>$item_v){?>
      <div class="orderprint">
        <div class="top">
          <?php if (empty($output['store_info']['store_label'])){?>
          <div class="full-title"><?php //echo $output['store_info']['store_name'];?> <?php echo $lang['member_printorder_title'];?></div>
          <?php }else {?>
          <div class="logo" ><img src="<?php echo $output['store_info']['store_label']; ?>"/></div>
          <div class="logo-title"><?php //echo $output['store_info']['store_name'];?><?php echo $lang['member_printorder_title'];?></div>
          <?php }?>
        </div>
        <table class="buyer-info">
          <tr>
            <td class="w200"><?php echo $lang['member_printorder_truename'].$lang['nc_colon']; ?><?php echo $output['order_info']['extend_order_common']['reciver_name'];?></td>
            <td><?php echo '电话'.$lang['nc_colon']; ?><?php echo @$output['order_info']['extend_order_common']['reciver_info']['phone'];?></td>
            <td></td>
          </tr>
          <tr>
            <td colspan="3"><?php echo $lang['member_printorder_address'].$lang['nc_colon']; ?><?php echo @$output['order_info']['extend_order_common']['reciver_info']['address'];?></td>
          </tr>
          <tr>
            <td><?php echo $lang['member_printorder_orderno'].$lang['nc_colon'];?><?php echo $output['order_info']['order_sn'];?></td>
            <td><?php echo '支付时间'.$lang['nc_colon'];?><?php echo $output['order_info']['payment_time']==0?'未支付':@date('Y-m-d H:m:s',$output['order_info']['payment_time']);?></td>
            <td><?php if ($output['order_info']['shippin_code']){?>
              <span><?php echo $lang['member_printorder_shippingcode'].$lang['nc_colon']; ?><?php echo $output['order_info']['shipping_code'];?></span>
              <?php }?></td>
          </tr>
        </table>
        <table class="order-info">
          <thead>
            <tr>
              <th class="w40"><?php echo $lang['member_printorder_serialnumber'];?></th>
              <th class="tl"><?php echo $lang['member_printorder_goodsname'];?></th>
              <th class="w70 tl"><?php //echo $lang['member_printorder_goodsprice'];?><?php //echo $lang['currency_zh'];?></th>
              <th class="w50"><?php echo $lang['member_printorder_goodsnum'];?></th>
              <th class="w70 tl"><?php //echo $lang['member_printorder_subtotal'];?><?php //echo $lang['currency_zh'];?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($item_v as $k=>$v){?>
            <tr>
              <td><?php echo $k;?></td>
              <td class="tl"><?php
              $arr = explode(' ',$v['goods_name']);

              if(count($arr)-1>1){
              $arr_sub = explode(' ',$v['goods_name'],-1);
                foreach($arr_sub as $va){
                echo $va." ";
                }
               echo '('.end($arr).')';
              } else {
              echo $v['goods_name'].'('.end($arr).')';

           }
               ?>

               </td>
              <td class="tl"><?php //echo $lang['currency'].$v['goods_price'];?></td>
              <td><?php echo $v['goods_num'];?></td>
              <td class="tl"><?php //echo $lang['currency'].$v['goods_all_price'];?></td>
            </tr>
            <?php }?>
            <tr>
              <th></th>
              <th colspan="2" class="tl"><?php //echo $lang['member_printorder_amountto'];?></th>
              <th><?php //echo $output['goods_all_num'];?></th>
              <th class="tl"><?php// echo $lang['currency'].$output['goods_total_price'];?></th>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="10"><span>运单号：<?php echo $output['logisticsNo']?$output['logisticsNo']:'无';?></span><span style="width: 220px;">发票类型：<?php echo $output['order_info']['extend_order_common']['invoice_info']['类型']?$output['order_info']['extend_order_common']['invoice_info']['类型']:'无';?></span><span>发票抬头：<?php echo $output['order_info']['extend_order_common']['invoice_info']['抬头']?$output['order_info']['extend_order_common']['invoice_info']['抬头']:'无';?></span><br><span><?php echo $lang['member_printorder_shop'].$lang['nc_colon'];?><?php echo $output['store_info']['store_name'];?></span><?php if (!empty($output['store_info']['store_qq'])){?><span style="width: 220px;">客服QQ：<?php echo $output['store_info']['store_qq'];?></span><?php }elseif (!empty($output['store_info']['store_ww'])){?><span><?php echo $lang['member_printorder_shopww'].$lang['nc_colon'];?><?php echo $output['store_info']['store_ww'];?></span><?php }?><span>联系电话：0371-55172898</span><br><span style="width: 230px;">班列购感谢您的惠顾，期待您再次光临！</span></th>
            </tr>
          </tfoot>
        </table>
        <?php if (empty($output['store_info']['store_stamp'])){?>
        <div class="explain">
        	<?php echo $output['store_info']['store_printdesc'];?>
        </div>
        <?php }else {?>
        <div class="explain">
        	<?php echo $output['store_info']['store_printdesc'];?>
        </div>
        <div class="seal"><img src="<?php echo $output['store_info']['store_stamp'];?>" onload="javascript:DrawImage(this,120,120);"/></div>
        <?php }?>
        <div class="tc page"><?php echo $lang['member_printorder_pagetext_1']; ?><?php echo $item_k;?><?php echo $lang['member_printorder_pagetext_2']; ?>/<?php echo $lang['member_printorder_pagetext_3']; ?><?php echo count($output['goods_list']);?><?php echo $lang['member_printorder_pagetext_2']; ?></div>
      </div>
      <?php }?>
    </div>
    <?php }?>
  </div>
</div>
</body>
<script>
$(function(){
	$("#printbtn").click(function(){
	$("#printarea").printArea();
	});
});

//打印提示
$('#printbtn').poshytip({
	className: 'tip-yellowsimple',
	showTimeout: 1,
	alignTo: 'target',
	alignX: 'center',
	alignY: 'bottom',
	offsetY: 5,
	allowTipHover: false
});
</script>
</html>