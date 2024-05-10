<?php defined('In718Shop') or exit('Access Invalid!');?>
<!--
<div class="tabmenu">
  <?php //include template('layout/submenu');?>
</div>
-->

<form method="get" action="index.php" target="_blank">
  <table class="search-form">
    <input type="hidden" name="act" value="store_company_order" />
    <input type="hidden" name="op" value="export_order_sub" id="op"/>
    <?php if ($_GET['state_type']) { ?>
    <input type="hidden" name="state_type" value="<?php echo $_GET['state_type']; ?>" />
    <?php } ?>

      <tr>
      <td>&nbsp;</td>
      </tr>
    <tr>
      
      <th style="display:none">商品模式</th>
      <td style="display:none">      
      <select name="is_mode">
        <option value=0 <?php if ($_GET['is_mode'] == 0) {?>selected="selected"<?php }?>>一般</option>
        <option value=1 <?php if ($_GET['is_mode'] == 1) {?>selected="selected"<?php }?>>备货</option>
        <option value=2 <?php if ($_GET['is_mode'] == 2) {?>selected="selected"<?php }?>>集货</option>
        <option value='' <?php if ($_GET['is_mode'] == '') {?>selected="selected"<?php }?>>所有</option>
        </select>
        &nbsp;&nbsp;支付方式&nbsp;
      <select name="pay_code">
        <option value='alipay' <?php if ($_GET['pay_code'] == 'alipay') {?>selected="selected"<?php }?>>支付宝</option>
        <option value='wx_saoma' <?php if ($_GET['pay_code'] == 'wx_saoma') {?>selected="selected"<?php }?>>微信支付</option>
        <option value='predeposit' <?php if ($_GET['pay_code'] == 'predeposit') {?>selected="selected"<?php }?>>站内余额</option>
        <option value='' <?php if ($_GET['pay_code'] == '') {?>selected="selected"<?php }?>>所有</option>
        </select>
      <!--  
      &nbsp;&nbsp;退款状态&nbsp;
      <select name="refund_state">
        <option value=0 <?php if ($_GET['refund_state'] == 0) {?>selected="selected"<?php }?>>无退款</option>
        <option value=1 <?php if ($_GET['refund_state'] == 1) {?>selected="selected"<?php }?>>部分退款</option>
        <option value=2 <?php if ($_GET['refund_state'] == 2) {?>selected="selected"<?php }?>>全部退款</option>
        <option value='' <?php if ($_GET['refund_state'] == '') {?>selected="selected"<?php }?>>所有</option>
        </select>
      -->
      &nbsp;&nbsp;订单状态&nbsp;
      <select name="order_state">
        <option value=20 <?php if ($_GET['order_state'] == 20) {?>selected="selected"<?php }?>>待发货</option>
        <option value=30 <?php if ($_GET['order_state'] == 30) {?>selected="selected"<?php }?>>待收货</option>
        <option value=40 <?php if ($_GET['order_state'] == 40) {?>selected="selected"<?php }?>>交易完成</option>
        <option value=10 <?php if ($_GET['order_state'] == 10) {?>selected="selected"<?php }?>>待付款</option>
        <option value=0  <?php if ($_GET['order_state'] == 0) {?>selected="selected"<?php }?>>已取消</option>
        <option value='' <?php if ($_GET['order_state'] == '') {?>selected="selected"<?php }?>>所有</option>
        </select>  
      </td>
      <?php if ($_GET['state_type'] == 'store_order') { ?>
      <th><input type="checkbox" id="skipoff2" value="1" <?php echo $_GET['skipoff2'] == 1 ? 'checked="checked"' : null;?>  name="skipoff2"> </th>
      <td align="left"><label for="skipoff2">不显示已关闭的订单</label></td>
      <?php } ?>
	  <th><?php echo $lang['store_order_order_sn'];?></th>
      <td class="w160"><input type="text" class="text w150" name="order_sn" value="<?php echo $_GET['order_sn']; ?>" /></td>
      <th><input  type="reset"  name="reset"  value="重置"/></th>      
          </tr>
    <tr> 
      <th><?php echo $lang['store_order_add_time'];?></th>
      <td class="w380"><input type="text" class="text w70" name="query_start_date2" id="query_start_date2" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date2" class="text w70" type="text" name="query_end_date2" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
	  <th>支付时间</th><!--xinzeng-->
      <td class="w380"><input type="text" class="text w70" name="query_start_date_pay2" id="query_start_date_pay2" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_pay2" class="text w70" type="text" name="query_end_date_pay2" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
      <th style="display:none">发货时间</th>
      <td style="display:none" class="w240"><input type="text" class="text w70" name="query_start_date2_fahuo" id="query_start_date2_fahuo" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date2_fahuo" class="text w70" type="text" name="query_end_date2_fahuo" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>

    </tr>
    <tr>
    <th style="display:none">支付时间</th><!--xinzeng-->
      <td style="display:none" class="w380"><input type="text" class="text w70" name="query_start_date_pay2" id="query_start_date_pay2" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_pay2" class="text w70" type="text" name="query_end_date_pay2" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
        <th style="display:none">订单完成时间</th>
        <td style="display:none" class="w240"><input type="text" class="text w70" name="query_start_date_finish2" id="query_start_date_finish2" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_finish2" class="text w70" type="text" name="query_end_date_finish2" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
    </tr>
    <tr style="display:none">    
      <th>买家帐号</th>  
      <td class="w100"><input type="text" class="text w150" name="buyer_name" value="<?php echo $_GET['buyer_name']; ?>" /></td>
      <th>收货人</th>  
      <td class="w100"><input type="text" class="text w150" name="consignee_name" value="<?php echo $_GET['consignee_name']; ?>" /></td>
    </tr>
    <tr style="display:none">  
      <th>商品名称</th>
      <td class="w160"><input type="text" class="text w150" name="goods_name" value="<?php echo $_GET['goods_name']; ?>" /></td>
      <th>商品货号</th>
      <td class="w160"><input type="text" class="text w150" name="goods_serial" value="<?php echo $_GET['goods_serial']; ?>" /></td>
    </tr>
    <tr>  

          </tr>
    <tr> 

    </tr>
    <tr> 
      
          </tr>
    <tr> 
    <th style="display:none"><?php echo $lang['store_order_order_sn'];?></th>
      <td style="display:none" class="w160"><input type="text" class="text w150" name="order_sn" value="<?php echo $_GET['order_sn']; ?>" /></td>
      <th style="display:none">发货人</th>
      <td class="w100" style="display:none"><input type="text" class="text w150" name="senderusername" value="<?php echo $_GET['senderusername']; ?>" /></td>
      <tr style="display:none">
          <th>活动类型</th>
          <td>
              <select name="order_type">
                  <option value=0 <?php if ($_GET['order_type'] == 0) {?>selected="selected"<?php }?>>无活动</option>
                  <option value=1 <?php if ($_GET['order_type'] == 1) {?>selected="selected"<?php }?>>阶梯价</option>
                  <option value=2 <?php if ($_GET['order_type'] == 2) {?>selected="selected"<?php }?>>团购</option>
                  <option value=3 <?php if ($_GET['order_type'] == 3) {?>selected="selected"<?php }?>>新人专享</option>
                  <option value=4 <?php if ($_GET['order_type'] == 4) {?>selected="selected"<?php }?>>限时秒杀</option>
                  <option value=5 <?php if ($_GET['order_type'] == 5) {?>selected="selected"<?php }?>>即买即送</option>
                  <option value='' <?php if ($_GET['order_type'] == '') {?>selected="selected"<?php }?>>所有</option>
              </select>
      </tr>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
      <td class="w70 tc"><label class="submit-border">
          <input type="submit" class="submit" value="导出"/>
        </label></td>
    </tr>
  </table>
