<?php defined('In718Shop') or exit('Access Invalid!');?>
<style type="text/css">
.sticky .tabmenu { padding: 0;  position: relative; }
</style>
        <span class="fr mr5"> <a href="<?php echo urlShop('store_deliver', 'waybill_print', array('order_id' => $output['order_info']['order_id']));?>" class="ncsc-btn-mini" target="_blank" title="打印运单"/><i class="icon-print"></i>打印运单</a></span>
<div class="wrap">
  <div class="step-title"><em><?php echo $lang['store_deliver_first_step'];?></em><?php echo $lang['store_deliver_confirm_trade'];?></div>
  <form name="deliver_form" method="POST" id="deliver_form" action="index.php?act=store_deliver&op=send&order_id=<?php echo $_GET['order_id'];?>" onsubmit="ajaxpost('deliver_form', '', '', 'onerror');return false;">
    <input type="hidden" value="<?php echo getReferer();?>" name="ref_url">
    <input type="hidden" value="ok" name="form_submit">
    <input type="hidden" id="shipping_express_id" value="<?php echo $output['order_info']['shipping_express_id'];?>" name="shipping_express_id">
    <input type="hidden" value="<?php echo $output['order_info']['extend_order_common']['reciver_name'];?>" name="reciver_name" id="reciver_name">
    <input type="hidden" value="<?php echo $output['order_info']['extend_order_common']['reciver_info']['area'];?>" name="reciver_area" id="reciver_area">
    <input type="hidden" value="<?php echo $output['order_info']['extend_order_common']['reciver_info']['street'];?>" name="reciver_street" id="reciver_street">
    <input type="hidden" value="<?php echo $output['order_info']['extend_order_common']['reciver_info']['mob_phone'];?>" name="reciver_mob_phone" id="reciver_mob_phone">
    <input type="hidden" value="<?php echo $output['order_info']['extend_order_common']['reciver_info']['tel_phone'];?>" name="reciver_tel_phone" id="reciver_tel_phone">
    <input type="hidden" value="<?php echo $output['order_info']['extend_order_common']['reciver_info']['dlyp'];?>" name="reciver_dlyp" id="reciver_dlyp">
    <input type="hidden" value="<?php echo $output['order_info']['extend_order_common']['reciver_info']['id_card'];?>" name="reciver_id_card" id="reciver_id_card">
    <table class="ncsc-default-table order deliver">
      <tbody>
        <?php if (is_array($output['order_info']) and !empty($output['order_info'])) { ?>
        <tr>
          <td colspan="20" class="sep-row"></td>
        </tr>
        <tr>
          <th colspan="20"/><a href="index.php?act=store_order_print&order_id=<?php echo $output['order_info']['order_id'];?>" target="_blank" class="fr" title="<?php echo $lang['store_show_order_printorder'];?>"/><i class="print-order"></i></a><span class="fr mr30"></span><span class="ml10"><?php echo $lang['store_order_order_sn'].$lang['nc_colon'];?><?php echo $output['order_info']['order_sn']; ?></span><span class="ml20"><?php echo $lang['store_order_add_time'].$lang['nc_colon'];?><em class="goods-time"><?php echo date("Y-m-d H:i:s",$output['order_info']['add_time']); ?></em></span>
        </tr>
        <?php foreach($output['order_info']['extend_order_goods'] as $k => $goods_info) { ?>
        <tr>
          <td class="bdl w10"></td>
          <td class="w50"><div class="pic-thumb"><a href="<?php echo urlShop('goods','index',array('goods_id'=>$goods_info['goods_id']));?>" target="_blank"><img src="<?php echo cthumb($goods_info['goods_image'],'60',$$output['order_info']['store_id']); ?>" /></a></div></td>
          <td class="tl"><dl class="goods-name">
              <dt><a target="_blank" href="<?php echo urlShop('goods','index',array('goods_id'=>$goods_info['goods_id']));?>"><?php echo $goods_info['goods_name']; ?></a></dt>
              <dd><strong>￥<?php echo $goods_info['goods_price']; ?></strong>&nbsp;x&nbsp;<em><?php echo $goods_info['goods_num'];?></em>件</dd>
            </dl></td>
          <?php if ((count($output['order_info']['extend_order_goods']) > 1 && $k ==0) || (count($output['order_info']['extend_order_goods']) == 1)){?>
          <td class="bdl bdr order-info w500" rowspan="<?php echo count($output['order_info']['extend_order_goods']);?>" style="padding-bottom: 0px;"><dl>
              <dt><?php echo $lang['store_deliver_shipping_amount'].$lang['nc_colon'];?></dt>
              <dd>
                <?php if (!empty($output['order_info']['shipping_fee']) && $output['order_info']['shipping_fee'] != '0.00'){?>
                <?php echo $output['order_info']['shipping_fee'];?>
                <?php }else{?>
                <?php echo $lang['nc_common_shipping_free'];?>
                <?php }?>
              </dd>
            </dl>
            <dl>
              <dt><?php echo $lang['store_deliver_forget'].$lang['nc_colon'];?></dt>
              <dd>
                <textarea name="deliver_explain" cols="100" rows="2" class="w400 tip-t" title="<?php echo $lang['store_deliver_forget_tips'];?>"><?php echo $output['order_info']['extend_order_common']['deliver_explain'];?></textarea>
                  <a onclick="deliver_explainsave();" class="ncsc-btn" style="float: right;"><?php echo '保存';?></a>
              </dd>
            </dl></td>
          <?php }?>
        </tr>
        <?php }?>
        <tr>
          <td colspan="20" class="tl bdl bdr" style="padding:8px" id="address"><strong class="fl"><?php echo $lang['store_deliver_buyer_adress'].$lang['nc_colon'];?></strong><span id="buyer_address_span"><?php echo $output['order_info']['extend_order_common']['reciver_name'];?>&nbsp;<?php echo $output['order_info']['extend_order_common']['reciver_info']['phone'];?>&nbsp;<?php echo $output['order_info']['extend_order_common']['reciver_info']['address'];?>&nbsp;<?php echo $output['order_info']['extend_order_common']['reciver_info']['id_card'];?></span><?php echo $output['order_info']['extend_order_common']['reciver_info']['dlyp'] ? '[自提服务站]' : '';?>
          <a href="javascript:void(0)" nc_type="dialog" dialog_title="<?php echo $lang['store_deliver_buyer_adress'];?>" dialog_id="edit_buyer_address" uri="index.php?act=store_deliver&op=buyer_address_edit&order_id=<?php echo $output['order_info']['order_id'];?>" dialog_width="550" class="ncsc-btn-mini fr"><i class="icon-edit"></i><?php echo $lang['nc_edit'];?></a></td>
        </tr>
        <?php } else { ?>
        <tr>
          <td colspan="20" class="norecord"><i>&nbsp;</i><span><?php echo $lang['no_record'];?></span></td>
        </tr>
        <?php } ?>
        <!--运单信息保存-->
        <tr>
          <td colspan="20" class="tl bdl bdr" style="padding:8px" id="waybill_info"><strong class="fl">跨境运单信息：</strong><span id="waybill_span"><?php echo $output['order_info']['extend_order_common']['waybill_info']['totalLogisticsNo'];?>&nbsp;&nbsp;<?php echo $output['order_info']['extend_order_common']['waybill_info']['jcbOrderTime'];?>&nbsp;&nbsp;<?php echo $output['order_info']['extend_order_common']['waybill_info']['jcbOrderPort'];?>&nbsp;&nbsp;<?php echo $output['order_info']['extend_order_common']['waybill_info']['jcbOrderPortInsp'];?>&nbsp;&nbsp;运单号：<?php echo $output['order_info']['extend_order_common']['waybill_info']['logisticsNo'];?></span>
          <a href="javascript:void(0)" nc_type="dialog" dialog_title="跨境运单信息" dialog_id="edit_waybill_info" uri="index.php?act=store_deliver&op=STO_parameter_edit&order_id=<?php echo $output['order_info']['order_id'];?>" dialog_width="550" class="ncsc-btn-mini fr"><i class="icon-edit"></i>跨境运单信息</a></td>
        </tr>
        <!--申通运单推送-->
        <tr>
          <td colspan="20" class="tl bdl bdr" style="padding:8px" id="stonoget">
          <a href="javascript:void(0)" nc_type="dialog" dialog_title="推送结果" dialog_id="orderpush" uri="index.php?act=store_deliver&op=STOPush&order_id=
          <?php echo $output['order_info']['order_id'];?>&op_type=1" dialog_width="550" class="ncsc-btn-mini fr"><i class="icon-edit"></i>运单推送（申通）</a>
          <a href="javascript:void(0)" nc_type="dialog" dialog_title="推送结果" dialog_id="orderpush" uri="index.php?act=store_deliver&op=STOPush&order_id=
          <?php echo $output['order_info']['order_id'];?>&op_type=2" dialog_width="550" class="ncsc-btn-mini fr"><i class="icon-edit"></i>修改（申通）</a>
          </td>
        </tr>
		<!--圆通运单推送-->
        <tr>
          <td colspan="20" class="tl bdl bdr" style="padding:8px" id="stonoget">
            <a href="index.php?act=store_deliver&op=YTOPrint&order_id=<?php echo $output['order_info']['order_id'].',';?>" target="_blank" class="ncsc-btn-mini fr"><i class="icon-print"></i>打印（圆通）</a>
          <a href="javascript:void(0)" nc_type="dialog" dialog_title="推送结果" dialog_id="orderpush" uri="index.php?act=store_deliver&op=YTOPush&order_id=
          <?php echo $output['order_info']['order_id'];?>&op_type=1" dialog_width="550" class="ncsc-btn-mini fr"><i class="icon-edit"></i>运单推送（圆通）</a>
