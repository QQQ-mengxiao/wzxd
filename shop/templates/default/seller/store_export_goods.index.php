<?php defined('In718Shop') or exit('Access Invalid!'); ?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css" />
<form method="get" action="index.php" target="_blank">
    <table class="search-form">
        <input type="hidden" name="act" value="store_export_goods" />
        <input type="hidden" name="op" value="export_order" id="op" />
        <tr>
            <th><i class="required">*</i>支付时间</th>
            <!--xinzeng-->
            <td class="w380"><input type="text" class="text w100" name="query_start_date_pay2" id="query_start_date_pay2" value="<?php echo $_GET['query_start_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_pay2" class="text w100" type="text" name="query_end_date_pay2" value="<?php echo $_GET['query_end_date']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
            <th>关联发货人</th>
            <td class="w160"><select name="daddress_id">
                    <option value="0">请选择...</option>
                    <?php if (is_array($output['daddress_list']) && !empty($output['daddress_list'])) { ?>
                        <?php foreach ($output['daddress_list'] as $val) { ?>
                            <option value="<?php echo $val['address_id']; ?>" <?php if ($_GET['daddress_id'] == $val['address_id']) {
                                                                                    echo 'selected=selected';
                                                                                } ?>><?php echo $val['seller_name']; ?></option>
                        <?php } ?>
                    <?php } ?>
                </select></td>
        </tr>
        <tr>
            <th>商品名称</th>
            <td class="w300">
                <input type="text" class="text w150" name="search_gname" value="<?php echo $_GET['search_gname']; ?>" />
            </td>
            <th>商品分类</th>
            <td class="w380"><span id="searchgc_td"></span><input type="hidden" id="choose_gcid" name="choose_gcid" value="0" />
            </td>
        </tr>
        <tr>

            <th>自提点</th>
            <td>
                <select name="address_id" class="w100">
                    <option value=""><?php echo $lang['nc_please_choose']; ?></option>
                    <?php foreach ($output['address_list'] as $val) { ?>
                        <option <?php if ($_GET['address_id'] == $val['address_id']) { ?>selected<?php } ?> value="<?php echo $val['address_id']; ?>"><?php echo $val['seller_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
            <th class=""><input type="reset" name="reset" value="重置" /></th>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="w70 tc"><label class="submit-border">
                    <input type="submit" class="submit" value="导出" />
                </label></td>
        </tr>
    </table>
</form>

<form method="get" action="index.php" target="_blank">
    <br>
    <p style="font-size: 20px;font-weight: 600;">团长结算</p>
    <br>
    <table class="search-form">
        <input type="hidden" name="act" value="store_export_goods" />
        <input type="hidden" name="op" value="export_order_tuan" id="op" />
        <tr>
            <th>支付时间</th>
            <!--xinzeng-->
            <td class="w380"><input type="text" class="text w100" name="query_start_date_pay2_tuan" id="query_start_date_pay2_tuan" value="<?php echo $_GET['query_start_date_tuan']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_pay2_tuan" class="text w100" type="text" name="query_end_date_pay2_tuan" value="<?php echo $_GET['query_end_date_tuan']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>

            <th>自提点</th>
            <td class="w160">
                <select name="address_id" class="w100">
                    <option value=""><?php echo $lang['nc_please_choose']; ?></option>
                    <?php foreach ($output['address_list'] as $val) { ?>
                        <option <?php if ($_GET['address_id'] == $val['address_id']) { ?>selected<?php } ?> value="<?php echo $val['address_id']; ?>"><?php echo $val['seller_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>发货时间</th>
            <!--xinzeng-->
            <td class="w380"><input type="text" class="text w100" name="query_start_date_deliver_tuan" id="query_start_date_deliver_tuan" value="<?php echo $_GET['query_start_date_deliver_tuan']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_deliver_tuan" class="text w100" type="text" name="query_end_date_deliver_tuan" value="<?php echo $_GET['query_end_date_deliver_tuan']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
            <th class=""><input type="reset" name="reset" value="重置" /></th>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="w70 tc"><label class="submit-border">
                    <input type="submit" class="submit" value="导出" />
                </label></td>
        </tr>
    </table>
</form>

<form method="get" action="index.php" target="_blank">
    <br>
    <p style="font-size: 20px;font-weight: 600;">供货商结算</p>
    <br>
    <table class="search-form">
        <input type="hidden" name="act" value="store_export_goods" />
        <input type="hidden" name="op" value="export_order_fa" id="op" />
        <tr>
            <th>支付时间</th>
            <!--xinzeng-->
            <td class="w380"><input type="text" class="text w100" name="query_start_date_pay2_fa" id="query_start_date_pay2_fa" value="<?php echo $_GET['query_start_date_fa']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_pay2_fa" class="text w100" type="text" name="query_end_date_pay2_fa" value="<?php echo $_GET['query_end_date_fa']; ?>" /><label class="add-on"><i class="icon-calendar"></i></label></td>
            <th>自提点</th>
            <td class="w160">
                <select name="address_id" class="w100">
                    <option value=""><?php echo $lang['nc_please_choose']; ?></option>
                    <?php foreach ($output['address_list'] as $val) { ?>
                        <option <?php if ($_GET['address_id'] == $val['address_id']) { ?>selected<?php } ?> value="<?php echo $val['address_id']; ?>"><?php echo $val['seller_name']; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
        <th>发货人类别</th>
          <td>
          <select name="peisong">
          <option value=0 <?php if ($_GET['peisong'] == 0) { ?>selected="selected" <?php } ?>>所有</option>
          <?php if (is_array($output['pei_list'])) { ?>
            <?php foreach ($output['pei_list'] as $key => $value) { ?>
              <option value=<?php echo $value['id'];?> <?php if ($_GET['peisong'] == 1) { ?>selected="selected" <?php } ?>><?php echo $value['p_name'];?></option>
            <?php } ?>
          <?php } ?>
        </select>
            </td>
            <th>关联发货人</th>
            <td class="w160"><select name="daddress_id">
                    <option value="0">请选择...</option>
                    <?php if (is_array($output['daddress_list']) && !empty($output['daddress_list'])) { ?>
                        <?php foreach ($output['daddress_list'] as $val) { ?>
                            <option value="<?php echo $val['address_id']; ?>" <?php if ($_GET['daddress_id'] == $val['address_id']) {
                                                                                    echo 'selected=selected';
                                                                                } ?>><?php echo $val['seller_name']; ?></option>
                        <?php } ?>
                    <?php } ?>
                </select></td>
        </tr>
        <tr>
            <th class=""><input type="reset" name="reset" value="重置" /></th>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="w70 tc"><label class="submit-border">
                    <input type="submit" class="submit" value="导出" />
                </label></td>
        </tr>
    </table>
</form>

<!--
<div style="text-align:right;"><a class="btns" target="_blank" href="index.php?<?php echo $_SERVER['QUERY_STRING']; ?>&op=export_step1"><span><?php echo $lang['nc_export']; ?>Excel</span></a></div>
-->
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/i18n/zh-CN.js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/common_select.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery.ajaxContent.pack.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/highcharts/highcharts.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery.poshytip.min.js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/i18n/zh-CN.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css" />
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css" />
<script src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>
<script type="text/javascript">
    $(function() {
        $('#query_start_date').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#query_end_date').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#query_start_date_fahuo').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#query_end_date_fahuo').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#query_start_date2').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#query_end_date2').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#query_start_date2_fahuo').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#query_end_date2_fahuo').datepicker({
            dateFormat: 'yy-mm-dd'
        });

        $('#query_start_date_pay').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#query_end_date_pay').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#query_start_date_pay2').datetimepicker({
            controlType: 'select'
        });
        $('#query_end_date_pay2').datetimepicker({
            controlType: 'select'
        });
        $('#query_start_date_finish').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#query_end_date_finish').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#query_start_date_finish2').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#query_end_date_finish2').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#query_start_date_pay2_tuan').datetimepicker({
            controlType: 'select'
        });
        $('#query_end_date_pay2_tuan').datetimepicker({
            controlType: 'select'
        });
        $('#query_start_date_deliver_tuan').datetimepicker({
            controlType: 'select'
        });
        $('#query_end_date_deliver_tuan').datetimepicker({
            controlType: 'select'
        });
        $('#query_start_date_pay2_fa').datetimepicker({
            controlType: 'select'
        });
        $('#query_end_date_pay2_fa').datetimepicker({
            controlType: 'select'
        });


        $('#export').click(function() {
            $('#op').val('export_order_sub');
            $('#formmx').submit();
        });
        $('#export_wzxd').click(function() {
            $('#op').val('export_order_sub_wzxd');
            $('#formmx').submit();
        });

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
<script type="text/javascript">
    $(function() {
        //商品分类
        init_gcselect(<?php echo $output['gc_choose_json']; ?>, <?php echo $output['gc_json'] ?>);

        $('#query_start_date').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#query_end_date').datepicker({
            dateFormat: 'yy-mm-dd'
        });

        //加载商品详情
        <?php if (!empty($output['goodslist']) && is_array($output['goodslist'])) { ?>
            getStatdata(<?php echo $output['goodslist'][0]['goods_id']; ?>);
        <?php } ?>
        $("[nc_type='showdata']").click(function() {
            var data_str = $(this).attr('data-param');
            eval('data_str = ' + data_str);
            getStatdata(data_str.gid);
        });
        //排序
        $("[nc_type='orderitem']").click(function() {
            var data_str = $(this).attr('data-param');
            eval("data_str = " + data_str);
            if ($(this).hasClass('desc')) {
                $("#orderby").val(data_str.orderby + ' asc');
            } else {
                $("#orderby").val(data_str.orderby + ' desc');
            }
            $('#formSearch').submit();
        });
    });

    function getStatdata(gid) {
        $('#goodsinfo_div').load('index.php?act=statistics_goods&op=goodsinfo&gid=' + gid);
    }
</script>