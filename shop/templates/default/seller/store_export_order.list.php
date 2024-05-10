<?php defined('In718Shop') or exit('Access Invalid!');?>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<form method="get" action="index.php" target="_blank">
  <table class="search-form">
    <input type="hidden" name="act" value="store_export_order_list" />
    <input type="hidden" name="op" value="export_order" id="op"/>
    <tr>
    <th><i class="required">*</i>支付时间</th><!--xinzeng-->
      <td class="w380"><input type="text" class="text w70" name="query_start_date_pay2" id="query_start_date_pay2" value="<?php echo $_GET['query_start_date']; ?>" style="width: 100px !important;" /><label class="add-on"><i class="icon-calendar"></i></label>&nbsp;&#8211;&nbsp;<input id="query_end_date_pay2" class="text w70" type="text" name="query_end_date_pay2" value="<?php echo $_GET['query_end_date']; ?>" style="width: 100px !important;" /><label class="add-on"><i class="icon-calendar"></i></label></td>
  <th>发货人类别</th>
      <td class="w160"><select name="delivery_type_id" id="delivery_type_id">
          <option value="0">请选择...</option>
          <?php if(is_array($output['delivery_type_list']) && !empty($output['delivery_type_list'])){?>
          <?php foreach ($output['delivery_type_list'] as $val) {?>
          <option value="<?php echo $val['id']; ?>" <?php if ($_GET['id'] == $val['id']){ echo 'selected=selected';}?>><?php echo $val['p_name']; ?></option>
          <?php }?>
          <?php }?>
        </select></td>
    </tr>
    <tr>
       
    <th>自提点</th>
            <td >
            <select name="address_id" class="w100" id="address_id">
            <option value=""><?php echo $lang['nc_please_choose'];?></option>
            <?php foreach($output['address_list'] as $val) { ?>
            <option <?php if($_GET['address_id'] == $val['address_id']){?>selected<?php }?> value="<?php echo $val['address_id']; ?>"><?php echo $val['seller_name']; ?></option>
            <?php } ?>
                     </select>
         </td>
         <th>导表类型</th>
         <td >
            <select name="type" class="w100" id="type">
            <option value="0">请选择</option>
            <option value="1">发货订单表</option>
            <option value="2">退款订单表</option>
            </select>
         </td>
    <th class=""><input  type="reset"  name="reset"  value="重置"/></th>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
      <td class="w70 tc"><label class="submit-border">
          <input type="submit" class="submit" value="导出" />
        </label></td>
    </tr>
  </table>
</form>
<p style="margin-top: 15px;">提示：此专用导出不用选发货人类别，导出内容为：1天1配 11点-11点，1天2配 20点-11点，团购当天取货</p>
<a class="ncsc-btn ncsc-btn-green" name="export11" id="export11" href="" value="">11点截单专用导出</a>
<p style="margin-top: 15px;">提示：此批量导出，自提点可选可不选，导出内容为筛选条件下的订单</p>
<a class="ncsc-btn ncsc-btn-green" name="batchExport" id="batchExport" href="" value="">仓库报表批量导出</a>
<!--
<div style="text-align:right;"><a class="btns" target="_blank" href="index.php?<?php echo $_SERVER['QUERY_STRING'];?>&op=export_step1"><span><?php echo $lang['nc_export'];?>Excel</span></a></div>
-->
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" ></script>
<script charset="utf-8" type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" ></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.ajaxContent.pack.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/highcharts/highcharts.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js"></script>
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

    // 11点专用导出
    $('#export11').click(function(){
        var query_start_date_pay2 = $('#query_start_date_pay2').val();
        var query_end_date_pay2 = $('#query_end_date_pay2').val();
        var delivery_type_id = $('#delivery_type_id').val();
        var address_id = $('#address_id').val();
        var type = $('#type').val();//alert('<?php echo $_SERVER['HTTP_HOST'];?>');
        var href = 'index.php?act=store_export_order_list&op=batchExportExcel11&query_start_date_pay2='+query_start_date_pay2+'&query_end_date_pay2='+query_end_date_pay2+'&delivery_type_id='+delivery_type_id+'&address_id='+address_id+'&type='+type;
        $('#export11').attr('href',href);
    });

    // 批量导出
    $('#batchExport').click(function(){
        var query_start_date_pay2 = $('#query_start_date_pay2').val();
        var query_end_date_pay2 = $('#query_end_date_pay2').val();
        var delivery_type_id = $('#delivery_type_id').val();
        var address_id = $('#address_id').val();
        var type = $('#type').val();//alert('<?php echo $_SERVER['HTTP_HOST'];?>');
        var href = 'index.php?act=store_export_order_list&op=batchExportExcel&query_start_date_pay2='+query_start_date_pay2+'&query_end_date_pay2='+query_end_date_pay2+'&delivery_type_id='+delivery_type_id+'&address_id='+address_id+'&type='+type;
        $('#batchExport').attr('href',href);
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
    init_gcselect(<?php echo $output['gc_choose_json'];?>,<?php echo $output['gc_json']?>);
    
    $('#query_start_date').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_date').datepicker({dateFormat: 'yy-mm-dd'});

    //加载商品详情
    <?php if (!empty($output['goodslist']) && is_array($output['goodslist'])) { ?>
    getStatdata(<?php echo $output['goodslist'][0]['goods_id'];?>);
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
