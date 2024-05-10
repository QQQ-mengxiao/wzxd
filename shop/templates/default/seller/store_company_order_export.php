<?php defined('In718Shop') or exit('Access Invalid!'); ?>
<!--
<div class="tabmenu">
  <?php //include template('layout/submenu');?>
</div>
-->

<form method="get" action="index.php" target="_blank">
    <table class="search-form">
        <input type="hidden" name="act" value="store_company_order"/>
        <input type="hidden" name="op" value="export_order_sub"/>
        <?php if ($_GET['state_type']) { ?>
            <input type="hidden" name="state_type" value="<?php echo $_GET['state_type']; ?>"/>
        <?php } ?>

        <tr>
            <th><?php echo $lang['store_order_order_sn']; ?></th>
            <td class="w160"><input type="text" class="text w150" name="order_sn"
                                    value="<?php echo $_GET['order_sn']; ?>"/></td>
            <th><input type="reset" name="reset" value="重置"/></th>
        </tr>
        <tr>
            <th><?php echo $lang['store_order_add_time']; ?></th>
            <td class="w380"><input type="text" class="text w70" name="query_start_date2" id="query_start_date2"
                                    value="<?php echo $_GET['query_start_date']; ?>"/><label class="add-on"><i
                            class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date2"
                                                                                        class="text w70" type="text"
                                                                                        name="query_end_date2"
                                                                                        value="<?php echo $_GET['query_end_date']; ?>"/><label
                        class="add-on"><i class="icon-calendar"></i></label></td>
            <th>支付时间</th><!--xinzeng-->
            <td class="w380"><input type="text" class="text w70" name="query_start_date_pay2" id="query_start_date_pay2"
                                    value="<?php echo $_GET['query_start_date']; ?>"/><label class="add-on"><i
                            class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_pay2"
                                                                                        class="text w70" type="text"
                                                                                        name="query_end_date_pay2"
                                                                                        value="<?php echo $_GET['query_end_date']; ?>"/><label
                        class="add-on"><i class="icon-calendar"></i></label></td>
            <th style="display:none">发货时间</th>
            <td style="display:none" class="w240"><input type="text" class="text w70" name="query_start_date2_fahuo"
                                                         id="query_start_date2_fahuo"
                                                         value="<?php echo $_GET['query_start_date']; ?>"/><label
                        class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input
                        id="query_end_date2_fahuo" class="text w70" type="text" name="query_end_date2_fahuo"
                        value="<?php echo $_GET['query_end_date']; ?>"/><label class="add-on"><i
                            class="icon-calendar"></i></label></td>

        </tr>
        <tr>
            <td class="w70 tc"><label class="submit-border">
                    <input type="submit" class="submit" value="导出"/>
                </label></td>
        </tr>
    </table>
</form>
<!--
<div style="text-align:right;"><a class="btns" target="_blank" href="index.php?<?php echo $_SERVER['QUERY_STRING']; ?>&op=export_step1"><span><?php echo $lang['nc_export']; ?>Excel</span></a></div>
-->
<script charset="utf-8" type="text/javascript"
        src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/i18n/zh-CN.js"></script>
<link rel="stylesheet" type="text/css"
      href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"/>
<link rel="stylesheet" type="text/css"
      href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"/>
<script src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>
<script type="text/javascript">
    $(function () {
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

        $('.checkall_s').click(function () {
            var if_check = $(this).attr('checked');
            $('.checkitem').each(function () {
                if (!this.disabled) {
                    $(this).attr('checked', if_check);
                }
            });
            $('.checkall_s').attr('checked', if_check);
        });
        $('#skip_off').click(function () {
            url = location.href.replace(/&skip_off=\d*/g, '');
            window.location.href = url + '&skip_off=' + ($('#skip_off').attr('checked') ? '1' : '0');
        });
        $('#skipoff2').click(function () {
            url = location.href.replace(/&skipoff2=\d*/g, '');
            window.location.href = url + '&skipoff2=' + ($('#skipoff2').attr('checked') ? '1' : '0');
        });
    });
</script> 