<!--              <a href="javascript:void(0)" nc_type="dialog" dialog_title="推送结果" dialog_id="orderpush" uri="index.php?act=store_deliver&op=yto_trackingInfo&order_id=-->
<!--          --><?php //echo $output['order_info']['order_id'];?><!--&op_type=1" dialog_width="550" class="ncsc-btn-mini fr"><i class="icon-edit"></i>测试</a>-->
              <a href="javascript:void(0)" nc_type="dialog" dialog_title="推送结果" dialog_id="orderpush" uri="index.php?act=store_deliver&op=testyd_parameter_edit&order_id=<?php echo $output['order_info']['order_id'];?>" dialog_width="550" class="ncsc-btn-mini fr"><i class="icon-edit"></i>KK国外走件推送</a>
          <a href="javascript:void(0)" nc_type="dialog" dialog_title="获取运单号"  uri="index.php?act=store_deliver&op=YTOLogisticsNo&order_id=
          <?php echo $output['order_info']['order_id'];?>" dialog_width="550" class="ncsc-btn-mini fr"><i class="icon-edit"></i>运单号生成（圆通）</a>
          </td>
        </tr>
        <!--订单报文生成-->
        <tr>
          <td colspan="20" class="tl bdl bdr" style="padding:8px" id="orderpush2">
          <a href="index.php?act=store_deliver&op=DorderPush&order_id=<?php echo $output['order_info']['order_id'];?>&op_type=1"   class="ncsc-btn-mini fr"><i class="icon-edit"></i>订单报文生成</a>
          <a href="index.php?act=store_deliver&op=DorderPush&order_id=<?php echo $output['order_info']['order_id'];?>&op_type=2"   class="ncsc-btn-mini fr"><i class="icon-edit"></i>订单报文修改</a>
              <select name="deliver" id="deliver" style="margin-right: 8px;float: right;">
                  <option value=0 onclick="deliver(this.value)" <?php if($_COOKIE['deliver']==0){?>selected="selected"<?php }?>>圆通</option>
                  <option value=1 onclick="deliver(this.value)" <?php if($_COOKIE['deliver']==1){?>selected="selected"<?php }?>>申通</option>
              </select>
          </td>
        </tr>
        <!--清单报文生成-->
        <tr>
          <td colspan="20" class="tl bdl bdr" style="padding:8px" id="listpush">
          <a href="index.php?act=store_deliver&op=DlistPush&order_id=<?php echo $output['order_info']['order_id'];?>&op_type=1"   class="ncsc-btn-mini fr"><i class="icon-edit"></i>清单报文生成</a>
          <a href="index.php?act=store_deliver&op=DlistPush&order_id=<?php echo $output['order_info']['order_id'];?>&op_type=2"   class="ncsc-btn-mini fr"><i class="icon-edit"></i>清单报文修改</a>
          </td>
        </tr>
        <!--
        <tr>
          <td colspan="20" class="tl bdl bdr" style="padding:8px" id="orderpush">
          <a href="javascript:void(0)" nc_type="dialog" dialog_title="推送结果" dialog_id="orderpush" uri="index.php?act=store_deliver&op=DorderPush&order_id=<?php echo $output['order_info']['order_id'];?>" dialog_width="550" class="ncsc-btn-mini fr"><i class="icon-edit"></i>订单推送</a>
          </td>
        </tr>
        -->
        <!--
                <tr>
          <td colspan="20" class="tl bdl bdr" style="padding:8px" id="stonoget">
          
          <a href="index.php?act=store_deliver&op=STOPush&order_id=
          <?php echo $output['order_info']['order_id'];?>" nc_type="" target='_blank' 
          dialog_title="申通运单反馈" dialog_id="stoget_dzka" uri="index.php?act=store_deliver&op=STOPush&order_id=
          <?php echo $output['order_info']['order_id'];?>" dialog_width="550" class="ncsc-btn-mini fr"><i class="icon-edit">
          >运单推送（申通）</a>
          </td>
        </tr>
        -->
      </tbody>
    </table>
    <div class="step-title mt30"><em><?php echo $lang['store_deliver_second_step'];?></em><?php echo $lang['store_deliver_confirm_daddress'];?></div>
    <div class="deliver-sell-info"><strong class="fl"><?php echo $lang['store_deliver_my_daddress'].$lang['nc_colon'];?></strong>
      <a href="javascript:void(0);" onclick="ajax_form('modfiy_daddress', '<?php echo $lang['store_deliver_select_daddress'];?>', 'index.php?act=store_deliver&op=send_address_select&order_id=<?php echo $output['order_info']['order_id'];?>', 640,0);" class="ncsc-btn-mini fr"><i class="icon-edit"></i><?php echo $lang['nc_edit'];?></a> 
      <span id="seller_address_span">
      <?php if (empty($output['daddress_info'])) {?>
      <?php echo $lang['store_deliver_none_set'];?>
      <?php } else { ?>
      <?php echo $output['daddress_info']['seller_name'];?>&nbsp;<?php echo $output['daddress_info']['telphone'];?>&nbsp;<?php echo $output['daddress_info']['area_info'];?>&nbsp;<?php echo $output['daddress_info']['address'];?>
      <?php } ?>
      </span>
    </div>
    <input type="hidden" name="daddress_id" id="daddress_id" value="<?php echo $output['daddress_info']['address_id'];?>">
    <div class="step-title mt30"><em><?php echo $lang['store_deliver_third_step'];?></em><?php echo $lang['store_deliver_express_select'];?></div>
    <div class="alert alert-success"><?php echo $lang['store_deliver_express_note'];?></div>
    <div class="tabmenu">
      <ul class="tab pngFix">
        <li id="eli1" class="active"><a href="javascript:void(0);" onclick="etab(1)"><?php echo $lang['store_deliver_express_zx'];?></a></li>
        <?php if (!isset($output['order_info']['extend_order_common']['reciver_info']['dlyp'])) {?>
        <li id="eli2" class="normal"><a href="javascript:void(0);" onclick="etab(2)"><?php echo $lang['store_deliver_express_wx'];?></a></li>
        <?php } ?>
      </ul>
    </div>
    <table class="ncsc-default-table order" id="texpress1">
      <tbody>
        <tr>
          <td class="bdl w150"><?php echo $lang['store_deliver_company_name'];?></td>
          <td class="w250"><?php echo $lang['store_deliver_shipping_code'];?></td>
          <td class="tc"><?php echo $lang['store_deliver_bforget'];?></td>
          <td class="bdr w90 tc"><?php echo $lang['nc_common_button_operate'];?></td>
        </tr>
        <?php if (is_array($output['my_express_list']) && !empty($output['my_express_list'])){?>
        <?php foreach ($output['my_express_list'] as $k=>$v){?>
        <?php if (!isset($output['express_list'][$v])) continue;?>
        <tr>
          <td class="bdl"><?php echo $output['express_list'][$v]['e_name'];?></td>
          <td class="bdl"><input name="shipping_code" type="text" class="text w200 tip-r" title="<?php echo $lang['store_deliver_shipping_code_tips'];?>" maxlength="20" nc_type='eb' nc_value="<?php echo $v;?>" /></td>
          <td class="bdl gray" nc_value="<?php echo $v;?>"></td>
          <td class="bdl bdr tc"><a nc_type='eb' nc_value="<?php echo $v;?>" href="javascript:void(0);" class="ncsc-btn"><?php echo $lang['nc_common_button_confirm'];?></a></td>
        </tr>
        <?php }?>
        <?php }?>
      </tbody>
    </table>
    <table class="ncsc-default-table order" id="texpress2" style="display:none">
      <tbody>
        <tr>
          <td colspan="2"></td>
        </tr>
        <tr>
          <td class="bdl tr"><?php echo $lang['store_deliver_no_deliver_tips'];?></td>
          <td class="bdr tl w400">&emsp;<a nc_type='eb' nc_value="e1000" href="javascript:void(0);" class="ncsc-btn"><?php echo $lang['nc_common_button_confirm'];?></a></td>
        </tr>
        <tr>
          <td colspan="2"></td>
        </tr>
      </tbody>
    </table>
  </form>
