<?php defined('In718Shop') or exit('Access Invalid!');?>
<!--
<div class="tabmenu">
  <?php //include template('layout/submenu');?>
</div>
-->
<form method="get" action="index.php" target="_blank">
  <table class="search-form">
    <input type="hidden" name="act" value="store_export_amount" />
    <input type="hidden" name="op" value="export_order" />
    <?php if ($_GET['state_type']) { ?>
    <input type="hidden" name="state_type" value="<?php echo $_GET['state_type']; ?>" />
    <?php } ?>
    <tr>
    <!-- <th>商品模式</th>
      <td>      
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
      <th><input type="checkbox" id="skip_off" value="1" <?php echo $_GET['skip_off'] == 1 ? 'checked="checked"' : null;?>  name="skip_off"> </th>
      <td><label for="skip_off">不显示已关闭的订单</label></td>
      <?php } ?> -->
      <th class=""><input  type="reset"  name="reset"  value="重置"/></th>
    </tr>
   <!--  <tr>  
      <th><?php echo $lang['store_order_add_time'];?></th>
      <td class="w380"><input type="text" class="text w70" name="query_start_date" id="query_start_date" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date" class="text w70" type="text" name="query_end_date" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
      <th>发货时间</th>
      <td class="w240"><input type="text" class="text w70" name="query_start_date_fahuo" id="query_start_date_fahuo" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_fahuo" class="text w70" type="text" name="query_end_date_fahuo" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>

    </tr> -->
    <tr>
    <th>支付时间</th><!--xinzeng-->
      <td class="w380"><input type="text" class="text w70" name="query_start_date_pay" id="query_start_date_pay" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_pay" class="text w70" type="text" name="query_end_date_pay" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
    <!-- <th>订单完成 时间</th>
      <td class="w240"><input type="text" class="text w70" name="query_start_date_finish" id="query_start_date_finish" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_finish" class="text w70" type="text" name="query_end_date_finish" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td> -->
    </tr>

    <td>&nbsp;</td>
    <td>&nbsp;</td>
      <td class="w70 tc"><label class="submit-border">
          <input type="submit" class="submit" value="导出订单" />
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

    $('#export').click(function(){
        $('#op').val('export_order_sub');
        $('#formmx').submit();
    });
    $('#export_wzxd').click(function(){
        $('#op').val('export_order_sub_wzxd');
        $('#formmx').submit();
    });

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
