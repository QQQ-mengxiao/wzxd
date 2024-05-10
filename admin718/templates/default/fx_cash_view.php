<?php defined('In718Shop') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_member_predepositmanage'];?></h3>
      <ul class="tab-base">
        <li><a href="index.php?act=withdraw_commission&op=fx_cash_list">郑欧币提现管理</span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span>提现明细</span></a></li>
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
          <td colspan="2" class="required"><label>用户ID:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['fxc_member_id']; ?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label>会员名称:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['fxc_member_name']; ?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label>申请时间:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo date("Y-m-d H:i",$output['info']['fxc_add_time']); ?></td>
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
          <td colspan="2" class="required"><label>审核状态:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo str_replace(array('1','2','3'), array('审核中','审核通过','已驳回'), $output['info']['fxc_payment_state']); ?>
          </td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label>提现方式:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo str_replace(array('1','2','3'), array('账户余额','支付宝','网银'), $output['info']['fx_cash_way']); ?>
          <!-- <?php echo $output['info']['withdraw_way']; ?> -->
          </td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required" style="color:orange"><label><?php echo 账户名;?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['fxc_bank_name']; ?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required" style="color:orange"><label><?php echo 收款账户号;?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['fxc_bank_no']; ?></td>
          <td class="vatop tips"></td>
        </tr>
          <tr>
          <td colspan="2" class="required" style="color:orange"><label><?php echo 开户账户名?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['fxc_bank_user']; ?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required" style="color:orange"><label><?php echo 开户银行名?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['fxc_bank_name']; ?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required" style="color:orange"><label><?php echo 开户银行地址?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['fx_cash_address']; ?></td>
          <td class="vatop tips"></td>
        </tr>
    </table>
</div>