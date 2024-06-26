<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>店铺统计</h3>
      <?php echo $output['top_link'];?>
    </div>
  </div>
  <div class="fixed-empty"></div>
  
  <form method="get" action="index.php" name="formSearch" id="formSearch">
    <input type="hidden" name="act" value="stat_store" />
    <input type="hidden" name="op" value="rank" />
    <div class="w100pre" style="width: 100%;">
        <table class="tb-type1 noborder search left">
          <tbody>
            <tr>
             <td>
              	<select name="stat_type" id="stat_type" class="querySelect">
                  <option value="" <?php echo $_REQUEST['stat_type']==''?'selected':''; ?>>订单量统计</option>
                  <option value="sale" <?php echo $_REQUEST['stat_type']=='sale'?'selected':''; ?>>销售额统计</option>
                </select></td>
              <td>
             <td>
              	<select name="order_type" id="order_type" class="querySelect">
                  <option value="" <?php echo $_REQUEST['order_type']==''?'selected':''; ?>>请选择</option>
                  <option value="<?php echo ORDER_STATE_NEW; ?>" <?php echo $_REQUEST['order_type']!='' && $_REQUEST['order_type']==ORDER_STATE_NEW?'selected':''; ?>>待付款</option>
                  <option value="<?php echo ORDER_STATE_PAY; ?>" <?php echo $_REQUEST['order_type']!='' && $_REQUEST['order_type']==ORDER_STATE_PAY?'selected':''; ?>>待发货</option>
                  <option value="<?php echo ORDER_STATE_SEND; ?>" <?php echo $_REQUEST['order_type']!='' && $_REQUEST['order_type']==ORDER_STATE_SEND?'selected':''; ?>>待收货</option>
                  <option value="<?php echo ORDER_STATE_SUCCESS; ?>" <?php echo $_REQUEST['order_type']!='' && $_REQUEST['order_type']==ORDER_STATE_SUCCESS?'selected':''; ?>>交易完成</option>
                  <option value="<?php echo ORDER_STATE_CANCEL; ?>" <?php echo $_REQUEST['order_type']!='' && $_REQUEST['order_type']==ORDER_STATE_CANCEL?'selected':''; ?>>已取消</option>
                </select></td>
              <td>
              	<select name="search_type" id="search_type" class="querySelect">
                  <option value="day" <?php echo $_REQUEST['search_type']=='day'?'selected':''; ?>>按照天统计</option>
                  <option value="week" <?php echo $_REQUEST['search_type']=='week'?'selected':''; ?>>按照周统计</option>
                  <option value="month" <?php echo $_REQUEST['search_type']=='month'?'selected':''; ?>>按照月统计</option>
                </select></td>
              <td id="searchtype_day" style="display:none;">
              	<input class="txt date" type="text" value="<?php echo $output['search_time'];?>" id="search_time" name="search_time">
              </td>
              <td id="searchtype_week" style="display:none;">
              	<select name="search_time_year" class="querySelect">
              		<?php foreach ($output['year_arr'] as $k=>$v){?>
              		<option value="<?php echo $k;?>" <?php echo $output['current_year'] == $k?'selected':'';?>><?php echo $v; ?></option>
              		<?php } ?>
                </select>
                <select name="search_time_month" class="querySelect">
                	<?php foreach ($output['month_arr'] as $k=>$v){?>
              		<option value="<?php echo $k;?>" <?php echo $output['current_month'] == $k?'selected':'';?>><?php echo $v; ?></option>
              		<?php } ?>
                </select>
                <select name="search_time_week" class="querySelect">
                	<?php foreach ($output['week_arr'] as $k=>$v){?>
              		<option value="<?php echo $v['key'];?>" <?php echo $output['current_week'] == $v['key']?'selected':'';?>><?php echo $v['val']; ?></option>
              		<?php } ?>
                </select>
              </td>
              <td id="searchtype_month" style="display:none;">
              	<select name="search_time_year" class="querySelect">
              		<?php foreach ($output['year_arr'] as $k=>$v){?>
              		<option value="<?php echo $k;?>" <?php echo $output['current_year'] == $k?'selected':'';?>><?php echo $v; ?></option>
              		<?php } ?>
                </select>
                <select name="search_time_month" class="querySelect">
                	<?php foreach ($output['month_arr'] as $k=>$v){?>
              		<option value="<?php echo $k;?>" <?php echo $output['current_month'] == $k?'selected':'';?>><?php echo $v; ?></option>
              		<?php } ?>
                </select>
              </td>
              <th>店铺名称</th>
         	  <td><input class="txt-long" type="text" name="store_name" value="<?php echo $_GET['store_name'];?>" /></td>
              <td><a href="javascript:void(0);" id="ncsubmit" class="btn-search tooltip" title="<?php echo $lang['nc_query'];?>">&nbsp;</a></td>
            </tr>
          </tbody>
        </table>
        <span class="right" style="margin:12px 0px 6px 4px;">
        	
        </span>
    </div>
  </form>
  <div class="stat-info"><?php if(trim($output['data_null']) != 'yes' && trim($_GET['store_name'])!=''){ ?><span>店铺：<strong><?php echo trim($_GET['store_name']); ?></strong></span><?php } ?><span>总下单量：<strong><?php echo $output['sum_data'][0]; ?></strong></span><span>总销售额：<strong><?php echo $output['sum_data'][1]?$output['sum_data'][1]:'0.00'; ?></strong>元</span></div>
  <div id="container" class="w100pre close_float" style="height:400px"></div>
  <div style="text-align:right;">
  	<input type="hidden" id="export_type" name="export_type" data-param='{"url":"<?php echo $output['actionurl'];?>&stat_type=<?php echo trim($_GET['stat_type']); ?>&order_type=<?php echo trim($_GET['order_type']); ?>&store_name=<?php echo trim($_GET['store_name']); ?>&exporttype=excel"}' value="excel"/>
  	<a class="btns" href="javascript:void(0);" id="export_btn"><span>导出Excel</span></a>
  </div>
  <table class="table tb-type2 nobdb">
    <thead>
      <tr class="thead">
      <?php foreach ($output['statlist']['headertitle'] as $v){?>
        <th class="align-center"><?php echo $v; ?></th>
      <?php }?>
      </tr>
    </thead>
    <tbody id="datatable">
    <?php if(!empty($output['store_list'])){ ?>
    <?php foreach ($output['store_list'] as $k=>$v){?>
      <tr class="hover">
        <td class="align-center"><?php echo $k+1;?></td>
        <td class="align-center"><?php echo $v['store_name'];?></td>
        <td class="align-center"><?php echo $v['allnum'];?></td>
      </tr>
    <?php } ?>
    <?php }else { ?>
    <tr class="no_data">
      <td colspan="15"><?php echo $lang['nc_no_record'];?></td>
    </tr>
    <?php } ?>
    </tbody>
  </table>
  <script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
  <script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
  <link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
  <script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/highcharts/highcharts.js"></script>
  <script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/statistics.js"></script>
