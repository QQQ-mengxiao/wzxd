<?php defined('In718Shop') or exit('Access Invalid!'); ?>
<!--
<div class="tabmenu">
  <?php //include template('layout/submenu');
  ?>
</div>
-->
<form method="get" action="index.php" target="_blank">
<p style="font-size: 16px;font-weight: 600;">会员分析报表</p>
<br>
  <table class="search-form">
    <input type="hidden" name="act" value="store_export_member_ziti" />
    <input type="hidden" name="op" value="export" />
    <?php if ($_GET['state_type']) { ?>
      <input type="hidden" name="state_type" value="<?php echo $_GET['state_type']; ?>" />
    <?php } ?>
    <tr>
      <th>注册时间</th>
      <td class="w240"><input type="text" class="text w70" name="query_start_date_add" id="query_start_date_add" value="<?php echo $_GET['query_start_date_add']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_add" class="text w70" type="text" name="query_end_date_add" value="<?php echo $_GET['query_end_date_add']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td class="w70 tc">
        <label class="submit-border">
          <input type="submit" class="submit" value="导出订单" />
        </label>
      </td>
    </tr>
  </table>
</form>


<form method="get" action="index.php" target="_blank" style="margin-top: 20px;">
<p style="font-size: 16px;font-weight: 600;">销售报表</p>
<br>
  <table class="search-form">
    <input type="hidden" name="act" value="store_export_member_ziti" />
    <input type="hidden" name="op" value="export_ziti" />
    <?php if ($_GET['state_type']) { ?>
      <input type="hidden" name="state_type" value="<?php echo $_GET['state_type']; ?>" />
    <?php } ?>
    <tr>
      <th>自提点</th>
      <td>
        <select name="address_id" class="w230">
          <option value=""><?php echo $lang['nc_please_choose']; ?></option>
          <?php foreach ($output['address_list'] as $val) { ?>
            <option style="font-size:large;" <?php if ($_GET['address_id'] == $val['address_id']) { ?>selected<?php } ?> value="<?php echo $val['address_id']; ?>"><?php echo $val['seller_name']; ?></option>
          <?php } ?>
        </select>
      </td>
      <th>支付时间</th>
      <td><input type="text" class="text w70" style="width:100px !important" name="query_start_date_pay" id="query_start_date_pay" value="<?php echo $_GET['query_start_date_pay']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_pay" class="text w70" type="text" style="width:100px !important" name="query_end_date_pay" value="<?php echo $_GET['query_end_date_pay']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td class="w70 tc">
        <label class="submit-border">
          <input type="submit" class="submit" value="导出订单" />
        </label>
      </td>
    </tr>
  </table>
</form>

<form method="get" action="index.php" target="_blank" style="margin-top: 20px;">
<p style="font-size: 16px;font-weight: 600;">销售报表2</p>
<br>
  <table class="search-form">
    <input type="hidden" name="act" value="store_export_member_ziti" />
    <input type="hidden" name="op" value="exportSalesData" />
    <?php if ($_GET['state_type']) { ?>
      <input type="hidden" name="state_type" value="<?php echo $_GET['state_type']; ?>" />
    <?php } ?>
    <tr>
      <th>自提点</th>
      <td>
        <select name="address_id" class="w230">
          <option value=""><?php echo $lang['nc_please_choose']; ?></option>
          <?php foreach ($output['address_list'] as $val) { ?>
            <option style="font-size:large;" <?php if ($_GET['address_id'] == $val['address_id']) { ?>selected<?php } ?> value="<?php echo $val['address_id']; ?>"><?php echo $val['seller_name']; ?></option>
          <?php } ?>
        </select>
      </td>
      <th>支付时间</th>
      <td><input type="text" class="text w70" style="width:100px !important" name="query_start_date_pay2" id="query_start_date_pay2" value="<?php echo $_GET['query_start_date_pay2']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_pay2" class="text w70" style="width:100px !important" type="text" name="query_end_date_pay2" value="<?php echo $_GET['query_end_date_pay2']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td class="w70 tc">
        <label class="submit-border">
          <input type="submit" class="submit" value="导出" />
        </label>
      </td>
    </tr>
  </table>
</form>


<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/i18n/zh-CN.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css" />
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css" />
<script src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>
<script type="text/javascript">
  $(function() {
//    query_start_date_pay
    $('#query_start_date_add').datepicker({
      dateFormat: 'yy-mm-dd'
    });
    $('#query_end_date_add').datepicker({
      dateFormat: 'yy-mm-dd'
    });
    $('#query_start_date_pay').datetimepicker({controlType: 'select'});
    $('#query_end_date_pay').datetimepicker({controlType: 'select'});
    
    $('#query_start_date_pay2').datetimepicker({controlType: 'select'});
    $('#query_end_date_pay2').datetimepicker({controlType: 'select'});

    $('.checkall_s').click(function() {
      var if_check = $(this).attr('checked');
      $('.checkitem').each(function() {
        if (!this.disabled) {
          $(this).attr('checked', if_check);
        }
      });
      $('.checkall_s').attr('checked', if_check);
    });
    $('#skip_off').click(function() {
      url = location.href.replace(/&skip_off=\d*/g, '');
      window.location.href = url + '&skip_off=' + ($('#skip_off').attr('checked') ? '1' : '0');
    });
    $('#skipoff2').click(function() {
      url = location.href.replace(/&skipoff2=\d*/g, '');
      window.location.href = url + '&skipoff2=' + ($('#skipoff2').attr('checked') ? '1' : '0');
    });
  });
</script>
