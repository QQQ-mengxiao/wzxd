<?php defined('In718Shop') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<form method="get" action="index.php" target="_blank">
    <table class="search-form">
        <input type="hidden" name="act" value="store_export_ps" id="act"/>
        <input type="hidden" name="op" value="export_order" id="op"/>
        <tr>
            <th class="w80"><i class="required">*</i>支付时间</th>
            <td class="w380" colspan="2">
                <input type="text" class="text w70" name="query_start_date_pay2" id="query_start_date_pay2" value="<?php echo $_GET['query_start_date']; ?>" style="width: 100px !important;" />
                <label class="add-on">
                    <i class="icon-calendar"></i>
                </label>
                &nbsp;&#8211;&nbsp;
                <input id="query_end_date_pay2" class="text w70" type="text" name="query_end_date_pay2" value="<?php echo $_GET['query_end_date']; ?>" style="width: 100px !important;" />
                <label class="add-on">
                    <i class="icon-calendar"></i>
                </label>
            </td>
            <th>关联发货人</th>
            <td class="w160">
                <select name="daddress_id" id="menu1" class="select1" onchange="getMenuByajax()">
                    <option value="0">请选择...</option>
                    <?php if (is_array($output['daddress_list']) && !empty($output['daddress_list'])) {?>
                    <?php foreach ($output['daddress_list'] as $val) {?>
                    <option value="<?php echo $val['address_id']; ?>" <?php if ($_GET['daddress_id'] == $val['address_id']) {echo 'selected=selected';}?>><?php echo $val['seller_name']; ?></option>
                    <?php }?>
                    <?php }?>
                </select>
            </td>
        </tr>
        <tr>
            <th>发货人类别</th>
            <td class="w160" colspan="2">
                <select name="delivery_type_id">
                    <option value="0">请选择...</option>
                    <?php if (is_array($output['delivery_type_list']) && !empty($output['delivery_type_list'])) {?>
                    <?php foreach ($output['delivery_type_list'] as $val) {?>
                    <option value="<?php echo $val['id']; ?>" <?php if ($_GET['id'] == $val['id']) {echo 'selected=selected';}?>><?php echo $val['p_name']; ?></option>
                    <?php }?>
                    <?php }?>
                </select>
            </td>
            <th>商品类别</th>
            <td class="w160">
                <select name="class_type">
                    <option value="0">请选择...</option>
                    <!-- <option value="1">果蔬</option>
                    <option value="2">其他(除果蔬)</option> -->
                    <option value="3">果蔬+冻品</option>
                    <option value="4">其他(除果蔬+冻品)</option>
                </select>
            </td>
            <td>&nbsp;</td>
            <td class="w80 tc"></td>
            <td class="w80 tc"></td>
        </tr>
        <tr>
            <th class="">
                <input  type="reset"  name="reset"  value="重置"/>
            </th>
            <td class="w80 tc" style="max-width: 50px !important;padding-left: 30px;">
                <input type="submit" class="w100" value="配货商品导出" id="export_order" />
                <label class="submit-border"></label>
                <input type="submit" class="green w100" value="批量配货商品导出" id="export_orderpl"/>
            </td>
            <td class="w80 tc" style="padding-left: 80px;">
                <input type="submit" class="w100" value="配货订单导出" id="export_order2"/>
                <label class="submit-border"></label>
                <input type="submit" class="green w100" value="批量配货订单导出" id="export_order3"/>
            </td>
            <td>
                <input type="submit" class="w100" value="配货订单+商品合并" id="export_order_merge" style="width: 140px !important;"/>
                <label class="submit-border"></label>
                <input type="submit" class="green w100" value="批量配货订单+商品合并" id="export_order_mergepl" style="width: 140px !important;"/>
            </td>
        </tr>
    </table>
</form>

<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/i18n/zh-CN.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/common_select.js" ></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery.ajaxContent.pack.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/highcharts/highcharts.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery.poshytip.min.js"></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/i18n/zh-CN.js" ></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css"  />
<script src="<?php echo RESOURCE_SITE_URL; ?>/js/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL; ?>/js/select2/select2.min.css"/>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL; ?>/js/select2/select2.full.min.js"></script>
<script type="text/javascript">
    //页面加载完成后初始化select2控件
    $(function () {
        $("#menu1").select2();
    });
    //下拉框
    function getMenuByajax(){
    //获取menu1的编号
    menuVal = $('#menu1').val();
    //alert('menuVal='+menuVal)
    $("#belong").attr("value",menuVal);//给隐藏的sysNum字段赋值。
    }
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

        $('#export_order').click(function(){
            $('#op').val('export_order');
            $('#formmx').submit();
        });
        $('#export_order2').click(function(){
            $('#op').val('export_order2');
            $('#formmx').submit();
        });
        $('#export_orderpl').click(function(){
            $('#op').val('export_orderpl');
            $('#formmx').submit();
        });
        $('#export_order3').click(function(){
            $('#op').val('export_order2pl');
            $('#formmx').submit();
        });
        $('#export_order_merge').click(function(){
            $('#op').val('export_data_merge');
            $('#formmx').submit();
        });
        $('#export_order_mergepl').click(function(){
            $('#op').val('export_data_mergepl');
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
<script type="text/javascript">
    $(function(){
        //商品分类
        init_gcselect(<?php echo $output['gc_choose_json']; ?>,<?php echo $output['gc_json'] ?>);

        $('#query_start_date').datepicker({dateFormat: 'yy-mm-dd'});
        $('#query_end_date').datepicker({dateFormat: 'yy-mm-dd'});

        //加载商品详情
        <?php if (!empty($output['goodslist']) && is_array($output['goodslist'])) {?>
        getStatdata(<?php echo $output['goodslist'][0]['goods_id']; ?>);
        <?php }?>
        $("[nc_type='showdata']").click(function(){
            var data_str = $(this).attr('data-param');
            eval('data_str = '+data_str);
            getStatdata(data_str.gid);
        });
        //排序
        $("[nc_type='orderitem']").click(function(){
            var data_str = $(this).attr('data-param');
            eval( "data_str = "+data_str);
            if($(this).hasClass('desc')){
                $("#orderby").val(data_str.orderby + ' asc');
            } else {
                $("#orderby").val(data_str.orderby + ' desc');
            }
            $('#formSearch').submit();
        });
    });
    function getStatdata(gid){
        $('#goodsinfo_div').load('index.php?act=statistics_goods&op=goodsinfo&gid='+gid);
    }
</script>
