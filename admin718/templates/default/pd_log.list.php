<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_member_predepositmanage'];?></h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['nc_member_predepositlog'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch" id="formSearch">
    <input type="hidden" name="act" value="pd_log_list">
    <input type="hidden" name="op" value="pd_log_list">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><label><?php echo $lang['admin_predeposit_membername'];?></label></th>
          <td><input type="text" name="mname" class="txt" value='<?php echo $_GET['mname'];?>'></td>
                    
          <th><label><?php echo $lang['admin_predeposit_maketime']; ?></label></th>
          <td><input type="text" id="stime" name="stime" class="txt date" value="<?php echo $_GET['stime'];?>" >
            <label>~</label>
            <input type="text" id="etime" name="etime" class="txt date" value="<?php echo $_GET['etime'];?>" ></td>
          
        </tr><tr><th><label><?php echo $lang['admin_predeposit_adminname'];?></label></th>
          <td><input type="text" name="aname" class="txt" value='<?php echo $_GET['aname'];?>'></td><td><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a>
            </td></tr>
      </tbody>
    </table>
  </form>
  <div class="fixed-empty"></div>
  <form method="get" name="formExport" id="formExport">
    <input type="hidden" name="act" value="brokerage">
    <input type="hidden" name="op" value="export_excell">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><label>团长id</label></th>
          <td><input type="text" name="tz_id" class="txt"></td>
                 
          <th><label>支付时间</label></th>
          <td><input type="text" id="pay_stime" name="pay_stime" class="txt date">
            <label>~</label>
            <input type="text" id="pay_etime" name="pay_etime" class="txt date" ></td>
        </tr>
        <tr><th><label>团长昵称</label></th>
          <td><input type="text" name="nickname" class="txt"></td><td><!-- <a href="javascript:void(0);" id="export" class="btn-search "> --><a id="export" class="btns" href="javascript:void(0);"><span>导出团长结算明细</span>&nbsp;</a>
            </td></tr>
      </tbody>
    </table>
  </form>
  <table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12"><div class="title">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td><ul>
            <li><?php echo $lang['admin_predeposit_log_help1'];?></li>
          </ul></td>
      </tr>
    </tbody>
  </table>
  <!-- <div style="text-align:right;"><a class="btns" target="_blank" href="index.php?act=brokerage&op=export_excell"><span><?php echo $lang['nc_export'];?>团长结算明细</span></a></div> -->
  <!-- <div style="text-align:right;"><a class="btns" target="_blank" href="index.php?<?php echo $_SERVER['QUERY_STRING'];?>&op=export_mx_step1"><span><?php echo $lang['nc_export'];?>Excel</span></a></div> -->
  <table class="table tb-type2">
    <thead>
      <tr class="thead">
        <th><?php echo $lang['admin_predeposit_membername'];?></th>
        <th class="align-center"><?php echo $lang['admin_predeposit_changetime']; ?></th>
        <th><?php echo $lang['admin_predeposit_pricetype_available'];?>(<?php echo $lang['currency_zh'];?>)</th>
        <th><?php echo $lang['admin_predeposit_pricetype_freeze'];?>(<?php echo $lang['currency_zh'];?>)</th>
        <!-- <th class="align-center"><?php echo $lang['admin_predeposit_log_stage'];?></th> -->
        <th><?php echo $lang['admin_predeposit_log_desc'];?></th>
      </tr>
    </thead>
    <tbody>
      <?php if(!empty($output['list_log']) && is_array($output['list_log'])){ ?>
      <?php foreach($output['list_log'] as $k => $v){?>
      <tr class="hover">
        <td><?php echo $v['lg_member_name'];?></td>
        <td class="nowarp align-center"><?php echo @date('Y-m-d H:i:s',$v['lg_add_time']);?></td>
        <td><?php echo floatval($v['lg_av_amount']) ? (floatval($v['lg_av_amount']) > 0 ? '+' : null ).$v['lg_av_amount'] : null;?></td>
        <td><?php echo floatval($v['lg_freeze_amount']) ? (floatval($v['lg_freeze_amount']) > 0 ? '+' : null ).$v['lg_freeze_amount'] : null;?></td>
        <td><?php echo $v['lg_desc'];?>
        <?php if ($v['lg_admin_name'] != '') { ?>
        ( <?php echo $lang['admin_predeposit_adminname'];?> <?php echo $v['lg_admin_name'];?>  )
        <?php } ?>
        </td>
      </tr>
      <?php } ?>
      <?php }else { ?>
      <tr class="no_data">
        <td colspan="10"><?php echo $lang['nc_no_record'];?></td>
      </tr>
      <?php } ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="16" id="dataFuncs"><div class="pagination"> <?php echo $output['show_page'];?></div></td>
      </tr>
    </tfoot>
  </table>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script language="javascript">
$(function(){
	$('#stime').datepicker({dateFormat: 'yy-mm-dd'});
	$('#etime').datepicker({dateFormat: 'yy-mm-dd'});
  $('#pay_stime').datepicker({dateFormat: 'yy-mm-dd'});
  $('#pay_etime').datepicker({dateFormat: 'yy-mm-dd'});
    $('#ncsubmit').click(function(){
    	$('input[name="op"]').val('pd_log_list');$('#formSearch').submit();
    });
    $('#export').click(function(){
      $('#formExport').submit();
    });
});
</script>