</form>
<!--
<div style="text-align:right;"><a class="btns" target="_blank" href="index.php?<?php echo $_SERVER['QUERY_STRING'];?>&op=export_step1"><span><?php echo $lang['nc_export'];?>Excel</span></a></div>
-->
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" ></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"  />
<script src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>
<script type="text/javascript">
$(function(){
    $('#query_start_date').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_start_date_fahuo').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date_fahuo').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_start_date2').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date2').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_start_date2_fahuo').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date2_fahuo').datepicker({dateFormat: 'yy-mm-dd'});

    $('#query_start_date_pay').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date_pay').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_start_date_pay2').datetimepicker({controlType: 'select'});
    $('#query_end_date_pay2').datetimepicker({controlType: 'select'});   
    $('#query_start_date_finish').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date_finish').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_start_date_finish2').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date_finish2').datepicker({dateFormat: 'yy-mm-dd'});

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
    $('#skip_off').click(function(){
        url = location.href.replace(/&skip_off=\d*/g,'');
        window.location.href = url + '&skip_off=' + ($('#skip_off').attr('checked') ? '1' : '0');
    });
    $('#skipoff2').click(function(){
        url = location.href.replace(/&skipoff2=\d*/g,'');
        window.location.href = url + '&skipoff2=' + ($('#skipoff2').attr('checked') ? '1' : '0');
    });
});
</script> 