</div>
<script>
//展示搜索时间框
function show_searchtime(){
	s_type = $("#search_type").val();
	$("[id^='searchtype_']").hide();
	$("#searchtype_"+s_type).show();
}
$(function () {
	<?php if(trim($output['data_null']) == 'yes'){ ?>
	alert('没有找到该店铺相关数据');
	<?php } ?>
	//统计数据类型
	var s_type = $("#search_type").val();
	$('#search_time').datepicker({dateFormat: 'yy-mm-dd'});

	show_searchtime();
	$("#search_type").change(function(){
		show_searchtime();
	});
	
	//更新周数组
	$("[name='search_time_month']").change(function(){
		var year = $("[name='search_time_year']").val();
		var month = $("[name='search_time_month']").val();
		$("[name='search_time_week']").html('');
		$.getJSON('index.php?act=common&op=getweekofmonth',{y:year,m:month},function(data){
	        if(data != null){
	        	for(var i = 0; i < data.length; i++) {
	        		$("[name='search_time_week']").append('<option value="'+data[i].key+'">'+data[i].val+'</option>');
			    }
	        }
	    });
	});

	$('select[name="search_time_year"]').change(function(){
		var s_year = $(this).val();
		$('select[name="search_time_year"]').each(function(){
			$(this).val(s_year);
		});
	});
	$('select[name="search_time_month"]').change(function(){
		var s_month = $(this).val();
		$('select[name="search_time_month"]').each(function(){
			$(this).val(s_month);
		});
	});
	
	$('#container').highcharts(<?php echo $output['stat_json'];?>);

	$('#ncsubmit').click(function(){
    	$('#formSearch').submit();
    });

	//导出图表
    $("#export_btn").click(function(){
        var item = $("#export_type");
        var type = $(item).val();
        if(type == 'excel'){
        	download_excel(item);
        }
    });
    
	$('#ncexport').click(function(){
		$("#")
    	$('#formSearch').submit();
    });
});
</script>