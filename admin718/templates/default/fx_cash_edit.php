<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_member_predepositmanage'];?></h3>
      <ul class="tab-base">
        <li><a href="index.php?act=withdraw_commission&op=fx_cash_list"><span>提现列表</span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['admin_predeposit_cashmanage']; ?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
    <table class="table tb-type2 nobdb">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label>提现编号:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">NC<?php echo $output['info']['fxc_sn']; ?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr class="noborder">
          <td colspan="2" class="required"><label>会员ID:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">NC<?php echo $output['info']['fxc_member_id']; ?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_membername'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['fxc_member_name']; ?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_cash_price'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['fxc_amount']; ?>&nbsp;<?php echo $lang['currency_zh'];?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label>提现方式:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo str_replace(array('1','2','3'), array('账户余额','支付宝','网银'), $output['info']['fx_cash_way']); ?>
          </td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td><label style="color:orange">当前审核状态:&nbsp;&nbsp;&nbsp;&nbsp;<?php echo str_replace(array('1','2','3'), array('审核中','审核通过','已驳回'), $output['info']['fxc_payment_state']); ?></label>
          </td>
        </tr>
      </tbody>
     <!--  <?php if (!intval($output['info']['pdc_payment_state'])) {?> -->
        <tfoot id="submit-holder">
        <tr class="tfoot">
        <td colspan="2">
        <a class="btn" href="javascript:if (confirm('<?php echo $lang['admin_fx_cash_confirm'];?>')){window.location.href='index.php?act=withdraw_commission&op=fx_cash_pay&id=<?php echo $output['info']['id']; ?>&fxc_amount=<?php echo $output['info']['fxc_amount']; ?>';}else{}"><span><?php echo $lang['admin_fx_cash_audited'];?></span></a>
        <a class="btn" href="javascript:if (confirm('<?php echo $lang['admin_refuse_fx_cash_confirm'];?>')){window.location.href='index.php?act=withdraw_commission&op=fx_refuse_cash_pay&id=<?php echo $output['info']['id']; ?>';}else{}"><span><?php echo $lang['admin_refuse_fx_cash_audited'];?></span></a>
        </td>

        </tr>
        </tfoot>
     <!-- <?php } ?> -->
    </table>
</div>