</div>
  <div class="step-title mt30"><em><?php echo 第四步;?></em><?php echo APP发货提醒?></div>
   <div class="deliver-sell-info">
   <strong class="fl"><?php echo 点击确认郑欧商城APP将收到发货信息提醒;?></strong>
   <div style="float:right">
    <form  method="POST"  action="http://m.zosc.com/zosc/rest/sendOrder.shtml" > 
      <input   type="hidden" name="orderId" id="orderId" value="<?php echo $output['order_info']['order_id'];?>">
      <input   type="submit" name="submit"   value="确认">
    </form>
   </div>
   </div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" ></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script type="text/javascript">
function etab(t){
	if (t==1){
		$('#eli1').removeClass('normal').addClass('active');
		$('#eli2').removeClass('active').addClass('normal');
		$('#texpress1').css('display','');
		$('#texpress2').css('display','none');
	}else{
		$('#eli1').removeClass('active').addClass('normal');
		$('#eli2').removeClass('normal').addClass('active');
		$('#texpress1').css('display','none');
		$('#texpress2').css('display','');
	}
}
function deliver(a){
    $.cookie('deliver', a);
}
function deliver_explainsave(){
    var deliver_explain = $("[name='deliver_explain']").val();
    var order_id = <?php echo $output['order_info']['order_id'];?>;
    $.getJSON('index.php?act=store_deliver&op=deliver_explainsave&deliver_explain='+deliver_explain+'&order_id='+order_id, function(data){
        if(data){
            if(data.result){
                alert('发货备忘保存成功');
            }else{
                alert('发货备忘保存失败');
            }
        }else{
            alert('err');
        }
    });
}
$(function(){
	//表单提示
	$('.tip-t').poshytip({
		className: 'tip-yellowsimple',
		showOn: 'focus',
		alignTo: 'target',
		alignX: 'center',
		alignY: 'top',
		offsetX: 0,
		offsetY: 2,
		allowTipHover: false
	});
	$('.tip-r').poshytip({
		className: 'tip-yellowsimple',
		showOn: 'focus',
		alignTo: 'target',
		alignX: 'right',
		alignY: 'center',
		offsetX: -50,
		offsetY: 0,
		allowTipHover: false
	});
	$('a[nc_type="eb"]').on('click',function(){
		if ($('input[nc_value="'+$(this).attr('nc_value')+'"]').val() == ''){
			showDialog('<?php echo $lang['store_deliver_shipping_code_pl'];?>', 'error','','','','','','','','',2);return false;
		}
		$('input[nc_type="eb"]').attr('disabled',true);
		$('input[nc_value="'+$(this).attr('nc_value')+'"]').attr('disabled',false);
		$('#shipping_express_id').val($(this).attr('nc_value'));
		$('#deliver_form').submit();
	});

    $('#add_time_from').datepicker({dateFormat: 'yy-mm-dd'});
    $('#add_time_to').datepicker({dateFormat: 'yy-mm-dd'});
    $('.checkall_s').click(function(){
        var if_check = $(this).attr('checked');
        $('.checkitem').each(function(){
            if(!this.disabled)
            {
                $(this).attr('checked', if_check);
            }
        });
        $('.checkall_s').attr('checked', if_check);
    });
    <?php if ($output['order_info']['shipping_code'] != ''){?>
    	$('input[nc_value="<?php echo $output['order_info']['extend_order_common']['shipping_express_id'];?>"]').val('<?php echo $output['order_info']['shipping_code'];?>');
    	$('td[nc_value="<?php echo $output['order_info']['extend_order_common']['shipping_express_id'];?>"]').html('<?php echo $output['order_info']['extend_order_common']['deliver_explain'];?>');
    <?php } ?>

    $('#my_address_add').click(function(){
		ajax_form('my_address_add', '<?php echo $lang['store_deliver_add_daddress'];?>' , 'index.php?act=store_deliver&op=send_address_edit', 550,0);
    });
});
</script>
