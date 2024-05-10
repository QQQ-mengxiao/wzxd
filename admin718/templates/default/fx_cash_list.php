<?php defined('In718Shop') or exit('Access Invalid!');?>
<?php header("Content-type:text/html;charset=utf-8");?>
<div class="page" >
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_member_commission_management'];?></h3>
      <ul class="tab-base">
        <li><a href="<?php echo urlAdmin('mb_fenxiao','fx_index')?>"><span>分销管理</span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span>郑欧币提现管理</span></a></li>
<!--          <li><a href="index.php?act=predeposit&op=predeposit_add"><span><?php echo $lang['nc_member_commission_withdrawal_d'];?></span></a></li> -->
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" action="index.php" name="formSearch" id="formSearch">
    <input type="hidden" name="act" value="withdraw_commission">
    <input type="hidden" name="op" value="com_cash_list">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><?php echo $lang['admin_predeposit_membername'];?></th>
          <td><input type="text" name="mname" class="txt" value='<?php echo $_GET['mname'];?>'></td>
          <th><?php echo $lang['admin_predeposit_apptime']; ?></th>
          <td colspan="2"><input type="text" id="stime" name="stime" class="txt date" value="<?php echo $_GET['stime'];?>">
            <label>~</label>
            <input type="text" id="etime" name="etime" class="txt date" value="<?php echo $_GET['etime'];?>"></td>
          <th><?php echo $lang['admin_predeposit_paystate']?></th>
          <td>
            <select id="paystate_search" name="paystate_search">
              <option value=""><?php echo $lang['nc_please_choose']; ?></option>
              <option value="1" <?php if($_GET['paystate_search'] == '1' ) { ?>selected="selected"<?php } ?>>审核中</option>
              <option value="2" <?php if($_GET['paystate_search'] == '2' ) { ?>selected="selected"<?php } ?>>审核通过</option>
              <option value="3" <?php if($_GET['paystate_search'] == '3' ) { ?>selected="selected"<?php } ?>>已驳回</option>
            </select>
            <a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a></td>
        </tr>
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
            <li><?php echo $lang['admin_predeposit_cash_help3'];?></li>
            <li><?php echo $lang['admin_predeposit_cash_help4'];?></li>
          </ul></td>
      </tr>
    </tbody>
  </table>
  <div style="text-align:right;"><a class="btns" target="_blank" href="index.php?<?php echo $_SERVER['QUERY_STRING'];?>&op=export_cash_step1"><span><?php echo $lang['nc_export'];?>Excel</span></a></div>
  <table class="table tb-type2 nobdb">
    <thead>
      <tr class="thead">
        <th>&nbsp;</th>
        <th>提现编号</th>
        <th><?php echo $lang['admin_predeposit_nameid'];?></th>
        <th><?php echo $lang['admin_predeposit_membername'];?></th>
        <th class="align-center"><?php echo $lang['admin_predeposit_apptime'];?></th>
        <th class="align-center"><?php echo $lang['admin_predeposit_cash_price']; ?>(<?php echo $lang['currency_zh']; ?>)</th>
        <th class="align-center"><?php echo $lang['admin_predeposit_approvestate']; ?></th>
        <th class="align-center">审核时间</th>
        <th class="align-center"><?php echo $lang['nc_handle']; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php if(!empty($output['list']) && is_array($output['list'])){ ?>
      <?php foreach($output['list'] as $k => $v){?>
      <tr class="hover">
        <td class="w12">&nbsp;</td>
        <td>NC<?php echo $v['fxc_sn']; ?></td>
        <td><?php echo $v['fxc_member_id']; ?></td>
        <td><?php echo $v['fxc_member_name']; ?></td>
        <td class="nowrap align-center"><?php echo @date('Y-m-d H:i:s',$v['fxc_add_time']);?></td>
        <td class="nowrap align-center"><?php echo $v['fxc_amount'];?></td>
        <td class="align-center"><?php echo str_replace(array('1','2','3'), array('审核中','审核通过','已驳回'), $v['fxc_payment_state']); ?></td>
        <td class="align-center"> <?php if ($v['fxc_payment_time'] != null) {?>
            <?php echo @date('Y-m-d H:i:s',$v['fxc_payment_time']);?>
            <?php } ?>
            </td> 
        <td class="w90 align-center">
          <?php  ?><a href="index.php?act=withdraw_commission&op=fx_cash_view&id=<?php echo $v['id']; ?>" class="edit"><?php echo $lang['nc_details']; ?></a>
          <a href="index.php?act=withdraw_commission&op=fx_cash_edit&id=<?php echo $v['id']; ?>" class="edit"><?php echo $lang['nc_audit']; ?></a>
          </td>
      </tr>
      <?php } ?>
      <?php } ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="16" id="dataFuncs"><div class="pagination"> <?php echo $output['show_page'];?> </div></td>
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
        $('#sptime').datepicker({dateFormat: 'yy-mm-dd'});
        $('#eptime').datepicker({dateFormat: 'yy-mm-dd'});
    $('#ncsubmit').click(function(){
    	$('input[name="op"]').val('fx_cash_list');$('#formSearch').submit();
    });
});
</script>